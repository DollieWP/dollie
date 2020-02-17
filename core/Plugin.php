<?php

namespace Dollie\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Plugin
 * @package Dollie\Core
 */
class Plugin extends Singleton {

	public function __construct() {
		parent::__construct();
	}

}
