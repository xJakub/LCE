<?php

/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 27/01/2016
 * Time: 18:25
 */
class Application extends Model
{
    public $applicationid;
    public $userid;
    public $username;
    public $url;
    public $captureboard;
    public $frequency;
    public $reason;
    public $contributions;
    public $dateline;
    public $avatar;
    public $region;

    /**
     * @param null $userid
     * @return static
     */
    public static function exists($userid = null) {
        if ($userid === null) {
            if (!TwitterAuth::isLogged()) {
                return null;
            }
            else {
                $userid = TwitterAuth::getUserId();
            }
        }
        return Application::findOne('userid = ? and dateline >= ?', [$userid, mktime(0, 0, 0, 1, 1, 2017)]);
    }
    
    public static function getAll() {
        return self::find("dateline >= ? order by dateline desc", [mktime(0, 0, 0, 1, 1, 2017)]);
    }

    public function getVotes() {
        return ApplicationVote::find('applicationid = ?', [$this->applicationid]);
    }

    public function getScore() {
        $votes = $this->getVotes();
        return array_sum(Model::pluck($votes, 'vote'));
    }
}
Application::init('applications', 'applicationid');