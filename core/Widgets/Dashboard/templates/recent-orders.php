<?php

if ( class_exists( 'WooCommerce' ) && get_option( 'options_wpd_charge_for_deployments' ) === '1' && dollie()->has_bought_product( get_current_user_id() ) ) {
	echo do_shortcode( '[my_orders]' );
}
