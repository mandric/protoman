
<?php Response::startBlock('title'); ?>
    Listing for type: <?php print Response::$context['listing_class']; ?>
<?php Response::endBlock('title'); ?>

<?php Response::startBlock('breadcrumbs'); ?>
    <?php /* TODO: Reverse()! */ ?>
    <a href="<?php print Controller::reverse('admin_home'); ?>">admin</a> > 
    <?php print Response::$context['listing_class']; ?>
<?php Response::endBlock('breadcrumbs'); ?>

<?php Response::startBlock('body'); ?>
    
    <a href="<?php print Controller::reverse('admin_object_add', Response::$context['listing_class']); ?>">add a new <?php print Response::$context['listing_class']; ?></a>
    <br /><br />
    
    <?php foreach (Response::$context['listing_objects'] as $obj): ?>
        <a href="<?php print Controller::reverse('admin_object_view', Response::$context['listing_class'], $obj->id); ?>"><?php echo $obj->toString(); ?></a>
        <br />
    <?php endforeach; ?>
    
<?php Response::endBlock('body'); ?>

<?php Response::extendTemplate('base.html'); ?>
