<?php


class Form
{
    protected $obj;
    
    public function __construct($obj)
    {
        $this->obj = $obj;
    }
    
    public function render()
    {
        $fields = Saveable::getFields($this->obj);
        
        Response::$context['form_object'] = $this->obj;
        Response::$context['form_fields'] = $fields;
        
        return Response::renderTemplate('forms', 'generic_form.php');
    }
}
