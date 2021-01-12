<?php

namespace Dollie\Core\Elementor\Library\Documents;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Elementor\Modules\Library\Documents\Library_Document as Elementor_Library_Document;

/**
 * Class Library_Document
 * @package Dollie\Core\Extras\Library\Documents
 */
abstract class Library_Document extends Elementor_Library_Document {

	/**
	 * Document edit url
	 *
	 * @return mixed|string
	 */
	public function get_edit_url() {
		$url = parent::get_edit_url();

		if ( isset( $_GET['action'] ) && 'elementor_new_post' === sanitize_text_field( $_GET['action'] ) ) { // phpcs:ignore
			$url .= '#library';
		}

		return $url;
	}
}
