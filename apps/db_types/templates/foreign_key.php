
<?php print Response::$context['field_label']; ?>: 
<br />
<select name="<?php print Response::$context['field_name']; ?>" style="width:400px;">
    <?php foreach (Response::$context['field_options'] as $obj): ?>
        <option value="<?php print $obj->id; ?>"<?php if ($obj->id == Response::$context['field_value']): ?> selected="selected"<?php endif; ?>><?php print $obj->first_name; ?></option>
    <?php endforeach; ?>
</select>
