<?php

if ( ! isset( $container ) || ! $container->is_blueprint() ) {
	return;
}

?>

<img data-toggle="tooltip" 
	data-placement="bottom" 
	data-tooltip="<?php echo esc_attr( $container->get_saved_description() ); ?>"
	class="fw-blueprint-screenshot acf__tooltip" src="<?php echo esc_url( $container->get_screenshot() ); ?>">
<?php echo esc_html( $container->get_saved_title() ); ?>
