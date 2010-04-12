
<?php Response::startBlock('title'); ?>
    Class listing - <?php Response::$blocks['super']['title']; ?>
<?php Response::endBlock('title'); ?>

<?php Response::startBlock('breadcrumbs'); ?>
    <a href="<?php print Controller::reverse('admin_home'); ?>">admin</a>
<?php Response::endBlock('breadcrumbs'); ?>

<?php Response::startBlock('body'); ?>
    
    <?php foreach (Response::$context['listing_classes'] as $cls): ?>
        <a href="<?php print Controller::reverse('admin_object_list', $cls); ?>"><?php echo $cls; ?></a>
        <br />
    <?php endforeach; ?>
    
<?php Response::endBlock('body'); ?>

<?php Response::extendTemplate('admin', 'base.php'); ?>
