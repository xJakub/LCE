<?php
/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 30/10/2015
 * Time: 0:33
 */

use Abraham\TwitterOAuth\TwitterOAuth;

class TwitterAuth
{
    static function isLogged() {
        self::doLogin();
        return isset($_SESSION['twitter-userid']) && $_SESSION['twitter-userid'];
    }

    public static function getAuthorizeURL($route = "/")
    {
        $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);
        $response = $connection->oauth("oauth/request_token", array("oauth_callback" => "http://".$_SERVER['SERVER_NAME'].$route));
        $_SESSION['oauth_token'] = $response['oauth_token'];
        $_SESSION['oauth_token_secret'] = $response['oauth_token_secret'];
        $url = $connection->url("oauth/authorize", array("oauth_token" => $response['oauth_token']));
        return $url;
    }

    public static function getBotAuthorizeURL($route = "/")
    {
        $connection = new TwitterOAuth(CONSUMER_KEY_BOT, CONSUMER_SECRET_BOT);
        $response = $connection->oauth("oauth/request_token", array("oauth_callback" => "http://".$_SERVER['SERVER_NAME'].$route));
        $_SESSION['oauth_token_bot'] = $response['oauth_token'];
        $_SESSION['oauth_token_secret_bot'] = $response['oauth_token_secret'];
        $url = $connection->url("oauth/authorize", array("oauth_token" => $response['oauth_token']));
        return $url;
    }

    public static function doLogin() {
        $oauth_token = HTMLResponse::fromGETorPOST('oauth_token');
        $oauth_verifier = HTMLResponse::fromGETorPOST('oauth_verifier');

        if ($oauth_token && $oauth_verifier && $oauth_token === $_SESSION['oauth_token']) {
            $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $oauth_token, $_SESSION['oauth_token_secret']);
            $access_token = $connection->oauth("oauth/access_token", array("oauth_verifier" => $oauth_verifier));
            if ($userid = $access_token['user_id']) {
                $_SESSION['twitter-userid'] = $userid;
                $_SESSION['twitter-username'] = $access_token['screen_name'];
                $_SESSION['oauth_token'] = $access_token['oauth_token'];
                $_SESSION['oauth_token_secret'] = $access_token['oauth_token_secret'];

                self::afterLogin();

                return true;
            }
        }

        return false;
    }

    public static function doBotLogin() {
        $oauth_token = HTMLResponse::fromGETorPOST('oauth_token');
        $oauth_verifier = HTMLResponse::fromGETorPOST('oauth_verifier');

        if ($oauth_token && $oauth_verifier && $oauth_token === $_SESSION['oauth_token_bot']) {
            $connection = new TwitterOAuth(CONSUMER_KEY_BOT, CONSUMER_SECRET_BOT, $oauth_token, $_SESSION['oauth_token_secret_bot']);
            $access_token = $connection->oauth("oauth/access_token", array("oauth_verifier" => $oauth_verifier));
            if ($userid = $access_token['user_id']) {
                $botConfig['twitter-userid'] = $userid;
                $botConfig['twitter-username'] = $access_token['screen_name'];
                $botConfig['oauth_token'] = $access_token['oauth_token'];
                $botConfig['oauth_token_secret'] = $access_token['oauth_token_secret'];
                $botConfig['dateline'] = time();

                unset($_SESSION['oauth_token_bot']);

                $con = "<?php return ".var_export($botConfig, true)."; ?>";

                file_put_contents("botconfig.php", $con);
                return true;
            }
        }

        return false;
    }

    public static function getBotConfig() {
        return file_exists("botconfig.php") ?
            include("botconfig.php") :
            array();
    }

    private static function afterLogin()
    {
        $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);

        // Seamos francos. Necesitamos los avatares.
        // Así que aprovechamos el login para coger el avatar del usuario
        // y de los usuarios para los que no tengamos avatares.

        $userids = Model::pluck(Bet::find('avatar = "" and userid != "" group by userid limit 100'), 'userid');

        if (count($userids)) {
            $json = $connection->get('users/lookup', ['user_id' => implode(',', $userids)]);

            $newavatars = [];

            foreach ($json as $userdata) {
                $newavatars[$userdata->id_str] = $userdata->profile_image_url;
            }

            /**
             * @var $bets Bet[]
             */
            $bets = Bet::find('userid in (' . implode(',', $userids) . ')');

            foreach ($bets as $index => $bet) {
                $userid = $bet->userid;
                $bet->avatar = $newavatars[$userid] ? $newavatars[$userid] : 'about:blank';
            }

            Model::saveAll($bets);
        }

        $json = $connection->get('users/show', ['user_id' => $_SESSION['twitter-userid']]);
        $_SESSION['twitter-avatar'] = isset($json->profile_image_url) ? $json->profile_image_url : '';

    }

    public static function forceLogin() {
        if (!self::isLogged()) {
            if (!self::doLogin()) {
                HTMLResponse::exitWithRoute(self::getAuthorizeURL());
            }
        }
    }

    public static function getUserId() {
        return $_SESSION['twitter-userid'];
    }

    public static function getUserName() {
        return $_SESSION['twitter-username'];
    }

    public static function getAvatar() {
        return $_SESSION['twitter-avatar'];
    }

    public static function getTeam($username = null) {
        if ($username === null) {
            $username = self::getUserName();
        }

        $team = Team::findOne('lower(username) = ?', [strtolower($username)]);

        if ($team && strtolower($team->username) === strtolower($username)) {
            return $team;
        }
    }

    public static function isBot() {
        return self::getUserName() == 'LCE_Pokemon';
    }

    public static function botSendPrivateMessage($dest, $message) {
        static $botConnection = null;
        if (!$botConnection) {
            $botConfig = self::getBotConfig();
            $botConnection = new TwitterOAuth(CONSUMER_KEY_BOT, CONSUMER_SECRET_BOT,
                $botConfig['oauth_token'], $botConfig['oauth_token_secret']);
        }
        return $botConnection->post('direct_messages/new', ['text'=>$message, 'screen_name'=>$dest]);
    }
}