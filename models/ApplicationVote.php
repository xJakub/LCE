<?php

/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 04/02/2016
 * Time: 22:25
 */
class ApplicationVote extends Model
{
    public $applicationvoteid;
    public $applicationid;
    public $userid;
    public $username;
    public $avatar;
    public $dateline;
    public $vote;

    public static function getUserVotes($userid = null) {
        if ($userid === null) {
            $userid = TwitterAuth::getUserId();
        }
        return ApplicationVote::find('userid = ?', [$userid]);
    }
}
ApplicationVote::init('applicationvotes', 'applicationvoteid');