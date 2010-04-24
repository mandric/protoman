<?php


class Response
{
    public static $context = array();
    public static $content = '';
    private static $blocks = array();
    
    private static $template_level = 0;
    private static $render_stack = array();
    
    public static $log = array();
    
    public static function error404($querystring)
    {
        Response::$context['querystring'] = $querystring;
        
        Response::renderTemplate('error404.php');
    }
    
    public static function renderTemplate()
    {
        $args = func_get_args();
        Response::$log[] = "Rendering template: " . var_export($args, true);
        
        //ob_start();
        eval(' ?>' . Response::parseTemplate($args) . '<?php ');
        $output = ob_get_contents();
        //ob_clean();
        
        Response::$content .= $output;
    }
    
    public static function parseTemplate($args)
    {
        $template = Response::parseBlocks(Response::loadTemplate($args));
        
        $extends = array();
        preg_match_all('/{{ extends (?<template_name>[^ ]+) }}/ms', $template, $extends);
        
        foreach ($extends['template_name'] as $idx => $template_name)
        {
            $template = str_replace($extends[0][$idx], '', $template);
            
            $template = Response::parseTemplate(explode('/', $template_name));
        }
        
        return $template;
    }
    
    private static function parseBlocks($template)
    {
        $blocks = array();
        $count = preg_match_all('/{{ block (?P<block_name>[^ ]+) }}(?P<content>.*){{ endblock (?P=block_name) }}/ms', $template, $blocks);
        
        if (!$count)
        {
            return $template;
        }
        
        foreach ($blocks['block_name'] as $idx => $block_name)
        {
            $content = $blocks['content'][$idx];
            
            if (Response::$blocks[$block_name])
            {
                $content = str_replace('{{ super }}', $content, Response::$blocks[$block_name]);
            }
            
            $template = str_replace($blocks[0][$idx], $content, $template);
            
            Response::$blocks[$block_name] = $content;
        }
        
        return Response::parseBlocks($template);
    }
    
    private static function loadTemplate($args)
    {
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
        
        $template = false;
        
        foreach ($path_parts as $parts)
        {
            $path = implode('/', $parts);
            
            if (is_file($path))
            {
                $template = file_get_contents($path);
                
                break;
            }
        }
        
        if ($template === false)
        {
            throw new Exception("Attempted to render nonexistent template: " . print_r($args, true));
        }
        
        return $template;
    }
}
