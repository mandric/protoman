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


class Type
{
    public function __construct()
    {
        Framework::$types[] = $this;
    }
}
