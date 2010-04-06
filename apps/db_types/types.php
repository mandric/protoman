<?php


class CharField implements Type
{
    protected $label = '';
    protected $name = '';
    public $default = '';
    protected $value = '';
    protected $null = false;
    public $hidden = false;
    protected $length = 256;
    protected $source_type = '';
    public $source_id = 0;
    
    public function __construct($source, $name, $args)
    {
        $this->source_id = $source->id;
        $this->source_type = $source->type;
        
        $this->name = $name;
        
        $this->label = ($args['label']) ? $args['label'] : $name ;
        
        foreach (array('default', 'null', 'length', 'hidden') as $var)
        {
            if ($args[$var])
            {
                $this->$var = $args[$var];
            }
        }
    }
    
    public function sql()
    {
        $create = array();
        $create[] = "`{$this->name}` varchar({$this->length})";
        
        if ($this->null)
        {
            $create[] = "not";
        }
        
        $create[] = "null";
        
        if ($this->default)
        {
            $default = call_user_func(array(Query::$db_class, 'escape_string'), $this->default);
            $create[] = "default '{$default}'";
        }
        
        return implode(' ', $create);
    }
    
    public function populate($value)
    {
        return $this->value = $value;
    }
    
    public function &get()
    {
        return $this->value;
    }
    
    public function set($value)
    {
        if ($this->value != $value)
        {
            $this->dirty = true;
        }
        
        return ($this->value = $value);
    }
    
    public function validate()
    {
        return is_string($this->value) && strlen($this->value);
    }
    
    public function databaseValue()
    {
        return "'" . call_user_func(array(Query::$db_class, 'escape_string'), $this->value) . "'";
    }
    
    public function displaySafe()
    {
        return htmlentities($this->value, ENT_QUOTES);
    }
    
    public function displayRaw()
    {
        return $this->value;
    }
    
    public function formField()
    {
        Response::$context['field_type'] = 'text';
        Response::$context['field_name'] = $this->name;
        Response::$context['field_label'] = $this->label;
        Response::$context['field_value'] = $this->displaySafe();
        return Response::renderTemplate('db_types', 'input_type.php');
    }
}


class PasswordField extends CharField
{
    public function &get()
    {
        return $this->value;
    }
    
    public function populate($value)
    {
        return $this->value = $value;
    }
    
    public function set($value)
    {
        if (strlen($value))
        {
            $hash = md5($this->value);
            
            if ($hash != $this->value)
            {
                $this->dirty = true;
            }
            
            return ($this->value = $hash);
        }
        
        return false;
    }
    
    public function validate()
    {
        return (is_string($this->value)) && $this->value;
    }
    
    public function databaseValue()
    {
        return "'" . call_user_func(array(Query::$db_class, 'escape_string'), $this->value) . "'";
    }
    
    public function displaySafe()
    {
        return htmlentities($this->value, ENT_QUOTES);
    }
    
    public function displayRaw()
    {
        return $this->value;
    }
    
    public function formField()
    {
        Response::$context['field_type'] = 'password';
        Response::$context['field_name'] = $this->name;
        Response::$context['field_label'] = $this->label;
        Response::$context['field_value'] = '';
        return Response::renderTemplate('db_types', 'input_type.php');
    }
}


class IntegerField implements Type
{
    protected $label = '';
    protected $name = '';
    public $default = '';
    protected $value = 0;
    protected $null = false;
    public $hidden = false;
    protected $unsigned = false;
    protected $length = 12;
    protected $source_type = '';
    public $source_id = 0;
    
    public function __construct($source, $name, $args)
    {
        $this->source_id = $source->id;
        $this->source_type = $source->type;
        
        $this->name = $name;
        
        $this->label = ($args['label']) ? $args['label'] : $name ;
        
        foreach (array('default', 'null', 'length', 'hidden') as $var)
        {
            if ($args[$var])
            {
                $this->$var = $args[$var];
            }
        }
    }
    
