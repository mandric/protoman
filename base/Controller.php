<?php


class Controller
{
    public static $controllers;
    
    public function __construct($build_list = true)
    {
        if ($build_list && !count(Controller::$controllers))
        {
            $declared = get_declared_classes();
            $post_controller = false;
            
            foreach ($declared as $classname)
            {
                $classname = strtolower($classname);
                
                if (!$post_controller)
                {
                    if ($classname == 'controller')
                    {
                        $post_controller = true;
                    }
                    
                    continue;
                }
                
                $obj = new $classname(false, false);
                
                if (is_subclass_of($obj, 'controller') || is_subclass_of($obj, 'Controller'))
                {
                    Controller::$controllers[$obj->plural_name] = $classname;
                }
            }
        }
    }
}
