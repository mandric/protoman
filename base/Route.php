<?php


class Route
{
    public static $routes;
    
    public function __construct($build_list = true)
    {
        if ($build_list && !count(Route::$routes))
        {
            $declared = get_declared_classes();
            $post_route = false;
            
            foreach ($declared as $classname)
            {
                $classname = strtolower($classname);
                
                if (!$post_route)
                {
                    if ($classname == 'route')
                    {
                        $post_route = true;
                    }
                    
                    continue;
                }
                
                $obj = new $classname(false, false);
                
                if (is_subclass_of($obj, 'route') || is_subclass_of($obj, 'Route'))
                {
                    Route::$routes[$obj->plural_name] = $classname;
                }
            }
        }
    }
}
