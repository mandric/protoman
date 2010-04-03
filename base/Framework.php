<?php


class Framework
{
    public static $apps = array();
    public static $routes = array();
    public static $types = array();
    public static $controllers = array();
}


class Route
{
    public function __construct()
    {
        $app = false;
        
        $args = func_get_args();
        
        if (count($args) == 1)
        {
            $routes = $args[0];
        }
        else if (count($args) == 2)
        {
            $app = $args[0];
            $routes = $args[1];
        }
        else
        {
            trigger_error("Routes require 1 or 2 arguments", E_USER_WARNING);
            return false;
        }
        
        if ($app)
        {
            Framework::$routes[$app] = array();
        }
        
        foreach ($routes as $url => $method)
        {
            if ($app)
            {
                Framework::$routes[$app][$url] = $method;
            }
            else
            {
                Framework::$routes[$url] = $method;
            }
        }
    }
}


interface Type
{
    public function __construct($source, $name, $args);
    
    public function sql();
    public function &get();
    
    public function set($value);
}


interface LiteralType extends Type
{
    public function validate();
    public function databaseValue();
    public function displaySafe();
    public function displayRaw();
    
    public function formField();
}


interface SingleRelationType extends Type
{
    public function validate();
    public function databaseValue();
    public function displaySafe();
    public function displayRaw();
    
    public function formField();
}


interface MultipleRelationType extends Type
{
    public function save();
    public function displaySafe();
    public function displayRaw();
    
    public function formField();
}
