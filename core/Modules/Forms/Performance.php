<?php

namespace Dollie\Core\Modules\Forms;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Log;
use Dollie\Core\Modules\ContainerManagement;
use Dollie\Core\Singleton;
use Dollie\Core\Utils\Api;

/**
 * Class Performance
 * @package Dollie\Core\Modules\Forms
 */
class Performance extends Singleton {

	private $form_key = 'form_dollie_performance';

	/**
	 * Performance constructor.
	 */
	public function __construct() {
		parent::__construct();

		add_action( 'acf/init', [ $this, 'acf_init' ] );
	}

	public function acf_init() {

		// Form args
		add_filter( 'af/form/args/key=' . $this->form_key, [ $this, 'change_form_args' ] );

		// Form submission action.
		add_action( 'af/form/before_submission/key=' . $this->form_key, [ $this, 'submission_callback' ], 10, 3 );
	}

	public function submission_callback( $form, $fields, $args ) {

	    // TODO apply PHP and Caching change
		$php = af_get_field( 'php_version' );


	}

	public function change_form_args( $args ) {
		$args['submit_text'] = esc_html__( 'Submit', 'dollie' );
		$args['target'] .= '#performance';

		return $args;
	}


}
