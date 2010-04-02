<?php



abstract class Saveable
{
    protected $id = 0;
    
    protected $type = '';
    protected $dirty = true;
    protected $is_saveable = true;
    
    public static $subclasses = array();
    
    private static $instances = array();
    private static $singlerels = array();
    private static $manyrels = array();
    
    private $ignore = array('dirty', 'ignore', 'type', 'instances', 'subclasses', 'singlerels', 'manyrels', 'is_saveable');
    private static $memcache = false;
    
    public function __construct($values = array())
    {
        if (MC_ENABLED)
        {
            global $memcache;
            Saveable::$memcache = $memcache;
        }
        
        $this->type = strtolower(get_class($this));
        /*
        if ($build_list && !count(Saveable::$subclasses))
        {
            $plural_names = array_keys(Saveable::$subclasses);
            
            foreach (Saveable::$subclasses as $plural_name => $type_name)
            {
                $obj = new $type_name();
                
                foreach (get_object_vars($obj) as $var_name => $default)
                {
                    if (in_array($var_name, $plural_names))
                    {
                        $target_type_name = Saveable::$subclasses[$var_name];
                        
                        if (!Saveable::$manyrels[$target_type_name])
                        {
                            Saveable::$manyrels[$target_type_name] = array();
                        }
                        
                        Saveable::$manyrels[$target_type_name][] = $type_name;
                    }
                    else if (in_array($var_name, Saveable::$subclasses))
                    {
                        if (!Saveable::$singlerels[$var_name])
                        {
                            Saveable::$singlerels[$var_name] = array();
                        }
                        
                        Saveable::$singlerels[$var_name][] = $type_name;
                    }
                }
            }
        }
        */
        
        if (is_array($values))
        {
            foreach ($values as $key => $value)
            {
                if (is_object($this->$key))
                {
                    $this->$key->set($value);
                }
                else
                {
                    $this->$key = $value;
                }
            }
        }
        else if ( is_numeric($values) && ($values > 0) )
        {
            $this->load($values);
        }
        else
        {
            $this->dirty = false;
        }
        
        foreach (get_object_vars($this) as $key => $value)
        {
            if (!in_array($key, $this->ignore))
            {
                if (is_array($value))
                {
                    $value_type = $value[0];
                    $this->$key = new $value_type($this, array_slice($value, 1));
                }
                else
                {
                    $this->$key = $value;
                }
            }
        }
    }
    
    public function load($id = false)
    {
        $id = mysql_escape_string(trim( ($id) ? $id : $this->id ));
        
        if ($id)
        {
            if (MC_ENABLED)
            {
                $cached = Saveable::$memcache->get($this->type . $id);
                
                if ($cached)
                {
                    $values = get_object_vars($cached);
                    
                    foreach ($values as $key => $value)
                    {
                        $this->$key = $value;
                    }
                    
                    return true;
                }
            }
            
            if (isset(self::$instances[$this->type][$id]) && is_a(self::$instances[$this->type][$id], $this->type))
            {
                $values = get_object_vars(self::$instances[$this->type][$id]);
                
                foreach ($values as $key => $value)
                {
                    $this->$key = $value;
                }
                
                return true;
            }
            
            $values = "select * from `{$this->type}` where `id`='{$id}'";
            $values = mysql_query($values);
            
            if ($values = mysql_fetch_assoc($values))
            {
                foreach ($values as $key => $value)
                {
                    if (is_object($this->$key))
                    {
                        $this->$key->set($value);
                    }
                    else
                    {
                        $this->$key = $value;
                    }
                }
                
                $this->dirty = false;
                
                $this->cache();
                
                return true;
            }
            else if (DEBUG)
            {
                trigger_error("Bad load call on type {$this->type} with id {$id}", E_USER_WARNING);
            }
            
            return false;
        }
        
        if (DEBUG)
        {
            throw new Exception("Tried to call load() with a value that evaluates to false. Type: {$this->type}");
        }
        else
        {
            return false;
        }
    }
    
