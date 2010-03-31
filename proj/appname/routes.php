<?php


$noargs = array('AppController', 'noargsConMethod');
$testcon = array('AppController', 'TestConMethod');
$word = array('AppController', 'wordMethod');
$input = array('AppController', 'inputMethod');

$login_required = array('AuthController', 'loginRequired');


new Route(array(
    '/input/' => array($input),
    '/test/path/' => array($login_required, $noargs),
    '/test/path/(?P<id>\d+)' => array($testcon),
    '/test/path/(?P<id>\d+)/(?P<slug>\w+)' => array($word),
    ));
