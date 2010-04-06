<?php


interface DbAdapter
{
    public static function connect();
    public static function query($query);
}
