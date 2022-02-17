<?php
/**
 * Plugins code for the admin area.
 *
 * @since 1.1.0 Admin code split from {@link CBox_Plugins}
 *
 * @package Dollie_Setup
 */

/**
 * Setup plugin code for the DOLLIE_SETUP admin area.
 *
 * @since 1.1.0
 */
class CBox_Admin_Plugins {
	public static function init() {
		return new self();
	}

	/**
	 * Constructor.
	 *
	 * @since 1.1.0
	 */
	protected function __construct() {
		// load PD on admin menu hook.
		add_action( 'dollie_setup_admin_menu',                       array( 'Plugin_Dependencies', 'init' ), 0 );

		// setup the DOLLIE_SETUP plugin menu
		add_action( 'dollie_setup_admin_menu',                       array( $this, 'setup_plugins_page' ) );

		// filter PD's dependency list
		add_filter( 'scr_plugin_dependency_before_parse',    array( $this, 'filter_pd_dependencies' ) );

		// prevent DOLLIE_SETUP plugins from being seen in the regular Plugins table and from WP updates
		if ( ! $this->is_override() ) {
			// exclude DOLLIE_SETUP plugins from the "Plugins" list table
			//add_filter( 'all_plugins',                   array( $this, 'exclude_dollie_setup_plugins' ) );

			// remove DOLLIE_SETUP plugins from WP's update plugins routine
			add_filter( 'site_transient_update_plugins', array( $this, 'remove_dollie_setup_plugins_from_updates' ) );

			// do not show PD's pre-activation warnings if admin cannot override DOLLIE_SETUP plugins
			add_filter( 'pd_show_preactivation_warnings', '__return_false' );
		}
	}

	/**
	 * For expert site managers, we allow them to view DOLLIE_SETUP plugins in the
	 * regular Plugins table and on the WP Updates page.
	 *
	 * To do this, add the following code snippet to wp-config.php
	 *
	 * 	define( 'DOLLIE_SETUP_OVERRIDE_PLUGINS', true );
	 *
	 * @return bool
	 */
	public function is_override() {
		return defined( 'DOLLIE_SETUP_OVERRIDE_PLUGINS' ) && constant( 'DOLLIE_SETUP_OVERRIDE_PLUGINS' ) === true;
	}

	/**
	 * Filter PD's dependencies to add our own specs.
	 *
	 * @return array
	 */
	public function filter_pd_dependencies( $plugins ) {
		$plugins_by_name = Plugin_Dependencies::$plugins_by_name;

		foreach( CBox_Plugins::get_plugins() as $plugin => $data ) {
			// try and see if our required plugin is installed
			$loader = ! empty( $plugins_by_name[ $plugin ] ) ? $plugins_by_name[ $plugin ] : false;

			// if plugin is installed and if the plugin doesn't already have predefined dependencies, add our custom deps!
			if( ! empty( $loader ) && ! empty( $data['depends'] ) && empty( $plugins[ $loader ]['Depends'] ) ) {
				$plugins[ $loader ]['Depends'] = $data['depends'];
			}
		}

		return $plugins;
	}

	/**
	 * Exclude DOLLIE_SETUP's plugins from the "Plugins" list table.
	 *
	 * @return array
	 */
	public function exclude_dollie_setup_plugins( $plugins ) {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			return $plugins;
		}

		$plugins_by_name = Plugin_Dependencies::$plugins_by_name;
		$dollie_setup_plugins    = CBox_Plugins::get_plugins();

		if ( is_multisite() ) {
			$dependency = CBox_Plugins::get_plugins( 'dependency' );
			if ( ! empty( $dependency ) ) {
				$dollie_setup_plugins = $dollie_setup_plugins + $dependency;
			}
		}

		foreach( $dollie_setup_plugins as $plugin => $data ) {
			// try and see if our required plugin is installed
			$loader = ! empty( $plugins_by_name[ $plugin ] ) ? $plugins_by_name[ $plugin ] : false;

			// if our DOLLIE_SETUP plugin is found, get rid of it
			if( ! empty( $loader ) && ! empty( $plugins[ $loader ] ) ) {
				// We want to show the plugin, so bail.
				if ( false === $data['hide'] ) {
					continue;
				}

				unset( $plugins[ $loader ] );
			}
		}