    protected function loadForeign()
    {
        foreach (get_object_vars($this) as $key => $value)
        {
            if (in_array($key, $this->ignore))
            {
                continue;
            }
            
            if (is_a($value, 'MultipleRelationType'))
            {
                $this->$key = $value->retrieve();
            }
        }
        
        return true;
    }
    
    public function updateValues($values)
    {
        foreach ($values as $key => $value)
        {
            $this->__set($key, $value);
        }
        
        return true;
    }
    
    private function getTypeFromColumn($column)
    {
        $type = str_replace('_id', '', trim($column));
        $type = str_replace('_', ' ', $type);
        $type = str_replace(' ', '', ucwords($type));
        
        return $type;
    }
    
    public function unjoin($type)
    {
        if (in_array($type, array_keys($table_types)))
        {
            $type = $table_types[$type];
        }
        
        $table = array_keys($table_types, $type);
        $table = $table[0];
        
        $tables = array($this->type, $table);
        sort($tables, SORT_STRING);
        $join_table = implode('_', $tables);
        
        $delete = "delete from `{$join_table}` where `{$this->type}_id` = '{$this->id}'";
        
        if (!mysql_query($delete) && DEBUG)
        {
            throw new Exception("Failed to unjoin with query {$delete}");
        }
    }
    
    public function dissociate($object)
    {
        $name = $object->type;
        
        $tables = array($this->type, $object->type);
        sort($tables, SORT_STRING);
        $join_table = implode('_', $tables);
        
        $delete = "delete from `{$join_table}` where `{$this->type}_id` = '{$this->id}' and `{$object->type}_id` = '{$object->id}'";
        
        if (!mysql_query($delete) && DEBUG)
        {
            throw new Exception("Failed to delete relation with query {$delete}");
        }
        
        if (isset($this->$name))
        {
            foreach ($this->$name as $key => $obj)
            {
                if ($obj->id == $object->id)
                {
                    unset($this->$name[$key]);
                }
            }
            
            $this->cache();
        }
        
        if (isset($object->$name))
        {
            foreach ($object->$name as $key => $obj)
            {
                if ($obj->id == $this->id)
                {
                    unset($object->$name[$key]);
                }
            }
            
            $this->cache();
        }
    }
    
    public function save()
    {
        if (!$this->dirty) // && $this->id)
        {
            return $this->id;
        }
        
        $values = get_object_vars($this);
        
        foreach ($this->ignore as $name)
        {
            unset($values[$name]);
        }
        
        $joins = array();
        $dbkeys = array('id');
        $dbvals = array($this->id);
        
        foreach ($values as $key => $value)
        {
            if (method_exists($value, 'save'))
            {
                $value->save();
            }
            else if (method_exists($value, 'databaseValue'))
            {
                $dbkeys[] = "`{$key}`";
                $dbvals[] = $value->databaseValue();
            }
        }
        
        $dbkeys = implode(',', $dbkeys);
        $dbvals = implode(',', $dbvals);
        
        $replace = "replace into `{$this->type}` ({$dbkeys}) values ({$dbvals})";
        
        if (mysql_query($replace))
        {
            if (!$this->id)
            {
                $this->id = mysql_insert_id();
                
                if (!$this->id && DEBUG)
                {
                    throw new Exception("Failed to populate id!");
                }
            }
            
            $this->cache();
            
            return $this->id;
        }
        
        if (DEBUG)
        {
            throw new Exception("Saving failed on object of type {$this->type} with id {$this->id}: " . mysql_error());
        }
        else
        {
            return false;
        }
    }
    
    private function cache()
    {
        if (MC_ENABLED)
        {
            if (Saveable::$memcache->set($this->type . $this->id, $this, false, 30))
            {
                return true;
            }
        }
        
        if (self::$instances[$this->type][$this->id] = $this)
        {
            return true;
        }
        
        return false;
    }
    
