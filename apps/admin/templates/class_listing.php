
{{ extends admin/base.php }}

{{ block title }}
    Class listing - {{ super }}
{{ endblock title }}

{{ block body }}
    
    <?php foreach (Response::$context['listing_classes'] as $cls): ?>
        <a href="<?php print Controller::reverse('admin_object_list', $cls); ?>"><?php echo $cls; ?></a>
        <br />
    <?php endforeach; ?>
    
{{ endblock body }}
