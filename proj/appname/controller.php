<?php


class AppController extends Controller
{
    public function noargsConMethod($args = array())
    {
        echo "In test controller.<br />No args.<br />";
    }
    public function testConMethod($args = array())
    {
        echo "In test controller.<br />Include a template or something here.<br />";
        print_r($args);
    }
}
