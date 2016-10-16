<?php

/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 08/11/2015
 * Time: 19:28
 */
class Ranking implements PublicSection
{
    public function __construct($seasonLink) {
        $this->season = Season::getByLink($seasonLink);
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
        return "LCE Pokémon";
    }

    /**
     * @return String
     */
    public function getSubtitle()
    {
        return "Clasificación";
    }

    /**
     * @return void
     */
    public function show()
    {
        $matches = Match::find('seasonid = ?', [$this->season->seasonid]);
        $wins = [];
        $games = [];
        $kills = [];
        $deaths = [];
        $mainPositions = [];
        $playoffsDefeat = [];
        $playoffsLast = [];

        foreach($this->season->getTeams() as $team) {
            $wins[$team->teamid] = 0;
            $kills[$team->teamid] = 0;
            $games[$team->teamid] = 0;
            $deaths[$team->teamid] = 0;
        }

        foreach($matches as $match) {
            if ($match->week > $this->season->mainweeks) continue;
            if (!$this->season->weekIsPublished($match->week)) continue;
            if ($match->isDelayed()) continue;

            $winner = $match->getWinner();
            if (!$winner) continue;
            $looser = $match->getLooser();

            $games[$winner]++;
            $games[$looser]++;

            $wins[$winner]++;

            $kills[$winner] += 6;
            $deaths[$looser] += 6;

            $looserKills = $match->getLooserKills();
            $kills[$looser] += $looserKills;
            $deaths[$winner] += $looserKills;
        }

        foreach(array_keys($kills) as $teamid) {
            $tiebreakers[$teamid] = array($wins[$teamid], $kills[$teamid]-$deaths[$teamid], $kills[$teamid], -$deaths[$teamid]);
        }
        arsort($tiebreakers);

        $lastPos = 0;
        $lastTiebreakers = null;
        foreach(array_keys($tiebreakers) as $pos => $teamid) {
            if ($lastTiebreakers != $tiebreakers[$teamid]) {
                $lastPos = $pos;
            }
            $mainPositions[$teamid] = $lastPos;
            $lastTiebreakers = $tiebreakers[$teamid];
        }

        foreach($matches as $match) {
            if ($match->week <= $this->season->mainweeks) continue;
            if (!$this->season->weekIsPublished($match->week)) continue;
            if ($match->isDelayed()) continue;

            $winner = $match->getWinner();
            if (!$winner) continue;
            $looser = $match->getLooser();

            $playoffsDefeat[$looser] = -$match->week;
            $playoffsLast[$winner] = $match->week;
            $playoffsLast[$looser] = $match->week;

            $playoffsPlayed[$winner] = 1;
            $playoffsPlayed[$looser] = 1;

            $games[$winner]++;
            $games[$looser]++;

            $wins[$winner]++;

            $kills[$winner] += 6;
            $deaths[$looser] += 6;

            $looserKills = $match->getLooserKills();
            $kills[$looser] += $looserKills;
            $deaths[$winner] += $looserKills;
        }

        foreach(array_keys($kills) as $teamid) {
            $tiebreakers[$teamid] = array($playoffsLast[$teamid]*1, $playoffsDefeat[$teamid]*1, -$mainPositions[$teamid]);
        }
        arsort($tiebreakers);


        ?>
        <table>
            <thead><tr>
                <td>Puesto</td>
                <td>Equipo</td>
                <td>Combates</td>
                <td>Victorias</td>
                <td>Derrotas</td>
                <td>Debilitados</td>
                <td>Perdidos</td>
            </tr></thead>

            <?
            /**
             * @var $teams Team[]
             */
            $teams = Model::indexBy(Team::find('1=1'), 'teamid');

            $lastTiebreakers = null;
            $lastPos = 0;
            foreach(array_keys($tiebreakers) as $pos => $teamid) {
                $team = $teams[$teamid];
                if ($lastTiebreakers != $tiebreakers[$teamid]) {
                    $lastPos = $pos;
                }

                ?>
                <tr>
                    <td><?= $lastPos+1 ?>º
                        <? if (isset($mainPositions[$teamid]) && $lastPos != $mainPositions[$teamid]) { ?>
                            <div style="height: 4px"></div>
                            <i title="<?= $mainPositions[$teamid]+1 ?>º en las jornadas" style="cursor: pointer; color: #666">
                                <?= $mainPositions[$teamid]+1 ?>º
                            </i>
                        <? } ?>
                    </td>
                    <td style="text-align: left">
                        <div class="teamimg64">
                            <img src="/<?=$team->getImageLink(64, 64)?>">
                        </div>
                        <a href="/<?=$this->season->getLink()?>/equipos/<?=$team->getLink()?>/" class="inblock" style="vertical-align:middle">
                            <?= $team->name ?>
                        </a></td>
                    <td><?= $games[$teamid] ?></td>
                    <td><b><?= $wins[$teamid]*1 ?></b></td>
                    <td><?= $games[$teamid]-$wins[$teamid] ?></td>
                    <td><?= $kills[$teamid] ?></td>
                    <td><?= $deaths[$teamid] ?></td>
                </tr>
                <?
                $lastTiebreakers = $tiebreakers[$teamid];
            }
            ?>
        </table>
        <?
    }
}