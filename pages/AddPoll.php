<?php

/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 16/02/2016
 * Time: 20:55
 */
class AddPoll implements PublicSection
{

    public function setDesign(PublicDesign $response)
    {
        // TODO: Implement setDesign() method.
    }

    /**
     * @return String
     */
    public function getTitle()
    {
        return "Votaciones LCE";
    }

    /**
     * @return String
     */
    public function getSubtitle()
    {
        return "Crear votación";
    }

    /**
     * @return void
     */
    public function show()
    {
        if (!TwitterAuth::isLogged()) {
            ?>
            Sólo los administradores pueden ver esta página.
            <a href="<?=HTMLResponse::getRoute()?>?authenticate=1">
                Inicia sesión.
            </a><br>
            <?php
            return;
        }
        else if (!Team::isAdmin()) {
            ?>
            Sólo los administradores pueden ver esta página.<br>
            <?php
            return;
        }
        else {
            $title = trim(HTMLResponse::fromPOST('title', ''));
            $description = trim(HTMLResponse::fromPOST('description', ''));
            $options = [];
            for ($i=1; $i<6; $i++) {
                $value = trim(HTMLResponse::fromPOST('option'.$i, ''));
                if (strlen($value)) {
                    $options[] = $value;
                }
            }

            if (strlen($title) && count($options)>=2) {
                $poll = Poll::create();
                $poll->title = $title;
                $poll->description = $description;
                $poll->isvisible = true;
                $poll->isopen = true;
                $poll->username = TwitterAuth::getUserName();
                $poll->userid = TwitterAuth::getUserId();
                $poll->dateline = time();
                $poll->avatar = TwitterAuth::getAvatar();
                $poll->save();

                foreach($options as $index => $option) {
                    $pollOption = PollOption::create();
                    $pollOption->pollid = $poll->pollid;
                    $pollOption->userid = TwitterAuth::getUserId();
                    $pollOption->username = TwitterAuth::getUserName();
                    $pollOption->title = $option;
                    $pollOption->save();
                }

                HTMLResponse::exitWithRoute("/votaciones/{$poll->pollid}/");
            }

            ?>
            <form action="<?=HTMLResponse::getRoute()?>" method="post">
                <div style="padding:3px">
                    <div class="inblock middle" style="width:120px">
                        Título
                    </div>
                    <input name="title" value="<?=htmlentities($title)?>">
                </div>

                <div style="padding:3px">
                    <div class="inblock middle" style="width:120px">
                        Descripción
                    </div>
                    <input name="description" value="<?=htmlentities($description)?>">
                </div>

                <?php
                for ($i=1; $i<=6; $i++) {
                    ?>
                    <div style="padding:3px">
                        <div class="inblock middle" style="width:120px">
                            Opción <?=$i?>
                        </div>
                        <input name="option<?=$i?>" value="<?=htmlentities($options[$i-1])?>">
                    </div>
                    <?php
                }
                ?>

                <div style="padding:3px">
                    <div class="inblock middle" style="width:120px">

                    </div>
                    <input type="submit" value="Crear votación">
                </div>
            </form>
            <?php


        }
    }
}