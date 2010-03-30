<?php


class Route
{
    public static $routes;
    
    public function __construct($array)
    {
        if (is_array($array))
        {
            foreach ($array as $url => $method)
            {
                Route::$routes[$url] = $method;
            }
        }
    }
}
