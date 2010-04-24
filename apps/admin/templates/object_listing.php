
{{ extends admin/base.php }}

{{ block title }}
    Listing for type: <?php print Response::$context['listing_class']; ?> - {{ super }}
{{ endblock title }}

{{ block breadcrumbs }}
    <a href="<?php print Controller::reverse('admin_home'); ?>">admin</a> > 
    <?php print Response::$context['listing_class']; ?>
{{ endblock breadcrumbs }}

{{ block body }}
    
    <a href="<?php print Controller::reverse('admin_object_add', Response::$context['listing_class']); ?>">add a new <?php print Response::$context['listing_class']; ?></a>
    <br /><br />
    
    <?php foreach (Response::$context['listing_objects'] as $obj): ?>
        <a href="<?php print Controller::reverse('admin_object_view', Response::$context['listing_class'], $obj->id); ?>"><?php echo $obj->toString(); ?></a>
        <br />
    <?php endforeach; ?>
    
{{ endblock body }}
