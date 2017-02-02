<?php
/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 22/03/2015
 * Time: 22:18
 */

ob_start('ob_gzhandler');
session_start();

require "vendor/autoload.php";

foreach(glob("lib/*.php") as $file) {
    require_once($file);
}

require "config.php";

foreach(glob("models/*.php") as $file) {
    require_once($file);
}

require_once('PublicDesign.php');
require_once('PublicSection.php');

$router = new Router();

$rId = '([^/|\-]+)';
$rDir = '([^/]+)';
$rNum = '([0-9]+)';
$rExtra = '(?:-[^/]*)?';
$rIdExtra = "{$rId}{$rExtra}";
$rNumExtra = "{$rNum}{$rExtra}";



// if (Team::isAdmin()) {
    $router->addRoute("/unete/", array('JoinUs'));
// }

$router->addRoute("/normas/", array('Rules'));
$router->addRoute("/votaciones/", array('Polls'));
$router->addRoute("/votaciones/crear/", array('AddPoll'));
$router->addRoute("/votaciones/{$rNum}/", array('ViewPoll'));

$router->addRoute("/admin/", array('Admin_Index'));
$router->addRoute("/admin/comunicados/", array('Admin_Notices'));
$router->addRoute("/admin/equipos/", array('Admin_Teams'));
$router->addRoute("/admin/equipos/{$rNum}/", array('Admin_Team'));
$router->addRoute("/admin/temporadas/", array('Admin_Seasons'));
$router->addRoute("/admin/temporadas/{$rNum}/", array('Admin_Season'));
$router->addRoute("/admin/temporadas/{$rNum}/jornadas/", array('Admin_Season_Weeks'));
$router->addRoute("/admin/temporadas/{$rNum}/eventos/", array('Admin_Season_Events'));

$router->addRoute("/", array('Index'));
$router->addRoute("/batch/", array('Batch'));
$router->addRoute("/{$rDir}/jornadas/{$rNum}/", array('Season_Index'));
$router->addRoute("/{$rDir}/equipos/", array('Teams'));
$router->addRoute("/{$rDir}/equipos/{$rDir}/", array('Team_Index'));
$router->addRoute("/{$rDir}/clasificacion/", array('Ranking'));
$router->addRoute("/{$rDir}/quiniela/", array('BetsRanking'));
$router->addRoute("/{$rDir}/calendario/", array('Calendar'));
$router->addRoute("/{$rDir}/", array('Season_Index'));

$router->addRoute(".*", array('Error_404'));

$route = HTMLResponse::getRoute();

$indent = $router->process($route);

if ($indent) {
    $indentClass = $indent[0];
    $indentDir = dirname(str_replace("_", "/", $indentClass));
    $indentFile = str_replace("//","","pages/$indentDir/$indentClass.php");
    $indentParams = array_slice($indent, 1);

    if (file_exists("pages/$indentDir/$indentDir.php")) {
        require_once("pages/$indentDir/$indentDir.php");
    }
    if (file_exists($indentFile)) {
        require_once($indentFile);
    }

    $r = new ReflectionClass($indentClass);

    /**
     * @var $section PublicSection
     */
    $section = $r->newInstanceArgs($indentParams);

    $response = new PublicDesign($section);
    $response->show();

}