    public function delete()
    {
        $id = mysql_real_escape_string($this->id);
        $delete = "delete from `{$this->type}` where id='$id'";
        
        if (mysql_query($delete))
        {
            $relations = array();
            
            foreach (get_object_vars($this) as $plural_name => $value)
            {
                if (is_a($value, 'ArrayProcessor') && !in_array($plural_name, $this->ignore))
                {
                    $relations[$plural_name] = Saveable::$subclasses[$plural_name];
                }
                
                if (Saveable::$manyrels[$this->type])
                {
                    foreach (Saveable::$manyrels[$this->type] as $plural_name)
                    {
                        $relations[$plural_name] = $plural_name;
                    }
                }
            }
            
            foreach ($relations as $name)
            {
                try
                {
                    // Delete from join table
                    $tables = array($this->type, $name);
                    sort($tables, SORT_STRING);
                    $join_table = implode('_', $tables);
                    
                    $delete = "delete from `{$join_table}` where `{$this->type}_id`='{$this->id}'";
                    mysql_query($delete);
                }
                catch (Exception $e)
                {
                    // May fail for various reasons; this could use actual testing.
                    trigger_error("Assoc deletion failed on {$this->type} id {$this->id}: " . $e->getMessage(), E_USER_WARNING);
                }
            }
            
            if (Saveable::$singlerels[$this->type])
            {
                foreach (Saveable::$singlerels[$this->type] as $target_type)
                {
                    $update = "update `{$target_type}` set `{$this->type}`=0 where `{$this->type}`='{$this->id}'";
                    mysql_query($update);
                }
            }
            
            if (!MC_ENABLED || !Saveable::$memcache->set($this->type . $this->id, $this, false, 30))
            {
                unset(self::$instances[$this->type][$this->id]);
            }
            
            $this->id = 0;
            
            return true;
        }
        
        if (DEBUG)
        {
            throw new Exception("Bad call to delete for object of type {$this->type} with id {$this->id}: " . mysql_error());
        }
        else
        {
            return false;
        }
    }
    
    public function &__get($name)
    {
        // In debug mode, assert that get calls only work on existing member variables
        if (!isset($this->$name) && DEBUG)
        {
            throw new Exception("Attempted to access bad property {$name} on object of type {$this->type}");
        }
        
        if (!in_array($name, $this->ignore) && is_object($this->$name))
        {
            return $this->$name->get();
        }
        
        return $this->$name;
    }
    
    public function __set($name, $value)
    {
        if (!isset($this->$name) && DEBUG)
        {
            // In debug mode, assert that set calls only work on existing member variables
            throw new Exception("Attempted to modify bad property {$name} on object of type {$this->type}");
        }
        
        $this->$name->set($value);
    }
    
    public function getOne($where = array())
    {
        return $this->get($where, true);
    }
    
    public function getAll($where = array())
    {
        return $this->get($where, false);
    }
    
    private function get($where, $one = false)
    {
        $conditions = array('1');
        
        foreach ($where as $field => $value)
        {
            $field = mysql_real_escape_string($field);
            $comp = '=';
            
            if (is_array($value))
            {
                foreach ($value as $comp => $val)
                {
                    $comp = in_array($comp, array('>', '<', '<=', '>=')) ? $comp : '=' ;
                    
                    $conditions[] = "`{$field}` {$comp} '{$val}'";
                }
            }
            else
            {
                $value = mysql_real_escape_string($value);
                
                $conditions[] = "`{$field}` {$comp} '{$value}'";
            }
        }
        
        $conditions = implode(' and ', $conditions);
        
        $records = "select id from `{$this->type}` where {$conditions} order by id asc";
        
        if ( !($records = mysql_query($records)) )
        {
            if (DEBUG)
            {
                throw new Exception("Bad get* call from {$this->type} for {$conditions}. " . mysql_error());
            }
            
            return false;
        }
        
        $type = $this->type;
        $return = ($one) ? false : array() ;
        
        while ($record = mysql_fetch_assoc($records))
        {
            if ($one)
            {
                return new $type($record['id']);
            }
            
            array_push($return, new $type($record['id']));
        }
        
        return $return;
    }
}
