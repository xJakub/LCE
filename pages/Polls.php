<?php

/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 16/02/2016
 * Time: 20:55
 */
class Polls implements PublicSection
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
        return "Lista de votaciones";
    }

    /**
     * @return void
     */
    public function show()
    {
        if (!TwitterAuth::isLogged()) {
            ?>
            Sólo los miembros pueden ver esta página.
            <a href="<?=HTMLResponse::getRoute()?>?authenticate=1">
                Inicia sesión.
            </a><br>
            <?php
            return;
        }
        else if (!Team::isMember()) {
            ?>
            Sólo los miembros pueden ver esta página.<br>
            <?php
            return;
        }
        else {
            ?><div style="text-align: left; margin: 0 auto" class="inblock"><?php
            if (Team::isMember()) {
                ?><ul><?php
                foreach(Poll::find('isvisible order by dateline desc') as $poll) {
                    ?>
                    <li>
                        <a href="/votaciones/<?=$poll->pollid?>/">
                            <?=htmlentities($poll->title)?>
                        </a>
                    </li>
                    <?php
                }
                ?></ul><?php

                ?>
                <a href="/votaciones/crear/">
                    Haz click aquí para añadir una nueva votación.
                </a>
                <?php
            }
            ?></div><br><br><?php

        }
    }
}