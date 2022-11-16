<?php

if ( class_exists( 'WooCommerce' ) && get_option( 'options_wpd_charge_for_deployments' ) === '1' && dollie()->subscription()->has_bought_product() ) {
	echo do_shortcode( '[my_orders]' );
}
