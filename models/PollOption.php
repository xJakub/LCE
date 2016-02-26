<?php

/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 16/02/2016
 * Time: 21:52
 */
class PollOption extends Model
{
    public $polloptionid;
    public $pollid;
    public $title;
    public $description;
    public $dateline;
    public $userid;
    public $username;

    function getHash() {
        if (!isset($_SESSION['csrf'])) {
            $_SESSION['csrf'] = rand(1, 1000000000);
        }
        return md5($_SESSION['csrf'] . $this->polloptionid);
    }
}
PollOption::init('polloptions', 'polloptionid');