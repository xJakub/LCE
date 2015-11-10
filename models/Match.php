<?php

/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 29/10/2015
 * Time: 12:07
 */
class Match extends Model
{
    public $matchid;
    public $team1id;
    public $team2id;
    public $week;
    public $result;

    /**
     * @return Team
     */
    function getTeam1() {
        return Team::get($this->team1id);
    }

    /**
     * @return Team
     */
    function getTeam2() {
        return Team::get($this->team2id);
    }

    function getVotes() {
        $bets = Bet::find('matchid = ?', array($this->matchid));
        $votes = array();
        foreach($bets as $bet) {
            $votes[$bet->teamid]++;
        }
        return $votes;
    }

    function hasVoted($userid = null) {
        if ($userid == null) {
            $userid = TwitterAuth::getUserId();
        }
        $bet = Bet::findOne('matchid = ? and userid = ?', array($this->matchid, $userid));
        if (!$bet) return false;
        else return $bet->teamid;
    }

    function getPublishDate() {
        return mktime(17, 00, 00, 11, 1 + 7 * ($this->week-1), 2015);
    }

    function isPublished() {
        return (time() >= $this->getPublishDate());
    }

    function getWinner() {
        if ($this->result >= 1 && $this->result <= 6) {
            return $this->team1id;
        }
        elseif ($this->result >= 7 && $this->result <= 12) {
            return $this->team2id;
        }
        else {
            return null;
        }
    }

    function getLooser() {
        if ($this->result >= 1 && $this->result <= 6) {
            return $this->team2id;
        }
        elseif ($this->result >= 7 && $this->result <= 12) {
            return $this->team1id;
        }
        else {
            return null;
        }
    }

    function getLooserKills() {
        if ($this->result >= 1 && $this->result <= 6) {
            return $this->result-1;
        }
        elseif ($this->result >= 7 && $this->result <= 12) {
            return 6-($this->result-6); // 6-(x-6) = 6-x+6 = 12-x
        }
        else {
            return null;
        }
    }
}
Match::init('matches', 'matchid');