    public function sql()
    {
        $create = array();
        $create[] = "`{$this->name}` int({$this->length})";
        
        if ($this->unsigned)
        {
            $create[] = "unsigned";
        }
        
        if ($this->null)
        {
            $create[] = "not";
        }
        
        $create[] = "null";
        
        if ($this->default)
        {
            $create[] = "default {$this->default}";
        }
        
        return implode(' ', $create);
    }
    
    public function populate($value)
    {
        return $this->value = $value;
    }
    
    public function &get()
    {
        return $this->value;
    }
    
    public function set($value)
    {
        if ($this->value != $value)
        {
            $this->dirty = true;
        }
        
        return ($this->value = $value);
    }
    
    public function validate()
    {
        return ( is_numeric($this->value) && ((int)$this->value == $this->value) );
    }
    
    public function databaseValue()
    {
        return $this->value;
    }
    
    public function displaySafe()
    {
        return $this->value;
    }
    
    public function displayRaw()
    {
        return $this->value;
    }
    
    public function formField()
    {
        Response::$context['field_type'] = 'text';
        Response::$context['field_name'] = $this->name;
        Response::$context['field_label'] = $this->label;
        Response::$context['field_value'] = $this->displaySafe();
        return Response::renderTemplate('db_types', 'input_type.php');
    }
}


class TextField implements Type
{
    protected $label = '';
    protected $name = '';
    public $default = '';
    protected $value = '';
    protected $null = false;
    public $hidden = false;
    protected $source_type = '';
    public $source_id = 0;
    
    public function __construct($source, $name, $args)
    {
        $this->source_type = $source->type;
        $this->source_id = $source->id;
        
        $this->name = $name;
        
        $this->label = ($args['label']) ? $args['label'] : $name ;
        
        foreach (array('default', 'null', 'length', 'hidden') as $var)
        {
            if ($args[$var])
            {
                $this->$var = $args[$var];
            }
        }
    }
    
    public function sql()
    {
        $create = array();
        $create[] = "`{$this->name}` text";
        
        if ($this->null)
        {
            $create[] = "not";
        }
        
        $create[] = "null";
        
        if ($this->default)
        {
            $default = call_user_func(array(Query::$db_class, 'escape_string'), $this->default);
            $create[] = "default '{$default}'";
        }
        
        return implode(' ', $create);
    }
    
    public function populate($value)
    {
        return $this->value = $value;
    }
    
    public function &get()
    {
        return $this->value;
    }
    
    public function set($value)
    {
        if ($this->value != $value)
        {
            $this->dirty = true;
        }
        
        return ($this->value = $value);
    }
    
    public function validate()
    {
        return (is_string($this->value) || is_numeric($this->value));
    }
    
    public function databaseValue()
    {
        $value = call_user_func(array(Query::$db_class, 'escape_string'), $this->value);
        return "'{$value}'";
    }
    
    public function displaySafe()
    {
        return htmlentities($this->value, ENT_QUOTES);
    }
    
    public function displayRaw()
    {
        return $this->value;
    }
    
    public function formField()
    {
        Response::$context['field_name'] = $this->name;
        Response::$context['field_label'] = $this->label;
        Response::$context['field_value'] = $this->displaySafe();
        return Response::renderTemplate('db_types', 'textarea.php');
    }
    
    public function __get($name)
    {
        return $this->$name;
    }
    
    public function __set($name, $value)
    {
        return $this->$name = $value;
    }
}


class ForeignKeyField implements SingleRelationType
{
    protected $label = '';
    protected $name = '';
    protected $class = '';
    public $default = 0;
    protected $value = false;
    protected $null = false;
    public $hidden = false;
    protected $source_type = '';
    public $source_id = 0;
    
    public function __construct($source, $name, $args)
    {
        $this->source_type = $source->type;
        $this->source_id = $source->id;
        
        $this->class = $args[0];
        $this->name = $name;
        
        $this->label = ($args['label']) ? $args['label'] : $name ;
        
        foreach (array('null', 'hidden') as $var)
        {
            if ($args[$var])
            {
                $this->$var = $args[$var];
            }
        }
    }
    