		return $plugins;
	}


	/**
	 * DOLLIE_SETUP plugins should be removed from WP's update plugins routine.
	 */
	public function remove_dollie_setup_plugins_from_updates( $plugins ) {
		$i = 0;

		foreach ( CBox_Plugins::get_plugins() as $plugin => $data ) {
			// get the plugin loader file
			$plugin_loader = Plugin_Dependencies::get_pluginloader_by_name( $plugin );

			// if our DOLLIE_SETUP plugin is found, get rid of it
			if ( ! empty( $plugins->response[ $plugin_loader ] ) ) {
				unset( $plugins->response[ $plugin_loader ] );
				++$i;
			}
		}

		// update the "Dashboard > Updates" count to be accurate
		if ( $i > 0 ) {
			set_site_transient( 'update_plugins', $plugins );
		}

		return $plugins;
	}

	/**
	 * Organize plugins by state.
	 *
	 * @since 0.3
	 *
	 * @return Associative array with plugin state as array key
	 */
	public static function organize_plugins_by_state( $plugins ) {
		$organized_plugins = array();

		foreach ( (array) $plugins as $plugin => $data ) {
			// attempt to get the plugin loader file
			$loader = Plugin_Dependencies::get_pluginloader_by_name( $plugin );

			// get the required plugin's state
			$state  = self::get_plugin_state( $loader, $data );

			$organized_plugins[$state][] = esc_attr( $plugin );
		}

		return $organized_plugins;
	}

	/**
	 * Helper method to see if a plugin is active.
	 *
	 * This is a resource-friendly version that already references the active
	 * plugins in the Plugin Dependencies variable.
	 *
	 * @since 0.2
	 *
	 * @param string $loader Plugin loader filename.
	 * @return bool
	 */
	public static function is_plugin_active( $loader ) {
		$is_active = null;

		// BuddyPress complicates things due to a different root blog ID.
		if ( ! dollie_setup_is_main_site() ) {
			$dollie_setup_plugins = CBox_Plugins::get_plugins();
			$plugin_data  = get_plugin_data( WP_PLUGIN_DIR . '/' . $loader );

			// 'network' flag is false, so switch to root blog.
			if ( false === $dollie_setup_plugins[ $plugin_data['Name'] ]['network'] ) {
				switch_to_blog( dollie_setup_get_main_site_id() );
				$is_active = is_plugin_active( $loader );
				restore_current_blog();

			// 'network' flag is true.
			} else {
				$is_active = is_plugin_active_for_network( $loader );
			}
		}

		// Use already-queried active plugins from PD.
		if ( null === $is_active ) {
			$is_active = in_array( $loader, (array) Plugin_Dependencies::$active_plugins );
		}

		return $is_active;
	}

	/**
	 * Helper method to get the DOLLIE_SETUP required plugin's state.
	 *
	 * @since 0.2
	 *
	 * @param str $loader The required plugin's loader filename
 	 * @param array $data The required plugin's data. See $this->register_required_plugins().
	 */
	public static function get_plugin_state( $loader, $data ) {
		$state = false;

		// plugin exists
		if ( $loader ) {
			// if plugin is active, set state to 'deactivate'
			if ( self::is_plugin_active( $loader ) )
				$state = 'deactivate';
			else
				$state = 'activate';

			// a required version was passed
			if ( ! empty( $data['version'] ) ) {
				// get the current, installed plugin's version
				$current_version = Plugin_Dependencies::$all_plugins[$loader]['Version'];

				// if current plugin is older than required plugin version, set state to 'upgrade'
				if ( version_compare( $current_version, $data['version'] ) < 0  )
					$state = 'upgrade';
			}
		}
		// plugin doesn't exist
		else {
			$state = 'install';
		}

		return $state;
	}

	/**
	 * Get plugins that require upgrades.
	 *
	 * @since 0.3
	 *
	 * @param string $type The type of plugins to get upgrades for. Either 'all' or 'active'.
	 * @return array of DOLLIE_SETUP plugin names that require upgrading
	 */
	public static function get_upgrades( $type = 'all' ) {
		$dollie_setup_plugins = CBox_Plugins::get_plugins();

		// Make sure dependency plugins are checked as well.
		$dependencies = CBox_Plugins::get_plugins( 'dependency' );
		if ( ! empty( $dependencies ) ) {
			foreach ( $dependencies as $plugin => $data ) {
				// If plugin is already listed, skip.
				if ( isset( $dollie_setup_plugins[ $plugin ] ) ) {
					continue;
				}

				// Add dependency plugin.
				$dollie_setup_plugins[ $plugin ] = $data;
			}
		}

		// Get all DOLLIE_SETUP plugins that require upgrades.
		$upgrades = self::organize_plugins_by_state( $dollie_setup_plugins );

		if ( empty( $upgrades['upgrade'] ) )
			return false;

		$upgrades = $upgrades['upgrade'];

		switch ( $type ) {
			case 'all' :
				return $upgrades;

				break;

			case 'active' :
				// get all active plugins
				$active_plugins = array_flip( Plugin_Dependencies::$active_plugins );

				$plugins = array();

				foreach ( $upgrades as $plugin ) {
					// attempt to get the plugin loader file
					$loader = Plugin_Dependencies::get_pluginloader_by_name( $plugin );

					// if the plugin is active, add it to our plugin array
					if ( isset( $active_plugins[$loader] ) )
						$plugins[] = $plugin;
				}

				// Get install-only plugins as well.
				$install_only_plugins = CBox_Plugins::get_plugins( 'install-only' );
				if ( ! empty( $install_only_plugins ) ) {
					$install_only_plugins = array_intersect( $upgrades, array_keys( $install_only_plugins ) );

					if ( ! empty( $install_only_plugins ) ) {
						$plugins = array_merge( $plugins, $install_only_plugins );
					}
				}

				if ( empty( $plugins ) )
					return false;

				return $plugins;

				break;
		}

	}

	/**
	 * Get settings links for our installed DOLLIE_SETUP plugins.
	 *
	 * @since 0.3
	 *
	 * @return Assosicate array with DOLLIE_SETUP plugin name as key and admin settings URL as the value.
	 */
	public static function get_settings() {
		// get all installed DOLLIE_SETUP plugins
		$dollie_setup_plugins = CBox_Plugins::get_plugins();

		// get active DOLLIE_SETUP plugins
		$active = self::organize_plugins_by_state( $dollie_setup_plugins );

		if ( empty( $active ) )
			return false;

		$active = isset( $active['deactivate'] ) ? $active['deactivate'] : array();

		$settings = array();

		foreach ( $active as $plugin ) {
			// network DOLLIE_SETUP install and DOLLIE_SETUP plugin has a network settings page
			if ( is_network_admin() && ! empty( $dollie_setup_plugins[$plugin]['network_settings'] ) ) {
				// if network plugin's settings resides on the root blog,
				// then make sure we use the root blog's domain to generate the admin settings URL
				if ( $dollie_setup_plugins[$plugin]['network_settings'] == 'root-blog-only' ) {
					// sanity check!
					// make sure BP is active so we can use bp_core_get_root_domain()
					if ( in_array( 'BuddyPress', $active ) ) {
						$settings[$plugin] = bp_core_get_root_domain() . '/wp-admin/' . $dollie_setup_plugins[$plugin]['admin_settings'];
					}
				}
				// if the network plugin resides in the network area, use network_admin_url()!
				else {
					$settings[$plugin] = network_admin_url( $dollie_setup_plugins[$plugin]['network_settings'] );
				}
			}

			// single-site DOLLIE_SETUP install and DOLLIE_SETUP plugin has an admin settings page
			elseif( ! is_network_admin() && ! empty( $dollie_setup_plugins[$plugin]['admin_settings'] ) ) {
				$settings[$plugin] = admin_url( $dollie_setup_plugins[$plugin]['admin_settings'] );
			}

		}

		return $settings;
	}

	/**
	 * Setup DOLLIE_SETUP's plugin menu item.
	 *
	 * The "Plugins" menu item only appears once DOLLIE_SETUP is completely setup.
	 *
	 * @since 0.3
	 *
	 * @uses dollie_setup_is_setup() To see if DOLLIE_SETUP is completely setup.
	 */
	public function setup_plugins_page() {
		// see if DOLLIE_SETUP is fully setup
		if ( dollie_setup_is_setup() ) {
			// add our plugins page
			$plugin_page = add_submenu_page(
				'dollie_setup',
				__( 'Dollie Setup Plugins', 'dollie-setup' ),
				__( 'Plugins', 'dollie-setup' ),
				'install_plugins', // todo - map cap?
				'dollie_setup-plugins',
				array( $this, 'admin_page' )
			);

			// load Plugin Dependencies plugin on the DOLLIE_SETUP plugins page
			add_action( "load-{$plugin_page}",       array( 'Plugin_Dependencies', 'init' ) );

			// validate any settings changes submitted from the DOLLIE_SETUP plugins page
			add_action( "load-{$plugin_page}",       array( $this, 'validate_dollie_setup_dashboard' ) );

		}
	}

	/**
	 * Before the DOLLIE_SETUP plugins page is rendered, do any validation and checks
	 * from form submissions or action links.
	 *
	 * @since 0.2
	 */
	public function validate_dollie_setup_dashboard() {
		// form submission
		if ( ! empty( $_REQUEST['dollie_setup-update'] ) ) {
			// verify nonce
			check_admin_referer( 'dollie_setup_update' );

			// see if any plugins were submitted
			// if so, set a reference variable to note that DOLLIE_SETUP is updating
			if ( ! empty( $_REQUEST['dollie_setup_plugins'] ) ) {
				dollie_setup()->update = true;
			}
		}

		// deactivate a single plugin from the DOLLIE_SETUP dashboard
		// basically a copy and paste of the code available in /wp-admin/plugins.php
		if ( ! empty( $_REQUEST['dollie_setup-action'] ) && ! empty( $_REQUEST['plugin'] ) ) {
			$plugin = $_REQUEST['plugin'];

			if ( ! current_user_can('activate_plugins') ) {
				wp_die( __( 'You do not have sufficient permissions to deactivate plugins for this site.' ) );
			}

			// Parse referer.
			$qs = parse_url( wp_get_referer(), PHP_URL_QUERY );
			parse_str( $qs, $qs );
			$type = ! empty( $qs['type'] ) ? esc_attr( $qs['type'] ) : '';

			// Set redirect URL
			$url = self_admin_url( 'admin.php?page=dollie_setup-plugins' );
			$url = ! empty( $type ) ? add_query_arg( 'type', $type, $url ) : $url;

			switch( $_REQUEST['dollie_setup-action'] ) {
				case 'deactivate' :
					check_admin_referer('deactivate-plugin_' . $plugin);

					// if plugin is already deactivated, redirect to DOLLIE_SETUP dashboard and stop!
					if ( ! self::is_plugin_active( $plugin ) ) {
						wp_safe_redirect( $url );
						exit;

					// start deactivating!
					} else {
						// Deactivate dependent plugins.
						$deactivated = call_user_func( array( 'Plugin_Dependencies', "deactivate_cascade" ), (array) $plugin );

						// Save markers.
						set_site_transient( "dollie_setup_deactivate_cascade", $deactivated );

						// Multisite
						if ( is_multisite() ) {
							// Darn BuddyPress...
							if ( ! dollie_setup_is_main_site() ) {
								switch_to_blog( dollie_setup_get_main_site_id() );
								$switched = true;
							}

							// Deactivate dependent plugins on main site as well.
							deactivate_plugins( $deactivated, false, false );

							/*
							 * Also deactivate the main plugin in question.
							 *
							 * Should probably look at our 'network' flag...
							 */
							deactivate_plugins( $plugin, false, is_plugin_active_for_network( $plugin ) );

							// Switch back.
							if ( ! empty( $switched ) ) {
								restore_current_blog();
								unset( $switched );
							}

						// Single site.
						} else {
							// Deactivate the main plugin in question.
							deactivate_plugins( $plugin, false );

						}

						if ( ! is_network_admin() )
							update_option('recently_activated', array($plugin => time()) + (array)get_option('recently_activated'));

						wp_safe_redirect( add_query_arg( 'deactivate', 'true', $url ) );
						exit;
					}

					break;

				case 'network-deactivate' :
					check_admin_referer('network-deactivate-plugin_' . $plugin);

					if ( ! is_multisite() ) {
						wp_safe_redirect( $url );
						exit;
					}

					$loader = Plugin_Dependencies::get_pluginloader_by_name( $plugin );

					// Network deactivate the plugin.
					deactivate_plugins( $loader, true, true );

					// Darn BuddyPress...
					if ( ! dollie_setup_is_main_site() ) {
						switch_to_blog( dollie_setup_get_main_site_id() );
						$switched = true;
					}

					// Make sure the plugin is active on the main site.
					activate_plugin( $loader, '', false, true );

					// Switch back.
					if ( ! empty( $switched ) ) {
						restore_current_blog();
						unset( $switched );
					}

					wp_safe_redirect( add_query_arg( 'network-deactivate', 'true', $url ) );

					break;

				case 'uninstall' :
					check_admin_referer( 'bulk-plugins' );

					$result = true;
					if ( CBox_Plugins::is_plugin_type( $plugin, 'install-only' ) ) {
						$loader = Plugin_Dependencies::get_pluginloader_by_name( $plugin );
						$result = delete_plugins( (array) $loader );

						if ( is_wp_error( $result ) ) {
							$result = 0;

						// If plugin was activated on the DOLLIE_SETUP site, refresh active plugins list.
						} elseif ( ! is_wp_error( $result ) && self::is_plugin_active( $loader ) ) {
							// Switch to DOLLIE_SETUP main site ID, if necessary.
							if ( ! dollie_setup_is_main_site() ) {
								switch_to_blog( dollie_setup_get_main_site_id() );
								$switched = true;
							}

							// Validate existing plugins.
							validate_active_plugins();

							// Switch back.
							if ( ! empty( $switched ) ) {
								restore_current_blog();
								unset( $switched );
							}
						}
					}

					wp_safe_redirect( add_query_arg( 'uninstall', $result, $url ) );
					exit;
					break;
			}
		}

		// admin notices
		if ( ! empty( $_REQUEST['deactivate'] ) ) {
			// add an admin notice
			$prefix = is_network_admin() ? 'network_' : '';
			add_action( $prefix . 'admin_notices', function() {
				echo '<div class="updated"><p>' . __( 'Plugin deactivated.', 'dollie-setup' ) . '</p></div>';
			} );

			// if PD deactivated any other dependent plugins, show admin notice here
			// basically a copy-n-paste of Plugin_Dependencies::generate_dep_list()
			$deactivated = get_site_transient( 'dollie_setup_deactivate_cascade' );
			delete_site_transient( 'dollie_setup_deactivate_cascade' );

			// if no other plugins were deactivated, stop now!
			if ( empty( $deactivated ) )
				return;

			$text = __( 'The following plugins have also been deactivated:', 'dollie-setup' );

			// render each plugin as a list item
			// not really a fan of the code below, but it's from Plugin Dependencies
			$all_plugins = Plugin_Dependencies::$all_plugins;
			$dep_list = '';
			foreach ( $deactivated as $dep ) {
				$plugin_ids = Plugin_Dependencies::get_providers( $dep );

				if ( empty( $plugin_ids ) ) {
					$name = html( 'span', esc_html( $dep['Name'] ) );
				} else {
					$list = array();
					foreach ( $plugin_ids as $plugin_id ) {
						$name = isset( $all_plugins[ $plugin_id ]['Name'] ) ? $all_plugins[ $plugin_id ]['Name'] : $plugin_id;
						//$list[] = html( 'a', array( 'href' => '#' . sanitize_title( $name ) ), $name );
						$list[] = $name;
					}
					$name = implode( ' or ', $list );
				}

				$dep_list .= html( 'li', $name );
			}

			// now add the admin notice for any other deactivated plugins by PD
			add_action( $prefix . 'admin_notices', function() {
				echo html( 'div', array( 'class' => 'updated' ),
					html( 'p', '$text', html( 'ul', array( 'class' => 'dep-list' ), '$dep_list' ) )
				);
			} );
		}

		// Uninstall notice.
		if ( ! empty( $_REQUEST['uninstall'] ) ) {
			$prefix = is_network_admin() ? 'network_' : '';
			add_action( $prefix . 'admin_notices', function() {
				echo '<div class="updated"><p>' . __( 'Plugin uninstalled.', 'dollie-setup' ) . '</p></div>';
			} );
		}

		// Network-deactivate notice.
		if ( ! empty( $_REQUEST['network-deactivate'] ) ) {
			$prefix = is_network_admin() ? 'network_' : '';
			add_action( $prefix . 'admin_notices', function() {
				echo '<div class="updated"><p>' . __( 'Plugin network-deactivated.', 'dollie-setup' ) . '</p></div>';
			} );
		}
	}

	/**
	 * Renders the DOLLIE_SETUP plugins page.
	 *
	 * @since 0.3
	 */
	public function admin_page() {
		dollie_setup_get_template_part('wrapper-header');
		// show this page during update
		$is_update = isset( dollie_setup()->update ) ? dollie_setup()->update : false;
		if ( $is_update ) {
			$this->update_screen();
		}

		// if upgrade process is finished, show regular plugins page
		else {
			$type = ! empty( $_GET['type'] ) ? $_GET['type'] : '';
			$url  = self_admin_url( 'admin.php?page=dollie_setup-plugins' );
			$url  = ! empty( $type ) ? add_query_arg( 'type', esc_attr( $_GET['type'] ), $url ) : $url;

			$plugin_types = array(
				'required' => array(
					'label'      => dollie_setup_get_string( 'tab_plugin_required' ),
					'submit_btn' => __( 'Update', 'dollie-setup' )
				)
			);
			if ( CBox_Plugins::get_plugins( 'optional' ) ) {
				$plugin_types['optional'] = array(
					'label'      => dollie_setup_get_string( 'tab_plugin_optional' ),
					'submit_btn' => __( 'Activate', 'dollie-setup' )
				);
			}
			if ( CBox_Plugins::get_plugins( 'install-only' ) ) {
				$plugin_types['install-only'] = array(
					'label'      => dollie_setup_get_string( 'tab_plugin_install' ),
					'submit_btn' => __( 'Update', 'dollie-setup' )
				);
			}
	?>
			<div class="wrap dollie_setup-admin-wrap">
				<h2><?php printf( __( '%1$s Plugins: %2$s', 'dollie-setup' ), dollie_setup_get_package_prop( 'name' ), $plugin_types[ '' === $type ? 'required' : $type ]['label'] ); ?></h2>

				<h2 class="nav-tab-wrapper wp-clearfix">
					<?php foreach ( $plugin_types as $plugin_type => $data ) : ?>
						<a href="<?php echo 'required' === $plugin_type ? remove_query_arg( 'type', $url ) : add_query_arg( 'type', $plugin_type, $url ); ?>" class="nav-tab<?php echo $plugin_type === $type || ( '' === $type && 'required' === $plugin_type ) ? ' nav-tab-active' : ''; ?>"><?php echo esc_html( $data['label'] ); ?></a>
					<?php endforeach; ?>
				</h2>

				<div class="dollie_setup-admin-content dollie_setup-plugins-content">

				<form method="post" action="<?php echo $url; ?>">
					<?php if ( '' === $type ) { $type = 'required'; } ?>
					<?php if ( ! empty( $plugin_types[ $type ] ) ) : ?>

						<div id="<?php echo esc_attr( $type ); ?>" class="dollie_setup-plugins-section">
							<h2><?php echo esc_html( $plugin_types[ $type ]['label']  ); ?></h2>

							<?php dollie_setup_get_template_part( "plugins-{$type}-header" ); ?>

							<?php self::render_plugin_table( array( 'type' => $type, 'submit_btn_text' => $plugin_types[ $type ]['submit_btn'] ) ); ?>
						</div>

						<?php if ( 'required' === $type && CBox_Plugins::get_plugins( 'recommended' ) ) : ?>

							<div id="recommended" class="dollie_setup-plugins-section">
								<?php dollie_setup_get_template_part( 'plugins-recommended-header' ); ?>

								<?php self::render_plugin_table( 'type=recommended' ); ?>
							</div>

						<?php endif; ?>

						<?php if ( 'install-only' === $type ) : ?>

							<div class="prompt" style="display:none">
								<p><?php esc_html_e( 'This plugin might be active on other member sites.  If so, removing the plugin will remove this functionality on those sites.', 'dollie-setup' ); ?></p>
								<p><?php esc_html_e( 'Are you sure you want to continue uninstalling?', 'dollie-setup' ); ?>
							</div>

							<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.1.1/jquery-confirm.min.css">
							<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.1.1/jquery-confirm.min.js"></script>

							<script>
jQuery('a[data-uninstall="1"]').confirm({
	type: 'red',
	content: function() {
		// Set modal title.
		this.setTitle( this.$target.attr( 'title' ) );

		// Set modal content.
		return jQuery( '.prompt' )[0].innerHTML;
	},
	title: function() {},
	boxWidth: '500px',
	useBootstrap: false,
	bgOpacity: 0.7,
	buttons: {
	        no: {
			text: '<?php esc_attr_e( 'No', 'dollie-setup' ); ?>',
			action: function() {}
		},
		yes: {
			text: '<?php esc_attr_e( 'Yes', 'dollie-setup' ); ?>',
			btnClass: 'btn-red',
			action: function () {
				location.href = this.$target.attr('href');
			}
		},
	}
});
							</script>

						<?php endif; ?>

					<?php endif; ?>

					<?php wp_nonce_field( 'dollie_setup_update' ); ?>
				</form>
				</div>

			</div>
	<?php
			dollie_setup_get_template_part('wrapper-footer');
		}
	}

	/**
	 * Screen that shows during an update.
	 *
	 * @since 0.2
	 */
	private function update_screen() {

		// if we're not in the middle of an update, stop now!
		$is_update = isset( dollie_setup()->update ) ? dollie_setup()->update : false;
		if ( ! $is_update )
			return;

		$plugins = $_REQUEST['dollie_setup_plugins'];

		// include the DOLLIE_SETUP Plugin Upgrade and Install API
		if ( ! class_exists( 'CBox_Plugin_Upgrader' ) )
			require( DOLLIE_SETUP_PLUGIN_DIR . 'admin/plugin-install.php' );

		// some HTML markup!
		echo '<div class="wrap">';
		echo '<h2>' . esc_html__('Update DOLLIE_SETUP', 'dollie-setup' ) . '</h2>';

		// start the upgrade!
		$installer = new CBox_Updater( $plugins );

		echo '</div>';
	}

	/**
	 * Inline CSS used on the DOLLIE_SETUP plugins page.
	 *
	 * @since 0.3
	 */
	public function inline_css() {
	?>
		<style type="text/css">
			.dep-list li {list-style:disc; margin-left:1.5em;}
		</style>
	<?php
	}


	/**
	 * Helper method to return the deactivation URL for a plugin on the DOLLIE_SETUP
	 * plugins page.
	 *
	 * @since 0.2
	 *
	 * @param str $loader The plugin's loader filename
	 * @return str Deactivation link
	 */
	public static function deactivate_link( $loader ) {
		return self_admin_url( 'admin.php?page=dollie_setup-plugins&amp;dollie_setup-action=deactivate&amp;plugin=' . urlencode( $loader ) . '&amp;_wpnonce=' . wp_create_nonce( 'deactivate-plugin_' . $loader ) );
	}

	/**
	 * Renders a plugin table for DOLLIE_SETUP's plugins.
	 *
	 * @since 0.3
	 *
	 * @param mixed $args Querystring or array of parameters. See inline doc for more details.
	 */
	public static function render_plugin_table( $args = '' ) {
		$defaults = array(
			'type'            => 'required', // 'required' (default), 'recommended', 'optional', 'dependency'
			'omit_activated'  => false,      // if set to true, this omits activated plugins from showing up in the plugin table
			'check_all'       => false,      // if set to true, this will mark all the checkboxes in the plugin table as checked
			'submit_btn_text' => __( 'Update', 'dollie-setup' )
		);

		$r = wp_parse_args( $args, $defaults );

		// get unfulfilled requirements for all plugins
		//$requirements = Plugin_Dependencies::get_requirements();
	?>

		<table class="fixed widefat plugins">
			<thead>
				<tr>
					<th scope="col" class="manage-column check-column"><label for="plugins-select-all-<?php echo esc_attr( $r['type'] ); ?>" class="screen-reader-text"><?php esc_html_e( 'Select all', 'dollie-setup' ); ?></label><input type="checkbox" id="plugins-select-all-<?php echo esc_attr( $r['type'] ); ?>" /></th>
					<th scope="col" id="<?php _e( $r['type'] ); ?>-name" class="manage-column column-name column-dollie_setup-plugin-name"><?php _e( 'Plugin', 'dollie-setup' ); ?></th>
					<th scope="col" id="<?php _e( $r['type'] ); ?>-description" class="manage-column column-description"><?php _e( 'Description', 'dollie-setup' ); ?></th>
				</tr>
			</thead>

			<tfoot>
				<tr>
					<th scope="col" class="manage-column check-column"><label for="plugins-select-all<?php echo esc_attr( $r['type'] ); ?>-2" class="screen-reader-text"><?php esc_html_e( 'Select all', 'dollie-setup' ); ?></label><input type="checkbox" id="plugins-select-all<?php echo esc_attr( $r['type'] ); ?>-2" /></th>
					<th scope="col" class="manage-column column-name column-dollie_setup-plugin-name"><?php _e( 'Plugin', 'dollie-setup' ); ?></th>
					<th scope="col" class="manage-column column-description"><?php _e( 'Description', 'dollie-setup' ); ?></th>
				</tr>
			</tfoot>

			<tbody>

			<?php
				foreach ( CBox_Plugins::get_plugins( $r['type'] ) as $plugin => $data ) :
					// attempt to get the plugin loader file
					$loader = Plugin_Dependencies::get_pluginloader_by_name( $plugin );
					$settings = self::get_settings();

					// get the required plugin's state
					$state  = self::get_plugin_state( $loader, $data );

					if ( $r['omit_activated'] && $state == 'deactivate' )
						continue;

					$css_class = 'activate' == $state && CBox_Plugins::is_plugin_type( $plugin, 'install-only' ) ? 'active' : '';
					$css_class = '' === $css_class ? $state == 'deactivate' ? 'active' : 'action-required' : $css_class;
			?>
				<tr id="<?php echo sanitize_title( $plugin ); ?>" class="dollie_setup-plugin-row-<?php echo $css_class; ?>">
					<th scope='row' class='check-column'>
						<?php if ( 'activate' == $state && CBox_Plugins::is_plugin_type( $plugin, 'install-only' ) ) : ?>
							<img src="<?php echo admin_url( 'images/yes.png' ); ?>" alt="" style="margin-left:7px;" /><span class="screen-reader-text"><?php esc_attr_e( 'Plugin is already installed', 'dollie-setup' ); ?></span>

						<?php elseif ( 'deactivate' !== $state ) : ?>
							<input title="<?php esc_attr_e( 'Check this box to install the plugin.', 'dollie-setup' ); ?>" type="checkbox" id="dollie_setup_plugins_<?php echo sanitize_title( $plugin ); ?>" name="dollie_setup_plugins[<?php echo $state; ?>][]" value="<?php echo esc_attr( $plugin ); ?>" <?php checked( $r['check_all'] ); ?>/><span class="screen-reader-text"><?php esc_attr_e( 'Check this box to install the plugin.', 'dollie-setup' ); ?></span>

						<?php else : ?>
							<img src="<?php echo admin_url( 'images/yes.png' ); ?>" alt="" title="<?php esc_attr_e( 'Plugin is already active!', 'dollie-setup' ); ?>" style="margin-left:7px;" /><span class="screen-reader-text"><?php esc_attr_e( 'Plugin is already active!', 'dollie-setup' ); ?></span>

						<?php endif; ?>
					</th>

					<td class="plugin-title">
						<?php if ( 'action-required' === $css_class ) : ?>
							<label for="dollie_setup_plugins_<?php echo sanitize_title( $plugin ); ?>">
						<?php endif; ?>

						<strong><?php echo $data['dollie_setup_name']; ?></strong>

						<?php if ( 'action-required' === $css_class ) : ?>
							</label>
						<?php endif; ?>

						<!-- start - plugin row links -->
						<?php
							$plugin_row_links = array();

							// settings link
							if ( ! empty( $settings[ $plugin ] ) ) {
								$plugin_row_links[] = sprintf(
									'<a title="%s" href="%s">%s</a>',
									__( "Click here to view this plugin's settings page", 'dollie-setup' ),
									$settings[ $plugin ],
									__( "Settings", 'dollie-setup' )
								);
							}

							// info link
							if ( ! empty( $data['documentation_url'] ) && $state != 'upgrade' ) {
								$plugin_row_links[] = sprintf(
									'<a title="%s" href="%s" target="_blank">%s</a>',
									__( "Click here for documentation on this plugin, from commonsinabox.org", 'dollie-setup' ),
									esc_url( $data['documentation_url'] ),
									__( "Info", 'dollie-setup' )
								);
							}

							// deactivate link - only show for non-required and non-install plugins.
							if ( 'deactivate' === $state && 'required' !== $r['type'] && 'install-only' !== $r['type'] ) {
								$plugin_row_links[] = sprintf(
									'<a title="%s" href="%s">%s</a>',
									__( "Deactivate this plugin.", 'dollie-setup' ),
									self::deactivate_link( $loader ),
									__( "Deactivate", 'dollie-setup' )
								);
							}

							// Uninstall - only for install-only plugins.
							if ( 'install-only' == $r['type'] && 'install' !== $state ) {
								$plugin_row_links[] = sprintf(
									'<a data-uninstall="1" title="%s" href="%s">%s</a>',
									sprintf( __( "Uninstall %s", 'dollie-setup' ), $plugin ),
									self_admin_url( 'admin.php?page=dollie_setup-plugins&amp;dollie_setup-action=uninstall&amp;plugin=' . urlencode( $plugin ) . '&amp;_wpnonce=' . wp_create_nonce( 'bulk-plugins' ) ),
									__( "Uninstall", 'dollie-setup' )
								);
							}
						?>

						<div class="row-actions-visible">
							<p><?php echo implode( ' | ', $plugin_row_links ); ?></p>

							<?php /* upgrade notice */ ?>
							<?php if ( $state == 'upgrade' ) : ?>
								<div class="plugin-card"><p class="update-now" title="<?php _e( "Select the checkbox and click on 'Update' to upgrade this plugin.", 'dollie-setup' ); ?>"><?php _e( 'Update available.', 'dollie-setup' ); ?></p></div>
							<?php endif; ?>
						</div>
						<!-- end - plugin row links -->
					</td>

					<td class="column-description desc">
						<div class="plugin-description">
							<p><?php echo $data['dollie_setup_description']; ?></p>

							<?php
								// parse dependencies if available
								// @todo this needs to reference PD's list instead of our internal one...
								if( $data['depends'] ) :
									$deps = array();

									echo '<p>';
									_e( 'Requires: ', 'dollie-setup' );

									foreach( Plugin_Dependencies::parse_field( $data['depends'] ) as $dependency ) :
										// a dependent name can contain a version number, so let's get just the name
										$plugin_name = rtrim( strtok( $dependency, '(' ) );

										$dep_str    = $dependency;
										$dep_loader = Plugin_Dependencies::get_pluginloader_by_name( $plugin_name );

										if ( $dep_loader && self::is_plugin_active( $dep_loader ) )
											$dep_str .= ' <span class="enabled">' . __( '(enabled)', 'dollie-setup' ) . '</span>';
										elseif( $dep_loader )
											$dep_str .= ' <span class="disabled">' . __( '(disabled)', 'dollie-setup' ) . '</span>';
										else
											$dep_str .= ' <span class="not-installed">' . sprintf( __( '(automatically installed with %s)', 'dollie-setup' ), $data['dollie_setup_name'] ) . '</span>';
										$deps[] = $dep_str;
									endforeach;

									echo implode( ', ', $deps ) . '</p>';
								endif;
							?>
						</div>

					</td>
				</tr>

				<?php
				// Notice for plugins that are activated network-wide, but shouldn't be.
				if ( false === $data['network'] && is_multisite() && is_plugin_active_for_network( $loader ) ) : ?>

					<tr class="dollie_setup-plugin-network-active">
						<td colspan="3"><div class="inline notice notice-error notice-alt"><p>
							<?php printf( esc_html__( '%1$s is network-activated, but we recommend only activating this plugin on the main site.', 'dollie-setup' ), $plugin ); ?>
							<?php printf(
								'<a href="%1$s">%2$s</a>',
								self_admin_url( 'admin.php?page=dollie_setup-plugins&amp;dollie_setup-action=network-deactivate&amp;plugin=' . urlencode( $plugin ) . '&amp;_wpnonce=' . wp_create_nonce( 'network-deactivate-plugin_' . $plugin ) ),
								esc_html__( '(Change)', 'dollie-setup' )
							); ?>
						</p></div></td>
					</tr>

				<?php endif; ?>

			<?php endforeach; ?>

			</tbody>
		</table>

		<?php if ( 'required' !== $r['type'] ) : ?>
			<p><input type="submit" value="<?php echo 'install-only' === $r['type'] ? esc_html( 'Install', 'dollie-setup' ) : $r['submit_btn_text']; ?>" class="button-primary" id="dollie_setup-update-<?php echo esc_attr( $r['type'] ); ?>" name="dollie_setup-update" /></p>
		<?php endif; ?>

	<?php
	}
}