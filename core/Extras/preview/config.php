<?php
// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//Only load the files we need.
define( 'WP_ADMIN', true );

ini_set( 'log_errors', 'On' );
ini_set( 'display_errors', 'Off' );
ini_set( 'error_reporting', E_ALL );

define( 'BASE_DOMAIN', get_site_url() ); // domain where preview bar is installed, with protocol
define( 'BASE_URL', BASE_DOMAIN . '/sites/' ); // url where this preview bar is installed


//=====================================================
// Config
//=====================================================
$plugin_url = plugin_dir_url( dirname( __FILE__ ) );
$products   = array();

$config = array(
	'title'             => 'LivePreview',
	'logo'              =>
		array(
			'url'   => 'http://dollie.lcl/wp-content/uploads/2018/05/wefoster-logo.png',
			'href'  => null,
			'blank' => true,
		),
	'theme'             => 'main-top',
	'page'              => null,
	'productList'       => true,
	'responsiveDevices' => true,
	'responsiveDevice'  => 'desktop',
	'buyButton'         => true,
	'buyButtonText'     => 'Buy Now!',
	'closeIframe'       => true,
	'preload'           => true,
	'items'             =>
		array(
			0 =>
				array(
					'active'  => true,
					'id'      => '28f5ada0',
					'title'   => 'Blueprint One',
					'thumb'   =>
						array(
							'url' => 'http://dollie.lcl/wp-content/uploads/2018/05/seniorchatters.jpg',
						),
					'url'     => 'https://cleaningservices-bp.dollie.io',
					'badge'   => 'Pro',
					'tag'     => 'Corporate',
					'year'    => 2019,
					'buy'     => 'https://cleaningservices-bp.dollie.io',
					'preload' => true,
				),
			1 =>
				array(
					'active'  => true,
					'id'      => '28f5ada0',
					'title'   => 'Blueprint One [copy]',
					'thumb'   =>
						array(
							'url' => 'http://dollie.lcl/wp-content/uploads/2018/05/seniorchatters.jpg',
						),
					'url'     => 'https://cleaningservices-bp.dollie.io',
					'badge'   => 'Pro',
					'tag'     => 'Corporate',
					'year'    => 2019,
					'buy'     => 'https://cleaningservices-bp.dollie.io',
					'preload' => true,
				),
		),
);
if ( $config ) {
	foreach ( $config->items as $product ) {
		if ( $product->active ) {
			array_push( $products, $product );
		}
	}
}

//=====================================================
// Init Tags
//=====================================================
$product_tags = array();
foreach ( $products as $key => $product ) {
	$tag = $product->tag;
	if ( $tag ) {
		$isFound = false;
		foreach ( $product_tags as $key => $value ) {
			if ( $tag == $key ) {
				$product_tags[ $tag ] = $value + 1;
				$isFound              = true;
				break;
			}
		}
		if ( ! $isFound ) {
			$product_tags[ $tag ] = 1;
		}
	}
}
arsort( $product_tags );
$product_tags = array( esc_html__( 'all', LIVEPREVIEWPRO_PLUGIN_NAME ) => sizeof( $products ) ) + $product_tags;

//=====================================================
// Init Years
//=====================================================
$product_years = array();
foreach ( $products as $product ) {
	$year = $product->year;
	if ( $year && ! in_array( $year, $product_years, true ) ) {
		array_push( $product_years, $year );
	}
}
arsort( $product_years );
array_unshift( $product_years, esc_html__( 'all times', LIVEPREVIEWPRO_PLUGIN_NAME ) );

//=====================================================
// Setup Active Product
//=====================================================
$product_id = null;
if ( isset( $_GET['product_id'] ) ) {
	$product_id = filter_input( INPUT_GET, 'product_id', FILTER_SANITIZE_STRING );
	$found      = false;
	foreach ( $products as $product ) {
		if ( $product->id == $product_id ) {
			$found = true;
			break;
		}
	}
	if ( ! $found ) {
		$product_id = null;
	}
}

//=====================================================
// Helpers
//=====================================================
function livepreviewpro_get_image_url( $image ) {
	return $image->url;
}

?>

