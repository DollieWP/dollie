<?php

namespace Dollie\Core\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;

final class StringVariants extends Singleton {
	public function get_user_type_string() {
		if ( ! function_exists( 'dollie_setup_get_string' ) ) {
			return __( 'Customer', 'dollie' );
		}

		$package_string = dollie_setup_get_string( 'user_type' );

		if ( $package_string ) {
			return dollie_setup_get_string( 'user_type' );
		}

		return __( 'Customer', 'dollie' );
	}

	public function get_user_type_plural_string() {
		if ( ! function_exists( 'dollie_setup_get_string' ) ) {
			return __( 'Customers', 'dollie' );
		}

		if ( function_exists( 'dollie_setup_get_string' ) ) {
			$package_string = dollie_setup_get_string( 'user_type_plural' );
		}

		if ( $package_string ) {
			return dollie_setup_get_string( 'user_type_plural' );
		}

		return __( 'Customers', 'dollie' );
	}

	public function get_site_type_string() {
		if ( function_exists( 'dollie_setup_get_string' ) && $package_string = dollie_setup_get_string( 'site_type' ) ) {
			return $package_string;
		}

		return __( 'Site', 'dollie' );
	}

	public function get_blueprint_type_string() {
		if ( function_exists( 'dollie_setup_get_string' ) && $package_string = dollie_setup_get_string( 'blueprint_type' ) ) {
			return $package_string;
		}

		return __( 'Blueprint', 'dollie' );
	}

	public function get_site_type_plural_string() {
		if ( ! function_exists( 'dollie_setup_get_string' ) ) {
			return __( 'Sites', 'dollie' );
		}

		$package_string = dollie_setup_get_string( 'site_type_plural' );

		if ( $package_string ) {
			return dollie_setup_get_string( 'site_type_plural' );
		}

		return __( 'Sites', 'dollie' );
	}

	public function get_blueprint_type_plural_string() {
		if ( ! function_exists( 'dollie_setup_get_string' ) ) {
			return __( 'Blueprints', 'dollie' );
		}

		$package_string = dollie_setup_get_string( 'blueprint_type_plural' );

		if ( $package_string ) {
			return dollie_setup_get_string( 'blueprint_type_plural' );
		}

		return __( 'Blueprints', 'dollie' );
	}
}
