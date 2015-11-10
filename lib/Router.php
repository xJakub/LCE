<?php
/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 23/12/2014
 * Time: 13:33
 */

class Router {

    function __construct() {
        $this->routes = Array();
    }

    function addRoute($route, $response) {
        if (!is_array($response)) {
            $response = array($response);
        }
        $this->routes[] = array('regex'=>"'^$route$'", 'response'=>$response);
    }

    function process($route) {
        foreach($this->routes as $arr) {
            if (preg_match($arr['regex'], $route, $match)) {
                $result = array_merge($arr['response'], array_slice($match, 1));
                return $result;
            }
        }
    }
}