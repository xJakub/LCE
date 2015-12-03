<?php

/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 27/11/2015
 * Time: 23:07
 */
class Player extends Model
{
    public $playerid;
    public $teamid;
    public $number;
    public $name;

    public static function getAvailable()
    {
        static $result = null;
        if ($result === null) {
            $result = [];
            foreach (glob("img/sprites/*.png") as $file) {
                $result[] = substr(utf8_encode(basename($file)), 0, -4);
            }
        }
        return $result;
    }
}
Player::init('players', 'playerid');