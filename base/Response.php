<?php


class Response
{
    public static $context = array();
    public static $content = '';
    
    private static $in_template = false;
    
    public static function renderTemplate()
    {
        $return = (Response::$in_template) ? true : false ;
        
        $args = func_get_args();
        
        switch (count($args))
        {
            case 1:
                $template_name = $args[0];
                
                $path_parts = array(
                    array('templates', $template_name),
                    );
                
                break;
            case 2:
                $app_name = $args[0];
                $template_name = $args[1];
                
                $path_parts = array(
                    array($app_name, 'templates', $template_name),
                    array('templates', $app_name, $template_name),
                    array(FRAMEWORK_APPS_PATH, $app_name, 'templates', $template_name),
                    );
                
                break;
            default:
                throw new Exception("Invalid renderTemplate call: Takes 1 or 2 arguments, given: " . print_r($args, true));
                break;
        }
        
        $content = false;
        
        Response::$in_template = true;
        
        foreach ($path_parts as $parts)
        {
            $path = implode('/', $parts);
            
            if (is_file($path))
            {
                ob_start();
                require_once($path);
                $content = ob_get_contents();
                ob_end_clean();
                
                break;
            }
        }
        
        if ($content === false)
        {
            throw new Exception("Attempted to load a nonexistent template");
        }
        
        if (!$return)
        {
            Response::$in_template = false;
        }
        else
        {
            return $content;
        }
        
        Response::$content .= $content;
    }
}
