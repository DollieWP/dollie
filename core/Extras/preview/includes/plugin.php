<?php
/**
 * Main class and entry point
 */

// If this file is called directly, abort.
if(!defined('ABSPATH')) {
	exit;
}

class LivePreviewPro_Builder {
	private $pluginBasename = NULL;
	
	private $ajax_action_item_update = NULL;
	private $ajax_action_settings_get = NULL;
	
	
	function __construct($pluginBasename) {
		$this->pluginBasename = $pluginBasename;
	}
	
	function run() {
		$upload_dir = wp_upload_dir();
		$plugin_url = plugin_dir_url(dirname(__FILE__));
		
		
		define('LIVEPREVIEWPRO_PLUGIN_PLAN', 'pro');
		
		
		if(is_admin()) {
			$this->ajax_action_item_update = LIVEPREVIEWPRO_PLUGIN_NAME . '_ajax_item_update';
			$this->ajax_action_settings_get = LIVEPREVIEWPRO_PLUGIN_NAME . '_ajax_settings_get';
			
			load_plugin_textdomain(LIVEPREVIEWPRO_PLUGIN_NAME, false, dirname(dirname(plugin_basename(__FILE__))) . '/languages/');
			
			add_action('admin_menu', array($this, 'admin_menu'));
			
			// important, because ajax has another url
			add_action('wp_ajax_' . $this->ajax_action_item_update, array($this, 'ajax_item_update'));
			add_action('wp_ajax_' . $this->ajax_action_settings_get, array($this, 'ajax_settings_get'));
		} else {
			add_filter('template_include', array($this, 'template_include'));
			add_action('wp_enqueue_scripts', array($this, 'template_scripts'), 9999999); // the last execute
		}
	}
	
	/**
	 * Load demo page
	 */
	function template_include($template) {
		$config = get_option(LIVEPREVIEWPRO_PLUGIN_NAME . '_config');
		if($config) {
			$config = unserialize($config);
			
			if($config->page && absint($config->page) > 0) {
				if(is_page(absint($config->page))) {
					$template_new = plugin_dir_path( dirname(__FILE__) ) . 'includes/page-preview.php';
					if($template_new) {
						return $template_new;
					}
				}
			}
		}
		
		return $template;
	}
	
