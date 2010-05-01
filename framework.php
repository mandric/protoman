<?php


require_once('db/adapters/' . DB_TYPE . '.php');

call_user_func(array(DB_TYPE, 'connect'));


if (is_file('routes.php'))
{
    require_once('routes.php');
}

$builtin_apps = array(
    'db_types',
    );

$test_apps = array_merge($apps);

$apps = array_merge($builtin_apps, $apps);

foreach ($apps as $app)
{
    Saveable::$apps[$app] = array();
}

Framework::$apps = $apps;

function get_loadable_path($app, $filename)
{
    $loadable = $app . "/" . $filename;
    
    if (is_file($loadable))
    {
        return $loadable;
    }
    else if (is_file(FRAMEWORK_APPS_PATH . $loadable))
    {
        return FRAMEWORK_APPS_PATH . $loadable;
    }
    
    return false;
}

$class_ct = count(get_declared_classes());

foreach ($loadables as $filename)
{
    foreach ($apps as $app)
    {
        $loadable = get_loadable_path($app, $filename);
        
        if ($loadable)
        {
            require_once($loadable);
        }
        
        if ($filename == 'classes.php')
        {
            $declared = array_slice(get_declared_classes(), $class_ct);
            
            foreach ($declared as $classname)
            {
                $classname = strtolower($classname);
                
                if (is_subclass_of($classname, 'Saveable'))
                {
                    Saveable::$subclasses[] = $classname;
                    
                    Saveable::$apps[$app][] = $classname;
                }
                else if (is_subclass_of($classname, 'Controller'))
                {
                    Framework::$controllers[$classname] = array();
                    
                    foreach (get_class_methods($classname) as $method)
                    {
                        Framework::$controllers[$classname][] = strtolower($method);
                    }
                }
                else
                {
                    $reflector = new ReflectionClass($classname);
                    
                    if ($reflector->implementsInterface('Type'))
                    {
                        Framework::$types[] = $classname;
                    }
                }
            }
            
            $class_ct += count($declared);
        }
    }
}


$declared = get_declared_classes();
$post_saveable = false;

foreach ($declared as $classname)
{
    $classname = strtolower($classname);
    
    if (!$post_saveable)
    {
        if ($classname == 'saveable')
        {
            $post_saveable = true;
        }
        
        continue;
    }
    
}


Request::$params = &$_REQUEST;
Request::$session = &$_SESSION;
Request::$get = &$_GET;
Request::$post = &$_POST;
Request::$files = &$_FILES;
Request::$cookie = &$_COOKIE;
Request::$server = &$_SERVER;
Request::$env = &$_ENV;


if (defined('CLI') && CLI)
{
    Cli::process($test_apps);
}
else
{
    // Processing a web request
    Controller::process($_SERVER['QUERY_STRING']);
    
    if (DEBUG)
    {
        Response::$content .= "<hr /><pre>" . print_r(Query::$queries, true) . "</pre>";
    }
}


echo Response::$content;
