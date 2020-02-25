<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Utils\Api;
use Dollie\Core\Log;
use Dollie\Core\Utils\Tpl;
use GFFormDisplay;
use GFFormsModel;
use RGFormsModel;

/**
 * Class DomainWizard
 * @package Dollie\Core\Modules
 */
class DomainWizard extends Singleton {

	/**
	 * DomainWizard constructor.
	 */
	public function __construct() {
		parent::__construct();

		$domain_forms = dollie()->get_dollie_gravity_form_ids( 'dollie-domain' );
		foreach ( $domain_forms as $form_id ) {
			add_filter( 'gform_validation_' . $form_id, [ $this, 'domain_wizard_add_domain' ], 20 );
			add_filter( 'gform_validation_' . $form_id, [ $this, 'domain_wizard_add_cloudflare' ], 20 );
			add_filter( 'gform_validation_' . $form_id, [ $this, 'domain_wizard_add_cloudflare_zone' ], 30 );
			add_filter( 'gform_validation_' . $form_id, [ $this, 'search_and_replace_domain' ], 30 );
			add_action( 'gform_post_paging_' . $form_id, [ $this, 'complete_migration_wizard' ], 10, 3 );
		}

		add_action( 'template_redirect', [ $this, 'continue_domain_setup' ] );
		add_filter( 'gform_pre_render', [ $this, 'gform_skip_page' ] );
		add_filter( 'gform_validation_message', [
			$this,
			'change_message'
		], dollie()->get_dollie_gravity_form_ids( 'dollie-domain' )[0], 2 );

		add_filter( 'gform_field_input', [ $this, 'populate_instruction_fields' ], 10, 5 );

		$this->register_confirmation_fields( dollie()->get_dollie_gravity_form_ids( 'dollie-domain' ), [
			55,
			60
		] );
		add_filter( 'gform_validation', [ $this, 'gfcf_validation' ] );
	}

	public function domain_wizard_add_domain( $validation_result ) {
		$currentQuery = dollie()->get_current_object();
		$request      = get_transient( 'dollie_s5_container_details_' . $currentQuery->slug . '' );

		$form         = $validation_result['form'];
		$entry        = GFFormsModel::get_current_lead();
		$current_page = rgpost( 'gform_source_page_number_' . $form['id'] ) ?: 1;

		// Our form field ID + User meta fields
		$domain = rgar( $entry, '55' );

		// Are we on the first page?
		if ( $current_page === 1 ) {

			$answer = Api::postRequestDollie( $request->id . '/routes', [ 'domain' => $domain ], 45 );

			$response = json_decode( $answer, true );

			// Show an error of S5 API can't add the Route.
			if ( ! array_key_exists( 'path', $response ) ) {
				// finding Field with ID of 1 and marking it as failed validation
				foreach ( $form['fields'] as $field ) {

					if ( $field->id === '55' ) {
						// validation failed
						$validation_result['is_valid'] = false;
						$field->failed_validation      = true;
						$field->validation_message     = 'Sorry, We could not link this domain to your site. This could be because the domain is already registered for another site in our network. It could also be an issue on our end! Please try again or <a href="https://dollie.co/support-redirect">Contact Support</a>';
					}

				}
			}
			if ( array_key_exists( 'path', $response ) ) {
				// Save the Domain Data and make another S5 Request for the WWW domain.
				update_post_meta( $currentQuery->id, 'wpd_domain_id', $response['id'] );
				update_post_meta( $currentQuery->id, 'wpd_domains', $domain );

				$update_answer   = Api::postRequestDollie( $request->id . '/routes', [ 'domain' => 'www.' . $domain ], 45 );
				$update_response = json_decode( $update_answer, true );

				// Also save the WWW Domain data.
				update_post_meta( $currentQuery->id, 'wpd_www_domain_id', $update_response['id'] );
				Log::add( $currentQuery->slug . ' linked up domain ' . $domain . '' );
			}

			// Assign modified $form object back to the validation result
			$validation_result['form'] = $form;

			return $validation_result;

		}

		// Just return the form
		$validation_result['form'] = $form;

		return $validation_result;
	}

