<?php

/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 30/10/2015
 * Time: 20:04
 */
class Bet extends Model
{
    public $betid;
    public $matchid;
    public $teamid;
    public $dateline;
    public $userid;
    public $username;
    public $avatar;
}
Bet::init('bets', 'betid');