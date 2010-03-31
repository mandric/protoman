<?php


/*
 * Usage: class Classname extends Saveable { ... }
 * 
 * All member variables MUST have defaults.
 * 
 * For joins, create a member var that's an array named for the table you're joining to, and populate it with objects.
 * Marks object dirty when values are changed; only saves dirty objects.
 * Appending objects to the correct array (e.g. User objects to 'users' array) will immediately associate in the database.
 * Plural names may be specified by subclasses and will then be used in all queries as the variable and table name.
 * 
 * Single assocations: For fields such as ->user_id, assigning a User object to ->user will have the same effect.
 * 
 * Supported calls:
 * ->save()                Cascading save of unsaved/dirty objects.
 * ->delete()            Removal of database entry corresponding to item's type and id.  Does not cascade.
 * 
 * Magic methods implemented:
 * ->__construct(values)    Creates from DB if values is an id, otherwise creates empty or with vars specified in array.
 * ->__get(name)            Returns specified member var; throws exception on bad call in debug mode.
 * ->__set(name, value)        Sets specified member var; throws exception on bad call in debug mode.  Also handles single assocations.
 * 
 * Database scheme:
 * class User maps to table user.
 * association with class Permission uses lower case table names alphabetically: join table should be permission_user.
 * single assocations should use lower cased class names followed by _id: user_id.
 * 
 */


class ArrayProcessor extends ArrayObject
{
    private $obj;
    
    public function __construct($arr, $obj)
    {
        $this->obj = $obj;
        
        parent::__construct(array());
        
        foreach ($arr as $key => $value)
        {
            $this->offsetSet($key, $value, false);
        }
    }
    
    public function append($value)
    {
        if ( is_object($value) && $value->is_saveable )
        {
            return $this->obj->associate($value);
        }
        
        return parent::append($value);
    }
    
    public function offsetSet($offset, $value, $overwrite=true)
    {
        if ( is_object($value) && $value->is_saveable )
        {
            if (is_numeric($offset))
            {
                $warning = "Assignment by offset is not supported for object types.";
                
                if ($overwrite)
                {
                    $warning .= " Removing specified index and appending passed object.";
                    
                    $this->offsetUnset($offset);
                }
                
                trigger_error($warning, E_USER_WARNING);
            }
            
            $this->obj->associate($value);
        }
        
        return parent::offsetSet($offset, $value);
    }
    
    public function offsetUnset($offset)
    {
        if ( is_object($this[$offset]) && $this[$offset]->is_saveable )
        {
            return $this->obj->dissociate($this->offsetGet($offset));
        }
        
        return parent::offsetUnset($offset);
    }
}


abstract class Saveable
{
    protected $id = 0;
    
    protected $type = '';
    protected $plural_name = '';
    protected $dirty = true;
    protected $loaded = false;
    protected $is_saveable = true;
    
    public static $subclasses = array();
    
    private static $instances = array();
    private static $singlerels = array();
    private static $manyrels = array();
    
    private $ignore = array('dirty', 'ignore', 'type', 'instances', 'subclasses', 'singlerels', 'manyrels', 'loaded', 'plural_name', 'is_saveable');
    private static $memcache = false;
    
