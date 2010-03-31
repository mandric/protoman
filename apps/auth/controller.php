<?php


class AuthController extends Controller
{
    public function loginRequired($args = array())
    {
        if (!$_SESSION['framework'][SITE_NAME]['user'] || !$_SESSION['framework'][SITE_NAME]['user']->id)
        {
            Response::renderTemplate('auth.login_form.html');
            
            return false;
        }
        
        return true;
    }
}
