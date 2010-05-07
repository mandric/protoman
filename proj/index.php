<?php


define('SITE_NAME', "Test Site");
define('DEBUG', true);


$apps = array(
    'admin',
    'auth',
    //'appname',
    'forms',
    'blog',
    );


$dbs = array(
    'dev' => array(
        'type' => 'mysql',
        'host' => 'localhost',
        'name' => 'protoproj_dev',
        'user' => 'protoproj_dev',
        'pass' => 'protoproj_dev!pass',
        ),
    'prod' => array(
        'type' => 'mysql',
        'host' => 'localhost',
        'name' => 'protoproj',
        'user' => 'protoproj',
        'pass' => 'protoproj!pass',
        ),
    'test' => array(
        'type' => 'mysql',
        'host' => 'localhost',
        'name' => 'protoproj_test',
        'user' => 'protoproj_test',
        'pass' => 'protoproj_test!pass',
        ),
    );


//define('MC_HOST', 'localhost');
//define('MC_PORT', '11211');
// Or...
//define('MC_PIDFILE', 'unix:///var/run/memcached/memcached.sock');


require_once('../framework.php');
