<?php


class AdminController extends Controller
{
    public function objectForm($class, $id=false)
    {
        $obj = new $class($id);
        
        if (Request::$post)
        {
            $obj->updateValues(Request::$post);
            $obj->save();
            
            // TODO: Named 'reverse' URL
            header("location:/admin/{$class}/{$obj->id}");
            exit();
        }
        
        Response::$context['object'] = $obj;
        
        $form = new Form($obj);
        $form->render('admin', 'generic_form.php');
    }
    
    public function objectListing($class)
    {
        $q = new Query($class);
        $q->run();
        
        Response::$context['listing_class'] = $class;
        Response::$context['listing_objects'] = $q;
        
        return Response::renderTemplate('admin', 'object_listing.php');
    }
    
    public function classListing()
    {
        Response::$context['listing_classes'] = Saveable::$subclasses;
        
        return Response::renderTemplate('admin', 'class_listing.php');
    }
}
