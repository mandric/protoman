<?php


class BlogController extends Controller
{
    public function postList()
    {
        $posts = new Query('post');
        $posts->run();
        
        Response::$context['posts'] = $posts;
        Response::renderTemplate('blog', 'post_list.php');
    }
    
    public function postView($id)
    {
        $post = new Post($id);
        
        Response::$context['post'] = $post;
        Response::renderTemplate('blog', 'post_view.php');
    }
}
