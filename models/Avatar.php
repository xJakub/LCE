<?php

/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 07/04/2016
 * Time: 3:04
 */
class Avatar extends Model
{
    public $avatarid;
    public $userid;
    public $username;
    public $url;
    public $dateline;

    static function setUsersAvatar($userid, $username, $url=null) {
        $avatar = Avatar::findOne('userid = ?', [$userid]);
        if (!$avatar) {
            $avatar = Avatar::create();
            $avatar->userid = $userid;
        }
        $avatar->username = $username;

        if ($url !== null) {
            $avatar->url = $url;
            $avatar->dateline = time();
        }
        else {
            $avatar->url = self::getDefault();
            $avatar->dateline = 0;
        }
        $avatar->save();
    }

    static function getUsersAvatar($userid, $username=null, $forceCreate=false) {
        $avatar = Avatar::findOne('userid = ?', [$userid]);
        if (!$avatar && $forceCreate) {
            self::setUsersAvatar($userid, $username);
            return self::getDefault();
        }
        return $avatar ? $avatar->url : self::getDefault();
    }

    static function getDefault() {
        return '/img/blank.png';
    }
}
Avatar::init('avatars', 'avatarid');