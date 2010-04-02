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
    public function sql();
    
    public function __construct($source, $args);
    
    public function &get();
    public function set($value);
    
    public function validate();
    public function databaseValue();
    public function displaySafe();
    public function displayRaw();
    
    public function formField();
}


interface SingleRelationType
{
    public function sql();
    
    public function __construct($source, $args);
    
    public function &get();
    public function set($value);
    
    public function validate();
    public function databaseValue();
    public function displaySafe();
    public function displayRaw();
    
    public function formField();
}


interface MultipleRelationType
{
    public function sql();
    
    public function __construct($source, $args);
    
    public function &get();
    public function set($value);
    
    public function save();
    public function displaySafe();
    public function displayRaw();
    
    public function formField();
}
