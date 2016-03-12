<?php

/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 29/10/2015
 * Time: 11:31
 */
class Teams implements PublicSection {

    public function __construct($seasonLink) {
        $this->season = Season::getByLink($seasonLink);
    }

    public function setDesign(PublicDesign $response)
    {
        $response->setSeason($this->season);
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
        foreach($this->season->getTeams() as $team) {
            ?>
            <div class="teambox">
                <a class="login" href="/<?=$this->season->getLink()?>/equipos/<?=$team->getLink()?>/"><?=htmlentities($team->name)?></a>
                <a href="/<?=$this->season->getLink()?>/equipos/<?=$team->getLink()?>/"><img src="/<?=$team->getImageLink(200, 150)?>"></a>
            </div>
            <?
        }
        ?><br><br><?
    }
}