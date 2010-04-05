
<?php Response::startBlock('title'); ?>
    Class listing
<?php Response::endBlock('title'); ?>

<?php Response::startBlock('breadcrumbs'); ?>
    <?php /* TODO: Reverse()! */ ?>
    <a href="/admin/">admin</a>
<?php Response::endBlock('breadcrumbs'); ?>

<?php Response::startBlock('body'); ?>
    
    <?php foreach (Response::$context['listing_classes'] as $cls): ?>
        <a href="<?php echo $cls; ?>/"><?php echo $cls; ?></a>
        <br />
    <?php endforeach; ?>
    
<?php Response::endBlock('body'); ?>

<?php Response::extendTemplate('base.html'); ?>