	public function domain_wizard_add_cloudflare( $validation_result ) {
		$currentQuery = dollie()->get_current_object();
		$install = dollie()->get_container_url();
		$form         = $validation_result['form'];
		$entry        = GFFormsModel::get_current_lead();
		$current_page = rgpost( 'gform_source_page_number_' . $form['id'] ) ?: 1;

		// Are the on the CloudFlare Setup Page?
		if ( $current_page === 2 ) {
			// Our form field ID + User meta fields
			$ssl_type = rgar( $entry, '11' );
			$email    = rgar( $entry, '50' );
			$api_key  = rgar( $entry, '27' );

			if ( $ssl_type === 'cloudflare' ) {

				// Set up the request to CloudFlare to verify
				$update = wp_remote_post( 'https://api.cloudflare.com/client/v4/user', [
					'method'  => 'GET',
					'timeout' => 45,
					'headers' => [
						'X-Auth-Email' => $email,
						'X-Auth-Key'   => $api_key,
						'Content-Type' => 'application/json',
					],
				] );

				// Parse the JSON request
				$answer   = wp_remote_retrieve_body( $update );
				$response = json_decode( $answer, true );

				// Throw an error if CloudFlare Details are incorrect.
				if ( ! isset( $response['result']['id'] ) ) {
					// finding Field with ID of 1 and marking it as failed validation
					foreach ( $form['fields'] as $field ) {

						if ( $field->id === '27' ) {
							// validation failed
							$validation_result['is_valid'] = false;
							$field->failed_validation      = true;
							$field->validation_message     = 'Your CloudFlare Email or API key is incorrect. Please try again or <a href="https://dollie.co/support-redirect">Contact Support</a>';
						}

					}
				}
				if ( isset( $response['result']['id'] ) ) {
					// Success now send the Worker request
					// This job will install + activate the CloudFlare plugin and populate the email + API key fields for the CloudFlare Options.
					$post_body = [
						'filter'    => 'name: ' . $install . '-' . DOLLIE_WORKER_KEY,
						'argString' => '-email ' . $email . ' -key ' . $api_key
					];

					Api::postRequestWorker( '1/job/3725d807-435e-400c-8679-2a438f765002/run/', $post_body );

					// All done, update user meta!
					update_post_meta( $currentQuery->id, 'wpd_cloudflare_email', $email );
					update_post_meta( $currentQuery->id, 'wpd_cloudflare_active', 'yes' );
					update_post_meta( $currentQuery->id, 'wpd_cloudflare_id', $response['result']['id'] );
					update_post_meta( $currentQuery->id, 'wpd_cloudflare_api', $api_key );
					Log::add( $currentQuery->slug . ' linked up CloudFlare account' );
				}

			} else {
				update_post_meta( $currentQuery->id, 'wpd_letsencrypt_enabled', 'yes' );
			}
		}

		$validation_result['form'] = $form;

		return $validation_result;
	}

	public function domain_wizard_add_cloudflare_zone( $validation_result ) {
		$currentQuery = dollie()->get_current_object();

		// Setup the Form
		$entry = GFFormsModel::get_current_lead();

		// Form Variables
		$form         = $validation_result['form'];
		$current_page = rgpost( 'gform_source_page_number_' . $form['id'] ) ?: 1;

		// Are the on the right page?
		if ( $current_page === 3 ) {
			// Our form field ID + User meta fields
			$email   = get_post_meta( $currentQuery->id, 'wpd_cloudflare_email', true );
			$api_key = get_post_meta( $currentQuery->id, 'wpd_cloudflare_api', true );
			$zone    = rgar( $entry, '66' );

			// Set up the request to CloudFlare to verify
			$update = wp_remote_post( 'https://api.cloudflare.com/client/v4/zones/' . $zone, [
				'method'  => 'GET',
				'timeout' => 45,
				'headers' => [
					'X-Auth-Email' => $email,
					'X-Auth-Key'   => $api_key,
					'Content-Type' => 'application/json',
				],
			] );

			// Parse the JSON request
			$answer   = wp_remote_retrieve_body( $update );
			$response = json_decode( $answer, true );

			// Throw an error if the Zone ID is not found.
			if ( ! isset( $response['result']['id'] ) ) {
				// finding Field with ID of 1 and marking it as failed validation
				foreach ( $form['fields'] as $field ) {

					if ( $field->id === '66' ) {
						// validation failed
						$validation_result['is_valid'] = false;
						$field->failed_validation      = true;
						$field->validation_message     = 'Your CloudFlare Zone ID is incorrect. Please make sure you copy and pasted the right ID without extra spaces. Need help? <a href="https://dollie.co/support-redirect">Contact Support</a>';
					}

				}
			} else {
				// Save our CloudFlare Zone ID to user meta.
				update_post_meta( $currentQuery->id, 'wpd_cloudflare_zone_id', $zone );
				Log::add( 'Cloudflare Zone ID ' . $zone . ' is used for analytics for ' . $currentQuery->slug );
			}
		}

		return $validation_result;
	}

