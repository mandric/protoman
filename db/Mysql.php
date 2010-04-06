<?php


class Mysql implements DbAdapter
{
    public static function insert_id()
    {
        return mysql_insert_id();
    }
    
    // TODO: m2m support w/joins
    public static function connect()
    {
        Query::$db_conn = mysql_connect(DB_HOST, DB_USER, DB_PASS);
        
        if (!Query::$db_conn)
        {
            throw new Exception("Failed to connect to mySQL database on " . DB_HOST);
        }
        
        if (!mysql_select_db(DB_NAME))
        {
            throw new Exception("Failed to connect to mySQL database " . DB_NAME);
        }
        
        return true;
    }
    
    public static function escape_string($string)
    {
        return mysql_real_escape_string($string);
    }
    
    public static function select($query)
    {
        $results = array();
        
        $rows = mysql_query($query, Query::$db_conn);
        
        while ($row = mysql_fetch_assoc($rows))
        {
            $results[] = $row;
        }
        
        return $results;
    }
    
    public static function delete($query)
    {
        return Mysql::query($query);
    }
    
    public static function insert($query)
    {
        return Mysql::query($query);
    }
    
    public static function update($query)
    {
        return Mysql::query($query);
    }
    
    private static function query($query)
    {
        return mysql_query($query, Query::$db_conn);
    }
}
