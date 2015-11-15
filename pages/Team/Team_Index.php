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

    public function __construct($dir) {
        $this->team = Team::fromLink($dir);
        if (!$this->team) HTMLResponse::exitWithRoute('/equipos/');
    }

    public function setDesign(PublicDesign $response)
    {
        $this->design = $response;
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

        ?>
        <div class="inblock" style="margin-right: 16px">
            <a target="_blank" href="/<?=$this->team->getImageLink()?>">
                <img src="/<?=$this->team->getImageLink(300, 200)?>" alt="Logo" class="teamlogo"><br>
            </a>
            <a href="https://twitter.com/hashtag/<?=$this->team->getHashtag()?>" target="_blank">#<?=$this->team->getHashtag()?></a>
            <div style="height:2px"></div>
            <a href="https://twitter.com/<?=$this->team->username?>" target="_blank">@<?=$this->team->username?></a>
            <?
            if ($this->team->isManager()) {
                ?>
                <br>Eres el Manager del equipo.
                <?
            }
            ?>
        </div>
        <div class="inblock">
            <?

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
                <? foreach(Match::find('team1id = ? or team2id = ? order by week asc',
                    [$this->team->teamid, $this->team->teamid]) as $match) {

                    if (HTMLResponse::fromPOST('matchid', '') === $match->matchid &&
                        strlen($newResult = HTMLResponse::fromPOST('result', ''))) {
                        $match->result = $newResult;
                        $match->save();
                        HTMLResponse::exitWithRoute(HTMLResponse::getRoute());
                    }

                    $date = $match->getPublishDate();

                    if ($match->team1id == $this->team->teamid) {
                        $posIndex = 0;
                    }
                    else {
                        $posIndex = 1;
                    }

                    $opponentsId = ($match->team1id != $this->team->teamid) ? $match->team1id : $match->team2id;
                    $opponents = Team::get($opponentsId);

                    ?>
                    <tr>
                        <td style="height:3em">Jornada <?=$match->week?></td>
                        <td><?= date("Y-m-d", $date) ?></td>
                        <td style="text-align: center">
                            <!--
                            <?=htmlentities($this->team->name)?>
                            VS
                            -->
                            <a href="/equipos/<?=$opponents->getLink()?>/">
                                <?=htmlentities($opponents->name)?>
                            </a>
                        </td>
                        <td>
                            <i style="color: #666" <? if ($this->team->isManager()) { ?>class="editableResult"<?}?>>
                                <?= ($this->team->isManager() || $match->isPublished()) ? $resultNames[$match->result][$posIndex] : $resultNames[0][0] ?>
                            </i>

                            <form class="editResult" method="POST" action="<?=HTMLResponse::getRoute()?>">
                                <select name="result">
                                    <? foreach($resultNames as $index => $names) {
                                        ?><option <?=($index==$match->result?'selected':'')?> value="<?=$index?>"><?=$names[$posIndex]?></option><?
                                    } ?>
                                </select>
                                <input type="hidden" name="matchid" value="<?=$match->matchid?>">
                            </form>

                        </td>
                        <td>
                            <? $this->showMatchVideo($this->team, $match, 2, "Ver Team Preview") ?>
                            <? $this->showMatchVideo($this->team, $match, 1, "Ver Combate") ?>

                        </td>
                    </tr>
                <? } ?>
                </tbody>
            </table>
        </div>
        <?
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
            <?
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
            <?
        }
        else if ($video && $match->isPublished()) {
            ?><a href="<?=htmlentities($video->link)?>" target="_blank"><?= $label ?></a><br><?
        }

    }
}