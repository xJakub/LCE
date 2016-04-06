<?php
/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 01/04/2016
 * Time: 21:50
 */
if (php_sapi_name() != "cli") {
    exit;
}

require "vendor/autoload.php";
foreach(glob("lib/*.php") as $file) {
    require_once($file);
}
require "config.php";
foreach(glob("models/*.php") as $file) {
    require_once($file);
}

$date = date('Y-m-d');

if (date('H')*1==15 && date('i')*1<15) {
    // 16:00 - 16:15
    // Partidas sin resultado
    // Jugadores sin video
    foreach(Season::find('1=1') as $season) {
        $weeksCount = $season->getWeeksCount();
        for ($week=1; $week<=$weeksCount; $week++) {
            if ($season->getWeekDate($week) == $date) {
                $matches = Match::find('seasonid = ? and week = ?', [$season->seasonid, $week]);
                foreach($matches as $match) {
                    foreach([$match->team1id, $match->team2id] as $teamid) {
                        if ($match->result == 0) {
                            // Falta resultado
                            $team = Team::get($teamid);
                            $msg = "Hola {$team->username}, falta por poner el resultado de tu combate de hoy.";
                            echo "-> $msg\n";
                            TwitterAuth::botSendPrivateMessage($team->username, $msg);
                            sleep(1);
                        }

                        $video = Video::findOne('matchid = ? and teamid = ? and type = ?',
                            [$match->matchid, $teamid, 1]);
                        if (!$video) {
                            // Falta video
                            $team = Team::get($teamid);
                            $msg = "Hola {$team->username}, falta por poner el vídeo de tu combate de hoy.";
                            echo "-> $msg\n";
                            TwitterAuth::botSendPrivateMessage($team->username, $msg);
                            sleep(1);
                        }
                    }
                }
            }
        }
    }
}


if (date('H')*1==17 && date('i')*1<15) {
    // 17:00 - 17:15
    // Videos
    foreach(Season::find('1=1') as $season) {
        $weeksCount = $season->getWeeksCount();
        for ($week=1; $week<=$weeksCount; $week++) {
            if ($season->getWeekDate($week) == $date) {
                $weekName = $season->getWeekName($week);

                $matches = Match::find('seasonid = ? and week = ?', [$season->seasonid, $week]);
                foreach($matches as $match) {
                    /** @var Team $team1 */
                    $team1 = $match->getTeam1();
                    /** @var Team $team2 */
                    $team2 = $match->getTeam2();

                    $video1 = Video::findOne('matchid = ? and teamid = ? and type = ?',
                        [$match->matchid, $team1->teamid, 1]);
                    $video2 = Video::findOne('matchid = ? and teamid = ? and type = ?',
                        [$match->matchid, $team2->teamid, 1]);

                    if ($video1 || $video2) {
                        $msg = "{$weekName} {$season->name}: #".($team1->getHashtag())." VS #".($team2->getHashtag());
                        $msg .= " @{$team1->username} VS @{$team2->username}";
                        if ($video1) $msg .= " {$video1->link}";
                        if ($video2) $msg .= " {$video2->link}";
                        echo "-> $msg\n";
                        TwitterAuth::botSendTweet($msg);
                        sleep(1);
                    }
                }
            }
        }
    }
}