    public function sql()
    {
        $create = array();
        $create[] = "`{$this->name}` int(11) unsigned not null default {$this->default}";
        
        return implode(' ', $create);
    }
    
    public function populate($value)
    {
        return $this->set($value);
    }
    
    public function &get()
    {
        return $this->value;
    }
    
    public function set($value)
    {
        if (is_numeric($value))
        {
            $value = new $this->class($value);
        }
        
        if (!is_a($value, $this->class))
        {
            throw new Exception("Bad assignment on foreignkey");
        }
        
        return ($this->value = $value);
        
        // TODO: Error/exception on bad set?
    }
    
    public function validate()
    {
        if (is_a($this->value, $this->class) && $this->value->id)
        {
            return true;
        }
        
        if ( $this->null && (!$this->value || !$this->value->id) )
        {
            return true;
        }
        
        return false;
    }
    
    public function databaseValue()
    {
        return ($this->value && $this->value->id) ? $this->value->id : $this->default ;
    }
    
    public function displaySafe()
    {
        return "{$this->class} id {$this->value->id}";
    }
    
    public function displayRaw()
    {
        return print_r($this->value, true);
    }
    
    public function formField()
    {
        $q = new Query($this->class);
        $q->run();
        
        Response::$context['field_name'] = $this->name;
        Response::$context['field_label'] = $this->label;
        Response::$context['field_value'] = ($this->value && $this->value->id) ? $this->value->id : $this->default ;
        Response::$context['field_options'] = $q;
        
        return Response::renderTemplate('db_types', 'foreign_key.php');
    }
}


class ManyToManyField implements MultipleRelationType
{
    protected $label = '';
    protected $name = '';
    protected $class = '';
    public $hidden = false;
    protected $values = false;
    protected $source_type = '';
    public $source_id = 0;
    
    public function __construct($source, $name, $args)
    {
        $this->source_type = $source->type;
        $this->source_id = $source->id;
        
        $this->class = strtolower($args[0]);
        $this->name = $name;
        
        $this->label = ($args['label']) ? $args['label'] : $name ;
        
        foreach (array('hidden') as $var)
        {
            if ($args[$var])
            {
                $this->$var = $args[$var];
            }
        }
    }
    
    public function sql()
    {
        $create = array();
        
        return implode(' ', $create);
    }
    
    public function save()
    {
        // Should always be current with DB
        return true;
    }
    
    public function populate($value)
    {
        return $this->set($value);
    }
    
    public function &get()
    {
        if (!$this->values)
        {
            $this->retrieve();
        }
        
        return $this->values;
    }
    
    public function set($values)
    {
        if (!$this->values)
        {
            $this->retrieve();
        }
        
        if (!$values)
        {
            $values = array();
        }
        
        foreach ($values as $key => $value)
        {
            if (is_object($value))
            {
                $values[$key] = $value->id;
            }
        }
        
        $existing_ids = array();
        
        foreach ($this->values as $key => $value)
        {
            $existing_ids[] = $value->id;
        }
        
        $vals = array();
        
        foreach ($values as $key => $value)
        {
            $vals[$key] = $value;
        }
        
        $values = $vals;
        
        foreach ($this->values as $key => $existing)
        {
            if (!in_array($existing->id, $values))
            {
                unset($this->values[$key]);
            }
        }
        
        if (count($this->values) != count($values))
        {
            foreach ($values as $value)
            {
                if (!in_array($value, $existing_ids))
                {
                    $this->values[] = new $this->class($value);
                }
            }
        }
        
        // TODO: Return indicator of success/failure?
        return true;
    }
    
