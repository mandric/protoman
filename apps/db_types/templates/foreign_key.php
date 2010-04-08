
<?php print Response::$context['field_label']; ?>: 
<br />
<select name="<?php print Response::$context['field_name']; ?>" style="width:400px;">
    <option value="0"></option>
    <?php foreach (Response::$context['field_options'] as $obj): ?>
        <option value="<?php print $obj->id; ?>"<?php if ($obj->id == Response::$context['field_value']): ?> selected="selected"<?php endif; ?>><?php print $obj->toString(); ?></option>
    <?php endforeach; ?>
</select>
