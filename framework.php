<?php


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
require_once('base/Type.php');
require_once('db/Saveable.php');
require_once('base/Controller.php');
require_once('base/Route.php');


foreach ($loadables as $filename)
{
    foreach ($apps as $app)
    {
        $loadable = $app . "/" . $filename;
        
        if (is_file($loadable))
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
    else if (is_subclass_of($obj, 'Type'))
    {
        Type::$types[] = $classname;
    }
    else if (is_subclass_of($obj, 'Controller'))
    {
        Controller::$controllers[$classname] = array();
        
        foreach (get_class_methods($classname) as $method)
        {
            Controller::$controllers[$classname][] = strtolower($method);
        }
    }
}


$querystring = $_GET['q'];

Controller::process($querystring);


if (DEBUG)
{
    echo "<hr><pre>";
    echo "Routes: ";
    print_r(Route::$routes);
    echo "Types: ";
    print_r(Type::$types);
    echo "Controllers: ";
    print_r(Controller::$controllers);
    echo "GET values: ";
    print_r($_GET);
    echo "</pre>";
}
