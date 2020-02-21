<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Utils\Api;
use Dollie\Core\Utils\Helpers;
use Dollie\Core\Log;
use WP_Query;

/**
 * Class Blueprints
 * @package Dollie\Core\Modules
 */
class Blueprints extends Singleton {

	/**
	 * @var mixed
	 */
	private $helpers;

	/**
	 * Backups constructor.
	 */
	public function __construct() {
		parent::__construct();

		$this->helpers = Helpers::instance();

		add_action( 'wf_before_container', [ $this, 'get_available_blueprints' ], 11 );


		foreach ( $this->helpers->get_dollie_gravity_form_ids( 'dollie-blueprint' ) as $form_id ) {
			add_action( 'gform_after_submission_' . $form_id, [ $this, 'deploy_new_blueprint' ], 10, 2 );
		}

		// Only apply to Form ID 11.
		add_filter( 'gform_pre_render', [ $this, 'list_blueprints' ] );
		add_action( 'template_redirect', [ $this, 'set_blueprint_cookie' ], - 99999 );
	}

	public function get_available_blueprints() {
		if ( isset( $_GET['page'] ) && is_singular( 'container' ) && $_GET['page'] === 'blueprint' ) {
			global $wp_query;
			if ( ob_get_length() > 0 ) {
				@ob_end_flush();
				@flush();
			}
			$post_id   = $wp_query->get_queried_object_id();
			$post_slug = get_queried_object()->post_name;
			$install   = $post_slug;
			$secret    = get_post_meta( $post_id, 'wpd_container_secret', true );
			$url       = $this->helpers->get_container_url( $post_id ) . '/' . $secret . '/codiad/backups/blueprints.php';

			$response = wp_remote_get( $url, [ 'timeout' => 20 ] );

			if ( is_wp_error( $response ) ) {
				return [];
			}

			$blueprints = json_decode( wp_remote_retrieve_body( $response ), true );

			if ( empty( $blueprints ) ) {
				return [];
			}

			$total_blueprints = array_filter( $blueprints, static function ( $value ) {
				return ! ( strpos( $value, 'restore' ) !== false );
			} );

			set_transient( 'dollie_' . $install . '_total_blueprints', count( $total_blueprints ), MINUTE_IN_SECONDS * 1 );
			update_post_meta( $post_id, 'wpd_installation_blueprints_available', count( $total_blueprints ) );

			return $blueprints;
		}
	}

	public function deploy_new_blueprint( $entry, $form ) {
		global $wp_query;
		$post_id   = $wp_query->get_queried_object_id();
		$post_slug = get_queried_object()->post_name;
		$time      = @date( 'd/M/Y:H:i' );

		//Only run the job on the container of the customer.
		$post_body = [
			'filter' => 'name: https://' . $post_slug . DOLLIE_DOMAIN . '-' . DOLLIE_RUNDECK_KEY
		];

		Api::postRequestRundeck( '1/job/b2fcd68d-3dab-4faf-95d8-6958c5811bae/run/', $post_body );

		update_post_meta( $post_id, 'wpd_blueprint_created', 'yes' );
		update_post_meta( $post_id, 'wpd_blueprint_time', $time );

		Log::add( $post_slug . ' updated/deployed a new Blueprint', '', 'blueprint' );
	}

