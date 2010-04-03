<?php


$class_listing = array('AdminController', 'classListing');
$object_listing = array('AdminController', 'objectListing');
$object_form = array('AdminController', 'objectForm');

$login_required = array('AuthController', 'loginRequired');


new Route('admin', array(
    //'/' => array($login_required, $class_listing),
    '/(?P<class>\w+)' => array($object_listing),
    '/(?P<class>\w+)/add' => array($object_form),
    '/(?P<class>\w+)/(?P<id>\d+)' => array($object_form),
    ));
