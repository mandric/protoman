<?php


require_once('settings.php');
require_once('db/Saveable.php');


class User extends Saveable
{
    protected $name = '';
}


class Thing extends Saveable
{
    protected $plural_name = 'thingies';
    
    protected $users = array();
}


class Stuff extends Saveable
{
    protected $thing = 0;
}


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
#$t1->delete();
#$u2->delete();
#$s1->delete();
