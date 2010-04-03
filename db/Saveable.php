<?php



abstract class Saveable
{
    protected $id = array('IntegerField', 'auto_increment' => true, 'hidden' => true);
    
    protected $type = '';
    protected $dirty = true;
    protected $is_saveable = true;
    protected $cache_index = false;
    
    public static $subclasses = array();
    
    private static $instances = array();
    private static $singlerels = array();
    private static $manyrels = array();
    
    private $ignore = array('dirty', 'ignore', 'type', 'instances', 'subclasses', 'singlerels', 'manyrels', 'is_saveable', 'cache_index');
    
    public function __construct($values = array())
    {
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
                $this->$key = $value;
            }
        }
        else if ( is_numeric($values) && ($values > 0) )
        {
            $this->load($values);
            
            Cache::set($this->cache_index, $this);
        }
        else
        {
            $this->dirty = false;
        }
        
        $ovars = get_object_vars($this);
        
        foreach (get_class_vars($this->type) as $key => $value)
        {
            if (!in_array($key, $this->ignore))
            {
                if (is_array($value))
                {
                    $value_type = $value[0];
                    $this->$key = new $value_type($this, $key, array_slice($value, 1));
                    
                    if ( $ovars[$key] && ($ovars[$key] != $value) )
                    {
                        $this->$key->set($ovars[$key]);
                    }
                }
                else
                {
                    trigger_error("Bad type definition on {$this->type}", E_USER_WARNING);
                }
            }
        }
    }
    
    public function load($id = false)
    {
        $id = mysql_escape_string(trim( ($id) ? $id : $this->id->get() ));
        
        if ($id)
        {
            $this->cache_index = $this->type . $id;
            
            $cached = Cache::get($this->cache_index);
            
            if ($cached)
            {
                $values = get_object_vars($cached);
                
                foreach ($values as $key => $value)
                {
                    if (is_object($value) && is_a($value, 'Type'))
                    {
                        $this->$key = $value->get();
                    }
                    else
                    {
                        $this->$key = $value;
                    }
                }
                
                return true;
            }
            
            $values = "select * from `{$this->type}` where `id`='{$id}'";
            $values = mysql_query($values);
            
            if ($values = mysql_fetch_assoc($values))
            {
                foreach ($values as $key => $value)
                {
                    $this->$key = $value;
                }
                
                $this->dirty = false;
                
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
    
    public static function getFields($obj)
    {
        $fields = array();
        
        $vars = get_object_vars($obj);
        
        foreach ($vars as $key => $var)
        {
            if (is_object($var) && is_a($var, 'Type'))
            {
                $fields[$key] = $var;
            }
        }
        
        return $fields;
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
        
        $delete = "delete from `{$join_table}` where `{$this->type}_id` = '" . $this->id->get() . "'";
        
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
        
        $delete = "delete from `{$join_table}` where `{$this->type}_id` = '" . $this->id->get() . "' and `{$object->type}_id` = '{$object->id}'";
        
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
            
            Cache::set($this->cache_index, $this);
        }
        
        if (isset($object->$name))
        {
            foreach ($object->$name as $key => $obj)
            {
                if ($obj->id == $this->id->get())
                {
                    unset($object->$name[$key]);
                }
            }
            
            Cache::set($this->cache_index, $this);
        }
    }
    
    public function save()
    {
        if (!$this->dirty)
        {
            return $this->id->get();
        }
        
        $values = get_object_vars($this);
        
        foreach ($this->ignore as $name)
        {
            unset($values[$name]);
        }
        
        $joins = array();
        $dbkeys = array();
        $dbvals = array();
        
        foreach ($values as $key => $value)
        {
            if (method_exists($value, 'validate'))
            {
                if (!$value->validate())
                {
                    // TODO: Throw exception on validation failure?
                    trigger_error("Validation exception on field {$key}, class {$this->type}", E_USER_WARNING);
                }
            }
            
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
            if (!$this->id->get())
            {
                $this->id->set(mysql_insert_id());
                
                if (!$this->id->get() && DEBUG)
                {
                    throw new Exception("Failed to populate id!");
                }
            }
            
            $vars = get_object_vars($this);
            
            foreach ($vars as $key => $value)
            {
                if (is_object($this->$key) && is_a($this->$key, 'Type'))
                {
                    $this->$key->source_id = $this->id->get();
                }
            }
            
            $this->cache_index = $this->type . $this->id->get();
            
            Cache::set($this->cache_index, $this);
            
            return $this->id->get();
        }
        
        if (DEBUG)
        {
            throw new Exception("Saving failed on object of type {$this->type} with id " . $this->id->get() . ": " . mysql_error());
        }
        else
        {
            return false;
        }
    }
    
    public function delete()
    {
        $id = mysql_real_escape_string($this->id->get());
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
                    
                    $delete = "delete from `{$join_table}` where `{$this->type}_id`='" . $this->id->get() . "'";
                    mysql_query($delete);
                }
                catch (Exception $e)
                {
                    // May fail for various reasons; this could use actual testing.
                    trigger_error("Assoc deletion failed on {$this->type} id " . $this->id->get() . ": " . $e->getMessage(), E_USER_WARNING);
                }
            }
            
            if (Saveable::$singlerels[$this->type])
            {
                foreach (Saveable::$singlerels[$this->type] as $target_type)
                {
                    $update = "update `{$target_type}` set `{$this->type}`=0 where `{$this->type}`='" . $this->id->get() . "'";
                    mysql_query($update);
                }
            }
            
            if (!MC_ENABLED || !Saveable::$memcache->set($this->type . $this->id, $this, false, 30))
            {
                unset(self::$instances[$this->type][$this->id->get()]);
            }
            
            $this->id->set(0);
            
            return true;
        }
        
        if (DEBUG)
        {
            throw new Exception("Bad call to delete for object of type {$this->type} with id " . $this->id->get() . ": " . mysql_error());
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
