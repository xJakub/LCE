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
        return Application::findOne('userid = ?', [$userid]);
    }
}
Application::init('applications', 'applicationid');