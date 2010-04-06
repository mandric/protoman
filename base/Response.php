<?php


class Response
{
    public static $context = array();
    public static $content = '';
    private static $blocks = array();
    
    private static $template_level = 0;
    private static $extends = array();
    
    public static function startBlock($name)
    {
        if (!Response::$blocks[$name])
        {
            Response::$blocks[$name] = false;
            ob_start();
        }
        else
        {
            if (!Response::$blocks['super'])
            {
                Response::$blocks['super'] = array();
            }
            
            // TODO: Test 'super' implementation
            Response::$blocks['super'][$name] = Response::$blocks[$name];
        }
    }
    
    public static function endBlock($name)
    {
        if (!Response::$blocks[$name])
        {
            Response::$blocks[$name] = ob_get_contents();
            ob_end_clean();
        }
    }
    
    public static function renderBlock($name)
    {
        if (Response::$blocks[$name])
        {
            return Response::$blocks[$name];
        }
        
        throw new Exception("Attempted to render nonexistent block: $name");
    }
    
    public static function extendTemplate()
    {
        $args = func_get_args();
        Response::$extends[] = $args;
    }
    
    public static function renderTemplate()
    {
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
        }
        
        $content = false;
        
        Response::$template_level++;
        
        foreach ($path_parts as $parts)
        {
            $path = implode('/', $parts);
            
            if (is_file($path))
            {
                ob_start();
                require($path);
                $content = ob_get_contents();
                ob_end_clean();
                
                break;
            }
        }
        
        if ($content === false)
        {
            throw new Exception("Attempted to load a nonexistent template");
        }
        
        Response::$template_level--;
        
        if (Response::$template_level > 0)
        {
            return $content;
        }
        
        // TODO: Run through $extends backwards?
        foreach (Response::$extends as $key => $args)
        {
            unset(Response::$extends[$key]);
            call_user_func_array(array('Response', 'renderTemplate'), $args);
            return;
        }
        
        Response::$content .= $content;
    }
}
