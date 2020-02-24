<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Utils\Api;
use Dollie\Core\Log;
use GFFormsModel;

/**
 * Class LaunchSite
 * @package Dollie\Core\Modules
 */
class LaunchSite extends Singleton {
    

	/**
	 * LaunchSite constructor.
	 */
	public function __construct() {
		parent::__construct();

		$launch_forms = dollie()->helpers()->get_dollie_gravity_form_ids( 'dollie-launch' );
		foreach ( $launch_forms as $form_id ) {
			add_action( 'gform_field_validation_' . $form_id, [ $this, 'add_new_site' ], 10, 4 );
		}

		add_action( 'wp_footer', [ $this, 'launch_splash' ] );
		add_filter( 'gform_field_value_siteurl', [ $this, 'populate_site_url' ] );
		add_action( 'template_redirect', [ $this, 'redirect_to_container_launch' ] );


	}

	public function add_new_site( $validation_result ) {
		$form  = $validation_result['form'];
		$entry = GFFormsModel::get_current_lead();

		$domain           = rgar( $entry, '1' );
		$email            = rgar( $entry, '2' );
		$demo             = rgar( $entry, '3' );
		$blueprint_cookie = $_COOKIE['dollie_blueprint_id'];
		if ( $blueprint_cookie ) {
			$blueprint = $blueprint_cookie;
		} else {
			$blueprint = rgar( $entry, '4' );
		}

		$post_body = [
			'domain'          => $domain . DOLLIE_DOMAIN,
			'package'         => DOLLIE_PACKAGE,
			'containerMemory' => DOLLIE_MEMORY,
			'username'        => 'sideadmin',
			'password'        => '1234567890',
			'description'     => $email . ' | ' . get_site_url(),
			'envVars'         => [
				'S5_DEPLOYMENT_URL'          => get_site_url(),
				'S5_EMAIL_DELIVERY_USERNAME' => get_option( 'options_wpd_delivery_username' ),
				'S5_EMAIL_DELIVERY_PORT'     => get_option( 'options_wpd_delivery_smtp' ),
				'S5_EMAIL_DELIVERY_HOST'     => get_option( 'options_wpd_delivery_smtp_host' ),
				'S5_EMAIL_DELIVERY_EMAIL'    => get_option( 'options_wpd_delivery_email' ),
				'S5_EMAIL_DELIVERY_PASSWORD' => get_option( 'options_wpd_delivery_password' )
			]
		];

		$answer = Api::postRequestDollie( '', $post_body, 45 );

		if ( is_wp_error( $answer ) ) {
			Log::add( $domain . ' API error for ' . DOLLIE_INSTALL . ' (see log)', print_r( $answer, true ), 'deploy' );

			if ( $field->id === '1' ) {
				$field_page = $field->pageNumber;
				// validation failed
				$validation_result['is_valid'] = false;
				$field->failed_validation      = true;
				$field->validation_message     = 'Sorry, We could not launch this site. Please try again with a different URL. Keep having problems? Please get in touch with our support!';
			}
		}

		Log::add( $domain . ' API request made to Dollie install ' . DOLLIE_INSTALL . ' (see log)', print_r( $answer, true ), 'deploy' );

		$response = json_decode( $answer, true );

		// Show an error of S5 API can't add the Route.
		if ( ! array_key_exists( 'id', $response ) ) {

			if ( $field->id === '1' ) {
				$field_page = $field->pageNumber;
				// validation failed
				$validation_result['is_valid'] = false;
				$field->failed_validation      = true;
				$field->validation_message     = 'Sorry, We could not launch this site. Please try again with a different URL. Keep having problems? Please get in touch with our support!';
			}
		} else {

			sleep( 5 );

			// $deploy = Api::postRequestDollie( $response['id'] . '/deploy', $update_post_body, 120 );

			// Todo: check this log
			// Log::add( $domain . ' Creating Site Dollie (see log)' . $post_slug, print_r( $deploy, true ), 'deploy' );

			sleep( 20 );

			$status = false;

			while ( $status === false ) {
				sleep( 5 );
				if ( $this->test_site_deployment( 'https://' . $domain . DOLLIE_DOMAIN ) === 200 ) {
					$status = true;
				}
			}

			$update_container = Api::getRequestDollie( $response['id'] . '/', 120 );

			$update_answer = wp_remote_retrieve_body( $update_container );

			//Log::add( $domain . 'Deploying created site ' . $post_slug, print_r( $update_container, true ), 'deploy' );

			sleep( 3 );

			$update_response = json_decode( $update_answer, true );

			// Show an error of S5 API has not completed the setup.
			if ( ! array_key_exists( 'id', $update_response ) ) {
				if ( $field->id === '1' ) {
					$validation_result['is_valid'] = false;
					$field->failed_validation      = true;
					$field->validation_message     = 'Sorry, It seems like there was an issue with launching your new site. Our support team has been notified';
				}
			} else {
				// Set the post ID so that we know the post was created successfully
				$my_post = [
					'comment_status' => 'closed',
					'ping_status'    => 'closed',
					'post_author'    => $user->id,
					'post_name'      => $domain,
					'post_title'     => $domain,
					'post_status'    => 'publish',
					'post_type'      => 'container',
				];

				$post_id = wp_insert_post( $my_post );

				Log::add( 'New Site ' . $domain . ' has container ID of ' . $update_response['id'], '', 'deploy' );

				add_post_meta( $post_id, 'wpd_container_id', $update_response['id'], true );
				add_post_meta( $post_id, 'wpd_container_ssh', $update_response['containerSshPort'], true );
				add_post_meta( $post_id, 'wpd_container_user', $update_response['containerSshUsername'], true );
				add_post_meta( $post_id, 'wpd_container_port', $update_response['containerSshPort'], true );
				add_post_meta( $post_id, 'wpd_container_password', $update_response['containerSshPassword'], true );
				add_post_meta( $post_id, 'wpd_container_ip', $update_response['containerHostIpAddress'], true );
				add_post_meta( $post_id, 'wpd_container_deploy_time', $update_response['deployedAt'], true );
				add_post_meta( $post_id, 'wpd_container_uri', $update_response['uri'], true );
				add_post_meta( $post_id, 'wpd_container_status', 'start', true );
				add_post_meta( $post_id, 'wpd_container_launched_by', $email, true );

				//Set Flag if Demo
				if ( $demo === 'yes' ) {
					add_post_meta( $post_id, 'wpd_container_is_demo', 'yes', true );
				}

				sleep( 3 );

				//Register Node via Rundeck
				ob_start(); ?>
                <node name="<?php echo $update_response['uri'] . '-' . DOLLIE_RUNDECK_KEY; ?>"
                      description="Deployed via <?php echo get_site_url(); ?>"
                      tags="<?php echo $update_response['id']; ?>,<?php echo get_site_url(); ?>,<?php echo $email; ?>,<?php echo DOLLIE_MEMORY; ?>,<?php echo $email; ?>,<?php echo DOLLIE_RUNDECK_KEY; ?>,blueprint-id-<?php echo $blueprint; ?>"
                      hostname="<?php echo $update_response['containerHostIpAddress']; ?>:<?php echo $update_response['containerSshPort']; ?>"
                      username="root"/>
                </project><?php
				//Create our new node details
				$new_node = ob_get_clean();

				//Grab our existing node details
				$all_nodes = ContainerRegistration::instance()->get_rundeck_nodes();

				$request_body = str_replace( '</project>', $new_node, $all_nodes );

				//Set up the request
				$rundeck_update = wp_remote_post( DOLLIE_RUNDECK_URL . '/api/23/project/Dollie-Containers/source/1/resources?format=xml', [
					'headers' => [
						'X-Rundeck-Auth-Token' => DOLLIE_RUNDECK_TOKEN,
						'Content-Type'         => 'text/xml',
					],
					'body'    => $request_body,
				] );

				if ( is_wp_error( $rundeck_update ) ) {
					Log::add( 'Node could not be registered for ' . $domain, print_r( $rundeck_update, true ), 'error' );
				} else {
					add_post_meta( $post_id, 'wpd_node_added', 'yes', true );
					Log::add( $domain . ' was added as a Rundeck node' );
				}

				//Set Flag if Blueprint
				if ( $blueprint !== '' ) {
					sleep( 6 );
					add_post_meta( $post_id, 'wpd_container_based_on_blueprint', 'yes', true );
					add_post_meta( $post_id, 'wpd_container_based_on_blueprint_id', $blueprint, true );

					$blueprint_url     = get_post_meta( $blueprint, 'wpd_container_uri', true );
					$blueprint_install = str_replace( 'https://', '', $blueprint_url );

					$blueprint_body = [
						'filter'    => 'name: https://' . $domain . DOLLIE_DOMAIN . '-' . DOLLIE_RUNDECK_KEY,
						'argString' => '-url ' . $blueprint_install . ' -domain ' . $domain . DOLLIE_DOMAIN
					];

					//Set up the request
					Api::postRequestRundeck( '1/job/a1a56354-a08e-4e7c-9dc5-bb72bb571dbe/run/', $blueprint_body );

					Log::add( $domain . ' will use blueprint' . $blueprint_install, '', 'deploy' );
					update_post_meta( $post_id, 'wpd_blueprint_deployment_complete', 'yes' );
				}

				if ( $demo === 'yes' && is_page( 'get-started' ) && function_exists( 'wpd_apply_partner_template' ) ) {
					Log::add( $domain . ' starts partner deploy', '', 'deploy' );
					wpd_apply_partner_template( $post_id, $domain, rgar( $entry, '6' ), rgar( $entry, '8' ), rgar( $entry, '9' ) );
				}

				if ( $demo === 'yes' && is_page( 'demo-deploy' ) && function_exists( 'wpd_apply_demo_template' ) ) {
					Log::add( $domain . ' starts partner deploy', '', 'deploy' );
					wpd_apply_demo_template( $post_id, $domain, rgar( $entry, '6' ), rgar( $entry, '8' ), rgar( $entry, '9' ), rgar( $entry, '12' ), rgar( $entry, '13' ) );
				}

				$validation_result['is_valid'] = true;
			}
		}

		//Assign modified $form object back to the validation result
		$validation_result['form'] = $form;

		return $validation_result;
	}

