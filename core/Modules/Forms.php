<?php

namespace Dollie\Core\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Modules\Forms\CreateBackup;
use Dollie\Core\Modules\Forms\DomainConnect;
use Dollie\Core\Modules\Forms\LaunchSite;
use Dollie\Core\Modules\Forms\ListBackups;
use Dollie\Core\Modules\Forms\Performance;
use Dollie\Core\Modules\Forms\PluginUpdates;
use Dollie\Core\Modules\Forms\DeleteSite;
use Dollie\Core\Modules\Forms\QuickLaunch;
use Dollie\Core\Singleton;

/**
 * Class Forms
 *
 * @package Dollie\Core\Modules
 */
class Forms extends Singleton {

	/**
	 * Forms constructor.
	 */
	public function __construct() {
		parent::__construct();

		LaunchSite::instance();
		QuickLaunch::instance();
		CreateBackup::instance();
		ListBackups::instance();
		DomainConnect::instance();
		PluginUpdates::instance();
		DeleteSite::instance();
		Performance::instance();

		add_action( 'template_redirect', [ $this, 'redirect_to_new_container' ] );

		add_action( 'init', [ $this, 'init' ] );
		add_action( 'acf/init', [ $this, 'acf_init' ] );

		if ( isset( $_GET['page'], $_GET['tool'] ) && 'acf-tools' === $_GET['page'] && 'export' === $_GET['tool'] ) {
			add_filter( 'acf/prepare_field_for_export', [ $this, 'localize_strings' ] );
		}

		add_action( 'af/form/hidden_fields', [ $this, 'hidden_fields' ], 10, 2 );

		// Custom form fields
		add_filter( 'af/form/valid_form', [ $this, 'add_custom_field_defaults' ], 10, 1 );
		add_filter( 'af/form/from_post', [ $this, 'form_from_post' ], 10, 2 );
		add_filter( 'af/form/to_post', [ $this, 'form_to_post' ], 10, 2 );
		add_filter( 'af/form/args', [ $this, 'change_form_args' ], 10, 2 );
	}

	/**
	 * Code that runs at WP init. Hooks and content alterations.
	 */
	public function init() {
		add_shortcode( 'dollie_form', [ $this, 'form_shortcode' ] );

		add_filter( 'acf/prepare_field/name=form_shortcode_message', [ $this, 'hide_af_form_shortcode' ], 12, 1 );
		add_filter(
			'acf/prepare_field/name=form_wpd_shortcode_message',
			[
				$this,
				'display_form_shortcode',
			],
			12,
			1
		);
	}

