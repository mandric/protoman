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
    
    public function wordMethod($args = array())
    {
        echo "In test controller.<br />Super awesome wordy stuff!<br />";
        print_r($args);
    }
    
    public function inputMethod($args = array())
    {
        $text = new Text();
        $text->name = "text_field";
        $text->value = "It's the Value of the Textingness";
        
        Response::$context['text'] = $text;
        
        Response::renderTemplate('appname', 'input_test.php');
    }
}
