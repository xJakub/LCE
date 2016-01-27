<?php

/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 29/10/2015
 * Time: 11:31
 */
class Index implements PublicSection {

    // public $week = 3;
    // public $canVote = true;

    public function __construct($requestedWeek = null) {

        $this->maxWeek = 1;
        $time = time();

        while($time >= Match::getPublishDateForWeek($this->maxWeek)) {
            $this->maxWeek++;
        }

        $maxWeekMatch = Match::findOne('1=1 order by week desc limit 1');
        $this->maxWeek = min($this->maxWeek, $maxWeekMatch->week);

        $this->week = $this->maxWeek;

        if ($requestedWeek && $requestedWeek < $this->week) {
            $this->week = $requestedWeek;
        }
        else if ($requestedWeek) {
            HTMLResponse::exitWithRoute("/");
        }

        $this->canVote = (Match::getPublishDateForWeek($this->week) - $time > 3600);
    }

    public function setDesign(PublicDesign $response)
    {
        // TODO: Implement setDesign() method.
    }

    /**
     * @return String
     */
    public function getTitle()
    {
        return "LCE Pokemon";
    }

    /**
     * @return String
     */
    public function getSubtitle()
    {
        return "Enfrentamientos de la jornada {$this->week}";
    }

    /**
     * @return void
     */
    public function show()
    {
        $week = $this->week;
        $canVote = $this->canVote;

        if ($this->maxWeek != 1) {
            ?>
            <div style="overflow: hidden">
                <?
                if ($this->week > 1) {
                    ?>
                    <a style="float:left; margin-left: 24px" href="/jornadas/<?=$this->week-1?>/">&lt;&lt; Ver jornada <?=$this->week-1?></a>
                    <?
                }
                if ($this->week < $this->maxWeek) {
                    ?>
                    <a style="float:right; margin-right: 24px" href="/jornadas/<?=$this->week+1?>/">Ver jornada <?=$this->week+1?> &gt;&gt;</a>
                    <?
                }
                ?>
            </div>
            <?
        }

        $matches = Match::find('week = ? order by matchid asc', [$week]);
        #shuffle($matches);

        foreach($matches as $match) {
            $team1 = $match->getTeam1();
            $team2 = $match->getTeam2();

            if (false && rand(0,1) == 1) {
                $tmp = $team2;
                $team2 = $team1;
                $team1 = $tmp;
            }


            $voteTeamid = HTMLResponse::fromPOST('teamid','') * 1;
            $voteMatchid = HTMLResponse::fromPOST('matchid','') * 1;
            $voteUnteamid = HTMLResponse::fromPOST('unteamid','') * 1;
            $voteUnmatchid = HTMLResponse::fromPOST('unmatchid','') * 1;

            if ($canVote && TwitterAuth::isLogged() && !$match->hasVoted() &&
                $voteMatchid == $match->matchid && ($voteTeamid == $team1->teamid || $voteTeamid == $team2->teamid)) {
                $bet = Bet::create();
                $bet->matchid = $match->matchid;
                $bet->dateline = time();
                $bet->userid = TwitterAuth::getUserId();
                $bet->teamid = $voteTeamid;
                $bet->username = TwitterAuth::getUserName();
                $bet->avatar = $_SESSION['twitter-avatar'];
                $bet->save();
            }

            if ($canVote && TwitterAuth::isLogged() && $match->hasVoted() &&
                $voteUnmatchid == $match->matchid && ($voteUnteamid == $team1->teamid || $voteUnteamid == $team2->teamid)) {
                $bet = Bet::findOne('matchid = ? and teamid = ? and userid = ?', array($voteUnmatchid, $voteUnteamid, TwitterAuth::getUserId()));
                $bet->delete();
            }

            $votes = $match->getVotes();
            $votesCount = array_sum($votes);

            $team1votes = 0;
            $team2votes = 0;
            if ($votesCount != 0) {
                $team1votes = $votes[$team1->teamid]*1;
                $team2votes = $votes[$team2->teamid]*1;
            }

            ?>

            <div class="matchbox">
                <? $this->showTeamBox($match, $team1, $team1votes, $votesCount) ?>
                <div class="vsbox">
                    VS
                </div>
                <? $this->showTeamBox($match, $team2, $team2votes, $votesCount) ?>
            </div>
            <?
        }
    }

    /**
     * @param $match Match
     * @param $team Team
     * @param $team1votes
     * @param $votesCount
     */
    private function showTeamBox($match, $team, $team1votes, $votesCount)
    {
        $canVote = $this->canVote;

        $team1per = 50;
        if ($votesCount != 0) {
            $team1per = round($team1votes / $votesCount * 100);
        }

        $team2 = ($match->team1id == $team->teamid) ? $match->getTeam2() : $match->getTeam1();
        $isGray = ($match->isPublished() && $match->getWinner() && $match->getWinner() != $team->teamid);

        ?>
        <div class="teambox">
            <div class="votecount"><?=$team1votes?> votos (<?=$team1per?>%)</div>
            <? if ($canVote) { ?>
                <? if (TwitterAuth::isLogged()) { ?>
                    <? if (!$match->hasVoted()) { ?>

                        <form method="post" action="<?=HTMLResponse::getRoute()?>">
                            <button type="submit" class="vote">Votar</button>
                            <input type="hidden" name="teamid" value="<?=$team->teamid?>">
                            <input type="hidden" name="matchid" value="<?=$match->matchid?>">
                        </form>
                    <? } else if ($match->hasVoted() == $team->teamid) { ?>
                        <form method="post" action="<?=HTMLResponse::getRoute()?>">
                            <div class="login">
                                <button type="submit">Quitar voto</button>
                                <a target="_blank" class="twitter-share-button"
                                   href="https://twitter.com/intent/tweet?text=<?=urlencode("¡He votado que #".$team->getHashtag()." ganará a #".$team2->getHashtag()." en la @LCE_Pokemon! http://lce.wz.tl")?>">
                                    ¡Twittear!</a>
                            </div>
                            <input type="hidden" name="unteamid" value="<?=$team->teamid?>">
                            <input type="hidden" name="unmatchid" value="<?=$match->matchid?>">
                        </form>
                    <? } ?>
                <? } else { ?>
                    <a href="<?= HTMLResponse::getRoute() ?>?authenticate=1" class="login">&iexcl;Usa Twitter para votar!</a>
                <? } ?>
            <? } else { ?>
                <? if (TwitterAuth::isLogged()) { ?>
                    <? if ($match->hasVoted() == $team->teamid) { ?>
                        <span class="login">Has votado por este equipo</span>
                    <? } ?>
                <? } else { ?>
                    <a href="<?= HTMLResponse::getRoute() ?>?authenticate=1" class="login">&iexcl;Entra para ver tus votos!</a>
                <? } ?>
            <? } ?>
            <a href="/equipos/<?=$team->getLink()?>/"><img class="<?=$isGray?'grayscale':''?>" src="/<?=$team->getImageLink(200, 150)?>"></a>
        </div>
        <?
    }
}