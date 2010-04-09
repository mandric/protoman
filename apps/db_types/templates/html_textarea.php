
<!-- TinyMCE -->
<script type="text/javascript" src="/media/scripts/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript">
	tinyMCE.init({
		mode : "exact",
        elements : "<?php print Response::$context['field_name']; ?>",
		theme : "advanced"
	});
</script>
<!-- /TinyMCE -->

<?php print Response::$context['field_label']?>: 
<br />
<textarea name="<?php print Response::$context['field_name']; ?>" style="width:600px; height:400px;"><?php print Response::$context['field_value']; ?></textarea>
