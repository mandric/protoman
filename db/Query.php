<?php


class Query extends ArrayObject
{
    public static $db_conn = false;
    
    private $table = '';
    
    private $ran = false;
    
    private $filters = array();
    private $orders = array();
    private $groups = array();
    
    private $excludes = array();
    
    // TODO: Define injection points
    private $injections = array();
    
    private static $operators = array('>', '>=', '=', '<=', '<', '!=', 'like', 'not like', 'in', 'not in');
    private $fields = false;
    
    private static $injection_points = array();
    
    public static $queries = array();
    
    public function __construct($table)
    {
        $this->table = $table;
        
        $this->fields = array_keys(Saveable::getFields($table));
        
        return parent::__construct(array());
    }
    
    public static function type_clean($value)
    {
        if (is_array($value))
        {
            $cleaned_values = array();
            
            foreach ($value as $raw)
            {
                $cleaned_values[] = Query::type_clean($raw);
            }
            
            return "(" . implode(',', $cleaned_values) . ")";
        }
        
        if (is_string($value))
        {
            $value = "'" . call_user_func(array(DB_TYPE, 'escape_string'), $value) . "'";
        }
        else if (!is_numeric($value))
        {
            throw new Exception("Attempted to filter table {$this->table} on variable with bad type " . gettype($value) . ": " . print_r($value, true));
        }
        
        return $value;
    }
    
    public function filter($field, $operator, $value)
    {
        if (!in_array($field, $this->fields))
        {
            throw new Exception("Attempting to filter table {$this->table} on bad field {$field}. Options: " . implode(', ', $this->fields));
        }
        
        if (!in_array($operator, Query::$operators))
        {
            throw new Exception("Attempting to filter table {$this->table} with bad operator {$operator}");
        }
        
        $value = Query::type_clean($value);
        
        $this->filters[$field][] = array($operator => $value);
        
        return $this;
    }
    
    public function order($field, $direction = 'asc')
    {
        if (!in_array($field, $this->fields))
        {
            throw new Exception("Attempting to order table {$this->table} on bad field {$field}");
        }
        
        $direction = strtolower(trim($direction));
        
        if (!in_array($direction, array('asc', 'desc')))
        {
            throw new Exception("Attempting to order table {$this->table} in bad direction {$direction}");
        }
        
        $this->orders[$field] = $direction;
        
        return $this;
    }
    
    public function group($field)
    {
        if (!in_array($field, $this->fields))
        {
            throw new Exception("Attempting to group table {$this->table} by bad field {$field}");
        }
        
        $this->groups[$field] = 1;
        
        return $this;
    }
    
    public function inject($point, $text)
    {
        if (!in_array($point, Query::$injection_points))
        {
            throw new Exception("Attempted to use invalid query injection point: {$point}");
        }
        
        $this->injections[$point][] = $text;
        
        return $this;
    }
    
    public function append($value)
    {
        return $this->offsetSet(null, $value);
    }
    
    public function build()
    {
        $filters = array();
        
        // TODO: Needs and/or definition of filters
        
        foreach ($this->filters as $field => $values)
        {
            foreach ($values as $pairs)
            {
                foreach ($pairs as $operator => $value)
                {
                    $filters[] = "`{$field}` {$operator} {$value}";
                }
            }
        }
        
        $groups = array();
        
        foreach (array_keys($this->groups) as $field)
        {
            $groups[] = "`{$field}`";
        }
        
        $orders = array();
        
        foreach ($this->orders as $field => $direction)
        {
            $orders[] = "`{$field}` {$direction}";
        }
        
        // TODO: Exclusion function?
        $excludes = array();
        
        // TODO: Needs injection point work
        
        $filters = implode(' and ', $filters);
        $groups = implode(',', $groups);
        $orders = implode(',', $orders);
        
        $query = "select * from `{$this->table}`";
        
        if ($filters)
        {
            $query .= " where {$filters}";
        }
        
        if ($groups)
        {
            $query .= " group by {$groups}";
        }
        
        if ($orders)
        {
            $query .= " order by {$orders}";
        }
        
        return $query;
    }
    
    public function run($raw = false)
    {
        $query = $this->build();
        $results = call_user_func(array(DB_TYPE, 'select'), $query);
        
        if ($raw)
        {
            return $results;
        }
        
        foreach ($results as $result)
        {
            $obj = new $this->table($result);
            parent::append($obj);
        }
        
        $this->ran = true;
    }
    
    public function count()
    {
        if (!$this->ran)
        {
            $this->run();
        }
        
        return parent::count();
    }
    
    public function offsetGet($index)
    {
        if (!$this->ran)
        {
            $this->run();
        }
        
        return parent::offsetGet($index);
    }
    
    public function offsetSet($index, $newval)
    {
        //throw new Exception("Querysets do not support appending or setting.");
        return parent::offsetSet($index, $newval);
    }
    
    public function offsetUnset($index)
    {
        //throw new Exception("Querysets do not support unsetting.");
        return parent::offsetUnset($index);
    }
}
