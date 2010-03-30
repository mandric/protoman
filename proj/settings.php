<?php


define('DEBUG', true);

$db = array();

if (true)
{
 $db['host'] = 'localhost';
 $db['user'] = 'store_debug';
 $db['pass'] = 'store_debug!pass';
 $db['name'] = 'store_debug';
}
else
{
 $db['host'] = 'localhost';
 $db['user'] = 'store_prod';
 $db['pass'] = 'store_prod!pass';
 $db['name'] = 'store_prod';
}

//define('MC_HOST', 'localhost');
//define('MC_PORT', '11211');
// Or...
//define('MC_PIDFILE', 'unix:///var/run/memcached/memcached.sock');

define('DB_HOST', $db['host']);
define('DB_USER', $db['user']);
define('DB_PASS', $db['pass']);
define('DB_NAME', $db['name']);

require_once("../framework.php");
