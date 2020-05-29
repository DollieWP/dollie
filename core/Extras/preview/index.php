<?php
//Only load the files we need.
define( 'WP_ADMIN', true );

// ini_set('display_errors', 'On');
// ini_set('error_reporting', E_ALL);
// define('WP_DEBUG', true);
// define('WP_DEBUG_LOG', false);
// define('WP_DEBUG_DISPLAY', true);

define( 'DOLLIE_PREVIEW_URL', DOLLIE_URL . 'core/Extras/preview/' );
define( 'LIVEPREVIEWPRO_PLUGIN_NAME', get_bloginfo( 'name' ) ); // displayed in author meta tags

if ( isset( $_GET['type'] ) ) {
	if ( $_GET['type'] === 'my-sites' ) {
		if ( is_user_logged_in() ) {
			$author = get_current_user_id();
		} else {
			$author = '58687848382305067080201305060';
		}
		$gp_args = array(
			'author'         => $author,
			'post_type'      => 'container',
			'posts_per_page' => 1000,
			'meta_key'       => 'wpd_setup_complete', // (string) - Custom field key.
			'meta_value'     => 'yes', // (string) - Custom field value.
		);
	} elseif ( $_GET['type'] === 'my-blueprints' ) {
		$gp_args = array(
			'author'         => get_current_user_id(),
			'post_type'      => 'container',
			'posts_per_page' => 1000,
			'meta_key'       => 'wpd_blueprint_created', // (string) - Custom field key.
			'meta_value'     => 'yes', // (string) - Custom field value.
		);
	}
} else {
	$gp_args = array(
		'post_type'      => 'container',
		'posts_per_page' => 1000,
		'post_status'    => 'publish',
		//Se the meta query
		'meta_query'     => array(
			//comparison between the inner meta fields conditionals
			'relation' => 'AND',
			//meta field condition one
			array(
				'key'   => 'wpd_blueprint_created',
				'value' => 'yes',
			),
			array(
				'key'   => 'wpd_is_blueprint',
				'value' => 'yes',
			),
			//meta
			//meta field condition one
			array(
				'key'     => 'wpd_installation_blueprint_title',
				//I think you really want != instead of NOT LIKE, fix me if I'm wrong
				//'compare'      => 'NOT LIKE',
				'compare' => 'EXISTS',
			),
		),

	);
}
$posts = query_posts( $gp_args );

$theme_array = array();

