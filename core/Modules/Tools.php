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
		add_action( 'gform_after_submission_1', [ $this, 'run_worker_tools_job' ], 10, 2 );
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

}
