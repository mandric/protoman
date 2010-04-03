<?php


class User extends Saveable
{
    protected $first_name = array('CharField');
    protected $last_name = array('CharField');
    
    protected $username = array('CharField');
    protected $password_hash = array('CharField');
}
