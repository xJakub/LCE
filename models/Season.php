<?php

/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 09/03/2016
 * Time: 21:07
 */
class Season extends Model
{
    public $seasonid;
    public $name;
    public $isdefault;
    public $ispublic;

    /**
     * @param $link
     * @return Season
     */
    public static function getByLink($link)
    {
        foreach(Season::find('1=1') as $season) {
            if (($season->ispublic || Team::isAdmin()) && $season->getLink() == $link) {
                return $season;
            }
        }
    }

    function getLink() {
        return HTMLResponse::toLink($this->name);
    }

    /**
     * @param bool|true $ispublic
     * @return Team[]
     */
    function getTeams($ispublic = true) {
        if (!$ispublic) {
            $teamIds = Model::pluck(SeasonTeam::find('seasonid = ?', [$this->seasonid]), 'teamid');
        }
        else {
            $teamIds = Model::pluck(SeasonTeam::find('seasonid = ? and ispublic', [$this->seasonid]), 'teamid');
        }
        if (!$teamIds) return [];
        return Model::orderBy(Team::getMultiple($teamIds), 'name');
    }
}
Season::init('seasons', 'seasonid');
