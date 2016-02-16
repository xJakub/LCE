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

    static function getPublishDateForWeek($week) {
        $delay = 0;
        if ($week >= 9) $delay += 2;
        if ($week >= 12) $delay += 1;
        return mktime(17, 00, 00, 11, 1 + 7 * ($week-1+$delay), 2015);
    }

    function getPublishDate() {
        return self::getPublishDateForWeek($this->week);
    }

    function isPublished() {
        return (time() >= $this->getPublishDate());
    }

    function getWinner() {
        if ($this->result >= 1 && $this->result <= 7) {
            return $this->team1id;
        }
        elseif ($this->result >= 8 && $this->result <= 14) {
            return $this->team2id;
        }
        else {
            return null;
        }
    }

    function getLooser() {
        if ($this->result >= 1 && $this->result <= 7) {
            return $this->team2id;
        }
        elseif ($this->result >= 8 && $this->result <= 14) {
            return $this->team1id;
        }
        else {
            return null;
        }
    }

    function getLooserKills() {
        if ($this->result >= 1 && $this->result <= 7) {
            return $this->result-1;
        }
        elseif ($this->result >= 8 && $this->result <= 14) {
            return 6-($this->result-8); // 6-(x-8) = 6-x+8 = 14-x
        }
        else {
            return null;
        }
    }

    static function getPlayoffsWeek($week) {
        $playersCount = Team::getCount();
        if ($week >= $playersCount) {
            return $week - $playersCount + 1;
        }
        else {
            return 0;
        }
    }

    static function getWeekName($week) {
        $playoffsWeek = Match::getPlayoffsWeek($week);
        $playoffsNames = [null, "Semifinales", "Final"];

        if ($playoffsWeek) {
            return $playoffsNames[$playoffsWeek-1]
                ? $playoffsNames[$playoffsWeek-1] : "Playoffs {$playoffsWeek}";
        }
        else {
            return "Jornada {$week}";
        }
    }
}
Match::init('matches', 'matchid');