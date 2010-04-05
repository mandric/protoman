
<?php foreach (Response::$context['listing_classes'] as $cls): ?>
    <a href="<?php echo $cls; ?>/"><?php echo $cls; ?></a>
    <br />
<?php endforeach; ?>
