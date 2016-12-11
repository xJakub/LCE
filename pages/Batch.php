<?php

/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 29/10/2015
 * Time: 12:07
 */
class Batch implements PublicSection
{
    private $results;

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

        $teams = [];
        foreach (explode("\n", file_get_contents("teams.txt")) as $line) {
            $parts = explode(" - ", " $line ");

            if (count($parts) != 2) continue;

            list($name, $username) = $parts;
            $username = trim($username);
            $name = trim($name);

            $team = Team::create();
            $team->isadmin = false;
            $team->ismember = true;

            if ($username[0] == '@') {
                $username = substr($username, 1);
                $team->isadmin = true;
            }
            else if ($username[0] == '!') {
                $username = substr($username, 1);
                $team->ismember = false;
            }

            $team->name = $name;
            $team->username = $username;
            $team->ispublic = true;

            if (!strlen($name)) {
                $team->name = $team->username;
                $team->ispublic = false;
            }

            $teams[] = $team;
        }

        if (count($teams) >= 1 && count($teams) != count(Team::find('1=1'))) {
            Team::truncate(true);
            Match::truncate(true);
            Model::saveAll($teams);
            ?>Equipos actualizados.<br><?php
        }
        else {
            ?>No hay cambios en los equipos.<br><?php
        }
    }

    public function makeMatches() {

        $teams = Team::find('1=1');
        $teamIdByName = Model::pluck($teams, 'teamid', 'name');

        $week = 0;
        $matches = [];

        foreach (explode("\n", file_get_contents("matches.txt")) as $line) {
            $parts = explode("VS", $line);
            if (count($parts) == 1 && substr(trim($line), 0, 2) == '--') {
                $week++;
            }
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
                die("Equipo desconocido: $name1");
            }
            if (!$match->team2id) {
                die("Equipo desconocido: $name2");
            }

        }

        if (count($matches) >= 1 && count($matches) != count(Match::find('1=1'))) {
            Match::truncate(true);
            Model::saveAll($matches);
            ?>Enfrentamientos actualizados.<br><?php
        }
        else {
            ?>No hay cambios en los enfrentamientos.<br><?php
        }

    }

    public function saveResults() {
        $this->results = [];
        foreach(Match::find('1=1') as $match) {
            $this->results[$match->matchid] = $match->result;
        }
    }

    public function restoreResults() {
        $matches = Match::find('1=1');
        foreach($matches as $match) {
            if (isset($this->results[$match->matchid])) {
                $match->result = $this->results[$match->matchid];
            }
        }
        Match::saveAll($matches);
    }

    /**
     * @return void
     */
    public function show()
    {
        // we won't need this anymore
        /*
        $this->saveResults();
        $this->makeTeams();
        $this->makeMatches();
        $this->restoreResults();
        */
    }
}