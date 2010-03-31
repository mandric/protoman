<?php


class Response
{
    public static $context = array();
    public static $content = '';
    
    private static $in_template = false;
    
    public static function renderTemplate($app_template)
    {
        $return = (Response::$in_template) ? true : false ;
        
        $names = explode('.', $app_template);
        $app_name = $names[0];
        $template_name = implode('.', array_slice($names, 1));
        
        // Find template:
        // [app]/templates
        // [proj]/templates/[app]
        // [framework]/apps/[app]/templates
        
        $path_parts = array(
            array($app_name, 'templates', $template_name),
            array('templates', $app_name, $template_name),
            array(FRAMEWORK_APPS_PATH, $app_name, 'templates', $template_name),
            );
        
        $content = false;
        
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
        
        Response::$in_template = true;
        
        // require template file
        
        if (!$return)
        {
            Response::$in_template = true;
        }
        
        if ($return)
        {
            return $content;
        }
        
        Response::$content .= $content;
    }
}
