<?php


$post_list = array('BlogController', 'postList');
$post_view = array('BlogController', 'postView');


new Route('blog', array(
    '/' => array($post_list, 'blog_post_list'),
    '/(?P<id>\d+)' => array($post_view, 'blog_post_view'),
    ));