	public function launch_splash() {
		if ( is_page_template( 'launch-site.php' ) ) {
			?>
            <script type="text/javascript">
                jQuery(document).ready(function ($) {
					<?php if (isset( $_COOKIE['dollie_blueprint_id'] )) { ?>
                    $("#field_14_4").addClass("hidden");
					<?php } ?>
                    if ($('#input_14_4 li').length === 0) {
                        $("#field_14_4").addClass("hidden");
                    }

                    jQuery("#field_14_1 .ginput_container").append("<span class='domain-suffix'><strong><?php echo DOLLIE_DOMAIN; ?></strong></span>");
                });
            </script>
            <div data-backdrop="static" data-keyboard="false" class="modal" id="modal-large" tabindex="-1" role="dialog"
                 aria-labelledby="modal-large" style="display: none;" aria-hidden="true">
                <div class="modal-dialog modal-lg text-center" role="document">
                    <div class="modal-content mt-100">
                        <div class="block block-themed block-transparent mb-0">
                            <div class="loader-wrap bg-primary">

                                <div class="cube-wrapper">
                                    <div class="cube-folding">
                                        <span class="leaf1"></span>
                                        <span class="leaf2"></span>
                                        <span class="leaf3"></span>
                                        <span class="leaf4"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="block-content mt-50 text-align-center pb-30 pl-50 pr-50 nice-copy-story">
                                <div id="content-1">
									<?php if ( get_field( 'wpd_launch_step_1', 'option' ) ) : ?>
										<?php the_field( 'wpd_launch_step_1', 'option' ); ?>
									<?php else : ?>
                                        <h4 class="mt-0"><i class="fab fa-docker"></i> Launching New Cloud Container
                                            <span class="dots"></span></h4>
                                        <p> We use state-of-the-art technology like PHP7, Nginx, Redis, Memcached and
                                            MariaDB inside isolated cloud containers to guarantee excellent performance
                                            and security for each and every site on our platform.</p>
									<?php endif; ?>
                                </div>
                                <div id="content-2">
									<?php if ( get_field( 'wpd_launch_step_2', 'option' ) ) : ?>
										<?php the_field( 'wpd_launch_step_2', 'option' ); ?>
									<?php else : ?>
                                        <h4 class="mt-0"><i class="fab fa-wordpress-simple"></i> Setting up WordPress
                                            <span class="dots"></span></h4>
                                        <p>
                                            We manage important WordPress security updates for you, and notify you when
                                            compromised plugins and themes with security issues are found. And of course
                                            free SSL certificates for your site are set up automatically..
                                        </p>
									<?php endif; ?>
                                </div>

                                <div id="content-3">
									<?php if ( get_field( 'wpd_launch_step_3', 'option' ) ) : ?>
										<?php the_field( 'wpd_launch_step_3', 'option' ); ?>
									<?php else : ?>
                                        <h4 class="mt-0"><i class="fal fa-gem"></i> Testing & Verifying
                                            Installation<span class="dots"></span></h4>
                                        <p>
                                            We're running some automated tests to make sure everything is set up and
                                            ready to go before you start building your brand new site!
                                        </p>
									<?php endif; ?>
                                </div>
                                <div id="content-4">
									<?php if ( get_field( 'wpd_launch_step_4', 'option' ) ) : ?>
										<?php the_field( 'wpd_launch_step_4', 'option' ); ?>
									<?php else : ?>
                                        <h4 class="mt-0"><i class="fal fa-box-check"></i> Site Setup Complete <span
                                                    class="dots"></span></h4>
                                        <p>
                                            Your new site Wordpress site is deployed to our cloud! You'll be redirected
                                            to the site setup wizard in just a couple of seconds...
                                        </p>
									<?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
			<?php
		}
	}

	public function wdp_populate_site_url( $form ) {
		// Grab URL from HTTP Server Var and put it into a variable
		// Return that value to the form
		return esc_url_raw( get_site_url() );
	}

	public function test_site_deployment( $url ) {
		$response = wp_remote_get( $url );

		return wp_remote_retrieve_response_code( $response );
	}

	public function redirect_to_container_launch() {
		if ( dollie()->helpers()->count_total_containers() === 0 && ! is_page( 'launch-site' ) && current_user_can( 'manage_options' ) ) {
			wp_redirect( get_site_url() . '/launch-site' );
			exit;
		}
	}

	public function staging_limit_reached() {
		return dollie()->helpers()->count_total_containers() >= 3 && ! Options::instance()->is_live();
	}

}
