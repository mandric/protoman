<?php


class Mysql implements DbAdapter
{
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
    
    public static function query($query)
    {
        $results = array();
        
        $rows = mysql_query($query, Query::$db_conn);
        
        while ($row = mysql_fetch_assoc($rows))
        {
            $results[] = $row;
        }
        
        return $results;
    }
}
