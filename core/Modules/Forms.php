<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Modules\Forms\CreateBackup;
use Dollie\Core\Modules\Forms\CreateBlueprint;
use Dollie\Core\Modules\Forms\DomainConnect;
use Dollie\Core\Modules\Forms\DomainDns;
use Dollie\Core\Modules\Forms\DomainUpdateUrl;
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
		DomainConnect::instance();
		DomainDns::instance();
		DomainUpdateUrl::instance();
		PluginUpdates::instance();
		DeleteSite::instance();
		Performance::instance();

		add_action( 'template_redirect', [ $this, 'redirect_to_new_container' ] );

		add_action( 'init', [ $this, 'init' ] );
		add_action( 'acf/init', [ $this, 'acf_init' ] );

		if ( isset( $_GET['page'], $_GET['tool'] ) && $_GET['page'] === 'acf-tools' && $_GET['tool'] === 'export' ) {
			add_filter( 'acf/prepare_field_for_export', [ $this, 'localize_strings' ] );
		}

		add_action( 'af/form/hidden_fields', [ $this, 'hidden_fields' ], 10, 2 );

		// Custom form fields
		add_filter( 'af/form/valid_form', array( $this, 'add_custom_field_defaults' ), 10, 1 );
		add_filter( 'af/form/from_post', array( $this, 'form_from_post' ), 10, 2 );
		add_filter( 'af/form/to_post', array( $this, 'form_to_post' ), 10, 2 );
		add_filter( 'af/form/after_fields', [ $this, 'add_splash_template' ], 10, 2 );
		add_filter( 'af/form/args', [ $this, 'change_form_args' ], 10, 2 );
	}

	/**
	 * Code that runs at WP init. Hooks and content alterations.
	 */
	public function init() {
		add_shortcode( 'dollie_form', [ $this, 'form_shortcode' ] );
		add_shortcode( 'dollie_blockquote', [ $this, 'blockquote_shortcode' ] );
		add_filter( 'acf/prepare_field/name=form_shortcode_message', array( $this, 'hide_af_form_shortcode' ), 12, 1 );
		add_filter( 'acf/prepare_field/name=form_wpd_shortcode_message', array(
			$this,
			'display_form_shortcode'
		), 12, 1 );
	}

	/**
	 * ACF/AF specific hooks
	 */
	public function acf_init() {

		add_filter( 'af/merge_tags/resolve', array( $this, 'resolve_fields_tag' ), 9, 3 );
		add_filter( 'af/merge_tags/resolve', [ $this, 'add_merge_tags' ], 10, 2 );
		add_filter( 'af/merge_tags/custom', [ $this, 'register_merge_tags' ], 10, 2 );

		// Placeholders/Change values
		add_filter( 'acf/prepare_field/type=message', [ $this, 'add_acf_placeholders' ], 10 );
		add_filter( 'af/field/prefill_value/name=admin_email', [ $this, 'prefill_site_admin_email' ], 10, 4 );
		add_filter( 'af/field/prefill_value/name=site_admin_email', [ $this, 'prefill_site_admin_email' ], 10, 4 );
		add_filter( 'af/field/prefill_value/name=site_name', [ $this, 'prefill_site_name' ], 10, 4 );
		add_filter( 'af/form/button_attributes', [ $this, 'filter_submit_button_attributes' ], 10, 3 );
		add_filter( 'af/form/next_button_atts', [ $this, 'filter_next_button_attributes' ], 10, 2 );
		add_filter( 'af/form/previous_button_atts', [ $this, 'filter_previous_button_attributes' ], 10, 2 );
		add_filter( 'af/field/prefill_value/name=site_description', [ $this, 'prefill_description' ], 10, 4 );
		add_filter( 'af/field/prefill_value/name=admin_username', [ $this, 'prefill_site_admin_user' ], 10, 4 );

		add_filter( 'af/form/settings_fields', array( $this, 'add_form_settings_fields' ), 2, 1 );
	}

	/**
	 * Add form merge tags
	 *
	 * @param $output
	 * @param $tag
	 *
	 * @return mixed|string
	 */
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

	/**
	 * Register form merge tags
	 *
	 * @param $tags
	 * @param $form
	 *
	 * @return array
	 */
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

	/**
	 * Return the site login url used in site form
	 * @return string
	 */
	public function get_container_login_url() {
		$container = self::get_form_container();
		if ( $container ) {
			return dollie()->get_customer_login_url( (int) $container->id );
		}

		return '';
	}

	/**
	 * Register Dollie form shortcode
	 *
	 * @param array $atts
	 *
	 * @return string
	 */
	public function form_shortcode( $atts = [] ) {

		return do_shortcode( '[advanced_form form="' . esc_attr( $atts['form'] ) . '"]' );
	}


	/**
	 * Add default form attributes
	 *
	 * @param $form
	 *
	 * @return mixed
	 */
	public function add_custom_field_defaults( $form ) {

		$form['is_launch_site']   = false;
		$form['splash_template']  = false;
		$form['site_blueprint']   = false;
		$form['redirect_to_site'] = false;

		return $form;
	}

	/**
	 * Add any settings to form object for forms loaded from posts
	 *
	 */
	function form_from_post( $form, $post ) {

		$is_launch_site   = get_field( 'form_is_launch_site', $post->ID );
		$site_blueprint   = get_field( 'site_blueprint', $post->ID );
		$redirect_to_site = get_field( 'form_redirect_to_site', $post->ID );
		$splash_template  = get_field( 'form_splash_template', $post->ID );

		if ( $is_launch_site ) {
			$form['is_launch_site'] = $is_launch_site;

			if ( $splash_template ) {
				$form['splash_template'] = $splash_template;
			}
			if ( $site_blueprint ) {
				$form['site_blueprint'] = $site_blueprint;
			}
		}

		if ( $redirect_to_site ) {
			$form['redirect_to_site'] = $redirect_to_site;
		}

		// Render shortcodes but make sure they are exported as shortcodes
		if ( isset( $_GET['export_json'] ) ) {
			$form['display']['success_message'] = get_field( 'form_success_message', $post->ID, false );
		}

		return $form;
	}

	/**
	 * Add actions on saving a post
	 *
	 * @param $form
	 * @param $post
	 */
	function form_to_post( $form, $post ) {
		update_field( 'field_form_is_launch_site', $form['is_launch_site'], $post->ID );
		update_field( 'field_form_splash_template', $form['splash_template'], $post->ID );
		update_field( 'field_form_site_blueprint', $form['site_blueprint'], $post->ID );
		update_field( 'field_form_redirect_to_site', $form['redirect_to_site'], $post->ID );
	}

	/**
	 * Load a template
	 *
	 * @param $form
	 * @param $args
	 */
	public function add_splash_template( $form, $args ) {

		$splash_template = $this->get_form_arg( 'splash_template', $form, $args );

		if ( $splash_template ) {
			Tpl::load( $splash_template, [], true );
		}
	}

	/**
	 * Change form args based on Form settings
	 *
	 * @param $args
	 * @param $form
	 *
	 * @return mixed
	 */
	public function change_form_args( $args, $form ) {
		$redirect = $this->get_form_arg( 'redirect_to_site', $form, $args );

		if ( $redirect == true ) {
			$args['redirect'] = add_query_arg( 'site', 'new', $args['redirect'] );
		}

		return $args;
	}

	/**
	 * Get form args
	 *
	 * @param $name
	 * @param $form
	 * @param $args
	 *
	 * @return mixed
	 */
	public function get_form_arg( $name, $form, $args ) {
		return isset( $args[ $name ] ) ? $args[ $name ] : $form[ $name ];
	}

	/**
	 * Hide form shortcode
	 *
	 * @param $field
	 *
	 * @return mixed
	 */
	public function hide_af_form_shortcode( $field ) {
		$field['conditional_logic'] = 1;

		return $field;
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
			if ( strpos( $key, 'form_dollie' ) !== false ) {
				$message = sprintf( '<code>[dollie_form form="%s"]</code>', $key );

				$message .= "<p class='description'>" . esc_html__( 'Possible shortcode attributes: ', 'dollie' ) . '<br>';
				$message .= "
				redirect_to_site=true|false
				site_blueprint=ID
				splash_template='path-to-splash-template'
				display_title=true|false
				display_description=true|false
				submit_text='Submit'
				label_placement=top|bottom
				instruction_placement=label|field</p>";

				$field['message'] = $message;
			}
		}

		return $field;
	}

	/**
	 * Add form settings for restrictions
	 *
	 * @since 1.0.3
	 */
	function add_form_settings_fields( $field_group ) {
		$field_group['fields'][] = array(
			'key'               => 'field_form_wpd_shortcode_tab',
			'label'             => '<span class="dashicons dashicons-admin-settings"></span>' . __( 'Settings', 'dollie' ),
			'name'              => '',
			'type'              => 'tab',
			'instructions'      => '',
			'required'          => 0,
			'conditional_logic' => 0,
			'wrapper'           => array(
				'width' => '',
				'class' => '',
				'id'    => '',
			),
			'placement'         => 'left',
			'endpoint'          => 0,
		);

		$field_group['fields'][] = array(
			'key'   => 'field_form_wpd_shortcode_message',
			'label' => __( 'Shortcode', 'dollie' ),
			'name'  => 'form_wpd_shortcode_message',
			'type'  => 'message',
		);

		$field_group['fields'][] = array(
			'key'           => 'field_form_is_launch_site',
			'label'         => esc_html__( 'Enable Launch Site options', 'dollie' ),
			'name'          => 'form_is_launch_site',
			'type'          => 'true_false',
			'instructions'  => esc_html__( 'If this form launches a new site you can enable this option to enable splash loader on launch and choose a default blueprint.', 'dollie' ),
			'required'      => 0,
			'placeholder'   => '',
			'wrapper'       => array(
				'width' => '',
				'class' => '',
				'id'    => '',
			),
			'ui'            => true,
			'default_value' => 0,
		);

		// Splash template
		$field_group['fields'][] = array(
			'key'               => 'field_form_splash_template',
			'label'             => __( 'Splash template', 'dollie' ),
			'name'              => 'form_splash_template',
			'type'              => 'text',
			'instructions'      => esc_html__( 'Enter a custom Splash template path to appear on form submit. Theme override location theme-name/dollie/tpl-name', 'dollie' ),
			'required'          => 0,
			'placeholder'       => '',
			'default_value'     => 'launch-splash',
			'conditional_logic' => array(
				array(
					array(
						'field'    => 'field_form_is_launch_site',
						'operator' => '==',
						'value'    => '1',
					),
				),
			),
			'wrapper'           => array(
				'width' => '50',
				'class' => '',
				'id'    => '',
			),
			'ui'                => true,
		);

		//blueprint
		$field_group['fields'][] = array(
			'key'               => 'field_form_site_blueprint',
			'label'             => esc_html__( 'Select a Blueprint (optional)', 'dollie' ),
			'name'              => 'site_blueprint',
			'type'              => 'radio',
			'instructions'      => esc_html__( 'Force a default blueprint to use for the launched site', 'dollie' ),
			'required'          => 0,
			'conditional_logic' => array(
				array(
					array(
						'field'    => 'field_form_is_launch_site',
						'operator' => '==',
						'value'    => '1',
					),
				),
			),
			'wrapper'           => array(
				'width' => '',
				'class' => '',
				'id'    => '',
			),
			'hide_admin'        => 0,
			'choices'           => array(),
			'allow_null'        => 0,
			'other_choice'      => 0,
			'default_value'     => '',
			'layout'            => 'vertical',
			'return_format'     => 'value',
			'save_other_choice' => 0,
		);

		//redirect to container YES/NO
		$field_group['fields'][] = array(
			'key'           => 'field_form_redirect_to_site',
			'label'         => esc_html__( 'Redirect to site dashboard', 'dollie' ),
			'name'          => 'form_redirect_to_site',
			'type'          => 'true_false',
			'instructions'  => esc_html__( 'Redirect to site dashboard page on form submit. Success Message will be ignored', 'dollie' ),
			'required'      => 0,
			'placeholder'   => '',
			'wrapper'       => array(
				'width' => '',
				'class' => '',
				'id'    => '',
			),
			'ui'            => true,
			'default_value' => 0,
		);

		return $field_group;
	}

	/**
	 * Get the blueprint to use when launching a site
	 *
	 * @param $form
	 * @param $args
	 *
	 * @return bool|mixed
	 */
	public function get_form_blueprint( $form, $args ) {
		if ( af_get_field( 'site_blueprint' ) ) {
			return af_get_field( 'site_blueprint' );
		}

		if ( isset( $_COOKIE[ Blueprints::COOKIE_NAME ] ) ) {
			return $_COOKIE[ Blueprints::COOKIE_NAME ];
		}

		return $this->get_form_arg( 'site_blueprint', $form, $args );
	}

	/**
	 * Register blockquote shortcode
	 *
	 * @param array $atts
	 * @param string $content
	 *
	 * @return string
	 */
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
   		 <h4>' . wp_kses_post( $atts['title'] ) . '</h4>
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

	/**
	 * If set from form settings, make sure to redirect to new container after form submit
	 */
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
	 * @param $field
	 *
	 * @return string|string[]
	 */
	public function add_acf_placeholders( $field ) {
		if ( isset( $field['message'] ) && $field['message'] ) {
			$currentQuery = dollie()->get_current_object();

			$user = wp_get_current_user();

			if ( ! is_admin() ) {
				$request = dollie()->get_customer_container_details();

				if ( $request && is_object( $request ) && isset( $request->uri ) ) {
					$hostname = preg_replace( '#^https?://#', '', $request->uri );

					$tpl_migration_instructions = Tpl::load( 'migration-instructions', [
						'post_slug' => $currentQuery->slug,
						'request'   => $request,
						'user'      => $user,
						'hostname'  => $hostname
					] );
					$field['message']           = str_replace( '{dollie_migration_instructions}', $tpl_migration_instructions, $field['message'] );
				}
			}

			$ip     = get_post_meta( $currentQuery->id, 'wpd_container_ip', true ) ?: '';
			$domain = get_post_meta( $currentQuery->id, 'wpd_domains', true ) ?: '';
			$url    = get_post_meta( $currentQuery->id, 'wpd_container_uri', true ) ?: '';

			$tpl_link_domain = Tpl::load( 'link-domain', [
				'has_domain'   => $domain,
				'ip'           => $ip,
				'platform_url' => $url,
			] );

			$field['message'] = str_replace( '{dollie_tpl_link_domain}', $tpl_link_domain, $field['message'] );

			if ( is_user_logged_in() ) {
				$user             = wp_get_current_user();
				$field['message'] = str_replace( '{dollie_user_display_name}', $user->display_name, $field['message'] );
			}

			//Support link
			$field['message'] = str_replace( '{dollie_support_link}', dollie()->get_support_link(), $field['message'] );

			// Allow shortcodes
			$field['message'] = do_shortcode( $field['message'] );
		}

		return $field;
	}

	/**
	 * Add form hidden input to save the post we are currently on
	 */
	public function hidden_fields( $form, $args ) {
		echo '<input type="hidden" name="dollie_post_id" value="' . get_the_ID() . '">';
	}

	/**
	 * Localize strings
	 *
	 * @param $field
	 *
	 * @return mixed
	 */
	public function localize_strings( $field ) {
		if ( $field['label'] ) {
			$field['label'] = "!!__(!!'" . $field['label'] . "!!', !!'" . 'dollie' . "!!')!!";
		}

		if ( $field['instructions'] ) {
			$field['instructions'] = "!!__(!!'" . $field['instructions'] . "!!', !!'" . 'dollie' . "!!')!!";
		}

		if ( $field['message'] ) {
			$field['message'] = "!!__(!!'" . $field['message'] . "!!', !!'" . 'dollie' . "!!')!!";
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

	/**
	 * Get the container we used the form on
	 * @return bool|\stdClass
	 */
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

	/**
	 * Prefill site admin email
	 *
	 * @param $value
	 * @param $field
	 * @param $form
	 * @param $args
	 *
	 * @return string
	 */
	public function prefill_site_admin_email( $value, $field, $form, $args ) {
		if ( ! is_user_logged_in() ) {
			return $value;
		}

		return get_userdata( get_current_user_id() )->user_email;
	}

	/**
	 * Prefill site name
	 *
	 * @param $value
	 * @param $field
	 * @param $form
	 * @param $args
	 *
	 * @return string
	 */
	public function prefill_site_name( $value, $field, $form, $args ) {
		return esc_html__( 'My New Site', 'dollie' );
	}

	/**
	 * Submit button attributes
	 *
	 * @param $attributes
	 * @param $form
	 * @param $args
	 *
	 * @return mixed
	 */
	public function filter_submit_button_attributes( $attributes, $form, $args ) {
		$attributes['class'] .= ' btn btn-primary btn-lg';

		return $attributes;
	}

	/**
	 * Filter next button attributes
	 *
	 * @param $attributes
	 * @param $field
	 *
	 * @return mixed
	 */
	public function filter_next_button_attributes( $attributes, $field ) {
		$attributes['class'] .= ' btn btn-primary';

		return $attributes;
	}

	/**
	 * Filter previous button attributes
	 *
	 * @param $attributes
	 * @param $field
	 *
	 * @return mixed
	 */
	public function filter_previous_button_attributes( $attributes, $field ) {
		$attributes['class'] .= ' btn btn-default';

		return $attributes;
	}

	/**
	 * Prefill description
	 *
	 * @param $value
	 * @param $field
	 * @param $form
	 * @param $args
	 *
	 * @return string
	 */
	public function prefill_description( $value, $field, $form, $args ) {
		return esc_html__( 'The best website in the world?', 'dollie' );
	}

	/**
	 * Prefill site admin username
	 *
	 * @param $value
	 * @param $field
	 * @param $form
	 * @param $args
	 *
	 * @return string
	 */
	public function prefill_site_admin_user( $value, $field, $form, $args ) {
		if ( ! is_user_logged_in() ) {
			return $value;
		}

		return get_userdata( get_current_user_id() )->user_login;
	}

}
