
<?php Response::startBlock('title'); ?>
    <?php print Response::$context['post']->title; ?>
<?php Response::endBlock('title'); ?>

<?php Response::startBlock('breadcrumbs'); ?>
    <a href="<?php print Controller::reverse('blog_post_list'); ?>">Posts</a> 
    > 
    <a href="<?php print Controller::reverse('blog_post_view', Response::$context['post']->id); ?>"><?php print Response::$context['post']->title; ?></a> 
<?php Response::endBlock('breadcrumbs'); ?>

<?php Response::startBlock('body'); ?>
    
    <h1><?php print Response::$context['post']->title; ?></h1>
    
    <?php print Response::$context['post']->body; ?>
    
    <hr />
    
    <?php foreach (Response::$context['post']->comments as $comment): ?>
        
        <?php print $comment->body; ?>
        <br />
        - <?php print $comment->name; ?>
        
        <br /><br />
        
    <?php endforeach; ?>
    
<?php Response::endBlock('body'); ?>

<?php Response::extendTemplate('base.html'); ?>
