<?php


$test_orm = array('AppController', 'testOrm');
$testcon = array('AppController', 'TestConMethod');
$word = array('AppController', 'wordMethod');

$login_required = array('AuthController', 'loginRequired');


new Route('appname', array(
    '/test/orm/' => array($test_orm),
    '/test/creation' => array($testcon),
    '/test/path/(?P<id>\d+)/(?P<slug>\w+)' => array($word),
    ));
