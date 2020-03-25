<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Utils\Api;
use Dollie\Core\Log;
use WP_Query;

/**
 * Class Blueprints
 * @package Dollie\Core\Modules
 */
class Blueprints extends Singleton {

	/**
	 * Backups constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'wf_before_container', [ $this, 'get_available_blueprints' ], 11 );

		foreach ( dollie()->get_dollie_gravity_form_ids( 'dollie-blueprint' ) as $form_id ) {
			add_action( 'gform_after_submission_' . $form_id, [ $this, 'deploy_new_blueprint' ], 10, 2 );
		}

		// Only apply to Form ID 11.
		add_filter( 'gform_pre_render', [ $this, 'list_blueprints' ] );
		add_action( 'template_redirect', [ $this, 'set_blueprint_cookie' ], - 99999 );
	}

	public function get_available_blueprints() {
		if ( isset( $_GET['page'] ) && $_GET['page'] === 'blueprint' && is_singular( 'container' ) ) {
			if ( ob_get_length() > 0 ) {
				@ob_end_flush();
				@flush();
			}

			$currentQuery = dollie()->get_current_object();

			$secret = get_post_meta( $currentQuery->id, 'wpd_container_secret', true );
			$url    = dollie()->get_container_url() . '/' . $secret . '/codiad/backups/blueprints.php';

			$requestGetBlueprint = Api::post( Api::ROUTE_BLUEPRINT_GET, [
				'container_url'    => $url,
				'container_secret' => $secret
			] );

			$responseGetBlueprint = json_decode( wp_remote_retrieve_body( $requestGetBlueprint ), true );

			if ( $responseGetBlueprint['status'] === 500 ) {
				return [];
			}

			$blueprints = json_decode( $responseGetBlueprint['body'], true );

			if ( empty( $blueprints ) ) {
				return [];
			}

			$total_blueprints = array_filter( $blueprints, static function ( $value ) {
				return ! ( strpos( $value, 'restore' ) !== false );
			} );

			set_transient( 'dollie_' . $currentQuery->slug . '_total_blueprints', count( $total_blueprints ), MINUTE_IN_SECONDS * 1 );
			update_post_meta( $currentQuery->id, 'wpd_installation_blueprints_available', count( $total_blueprints ) );

			return $blueprints;
		}
	}

	public function deploy_new_blueprint( $entry, $form ) {
		$currentQuery = dollie()->get_current_object();

		Api::post( Api::ROUTE_BLUEPRINT_CREATE_OR_UPDATE, [ 'container_url' => dollie()->get_container_url() ] );

		update_post_meta( $currentQuery->id, 'wpd_blueprint_created', 'yes' );
		update_post_meta( $currentQuery->id, 'wpd_blueprint_time', @date( 'd/M/Y:H:i' ) );

		Log::add( $currentQuery->slug . ' updated/deployed a new Blueprint', '', 'blueprint' );
	}

	public function list_blueprints( $form ) {
		foreach ( $form['fields'] as $field ) {
			if ( $field['type'] !== 'radio' || strpos( $field['cssClass'], 'site-blueprints' ) === false ) {
				continue;
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
				'p'              => isset( $_COOKIE['dollie_blueprint_id'] ) ? $_COOKIE['dollie_blueprint_id'] : '',
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

		$currentQuery   = dollie()->get_current_object();
		$setup_complete = get_post_meta( $currentQuery->id, 'wpd_container_based_on_blueprint', true );

		// No Cookies set? Check is parameter are valid
		if ( isset( $cookie_id ) ) {
			setcookie( 'dollie_blueprint_id', $cookie_id, time() + ( 86400 * 30 ), '/' );
		}

		if ( $setup_complete === 'yes' && is_singular( 'container' ) ) {
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
				// Split info via pipe
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

				// Time is first part but needs to be split
				$backup_date = explode( '_', $info[0] );

				// Date of backup
				$date        = strtotime( $backup_date[0] );
				$raw_time    = str_replace( '-', ':', $backup_date[1] );
				$pretty_time = date( 'g:i a', strtotime( $raw_time ) );

				// Time of backup
				$time = ' at ' . $pretty_time . '';
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
