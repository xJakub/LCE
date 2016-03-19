<?php

/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 08/11/2015
 * Time: 22:57
 */
class BetsRanking implements PublicSection
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
        return "Quiniela";
    }

    /**
     * @return void
     */
    public function show()
    {
        $matches = Match::find('seasonid = ? and result != 0', [$this->season->seasonid]);
        $matches = Model::indexBy($matches, 'matchid');

        $teams = Model::indexBy(Team::find('1=1'), 'teamid');

        $matchWinner = [];

        foreach($matches as $match) {
            if ($this->season->weekIsPublished($match->week)) {
                $matchWinner[$match->matchid] = $match->getWinner();
            }
        }

        $totalBets = [];
        $correctBets = [];

        $usernames = [];
        $avatars = [];

        foreach(Bet::find('1=1') as $bet) {
            if (isset($matchWinner[$bet->matchid])) {
                if ($bet->teamid && $matchWinner[$bet->matchid] == $bet->teamid) {
                    $correctBets[$bet->userid]++;
                }
                $totalBets[$bet->userid]++;
            }
            if ($bet->username) {
                $usernames[$bet->userid] = $bet->username;
            }
            if ($bet->avatar) {
                $avatars[$bet->userid] = $bet->avatar;
            }
        }

        $tiebreakers = [];
        foreach(array_keys($correctBets) as $userid) {
            $tiebreakers[$userid] = [-$correctBets[$userid], $totalBets[$userid], strtolower($usernames[$userid])];
        }
        asort($tiebreakers);


        if (TwitterAuth::isLogged()) {
            $userid = TwitterAuth::getUserId();
            $userBets = Bet::find('userid = ? order by matchid desc', [$userid]);

            $userpos = array_search($userid, array_keys($tiebreakers));
            $userpos = ($userpos === FALSE) ? 0 : $userpos+1;

            ?>
            <div class="inblock" style="text-align: left; margin-right: 20px">
                <h2>Tus estadísticas</h2>
                <table>
                    <thead>
                    <tr>
                        <td>Puesto</td>
                        <td>Nombre</td>
                        <td>Aciertos</td>
                        <td>Fallos</td>
                    </tr>
                    </thead>
                    <tr>
                        <td><?= $userpos ?>º</td>
                        <td style="text-align: left">
                            <div class="inblock" style="vertical-align: middle">
                                <a href="http://twitter.com/<?=htmlentities($usernames[$userid])?>" target="_blank">
                                    <img src="<?= htmlentities($avatars[$userid]) ?>" style="width:40px; height:40px; border-radius: 20px">
                                </a>
                            </div>
                            <div class="inblock" style="vertical-align: middle">
                                <a href="http://twitter.com/<?=htmlentities($usernames[$userid])?>" target="_blank">
                                    <?= htmlentities($usernames[$userid]) ?>
                                    <? if (!isset($usernames[$userid])) echo "<i>$userid</i>"; ?>
                                </a>
                            </div>
                        </td>
                        <td><?= $correctBets[$userid] ?></td>
                        <td><?= $totalBets[$userid]-$correctBets[$userid] ?></td>
                    </tr>
                </table>

                <h2>Tus apuestas</h2>
                <table>
                    <thead>
                    <tr>
                        <td>Jornada</td>
                        <td>Enfrentamiento</td>
                        <td>Acierto</td>
                    </tr>
                    </thead>
                    <? foreach($userBets as $bet) {
                        if (!isset($matches[$bet->matchid])) continue;
                        /**
                         * @var $match Match
                         */
                        $match = $matches[$bet->matchid];
                        if (!$match->isPublished()) continue;

                        $team1 = $teams[$match->team1id];
                        $team2 = $teams[$match->team2id];

                        $success = $match->getWinner() == $bet->teamid;
                        ?>
                        <tr>
                            <td><?= $match->week ?></td>
                            <td>
                                <div class="inblock">
                                    <div class="teamimg64">
                                        <img src="/<?= $team1->getImageLink(64, 64) ?>" class="<?= $match->getWinner() == $team1->teamid ? '' : 'grayscale' ?>">
                                    </div>
                                    <? if ($bet->teamid == $team1->teamid) { ?>
                                        <br><i style="font-size:11px">Votado</i>
                                    <? } ?>
                                </div>

                                <div class="inblock" style="line-height: 64px; margin: 0px 4px">
                                    VS
                                </div>

                                <div class="inblock">
                                    <div class="teamimg64">
                                        <img src="/<?= $team2->getImageLink(64, 64) ?>" class="<?= $match->getWinner() == $team2->teamid ? '' : 'grayscale' ?>">
                                    </div>
                                    <? if ($bet->teamid == $team2->teamid) { ?>
                                        <br><i style="font-size:11px">Votado</i>
                                    <? } ?>
                                </div>
                            </td>
                            <td>
                                <?= $success
                                    ? '<div class="success-icon">&#x2714;</div>'
                                    : '<div class="fail-icon">&#x2718</div>' ?>
                            </td>

                        </tr>
                        <?
                    } ?>
                </table>
            </div>
            <?
        }


        ?>
        <div class="inblock">
            <h2>Clasificación</h2>
            <table>
                <thead>
                <tr>
                    <td>Puesto</td>
                    <td>Nombre</td>
                    <td>Aciertos</td>
                    <td>Fallos</td>
                </tr>
                </thead>

                <?
                $lastTiebreakers = null;
                $lastPos = 0;
                foreach(array_keys($tiebreakers) as $pos => $userid) {
                    unset($tiebreakers[$userid][2]);

                    if ($lastTiebreakers != $tiebreakers[$userid]) {
                        $lastPos = $pos;
                    }
                    ?>
                    <tr>
                        <td><?= $lastPos+1 ?>º</td>
                        <td style="text-align: left">
                            <div class="inblock" style="vertical-align: middle">
                                <a href="http://twitter.com/<?=htmlentities($usernames[$userid])?>" target="_blank">
                                    <img src="<?= htmlentities($avatars[$userid]) ?>" style="width:40px; height:40px; border-radius: 20px">
                                </a>
                            </div>
                            <div class="inblock" style="vertical-align: middle">
                                <a href="http://twitter.com/<?=htmlentities($usernames[$userid])?>" target="_blank">
                                    <?= htmlentities($usernames[$userid]) ?>
                                    <? if (!isset($usernames[$userid])) echo "<i>$userid</i>"; ?>
                                </a>
                            </div>
                        </td>
                        <td><?= $correctBets[$userid] ?></td>
                        <td><?= $totalBets[$userid]-$correctBets[$userid] ?></td>
                    </tr>
                    <?
                    $lastTiebreakers = $tiebreakers[$userid];
                }
                ?>
            </table>
        </div>
        <?
    }
}