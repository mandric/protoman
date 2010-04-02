<?php


$test_orm = array('AppController', 'testOrm');
$testcon = array('AppController', 'TestConMethod');
$word = array('AppController', 'wordMethod');
$input = array('AppController', 'inputMethod');

$login_required = array('AuthController', 'loginRequired');


new Route(array(
    '/input/' => array($input),
    '/test/orm/' => array($test_orm),
    '/test/path/(?P<id>\d+)' => array($testcon),
    '/test/path/(?P<id>\d+)/(?P<slug>\w+)' => array($word),
    ));
