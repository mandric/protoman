<?php


class Controller
{
    public static $routes = array();
    public static $named_routes = array();
    
    public static function reverse()
    {
        $args = func_get_args();
        
        if (!count($args))
        {
            throw new Exception("reverse() expects at least 1 argument");
        }
        
        $name = $args[0];
        $args = array_slice($args, 1);
        
        if (!in_array($name, array_keys(Controller::$named_routes)))
        {
            throw new Exception("reverse() called with invalid route name: {$name}");
        }
        
        $route = Controller::$named_routes[$name];
        
        $url = ($route[0]) ? "/{$route[0]}" : '' ;
        $route = $route[1];
        
        $matches = array();
        $mct = preg_match_all("/\([^)]+\)/", $route, $matches);
        
        if ($mct != count($args))
        {
            throw new Exception("Incorrect number of arguments to reverse(): expected {$mct}, got " . count($args));
        }
        
        foreach ($matches[0] as $idx => $match)
        {
            if (!preg_match("/^{$match}$/", $args[$idx], $matches))
            {
                throw new Exception("Got bad argument {$args[$idx]} for regex {$match}");
            }
            
            $route = str_replace($match, $args[$idx], $route);
        }
        
        return $url . $route;
    }
    
    public static function process($querystring, $routes=false)
    {
        if (!$routes)
        {
            $routes = Controller::$routes;
        }
        
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
                        if (is_array($method))
                        {
                            $result = call_user_func_array($method, array_slice($matches, 1));
                            
                            // Return false from a controller method to prevent others from running, i.e. loginRequired
                            if ($result === false)
                            {
                                break;
                            }
                        }
                    }
                    
                    return;
                }
                else if (is_string($route))
                {
                    if (in_array($route, Framework::$apps))
                    {
                        return Controller::process($matches[1], Controller::$routes[$route]);
                    }
                }
            }
        }
        
        if (!DEBUG)
        {
            throw new Exception("404 handler being invoked for: " . $querystring);
        }
        
        return Response::error404($querystring);
    }
}
