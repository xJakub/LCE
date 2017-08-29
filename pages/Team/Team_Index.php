<?php

/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 01/11/2015
 * Time: 20:49
 */
class Team_Index implements PublicSection
{

    /**
     * @var $design PublicDesign
     */
    private $design;

    public function __construct($seasonLink, $dir) {
        $this->season = Season::getByLink($seasonLink);

        $this->team = Team::fromLink($dir);

        if (!$this->team) HTMLResponse::exitWithRoute("/$seasonLink/equipos/");
        if (!Team::isSuperAdmin()
            && !SeasonTeam::findOne('seasonid = ? and teamid = ? and ispublic', [$this->season->seasonid, $this->team->teamid])
        ) HTMLResponse::exitWithRoute("/$seasonLink/equipos/");

        $this->tiers = $this->season->getPlayerNames();
    }

    public function setDesign(PublicDesign $response)
    {
        $this->design = $response;
        $this->design->setSeason($this->season);
    }

    /**
     * @return String
     */
    public function getTitle()
    {
        return "Información del equipo";
    }

    /**
     * @return String
     */
    public function getSubtitle()
    {
        return $this->team->name;
    }

    /**
     * @return void
     */
    public function show()
    {

        $resultNames = [
            ['Sin resultado', 'Sin resultado'],
        ];
        for ($i=6; $i>=0; $i--) {
            $resultNames[] = ["Victoria $i-0", "Derrota 0-$i"];
        }
        for ($i=0; $i<=6; $i++) {
            $resultNames[] = ["Derrota 0-$i", "Victoria $i-0"];
        }
        $resultNames[] = ["Victoria 6-0 (sin jugar)", "Derrota 0-6 (sin jugar)"];
        $resultNames[] = ["Derrota 0-6 (sin jugar)", "Victoria 6-0 (sin jugar)"];
        $resultNames[] = ["Aplazado", "Aplazado"];

        if (!($csrf = $_SESSION['csrf'])) {
            $_SESSION['csrf'] = $csrf = rand(1, 1000000) . "";
        }
        $postCsrf = HTMLResponse::fromPOST('csrf', '');

        if ($postCsrf == $csrf) {
            if (HTMLResponse::fromPOST('color') !== null) {
                $this->team->color = HTMLResponse::fromPOST('color');
                $this->team->save();
            }
        }
        $color = $this->team->color;

        ?>
        <div class="inblock" style="margin-right: 16px">
            <a target="_blank" href="/<?=$this->team->getImageLink()?>">
                <img src="/<?=$this->team->getImageLink(300, 200)?>" alt="Logo" class="teamlogo"><br>
            </a>
            <a href="https://twitter.com/hashtag/<?=$this->team->getHashtag()?>" target="_blank">#<?=$this->team->getHashtag()?></a>
            <div style="height:2px"></div>
            <a href="https://twitter.com/<?=$this->team->username?>" target="_blank">@<?=$this->team->username?></a>
            <div style="height: 6px"></div>

            <span style="text-decoration: underline;">Color oficial</span>: <?php
            if (preg_match("'^#[abcdefABCDEF0-9]{6}$'", $color)) {
                ?><span id="teamcolor"><?= $color ?></span><?php
            } else {
                ?><i id="teamcolor">Sin color</i><?php
                $color = '#000000';
            }
            ?>
            <div class="teamcolor" style="background: <?=$color?>"></div>

            <br><?php
            if ($this->team->isManager()) {
                ?>
                <br>Eres el Manager del equipo.

                <form action="<?=HTMLResponse::getRoute()?>" method="post" id="colorform">
                    <input type="hidden" name="color" value="<?=$color?>">
                    <input type="hidden" name="csrf" value="<?=$csrf?>">
                </form>
                <?php
                $this->design->addJavaScript('/js/jquery-ui.min.js');
                $this->design->addStyleSheet('/css/jquery-ui.min.css');
                $this->design->addStyleSheet('/css/jquery.colorpicker.css');
                $this->design->addJavaScript('/js/jquery.colorpicker.js');
                $this->design->addJavaScript("
                    $('.teamcolor').colorpicker({
                    inline: false,
                    color: '{$color}',
                    colorFormat: '#HEX',
                    closeOnOutside: false,
                    closeOnEscape: false,
                    ok: function(event, color) {
                        $('#colorform input[name=\"color\"]').val(color.formatted);
                        $('#colorform').submit();
                    }
                    }).css('cursor', 'pointer');
                    ", false);
            }
            ?>
        </div>
        <div class="inblock">
            <?php

            ?>
            <h2>Calendario de enfrentamientos</h2>
            <table>
                <thead>
                <tr>
                    <td>Jornada</td>
                    <td>Fecha</td>
                    <td>Oponentes</td>
                    <td>Resultado</td>
                    <td>Vídeos</td>
                </tr>
                </thead>
                <tbody>
                <?php foreach(Match::find('(team1id = ? or team2id = ?) and seasonid = ? and week <= ? order by week asc',
                    [$this->team->teamid, $this->team->teamid, $this->season->seasonid, $this->season->getWeeksCount()]) as $match) {

                    if (!$this->team->isManager() && !$this->season->weekIsPublic($match->week)) {
                        continue;
                    }

                    if (HTMLResponse::fromPOST('matchid', '') === $match->matchid &&
                        strlen($newResult = HTMLResponse::fromPOST('result', ''))) {
                        $match->result = $newResult;
                        $match->save();
                        HTMLResponse::exitWithRoute(HTMLResponse::getRoute());
                    }

                    $date = $this->season->getPublishTimeForWeek($match->week);

                    if ($match->team1id == $this->team->teamid) {
                        $posIndex = 0;
                    } else {
                        $posIndex = 1;
                    }

                    $opponentsId = ($match->team1id != $this->team->teamid) ? $match->team1id : $match->team2id;
                    $opponents = Team::get($opponentsId);

                    ?>
                    <tr>
                        <td style="height:3em">
                            <?php
                            echo $this->season->getWeekName($match->week);
                            ?>
                        </td>
                        <td><?= date("Y-m-d", $date) ?></td>
                        <td style="text-align: center">
                            <!--
                            <?=htmlentities($this->team->name)?>
                            VS
                            -->
                            <a href="/<?=$this->season->getLink()?>/equipos/<?=$opponents->getLink()?>/">
                                <?=htmlentities($opponents->name)?>
                            </a>
                        </td>
                        <td>
                            <i style="color: #666" <?php if ($this->team->isManager()) { ?>class="editableResult"<?php } ?>>
                                <?= ($this->team->isManager() || $match->isPublished()) ? $resultNames[$match->result][$posIndex] : $resultNames[0][0] ?>
                            </i>

                            <form class="editResult" method="POST" action="<?=HTMLResponse::getRoute()?>">
                                <select name="result">
                                    <?php foreach($resultNames as $index => $names) {
                                        ?><option <?=($index==$match->result?'selected':'')?> value="<?=$index?>"><?=$names[$posIndex]?></option><?php
                                    } ?>
                                </select>
                                <input type="hidden" name="matchid" value="<?=$match->matchid?>">
                            </form>

                        </td>
                        <td>
                            <?php $this->showMatchVideo($this->team, $match, 2, "Ver Team Preview") ?>
                            <?php $this->showMatchVideo($this->team, $match, 1, "Ver Combate") ?>

                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
            <?php
            $this->showFriendlyMatches();

            if ($this->team->isManager()) {
                $this->checkPlayerChanges();
            }
            $this->showPlayers();
            if ($this->team->isManager()) {
                $this->showPlayersEditor();
            }

            $sanctionLevels = Sanction::getLevelNames();

            $sanctions = Sanction::find('seasonid = ? and teamid = ? order by dateline desc',
                [$this->season->seasonid, $this->team->teamid]);

            if ($sanctions && Team::isMember()) {
                ?>
                <h2>Sanciones recibidas</h2>
                <table style="min-width: 512px">
                    <thead>
                    <tr>
                        <!-- <td>Fecha</td> -->
                        <td>Tipo</td>
                        <td>Razón</td>
                    </tr>
                    </thead>

                    <?php foreach($sanctions as $sanction) { ?>
                        <tr>
                            <!-- <td style="font-style: italic">
                                <?= date("Y-m-d H:i:s", $sanction->dateline) ?>
                            </td> -->
                            <td>
                                <?= $sanctionLevels[$sanction->level] ?>
                                <?php if (Team::isAdmin()) { ?>
                                    <i style="color: #666">
                                        por
                                    </i>
                                    <?= htmlentities($sanction->adminname) ?>
                                <?php } ?>
                            </td>
                            <td>
                                <?= htmlentities($sanction->reason) ?>
                            </td>
                        </tr>
                    <?php } ?>
                </table><br>

                <?php
            }

            if (Team::isAdmin()) {

                $postCsrf = HTMLResponse::fromPOST('sanctioncsrf', '');

                if ($postCsrf == $csrf) {
                    if (strlen($reason = HTMLResponse::fromPOST('sanctionreason'))) {
                        $sanction = Sanction::create();
                        $sanction->adminid = TwitterAuth::getUserId();
                        $sanction->adminname = TwitterAuth::getUserName();
                        $sanction->dateline = time();
                        $sanction->reason = $reason;
                        $sanction->seasonid = $this->season->seasonid;
                        $sanction->teamid = $this->team->teamid;
                        $sanction->level = HTMLResponse::fromPOST('sanctionlevel', 0);
                        $sanction->save();
                        HTMLResponse::exitWithRoute(HTMLResponse::getRoute());
                    }
                }

                ?>
                <h2>Añadir nueva sanción</h2>
                <form action="<?=HTMLResponse::getRoute()?>" method="post">
                    <table style="min-width: 512px">
                        <thead>
                        <tr>
                            <td>Tipo</td>
                            <td>Razón</td>
                        </tr>
                        </thead>

                        <tr>
                            <td>
                                <select name="sanctionlevel">
                                    <?php foreach ($sanctionLevels as $index => $label) { ?>
                                        <option value="<?=$index?>">
                                            <?= $label ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </td>
                            <td>
                                <textarea name="sanctionreason" style="width: 250px"></textarea>
                            </td>
                        </tr>
                    </table>
                    <input type="hidden" name="sanctioncsrf" value="<?=$csrf?>">
                    <div style="height: 6px"></div>
                    <button type="submit">Añadir sanción</button>
                </form>
                <?php
            } ?><br>
            <?php
            $this->showTeamSeasons();
            ?>
        </div>
        <?php
    }

    /**
     * @param $team Team
     * @param $match Match
     * @param $type
     * @param $label
     */
    private function showMatchVideo($team, $match, $type, $label)
    {
        $isManager = $team->isManager();

        $video = Video::findOne('teamid = ? and matchid = ? and type = ?', [$team->teamid, $match->matchid, $type]);


        if (HTMLResponse::fromPOST('matchid', '') === $match->matchid &&
            HTMLResponse::fromPOST('teamid', '') === $team->teamid &&
            HTMLResponse::fromPOST('type', '') === "$type") {

            $newLink = HTMLResponse::fromPOST('link', '');

            // regex por http://lasnv.net/foro/839/Javascript_parsear_URL_de_YouTube
            $regex = '/^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#\&\?]*).*/';

            if (strlen($newLink) == 0 || preg_match($regex, $newLink)) {
                if ($video) {
                    $video->link = $newLink;
                } else {
                    $video = Video::create();
                    $video->link = $newLink;
                    $video->matchid = $match->matchid;
                    $video->teamid = $team->teamid;
                    $video->type = $type;
                }

                $video->dateline = time();
                $video->save();
            }
            else {
                $this->design->addJavaScript("
                    $(function() { alert(\"El enlace que has puesto no es un enlace de YouTube válido\"); })
                ", false);
            }
        }

        if (!$video->link) $video = null;


        if (!$video && $isManager) {
            ?>
            <div style="white-space: nowrap">
                <span class="inblock" style="text-decoration: line-through; color: #666"><?= $label ?></span>
                <form class="editVideo inblock" method="POST" action="<?= HTMLResponse::getRoute() ?>">
                    <span class="editableVideo">Editar</span>
                    <input type="hidden" name="link" class="editInput" value="">
                    <input type="hidden" name="matchid" value="<?=$match->matchid?>">
                    <input type="hidden" name="teamid" value="<?=$team->teamid?>">
                    <input type="hidden" name="type" value="<?=$type?>">
                    <input type="hidden" name="label" value="<?=$label?>">
                </form>
            </div>
            <?php
        } else if ($video && $isManager) {
            ?>
            <div style="white-space: nowrap">
                <a class="inblock" href="<?=htmlentities($video->link)?>" target="_blank"><?= $label ?></a>
                <form class="editVideo inblock" method="POST" action="<?= HTMLResponse::getRoute() ?>">
                    <span class="editableVideo">Editar</span>
                    <input type="hidden" name="link" class="editInput" value="<?=htmlentities($video->link)?>">
                    <input type="hidden" name="matchid" value="<?=$match->matchid?>">
                    <input type="hidden" name="teamid" value="<?=$team->teamid?>">
                    <input type="hidden" name="type" value="<?=$type?>">
                    <input type="hidden" name="label" value="<?=$label?>">
                </form>
            </div>
            <?php
        }
        else if ($video && ($type != 1 || $match->isPublished())) {
            ?><a href="<?=htmlentities($video->link)?>" target="_blank"><?= $label ?></a><br><?php
        }

    }

    private function showPlayersEditor()
    {
        ?>
        <h2>Editar jugadores iniciales</h2>
        <div class="inblock" style="display: inline-block; text-align: left">
        <?php
        for ($i=1; $i<=$this->season->teamplayers; $i++) {
            $player = Player::findOne('teamid = ? and number = ? and seasonid = ?',
                [$this->team->teamid, $i, $this->season->seasonid]);
            $pname = $player ? $player->name : '';
            ?>
            <form method="post" class="playerEdit player<?=$i?>" action="<?=HTMLResponse::getRoute()?>">
                <div style="width:80px" class="inblock middle">
                    Jugador <?= $i ?>
                </div>
                <div style="width:80px; text-align: center; color: #666" class="inblock middle">
                    <?= $this->tiers[$i-1] ?>
                </div>
                <select name="name" onchange="$(this).closest('form').submit()">
                    <option value="">-- Elige Pokémon --</option>
                    <?php
                    foreach(Player::getAvailable() as $sprite) {
                        ?>
                        <option <?=$pname==$sprite?'selected':''?> value="<?=htmlentities($sprite)?>">
                            <?= htmlentities(ucwords($sprite)) ?>
                        </option>
                        <?php
                    }
                    ?>
                </select>
                <input type="hidden" name="number" value="<?=$i?>">
            </form>
            <div style="margin-bottom: 6px"></div>
            <?php
        }
        ?>
        </div><?php
    }

    private function showPlayers()
    {
        $players = Player::find('teamid = ? and name != ? and seasonid = ? order by number asc',
            [$this->team->teamid, '', $this->season->seasonid]);
        if (!$players) return;

        $playersByNumber = Model::indexBy($players, 'number');

        ?>
        <h2>Jugadores iniciales</h2>
        <?php for ($y=0; $y*3 < $this->season->teamplayers; $y++) {
        ?><table>
        <thead>
        <tr>
            <?php
            $colspan = 1;
            for ($x=0; $x<3; $x++) {
                if ($x != 2 && ($this->tiers[$y*3 + $x] == $this->tiers[$y*3 + $x + 1])) {
                    $colspan++;
                }
                else {
                    ?>
                    <td colspan="<?=$colspan?>">
                        <?= $this->tiers[$y*3 + $x] ?>
                    </td>
                    <?php
                    $colspan = 1;
                }
            } ?>
        </tr>
        </thead><tr><?php
            for ($x=0; $x<3; $x++) {
                $number = $y*3 + $x + 1;
                $player = $playersByNumber[$number];
                $playerLink = preg_replace("'\\-[0-9]$'", "", $player->name);
                $infoLink = "http://www.smogon.com/dex/sm/pokemon/{$playerLink}/";
                ?>
                <td>
                    <div style="width: 144px">
                        <?php if ($player) { ?>
                            <div class="inblock middle" style="text-align: center">
                                <a href="<?=$infoLink?>" target="_blank" title="Ver en Smogon">
                                    <img src="/img/sprites/<?= $player->name ?>.png">
                                </a>
                            </div><br>
                            <a href="<?=$infoLink?>" target="_blank" title="Ver en Smogon">
                                <b><?= ucwords($player->name) ?></b>
                            </a>
                        <?php } else { ?>
                            <div class="inblock middle" style="text-align: center">
                                <div style="width:100px; height:100px"
                            </div><br>
                        <?php } ?>
                    </div>
                </td>
                <?php
            }
            ?></tr></table>
        <div style="height: 6px"></div><?php
    } ?>
        <?php
    }

    private function checkPlayerChanges()
    {
        $number = HTMLResponse::fromPOST('number', 0);
        $name = HTMLResponse::fromPOST('name', '');

        if ($number >= 1 && $number <= count($this->tiers)) {
            if ($name == '' || in_array($name, Player::getAvailable())) {
                $player = Player::findOne('teamid = ? and number = ? and seasonid = ?',
                    [$this->team->teamid, $number, $this->season->seasonid]);
                if (!$player) {
                    $player = Player::create();
                    $player->number = $number;
                    $player->teamid = $this->team->teamid;
                    $player->seasonid = $this->season->seasonid;
                }
                $player->name = $name;
                $player->save();
            }
        }
    }

    private function showFriendlyMatches() {
        $csrf = $_SESSION['csrf'];
        $opponents = Model::indexBy(Team::getAllMembers(), 'teamid');

        $postCsrf = HTMLResponse::fromPOST('friendlycsrf', '');

        if ($postCsrf == $csrf && $this->team->isManager()) {
            $url = HTMLResponse::fromPOST('friendlyurl');
            $opponentsId = HTMLResponse::fromPOST('friendlyopponentsid');
            $publishDate = HTMLResponse::fromPOST('friendlydate');
            $publishTime = HTMLResponse::fromPOST('friendlytime');

            if (!strlen($publishDate)) $publishDate = date('Y-m-d');
            if (!strlen($publishTime)) $publishTime = date('H').':00';

            $possibleOpponents = Model::pluck(Team::getAllMembers(), 'teamid');

            $regex = '/^.*((youtu.be\/)|(v\/)|(\/u\/\w\/)|(embed\/)|(watch\?))\??v?=?([^#\&\?]*).*/';
            $timeRegex = "'^[0-9]{2}:[0-9]{2}$'";
            $dateRegex = "'^[0-9]{4}\\-[0-9]{2}\\-[0-9]{2}$'";

            $removeId = HTMLResponse::fromPOST('removeid');
            if ($removeId) {
                /** @var Video $video */
                if ($video = Video::findOne('seasonid = ? and type = ? and videoid = ? and teamid = ?',
                    [$this->season->seasonid, 3, $removeId, $this->team->teamid])) {
                    $video->delete();
                    HTMLResponse::exitWithRoute(HTMLResponse::getRoute());
                }
            }

            if (!strlen($opponentsId) || !strlen($publishTime) || !strlen($publishDate) || !strlen($url)) {
                $this->design->addJavaScript("
                    $(function() { alert(\"No has rellenado todos los datos\"); })
                ", false);
            } else {
                if ($opponentsId != $this->team->teamid && in_array($opponentsId, $possibleOpponents)) {
                    if (!preg_match($regex, $url)) {
                        $this->design->addJavaScript("
                    $(function() { alert(\"El enlace que has puesto no es un enlace de YouTube válido\"); })
                ", false);
                    } else {
                        if (!preg_match($timeRegex, $publishTime)) {
                            $this->design->addJavaScript("
                    $(function() { alert(\"La hora que has puesto tiene un formato inválido (ha de ser 08:06)\"); })
                ", false);
                        } else {
                            if (!preg_match($dateRegex, $publishDate)) {
                                $this->design->addJavaScript("
                    $(function() { alert(\"La fecha que has puesto tiene un formato inválido (ha de ser 2099-12-31)\"); })
                ", false);
                            } else {
                                $video = Video::create();
                                $video->dateline = time();
                                $video->publishdate = $publishDate;
                                $video->publishtime = $publishTime;
                                $video->link = $url;
                                $video->opponentid = $opponentsId * 1;
                                $video->teamid = $this->team->teamid;
                                $video->type = 3;
                                $video->seasonid = $this->season->seasonid;
                                $video->save();
                                HTMLResponse::exitWithRoute(HTMLResponse::getRoute());
                            }
                        }
                    }
                }
            }
        }

        $videos = Video::find('seasonid = ? and teamid = ? and type = ? order by publishdate asc, publishtime asc',
            [$this->season->seasonid, $this->team->teamid, 3]);

        if ($videos || $this->team->isManager()) {
            ?>
            <h2>Combates amistosos</h2>
            <?php if ($this->team->isManager()) { ?>
                <form action="<?=HTMLResponse::getRoute()?>" method="post">
            <?php } ?>
            <table>
                <thead>
                <tr>
                    <td>Fecha</td>
                    <td>Hora</td>
                    <td>Oponentes</td>
                    <td>Vídeo</td>
                </tr>
                </thead>
                <?php foreach($videos as $video) {
                    if (!$this->team->isManager() &&
                        ($video->publishdate > date('Y-m-d') ||
                            ($video->publishdate == date('Y-m-d') && $video->publishtime > date('H:i')))) {
                        continue;
                    }
                    ?>
                    <tr>
                        <td><?= $video->publishdate ?></td>
                        <td><?= $video->publishtime ?></td>
                        <td>
                            <a href="/<?=$this->season->getLink()?>/equipos/<?=$opponents[$video->opponentid]->getLink()?>/">
                                <?= htmlentities($opponents[$video->opponentid]->name) ?>
                            </a>
                        </td>
                        <td>
                            <a href="<?=htmlentities($video->link)?>" target="_blank">
                                Ver combate
                            </a>
                            <?php if ($this->team->isManager()) { ?>
                                <a style="font-size: 10px" href="javascript:void(0)" onclick="removeFriendlyVideo(this, <?=$video->videoid?>)">
                                    (Quitar)
                                </a>
                            <?php } ?>
                        </td>
                    </tr>
                <?php } ?>
                <?php if ($this->team->isManager()) { ?>
                    <tr>
                        <td>
                            <input type="date" name="friendlydate" placeholder="<?=date('Y-m-d')?>" style="width:80px">
                        </td>
                        <td>
                            <input name="friendlytime" placeholder="<?=date('H:i')?>" style="width: 64px">
                        </td>
                        <td>
                            <select name="friendlyopponentsid">
                                <option value="">-- Elige oponentes --</option>
                                <?php
                                foreach(Team::getAllMembers() as $team) {
                                    if ($team->teamid == $this->team->teamid) continue;
                                    ?>
                                    <option value="<?=$team->teamid?>">
                                        <?= htmlentities($team->name) ?>
                                    </option>
                                    <?php
                                }
                                ?>
                            </select>
                        </td>
                        <td>
                            <input name="friendlyurl" placeholder="http://youtube.com/..." style="width:200px">
                        </td>
                    </tr>
                <?php } ?>
            </table>
            <?php if ($this->team->isManager()) { ?>
                <div style="height: 6px"></div>
                <button type="submit">Añadir amistoso</button>
                <input type="hidden" name="friendlycsrf" value="<?=$csrf?>">
                <input type="hidden" name="removeid" value="">
                </form>
            <?php }
        }
    }

    private function showTeamSeasons()
    {
        $teamSeasonIds = Model::pluck(SeasonTeam::find('teamid = ?', [$this->team->teamid]), 'seasonid');
        $teamSeasons = Season::getMultiple($teamSeasonIds);
        Model::orderBy($teamSeasons, 'seasonid');

        $teamMatches = Match::find('team1id = ? or team2id = ?', [$this->team->teamid, $this->team->teamid]);
        // $games = [];
        $playedGames = [];
        $wins = [];
        $losses = [];

        foreach($teamMatches as $match) {
            if (!$match->isPublished()) continue;
            // $games[$match->seasonid]++;
            if ($match->getWinner() == $this->team->teamid) {
                $playedGames[$match->seasonid]++;
                $wins[$match->seasonid]++;
            }
            if ($match->getLooser() == $this->team->teamid) {
                $playedGames[$match->seasonid]++;
                $losses[$match->seasonid]++;
            }
        }

        ?>
        <h2>Actividad por temporadas</h2>

        <table style="width: 400px">
        <thead><tr>
            <td>Nombre</td>
            <td>Combates</td>
            <td>Victorias</td>
            <td>Derrotas</td>
        </tr></thead>
        <?php
        foreach($teamSeasons as $season) {
            if (!$season->ispublic && !Team::isSuperAdmin()) continue;
            ?>
            <tr>
                <td>
                    <a href="/<?=$season->getLink()?>/equipos/<?=$this->team->getLink()?>/">
                        <?= htmlentities($season->name) ?>
                    </a>
                </td>
                <td><?= $playedGames[$season->seasonid] * 1 ?></td>
                <td><?= $wins[$season->seasonid] * 1 ?></td>
                <td><?= $losses[$season->seasonid] * 1 ?></td>
            </tr>
            <?php
        }
        ?></table><br><?php

    }
}