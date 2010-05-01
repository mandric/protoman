
{{ extends admin/base.php }}

{{ block title }}
    Class listing - {{ super }}
{{ endblock title }}

{{ block body }}
    
    <?php foreach (Response::$context['listing_apps'] as $app => $classes): ?>
        <?php if (count($classes)): ?>
            <fieldset>
                <legend><?php print $app; ?></legend>
                <?php foreach ($classes as $class): ?>
                    <a href="<?php print Controller::reverse('admin_object_list', $class); ?>"><?php echo $class; ?></a>
                    <br />
                <?php endforeach; ?>
            </fieldset>
        <?php endif; ?>
    <?php endforeach; ?>
    
{{ endblock body }}
