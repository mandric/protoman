<?php


class Form
{
    protected $obj;
    
    public function __construct($obj)
    {
        $this->obj = $obj;
    }
    
    public function getFormFields()
    {
        $fields = Saveable::getFields($this->obj);
        
        foreach ($fields as $key => $field)
        {
            if ($field->hidden)
            {
                unset($fields[$key]);
            }
        }
        
        return $fields;
    }
    
    public function render()
    {
        $fields = $this->getFormFields();
        
        Response::$context['form_object'] = $this->obj;
        Response::$context['form_fields'] = $fields;
        
        $args = func_get_args();
        $template = (count($args)) ? $args : array('forms', 'generic_form.php') ;
        
        return call_user_func_array(array('Response', 'renderTemplate'), $template);
    }
}
