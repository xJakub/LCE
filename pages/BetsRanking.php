<?php

/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 08/11/2015
 * Time: 22:57
 */
class BetsRanking implements PublicSection
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
        return "Ránking de la Quiniela";
    }

    /**
     * @return void
     */
    public function show()
    {
        $matches = Match::find('result != 0');
        $matchWinner = [];

        foreach($matches as $match) {
            if ($match->isPublished()) {
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

        ?>
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
            foreach(array_keys($tiebreakers) as $pos => $userid) {
                ?>
                <tr>
                    <td><?= $pos+1 ?>º</td>
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
            }
            ?>
        </table>
        <?
    }
}