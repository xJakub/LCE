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

        if (count($teams) >= 1 && count($teams) != count(Team::find('1=1'))) {
            foreach(Team::find('1=1') as $team) {
                $team->delete();
            }
            Model::saveAll($teams);
            ?>Equipos actualizados.<br><?
        }
        else {
            ?>No hay cambios en los equipos.<br><?
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
            foreach(Match::find('1=1') as $match) {
                $match->delete();
            }
            Model::saveAll($matches);
            ?>Enfrentamientos actualizados.<br><?
        }
        else {
            ?>No hay cambios en los enfrentamientos.<br><?
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