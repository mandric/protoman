<?php


class AppController extends Controller
{
    public function noargsConMethod()
    {
        echo "In test controller.<br />No args.<br />";
    }
    
    public function testConMethod($id)
    {
        echo "In test controller.<br />Include a template or something here.<br />Passed ID: $id<br />";
    }
    
    public function wordMethod($id, $slug)
    {
        echo "In test controller.<br />Super awesome wordy stuff!<br />Passed ID: $id<br />Passed slug: $slug<br />";
    }
    
    public function inputMethod()
    {
        $text = new Text();
        $text->name = "text_field";
        $text->value = "It's the Value of the Textingness";
        
        Response::$context['text'] = $text;
        
        Response::renderTemplate('appname', 'input_test.php');
    }
}
