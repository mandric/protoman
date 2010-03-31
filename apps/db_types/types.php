<?php


class Text implements Type
{
    protected $name;
    protected $value;
    
    public function serialize()
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
