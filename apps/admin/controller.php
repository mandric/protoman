<?php


class AdminController extends Controller
{
    public function objectForm($class, $id=false)
    {
        $obj = new $class($id);
        $form = new Form($obj);
        $form->render();
    }
    
    public function objectListing($class)
    {
        $obj = new $class();
        Response::$context['listing_objects'] = $obj->getAll();
        
        return Response::renderTemplate('admin', 'object_listing.php');
    }
}
