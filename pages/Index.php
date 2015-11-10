<?php

/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 29/10/2015
 * Time: 11:31
 */
class Index implements PublicSection {

    public $week = 3;
    public $canVote = true;

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
                $bet->save();
            }

            if ($canVote && TwitterAuth::isLogged() && $match->hasVoted() &&
                $voteUnmatchid == $match->matchid && ($voteUnteamid == $team1->teamid || $voteUnteamid == $team2->teamid)) {
                $bet = Bet::findOne('matchid = ? and teamid = ? and userid = ?', array($voteUnmatchid, $voteUnteamid, TwitterAuth::getUserId()));
                $bet->delete();
            }

            $votes = $match->getVotes();
            $votesCount = array_sum($votes);

            $team1per = 50;
            $team2per = 50;
            $team1votes = 0;
            $team2votes = 0;
            if ($votesCount != 0) {
                $team1votes = $votes[$team1->teamid]*1;
                $team2votes = $votes[$team2->teamid]*1;
                $team1per = round($team1votes / $votesCount * 100);
                $team2per = round($team2votes / $votesCount * 100);
            }

            ?>

            <div class="matchbox">
                <div class="teambox">
                    <div class="votecount"><?=$team1votes?> votos (<?=$team1per?>%)</div>
                    <? if ($canVote) { ?>
                        <? if (TwitterAuth::isLogged()) { ?>
                            <? if (!$match->hasVoted()) { ?>

                        <form method="post" action="<?=HTMLResponse::getRoute()?>">
                            <button type="submit" class="vote">Votar</button>
                            <input type="hidden" name="teamid" value="<?=$team1->teamid?>">
                            <input type="hidden" name="matchid" value="<?=$match->matchid?>">
                        </form>
                            <? } else if ($match->hasVoted() == $team1->teamid) { ?>
                                <form method="post" action="<?=HTMLResponse::getRoute()?>">
                                    <div class="login">
                                        <button type="submit">Quitar voto</button>
                                        <a target="_blank" class="twitter-share-button"
                                           href="https://twitter.com/intent/tweet?text=<?=urlencode("¡He votado que #".$team1->getHashtag()." ganará a #".$team2->getHashtag()." en la @LCE_Pokemon! http://lce.wz.tl")?>">
                                            ¡Twittear!</a>
                                    </div>
                                    <input type="hidden" name="unteamid" value="<?=$team1->teamid?>">
                                    <input type="hidden" name="unmatchid" value="<?=$match->matchid?>">
                                </form>
                            <? } ?>
                        <? } else { ?>
                            <a href="/authenticate/" class="login">&iexcl;Usa Twitter para votar!</a>
                        <? } ?>
                    <? } else { ?>
                        <? if (TwitterAuth::isLogged()) { ?>
                            <? if ($match->hasVoted() == $team1->teamid) { ?>
                                <span class="login">Has votado por este equipo</span>
                            <? } ?>
                        <? } else { ?>
                            <a href="/authenticate/" class="login">&iexcl;Entra para ver tus votos!</a>
                        <? } ?>
                    <? } ?>
                    <a href="/equipos/<?=$team1->getLink()?>/"><img src="/img/<?=$team1->getLink()?>.png"></a>
                </div>
                <div class="vsbox">
                    VS
                </div>
                <div class="teambox">
                    <div class="votecount"><?=$team2votes?> votos (<?=$team2per?>%)</div>

                    <? if ($canVote) { ?>
                        <? if (TwitterAuth::isLogged()) { ?>
                            <? if (!$match->hasVoted()) { ?>

                                <form method="post" action="<?=HTMLResponse::getRoute()?>">
                                    <button type="submit" class="vote">Votar</button>
                                    <input type="hidden" name="teamid" value="<?=$team2->teamid?>">
                                    <input type="hidden" name="matchid" value="<?=$match->matchid?>">
                                </form>
                            <? } else if ($match->hasVoted() == $team2->teamid) { ?>
                                <form method="post" action="<?=HTMLResponse::getRoute()?>">
                                    <div class="login">
                                        <button type="submit">Quitar voto</button>
                                        <a target="_blank" class="twitter-share-button"
                                           href="https://twitter.com/intent/tweet?text=<?=urlencode("¡He votado que #".$team2->getHashtag()." ganará a #".$team1->getHashtag()." en la @LCE_Pokemon! http://lce.wz.tl")?>">
                                            ¡Twittear!</a>
                                    </div>
                                    <input type="hidden" name="unteamid" value="<?=$team2->teamid?>">
                                    <input type="hidden" name="unmatchid" value="<?=$match->matchid?>">
                                </form>
                            <? } ?>
                        <? } else { ?>
                            <a href="/authenticate/" class="login">&iexcl;Usa Twitter para votar!</a>
                        <? } ?>
                    <? } else { ?>
                        <? if (TwitterAuth::isLogged()) { ?>
                            <? if ($match->hasVoted() == $team2->teamid) { ?>
                                <span class="login">Has votado por este equipo</span>
                            <? } ?>
                        <? } else { ?>
                            <a href="/authenticate/" class="login">&iexcl;Entra para ver tus votos!</a>
                        <? } ?>
                    <? } ?>
                    <a href="/equipos/<?=$team2->getLink()?>/"><img src="/img/<?=$team2->getLink()?>.png"></a>
                </div>
            </div>
            <?
        }
    }
}