<?php

/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 27/11/2015
 * Time: 23:10
 */
class Trade extends Model
{
    public $tradeid;
    public $player1id;
    public $team1id;
    public $player2id;
    public $team2id;
    public $week;
}
Trade::init('trades', 'tradeid');