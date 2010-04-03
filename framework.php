<?php


define('FRAMEWORK_PATH', substr(__FILE__, 0, strrpos(__FILE__, '/') + 1));
define('FRAMEWORK_APPS_PATH', FRAMEWORK_PATH . 'apps/');

session_start();

(mysql_connect(DB_HOST, DB_USER, DB_PASS) && mysql_select_db(DB_NAME))
 || die("Connection error: " . mysql_error()) ;


$mc = false;

try
{
    $memcache = new Memcache;
    
    if (defined('MC_PIDFILE'))
    {
        $mc = $memcache->connect(MC_PIDFILE, 0);
    }
    else if ( defined('MC_HOST') && defined('MC_PORT') )
    {
        $mc = $memcache->connect(MC_HOST, MC_PORT);
    }
}
catch (Exception $e)
{
    if (DEBUG)
    {
        trigger_error($e->getMessage(), E_USER_WARNING);
    }
}

define('MC_ENABLED', $mc);


function __autoload($class_name)
{
    if (DEBUG)
    {
        trigger_error("Classes extending Saveable MUST be loaded without relying on __autoload(...) [$class_name]", E_USER_WARNING);
    }
}


$loadables = array(
    'types.php',
    'classes.php',
    'controller.php',
    'routes.php',
    );


// The order here is important!  Changing it could break loading.
require_once('db/Saveable.php');
require_once('base/Controller.php');
require_once('base/Framework.php');
require_once('base/Request.php');
require_once('base/Response.php');

$builtin_apps = array(
    'db_types',
    );

$apps = array_merge($builtin_apps, $apps);

foreach ($loadables as $filename)
{
    foreach ($apps as $app)
    {
        $loadable = $app . "/" . $filename;
        
        $import = false;
        
        if (is_file($loadable))
        {
            $import = true;
        }
        else if (is_file(FRAMEWORK_APPS_PATH . $loadable))
        {
            $import = true;
            $loadable = FRAMEWORK_APPS_PATH . $loadable;
        }
        
        if ($import)
        {
            require_once($loadable);
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
    
    $obj = new $classname(false, false);
    
    if (is_subclass_of($obj, 'Saveable'))
    {
        Saveable::$subclasses[$obj->plural_name] = $obj->type;
    }
    else if (is_a($obj, 'Type'))
    {
        Framework::$types[] = $classname;
    }
    else if (is_subclass_of($obj, 'Controller'))
    {
        Framework::$controllers[$classname] = array();
        
        foreach (get_class_methods($classname) as $method)
        {
            Framework::$controllers[$classname][] = strtolower($method);
        }
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

Controller::process($_SERVER['QUERY_STRING']);

echo Response::$content;

/*
if (DEBUG)
{
    echo "<hr /><pre>";
    
    echo "Routes: ";
    print_r(Framework::$routes);
    
    echo "Types: ";
    print_r(Framework::$types);
    
    echo "Controllers: ";
    print_r(Framework::$controllers);
    
    echo "Request params: ";
    print_r(Request::$params);
    
    echo "Response context: ";
    print_r(Response::$context);
    
    echo "</pre>";
}
*/
