<?php

/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 16/02/2016
 * Time: 21:52
 */
class PollVote extends Model
{
    public $pollvoteid;
    public $pollid;
    public $polloptionid;
    public $dateline;
    public $userid;
    public $username;
    public $avatar;
}
PollVote::init('pollvotes', 'pollvoteid');