
<?php print Response::$context['field_label']; ?>: 
<br />
<input type="hidden" name="<?php print Response::$context['field_name']; ?>" value="0" />
<select name="<?php print Response::$context['field_name']; ?>[]" multiple="true" style="width:400px;">
    <?php foreach (Response::$context['field_options'] as &$obj): ?>
        <option value="<?php print $obj->id; ?>"<?php if (in_array($obj->id, Response::$context['field_value_ids'])): ?> selected="selected"<?php endif; ?>><?php print $obj->toString(); ?></option>
    <?php endforeach; ?>
</select>
