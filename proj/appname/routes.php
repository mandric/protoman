<?php


new Route(array(
    '/test/path/' => array('AppController', 'noargsConMethod'),
    '/test/path/(\d+)' => array('AppController', 'TestConMethod'),
    ));