    public function __construct($values = array(), $build_list=true)
    {
        if (MC_ENABLED)
        {
            global $memcache;
            Saveable::$memcache = $memcache;
        }
        
        $this->type = strtolower(get_class($this));
        
        if (!$this->plural_name)
        {
            $this->plural_name = $this->type . 's';
        }
        
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
        
        foreach (get_object_vars($this) as $key => $value)
        {
            if (is_array($value) && !in_array($key, $this->ignore))
            {
                $this->$key = new ArrayProcessor($value, $this);
            }
        }
        
        if (is_array($values))
        {
            foreach ($values as $key => $value)
            {
                // $key = $value within the class will bypass __set
                $this->__set($key, $value);
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
                    // $key = $value within the class will bypass __set
                    $this->__set($key, $value);
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
            
            if (is_a($value, 'ArrayProcessor'))
            {
                $this->$key = $this->getJoined(Saveable::$subclasses[$key]);
            }
        }
        
        $this->loaded = true;
        
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
    
    private function getJoined($type)
    {
        $return = new ArrayProcessor(array(), $this);
        
        $types = array($this->type, $type);
        sort($types, SORT_STRING);
        $join_table = implode('_', $types);
        
        $self_column = $this->getColumn();
        $object_column = $type . '_id';
        
        $joins = "
            select distinct t.id from `{$type}` t
            join `{$join_table}` j on j.`{$object_column}` = t.`id`
            where j.`{$this->type}_id` = '{$this->id}' 
            order by t.id asc
            ";
        
        $joins = mysql_query($joins);
        
        if ($joins && mysql_numrows($joins))
        {
            while ($record = mysql_fetch_assoc($joins))
            {
                // Built from record but didn't load children; used t.* in query:
            //    array_push($return, new $type($record));
                $record = new $type($record['id']);
                
                if ($record->id)
                {
                    array_push($return, $record);
                }
            }
        }
        
        return $return;
    }
    
    private function getTypeFromColumn($column)
    {
        $type = str_replace('_id', '', trim($column));
        $type = str_replace('_', ' ', $type);
        $type = str_replace(' ', '', ucwords($type));
        
        return $type;
    }
    
    private function getColumn()
    {
        return ($this->type) ? $this->type : $this->type . 's';
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
    
    public function associate($object)
    {
        if (!$this->id)
        {
            trigger_error("Called associate on an unsaved object; saving automatically.", E_USER_WARNING);
            $this->save();
        }
        
        $name = $object->type;
        
        $tables = array($this->type, $object->type);
        sort($tables, SORT_STRING);
        $join_table = implode('_', $tables);
        
        $pairs = "`{$this->type}_id` = '{$this->id}', `{$object->type}_id` = '{$object->id}'";
        $replace = "replace into `{$join_table}` set {$pairs}";
        
        if (!mysql_query($replace) && DEBUG)
        {
            throw new Exception("Failed to save relation with query {$replace}");
        }
        
        if ($this->loaded && isset($this->$name))
        {
            $this->$name->append($object);
            
            $this->cache();
        }
        
        if ($object->loaded && isset($object->$name))
        {
            $object->$name->append($object);
            
            $this->cache();
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
        
        if ($this->loaded && isset($this->$name))
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
        
        if ($object->loaded && isset($object->$name))
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
        $pairs = array();
        
        foreach ($values as $key => $value)
        {
            // If the value is an array, update it as a *-to-many join
            if (is_array($value))
            {
                $joins[] = $value;
            }
            else
            {
                $key = mysql_real_escape_string($key);
                
                // If this is a flag, change it to 1 or 0
                if (is_bool($value))
                {
                    $value = ($value) ? 1 : 0 ;
                }
                else if (is_object($value))
                {
                    continue;
                }
                else
                {
                    $value = mysql_real_escape_string($value);
                }
                
                $pairs[] = "`{$key}` = '{$value}'";
            }
        }
        
        $pairs = implode(', ', $pairs);
        
        $replace = "replace into `{$this->type}` set {$pairs}";
        
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
            
            if ($this->loaded)
            {
                foreach ($values as $key => $value)
                {
                    if (is_object($value))
                    {
                        if ($value->dirty) // || !$value->id)
                        {
                            $value->save();
                        }
                        
                        $key .= '_id';
                        $this->$key = $value->id;
                    }
                }
                
                foreach ($joins as $join)
                {
                    $ids = array();
                    $table = '';
                    
                    foreach ($join as $key => $object)
                    {
                        if (is_object($object))
                        {
                            if ($object->dirty) // || !$object->id)
                            {
                                $object->save();
                            }
                            
                            $this->associate($object);
                            
                            $ids[] = $object->id;
                            $table = $object->type;
                        }
                        else if (DEBUG)
                        {
                            throw new Exception(
                                "Tried to save a relation without an object from member {$key} in object of type {$this->type} with id {$this->id}."
                                );
                        }
                    }
                    
                    if ($table)
                    {
                        $tables = array($this->type, $object->type);
                        sort($tables, SORT_STRING);
                        $join_table = implode('_', $tables);
                        
                        $ids = "'" . implode("', '", $ids) . "'";
                        $delete = "delete from `{$join_table}` where `{$this->type}_id` = '{$this->id}' and `{$table}_id` not in ({$ids})";
                        
                        if (!mysql_query($delete) && DEBUG)
                        {
                            throw new Exception("Failed to delete relation with query {$delete}");
                        }
                    }
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
        if (!$this->loaded)
        {
            $this->loadForeign();
        }
        
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
        if ( !$this->loaded && in_array($name, array_merge(Saveable::$subclasses, array_keys(Saveable::$subclasses))) )
        {
            $this->loadForeign();
        }
        
        // In debug mode, assert that get calls only work on existing member variables
        if (!isset($this->$name) && DEBUG)
        {
            throw new Exception("Attempted to access bad property {$name} on object of type {$this->type}");
        }
        
        if (in_array($name, Saveable::$subclasses) && $this->$name)
        {
            return new $name($this->$name);
        }
        
        return $this->$name;
    }
    
    public function __set($name, $value)
    {
        if (is_array($value))
        {
            $value = new ArrayProcessor($value, $this);
        }
        
        if (!isset($this->$name) && DEBUG)
        {
            // In debug mode, assert that set calls only work on existing member variables
            throw new Exception("Attempted to modify bad property {$name} on object of type {$this->type}");
        }
        
        if ($this->$name != $value)
        {
            $this->dirty = true;
        }
        
        if (in_array($name, Saveable::$subclasses))
        {
            if (!$value->id)
            {
                trigger_error("Attempted to associate unsaved object; saving before association", e_USER_WARNING);
                $value->save();
            }
            
            $value = $value->id;
        }
        
        return ($this->$name = $value);
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
