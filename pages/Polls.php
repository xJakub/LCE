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
            <?
            return;
        }
        else if (!Team::isMember()) {
            ?>
            Sólo los miembros pueden ver esta página.<br>
            <?
            return;
        }
        else {
            ?><div style="text-align: left; margin: 0 auto" class="inblock"><?
            if (Team::isMember()) {
                ?><ul><?
                foreach(Poll::find('isvisible order by dateline desc') as $poll) {
                    ?>
                    <li>
                        <a href="/votaciones/<?=$poll->pollid?>/">
                            <?=htmlentities($poll->title)?>
                        </a>
                    </li>
                    <?
                }
                ?></ul><?

                ?>
                <a href="/votaciones/crear/">
                    Haz click aquí para añadir una nueva votación.
                </a>
                <?
            }
            ?></div><br><br><?

        }
    }
}