<?php


class Controller
{
    public static $controllers = array();
    
    public static function process($querystring)
    {
        // TODO: Replace this with something else?  Hackish but OK?
        $querystring = preg_replace('/[\/]+$/', '', trim($querystring));
        
        foreach (Route::$routes as $url => $method)
        {
            $matches = array();
            
            $regex = '/^' . str_replace('/', '\/', preg_replace('/[\/]+$/', '', $url)) . '\/{0,1}$/';
            preg_match($regex, $querystring, $matches);
            
            if (count($matches))
            {
                call_user_func($method, array_slice($matches, 1));
                return;
            }
        }
        
        if (DEBUG)
        {
            trigger_error("No URL found to match query: " . $querystring, E_USER_WARNING);
        }
        else
        {
            // 404 redirection here
        }
    }
}
