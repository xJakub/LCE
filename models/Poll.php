<?php

/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 16/02/2016
 * Time: 21:50
 */
class Poll extends Model
{
    public $pollid;
    public $title;
    public $description;
    public $isvisible;
    public $isopen;
    public $dateline;
    public $userid;
    public $username;
    public $avatar;
}
Poll::init('polls', 'pollid');