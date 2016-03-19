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
    public $mainweeks;
    public $playoffsweeks;
    public $weeknames;
    public $weekdates;

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

    function getWeekName($week) {
        if (!strlen($this->weeknames)) $this->weeknames = '[]';
        $weeknames = json_decode($this->weeknames, true);
        return isset($weeknames[$week]) ? $weeknames[$week] : "Jornada {$week}";
    }

    function setWeekName($week, $name) {
        if (!strlen($this->weeknames)) $this->weeknames = '[]';
        $weeknames = json_decode($this->weeknames, true);
        $weeknames[$week] = $name;
        $this->weeknames = json_encode($weeknames);
    }

    function getWeekDate($week) {
        if (!strlen($this->weekdates)) $this->weekdates = '[]';
        $weekdates = json_decode($this->weekdates, true);
        return isset($weekdates[$week]) ? $weekdates[$week] : "2099-12-31";
    }

    function setWeekDate($week, $date) {
        if (!strlen($this->weekdates)) $this->weekdates = '[]';
        $weekdates = json_decode($this->weekdates, true);
        $weekdates[$week] = $date;
        $this->weekdates = json_encode($weekdates);
    }

    function getWeeksCount() {
        return $this->mainweeks + $this->playoffsweeks;
    }

    function getPublishDateForWeek($week) {
        $str = $this->getWeekDate($week);
        $date = mktime(17, 0, 0, 12, 31, 2099);

        if (preg_match("'^([0-9]{4})\\-([0-9]{2})\\-([0-9]{2})$'", $str, $match)) {
            $date = mktime(17, 0, 0, $match[2]*1, $match[3]*1, $match[1]*1);
        }

        return $date;
    }

    function weekIsPublished($week) {
        $date = $this->getPublishDateForWeek($week);
        return ($date >= 86400 && time() >= $date);
    }
}
Season::init('seasons', 'seasonid');
