<?php


class Type
{
    public static $types;
    
    public function __construct($build_list = true)
    {
        if ($build_list && !count(Type::$types))
        {
            $declared = get_declared_classes();
            $post_type = false;
            
            foreach ($declared as $classname)
            {
                $classname = strtolower($classname);
                
                if (!$post_type)
                {
                    if ($classname == 'type')
                    {
                        $post_type = true;
                    }
                    
                    continue;
                }
                
                $obj = new $classname(false, false);
                
                if (is_subclass_of($obj, 'type') || is_subclass_of($obj, 'Type'))
                {
                    Type::$types[$obj->plural_name] = $classname;
                }
            }
        }
    }
}
