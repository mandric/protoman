<?php


if (php_sapi_name() == 'cli' && (!in_array('REMOTE_ADDR', array_keys($_SERVER)) || empty($_SERVER['REMOTE_ADDR'])) )
{
    if ( ($_SERVER['argc'] > 1) && ($_SERVER['argv'][1] == 'test') )
    {
        define('TEST', true);
    }
}

if (defined('TEST') && TEST)
{
    $db = $dbs['test'];
}
else
{
    if (DEBUG)
    {
        $db = $dbs['dev'];
    }
    else
    {
        $db = $dbs['prod'];
    }
}

//define('MC_HOST', 'localhost');
//define('MC_PORT', '11211');
// Or...
//define('MC_PIDFILE', 'unix:///var/run/memcached/memcached.sock');

define('DB_TYPE', $db['type']);
define('DB_HOST', $db['host']);
define('DB_USER', $db['user']);
define('DB_PASS', $db['pass']);
define('DB_NAME', $db['name']);

require_once("../framework.php");
