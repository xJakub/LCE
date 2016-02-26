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


$router->addRoute("/", array('Index'));
$router->addRoute("/jornadas/{$rNum}/", array('Index'));
$router->addRoute("/batch/", array('Batch'));
$router->addRoute("/equipos/", array('Teams'));
$router->addRoute("/equipos/{$rDir}/", array('Team_Index'));
$router->addRoute("/clasificacion/", array('Ranking'));
$router->addRoute("/quiniela/", array('BetsRanking'));

$router->addRoute("/unete/", array('JoinUs'));
$router->addRoute("/normas/", array('Rules'));
$router->addRoute("/votaciones/", array('Polls'));
$router->addRoute("/votaciones/crear/", array('AddPoll'));
$router->addRoute("/votaciones/{$rNum}/", array('ViewPoll'));
$router->addRoute("/comunicados/", array('Notices'));


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