	/**
	 * Load demo page scripts
	 */
	function template_scripts() {
		$config = get_option(LIVEPREVIEWPRO_PLUGIN_NAME . '_config');
		if($config) {
			$config = unserialize($config);
			
			if($config->page && absint($config->page) > 0) {
				if(is_page(absint($config->page))) {
					global $wp_styles;
					foreach( $wp_styles->queue as $handle ) :
						wp_dequeue_style($handle);
					endforeach;
					
					global $wp_scripts;
					foreach($wp_scripts->queue as $handle) :
						wp_dequeue_script($handle);
					endforeach;
					
					$plugin_url = plugin_dir_url(dirname(__FILE__));
					
					wp_enqueue_style(LIVEPREVIEWPRO_PLUGIN_NAME . '_loader', $plugin_url . 'assets/css/loader.min.css', array(), LIVEPREVIEWPRO_PLUGIN_VERSION, 'all' );
					wp_enqueue_style(LIVEPREVIEWPRO_PLUGIN_NAME . '_fontawesome', $plugin_url . 'assets/css/font-awesome.min.css', array(), LIVEPREVIEWPRO_PLUGIN_VERSION, 'all' );
					wp_enqueue_style(LIVEPREVIEWPRO_PLUGIN_NAME . '_bootstrap', $plugin_url . 'assets/css/bootstrap.min.css', array(), LIVEPREVIEWPRO_PLUGIN_VERSION, 'all' );
					
					if($config->theme) {
						wp_enqueue_style(LIVEPREVIEWPRO_PLUGIN_NAME . '_theme', $plugin_url . 'assets/themes/' . $config->theme . '.min.css', array(), LIVEPREVIEWPRO_PLUGIN_VERSION, 'all' );
					}
					
					wp_enqueue_script(LIVEPREVIEWPRO_PLUGIN_NAME . '_bootstrap', $plugin_url . 'assets/js/lib/bootstrap.min.js', array('jquery'), LIVEPREVIEWPRO_PLUGIN_VERSION, false);
					wp_enqueue_script(LIVEPREVIEWPRO_PLUGIN_NAME . '_lazyload', $plugin_url . 'assets/js/lib/lazyload.min.js', array('jquery'), LIVEPREVIEWPRO_PLUGIN_VERSION, false);
					wp_enqueue_script(LIVEPREVIEWPRO_PLUGIN_NAME . '_ellipsis', $plugin_url . 'assets/js/lib/jquery.ellipsis.min.js', array('jquery'), LIVEPREVIEWPRO_PLUGIN_VERSION, false);
					wp_enqueue_script(LIVEPREVIEWPRO_PLUGIN_NAME . '_history', $plugin_url . 'assets/js/lib/jquery.history.min.js', array('jquery'), LIVEPREVIEWPRO_PLUGIN_VERSION, false);
					wp_enqueue_script(LIVEPREVIEWPRO_PLUGIN_NAME . '_query_object', $plugin_url . 'assets/js/lib/jquery.query-object.min.js', array('jquery'), LIVEPREVIEWPRO_PLUGIN_VERSION, false);
					wp_enqueue_script(LIVEPREVIEWPRO_PLUGIN_NAME . '_main', $plugin_url . 'assets/js/main.min.js', array('jquery'), LIVEPREVIEWPRO_PLUGIN_VERSION, false);
					
					$globals = array(
						'plan' => LIVEPREVIEWPRO_PLUGIN_PLAN,
						'responsiveDevice' => $config->responsiveDevice
					);
					wp_localize_script(LIVEPREVIEWPRO_PLUGIN_NAME . '_main', LIVEPREVIEWPRO_PLUGIN_NAME . '_globals', $globals);
				}
			}
		}
	}
	
	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 */
	function admin_menu() {
		// add "edit_posts" if we want to give access to author, editor and contributor roles
		add_menu_page(esc_html__('LivePreview', LIVEPREVIEWPRO_PLUGIN_NAME), esc_html__('LivePreview', LIVEPREVIEWPRO_PLUGIN_NAME), 'manage_options', LIVEPREVIEWPRO_PLUGIN_NAME . '_builder', array( $this, 'admin_menu_page_builder' ), 'dashicons-welcome-view-site');
	}
	
	/**
	 * Show admin menu item builder page
	 */
	function admin_menu_page_builder() {
		$page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_STRING);
		
