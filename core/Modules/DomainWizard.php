<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Utils\Helpers;
use Dollie\Core\Utils\Api;
use Dollie\Core\Log;
use GFFormsModel;

/**
 * Class DomainWizard
 * @package Dollie\Core\Modules
 */
class DomainWizard extends Singleton {

	/**
	 * @var mixed
	 */
	private $helpers;

	/**
	 * DomainWizard constructor.
	 */
	public function __construct() {
		parent::__construct();

		$this->helpers = Helpers::instance();

		$domain_forms = $this->helpers->get_dollie_gravity_form_ids( 'dollie-domain' );
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
		], $this->helpers->get_dollie_gravity_form_ids( 'dollie-domain' )[0], 2 );

		add_filter( 'gform_field_input', [ $this, 'populate_instruction_fields' ], 10, 5 );

		$this->register_confirmation_fields( $this->helpers->get_dollie_gravity_form_ids( 'dollie-domain' ), [
			55,
			60
		] );
		add_filter( 'gform_validation', [ $this, 'gfcf_validation' ] );
	}

	public function domain_wizard_add_domain( $validation_result ) {
		global $wp_query;
		$post_id   = $wp_query->get_queried_object_id();
		$post_slug = get_queried_object()->post_name;

		$request = get_transient( 'dollie_s5_container_details_' . $post_slug . '' );

		$form         = $validation_result['form'];
		$entry        = GFFormsModel::get_current_lead();
		$current_page = rgpost( 'gform_source_page_number_' . $form['id'] ) ?: 1;

		//Our form field ID + User meta fields
		$domain = rgar( $entry, '55' );

		//Are we on the first page?
		if ( $current_page === 1 ) {

			$answer = Api::postRequestDollie( $request->id . '/routes', [ 'domain' => $domain ], 45 );

			$response = json_decode( $answer, true );

			// Show an error of S5 API can't add the Route.
			if ( ! array_key_exists( 'path', $response ) ) {
				//finding Field with ID of 1 and marking it as failed validation
				foreach ( $form['fields'] as $field ) {

					if ( $field->id == '55' ) {
						$field_page = $field->pageNumber;
						// validation failed
						$validation_result['is_valid'] = false;
						$field->failed_validation      = true;
						$field->validation_message     = 'Sorry, We could not link this domain to your site. This could be because the domain is already registered for another site in our network. It could also be an issue on our end! Please try again or <a href="https://dollie.co/support-redirect">Contact Support</a>';
					}

				}
			}
			if ( array_key_exists( 'path', $response ) ) {

				//Save the Domain Data and make another S5 Request for the WWW domain.
				update_post_meta( $post_id, 'wpd_domain_id', $response['id'] );
				update_post_meta( $post_id, 'wpd_domains', $domain );

				$update_answer = Api::postRequestDollie( $request->id . '/routes', [ 'domain' => 'www.' . $domain ], 45 );

				$update_response = json_decode( $update_answer, true );

				//Also save the WWW Domain data.
				update_post_meta( $post_id, 'wpd_www_domain_id', $update_response['id'] );
				Log::add( $post_slug . ' linked up domain ' . $domain . '' );
			}
			//Assign modified $form object back to the validation result
			$validation_result['form'] = $form;

			return $validation_result;

		}

		//Just return the form
		$validation_result['form'] = $form;

		return $validation_result;
	}

	public function domain_wizard_add_cloudflare( $validation_result ) {
		//User Meta
		global $wp_query;
		$post_id   = $wp_query->get_queried_object_id();
		$post_slug = get_queried_object()->post_name;


		$form         = $validation_result['form'];
		$entry        = GFFormsModel::get_current_lead();
		$current_page = rgpost( 'gform_source_page_number_' . $form['id'] ) ? rgpost( 'gform_source_page_number_' . $form['id'] ) : 1;

		//Are the on the CloudFlare Setup Page?
		if ( $current_page === 2 ) {
			//Our form field ID + User meta fields
			$ssl_type = rgar( $entry, '11' );
			$email    = rgar( $entry, '50' );
			$api_key  = rgar( $entry, '27' );

			if ( $ssl_type === 'cloudflare' ) {

				//Set up the request to CloudFlare to verify
				$update = wp_remote_post( 'https://api.cloudflare.com/client/v4/user', [
					'method'  => 'GET',
					'timeout' => 45,
					'headers' => [
						'X-Auth-Email' => $email,
						'X-Auth-Key'   => $api_key,
						'Content-Type' => 'application/json',
					],
				] );
				//Parse the JSON request
				$answer   = wp_remote_retrieve_body( $update );
				$response = json_decode( $answer, true );

				//Throw an error if CloudFlare Details are incorrect.
				if ( ! isset( $response['result']['id'] ) ) {
					//finding Field with ID of 1 and marking it as failed validation
					foreach ( $form['fields'] as $field ) {

						if ( $field->id === '27' ) {
							$field_page = $field->pageNumber;

							// validation failed
							$validation_result['is_valid'] = false;
							$field->failed_validation      = true;
							$field->validation_message     = 'Your CloudFlare Email or API key is incorrect. Please try again or <a href="https://dollie.co/support-redirect">Contact Support</a>';
						}

					}
				}
				if ( isset( $response['result']['id'] ) ) {
					//Success now send the Rundeck request
					//This job will install + activate the CloudFlare plugin and populate the email + API key fields for the CloudFlare Options.
					$post_body = [
						'filter'    => 'name: https://' . $post_slug . DOLLIE_DOMAIN . '-' . DOLLIE_RUNDECK_KEY,
						'argString' => '-email ' . $email . ' -key ' . $api_key
					];

					Api::postRequestRundeck( '1/job/3725d807-435e-400c-8679-2a438f765002/run/', $post_body );

					//All done, update user meta!
					update_post_meta( $post_id, 'wpd_cloudflare_email', $email );
					update_post_meta( $post_id, 'wpd_cloudflare_active', 'yes' );
					update_post_meta( $post_id, 'wpd_cloudflare_id', $response['result']['id'] );
					update_post_meta( $post_id, 'wpd_cloudflare_api', $api_key );
					Log::add( $post_slug . ' linked up CloudFlare account' );
				}

			} else {
				update_post_meta( $post_id, 'wpd_letsencrypt_enabled', 'yes' );
			}

			//Assign modified $form object back to the validation result
			$validation_result['form'] = $form;

			return $validation_result;

		} else {
			$validation_result['form'] = $form;

			return $validation_result;

		}
	}

	public function domain_wizard_add_cloudflare_zone( $validation_result ) {
		//User Meta
		global $wp_query;
		$post_id   = $wp_query->get_queried_object_id();
		$post_slug = get_queried_object()->post_name;

		//Setup the Form
		$entry = GFFormsModel::get_current_lead();

		//Form Variables
		$form         = $validation_result['form'];
		$current_page = rgpost( 'gform_source_page_number_' . $form['id'] ) ? rgpost( 'gform_source_page_number_' . $form['id'] ) : 1;

		//Are the on the rigt page?
		if ( $current_page == 3 ) {
			//Our form field ID + User meta fields
			$email   = get_post_meta( $post_id, 'wpd_cloudflare_email', true );
			$api_key = get_post_meta( $post_id, 'wpd_cloudflare_api', true );
			$zone    = rgar( $entry, '66' );

			//Set up the request to CloudFlare to verify
			$update = wp_remote_post( 'https://api.cloudflare.com/client/v4/zones/' . $zone, [
				'method'  => 'GET',
				'timeout' => 45,
				'headers' => [
					'X-Auth-Email' => $email,
					'X-Auth-Key'   => $api_key,
					'Content-Type' => 'application/json',
				],
			] );
			//Parse the JSON request
			$answer   = wp_remote_retrieve_body( $update );
			$response = json_decode( $answer, true );

			//Throw an error if the Zone ID is not found.
			if ( ! isset( $response['result']['id'] ) ) {
				//finding Field with ID of 1 and marking it as failed validation
				foreach ( $form['fields'] as $field ) {

					if ( $field->id == '66' ) {
						$field_page = $field->pageNumber;

						// validation failed
						$validation_result['is_valid'] = false;
						$field->failed_validation      = true;
						$field->validation_message     = 'Your CloudFlare Zone ID is incorrect. Please make sure you copy and pasted the right ID without extra spaces. Need help? <a href="https://dollie.co/support-redirect">Contact Support</a>';
					}

				}
			} else {
				//Save our CloudFlare Zone ID to user meta.
				update_post_meta( $post_id, 'wpd_cloudflare_zone_id', $zone );
				Log::add( 'Cloudflare Zone ID ' . $zone . ' is used for analytics for ' . $post_slug );
			}
			//Assign modified $form object back to the validation result
			$validation_result['form'] = $form;

			return $validation_result;

		} else {
			//Just return the form
			$validation_result['form'] = $form;

			return $validation_result;

		}
	}

	public function search_and_replace_domain( $validation_result ) {
		//User Meta
		global $wp_query;
		$post_id   = $wp_query->get_queried_object_id();
		$post_slug = get_queried_object()->post_name;

		$user_auth = DOLLIE_S5_USER;
		$user_pass = DOLLIE_S5_PASSWORD;

		//Domain
		$container = get_post_meta( $post_id, 'wpd_container_id', true );
		$le_domain = get_post_meta( $post_id, 'wpd_domain_id', true );

		//Setup the Form

		$entry = GFFormsModel::get_current_lead();
		//Form Variables
		if ( isset( $validation_result['form'] ) ) {
			$form         = $validation_result['form'];
			$current_page = rgpost( 'gform_source_page_number_' . $form['id'] ) ? rgpost( 'gform_source_page_number_' . $form['id'] ) : 1;
		}

		//Are the on the Domain Replace page?
		if ( isset( $current_page ) && $current_page == 5 ) {
			$www = rgpost( 'input_81' );

			if ( $www === 'yes' ) {
				$domain = 'www.' . get_post_meta( $post_id, 'wpd_domains', true );
			} else {
				$domain = get_post_meta( $post_id, 'wpd_domains', true );
			}

			$post_body = [
				'filter'    => 'name: https://' . $post_slug . DOLLIE_DOMAIN . '-' . DOLLIE_RUNDECK_KEY,
				'argString' => '-install ' . $post_slug . DOLLIE_DOMAIN . ' -domain ' . $domain
			];

			$update = Api::postRequestRundeck( '1/job/ba12c626-a9aa-4abc-b239-278238f1b2a9/run/', $post_body );

			$answer = wp_remote_retrieve_body( $update );

			Log::add( 'Search and replace ' . $post_slug . ' to update URL to ' . $domain . ' has started', $answer );


			$le = get_post_meta( $post_id, 'wpd_letsencrypt_enabled', true );
			if ( $le === 'yes' ) {
				$le_answer = Api::postRequestDollie( $container . '/routes/' . $le_domain . '/autoCert', [], 90 );

				// Show an error of S5 API can't add the Route.
				if ( is_wp_error( $le_answer ) ) {
					//finding Field with ID of 1 and marking it as failed validation
					foreach ( $form['fields'] as $field ) {

						if ( $field->id == '72' ) {
							$field_page = $field->pageNumber;
							// validation failed
							$validation_result['is_valid'] = false;
							$field->failed_validation      = true;
							$field->validation_message     = 'Sorry, We could not generate a SSL certificate for this domain. Please contact support so we can look into why this has happened.';
						}

					}
				} else {
					update_post_meta( $post_id, 'wpd_letsencrypt_setup_complete', 'yes' );
				}

			}

			//We will add an artificial delay because if we're dealing with a big database it could take a bit of time to run the search and replace via the Rundeck/WP-CLI command.
			sleep( 20 );

			//Assign modified $form object back to the validation result
			$validation_result['form'] = $form;
		}

		return $validation_result;
	}

	public function continue_domain_setup() {
		if ( is_singular( 'container' ) && $_GET['page'] == 'domain' && ! isset( $_GET['form_page'] ) ) {
			global $wp_query;
			$post_id   = $wp_query->get_queried_object_id();
			$post_slug = get_queried_object()->post_name;

			$has_domain     = get_post_meta( $post_id, 'wpd_domains', true );
			$has_cloudflare = get_post_meta( $post_id, 'wpd_cloudflare_email', true );
			$has_analytics  = get_post_meta( $post_id, 'wpd_cloudflare_zone_id', true );
			$has_le         = get_post_meta( $post_id, 'wpd_letsencrypt_enabled', true );

			if ( $has_cloudflare && ! $has_analytics ) {
				wp_redirect( get_site_url() . '/site/' . $post_slug . '?page=domain&form_page=3' );
				exit;
			}

			if ( $has_domain && $has_le ) {
				wp_redirect( get_site_url() . '/site/' . $post_slug . '?page=domain&form_page=4' );
				exit;
			}

			if ( $has_domain && ! $has_analytics ) {
				wp_redirect( get_site_url() . '/site/' . $post_slug . '?page=domain&form_page=2' );
				exit;
			}

			if ( $has_analytics ) {
				wp_redirect( get_site_url() . '/site/' . $post_slug . '?page=domain&form_page=4' );
				exit;
			}
		}
	}

	public function gform_skip_page( $form ) {
		if ( ! rgpost( "is_submit_{$form['id']}" ) && rgget( 'form_page' ) && is_user_logged_in() ) {
			GFFormDisplay::$submission[ $form['id'] ]["page_number"] = rgget( 'form_page' );
		}

		return $form;
	}

	public function change_message( $message, $form ) {
		return '
<div class="blockquote-box blockquote-danger clearfix">
     <div class="square pull-left">
         <i class="fal fa-warning"></i>
     </div>
     <h4>
     Woops, something is not right! Please see the highlighted fields below!
    </h4>
     <p>
     </p>
 </div>
';
	}

	public function populate_instruction_fields( $input, $field, $value, $lead_id, $form_id ) {
		global $wp_query;
		$post_id   = $wp_query->get_queried_object_id();
		$post_slug = get_queried_object()->post_name;

		$user         = wp_get_current_user();
		$has_domain   = get_post_meta( $post_id, 'wpd_domains', true );
		$ip           = get_post_meta( $post_id, 'wpd_container_ip', true );
		$platform_url = get_post_meta( $post_id, 'wpd_url', true );

		if ( $form_id === $this->helpers->get_dollie_gravity_form_ids( 'dollie-domain' )[0] && $field->id === 40 ) {
			$input = '<h3>Linking Your Custom Domain to your Site!</h3>
          <p>
          In order to get your custom domain to work we need to make a change to your DNS configuration, so please make sure you have accesss to the control panel of where you registered your domain. Usually making a DNS change is very easy to do and your registrar will have documentation available on how to do this (or simply ask support to do this for you.) Here are the instructions on the changes you need to make.
          </p>
          <ul>
            <li><a href="https://www.cloudflare.com/a/login" target="_blank">Login to Your Domain Management account</a><br>
            </li>
            <li>Go to your Domain name <strong>' . $has_domain . '</strong> and go to the DNS Manager
            <li>Replace the IP address for the <strong>"A"</strong> <strong>' . $has_domain . '</strong> record with the following IP address: <strong>' . $ip . '</strong>
            </li>
          </ul>
                      <img src="https://s3.amazonaws.com/helpscout.net/docs/assets/5742f232c697917290ddb900/images/574ef6b89033604d43daab48/file-D4wzErhF62.jpg" alt="" width="60%" height="auto" />
          <div class="alert alert-info col-sm-9">
          <strong>Important Note: You only need to change the A and WWW records</strong><br>
Your domain might have multiple DNS records set up. For example if you also have your own email address (yourname@yourdomain.com) or subdomains (anothersite.yourdomain.com). For the migration to work you only need to make a change to the A and (if existing) WWW record. Do not change the other records unless specified by our support team or in unique domain setups configured by you or your developer.
          </div>
          ';
		}

		if ( $form_id === $this->helpers->get_dollie_gravity_form_ids( 'dollie-domain' )[0] && $field->id === 43 ) {
			$input = '
          <div class="blockquote-box blockquote-success clearfix">
   <div class="square pull-left">
       <i class="fal fa-wordpress"></i>
   </div>
   <h4>
  Final Step: Updating Your WordPress URL!
  </h4>
            <p>
          We have almost completed setting up your domain! The last step is updating the temporary site URL <strong>' . $platform_url . DOLLIE_DOMAIN . ' to your live domain ' . $has_domain . '</strong>. <br><br>Just click on "Update My Domain" and our migration minions will do all the heavy lifting behind the scenes.
            </p>
            ';
		}

		if ( $form_id === $this->helpers->get_dollie_gravity_form_ids( 'dollie-domain' ) && $field->id === 57 ) {
			$input = '
            <div class="blockquote-box blockquote-warning clearfix">
     <div class="square pull-left">
         <i class="fal fa-warning"></i>
     </div>
     <h5>
    <strong>Have your completed the content migration via our Easy Site Migration plugin?</strong>
    </h5>
       <p class="font-size-smaller">Because you are migrating your "Live Site" to our platform it is important that you have migrated all of your files and your database via our "WeFoster Automated Site Migration" plugin at least once before you continue this wizard. <br></p>
 </div>';
		}

		return $input;
	}

	public function complete_migration_wizard( $form, $source_page_number, $current_page_number ) {
		global $wp_query;
		$post_id   = $wp_query->get_queried_object_id();
		$post_slug = get_queried_object()->post_name;

		if ( $current_page_number > 6 ) {
			//Update user meta used to show/hide specific Dashboard areas/tabs
			update_post_meta( $post_id, 'wpd_cloudflare_active', 'yes' );
			update_post_meta( $post_id, 'wpd_domain_migration_complete', 'yes' );

			//Log our success
			Log::add( $post_slug . ' domain setup completed. Using live real domain from this point onwards.' );

			//Make a backup.
			Backups::instance()->trigger_backup();

			//Update our container details so that the new domain will be used to make container HTTP requests.
			$this->helpers->flush_container_details();
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
				if ( RGFormsModel::get_input_type( $field ) == 'phone' ) {
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
