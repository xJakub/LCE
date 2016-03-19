<?php

/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 19/03/2016
 * Time: 17:29
 */
class Admin_Season_Weeks implements PublicSection
{
    public function __construct($seasonId) {
        $this->season = Season::get($seasonId);
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
        return "Jornadas de {$this->season->name}";
    }

    /**
     * @return void
     */
    public function show()
    {
        if (!Team::isSuperAdmin()) {
            HTMLResponse::exitWithRoute('/');
        }

        if (!($csrf = $_SESSION['csrf'])) {
            $_SESSION['csrf'] = $csrf = rand(1, 1000000);
        }
        $postCsrf = HTMLResponse::fromPOST('csrf', '');

        $totalWeeks = $this->season->getWeeksCount();
        if ($csrf == $postCsrf) {
            $this->season->mainweeks = HTMLResponse::fromPOST('mainweeks', $this->season->mainweeks);
            $this->season->playoffsweeks = HTMLResponse::fromPOST('playoffsweeks', $this->season->playoffsweeks);
            $this->season->save();
        }

        ?>
        <div class="inblock middle">
        <form action="<?=HTMLResponse::getRoute()?>" method="post">
            <table style="width:640px; margin: 0 auto; text-align: left">
                <thead>
                <tr style="text-align: center">
                    <td>
                        Propiedad
                    </td>
                    <td>
                        Valor
                    </td>
                </tr>
                </thead>
                <tr>
                    <td>
                        <b>Número de jornadas (principales)</b>
                    </td><td>
                        <input name="mainweeks" type="number" value="<?=htmlentities($this->season->mainweeks)?>">
                    </td>
                </tr>
                <tr>
                    <td>
                        <b>Número de jornadas (playoffs)</b>
                    </td><td>
                        <input name="playoffsweeks" type="number" value="<?=htmlentities($this->season->playoffsweeks)?>">
                    </td>
                </tr>
            </table>

            <?
            $teams = $this->season->getTeams(false);
            $maxMatches = ceil(count($teams)/2);

            $matches =
                Model::groupBy(
                    Match::find('seasonid = ? order by week asc, matchid asc', [$this->season->seasonid]),
                    'week'
                );

            if ($csrf == $postCsrf) {
                for ($week=1; $week<=$totalWeeks; $week++) {
                    $name = HTMLResponse::fromPOST("week{$week}name");
                    $date = HTMLResponse::fromPOST("week{$week}date");
                    $this->season->setWeekName($week, $name);
                    $this->season->setWeekDate($week, $date);
                }
                $this->season->save();

                $newMatches = [];
                $oldMatches = [];
                for ($week=1; $week<=$this->season->getWeeksCount(); $week++) {
                    for ($i=0; $i<$maxMatches; $i++) {
                        $team1id = HTMLResponse::fromPOST("week{$week}match{$i}team1id");
                        $team2id = HTMLResponse::fromPOST("week{$week}match{$i}team2id");
                        if ($team1id === null || $team2id === null) continue;

                        $team1id *= 1;
                        $team2id *= 1;

                        if ($team1id && $team2id) {
                            if (isset($matches[$week][$i])) {
                                $match = $matches[$week][$i];
                                $match->team1id = "$team1id";
                                $match->team2id = "$team2id";
                                $oldMatches[] = $match;
                            }
                            else {
                                $match = Match::create();
                                $match->result = 0;
                                $match->week = $week;
                                $match->seasonid = $this->season->seasonid;
                                $match->team1id = $team1id;
                                $match->team2id = $team2id;
                                $newMatches[] = $match;
                            }
                        }
                        else {
                            if (isset($matches[$week][$i])) {
                                $matches[$week][$i]->delete();
                            }
                        }
                    }
                }
                Model::saveAll($newMatches);
                Model::saveAll($oldMatches);

                $matches =
                    Model::groupBy(
                        Match::find('seasonid = ? order by week asc, matchid asc', [$this->season->seasonid]),
                        'week'
                    );
            }


            for ($week=1; $week<=$totalWeeks; $week++) {
                ?>
                <br>
                <table style="width:640px; margin: 0 auto; text-align: left">
                    <thead>
                    <tr>
                        <td colspan="2" style="text-align: center">Jornada Nº<?=$week?></td>
                    </tr>
                    </thead>
                    <tr>
                        <td>
                            <b>Nombre de la jornada</b>
                        </td><td>
                            <input name="week<?=$week?>name" value="<?=htmlentities($this->season->getWeekName($week))?>">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <b>Fecha de publicación</b>
                        </td><td>
                            <input name="week<?=$week?>date" type="date" value="<?=htmlentities($this->season->getWeekDate($week))?>">
                        </td>
                    </tr>
                    <?
                    for ($i=0; $i<$maxMatches; $i++) {
                        ?>
                        <tr>
                            <td>
                                <b>- Enfrentamiento #<?=$i+1?></b>
                            </td>
                            <td>
                                <select name="week<?=$week?>match<?=$i?>team1id">
                                    <option value="0">-- Elige equipo --</option>
                                    <?
                                    foreach($teams as $team) {
                                        $selected = isset($matches[$week][$i])
                                            ? (
                                            $matches[$week][$i]->team1id == $team->teamid
                                                ? 'selected'
                                                : ''
                                            )
                                            : '';
                                        ?>
                                        <option value="<?=$team->teamid?>" <?=$selected?>>
                                            <?=htmlentities($team->name)?>
                                        </option>
                                        <?
                                    }
                                    ?>
                                </select>
                                VS
                                <select name="week<?=$week?>match<?=$i?>team2id">
                                    <option value="0">-- Elige equipo --</option>
                                    <?
                                    foreach($teams as $team) {
                                        $selected = isset($matches[$week][$i])
                                            ? (
                                            $matches[$week][$i]->team2id == $team->teamid
                                                ? 'selected'
                                                : ''
                                            )
                                            : '';
                                        ?>
                                        <option value="<?=$team->teamid?>" <?=$selected?>>
                                            <?=htmlentities($team->name)?>
                                        </option>
                                        <?
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                        <?
                    }
                    ?>
                </table>
                <?
            }
            ?>

            <input type="hidden" name="csrf" value="<?= $csrf ?>"><br>
            <button type="submit">Guardar cambios</button><br><br>

        </form>
        </div><?
    }
}