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

    public static function getAuthorizeURL()
    {
        $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);
            $response = $connection->oauth("oauth/request_token", array("oauth_callback" => "http://".$_SERVER['SERVER_NAME']."/"));
        $_SESSION['oauth_token'] = $response['oauth_token'];
        $_SESSION['oauth_token_secret'] = $response['oauth_token_secret'];
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
                return true;
            }
        }

        return false;
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

    public static function getTeam($username = null) {
        if ($username === null) {
            $username = self::getUserName();
        }

        $team = Team::findOne('lower(username) = ?', [strtolower($username)]);

        if ($team && strtolower($team->username) === strtolower($username)) {
            return $team;
        }
    }
}