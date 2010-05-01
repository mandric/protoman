<?php


class Framework
{
    public static $apps = array();
    public static $types = array();
    public static $controllers = array();
    
    public static $tests = array();
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
            throw new Exception("Routes require 1 or 2 arguments");
        }
        
        if ($app)
        {
            Controller::$routes[$app] = array();
        }
        
        foreach ($routes as $url => $methods)
        {
            if ($app)
            {
                Controller::$routes[$app][$url] = $methods;
            }
            else
            {
                Controller::$routes[$url] = $methods;
            }
            
            if (is_array($methods))
            {
                foreach ($methods as $value)
                {
                    if (is_string($value))
                    {
                        Controller::$named_routes[$value] = array($app, $url);
                    }
                }
            }
        }
    }
}


interface Type
{
    public function __construct($source, $name, $args);
    
    public function sql();
    
    public function populate($value);
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
