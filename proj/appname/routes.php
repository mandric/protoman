<?php


$test_orm = array('AppController', 'testOrm');
$test_query = array('AppController', 'testQuery');

$testcon = array('AppController', 'TestConMethod');
$word = array('AppController', 'wordMethod');

$login_required = array('AuthController', 'loginRequired');


new Route('appname', array(
    '/test/orm/' => array($test_orm),
    '/test/query/' => array($test_query),
    '/test/creation' => array($testcon),
    '/test/path/(?P<id>\d+)/(?P<slug>\w+)' => array($word),
    ));
