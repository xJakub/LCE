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


    public function __construct(PublicSection $section)
    {
        parent::__construct();

        $this->topMenu = array();

        $this->setMeta('charset', 'utf-8');
        $this->setMeta('viewport', 'width=device-width, initial-scale=1.0');
        $this->setTitle($section->getTitle() . ' - ' . $section->getSubtitle());

        $this->addStyleSheet('/style.css');

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

        $section->setDesign($this);
        $this->section = $section;

        $this->addToTopMenu('/', 'Enfrentamientos', '/');
        $this->addToTopMenu('/equipos/', 'Equipos', '/equipos/.*');
        $this->addToTopMenu('/clasificacion/', 'Clasificación', '/clasificacion/');
        $this->addToTopMenu('/quiniela/', 'Quiniela', '/quiniela/');

        $this->addJavaScript('/lce.js', true);
    }


    public function addToTopMenu($link, $label, $re)
    {
        $this->topMenu[] = array($link, $label, $re);
    }


    public function showBody()
    {
        ?>
        <div id="main" class="public">
            <div class="content">
                <div class="nav" style="text-align: left">
                    <?
                    foreach ($this->topMenu as $arr) {
                        list($link, $label, $re) = $arr;
                        $status = '';
                        if (preg_match("'^$re$'", HTMLResponse::getRoute())) {
                            $status = 'selected';
                        }

                        ?><a href="<?= $link ?>" class="<?= $status ?>"><?= $label ?></a><? }

                    ?></div>
                <? if (TwitterAuth::isLogged()) { ?>
                    Estás como <?=htmlentities(TwitterAuth::getUserName())?>. <a href="/logout/">Cerrar sesión</a><br>
                <? } ?>

                <div class="title">
                    <?= $this->section->getSubtitle() ?>
                </div>

                <?=$this->section->show()?>
            </div>
        </div>
        <?
    }
}