	/**
	 * ACF/AF specific hooks
	 */
	public function acf_init() {
		add_filter( 'af/merge_tags/resolve', [ $this, 'resolve_fields_tag' ], 9, 3 );
		add_filter( 'af/merge_tags/resolve', [ $this, 'add_merge_tags' ], 10, 2 );
		add_filter( 'af/merge_tags/custom', [ $this, 'register_merge_tags' ], 10, 2 );

		// Placeholders/Change values.
		add_filter( 'acf/prepare_field/type=message', [ $this, 'add_acf_placeholders' ], 10 );

		add_filter( 'af/form/button_attributes', [ $this, 'filter_submit_button_attributes' ], 10, 3 );
		add_filter( 'af/form/next_button_atts', [ $this, 'filter_next_button_attributes' ], 10, 2 );
		add_filter( 'af/form/previous_button_atts', [ $this, 'filter_previous_button_attributes' ], 10, 2 );

		add_filter( 'af/field/prefill_value/name=admin_username', [ $this, 'prefill_site_admin_user' ], 10, 4 );
		add_filter( 'af/field/prefill_value/name=admin_email', [ $this, 'prefill_site_admin_email' ], 10, 4 );
		add_filter( 'af/field/prefill_value/name=site_admin_email', [ $this, 'prefill_site_admin_email' ], 10, 4 );

		add_filter( 'af/form/settings_fields', [ $this, 'add_form_settings_fields' ], 2, 1 );
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
	public function register_merge_tags( $tags, $form ) {
		$tags[] = [
			'value' => 'dollie_container_login_url',
			'label' => __( 'Dollie Container Login URL', 'dollie' ),
		];
		$tags[] = [
			'value' => 'dollie_container_url',
			'label' => __( 'Dollie Container URL', 'dollie' ),
		];

		return $tags;
	}

	/**
	 * Return the site login url used in site form
	 *
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
		if ( ! function_exists( 'advanced_form' ) ) {
			return '';
		}
		$form_id_or_key = $atts['form'];
		unset( $atts['form'] );

		$atts['echo'] = false;
		if ( isset( $atts['values'] ) ) {

			$final_values = [];
			$values_array = explode( ',', $atts['values'] );
			foreach ( $values_array as $value ) {
				$array_value = explode( ':', $value );
				if ( count( $array_value ) > 1 ) {
					$final_values[ $array_value[0] ] = $array_value[1];
				}
			}
			$atts['values'] = $final_values;
		}

		wp_enqueue_script( 'dollie-launch-dynamic-data' );

		return advanced_form( $form_id_or_key, $atts );
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
		$form['site_blueprint']   = false;
		$form['redirect_to_site'] = false;

		return $form;
	}

	/**
	 * Add any settings to form object for forms loaded from posts
	 *
	 * @param $form
	 * @param $post
	 *
	 * @return mixed
	 */
	public function form_from_post( $form, $post ) {
		$is_launch_site   = get_field( 'form_is_launch_site', $post->ID );
		$site_blueprint   = get_field( 'site_blueprint', $post->ID );
		$redirect_to_site = get_field( 'form_redirect_to_site', $post->ID );

		if ( $is_launch_site ) {
			$form['is_launch_site'] = $is_launch_site;

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
	public function form_to_post( $form, $post ) {
		update_field( 'field_form_is_launch_site', $form['is_launch_site'], $post->ID );
		update_field( 'field_form_site_blueprint', $form['site_blueprint'], $post->ID );
		update_field( 'field_form_redirect_to_site', $form['redirect_to_site'], $post->ID );
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

		if ( true === $redirect ) {
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
	 * @param $field
	 *
	 * @return mixed
	 */
	public function display_form_shortcode( $field ) {
		global $post;

		if ( $post && $key = get_post_meta( $post->ID, 'form_key', true ) ) {
			if ( strpos( $key, 'form_dollie' ) !== false ) {
				$message = sprintf( '<code>[dollie_form form="%s"]</code>', $key );

				$message .= "<p class='description'>" . __( 'Possible shortcode attributes: ', 'dollie' ) . '<br>';
				$message .= "
				redirect_to_site=true|false
				site_blueprint=ID
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
	 * @param $field_group
	 *
	 * @return mixed
	 */
	public function add_form_settings_fields( $field_group ) {
		$field_group['fields'][] = [
			'key'               => 'field_form_wpd_shortcode_tab',
			'label'             => '<span class="dashicons dashicons-admin-settings"></span>' . __( 'Settings', 'dollie' ),
			'name'              => '',
			'type'              => 'tab',
			'instructions'      => '',
			'required'          => 0,
			'conditional_logic' => 0,
			'wrapper'           => [
				'width' => '',
				'class' => '',
				'id'    => '',
			],
			'placement'         => 'left',
			'endpoint'          => 0,
		];

		$field_group['fields'][] = [
			'key'   => 'field_form_wpd_shortcode_message',
			'label' => __( 'Shortcode', 'dollie' ),
			'name'  => 'form_wpd_shortcode_message',
			'type'  => 'message',
		];

		$field_group['fields'][] = [
			'key'           => 'field_form_is_launch_site',
			'label'         => __( 'Enable Launch Site options', 'dollie' ),
			'name'          => 'form_is_launch_site',
			'type'          => 'true_false',
			'instructions'  => __( 'If this form launches a new site you can enable this option to enable splash loader on launch and choose a default blueprint.', 'dollie' ),
			'required'      => 0,
			'placeholder'   => '',
			'wrapper'       => [
				'width' => '',
				'class' => '',
				'id'    => '',
			],
			'ui'            => true,
			'default_value' => 0,
		];

		// blueprint
		$field_group['fields'][] = [
			'key'               => 'field_form_site_blueprint',
			'label'             => __( 'Select a Blueprint (optional)', 'dollie' ),
			'name'              => 'site_blueprint',
			'type'              => 'radio',
			'instructions'      => __( 'Force a default blueprint to use for the launched site', 'dollie' ),
			'required'          => 0,
			'conditional_logic' => [
				[
					[
						'field'    => 'field_form_is_launch_site',
						'operator' => '==',
						'value'    => '1',
					],
				],
			],
			'wrapper'           => [
				'width' => '',
				'class' => '',
				'id'    => '',
			],
			'hide_admin'        => 0,
			'choices'           => [],
			'allow_null'        => 0,
			'other_choice'      => 0,
			'default_value'     => '',
			'layout'            => 'vertical',
			'return_format'     => 'value',
			'save_other_choice' => 0,
		];

		// redirect to container YES/NO
		$field_group['fields'][] = [
			'key'           => 'field_form_redirect_to_site',
			'label'         => __( 'Redirect to site', 'dollie' ),
			'name'          => 'form_redirect_to_site',
			'type'          => 'true_false',
			'instructions'  => __( 'Redirect to site page after form submit. Success Message will be ignored', 'dollie' ),
			'required'      => 0,
			'placeholder'   => '',
			'wrapper'       => [
				'width' => '',
				'class' => '',
				'id'    => '',
			],
			'ui'            => true,
			'default_value' => 0,
		];

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

		if ( isset( $_COOKIE[ DOLLIE_BLUEPRINTS_COOKIE ] ) ) {
			return $_COOKIE[ DOLLIE_BLUEPRINTS_COOKIE ];
		}

		return $this->get_form_arg( 'site_blueprint', $form, $args );
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

		if ( is_array( $field['value'] ) && 'array' === $field['return_format']
			 && ( 'radio' === $field['type'] || 'select' === $field['type'] ) ) {

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
		if ( isset( $_GET['site'] ) && 'new' === $_GET['site'] ) {
			$url = dollie()->get_latest_container_url();

			if ( $url ) {
				wp_redirect( $url );
				exit();
			}
		}
	}

	/**
	 * Replace form placeholders
	 *
	 * @param $field
	 *
	 * @return string|array
	 */
	public function add_acf_placeholders( $field ) {
		if ( isset( $field['message'] ) && $field['message'] ) {
			$current_query = dollie()->get_current_object();

			$user = wp_get_current_user();

			$ip     = dollie()->get_wp_site_data( 'ip', $current_query->id );
			$url    = dollie()->get_wp_site_data( 'uri', $current_query->id );
			$domain = get_post_meta( $current_query->id, 'wpd_domains', true ) ?: '';

			$tpl_domain_not_managed = dollie()->load_template(
				'widgets/site/pages/domain/connect/not-managed',
				[
					'has_domain'   => $domain,
					'ip'           => $ip,
					'platform_url' => $url,
				]
			);

			$field['message'] = str_replace( '{dollie_tpl_domain_not_managed}', $tpl_domain_not_managed, $field['message'] );

			$tpl_domain_managed = dollie()->load_template(
				'widgets/site/pages/domain/connect/managed',
				[
					'has_domain'   => $domain,
					'ip'           => $ip,
					'platform_url' => $url,
				]
			);

			$field['message'] = str_replace( '{dollie_tpl_domain_managed}', $tpl_domain_managed, $field['message'] );

			if ( is_user_logged_in() ) {
				$user             = wp_get_current_user();
				$field['message'] = str_replace( '{dollie_user_display_name}', $user->display_name, $field['message'] );
			}

			// Support link.
			$field['message'] = str_replace( '{dollie_support_link}', dollie()->get_support_link(), $field['message'] );

			// Allow shortcodes.
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

		if ( isset( $field['instructions'] ) && $field['instructions'] ) {
			$field['instructions'] = "!!__(!!'" . $field['instructions'] . "!!', !!'" . 'dollie' . "!!')!!";
		}

		if ( isset( $field['message'] ) && $field['message'] ) {
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
	 *
	 * @return bool|\stdClass
	 */
	public static function get_form_container() {
		if ( isset( AF()->submission['extra'], AF()->submission['extra']['dollie_container_id'] ) ) {
			$container_id = AF()->submission['extra']['dollie_container_id'];
		} elseif ( isset( $_POST['dollie_post_id'] ) ) {
			$container_id = (int) $_POST['dollie_post_id'];
		}

		if ( ! isset( $container_id ) ) {
			return false;
		}

		$container = dollie()->get_current_object( $container_id );

		if ( 0 === $container_id ) {
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