    private function retrieve()
    {
        // TODO: Implement Cache; use here
        $this->values = new ArrayProcessor(array(), $this);
        
        $types = array($this->class, $this->source_type);
        sort($types, SORT_STRING);
        $join_table = implode('_', $types);
        
        $object_column = $this->class . '_id';
        $self_column = $this->source_type . '_id';
        
        // TODO: Default ordering?
        $joins = "
            select t.* from `{$this->class}` t
            join `{$join_table}` j on j.`{$object_column}` = t.`id`
            where j.`{$self_column}` = '{$this->source_id}' 
            order by t.id asc
            ";
        
        $joins = call_user_func(array(Query::$db_class, 'select'), $joins);
        
        if ($joins && count($joins))
        {
            foreach ($joins as $record)
            {
                // Built from record but didn't load children; used t.* in query:
            //    array_push($return, new $type($record));
                $record = new $this->class($record);
                
                if ($record->id)
                {
                    $this->values->append($record);
                }
            }
        }
        
        return $this->values;
    }
    
    public function displaySafe()
    {
        if (!$this->values)
        {
            $this->retrieve();
        }
        
        $display = array();
        
        foreach ($this->values as $obj)
        {
            $display[] = "{$obj->class} id {$obj->id}";
        }
        
        return implode(';', $display);
    }
    
    public function displayRaw()
    {
        if (!$this->values)
        {
            $this->retrieve();
        }
        
        $display = array();
        
        foreach ($this->values as $obj)
        {
            $display[] = print_r($this->value, true);
        }
        
        return implode(';', $display);
    }
    
    public function formField()
    {
        if (!$this->values)
        {
            $this->retrieve();
        }
        
        $q = new Query($this->class);
        $q->run();
        
        Response::$context['field_name'] = $this->name;
        Response::$context['field_label'] = $this->label;
        Response::$context['field_options'] = $q;
        
        Response::$context['field_value_ids'] = array();
        
        foreach ($this->values as $key => $value)
        {
            Response::$context['field_value_ids'][] = $value->id;
        }
        
        return Response::renderTemplate('db_types', 'many_to_many.php');
    }
    
    public function associate($object)
    {
        if (!$this->source_id || is_array($this->source_id))
        {
            throw new Exception("Called associate on an unsaved object.");
            /*
            $source = new $this->source_type();
            $source->save();
            
            $this->source_id = $source->id;
            */
        }
        
        $name = $object->type;
        
        $tables = array($this->source_type, $object->type);
        sort($tables, SORT_STRING);
        $join_table = implode('_', $tables);
        
        $pairs = "`{$this->source_type}_id` = '{$this->source_id}', `{$object->type}_id` = '{$object->id}'";
        $insert = "insert into `{$join_table}` set {$pairs}";
        
        if (!call_user_func(array(Query::$db_class, 'insert'), $insert) && DEBUG)
        {
            throw new Exception("Failed to save relation with query {$insert}");
        }
    }
    
    public function dissociate($object)
    {
        $tables = array($this->source_type, $object->type);
        sort($tables, SORT_STRING);
        $join_table = implode('_', $tables);
        
        $delete = "delete from `{$join_table}` where `{$this->source_type}_id` = '" . $this->source_id . "' and `{$object->type}_id` = '{$object->id}'";
        
        if (!call_user_func(array(Query::$db_class, 'delete'), $delete) && DEBUG)
        {
            throw new Exception("Failed to delete relation with query {$delete}");
        }
        
        Cache::delete($source->cache_index);
    }
}


class ArrayProcessor extends ArrayObject
{
    protected $obj;
    
    public function __construct($arr, $obj)
    {
        $this->obj = $obj;
        
        parent::__construct(array());
        
        foreach ($arr as $key => $value)
        {
            $this->offsetSet($key, $value, false, false);
        }
    }
    
    public function append($value)
    {
        return $this->offsetSet(null, $value, true, false);
    }
    
    public function offsetSet($offset, $value, $overwrite=true, $save=true)
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
            
            if ($save)
            {
                $this->obj->associate($value);
            }
        }
        
        return parent::offsetSet($offset, $value);
    }
    
    public function offsetUnset($offset)
    {
        if ( is_object($this[$offset]) && $this[$offset]->is_saveable )
        {
            $this->obj->dissociate($this->offsetGet($offset));
        }
        
        return parent::offsetUnset($offset);
    }
}