	public function list_blueprints( $form ) {
		foreach ( $form['fields'] as $field ) {
			if ( $field['type'] !== 'radio' || strpos( $field['cssClass'], 'site-blueprints' ) === false ) {
				continue;
			}

			$blueprint_cookie = $_COOKIE['dollie_blueprint_id'];
			if ( $blueprint_cookie ) {
				$blueprint = $blueprint_cookie;
			} else {
				$blueprint = '';
			}

			// Get our available blueprints
			// Instantiate custom query

			$query = new WP_Query( [
				'post_type'      => 'container',
				'posts_per_page' => 1000,
				'meta_query'     => [
					'relation' => 'AND',
					[
						'key'   => 'wpd_blueprint_created',
						'value' => 'yes',
					],
					[
						'key'   => 'wpd_is_blueprint',
						'value' => 'yes',
					],
					[
						'key'     => 'wpd_installation_blueprint_title',
						'compare' => 'EXISTS',
					]
				],
				'p'              => $blueprint,
			] );

			$choices = [];

			// Output custom query loop
			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) {
					$query->the_post();

					$private = get_field( 'wpd_private_blueprint' );

					if ( $private === 'yes' && ! current_user_can( 'manage_options' ) ) {
						continue;
					}

					if ( get_field( 'wpd_blueprint_image' ) === 'custom' ) {
						$image = get_field( 'wpd_blueprint_custom_image' );
					} elseif ( get_field( 'wpd_blueprint_image' ) === 'theme' ) {
						$image = wpthumb( get_post_meta( get_the_ID(), 'wpd_installation_site_theme_screenshot', true ), 'width=900&crop=0' );
					} else {
						$image = get_post_meta( get_the_ID(), 'wpd_site_screenshot', true );
					}

					$choices[] = [
						'text'  => '<img data-toggle="tooltip" data-placement="bottom" title="' . get_post_meta( get_the_ID(), 'wpd_installation_blueprint_description', true ) . '" class="fw-blueprint-screenshot" src=' . $image . '>' . get_post_meta( get_the_ID(), 'wpd_installation_blueprint_title', true ),
						'value' => get_the_ID(),
                    ];
				}
			}

			$field['choices'] = $choices;

			wp_reset_postdata();
			wp_reset_query();
		}

		return $form;
	}

	public function set_blueprint_cookie() {
		if ( isset( $_GET['blueprint_id'] ) ) {
			$cookie_id = $_GET['blueprint_id'];
		}

		global $wp_query;
		$post_id        = $wp_query->get_queried_object_id();
		$setup_complete = get_post_meta( $post_id, 'wpd_container_based_on_blueprint', true );

		//No Cookies set? Check is parameter are valid
		if ( isset( $cookie_id ) ) {
			setcookie( 'dollie_blueprint_id', $cookie_id, time() + ( 86400 * 30 ), '/' );
		}
		if ( is_singular( 'container' ) && $setup_complete == 'yes' ) {
			setcookie( 'dollie_blueprint_id', '', time() - 3600, '/' );
		}
	}

	public function list_available_blueprints() {
		$blueprints = $this->get_available_blueprints();

		if ( empty( $blueprints ) ) {
			echo 'No Blueprints Created yet';
		} else {
			?>
            <ul class="list-unstyled">
			<?php foreach ( $blueprints as $blueprint ) : ?>
				<?php
				//Split info via pipe
				$info = explode( '|', $blueprint );
				if ( $info[1] === 'restore' ) {
					continue;
				}

				if ( strpos( $info[1], 'MB' ) !== false ) {
					$get_mb_size = str_replace( 'MB', '', $info[1] );
					$real_size   = $get_mb_size . ' MB';
				} else {
					$real_size = $info[1];
				}

				$size = '<br><span class="pull-right mt-2"><i class="fal fa-hdd"></i> Size ' . $real_size . '</span>';
				//Time is firt part but needs to be split
				$backup_date = explode( '_', $info[0] );
				//Date of backup
				$date        = strtotime( $backup_date[0] );
				$raw_time    = str_replace( '-', ':', $backup_date[1] );
				$pretty_time = date( 'g:i a', strtotime( $raw_time ) );

				//Time of backup
				$time = ' at ' . $pretty_time . '';
				//Size of backup
				//Format for compat with duplicity.
				$format_time    = str_replace( '-', ':', $backup_date[1] );
				$duplicity_time = $backup_date[0] . 'T' . $format_time . ':00';

				?>
                <li>
                    <i class='fal fa-calendar'></i> Created on <?php echo date( 'd F y', $date ) . $time . $size; ?>
                </li>
			<?php endforeach; ?>
			<?php
			echo '</ul>';
		}
	}

}
