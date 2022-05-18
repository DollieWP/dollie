<?php
if ( isset( $_GET['default_site_url'] ) && ! empty( $_GET['default_site_url'] ) ) {

	$message = 'We are now going to launch the very first site on your very own platform.';

	dollie()->load_template(
		'notice',
		[
			'type'    => 'success',
			'icon'    => 'fas fa-heart',
			'title'   => __( 'Congratulations, your platform is about to take off.', 'dollie' ),
			'message' => $message,
		],
		true
	);


}
