
{{ extends admin/base.php }}

{{ block title }}
    Object detail: <?php print Response::$context['object']->toString(); ?> - {{ super }}
{{ endblock title }}

{{ block breadcrumbs }}
    <a href="<?php print Controller::reverse('admin_home'); ?>">admin</a> > 
    <a href="<?php print Controller::reverse('admin_object_list', Response::$context['object']->type); ?>"><?php print Response::$context['object']->type; ?></a> >
    <?php print Response::$context['object']->toString(); ?>
{{ endblock breadcrumbs }}

{{ block body }}
    
    <form method="POST">
        
        <?php foreach (Response::$context['form_fields'] as $key => $field): ?>
            
            <?php print $field->formField(); ?>
            <br /><br />
            
        <?php endforeach; ?>
        
        <input type="submit" />
        
    </form>
    
{{ endblock body }}
