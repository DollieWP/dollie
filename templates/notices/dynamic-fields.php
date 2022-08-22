<?php

if ( ! isset( $fields ) ) {
	return;
}

?>

<div>
	<?php esc_html_e( 'Please fill in the fields below. We will use this information populate your new site automatically.', 'dollie' ); ?>
</div>

<?php foreach ( $fields as $field ) :
	$description = isset( $field['description'] ) ? $field['description'] : '';
	$section = isset( $field['section_title'] ) ? $field['section_title'] : '';
	?>

	<?php if ( $section) : ?>
		<div class="dol-py-6 dol-pb-2 dol-border-b dol-border-gray-200 dol-mb-5">
  			<h3 class="dol-text-lg dol-leading-6 dol-font-medium dol-text-gray-900"><?php echo $section; ?></h3>
		</div>
	<?php endif; ?>

	<div class="acf-field-text acf-field dol-<?php echo esc_attr( $field['width'] ); ?> <?php echo esc_attr( $field['css_class'] ); ?>">
		<div class="af-label acf-label">
			<label><?php echo esc_html( $field['name'] ); ?></label>

			<?php if ( $description) : ?>
				<span data-toggle="tooltip"
				data-placement="bottom"
				data-tooltip="<?php echo $description;?>">
				<i class="fas fa-info-circle"></i>
				</span>
			<?php endif;?>

		</div>
		<div class="af-input acf-input">
			<input name="wpd_bp_data[<?php echo esc_attr( $field['placeholder'] ); ?>]" type="text" placeholder="<?php echo esc_attr( $field['default_value'] ); ?>">
		</div>
	</div>
<?php endforeach; ?>
