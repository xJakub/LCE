<?php

/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 30/10/2015
 * Time: 20:04
 */
class Video extends Model
{
    public $videoid;
    public $teamid;
    public $link;
    public $dateline;
    public $publishdate;
    public $publishtime;
    public $matchid;
    public $type;

}
Video::init('videos', 'videoid');