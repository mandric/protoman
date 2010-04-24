
{{ extends base.php }}

{{ block title }}
    <?php print Response::$context['post']->title; ?>
{{ endblock title }}

{{ block breadcrumbs }}
    {{ super }}
    > 
    <a href="<?php print Controller::reverse('blog_post_view', Response::$context['post']->id); ?>"><?php print Response::$context['post']->title; ?></a> 
{{ endblock breadcrumbs }}

{{ block body }}
    
    <h1><?php print Response::$context['post']->title; ?></h1>
    
    <?php print Response::$context['post']->body; ?>
    
    <hr />
    
    <?php foreach (Response::$context['post']->comments as $comment): ?>
        
        <?php print $comment->body; ?>
        <br />
        - <?php print $comment->name; ?>
        
        <br /><br />
        
    <?php endforeach; ?>
    
{{ endblock body }}
