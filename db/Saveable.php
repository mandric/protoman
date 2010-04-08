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
            // TODO: Determine if default dirty value can work for all-m2m models
            //$this->dirty = false;
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
                        $this->$key->populate($ovars[$key]);
                    }
                    else if ($this->$key->default)
                    {
                        $this->$key->populate($this->$key->default);
                    }
                }
                else
                {
                    throw new Exception("Bad type definition on {$this->type}");
                }
            }
        }
    }
    
    public function toString()
    {
        return "{$this->type} id " . $this->id->get();
    }
    
    public function load($id = false)
    {
        $id = call_user_func(array(Query::$db_class, 'escape_string'), trim( ($id) ? $id : $this->id->get() ));
        
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
            
            $q = new Query($this->type);
            $q->filter('id', '=', $id);
            $results = $q->run(true);
            
            if (count($results))
            {
                foreach ($results[0] as $key => $value)
                {
                    $this->$key = $value;
                }
                
                $this->dirty = false;
                
                return true;
            }
            
            throw new Exception("Bad load call on type {$this->type} with id {$id}");
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
            if ( is_object($this->$key) && is_a($this->$key, 'Type') )
            {
                $previous = $this->$key->get();
                $this->$key->set($value);
                
                if ($previous != $this->$key->get())
                {
                    $this->dirty = true;
                }
            }
            else if ($this->$key != $value)
            {
                $this->$key = $value;
                $this->dirty = true;
            }
        }
        
        return true;
    }
    
    public static function getFields($obj)
    {
        $fields = array();
        
        if (is_object($obj))
        {
            $vars = get_object_vars($obj);
            
            foreach ($vars as $key => $var)
            {
                if (is_object($var) && is_a($var, 'Type'))
                {
                    $fields[$key] = $var;
                }
            }
        }
        else
        {
            return get_class_vars($obj);
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
        
        if (!call_user_func(array(Query::$db_class, 'delete'), $delete) && DEBUG)
        {
            throw new Exception("Failed to unjoin with query {$delete}");
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
                    throw new Exception("Validation exception on field {$key}, class {$this->type}");
                }
            }
            
            if (method_exists($value, 'save'))
            {
                $value->save();
            }
            
            if (method_exists($value, 'databaseValue'))
            {
                $dbkeys[] = "`{$key}`";
                $dbvals[] = $value->databaseValue();
            }
        }
        
        $id = $this->id->get();
        
        if ($id)
        {
            $pairs = array();
            
            foreach ($dbkeys as $idx => $key)
            {
                $pairs[] = "{$key} = {$dbvals[$idx]}";
            }
            
            $pairs = implode(',', $pairs);
            
            $update = "update `{$this->type}` set {$pairs} where `id`={$id}";
            
            if (call_user_func(array(Query::$db_class, 'update'), $update))
            {
                return $id;
            }
        }
        else
        {
            $dbkeys = implode(',', $dbkeys);
            $dbvals = implode(',', $dbvals);
            
            $insert = "insert into `{$this->type}` ({$dbkeys}) values ({$dbvals})";
            
            if (call_user_func(array(Query::$db_class, 'insert'), $insert))
            {
                $this->id->populate(call_user_func(array(Query::$db_class, 'insert_id')));
                
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
            throw new Exception("Saving failed on object of type {$this->type} with id " . $this->id->get());
        }
        else
        {
            return false;
        }
    }
    
    public function delete()
    {
        $id = call_user_func(array(Query::$db_class, 'escape_string'), $this->id->get());
        $delete = "delete from `{$this->type}` where id='$id'";
        
        if (call_user_func(array(Query::$db_class, 'delete'), $delete))
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
                    call_user_func(array(Query::$db_class, 'delete'), $delete);
                }
                catch (Exception $e)
                {
                    // May fail for various reasons; this could use actual testing.
                    throw new Exception("Assoc deletion failed on {$this->type} id " . $this->id->get() . ": " . $e->getMessage());
                }
            }
            
            if (Saveable::$singlerels[$this->type])
            {
                foreach (Saveable::$singlerels[$this->type] as $target_type)
                {
                    $update = "update `{$target_type}` set `{$this->type}`=0 where `{$this->type}`='" . $this->id->get() . "'";
                    call_user_func(array(Query::$db_class, 'update'), $update);
                }
            }
            
            if (!MC_ENABLED || !Saveable::$memcache->set($this->type . $this->id, $this, false, 30))
            {
                unset(self::$instances[$this->type][$this->id->get()]);
            }
            
            $this->id->populate(0);
            
            return true;
        }
        
        if (DEBUG)
        {
            throw new Exception("Bad call to delete for object of type {$this->type} with id " . $this->id->get());
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
}
