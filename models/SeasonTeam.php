<?php

/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 11/03/2016
 * Time: 14:45
 */
class SeasonTeam extends Model
{
    public $seasonteamid;
    public $seasonid;
    public $teamid;
    public $ispublic;
}
SeasonTeam::init('seasonteams', 'seasonteamid');