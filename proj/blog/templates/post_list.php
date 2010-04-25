
{{ extends base.php }}

{{ block title }}
    Posts - {{ super }}
{{ endblock title }}

{{ block breadcrumbs }}
    <a href="<?php print Controller::reverse('blog_post_list'); ?>">Posts</a>
{{ endblock breadcrumbs }}

{{ block body }}
    
    <?php foreach (Response::$context['posts'] as $post): ?>
        
        <h2><a href="<?php print Controller::reverse('blog_post_view', $post->id); ?>"><?php print $post->title; ?></a></h2>
        
        <?php print $post->summary; ?>
        
        <br /><br />
        
    <?php endforeach; ?>
    
{{ endblock body }}
