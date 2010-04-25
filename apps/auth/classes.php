<?php


class User extends Saveable
{
    protected $first_name = array('CharField', 'label' => 'First name');
    protected $last_name = array('CharField', 'label' => 'Last name');
    
    protected $username = array('CharField', 'label' => 'Username');
    protected $password = array('PasswordField', 'label' => 'Password');
    
    public function toString()
    {
        return $this->username->get();
    }
}
