<?php


class Framework
{
    public static $routes = array();
    public static $types = array();
    public static $controllers = array();
}


class Route
{
    public function __construct($array)
    {
        if (is_array($array))
        {
            foreach ($array as $url => $method)
            {
                Framework::$routes[$url] = $method;
            }
        }
    }
}


interface Type
{
    public function columnSql();
    public function databaseValue();
    public function displaySafe();
    public function displayRaw();
    public function form();
}
