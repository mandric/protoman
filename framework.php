<?php


session_start();

(mysql_connect(DB_HOST, DB_USER, DB_PASS) && mysql_select_db(DB_NAME))
 || die("Connection error: " . mysql_error()) ;


$loadables = array(
    'Types' => 'types.php',
    'Classes' => 'classes.php',
    'Controllers' => 'controller.php',
    'Routes' => 'routes.php',
    );

require_once('db/Saveable.php');


foreach ($loadables as $classname => $filename)
{
    foreach ($apps as $app)
    {
        $loadable = $app . "/" . $filename;
        
        if (is_file($loadable))
        {
            echo "$loadable<br />";
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
        $memcache->connect(MC_PIDFILE, 0);
    }
    else
    {
        $memcache->connect(MC_HOST, MC_PORT);
    }
    
    $mc = true;
}
catch (Exception $e)
{
    if (DEBUG)
    {
        trigger_error($e->getMessage(), E_USER_WARNING);
    }
}

define('MC_ENABLED', $mc);
