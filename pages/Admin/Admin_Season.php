<?php

/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 11/03/2016
 * Time: 17:51
 */
class Admin_Season implements PublicSection
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
        return "#{$this->season->seasonid} - {$this->season->name}";
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

        if ($postCsrf == $csrf) {
            $this->season->name = HTMLResponse::fromPOST('name', $this->season->name);
            $this->season->teamplayers = min(HTMLResponse::fromPOST('teamplayers', $this->season->teamplayers), 99);

            $playerNames = $this->season->getPlayerNames();
            for ($i=0; $i<$this->season->teamplayers; $i++) {
                $playerNames[$i] = HTMLResponse::fromPOST("player{$i}name", $playerNames[$i]."");
            }
            $this->season->setPlayerNames($playerNames);

            $this->season->ispublic = !!HTMLResponse::fromPOST("ispublic", 0);

            $this->season->isdefault = 0;
            if (HTMLResponse::fromPOST("isdefault", 0)) {
                foreach(Season::find('isdefault') as $season) {
                    $season->isdefault = 0;
                    $season->save();
                }
                $this->season->isdefault = 1;
            }

            if ($_FILES['background']['tmp_name']) {
                $con = file_get_contents($_FILES['background']['tmp_name']);
                file_put_contents($this->season->getBackgroundLink(true), $con);
            }

            $this->season->save();
        }

        $teams = Team::find('1=1');

        ?><div class="inblock middle">
        <form enctype="multipart/form-data" action="<?=HTMLResponse::getRoute()?>" method="post">
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
                        <b>Nombre</b>
                    </td><td>
                        <input name="name" value="<?=htmlentities($this->season->name)?>">
                    </td>
                </tr>
                <tr>
                    <td>
                        <b>Opciones</b>
                    </td><td>
                        <input type="checkbox" name="ispublic" <?=$this->season->ispublic?'checked':''?>>
                        Visible<br>
                        <input type="checkbox" name="isdefault" <?=$this->season->isdefault?'checked':''?>>
                        Por defecto<br>
                    </td>
                </tr>
                <tr>
                    <td>
                        <b>Jugadores por equipo</b>
                    </td><td>
                        <input name="teamplayers" type="number" value="<?=htmlentities($this->season->teamplayers)?>">
                    </td>
                </tr>
                <tr>
                    <td>
                        <b>Nueva imagen de fondo</b>
                    </td><td>
                        <input name="background" type="file">
                    </td>
                </tr>
            </table>
            <input type="hidden" name="csrf" value="<?= $csrf ?>"><br><br>

            <table style="width:640px; margin: 0 auto">
                <thead>
                <tr>
                    <td colspan="3">Nombres de jugadores</td>
                </tr>
                </thead>
                <tr>
                    <?
                    $playerNames = $this->season->getPlayerNames();

                    for ($i=0; $i<$this->season->teamplayers; $i++) {
                    if ($i > 0 && ($i % 3) == 0) {
                    ?></tr><tr><?
                    }
                    ?><td style="padding: 6px">
                        <input name="player<?=$i?>name" placeholder="Jugador <?=$i+1?>" value="<?=htmlentities($playerNames[$i])?>">
                    </td><?
                    }
                    ?>
                </tr>
            </table><br><br>

            <table style="width:640px; margin: 0 auto; text-align: left">
                <thead>
                <tr>
                    <td>Logo</td>
                    <td>Equipo</td>
                    <td>Opciones</td>
                </tr>
                </thead>
                <?


                if ($csrf == $postCsrf) {
                    if ($newTeamId = HTMLResponse::fromPOST('newteamid', 0)*1) {
                        if (!SeasonTeam::findOne('seasonid = ? and teamid = ?', [$this->season->seasonid, $newTeamId*1])) {
                            $seasonTeam = SeasonTeam::create();
                            $seasonTeam->seasonid = $this->season->seasonid;
                            $seasonTeam->teamid = $newTeamId;
                            $seasonTeam->ispublic = (HTMLResponse::fromPOST("newispublic") ? '1' : '0');
                            $seasonTeam->save();
                        }
                    }
                }

                $teams = $this->season->getTeams(false);
                $teamsById = Model::indexBy($teams, 'teamid');

                /**
                 * @var $seasonTeams SeasonTeam[]
                 */
                $seasonTeams = Model::indexBy(SeasonTeam::find('seasonid = ?', [$this->season->seasonid]), 'teamid');

                foreach($teams as $team) {
                    $disabled = '';
                    $noname = $team->username == $team->name;

                    /*
                            if (Team::isSuperAdmin($team->username) && $team->username != TwitterAuth::getUserName()) {
                                $disabled = 'disabled';
                            }
                            else {
                    */
                    $seasonTeam = $seasonTeams[$team->teamid];

                    if ($csrf == $postCsrf) {
                        if (!HTMLResponse::fromPOST("remove{$team->teamid}")) {
                            $seasonTeam->ispublic = HTMLResponse::fromPOST("ispublic{$team->teamid}") ? '1' : '0';
                            $seasonTeam->save();
                        }
                        else {
                            $seasonTeam->delete();
                            continue;
                        }
                    }
                    /* } */
                    ?>
                    <tr>
                        <td style="text-align: center">
                            <img src="/<?=$team->getImageLink(40, 40)?>">
                        </td>
                        <td style="<?=$noname?'font-style:italic; color: #666':''?>">
                            <?=htmlentities($team->name)?> (@<?=$team->username?>)
                        </td>
                        <td>
                            <input type="checkbox" name="ispublic<?=$team->teamid?>" <?=$seasonTeam->ispublic?'checked':''?> <?=$disabled?>>
                            Visible |
                            <input type="checkbox" name="remove<?=$team->teamid?>"> Eliminar
                        </td>
                    </tr>
                    <?
                }
                ?>
                <tr>
                    <td style="text-align: center">
                        -
                    </td>
                    <td>
                        <select name="newteamid">
                            <option>-- Elige nuevo equipo --</option>
                            <?
                            foreach(Team::find('1=1 order by name asc') as $team) {
                                if ($teamsById[$team->teamid]) continue;
                                ?><option value="<?=$team->teamid?>">
                                <?=htmlentities($team->name)?> (@<?=$team->username?>)
                                </option><?
                            }
                            ?>
                        </select>
                    </td>
                    <td>
                        <input type="checkbox" name="newispublic"> Visible
                    </td>
                </tr>
            </table><br>
            <button type="submit">Guardar cambios</button><br><br>

        </form>
        </div><?
    }
}