<?php


new Route(array(
    '/test/path/' => array('AppController', 'noargsConMethod'),
    '/test/path/(\d+)' => array('AppController', 'TestConMethod'),
    '/test/path/(\d+)/(\w+)' => array('AppController', 'wordMethod'),
    ));
