<?php


class Thing extends Saveable
{
    protected $plural_name = 'thingies';
    
    protected $users = array();
}


class Stuff extends Saveable
{
    protected $thing = 0;
}