	public function search_and_replace_domain( $validation_result ) {
		$currentQuery = dollie()->get_current_object();

		// Domain
		$container = get_post_meta( $currentQuery->id, 'wpd_container_id', true );
		$le_domain = get_post_meta( $currentQuery->id, 'wpd_domain_id', true );

		$form = $validation_result['form'];

		// Form Variables
		if ( isset( $validation_result['form'] ) ) {
			$current_page = rgpost( 'gform_source_page_number_' . $form['id'] ) ? rgpost( 'gform_source_page_number_' . $form['id'] ) : 1;
		}

		// Are the on the Domain Replace page?
		if ( isset( $current_page ) && $current_page === 5 ) {
			$www = rgpost( 'input_81' );

			if ( $www === 'yes' ) {
				$domain = 'www.' . get_post_meta( $currentQuery->id, 'wpd_domains', true );
			} else {
				$domain = get_post_meta( $currentQuery->id, 'wpd_domains', true );
			}

			$post_body = [
				'filter'    => 'name: https://' . $currentQuery->slug . DOLLIE_DOMAIN . '-' . DOLLIE_WORKER_KEY,
				'argString' => '-install ' . $currentQuery->slug . DOLLIE_DOMAIN . ' -domain ' . $domain
			];

			$update = Api::postRequestWorker( '1/job/ba12c626-a9aa-4abc-b239-278238f1b2a9/run/', $post_body );

			$answer = wp_remote_retrieve_body( $update );

			Log::add( 'Search and replace ' . $currentQuery->slug . ' to update URL to ' . $domain . ' has started', $answer );


			$le = get_post_meta( $currentQuery->id, 'wpd_letsencrypt_enabled', true );
			if ( $le === 'yes' ) {
				$le_answer = Api::postRequestDollie( $container . '/routes/' . $le_domain . '/autoCert', [], 90 );

				// Show an error of S5 API can't add the Route.
				if ( is_wp_error( $le_answer ) ) {
					// finding Field with ID of 1 and marking it as failed validation
					foreach ( $form['fields'] as $field ) {

						if ( $field->id === '72' ) {
							// validation failed
							$validation_result['is_valid'] = false;
							$field->failed_validation      = true;
							$field->validation_message     = 'Sorry, We could not generate a SSL certificate for this domain. Please contact support so we can look into why this has happened.';
						}

					}
				} else {
					update_post_meta( $currentQuery->id, 'wpd_letsencrypt_setup_complete', 'yes' );
				}
			}

			// We will add an artificial delay because if we're dealing with a big database it could take a bit of time to run the search and replace via the Worker/WP-CLI command.
			sleep( 20 );
		}

		return $validation_result;
	}

	public function continue_domain_setup() {
		if ( isset( $_GET['page'] ) && ! isset( $_GET['form_page'] ) && $_GET['page'] === 'domain' && is_singular( 'container' ) ) {
			$currentQuery = dollie()->get_current_object();

			$has_domain     = get_post_meta( $currentQuery->id, 'wpd_domains', true );
			$has_cloudflare = get_post_meta( $currentQuery->id, 'wpd_cloudflare_email', true );
			$has_analytics  = get_post_meta( $currentQuery->id, 'wpd_cloudflare_zone_id', true );
			$has_le         = get_post_meta( $currentQuery->id, 'wpd_letsencrypt_enabled', true );

			if ( $has_cloudflare && ! $has_analytics ) {
				wp_redirect( get_site_url() . '/site/' . $currentQuery->slug . '?page=domain&form_page=3' );
				exit;
			}

			if ( $has_domain && $has_le ) {
				wp_redirect( get_site_url() . '/site/' . $currentQuery->slug . '?page=domain&form_page=4' );
				exit;
			}

			if ( $has_domain && ! $has_analytics ) {
				wp_redirect( get_site_url() . '/site/' . $currentQuery->slug . '?page=domain&form_page=2' );
				exit;
			}

			if ( $has_analytics ) {
				wp_redirect( get_site_url() . '/site/' . $currentQuery->slug . '?page=domain&form_page=4' );
				exit;
			}
		}
	}

