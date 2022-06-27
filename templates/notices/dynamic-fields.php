<?php

if ( ! isset( $fields ) ) {
	return;
}

$fields = array_filter(
	$fields,
	function( $v, $k ) {
		return ! empty( $v['placeholder'] );
	},
	ARRAY_FILTER_USE_BOTH
);

?>

<div>
	<?php esc_html_e( 'Please fill in the fields below. We will use this information populate your new site automatically.', 'dollie' ); ?>
</div>

<?php foreach ( $fields as $field ) : ?>
	<div class="acf-field-text acf-field" style="width: 50%;" data-width="50">
		<div class="af-label acf-label">
			<label><?php echo esc_html( $field['name'] ); ?></label>
		</div>
		<div class="af-input acf-input">
			<input name="wpd_bp_data[<?php echo esc_attr( $field['placeholder'] ); ?>]" type="text" placeholder="<?php echo esc_attr( $field['default_value'] ); ?>">
		</div>
	</div>
<?php endforeach; ?>
