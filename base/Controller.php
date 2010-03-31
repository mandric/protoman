<?php


class Controller
{
    public static $controllers = array();
    
    public static function process($querystring)
    {
        foreach (Route::$routes as $url => $method)
        {
            if ($url == $querystring)
            {
                call_user_func($method);
            }
        }
    }
}
