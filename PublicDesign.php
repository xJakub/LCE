<?php
/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 28/03/2015
 * Time: 3:38
 */

require_once('lib/HTMLResponse.php');

class PublicDesign extends HTMLResponse
{
    /**
     * @var Season
     */
    private $season;


    public function __construct(PublicSection $section)
    {
        parent::__construct();

        if (HTMLResponse::fromGET('authenticate')) {
            HTMLResponse::exitWithRoute(TwitterAuth::getAuthorizeURL(HTMLResponse::getRoute()));
        }


        if (HTMLResponse::fromGET('logout')) {
            session_destroy();
            HTMLResponse::exitWithRoute(HTMLResponse::getRoute());
        }

        TwitterAuth::isLogged();

        if (TwitterAuth::isBot()) {
            if (HTMLResponse::fromGET('authenticatebot')) {
                HTMLResponse::exitWithRoute(TwitterAuth::getBotAuthorizeURL(HTMLResponse::getRoute()));
            }
            TwitterAuth::doBotLogin();
        }

        $this->topMenu = array();

        $this->setMeta('charset', 'utf-8');
        $this->setMeta('viewport', 'width=device-width, initial-scale=1.0');
        $this->setTitle($section->getTitle() . ' - ' . $section->getSubtitle());

        $this->addStyleSheet('/css/style.css');

        if ($_SERVER['REMOTE_ADDR'] == '127.0.0.1' || $_SERVER['REMOTE_ADDR'] == '::1') {
            $this->addJavaScript("http://localhost/jquery.js", true);
        }
        else {
            $this->addJavaScript("https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js", true);
        }

        $this->addJavaScript("
            $(document).ready(function() {
                if(screen.width <= 512) {
                    document.getElementById('meta-viewport').setAttribute('content','width=512');
                }
            })
        ", false);



        $this->season = null;
        if ($_SESSION['seasonid']) {
            $this->season = Season::get($_SESSION['seasonid']);
        }

        $section->setDesign($this);
        $this->section = $section;

        if ($this->season == null) {
            $this->season = Season::findOne('ispublic order by isdefault desc');
        }
        $seasonLink = $this->season->getLink();

        $this->addToTopMenu("/{$seasonLink}/", 'Enfrentamientos', '/');
        $this->addToTopMenu("/{$seasonLink}/equipos/", 'Equipos', '/equipos/.*');
        $this->addToTopMenu("/{$seasonLink}/calendario/", 'Calendario', '/calendario/');
        $this->addToTopMenu("/{$seasonLink}/clasificacion/", 'Clasificación', '/clasificacion/');
        $this->addToTopMenu("/{$seasonLink}/quiniela/", 'Quiniela', '/quiniela/');

        $this->addToTopMenu('/normas/', 'Normas', '/normas/');

        if (Team::isAdmin()) {
            $this->addToTopMenu('/unete/', '¡Únete!', '/unete/');
        }

        /*
        if (Team::isMember()) {
            // $this->addToTopMenu('/votaciones/', 'Votaciones', '/votaciones/.*');
        }

        if (Team::isSuperAdmin()) {
            // $this->addToTopMenu('/admin/', 'Admin', '/admin/.*');
            // $this->addToTopMenu('/comunicados/', 'Comunicados', '/comunicados/.*');
        }
        */

        $this->addJavaScript('/js/lce.js', true);
        $this->addStyleSheet("
            html {
                background: url(/".$this->season->getBackgroundLink().") no-repeat center center fixed;
                -webkit-background-size: cover;
                -moz-background-size: cover;
                -o-background-size: cover;
                background-size: cover;
            }
        ", false);
        ob_start();
        $this->section->show();
        $this->contents = ob_get_contents();
        ob_end_clean();
    }


    public function addToTopMenu($link, $label, $re)
    {
        $this->topMenu[] = array($link, $label, $re);
    }

    public function setSeason(Season $season) {
        $this->season = $season;
        $_SESSION['seasonid'] = $season->seasonid;
    }

    public function showBody()
    {
        ?>
        <div id="main" class="public">
            <div class="content">
                <div class="banner"></div>
                <div class="nav" style="text-align: left">
                    <?php
                    foreach ($this->topMenu as $arr) {
                        list($link, $label, $re) = $arr;
                        $status = '';
                        if (preg_match("'^$re$'", HTMLResponse::getRoute())) {
                            $status = 'selected';
                        }

                        ?><a href="<?= $link ?>" class="<?= $status ?>"><?= $label ?></a><?php }
                    ?>
                    <div style="float: right">
                        <select name="season" id="navSeason">
                            <?php
                            $seasons = Team::isSuperAdmin() ?
                                Season::find('1=1') :
                                Season::find('ispublic');
                            foreach($seasons as $season) {
                                $selected = $season->seasonid == $this->season->seasonid ? 'selected' : '';
                                ?>
                                <option value="<?=$season->getLink()?>" <?=$selected?>>
                                    <?=htmlentities($season->name)?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div style="padding: 6px">
                    <?php if (TwitterAuth::isLogged()) { ?>
                        Estás identificado como <?=htmlentities(TwitterAuth::getUserName())?>. <a href="<?=HTMLResponse::getRoute()?>?logout=1">Cerrar sesión</a>
                        <?php if (Team::isMember()) {
                            $usersTeam = Team::getUsersTeam();
                            ?>/ <a href="/votaciones/">Votaciones</a>
                            <?php if ($usersTeam) { ?>/ <a href="/admin/equipos/<?=$usersTeam[0]->teamid?>/">Editar equipo</a><?php } ?>
                        <?php } ?>
                        <?php if (Team::isSuperAdmin()) {
                            ?>/ <a href="/admin/">Panel de admin</a><?php
                        } ?>
                        <br>
                        <?php
                        if (TwitterAuth::isBot()) {
                            $botConfig = TwitterAuth::getBotConfig();
                            ?>
                            <br>Eres la cuenta oficial de la LCE, haz <a href="/?authenticatebot=1">click aquí</a> para autorizar esta web a usarte como un bot.<br>
                            <b>Última autorización:</b> <?= isset($botConfig['dateline'])
                                ? date('Y/m/d H:i:s', $botConfig['dateline'])
                                : 'Nunca' ?>
                            <?php
                        }
                        ?>
                    <?php } else { ?>
                        No estás identificado. <a href="<?=HTMLResponse::getRoute()?>?authenticate=1">Iniciar sesión</a><br>
                    <?php } ?>
                </div>

                <div class="title">
                    <?= $this->section->getSubtitle() ?>
                </div>

                <?=$this->contents?>
            </div>
        </div>
        <?php
    }
}