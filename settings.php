<?php


define('DEBUG', false);

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
define('MC_PIDFILE', 'unix:///var/run/memcached/memcached.sock');

define('DB_HOST', $db['host']);
define('DB_USER', $db['user']);
define('DB_PASS', $db['pass']);
define('DB_NAME', $db['name']);


session_start();

(mysql_connect(DB_HOST, DB_USER, DB_PASS) && mysql_select_db(DB_NAME))
 || die("Connection error: " . mysql_error()) ;

function __autoload($class_name)
{
    trigger_error("Classes extending Saveable MUST be loaded without relying on __autoload(...)", E_USER_WARNING);
}