	public function gform_skip_page( $form ) {
		if ( ! rgpost( "is_submit_{$form['id']}" ) && rgget( 'form_page' ) && is_user_logged_in() ) {
			GFFormDisplay::$submission[ $form['id'] ]['page_number'] = rgget( 'form_page' );
		}

		return $form;
	}

	public function change_message( $message, $form ) {
		?>
        <div class="blockquote-box blockquote-danger clearfix">
            <div class="square pull-left">
                <i class="fal fa-warning"></i>
            </div>
            <h4>
				<?php _e( 'Woops, something is not right! Please see the highlighted fields below!', DOLLIE_SLUG ); ?>
            </h4>
            <p>
            </p>
        </div>
		<?php
	}

	public function populate_instruction_fields( $input, $field, $value, $lead_id, $form_id ) {
		$currentQuery = dollie()->get_current_object();
		$form_ids     = dollie()->get_dollie_gravity_form_ids( 'dollie-domain' );

		if ( $form_id !== $form_ids[0] ) {
			return $input;
		}

		$tpl          = '';
		$has_domain   = get_post_meta( $currentQuery->id, 'wpd_domains', true );
		$ip           = get_post_meta( $currentQuery->id, 'wpd_container_ip', true );
		$platform_url = get_post_meta( $currentQuery->id, 'wpd_url', true );

		if ( $field->id === 40 ) {
			$tpl = 'wizard/link-domain';
		}

		if ( $field->id === 43 ) {
			$tpl = 'wizard/update-url';
		}

		if ( $field->id === 57 ) {
			$tpl = 'wizard/completed';
		}

		return Tpl::load( DOLLIE_MODULE_TPL_PATH . $tpl, [
			'has_domain'   => $has_domain,
			'ip'           => $ip,
			'platform_url' => $platform_url
		] );
	}

	public function complete_migration_wizard( $form, $source_page_number, $current_page_number ) {
		if ( $current_page_number > 6 ) {
			$currentQuery = dollie()->get_current_object();

			// Update user meta used to show/hide specific Dashboard areas/tabs
			update_post_meta( $currentQuery->id, 'wpd_cloudflare_active', 'yes' );
			update_post_meta( $currentQuery->id, 'wpd_domain_migration_complete', 'yes' );

			// Log our success
			Log::add( $currentQuery->slug . ' domain setup completed. Using live real domain from this point onwards.' );

			// Make a backup.
			Backups::instance()->trigger_backup();

			// Update our container details so that the new domain will be used to make container HTTP requests.
			dollie()->flush_container_details();
		}
	}

	public function gfcf_validation( $validation_result ) {
		global $gfcf_fields;

		$form          = $validation_result['form'];
		$confirm_error = false;

		if ( ! isset( $gfcf_fields[ $form['id'] ] ) ) {
			return $validation_result;
		}

		foreach ( $gfcf_fields[ $form['id'] ] as $confirm_fields ) {
			$values = [];

			// loop through form fields and gather all field values for current set of confirm fields
			foreach ( $form['fields'] as $field ) {
				if ( ! in_array( $field['id'], $confirm_fields ) ) {
					continue;
				}

				$values[] = rgpost( "input_{$field['id']}" );
			}

			// filter out unique values, if greater than 1, a value was different
			if ( count( array_unique( $values ) ) <= 1 ) {
				continue;
			}

			$confirm_error = true;

			foreach ( $form['fields'] as $field ) {
				if ( ! in_array( $field['id'], $confirm_fields ) ) {
					continue;
				}

				// fix to remove phone format instruction
				if ( RGFormsModel::get_input_type( $field ) === 'phone' ) {
					$field['phoneFormat'] = '';
				}

				$field['failed_validation']  = true;
				$field['validation_message'] = 'Your domain names do not match.';
			}
		}

		$validation_result['form']     = $form;
		$validation_result['is_valid'] = ! $validation_result['is_valid'] ? false : ! $confirm_error;

		return $validation_result;
	}

	public function register_confirmation_fields( $form_id, $fields ) {
		global $gfcf_fields;
		$form_id = $form_id[0];

		if ( ! $gfcf_fields ) {
			$gfcf_fields = [];
		}

		if ( ! isset( $gfcf_fields[ $form_id ] ) ) {
			$gfcf_fields[ $form_id ] = [];
		}

		$gfcf_fields[ $form_id ][] = $fields;
	}

}
