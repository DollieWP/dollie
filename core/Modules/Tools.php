<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;

/**
 * Class Tools
 * @package Dollie\Core\Modules
 */
class Tools extends Singleton {

	/**
	 * Tools constructor.
	 */
	public function __construct() {
		parent::__construct();

		$performance_form = dollie()->get_dollie_gravity_form_ids( 'dollie-performance' );
		if ( ! empty( $performance_form ) ) {
			add_action( 'gform_after_submission_' . $performance_form[0], [ $this, 'run_worker_tools_job' ], 10, 2 );

		}

		add_action( 'gform_after_save_form', [ $this, 'remove_forms_ids_transient' ] );

		add_action( 'admin_init', [ $this, 'last_admin_activity' ], 10 );

	}

	public function run_worker_tools_job( $entry, $form ) {
		$php = rgar( $entry, '2' );

		if ( $php === 'php-5' ) {
			ContainerManagement::instance()->start_worker_job( 'a98ea708-f42e-418c-bc35-1066fb533e8e' );
		}

		if ( $php === 'php-7' ) {
			ContainerManagement::instance()->start_worker_job( 'cf9d0568-e150-4f59-b014-b7c7d9ca5e46' );
		}

		?>
        <script>
            jQuery(document).ready(function () {
                setTimeout(function () {
                    // Check if the S5 Dashboard shows the icon for the successfully launched container.
                    // Redirect to our own dashboard to show setup wizard.
                    window.location = "<?php echo get_site_url(); ?>/developers/?tab#tools";
                }, 3000);
            });
        </script>
		<?php
	}

	/**
	 * Remove cached data for gravity form ids mapping
	 */
	public function remove_forms_ids_transient() {
		delete_transient( 'dollie_gform_ids' );
	}

	/**
	 * Save last admin activity when on staging
	 */
	public function last_admin_activity() {
	    if( is_user_logged_in() && ! dollie()->is_live() ) {
		    update_option( 'wpd_staging_last_seen', time() );
        }
	}
}
