<?php


class Thing extends Saveable
{
    protected $users = array('ManyToManyField', 'User');
}


class Stuff extends Saveable
{
    protected $thing = array('ForeignKeyField', 'Thing');
}
