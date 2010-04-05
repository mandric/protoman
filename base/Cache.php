<?php


// TODO: Handle this better than extending Saveable just to get access to ::$instances?
class Cache
{
    protected static $memcache = false;
    protected static $internal = array();
    
    public static function get($index)
    {
        if (MC_ENABLED)
        {
            $mc = Cache::$memcache->get($index);
            
            if ($mc)
            {
                return $mc;
            }
        }
        
        if (Cache::$internal[$index])
        {
            return Cache::$internal[$index];
        }
        
        return false;
    }
    
    public static function delete($index)
    {
        if (MC_ENABLED)
        {
            $mc = Cache::$memcache->delete($index);
            
            if ($mc)
            {
                return $mc;
            }
        }
        
        if (Cache::$internal[$index])
        {
            unset(Cache::$internal[$index]);
            return true;
        }
        
        return false;
    }
    
    public static function set($index, $value)
    {
        if (MC_ENABLED)
        {
            if (Cache::$memcache->set($index, $value, false, 30))
            {
                return true;
            }
        }
        
        if (Cache::$internal[$index] = $value)
        {
            return true;
        }
        
        return false;
    }
}
