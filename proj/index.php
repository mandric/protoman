<?php


define('SITE_NAME', "Test Site");

$apps = array(
    'auth',
    'appname',
    );


require_once('settings.php');

// Leftover ORM tests.
/*
$u1 = new User();
$u1->name = 'Bob';
$u1->save();

$u2 = new User();
$u2->name = 'James';
$u2->save();

$t1 = new Thing();
$t1->save();
$t1->users[] = $u2;
$t1->users[] = $u1;

$s1 = new Stuff();
$s1->thing = $t1;
$s1->save();

$u1->delete();
$t1->delete();
$u2->delete();
$s1->delete();
*/