if ( have_posts() ) :
	while ( have_posts() ) : the_post();

		$product_id    = get_field( 'wpd_installation_blueprint_hosting_product' );
		$product_obj   = wc_get_product( $product_id[0] );
		$checkout_link = dollie()->get_woo_checkout_link( $product_id[0], get_the_ID() );

		if ( isset( $_GET["type"] ) && $_GET["type"] === 'my-sites' ) {

			if (
				( isset( $_SERVER['HTTPS'] ) && ( $_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1 ) ) ||
				( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' )
			) {
				$protocol = 'https://';
			} else {
				$protocol = 'http://';
			}

			$screenshot = wpthumb( get_post_meta( get_the_ID(), 'wpd_installation_site_theme_screenshot', true ) );
			if ( $screenshot ) {
				$screenshot = wpthumb( get_post_meta( get_the_ID(), 'wpd_installation_site_theme_screenshot', true ), 'width=450&crop=0' );
			} else {
				$screenshot = $protocol . $_SERVER['SERVER_NAME'] . dirname( $_SERVER['REQUEST_URI'] ) . '/assets/images/no-screenshot.png';
			}

			$theme_array[] = array(
				'active'      => 1,
				'id'          => get_the_ID(),
				'title'       => get_post_field( 'post_name', $post_id ),
				'title_short' => get_post_field( 'post_name', $post_id ),
				'url'         => get_post_meta( get_the_ID(), 'wpd_container_uri', true ),
				'buy'         => dollie()->get_customer_login_url( get_the_ID(), get_post_field( 'post_name' ) ),
				'login_url'   => dollie()->get_customer_login_url( get_the_ID(), get_post_field( 'post_name' ) ),
				'thumb'       => array(
					'url' => $screenshot,
				),
				'info'        => get_post_meta( get_the_ID(), 'wpd_installation_blueprint_description', true ),
				'tag'         => 'tag',
				'year'        => '2019',
				'preload'     => '0',
				'badge'       => 'Pro',
			);
		} else {

			if ( get_field( 'wpd_blueprint_image' ) == 'custom' ) {
				$image = get_field( 'wpd_blueprint_custom_image' );
			} elseif ( get_field( 'wpd_blueprint_image' ) == 'theme' ) {
				$image = wpthumb( get_post_meta( get_the_ID(), 'wpd_installation_site_theme_screenshot', true ), 'width=900&crop=0' );
			} else {
				$image = get_post_meta( get_the_ID(), 'wpd_site_screenshot', true );
			}

			$theme_array[] = array(
				'active'      => 1,
				'id'          => get_the_ID(),
				'title'       => get_post_meta( get_the_ID(), 'wpd_installation_blueprint_title', true ),
				'title_short' => get_post_field( 'post_name', $post_id ),
				'url'         => get_post_meta( get_the_ID(), 'wpd_container_uri', true ),
				'buy'         => $checkout_link,
				'login_url'   => dollie()->get_customer_login_url( get_the_ID(), get_post_field( 'post_name' ) ),
				'thumb'       => array(
					'url' => $image,
				),
				'info'        => get_post_meta( get_the_ID(), 'wpd_installation_blueprint_description', true ),
				'tag'         => 'tag',
				'year'        => '2019',
				'preload'     => '0',
				'badge'       => 'Pro',
			);
		}

	endwhile;
else :
	$no_sites = true;
endif;
wp_reset_postdata();

//=====================================================
// Config
//=====================================================
$plugin_url = plugin_dir_url( dirname( __FILE__ ) );
$products   = array();


$logo = get_field( 'wpd_dashboard_logo_inversed', 'option' );

if ( $logo ) {
	$logo = get_field( 'wpd_dashboard_logo_inversed', 'option' );
} else {
	$logo = get_template_directory_uri() . '/assets/img/logo-inversed.png';
}


$config = array(
	'title'             => get_bloginfo( 'name' ),
	'logo'              => array(
		'url'   => $logo,
		'href'  => get_site_url(),
		'blank' => 1
	),
	'theme'             => 'main-top',
	'page'              => null,
	'productList'       => true,
	'responsiveDevices' => true,
	'responsiveDevice'  => 'desktop',
	'buyButton'         => true,
	'buyButtonText'     => esc_html( 'Launch Site', 'dollie' ),
	'closeIframe'       => true,
	'preload'           => false,
	'items'             => $theme_array,
);

//print("<pre>".print_r(json_decode(json_encode($config)),true)."</pre>");


$config = json_decode( json_encode( $config ) );

// $config = get_option(LIVEPREVIEWPRO_PLUGIN_NAME . '_config');
// $config = unserialize($config);
// print("<pre>".print_r($config,true)."</pre>");


if ( $config ) {
	//$config = unserialize($config);
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
	return $image;
}

//print("<pre>".print_r($theme_array,true)."</pre>");
//die();
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>
    <!-- styles begin -->
    <link href="https://fonts.googleapis.com/css?family=Muli:300,400,600,700,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="<?php echo DOLLIE_PREVIEW_URL . 'assets/css/font-awesome.min.css'; ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo DOLLIE_PREVIEW_URL . 'assets/css/bootstrap.min.css'; ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo DOLLIE_PREVIEW_URL . 'assets/themes/main-top.min.css'; ?>">
    <title><?php echo get_bloginfo( 'name' ); ?></title>
    <script type='text/javascript'>
        /* <![CDATA[ */
        var livepreviewpro_globals = {
            "plan": "pro",
            "responsiveDevice": null
        };
        /* ]]> */
    </script>
</head>

<body>

<?php if ( $config && $config->preload ) { ?>
    <div class="page-loader">
        <div class="loader-wrap">
            <div class="loader">

            </div>
        </div>
    </div>
<?php } ?>
<div class="livepreview-wrap">
    <div class="page">
		<?php if ( $config && sizeof( $products ) > 0 ) { ?>
            <div id="header" class="header">
                <div class="container">
                    <div class="row">
                        <div class="hidden-xs col-sm-2 col-md-2">
							<?php if ( $config && $config->logo ) { ?>
								<?php if ( $config->logo->href ) { ?>
                                    <a class="logo" href="<?php echo $config->logo->href; ?>"
                                       target="<?php echo( $config->logo->blank ? '_blank' : '_self' ) ?>"><img
                                                src="<?php echo $config->logo->url; ?>"></a>
								<?php } else { ?>
                                    <div class="logo"><img src="<?php echo $config->logo->url; ?>"></div>
								<?php } ?>
							<?php } ?>
                        </div>
                        <div class="col-xs-5 col-sm-6 col-md-4">
							<?php if ( $config->productList ) { ?>
                                <div id="product-toggle" class="product-toggle">
                                    <span id="product-name" class="product-name">&nbsp;</span>
                                    <span class="product-btn">
											<i class="product-show fa fa-angle-down"></i>
											<i class="product-hide fa fa-angle-up"></i>
										</span>
                                </div>
							<?php } ?>
                        </div>
                        <div class="col-xs-7 col-sm-4 col-md-6">
                            <div class="product-toolbar clearfix">
								<?php if ( $config->closeIframe ) { ?>
                                    <a id="product-frame-close" class="product-frame-close" href="#"
                                       title="<?php esc_html_e( 'close iframe', LIVEPREVIEWPRO_PLUGIN_NAME ); ?>"><i
                                                class="fa fa-close"></i></a>
								<?php } ?>
								<?php if ( $config->buyButton ) { ?>
                                    <div class="product-buttons">
                                        <a id="buy" class="btn btn-success" href="#"
                                           style="display:none"><?php echo esc_html( $config->buyButtonText ); ?></a>
                                    </div>
								<?php } ?>
								<?php if ( $config->responsiveDevices ) { ?>
                                    <div id="product-devices" class="product-devices hidden-sm hidden-xs">
                                        <a href="#" class="desktop" data-device="desktop" title="Desktop"></a>
                                        <a href="#" class="tabletlandscape" data-device="tabletlandscape"
                                           title="<?php esc_html_e( 'Tablet Landscape (1024x768)', LIVEPREVIEWPRO_PLUGIN_NAME ); ?>"></a>
                                        <a href="#" class="tabletportrait" data-device="tabletportrait"
                                           title="<?php esc_html_e( 'Tablet Portrait (768x1024)', LIVEPREVIEWPRO_PLUGIN_NAME ); ?>"></a>
                                        <a href="#" class="mobilelandscape" data-device="mobilelandscape"
                                           title="<?php esc_html_e( 'Mobile Landscape (480x320)', LIVEPREVIEWPRO_PLUGIN_NAME ); ?>"></a>
                                        <a href="#" class="mobileportrait" data-device="mobileportrait"
                                           title="<?php esc_html_e( 'Mobile Portrait (320x480)', LIVEPREVIEWPRO_PLUGIN_NAME ); ?>"></a>
                                    </div>
								<?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="products" class="products-wrap">
                <div class="container">
                    <div class="products">
                        <div id="filters" class="filters hidden-xs">
                            <div class="row">
                                <div class="col-sm-7">
                                    <div id="filter-tags" class="filter filter-tags">
										<?php
										$len  = sizeof( $product_tags );
										$data = '';
										if ( $len ) {
											$index  = 0;
											$isMore = false;

											$data .= '<ul>' . PHP_EOL;
											foreach ( $product_tags as $tag => $count ) {
												$tag  = strtolower( $tag );
												$data .= '<li><a href="#" data-tag="' . ( $index == 0 ? '*' : $tag ) . '">' . $tag . ' <span>(' . $count . ')</span></a></li>' . PHP_EOL;
												$index ++;
												if ( $index == 3 && $len > 3 ) {
													$data   .= '<li class="has-child">' . PHP_EOL;
													$data   .= '<a href="#">' . esc_html__( 'More +', LIVEPREVIEWPRO_PLUGIN_NAME ) . '</a>' . PHP_EOL;
													$data   .= '<ul>' . PHP_EOL;
													$isMode = true;
												}
											}
											if ( $isMore ) {
												$data .= '</ul>' . PHP_EOL;
												$data .= '</li>' . PHP_EOL;
											}
											$data .= '</ul>' . PHP_EOL;

											echo wp_kses_post( $data );
										}
										?>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div id="filter-search" class="filter filter-search">
                                        <input type="text"
                                               placeholder="<?php esc_html_e( 'Search', LIVEPREVIEWPRO_PLUGIN_NAME ) ?>">
                                    </div>
                                </div>
                                <div class="col-sm-2">
                                    <div id="filter-years" class="filter filter-years">
										<?php
										$len  = sizeof( $product_years );
										$data = '';
										if ( $len ) {
											$data  .= '<ul>' . PHP_EOL;
											$data  .= '<li class="has-child"><a href="#">' . esc_html__( 'Years +', LIVEPREVIEWPRO_PLUGIN_NAME ) . '</a>' . PHP_EOL;
											$data  .= '<ul>' . PHP_EOL;
											$index = 0;
											foreach ( $product_years as $year => $value ) {
												$data .= '<li><a href="#" data-year="' . ( $index == 0 ? '*' : $value ) . '">' . $value . '</a></li>' . PHP_EOL;
												$index ++;
											}
											$data .= '</ul>' . PHP_EOL;
											$data .= '</li>' . PHP_EOL;
											$data .= '</ul>' . PHP_EOL;

											echo wp_kses_post( $data );
										}
										?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="product-list" class="product-list">
							<?php
							$index = 0;
							$data  = '';
							foreach ( $products as $product ) {
								if ( $index % 4 == 0 ) {
									if ( $index > 0 ) {
										$data .= '</div>' . PHP_EOL;
									}
									$data .= '<div class="row">' . PHP_EOL;
								}

								$active = false;
								if ( $product_id == null && $index == 0 ) {
									$active = true;
								} else {
									if ( $product->id != null && $product->id == $product_id ) {
										$active = true;
									}
								}

								$data .= '<div class="col-xs-6 col-sm-3">' . PHP_EOL;
								$data .= '<div class="product' . ( $active ? ' active' : '' ) . '" data-product="' . htmlspecialchars( json_encode( $product ), ENT_QUOTES, 'UTF-8' ) . '" data-product-id="' . $product->id . '">' . PHP_EOL;

								if ( $product->badge ) {
									$data .= '<span class="badge">' . $product->badge . '</span>' . PHP_EOL;
								}

								$data .= '<div class="demo">' . PHP_EOL;
								$data .= '<a class="link" href="#">' . PHP_EOL;
								$data .= '<img class="img-responsive" data-src="' . $product->thumb->url . '" src="' . $plugin_url . 'assets/images/thumb-blank.jpg" alt="' . $product->title . '">' . PHP_EOL;
								$data .= '</a>' . PHP_EOL;
								$data .= '</div>' . PHP_EOL;

								$data .= '<h3 class="title"><span>' . $product->title . '</span></h3>' . PHP_EOL;
								$data .= '</div>' . PHP_EOL;
								$data .= '</div>' . PHP_EOL;

								$index ++;
							}
							if ( $index > 0 ) {
								$data .= '</div>' . PHP_EOL;
							}
							echo wp_kses_post( $data );
							?>
                        </div>
                        <div id="pagination" class="pagination">
                        </div>
                    </div>
                </div>
            </div>
            <div class="iframe-wrap">
                <div class="iframe-loader">
                    <div class="loader-wrap">
                        <div class="loader">
                            <div class="loader-inner"></div>
                        </div>
                    </div>
                </div>
                <iframe id="iframe" class="iframe border" src="" frameborder="0"></iframe>
            </div>
		<?php } ?>
    </div>
</div>
<?php //wp_footer();
?>

<!-- styles end -->
<!-- scripts begin -->

<script type='text/javascript'
        src='<?php echo get_site_url(); ?>/wp-includes/js/jquery/jquery.js?ver=1.12.4-wp'></script>
<script type='text/javascript'
        src='<?php echo get_site_url(); ?>/wp-includes/js/jquery/jquery-migrate.min.js?ver=1.4.1'></script>
<script type="text/javascript" src="<?php echo DOLLIE_PREVIEW_URL . 'assets/js/main.min.js'; ?>"></script>
<script type="text/javascript"
        src="<?php echo DOLLIE_PREVIEW_URL . 'assets/js/lib/jquery.ellipsis.min.js'; ?>"></script>
<script type="text/javascript" src="<?php echo DOLLIE_PREVIEW_URL . 'assets/js/lib/jquery.history.min.js'; ?>"></script>
<script type="text/javascript" src="<?php echo DOLLIE_PREVIEW_URL . 'assets/js/lib/bootstrap.min.js'; ?>"></script>
<script type="text/javascript" src="<?php echo DOLLIE_PREVIEW_URL . 'assets/js/lib/lazyload.min.js'; ?>"></script>
</body>

</html>
