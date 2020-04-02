<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Modules\Forms\CreateBackup;
use Dollie\Core\Modules\Forms\CreateBlueprint;
use Dollie\Core\Modules\Forms\DomainWizard;
use Dollie\Core\Modules\Forms\LaunchSite;
use Dollie\Core\Modules\Forms\AfterLaunchWizard;
use Dollie\Core\Modules\Forms\ListBackups;
use Dollie\Core\Modules\Forms\Performance;
use Dollie\Core\Modules\Forms\PluginUpdates;
use Dollie\Core\Modules\Forms\DeleteSite;
use Dollie\Core\Modules\Forms\QuickLaunch;
use Dollie\Core\Singleton;
use Dollie\Core\Utils\Tpl;

/**
 * Class Forms
 * @package Dollie\Core\Modules
 */
class Forms extends Singleton {

	/**
	 * Forms constructor.
	 */
	public function __construct() {
		parent::__construct();

		LaunchSite::instance();
		AfterLaunchWizard::instance();
		QuickLaunch::instance();
		CreateBackup::instance();
		ListBackups::instance();
		CreateBlueprint::instance();
		DomainWizard::instance();
		PluginUpdates::instance();
		DeleteSite::instance();
		Performance::instance();

		add_action( 'template_redirect', [ $this, 'redirect_to_new_container' ] );

		add_action( 'init', [ $this, 'init' ] );
		add_action( 'acf/init', [ $this, 'acf_init' ] );

		add_filter( 'acf/load_field', [ $this, 'localize_strings' ] );
		add_action( 'af/form/hidden_fields', [ $this, 'hidden_fields' ], 10, 2 );

	}

	public function init() {
		add_shortcode( 'dollie_form', [ $this, 'form_shortcode' ] );
		add_shortcode( 'dollie_blockquote', [ $this, 'blockquote_shortcode' ] );
		add_filter( 'acf/prepare_field/name=form_shortcode_message', array( $this, 'display_form_shortcode' ), 12, 1 );

	}

	public function acf_init() {

		add_filter( 'af/merge_tags/resolve', array( $this, 'resolve_fields_tag' ), 9, 3 );
		add_filter( 'af/merge_tags/resolve', [ $this, 'add_merge_tags' ], 10, 2 );
		add_filter( 'af/merge_tags/custom', [ $this, 'register_merge_tags' ], 10, 2 );

		// Placeholders/Change values
		add_filter( 'acf/prepare_field/type=message', [ $this, 'add_acf_placeholders' ], 10 );

	}

	public function add_merge_tags( $output, $tag ) {
		if ( 'dollie_container_login_url' === $tag ) {
			return esc_url( call_user_func( [ $this, 'get_container_login_url' ] ) );
		}

		if ( ( 'dollie_container_url' === $tag ) && isset( $_POST['dollie_post_id'] ) ) {
			$container = self::get_form_container();
			if ( $container ) {
				return dollie()->get_container_url( (int) $container->id );
			}
		}

		return $output;
	}

	public function get_container_login_url() {
		$container = self::get_form_container();
		if ( $container ) {
			return dollie()->get_customer_login_url( (int) $container->id );
		}

		return '';
	}

	function register_merge_tags( $tags, $form ) {
		$tags[] = array(
			'value' => 'dollie_container_login_url',
			'label' => esc_html__( 'Dollie Container Login URL', 'dollie' ),
		);
		$tags[] = array(
			'value' => 'dollie_container_url',
			'label' => esc_html__( 'Dollie Container URL', 'dollie' ),
		);

		return $tags;
	}


	public function form_shortcode( $atts = [] ) {

		return do_shortcode( '[advanced_form form="' . esc_attr( $atts['form'] ) . '"]' );

	}


	/**
	 * Display the form shortcode in the form settings.
	 *
	 * @since 1.6.4
	 *
	 */
	function display_form_shortcode( $field ) {
		global $post;

		if ( $post && $key = get_post_meta( $post->ID, 'form_key', true ) ) {
			if (strpos( $key, 'form_dollie'  ) !== false) {
				$message = sprintf( '<code>[dollie_form form="%s"]</code>', $key );
				$field['message'] = $message;
			}
		}

		return $field;
	}

	public function blockquote_shortcode( $atts = [], $content = '' ) {

		$atts = shortcode_atts( array(
			'icon'  => 'fa fa-info-circle',
			'type'  => 'success',
			'title' => ''
		), $atts, 'dollie_blockquote' );

		$data = '<div class="blockquote-box blockquote-' . esc_attr( $atts['type'] ) . ' clearfix">
   		 <div class="square pull-left">
   				 <i class="' . esc_attr( $atts['icon'] ) . '"></i>
   		 </div>
   		 <h4>' . esc_html( $atts['title'] ) . '</h4>
   		 <p>' . wp_kses_post( $content ) . '</p>
    </div>';

		return $data;

	}

	/**
	 * Add our custom logic to parse placeholders
	 *
	 * @param $output
	 * @param $tag
	 * @param $fields
	 *
	 * @return false|mixed|string
	 */
	public function resolve_fields_tag( $output, $tag, $fields ) {

		if ( ! preg_match_all( '/field:(.*)/', $tag, $matches ) ) {
			return $output;
		}

		$field_name = $matches[1][0];

		$field = af_get_field_object( $field_name, $fields );

		if ( is_array( $field['value'] ) && $field['return_format'] === 'array'
		     && ( $field['type'] === 'radio' || $field['type'] === 'select' ) ) {

			$value = $field['value']['label'];
			if ( strtotime( $value ) ) {
				$value = date( 'd F y \a\t g:i a', strtotime( $value ) );
			}

			return $value;
		}

		return _af_render_field_include( $field );

	}

