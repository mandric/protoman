<?php


$noargs = array('AppController', 'noargsConMethod');
$testcon = array('AppController', 'TestConMethod');
$word = array('AppController', 'wordMethod');

$login_required = array('AuthController', 'loginRequired');


new Route(array(
    '/test/path/' => array($login_required, $noargs),
    '/test/path/(\d+)' => array($testcon),
    '/test/path/(\d+)/(\w+)' => array($word),
    ));
