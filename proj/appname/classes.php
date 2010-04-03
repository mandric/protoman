<?php


class Thing extends Saveable
{
    protected $users = array('ManyToManyField', 'User', 'label' => 'User set');
}


class Stuff extends Saveable
{
    protected $thing = array('ForeignKeyField', 'Thing');
}
