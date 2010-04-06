<?php


interface DbAdapter
{
    public static function connect();
    public static function query($query);
    public static function insert_id();
}
