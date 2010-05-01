<?php


define('FRAMEWORK_PATH', substr(__FILE__, 0, strrpos(__FILE__, '/') + 1));
define('FRAMEWORK_APPS_PATH', FRAMEWORK_PATH . 'apps/');

session_start();


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
        throw new Exception($e->getMessage());
    }
}
define('MC_ENABLED', $mc);


function __autoload($class_name)
{
    if (DEBUG)
    {
        throw new Exception("Classes extending Saveable MUST be loaded without relying on __autoload(...) [$class_name]");
    }
}


$loadables = array(
    'types.php',
    'classes.php',
    'controller.php',
    'routes.php',
    );


// The order here is important!  Changing it could break loading.
require_once('db/Query.php');
require_once('db/DbAdapter.php');
require_once('db/Saveable.php');

require_once('base/Cache.php');
require_once('base/Controller.php');
require_once('base/Cli.php');
require_once('base/Framework.php');
require_once('base/Request.php');
require_once('base/Response.php');
