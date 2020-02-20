<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use Dollie\Core\Helpers;
use Dollie\Core\Log;

/**
 * Class WelcomeWizard
 * @package Dollie\Core\Modules
 */
class WelcomeWizard extends Singleton {

	/**
	 * @var mixed
	 */
	private $helpers;

	/**
	 * WelcomeWizard constructor.
	 */
	public function __construct() {
		parent::__construct();

		$this->helpers = Helpers::instance();

		$setup_forms = $this->helpers->get_dollie_gravity_form_ids( 'dollie-wizard' );
		foreach ( $setup_forms as &$form_id ) {
			add_action( 'gform_post_paging_' . $form_id, [ $this, 'update_site_details' ], 10, 3 );
			add_action( 'gform_after_submission_' . $form_id, [ $this, 'complete_setup_wizard' ], 10, 2 );
			add_filter( 'gform_field_input_' . $form_id, [ $this, 'populate_migration_instructions' ], 10, 5 );
		}

	}

	public function update_site_details( $form, $source_page_number, $current_page_number ) {
		global $wp_query;
		$post_id            = $wp_query->get_queried_object_id();
		$post_slug          = get_queried_object()->post_name;
		$install            = $post_slug;
		$is_partner_lead    = get_post_meta( $post_id, 'wpd_is_partner_lead', true );
		$partner            = get_post_meta( $post_id, 'wpd_partner_ref', true );
		$user_details       = get_userdatabylogin( $partner );
		$partner_id         = $user_details->ID;
		$partner_install    = get_post_meta( $partner_id, 'wpd_url', true );
		$partner_blueprint  = get_post_meta( $partner_id, 'wpd_partner_blueprint_created', true );
		$blueprint_deployed = get_post_meta( $post_id, 'wpd_blueprint_deployment_complete', true );
		$demo               = get_post_meta( $post_id, 'wpd_container_is_demo', true );

		if ( $current_page_number > 1 && ! $current_page_number < 3 ) {
			$value = rgpost( 'input_1' );
			if ( $value === 'setup' ) {

				if ( $demo !== 'yes' && $is_partner_lead === 'yes' && $partner_blueprint === 'yes' && $blueprint_deployed !== 'yes' ) {
					//TODO ADD PARTNER FUNCTIONALITY
					$rundeck_job = DOLLIE_RUNDECK_URL . '/api/1/job/85783830-a89d-439f-b4db-4a5e0e0fd6a9/run/';
					//Output buffer our Node details
					ob_start(); ?>
                    {
                    "filter": "name: https://<?php echo $install . DOLLIE_DOMAIN . '-' . DOLLIE_RUNDECK_KEY; ?>",
                    "argString": "-url '<?php echo $partner_install . DOLLIE_DOMAIN; ?>' -domain '<?php echo $install . DOLLIE_DOMAIN; ?>'"
                    }
					<?php
					//Create our new node details
					$post_body = ob_get_clean();

					//Set up the request
					$update = wp_remote_post(
						$rundeck_job, array(
							'headers' => array(
								'X-Rundeck-Auth-Token' => WF_RUNDECK_TOKEN,
								'Content-Type'         => 'application/json',
							),
							'body'    => $post_body,
						)
					);
					update_post_meta( $post_id, 'wpd_partner_blueprint_deployed', 'yes' );
					sleep( 5 );
				} else if ( $demo !== 'yes' ) {
					$rundeck_job = DOLLIE_RUNDECK_URL . '/api/1/job/f0b8f078-fb6d-47e7-ac8b-2962fe8b0241/run/';
					$email       = rgpost( 'input_5' );
					$name        = rgpost( 'input_4' );
					$username    = rgpost( 'input_26' );
					$password    = rgpost( 'input_27' );
					$description = rgpost( 'input_11' );
					//Output buffer our Node details
					ob_start();
					?>
                    {
                    "filter": "name: https://<?php echo $install . DOLLIE_DOMAIN . '-' . DOLLIE_RUNDECK_KEY; ?>",
                    "argString": "-email <?php echo $email; ?> -name '<?php echo $name; ?>' -description '<?php echo $description; ?>' -password '<?php echo $password; ?>' -username '<?php echo $username; ?>'"
                    }
					<?php
					//Create our new node details
					$post_body = ob_get_clean();

					//Set up the request
					wp_remote_post(
						$rundeck_job, array(
							'headers' => array(
								'X-Rundeck-Auth-Token' => DOLLIE_RUNDECK_TOKEN,
								'Content-Type'         => 'application/json',
							),
							'body'    => $post_body,
						)
					);
				}

				$this->helpers->flush_container_details();
			}
		}
	}

