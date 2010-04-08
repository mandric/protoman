
<?php Response::startBlock('title'); ?>
    Object detail (TODO: str representation here)
<?php Response::endBlock('title'); ?>

<?php Response::startBlock('breadcrumbs'); ?>
    <?php /* TODO: Reverse()! */ ?>
    <a href="<?php print Controller::reverse('admin_home'); ?>">admin</a> > 
    <a href="<?php print Controller::reverse('admin_object_list', Response::$context['object']->type); ?>"><?php print Response::$context['object']->type; ?></a> >
    <?php print Response::$context['object']->toString(); ?>
<?php Response::endBlock('breadcrumbs'); ?>

<?php Response::startBlock('body'); ?>
    
    <form method="POST">
        
        <?php foreach (Response::$context['form_fields'] as $key => $field): ?>
            
            <?php print $field->formField(); ?>
            <br /><br />
            
        <?php endforeach; ?>
        
        <input type="submit" />
        
    </form>
    
<?php Response::endBlock('body'); ?>

<?php Response::extendTemplate('base.html'); ?>
