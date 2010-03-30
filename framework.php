<?php


session_start();

(mysql_connect(DB_HOST, DB_USER, DB_PASS) && mysql_select_db(DB_NAME))
 || die("Connection error: " . mysql_error()) ;


$loadables = array(
    'types.php',
    'classes.php',
    'controller.php',
    'routes.php',
    );


require_once('db/Saveable.php');
require_once('base/Type.php');
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

function __autoload($class_name)
{
    if (DEBUG)
    {
        trigger_error("Classes extending Saveable MUST be loaded without relying on __autoload(...) [$class_name]", E_USER_WARNING);
    }
}


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
echo $mc;
