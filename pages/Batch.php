<?php

/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 29/10/2015
 * Time: 12:07
 */
class Batch implements PublicSection
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
        // TODO: Implement getTitle() method.
    }

    /**
     * @return String
     */
    public function getSubtitle()
    {
        // TODO: Implement getSubtitle() method.
    }

    public function makeTeams() {
        if (Team::find('1=1') === array()) {

            $teams = [];
            foreach (explode("\n", file_get_contents("teams.txt")) as $line) {
                $parts = explode(" - ", $line);

                if (count($parts) != 2) continue;

                list($name, $username) = $parts;

                $team = Team::create();
                $team->name = trim($name);
                $team->username = trim($username);
                $teams[] = $team;
            }

            Model::saveAll($teams);
        }
    }

    public function makeMatches() {
        if (Match::find('1=1') === array()) {

            $teams = Team::find('1=1');
            $teamIdByName = Model::pluck($teams, 'teamid', 'name');

            $count = 0;
            $week = 1;
            $matches = [];

            foreach (explode("\n", file_get_contents("matches.txt")) as $line) {
                $parts = explode("VS", $line);
                if (count($parts) != 2) continue;

                list($name1, $name2) = $parts;
                $name1 = trim($name1);
                $name2 = trim($name2);

                $match = Match::create();
                $match->team1id = $teamIdByName[$name1];
                $match->team2id = $teamIdByName[$name2];
                $match->week = $week;
                $matches[] = $match;

                if (!$match->team1id) {
                    die("Unknown team: $name1");
                }
                if (!$match->team2id) {
                    die("Unknown team: $name2");
                }

                if (++$count % 6 == 0) {
                    $week++;
                }

            }

            Model::saveAll($matches);
        }
    }

    public function makeImages() {

    }

    /**
     * @return void
     */
    public function show()
    {
        $this->makeTeams();
        $this->makeMatches();
    }
}