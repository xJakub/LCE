<?php

/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 29/10/2015
 * Time: 11:31
 */
class Season_Index implements PublicSection {

    public function __construct($seasonId, $requestedWeek = null) {

        $this->season = Season::getByLink($seasonId);
        $this->week = 1;
        $time = time();

        while($this->week < $this->season->getWeeksCount()
            && $this->season->weekIsPublished($this->week)) {
            $this->week++;
        }

        $maxWeekMatch = Match::findOne('seasonid = ? order by week desc limit 1', [$this->season->seasonid]);
        $this->maxWeek = $maxWeekMatch->week;

        if (!Team::isMember()) {
            while (!$this->season->weekIsPublic($this->maxWeek)) {
                $this->maxWeek--;
            }
        }

        if ($requestedWeek && $requestedWeek <= $this->maxWeek) {
            $this->week = $requestedWeek;
        }
        else if ($requestedWeek) {
            HTMLResponse::exitWithRoute("/");
        }

        $this->canVote = ($this->season->getPublishTimeForWeek($this->week) - $time > 3600);
    }

    public function setDesign(PublicDesign $response)
    {
        $response->setSeason($this->season);
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

        $matches = Match::find('seasonid = ? and week = ? order by matchid asc', [$this->season->seasonid, $this->week]);

        if (!$matches) {
            return $this->season->name;
        }

        return "Enfrentamientos de ".strtolower($this->season->getWeekName($this->week));
    }

    /**
     * @return void
     */
    public function show()
    {
        $week = $this->week;
        $canVote = $this->canVote;

        $publishTime = $this->season->getPublishTimeForWeek($week);

        if ($publishTime >= 1000) {

            $days = explode(',', 'Domingo,Lunes,Martes,Miércoles,Jueves,Viernes,Sábado');
            $months = explode(',', 'enero,febrero,marzo,abril,mayo,junio,julio,agosto,septiembre,octubre,noviembre,diciembre')

            ?>
            <div>
                <?= $days[date('w', $publishTime)] ?>
                <?= date('j', $publishTime)?> de <?= $months[date('m', $publishTime)-1] ?> de <?= date('Y', $publishTime) ?>,
                <?= date('H:i', $publishTime) ?> (hora española)
            </div>
        <? } ?>
        <div style="height: 6px"></div>
        <table style="border: 0; padding: 0; margin: 0; width: 100%">
            <tr>
                <td style="width: 150px; border: 0; padding: 0; margin: 0; text-align: left">
                    <?
                    if ($this->week > 1) {
                        ?>
                        <a style="float:left; margin-left: 24px" href="/<?=$this->season->getLink()?>/jornadas/<?=$this->week-1?>/">
                            &lt;&lt;
                            Ver <?= strtolower($this->season->getWeekName($this->week-1)) ?>
                        </a>
                        <?
                    }
                    ?>
                </td>
                <td style="border: 0; padding: 0; margin: 0; text-align: center">
                    <?
                    if (time() >= $publishTime) {
                        ?>
                        <a href="javascript:void(0)" onclick="$('._grayscale').toggleClass('grayscale'); $('.result').toggle(); $(this).find('span').toggle()">
                            <span>Mostrar resultados</span>
                            <span style="display: none">Ocultar resultados</span>
                        </a>
                        <?
                    }
                    ?>
                </td>
                <td style="width: 150px; border: 0; padding: 0; margin: 0; text-align: right"><?
                    if ($this->week < $this->maxWeek) {
                        ?>
                        <a style="float:right; margin-right: 24px" href="/<?=$this->season->getLink()?>/jornadas/<?=$this->week+1?>/">
                            Ver <?= strtolower($this->season->getWeekName($this->week+1)) ?>
                            &gt;&gt;
                        </a>
                        <?
                    }
                    ?>
                </td>
            </tr>
        </table>
        <?

        $matches = Match::find('seasonid = ? and week = ? order by matchid asc', [$this->season->seasonid, $week]);
        #shuffle($matches);

        if (!$matches) {
            ?>
            No hay enfrentamientos disponibles en estos momentos.<br><br>
            <?
            return;
        }

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

            if ($canVote && TwitterAuth::isLogged() && !$match->hasVoted() && $match->matchid &&
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
                    <? if ($match->isPublished() && $match->getWinner()) {
                        $score1 = $team1->teamid==$match->getWinner() ? 6-$match->getLooserKills() : 0;
                        $score2 = $team2->teamid==$match->getWinner() ? 6-$match->getLooserKills() : 0;
                        ?>
                        <div style="font-size:90%; display: none" class="result">
                            <?=$score1?>-<?=$score2?>
                        </div>
                        <?
                    } else if ($match->isPublished() && $match->isDelayed()) { ?>
                        <div style="font-size:90%; display: none" class="result">
                            Apl.
                        </div>
                    <? } ?>
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
        $video = Video::findOne('matchid = ? and teamid = ? and type = ?',
            [$match->matchid, $team->teamid, 1]);

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
                    <? if (!$match->isPublished() || !$match->getWinner()) { ?>
                        <? if ($match->hasVoted() == $team->teamid) { ?>
                            <span class="login">Has votado por este equipo</span>
                        <? } ?>
                    <? } else if ($video) { ?>
                        <a class="login" href="<?=htmlentities($video->link)?>" target="_blank">
                            Ver combate
                        </a>
                    <? } ?>
                <? } else if (!$match->isPublished()) { ?>
                    <a href="<?= HTMLResponse::getRoute() ?>?authenticate=1" class="login">&iexcl;Entra para ver tus votos!</a>
                <? } else if ($video) { ?>
                    <a class="login" href="<?=htmlentities($video->link)?>" target="_blank">
                        Ver combate
                    </a>
                <? } ?>
            <? } ?>
            <a href="/<?=$this->season->getLink()?>/equipos/<?=$team->getLink()?>/"><img class="<?=$isGray?'_grayscale':''?>" src="/<?=$team->getImageLink(200, 150)?>"></a>
        </div>
        <?
    }
}