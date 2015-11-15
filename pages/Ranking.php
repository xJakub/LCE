<?php

/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 08/11/2015
 * Time: 19:28
 */
class Ranking implements PublicSection
{

    public function setDesign(PublicDesign $response)
    {
        // TODO: Implement setDesign() method.
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
        $matches = Match::find('1=1');
        $wins = [];
        $games = [];
        $kills = [];
        $deaths = [];

        foreach($matches as $match) {
            if (!$match->isPublished()) continue;

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
            $tiebreakers[$teamid] = array($wins[$teamid], -$deaths[$teamid], $kills[$teamid], -$deaths[$teamid]);
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

            foreach(array_keys($tiebreakers) as $pos => $teamid) {
                $team = $teams[$teamid];

                ?>
                <tr>
                    <td><?= $pos+1 ?>º</td>
                    <td style="text-align: left">
                        <div class="teamimg64">
                            <img src="/<?=$team->getImageLink(64, 64)?>">
                        </div>
                        <a href="/equipos/<?=$team->getLink()?>/" class="inblock" style="vertical-align:middle">
                            <?= $team->name ?>
                    </a></td>
                    <td><?= $games[$teamid] ?></td>
                    <td><b><?= $wins[$teamid]*1 ?></b></td>
                    <td><?= $games[$teamid]-$wins[$teamid] ?></td>
                    <td><?= $kills[$teamid] ?></td>
                    <td><?= $deaths[$teamid] ?></td>
                </tr>
                <?
            }
            ?>
        </table>
        <?
    }
}