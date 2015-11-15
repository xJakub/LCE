<?php

/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 29/10/2015
 * Time: 11:31
 */
class Teams implements PublicSection {


    public function setDesign(PublicDesign $response)
    {
        // TODO: Implement setDesign() method.
    }

    /**
     * @return String
     */
    public function getTitle()
    {
        return "LCE Pokemon";
    }

    /**
     * @return String
     */
    public function getSubtitle()
    {
        return "Lista de equipos";
    }

    /**
     * @return void
     */
    public function show()
    {
        foreach(Team::find('1=1 order by teamid asc') as $team) {
            ?>
            <div class="teambox">
                <a class="login" href="/equipos/<?=$team->getLink()?>/"><?=htmlentities($team->name)?></a>
                <a href="/equipos/<?=$team->getLink()?>/"><img src="/<?=$team->getImageLink(200, 150)?>"></a>
            </div>
            <?
        }
        ?><br><br><?
    }
}