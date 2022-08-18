<?php

namespace Dollie\Core\Modules;

use Dollie\Core\Singleton;

class Preview extends Singleton {
	/**
	 * Preview constructor
	 */
	public function __construct() {
		add_filter( 'show_admin_bar', '__return_false' );

		// Remove all WordPress actions
		remove_all_actions( 'wp_head' );
		remove_all_actions( 'wp_print_styles' );
		remove_all_actions( 'wp_print_head_scripts' );
		remove_all_actions( 'wp_footer' );

		// Handle `wp_head`
		add_action( 'wp_head', 'wp_enqueue_scripts', 1 );
		add_action( 'wp_footer', 'wp_print_footer_scripts', 20 );
		add_action( 'wp_head', 'wp_site_icon' );

		// Handle `wp_enqueue_scripts`
		remove_all_actions( 'wp_enqueue_scripts' );

		// Also remove all scripts hooked into after_wp_tiny_mce.
		remove_all_actions( 'after_wp_tiny_mce' );

		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ], 99999 );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ], 999999 );

		// Setup default heartbeat options
		add_filter(
			'heartbeat_settings',
			function ( $settings ) {
				$settings['interval'] = 15;

				return $settings;
			}
		);

		add_action( 'wp_body_open', [ $this, 'content' ] );
	}

	public function enqueue_scripts() {
		wp_enqueue_script( 'jquery-lazyload', DOLLIE_ASSETS_URL . 'js/preview/lib/lazyload.min.js', [ 'jquery' ], false, true );
		wp_enqueue_script( 'jquery-ellipsis', DOLLIE_ASSETS_URL . 'js/preview/lib/jquery.ellipsis.min.js', [ 'jquery' ], false, true );
		wp_enqueue_script( 'wpd-preview', DOLLIE_ASSETS_URL . 'js/preview/preview.min.js', [ 'jquery' ], false, true );

		wp_localize_script(
			'wpd-preview',
			'livepreviewpro_globals',
			[
				'plan'             => 'pro',
				'responsiveDevice' => null,
			]
		);
	}

	public function enqueue_styles() {
		wp_enqueue_style( 'bootstrap', DOLLIE_ASSETS_URL . 'css/preview/bootstrap.min.css', [], '4.3.1' );
		wp_enqueue_style( 'wpd-preview', DOLLIE_ASSETS_URL . 'css/preview/main-top.min.css', DOLLIE_VERSION );
	}

	public function content() {
		if ( isset( $_GET['type'] ) ) {
			if ( 'my-sites' === $_GET['type'] ) {
				if ( is_user_logged_in() ) {
					$author = get_current_user_id();
				} else {
					$author = '58687848382305067080201305060';
				}

				$args = [
					'author'         => $author,
					'post_type'      => 'container',
					'posts_per_page' => 20,
				];
			} elseif ( 'my-blueprints' === $_GET['type'] ) {
				$args = [
					'author'         => get_current_user_id(),
					'post_type'      => 'container',
					'posts_per_page' => -1,
					'post_status'    => 'publish',
					'meta_query'     => [
						'relation' => 'AND',
						[
							'key'   => 'dollie_container_type',
							'value' => '1',
						],
						[
							'key'   => 'wpd_blueprint_created',
							'value' => 'yes',
						],
						[
							'key'     => 'wpd_installation_blueprint_title',
							'compare' => 'EXISTS',
						],
					],

				];
			}
		} else {
			$args = [
				'post_type'      => 'container',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'meta_query'     => [
					'relation' => 'AND',
					[
						'key'   => 'dollie_container_type',
						'value' => '1',
					],
					[
						'key'   => 'wpd_blueprint_created',
						'value' => 'yes',
					],
					[
						'key'     => 'wpd_installation_blueprint_title',
						'compare' => 'EXISTS',
					],
				],

			];
		}

		$query       = new \WP_Query( $args );
		$posts       = $query->get_posts();
		$theme_array = [];

		foreach ( $posts as $post ) {
			$container = dollie()->get_container( $post );

			if ( is_wp_error( $container ) ) {
				continue;
			}

			if ( isset( $_GET['type'] ) && 'my-sites' === $_GET['type'] ) {
				$theme_array[] = [
					'active'      => 1,
					'id'          => $container->get_id(),
					'title'       => $container->get_title(),
					'title_short' => $container->get_title(),
					'url'         => $container->get_url( true ),
					'buy'         => html_entity_decode( $container->get_customer_login_url() ),
					'login_url'   => html_entity_decode( $container->get_customer_login_url() ),
					'thumb'       => [
						'url' => $container->get_screenshot(),
					],
					'info'        => $container->is_blueprint() ? $container->get_saved_description() : '',
					'preload'     => '0',
				];
			} else {
				$product_id = get_post_meta( $container->get_id(), 'wpd_installation_blueprint_hosting_product', true );

				if ( $product_id ) {

					if ( get_field( 'wpd_blueprint_image', $container->get_id() ) === 'custom' ) {
						$image = get_field( 'wpd_blueprint_custom_image', $container->get_id() );
					} elseif ( get_field( 'wpd_blueprint_image', $container->get_id() ) === 'theme' ) {
						$image = get_post_meta( $container->get_id(), 'wpd_blueprint_active_theme_screenshot_url', true );
					} else {
						$image = get_the_post_thumbnail_url( $container->get_id(), 'full' );
					}


					$theme_array[] = [
						'active'      => 1,
						'id'          => $container->get_id(),
						'title'       => $container->get_saved_title(),
						'title_short' => $container->get_name(),
						'url'         => $container->get_url(true),
						'buy'         => dollie()->subscription()->get_checkout_link(
							[
								'product_id'   => $product_id[0],
								'blueprint_id' => $container->get_id(),
							]
						),
						'login_url'   => $container->get_customer_login_url(),
						'thumb'       => [
							'url' => $image,
						],
						'info'        => $container->is_blueprint() ? $container->get_saved_description() : '',
						'tag'         => 'tag',
						'year'        => '2022',
						'preload'     => '0',
						'badge'       => 'Pro',
					];
				}
			}
		}

		wp_reset_postdata();

		// Config
		$products = [];

		if ( isset( $_GET['type'] ) && 'my-sites' === $_GET['type'] ) {
			$button_text = esc_html__( 'Login to Site', 'dollie' );
		} else {
			$button_text = esc_html__( 'Launch Site', 'dollie' );
		}

		$logo = get_field( 'wpd_dashboard_preview_logo', 'option' ) ?: '';

		$config = [
			'title'             => get_bloginfo( 'name' ),
			'logo'              => [
				'url'   => $logo,
				'href'  => get_site_url(),
				'blank' => 1,
			],
			'theme'             => 'main-top',
			'page'              => null,
			'productList'       => true,
			'responsiveDevices' => true,
			'responsiveDevice'  => 'desktop',
			'buyButton'         => true,
			'buyButtonText'     => $button_text,
			'closeIframe'       => true,
			'preload'           => false,
			'items'             => $theme_array,
		];

		$config = json_decode( json_encode( $config ) );

		if ( $config ) {
			foreach ( $config->items as $product ) {
				if ( $product->active ) {
					$products[] = $product;
				}
			}
		}

		// Init Tags
		$product_tags = [];
		foreach ( $products as $product_key => $product ) {

			if ( isset( $product->tag ) ) {
				$tag      = $product->tag;
				$is_found = false;

				foreach ( $product_tags as $key => $value ) {
					if ( $tag == $key ) {
						$product_tags[ $tag ] = $value + 1;
						$is_found             = true;
						break;
					}
				}
				if ( ! $is_found ) {
					$product_tags[ $tag ] = 1;
				}
			}
		}
		arsort( $product_tags );
		$product_tags = [ esc_html__( 'all', 'dollie' ) => count( $products ) ] + $product_tags;

		// Init Years
		$product_years = [];
		foreach ( $products as $product ) {
			if ( isset( $product->year ) ) {
				$year = $product->year;
				if ( $year && ! in_array( $year, $product_years, true ) ) {
					$product_years[] = $year;
				}
			}
		}
		if ( ! empty( $product_years ) ) {
			arsort( $product_years );
		}
		array_unshift( $product_years, esc_html__( 'all times', 'dollie' ) );

		// Setup Active Product
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

		if ( $config && $config->preload ) { ?>
			<div class="page-loader">
				<div class="loader-wrap">
					<div class="loader">

					</div>
				</div>
			</div>
		<?php } ?>

		<div class="livepreview-wrap">
			<div class="page">
				<?php if ( $config && count( $products ) > 0 ) { ?>
					<div id="header" class="header">
						<div class="container">
							<div class="row">
								<div class="hidden-xs col-sm-2 col-md-2">
									<?php if ( $config && $config->logo ) { ?>
										<?php if ( $config->logo->href ) { ?>
											<a class="logo" href="<?php echo esc_url( $config->logo->href ); ?>"
											   target="<?php echo( $config->logo->blank ? '_blank' : '_self' ); ?>">
												<img src="<?php echo esc_url( $config->logo->url ); ?>">
											</a>
										<?php } else { ?>
											<div class="logo">
												<img src="<?php echo esc_url( $config->logo->url ); ?>">
											</div>
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
									<div class="clearfix product-toolbar">
										<?php if ( $config->closeIframe ) { ?>
											<a id="product-frame-close" class="product-frame-close" href="#"
											   title="<?php esc_html_e( 'close iframe', 'dollie' ); ?>">
												<span class="dashicons dashicons-no-alt"></span>
											</a>
										<?php } ?>
										<?php if ( $config->buyButton ) { ?>
											<div class="product-buttons">
												<a id="buy" class="btn btn-success" href="#" style="display:none">
													<?php echo esc_html( $config->buyButtonText ); ?>
												</a>
											</div>
										<?php } ?>
										<?php if ( $config->responsiveDevices ) { ?>
											<div id="product-devices" class="product-devices hidden-sm hidden-xs">
												<a href="#" class="desktop" data-device="desktop" title="Desktop"></a>
												<a href="#" class="tabletlandscape" data-device="tabletlandscape"
												   title="<?php esc_html_e( 'Tablet Landscape (1024x768)', 'dollie' ); ?>"></a>
												<a href="#" class="tabletportrait" data-device="tabletportrait"
												   title="<?php esc_html_e( 'Tablet Portrait (768x1024)', 'dollie' ); ?>"></a>
												<a href="#" class="mobilelandscape" data-device="mobilelandscape"
												   title="<?php esc_html_e( 'Mobile Landscape (480x320)', 'dollie' ); ?>"></a>
												<a href="#" class="mobileportrait" data-device="mobileportrait"
												   title="<?php esc_html_e( 'Mobile Portrait (320x480)', 'dollie' ); ?>"></a>
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
										<div class="col-sm-8">
											<div id="filter-tags" class="filter filter-tags">
												<?php
												$len  = sizeof( $product_tags );
												$data = '';
												if ( $len ) {
													$index  = 0;
													$isMore = false;

													$data .= '<ul>' . PHP_EOL;
													foreach ( $product_tags as $tag => $count ) {
														$tag   = strtolower( $tag );
														$data .= '<li><a href="#" data-tag="' . ( $index == 0 ? '*' : $tag ) . '">' . $tag . ' <span>(' . $count . ')</span></a></li>' . PHP_EOL;
														$index ++;
														if ( $index == 3 && $len > 3 ) {
															$data  .= '<li class="has-child">' . PHP_EOL;
															$data  .= '<a href="#">' . esc_html__( 'More +', 'dollie' ) . '</a>' . PHP_EOL;
															$data  .= '<ul>' . PHP_EOL;
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
										<div class="col-sm-4">
											<div id="filter-search" class="filter filter-search">
												<input type="text"
													   placeholder="<?php esc_html_e( 'Search', 'dollie' ); ?>">
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

										if ( isset( $product->badge ) ) {
											$data .= '<span class="badge">' . $product->badge . '</span>' . PHP_EOL;
										}

										$data .= '<div class="demo">' . PHP_EOL;
										$data .= '<a class="link" href="#">' . PHP_EOL;
										$data .= '<img class="img-responsive" data-src="' . $product->thumb->url . '" src="' . DOLLIE_ASSETS_URL . 'img/preview/thumb-blank.jpg" alt="' . $product->title . '">' . PHP_EOL;
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
						<iframe id="iframe" class="border iframe" src="" frameborder="0"></iframe>
					</div>
				<?php } ?>
			</div>
		</div>
		<?php
	}
}
