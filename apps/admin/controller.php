<?php


class AdminController extends Controller
{
    public function objectForm($class, $id=false)
    {
        $stuff = func_get_args();
        $thing = new $class($id);
        $form = new Form($thing);
        $form->render();
    }
    
    public function objectListing($class)
    {
        $obj = new $class();
        Response::$context['listing_objects'] = $obj->getAll();
        
        return Response::renderTemplate('admin', 'object_listing.php');
    }
    
    public function classListing()
    {
        Response::$context['listing_classes'] = Saveable::$subclasses;
        
        return Response::renderTemplate('admin', 'class_listing.php');
    }
}
