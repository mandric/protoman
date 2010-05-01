<?php


if (DEBUG)
{
    $db = $dbs['dev'];
}
else
{
    $db = $dbs['prod'];
}


require_once('../init_framework.php');


if (php_sapi_name() == 'cli' && (!in_array('REMOTE_ADDR', array_keys($_SERVER)) || empty($_SERVER['REMOTE_ADDR'])) )
{
    define('CLI', true);
    Cli::preprocess($_SERVER['argv']);
}

if (defined('TEST') && TEST)
{
    $db = $dbs['test'];
}

define('DB_TYPE', $db['type']);
define('DB_HOST', $db['host']);
define('DB_USER', $db['user']);
define('DB_PASS', $db['pass']);
define('DB_NAME', $db['name']);


require_once("../framework.php");
