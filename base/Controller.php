<?php


class Controller
{
    public static function process($querystring)
    {
        // TODO: Replace this with something else?  Hackish but OK?
        $querystring = preg_replace('/[\/]+$/', '', trim($querystring));
        
        foreach (Framework::$routes as $url => $methods)
        {
            $matches = array();
            
            $regex = '/^' . str_replace('/', '\/', preg_replace('/[\/]+$/', '', $url)) . '\/{0,1}$/';
            preg_match($regex, $querystring, $matches);
            
            if (count($matches))
            {
                foreach ($methods as $method)
                {
                    call_user_func($method, array_slice($matches, 1));
                }
                
                // TODO: Return something chainable?  Request class?
                return;
            }
        }
        
        if (DEBUG)
        {
            trigger_error("404 handler being invoked for: " . $querystring, E_USER_WARNING);
        }
        
        // TODO: 404 handling here
    }
}
