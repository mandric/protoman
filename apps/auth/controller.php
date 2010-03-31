<?php


class AuthController extends Controller
{
    public function loginRequired($args = array())
    {
        echo "Requiring login.<br />";
    }
}
