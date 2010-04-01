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
    public function validate();
    public function columnSql();
    public function databaseValue();
    public function displaySafe();
    public function displayRaw();
    public function form();
}


interface SingleRelationType
{
    public function validate();
    public function columnSql();
    public function databaseValue();
    public function form();
}


interface MultipleRelationType
{
    public function validate();
    public function tableSql();
    public function databaseValue();
    public function form();
}
