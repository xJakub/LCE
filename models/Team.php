<?php

/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 29/10/2015
 * Time: 11:29
 */
class Team extends Model {
    public $teamid;
    public $name;
    public $username;

    /**
     * @param $link
     * @return \___PHPSTORM_HELPERS\static
     */
    public static function fromLink($link)
    {
        foreach(Team::find('1=1') as $team) {
            if ($team->getLink() == $link) {
                return $team;
            }
        }
    }

    function getLink() {
        return HTMLResponse::toLink($this->name);
    }

    function getHashtag() {
        return str_replace(" ", "", ucwords(str_replace("-", " ", $this->getLink())));
    }

    function isManager($username = null) {
        if ($username === null) {
            $username = TwitterAuth::getUserName();
        }

        return (
            strtolower($this->username) === strtolower($username)
            || strtolower($username) === 'xjakub'
        );
    }
}

Team::init('teams', 'teamid');