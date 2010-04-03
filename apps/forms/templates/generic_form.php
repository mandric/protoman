
<form method="POST">
    
    <?php foreach (Response::$context['form_fields'] as $key => $field): ?>
        
        <?php print $field->formField(); ?>
        <br /><br />
        
    <?php endforeach; ?>
    
    <input type="submit" />
    
</form>