	public function redirect_to_new_container() {
		if ( isset( $_GET['site'] ) && $_GET['site'] === 'new' ) {
			$url = dollie()->get_latest_container_url();

			if ( $url ) {
				wp_redirect( $url );
				exit();
			}
		}
	}

	/**
	 * Add migration instruction to form
	 *
	 * @param $value
	 * @param $post_id
	 * @param $field
	 *
	 * @return string|string[]
	 */
	public function add_acf_placeholders( $field ) {

		if ( isset( $field['message'] ) && $field['message'] ) {

			$currentQuery = dollie()->get_current_object();

			$user    = wp_get_current_user();
			$request = dollie()->get_customer_container_details();

			if ( ! $request || ! is_object( $request ) ) {
				return $field;
			}

			$hostname = preg_replace( '#^https?://#', '', $request->uri );

			$tpl_migration_instructions = Tpl::load( DOLLIE_MODULE_TPL_PATH . 'migration-instructions', [
				'post_slug' => $currentQuery->slug,
				'request'   => $request,
				'user'      => $user,
				'hostname'  => $hostname
			] );

			$ip     = get_post_meta( $currentQuery->id, 'wpd_container_ip', true ) ?: '';
			$domain = get_post_meta( $currentQuery->id, 'wpd_domains', true ) ?: '';
			$url    = get_post_meta( $currentQuery->id, 'wpd_container_uri', true ) ?: '';

			$tpl_is_migration_complete = Tpl::load( DOLLIE_MODULE_TPL_PATH . 'wizard/completed', [
				'has_domain'   => $domain,
				'ip'           => $ip,
				'platform_url' => $url,
			] );

			$tpl_link_domain = Tpl::load( DOLLIE_MODULE_TPL_PATH . 'wizard/link-domain', [
				'has_domain'   => $domain,
				'ip'           => $ip,
				'platform_url' => $url,
			] );

			$field['message'] = str_replace( '{dollie_migration_instructions}', $tpl_migration_instructions, $field['message'] );
			$field['message'] = str_replace( '{dollie_is_migration_complete}', $tpl_is_migration_complete, $field['message'] );
			$field['message'] = str_replace( '{dollie_tpl_link_domain}', $tpl_link_domain, $field['message'] );

			if ( is_user_logged_in() ) {
				$user             = wp_get_current_user();
				$field['message'] = str_replace( '{dollie_user_display_name}', $user->display_name, $field['message'] );

			}

			// Allow shortcodes
			$field['message'] = do_shortcode( $field['message'] );
		}

		return $field;
	}

	function hidden_fields( $form, $args ) {
		echo '<input type="hidden" name="dollie_post_id" value="' . get_the_ID() . '">';
	}

	public function localize_strings( $field ) {

		if ( $field['label'] === 'Choose Your URL' ) {
			$field['label'] = esc_html__( 'Choose Your URL', 'dollie' );
		}

		if ( $field['instructions'] === 'Please choose a temporary URL for your site. This will be the place where you can work on your site until you connect your own domain.' ) {
			$field['instructions'] = esc_html__( 'Please choose a temporary URL for your site. This will be the place where you can work on your site until you connect your own domain.', 'dollie' );
		}

		if ( $field['label'] === 'Admin Email' ) {
			$field['label'] = esc_html__( 'Admin Email', 'dollie' );
		}

		if ( $field['instructions'] === 'This is the email address you use to login to your WordPress admin.' ) {
			$field['instructions'] = esc_html__( 'This is the email address you use to login to your WordPress admin.', 'dollie' );
		}

		if ( $field['label'] === 'Select a Blueprint (optional)' ) {
			$field['label'] = esc_html__( 'Select a Blueprint (optional)', 'dollie' );
		}
		if ( $field['instructions'] === 'Carefully crafted site designs made by our team which you can use as a starting point for your new site.' ) {
			$field['instructions'] = esc_html__( 'Carefully crafted site designs made by our team which you can use as a starting point for your new site.', 'dollie' );
		}

		return $field;
	}

	/**
	 * Get value for a form field when processing data
	 *
	 * @param $name
	 *
	 * @return bool|mixed
	 */
	public static function get_field( $name ) {
		$data = af_get_field( $name );
		if ( is_array( $data ) && isset( $data['value'] ) ) {
			$data = $data['value'];
		}

		return $data;
	}

	public static function get_form_container() {

		if ( isset( AF()->submission['extra'], AF()->submission['extra']['dollie_container_id'] ) ) {
			$container_id = AF()->submission['extra']['dollie_container_id'];
		} else if ( isset( $_POST['dollie_post_id'] ) ) {
			$container_id = (int) $_POST['dollie_post_id'];
		}

		if ( ! isset( $container_id ) ) {
			return false;
		}

		$container = dollie()->get_current_object( $container_id );

		if ( $container_id === 0 ) {
			return false;
		}

		return $container;
	}
}
