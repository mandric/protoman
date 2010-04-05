
<?php foreach (Response::$context['listing_objects'] as $obj): ?>
    <a href="<?php echo $obj->id; ?>"><?php echo $obj->id; ?></a>
    <br />
<?php endforeach; ?>
