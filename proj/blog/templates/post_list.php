
<?php Response::startBlock('title'); ?>
    Posts
<?php Response::endBlock('title'); ?>

<?php Response::startBlock('breadcrumbs'); ?>
    <a href="<?php print Controller::reverse('blog_post_list'); ?>">Posts</a>
<?php Response::endBlock('breadcrumbs'); ?>

<?php Response::startBlock('body'); ?>
    
    <?php foreach (Response::$context['posts'] as $post): ?>
        
        <h2><a href="<?php print Controller::reverse('blog_post_view', $post->id); ?>"><?php print $post->title; ?></a></h2>
        
        <?php print $post->summary; ?>
        
        <br /><br />
        
    <?php endforeach; ?>
        
<?php Response::endBlock('body'); ?>

<?php Response::extendTemplate('base.html'); ?>
