<?php


class Controller
{
    public static function process($querystring, $routes=false)
    {
        if (!$routes)
        {
            $routes = Framework::$routes;
        }
        
        // TODO: Replace this with something else?  Hackish but OK?
        $querystring = preg_replace('/[\/]+$/', '', trim($querystring));
        
        foreach ($routes as $url => $route)
        {
            $matches = array();
            
            $regex = '/^' . str_replace('/', '\/', preg_replace('/[\/]+$/', '', $url)) . '\/{0,1}$/';
            preg_match($regex, $querystring, $matches);
            
            if (count($matches))
            {             
                foreach ($matches as $key => $value)
                {
                    if (!is_numeric($key))
                    {
                        unset($matches[$key]);
                    }
                }
                
                if (is_array($route))
                {
                    foreach ($route as $method)
                    {
                        $result = call_user_func_array($method, array_slice($matches, 1));
                        
                        // Return false from a controller method to prevent others from running, i.e. loginRequired
                        if ($result === false)
                        {
                            break;
                        }
                    }
                    
                    // TODO: Return something chainable?  Request class?
                    return;
                }
                else if (is_string($route))
                {
                    if (in_array($route, Framework::$apps))
                    {
                        return Controller::process($matches[1], Framework::$routes[$route]);
                    }
                }
            }
        }
        
        if (DEBUG)
        {
            trigger_error("404 handler being invoked for: " . $querystring, E_USER_WARNING);
        }
        
        // TODO: 404 handling here
    }
}
