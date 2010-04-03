<?php


class CharField implements Type
{
    protected $label = '';
    protected $name = '';
    protected $default = '';
    protected $value = '';
    protected $null = false;
    protected $length = 256;
    protected $source = false;
    
    public function __construct($source, $name, $args)
    {
        $this->source = $source;
        $this->name = $name;
        
        $this->label = ($args['label']) ? $args['label'] : $name ;
        
        foreach (array('default', 'null', 'length') as $var)
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
            $default = mysql_real_escape_string($this->default);
            $create[] = "default '{$default}'";
        }
        
        return implode(' ', $create);
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
        return "'" . mysql_real_escape_string($this->value) . "'";
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


class IntegerField implements Type
{
    protected $label = '';
    protected $name = '';
    protected $default = '';
    protected $value = '';
    protected $null = false;
    protected $unsigned = false;
    protected $length = 12;
    protected $source = false;
    
    public function __construct($source, $name, $args)
    {
        $this->source = $source;
        $this->name = $name;
        
        $this->label = ($args['label']) ? $args['label'] : $name ;
        
        foreach (array('default', 'null', 'length') as $var)
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
        return is_int($this->value);
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
    protected $default = '';
    protected $value = '';
    protected $null = false;
    protected $source = false;
    
    public function __construct($source, $name, $args)
    {
        $this->source = $source;
        $this->name = $name;
        
        $this->label = ($args['label']) ? $args['label'] : $name ;
        
        foreach (array('default', 'null', 'length') as $var)
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
            $default = mysql_real_escape_string($this->default);
            $create[] = "default '{$default}'";
        }
        
        return implode(' ', $create);
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
        $value = mysql_real_escape_string($this->value);
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
    protected $value = false;
    protected $null = false;
    protected $source = false;
    
    public function __construct($source, $name, $args)
    {
        $this->source = $source;
        $this->class = $args[0];
        $this->name = $name;
        
        $this->label = ($args['label']) ? $args['label'] : $name ;
        
        if ($args['null'])
        {
            $this->null = $args['null'];
        }
    }
    
    public function sql()
    {
        $create = array();
        $create[] = "`{$this->name}` int(11) unsigned not null default {$this->default}";
        
        return implode(' ', $create);
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
        if (is_a($this->value, $this->class) && $this->value->id)
        {
            return true;
        }
        
        if ($this->null && !$this->value)
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
        $objs = new $this->class();
        $objs = $objs->getAll();
        
        Response::$context['field_name'] = $this->name;
        Response::$context['field_label'] = $this->label;
        Response::$context['field_value'] = ($this->value && $this->value->id) ? $this->value->id : $this->default ;
        Response::$context['field_options'] = $objs;
        return Response::renderTemplate('db_types', 'foreign_key.php');
    }
}


class ManyToManyField implements MultipleRelationType
{
    protected $label = '';
    protected $name = '';
    protected $class = '';
    protected $values = false;
    protected $source = false;
    
    public function __construct($source, $name, $args)
    {
        $this->source = $source;
        $this->class = strtolower($args[0]);
        $this->name = $name;
        
        $this->label = ($args['label']) ? $args['label'] : $name ;
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
    
    public function &get()
    {
        if (!$this->values)
        {
            $this->retrieve();
        }
        
        return $this->values;
    }
    
    public function set($value)
    {
        if (!$this->values)
        {
            $this->retrieve();
        }
        
        return ($this->value = $value);
    }
    
    private function retrieve()
    {
        // TODO: Implement Cache; use here
        $this->values = new ArrayProcessor(array(), $this);
        
        $types = array($this->class, $this->source->type);
        sort($types, SORT_STRING);
        $join_table = implode('_', $types);
        
        $object_column = $this->class . '_id';
        $self_column = $this->source->type . '_id';
        
        // TODO: Default ordering?
        $joins = "
            select t.* from `{$this->class}` t
            join `{$join_table}` j on j.`{$object_column}` = t.`id`
            where j.`{$self_column}` = '{$this->source->id}' 
            order by t.id asc
            ";
        
        $joins = mysql_query($joins);
        
        if ($joins && mysql_numrows($joins))
        {
            while ($record = mysql_fetch_assoc($joins))
            {
                // Built from record but didn't load children; used t.* in query:
            //    array_push($return, new $type($record));
                $record = new $this->class($record);
                
                if ($record->id)
                {
                    $this->values[] = $record;
                }
            }
        }
        
        return $this->values;
    }
    
    public function associate($object)
    {
        if (!$this->source->id)
        {
            trigger_error("Called associate on an unsaved object; saving automatically.", E_USER_WARNING);
            $this->source->save();
        }
        
        $name = $object->type;
        
        $tables = array($this->source->type, $object->type);
        sort($tables, SORT_STRING);
        $join_table = implode('_', $tables);
        
        $pairs = "`{$this->source->type}_id` = '{$this->source->id}', `{$object->type}_id` = '{$object->id}'";
        $replace = "replace into `{$join_table}` set {$pairs}";
        
        if (!mysql_query($replace) && DEBUG)
        {
            throw new Exception("Failed to save relation with query {$replace}");
        }
        
        if (isset($this->source->$name))
        {
            $this->source->$name->append($object);
            
            $this->source->cache();
        }
        
        if (isset($object->$name))
        {
            $object->$name->append($object);
            
            $this->cache();
        }
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
        
        $objs = new $this->class();
        $objs = $objs->getAll();
        
        Response::$context['field_name'] = $this->name;
        Response::$context['field_label'] = $this->label;
        Response::$context['field_options'] = $objs;
        
        Response::$context['field_value_ids'] = array();
        
        foreach ($this->values as $key => $value)
        {
            Response::$context['field_value_ids'][] = $value->id;
        }
        
        return Response::renderTemplate('db_types', 'many_to_many.php');
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
        return $this->offsetSet('', $value, true, false);
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
            return $this->obj->dissociate($this->offsetGet($offset));
        }
        
        return parent::offsetUnset($offset);
    }
}