		if($page===LIVEPREVIEWPRO_PLUGIN_NAME . '_builder') {
			$plugin_url = plugin_dir_url(dirname(__FILE__));
			$upload_dir = wp_upload_dir();
			
			// styles
			wp_enqueue_style(LIVEPREVIEWPRO_PLUGIN_NAME . '_admin', $plugin_url . 'assets/css/admin.min.css', array(), LIVEPREVIEWPRO_PLUGIN_VERSION, 'all' );
			wp_enqueue_style(LIVEPREVIEWPRO_PLUGIN_NAME . '_fontawesome', $plugin_url . 'assets/css/font-awesome.min.css', array(), LIVEPREVIEWPRO_PLUGIN_VERSION, 'all' );
			
			// scripts
			wp_enqueue_script(LIVEPREVIEWPRO_PLUGIN_NAME . '_admin', $plugin_url . 'assets/js/admin.min.js', array('jquery'), LIVEPREVIEWPRO_PLUGIN_VERSION, false );
			wp_enqueue_media();
			
			// global settings to help ajax work
			$globals = array(
				'plan' => LIVEPREVIEWPRO_PLUGIN_PLAN,
				'msg_pro_title' => esc_html__('Available only in the pro version', LIVEPREVIEWPRO_PLUGIN_NAME),
				'wp_base_url' => get_site_url(),
				'upload_base_url' => $upload_dir['baseurl'],
				'plugin_base_url' => $plugin_url,
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'ajax_nonce' => wp_create_nonce( LIVEPREVIEWPRO_PLUGIN_NAME . '_ajax' ),
				'ajax_action_get' => $this->ajax_action_settings_get,
				'ajax_action_update' => $this->ajax_action_item_update,
				'ajax_msg_error' => esc_html__('Uncaught Error', LIVEPREVIEWPRO_PLUGIN_NAME), //Look at the console (F12 or Ctrl+Shift+I, Console tab) for more information
				'lite_msg_error' => esc_html__('You can create and use only 3 items. Buy the pro version.', LIVEPREVIEWPRO_PLUGIN_NAME)
			);
			
			$globals['config'] = NULL;
			
			$config = get_option(LIVEPREVIEWPRO_PLUGIN_NAME . '_config');
			if($config) {
				$globals['config'] = json_encode(unserialize($config));
			}
			
			require_once( plugin_dir_path( dirname(__FILE__) ) . 'includes/page-builder.php' );
			
			// set global settings
			wp_localize_script(LIVEPREVIEWPRO_PLUGIN_NAME . '_admin', LIVEPREVIEWPRO_PLUGIN_NAME . '_globals', $globals);
		}
	}
	
	/**
	 * Ajax update item data
	 */
	function ajax_item_update() {
		$error = false;
		$data = array();
		
		if(check_ajax_referer(LIVEPREVIEWPRO_PLUGIN_NAME . '_ajax', 'nonce', false)) {
			$optionName = 
			$inputData = filter_input(INPUT_POST, 'data', FILTER_UNSAFE_RAW);
			$itemData = json_decode($inputData);
			
			update_option(LIVEPREVIEWPRO_PLUGIN_NAME . '_config', serialize($itemData), false);
			
			$data['msg'] = esc_html__('Item updated', LIVEPREVIEWPRO_PLUGIN_NAME);
		} else {
			$error = true;
			$data['msg'] = esc_html__('The operation failed', LIVEPREVIEWPRO_PLUGIN_NAME);
		}
		
		if($error) {
			wp_send_json_error($data);
		} else {
			wp_send_json_success($data);
		}
		
		wp_die(); // this is required to terminate immediately and return a proper response
	}
	
	/**
	 * Ajax settings get data
	 */
	function ajax_settings_get() {
		$error = false;
		$data = array();
		$type = filter_input(INPUT_POST, 'type', FILTER_SANITIZE_STRING);
		
		if(check_ajax_referer(LIVEPREVIEWPRO_PLUGIN_NAME . '_ajax', 'nonce', false)) {
			switch($type) {
				case 'themes': {
					$data['list'] = array();
					
					$files = glob(plugin_dir_path( dirname(__FILE__) ) . 'assets/themes/*.min.css');
					foreach($files as $file) {
						$filename = basename($file, '.min.css');
						
						array_push($data['list'], array(
							'id' => $filename, 
							'title' => str_replace('-', ' ', $filename)
						));
					}
				}
				break;
				case 'pages': {
					$data['list'] = array();
					$pages = get_pages();
					
					foreach($pages as $page) {
						$ancestors = get_post_ancestors($page->ID);
						$level = count($ancestors);
						
						array_push($data['list'], array(
							'id' => $page->ID,
							'level' => $level,
							'title' => str_repeat('- ', $level) . $page->post_title,
							'url' => esc_url(get_permalink($page->ID))
						));
					}
				}
				break;
				default: {
					$error = true;
					$data['msg'] = esc_html__('The operation failed', LIVEPREVIEWPRO_PLUGIN_NAME);
				}
				break;
			}
		} else {
			$error = true;
			$data['msg'] = esc_html__('The operation failed', LIVEPREVIEWPRO_PLUGIN_NAME);
		}
		
		if($error) {
			wp_send_json_error($data);
		} else {
			wp_send_json_success($data);
		}
		
		wp_die(); // this is required to terminate immediately and return a proper response
	}
}

?>