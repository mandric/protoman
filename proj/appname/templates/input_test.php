
<?php Response::startBlock('title'); ?>
    An Awesome Page
<?php Response::endBlock('title'); ?>

<?php Response::startBlock('body'); ?>
    <form method="POST">
        
        <?php print Response::$context['text']->form(); ?>
        
        <br /><br />
        
        <?php print Response::$context['char']->form(); ?>
        
        <br /><br />
        
        <?php print Response::$context['key']->form(); ?>
        
        <br /><br />
        
        <input type="submit" />
        
    </form>
<?php Response::endBlock('body'); ?>

<?php Response::extendTemplate('admin', 'base.php'); ?>
