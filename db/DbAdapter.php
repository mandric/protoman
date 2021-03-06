<?php


interface DbAdapter
{
    public static function connect();
    
    public static function select($query);
    public static function insert($query);
    public static function delete($query);
    
    public static function create($query);
    public static function drop($query);
    
    public static function insert_id();
}
