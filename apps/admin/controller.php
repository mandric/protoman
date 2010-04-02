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
}