	public function complete_setup_wizard( $entry, $form ) {
		global $wp_query;
		$post_id   = $wp_query->get_queried_object_id();
		$post_slug = get_queried_object()->post_name;
		update_post_meta( $post_id, 'wpd_setup_complete', 'yes' );
		Log::add( $post_slug . ' has completed the initial site setup', '', 'setup' );
		Backups::instance()->trigger_backup();
		?>
		<?php
	}

	public function populate_migration_instructions( $input, $field, $value, $lead_id, $form_id ) {
		$post_slug = get_queried_object()->post_name;
		$user      = wp_get_current_user();
		$request   = get_transient( 'dollie_s5_container_details_' . $post_slug );
		$hostname  = preg_replace( '#^https?://#', '', $request->uri );

		if ( $form_id === $this->helpers->get_dollie_gravity_form_ids( 'dollie-wizard' )[0] && $field->id === 7 ) {
			$input = '   <div class="blockquote-box blockquote-success clearfix">
   		 <div class="square pull-left">
   				 <i class="fal fa-truck"></i>
   		 </div>
   		 <h4>
   			<strong>Ready for the easiest migration ever?</strong>
   		</h4>
        <p>We are going make an exact copy of your current WordPress install to <strong>' . $post_slug . DOLLIE_DOMAIN . '</strong> </p>
    </div>
  <p>
  <strong>Do not worry; your live site will not be touched or modified in any way!</strong>
  </p>
  <h4>Step 1 - Install the Easy Migration Plugin</h4>
  <p>
     <ol>
      <li><a href="' . DOLLIE_PLUGIN_URL . '/setup/easy-migration.zip">Click here to download our Easy Migration Plugin</a></li>
       <li>Login to the WordPress Admin of your <strong>current</strong> WordPress site (yoursite.com)</li>
       <li>Go to <strong>Plugins > Add New > Upload Plugin</strong> and select the zip file of the Easy Migration plugin you just downloaded. </li>
       <li>Press the <strong>Install Now</strong> button.</li>
       <li>Activate the plugin!</li>
     </ol>
   </p>
   <h4>Step 2 - Fill in the Migration Details</h4>

   On the Migration Settings you will be asked to fill in your Site Details.

   Copy and paste the values displayed below into the migration fields.
   <div class="clearfix"></div>
   <div class="col-sm-10 p-30 alert-info alert">
   <div class="col-sm-6 margin-bottom-half">
         Email<br> <strong>' . $user->user_email . '</strong>
    </div>
    <div class="col-sm-6 margin-bottom-half">
          Platform Site URL<br><strong>' . $hostname . '</strong>
     </div>
     <div class="clearfix"></div>
      <div class="col-sm-4 margin-bottom-half">
            SFTP Username: <br><strong>' . $request->containerSshUsername . '</strong>
       </div>
       <div class="col-sm-4 margin-bottom-half">
              Password:<br> <strong>' . $request->containerSshPassword . '</strong><br>
        </div>
        <div class="col-sm-4 margin-bottom-half">
               Port: <br><strong>' . $request->containerSshPort . '</strong>
         </div>
            <div class="clearfix"></div>
   </div><div class="clearfix"></div>
    <h4>Step 3 - Sit back and enjoy the show!</h4>

   Press <strong>Start Site Migration</strong> and sit back and enjoy the show. Depending on the size of your site and the speed of your current host this process could take up to a couple of hours. Do not worry, this is completely normal! We will send you an email when the migration has completed so you can easily continue to this setup wizard.
';
		}

		return $input;
	}

}
