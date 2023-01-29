<?php
if (!isset($fields))
{
	return;
}

?>

<div class="dol-real-time-customizer dol-mb-6">
    <div>
        <?php esc_html_e('Please fill in the fields below. We will use this information populate your new site automatically.', 'dollie'); ?>
    </div>
    <?php
$count = 0;
foreach ($fields as $field):
	$id = 'field_id_' . ++$count;
	$description = isset($field['description']) ? $field['description'] : '';
	$section = isset($field['section_title']) ? $field['section_title'] : '';
	$width = isset($field['width']) ? $field['width'] : '';

	$prepropulate = isset($field['prepopulate_field']) ? $field['prepopulate_field'] : '';

	if ($prepropulate !== 'none') {

		$user_meta = isset($field['usermeta_name']) ? $field['usermeta_name'] : '';

		// When user meta get the value from the user meta
		if ($user_meta && $prepropulate == 'user_meta') {
			$get_meta = get_user_meta(get_current_user_id(), $user_meta, true);
			$populate = sanitize_text_field('value="' . $get_meta . '"');
		}
		//If Query Parameter get the value via jQuery because of fields are retrieved with ajax.
		else {
			$query_parameter = isset($field['query_string_name']) ? $field['query_string_name'] : '';
			$populate = '';
			?>
			<script>
			jQuery(document).ready(function() {
				var urlParams = new URLSearchParams(window.location.search); //get parameter
				var field_param = urlParams.get(
					'<?php echo sanitize_text_field($query_parameter); ?>'
				); //extract the foo parameter - this will return NULL if foo isn't a parameter

				if (field_param) { //check if foo parameter is set to anything
					jQuery('input#<?php echo $id; ?>').val(field_param);
				}

			});
			</script>
			<?php
		}

	}
	else {
		$populate = '';
	}

	if ($width == '50-width') {
		$width = 'dol-half-width';
	}
	elseif ($width == '33-width') {
		$width = 'dol-third-width"';
	}
	else {
		$width = 'dol-full-width"';
	}
?>

    <?php if ($section): ?>
    <div class="dol-py-6 dol-pb-2 dol-border-b dol-border-gray-200 dol-mb-5">
        <h3 class="dol-text-lg dol-leading-6 dol-font-medium dol-text-gray-900"><?php echo $section; ?></h3>
    </div>
    <?php
	endif; ?>

    <div
        class="acf-field-text acf-field dol-<?php echo esc_attr($field['width']); ?> <?php echo esc_attr($field['css_class']); ?> <?php echo $width; ?>">
        <div class="af-label acf-label">
            <label><?php echo esc_html($field['name']); ?></label>

            <?php if ($description): ?>
            <span data-toggle="tooltip" data-placement="bottom" data-tooltip="<?php echo $description; ?>">
                <i class="fas fa-info-circle"></i>
            </span>
            <?php
	endif; ?>

        </div>
        <div class="af-input acf-input">
            <input <?php echo $populate; ?> id="<?php echo $id; ?>"
                name="wpd_bp_data[<?php echo esc_attr($field['placeholder']); ?>]" type="text"
                placeholder="<?php echo esc_attr($field['default_value']); ?>">
        </div>
    </div>
    <?php
endforeach; ?>

</div>
