<?php


class CharField implements Type
{
    protected $label = '';
    protected $name = '';
    protected $default = '';
    protected $value = '';
    protected $null = false;
    protected $length = 256;
    
    public function validate()
    {
        return (is_string($this->value) || is_numeric($this->value));
    }
    
    public function columnSql()
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
    
    public function databaseValue()
    {
        return $this->value;
    }
    
    public function displaySafe()
    {
        return htmlentities($this->value, ENT_QUOTES);
    }
    
    public function displayRaw()
    {
        return $this->value;
    }
    
    public function form()
    {
        Response::$context['field_type'] = 'text';
        Response::$context['field_name'] = $this->name;
        Response::$context['field_label'] = $this->label;
        Response::$context['field_value'] = $this->displaySafe();
        return Response::renderTemplate('db_types', 'input_type.php');
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


class TextField implements Type
{
    protected $label = '';
    protected $name = '';
    protected $default = '';
    protected $value = '';
    protected $null = false;
    
    public function validate()
    {
        return (is_string($this->value) || is_numeric($this->value));
    }
    
    public function columnSql()
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
    
    public function form()
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
    protected $default = 0;
    protected $value = false;
    protected $null = false;
    
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
    
    public function columnSql()
    {
        $create = array();
        $create[] = "`{$this->name}` int(11) unsigned not null default {$this->default}";
        
        return implode(' ', $create);
    }
    
    public function databaseValue()
    {
        return ($this->value && $this->value->id) ? $this->value->id : $this->default ;
    }
    
    public function displaySafe()
    {
        return "{$this->class} id {$this->value}";
    }
    
    public function displayRaw()
    {
        return print_r(new $this->$class($this->value));
    }
    
    public function form()
    {
        $objs = new $this->class();
        $objs = $objs->getAll();
        
        Response::$context['field_name'] = $this->name;
        Response::$context['field_label'] = $this->label;
        Response::$context['field_value'] = ($this->value && $this->value->id) ? $this->value->id : $this->default ;
        Response::$context['field_options'] = $objs;
        return Response::renderTemplate('db_types', 'foreign_key.php');
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
