<?php

/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 07/04/2016
 * Time: 20:17
 */
class Sanction extends Model
{
    public $sanctionid;
    public $dateline;
    public $teamid;
    public $adminid;
    public $adminname;
    public $reason;
    public $seasonid;
    public $level;

    static function getLevelNames() {
        return [
            "Leve",
            "Grave",
            "Nula"
        ];
    }
}
Sanction::init('sanctions', 'sanctionid');