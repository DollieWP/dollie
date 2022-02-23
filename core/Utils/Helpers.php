<?php

namespace Dollie\Core\Utils;

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Modules\AccessControl;
use Dollie\Core\Singleton;
use WP_Query;

use Dollie\Core\Modules\Sites\WP;
use Dollie\Core\Modules\Subscription\Subscription;
use Dollie\Core\Modules\Backups;
use Dollie\Core\Modules\Blueprints;
use Dollie\Core\Modules\Container;
use Dollie\Core\Modules\SiteInsights;
use Dollie\Core\Modules\ContainerBulkActions;
use Dollie\Core\Modules\Domain;

/**
 * Class Helpers
 *
 * @package Dollie\Core
 */
class Helpers extends Singleton
{

	/**
	 * Get current queried object data
	 *
	 * @param int $id
	 *
	 * @return \stdClass
	 */
	public function get_current_object($id = null)
	{
		if (isset($id) && $id > 0) {
			$object = get_post($id);
		} else {
			$object = get_queried_object();
		}

		$response = [
			'id'     => 0,
			'slug'   => '',
			'author' => 0,
		];

		if (!$object instanceof \WP_Post) {
			return (object) $response;
		}

		if (get_post_type($object->ID) !== 'container') {
			return (object) $response;
		}

		if (isset($object->ID)) {
			$response['id']     = $object->ID;
			$response['slug']   = $object->post_name;
			$response['author'] = $object->post_author;
		}

		return (object) $response;
	}

	/**
	 * An array of Supported Plugins
	 *
	 * @return array
	 */
	public function supported_plugins()
	{
		$supported_plugins = [
			'Cloudflare'       => [
				'reason' => esc_html__('We strongly recommend to keep this plugin active and installed at all times.', 'dollie'),
				'info'   => 'foo',
			],
			'WP Fastest Cache' => [
				'reason' => esc_html__('Our SiteCache solution is powered partly by the WP Fastest Cache plugin. We recommend to keep this plugin activated.', 'dollie'),
				'info'   => 'foo',
			],
		];

		return $supported_plugins;
	}

	/**
	 * An array of Unsupported Plugins
	 *
	 * @return array
	 */
	public function unsupported_plugins()
	{
		return [];
	}

	/**
	 * Get container URL
	 *
	 * @param null $container_id
	 * @param bool $temp_url
	 *
	 * @return string
	 */
	public function get_container_url($container_id = null, $temp_url = false)
	{
		if (null === $container_id) {
			$container_id = $this->get_current_object()->id;
		}

		$domain              = get_post_meta($container_id, 'wpd_domains', true);
		$domain_url_migrated = get_post_meta($container_id, 'wpd_domain_migration_complete', true);

		if ('' !== $domain && 'yes' === $domain_url_migrated && !$temp_url) {
			$install = 'https://' . get_post_meta($container_id, 'wpd_domains', true);
		} else {
			$install = dollie()->get_wp_site_data('uri', $container_id);
		}

		return strtolower($install);
	}

	/**
	 * Get container staging url
	 *
	 * @param null $container_id
	 * @param boolean $temp_url
	 *
	 * @return string
	 */
	public function get_container_staging_url($container_id = null, $temp_url = false)
	{
		if (null === $container_id) {
			$container_id = $this->get_current_object()->id;
		}

		$staging_url = get_post_meta($container_id, '_wpd_staging_url', true);

		if ($staging_url) {
			$staging_url = 'https://' . $staging_url;
		}

		return strtolower($staging_url);
	}

	/**
	 * Get container status
	 *
	 * @param $container_id
	 *
	 * @return mixed
	 */
	public function get_container_status($container_id)
	{
		return Container::instance()->get_status($container_id);
	}

	/**
	 * Get login url for container
	 *
	 * @param null $container_id
	 * @param null $container_location
	 * @param boolean $staging
	 *
	 * @return string
	 */
	public function get_customer_login_url($container_id = null, $container_location = null, $staging = false)
	{
		$container  = dollie()->get_current_object($container_id);
		$url_params = 'site_login_redirect?site=' . $container->id;

		if ($staging) {
			$url_params .= '&staging=1';
		}

		$url = home_url($url_params);

		if (!empty($container_location)) {
			$url = add_query_arg('location', $container_location, $url);
		}

		return wp_nonce_url($url, 'get_site_login', '_nonce');
	}

	/**
	 * Customer login
	 *
	 * @param null $container_id
	 * @param null $container_location
	 * @param boolean $staging
	 *
	 * @return string
	 */
	public function final_customer_login_url($container_id = null, $container_location = null, $staging = false)
	{
		$container = $this->get_current_object($container_id);

		if ($staging) {
			$container_url = $this->get_container_staging_url($container->id);
		} else {
			$container_url = $this->get_container_url($container->id);
		}

		if (empty($container_url)) {
			return '';
		}

		$url                 = $container_url . '/wp-login.php';
		$pending_role_action = get_post_meta($container->id, '_wpd_user_role_change_pending', true);

		// User role change is not yet complete.
		if ($pending_role_action) {
			return '';
		}

		if (null !== $container_location) {
			$location = '&location=' . $container_location;
		} else {
			$location = '';
		}

		// Generate different login links based on user access control.
		$client_user_id = get_post_field('post_author', $container->id);
		$user_role      = dollie()->get_customer_user_role($client_user_id);

		// If we are admin and visiting client site.
		if ('administrator' !== $user_role && current_user_can('manage_options')) {
			$username = get_option('options_wpd_admin_user_name');
		} else {
			$username = Container::instance()->get_customer_username($container->id);
		}

		$token_details = Container::instance()->get_login_token($container_url, $container->id, $username, $staging);

		if (empty($token_details)) {
			return $url;
		}

		if (is_object($token_details) && isset($token_details->Token) && $token_details->Token) {
			$url .= '?s5token=' . $token_details->Token . $location;
		} else {
			$details = Container::instance()->get_info($container->id);
			$url     .= '?s5token=' . $details->Token . $location;
		}

		return $url;
	}

	/**
	 * @return string
	 */
	public function get_customer_admin_url()
	{
		return $this->get_container_url() . '/wp-admin/';
	}

	/**
	 * @return string
	 */
	public function get_customer_secret_url()
	{
		$secret = get_post_meta($this->get_current_object()->id, 'wpd_container_secret', true);

		return $this->get_container_url() . '/' . $secret;
	}

	/**
	 * @return int
	 */
	public function get_container_id_by_string()
	{
		return get_page_by_path($_GET['site'], OBJECT, 'container')->ID;
	}

	/**
	 * @return mixed
	 */
	public function get_container_slug_by_string()
	{
		return $_GET['site'];
	}

	/**
	 * @param null $container_id
	 *
	 * @return string
	 */
	public function get_site_screenshot($container_id = null, $html = true)
	{
		$post_id = $this->get_current_object($container_id)->id;

		$deploying = 'pending' === \Dollie\Core\Modules\Container::instance()->get_status($post_id);

		$site  = $this->get_container_url($post_id);
		$image = DOLLIE_ASSETS_URL . 'img/generating-preview.png';

		if (!empty(get_field('default_screenshot', 'option'))) {
			$image = get_field('default_screenshot', 'option');
		}

		if (!$deploying && !empty($site)) {
			$screenshot = get_transient('wpd_container_ss_' . $post_id);

			if (!is_array($screenshot) || empty($screenshot) || !isset($screenshot['desktop']) || !$screenshot['desktop']) {
				$screenshot = $this->container_screenshot($site);
			}

			if (is_array($screenshot) && !empty($screenshot) && isset($screenshot['desktop']) && $screenshot['desktop']) {
				$image = $screenshot['desktop'] . '?ver=' . current_time('timestamp');

				set_transient('wpd_container_ss_' . $post_id, $screenshot, MINUTE_IN_SECONDS * 60);
			}
		}

		if (!$html) {
			return $image;
		}

		//Resize image and store locally
		$local_image = wpthumb($image, 'width=700&height=99999&crop=0');

		$image_tag = '<img width="700" class="dol-block dol-object-cover" alt="' . esc_attr($site) . '" src="' . $local_image . '" />';

		if ($deploying) {
			return $image_tag;
		}

		$screenshot = '<a class="dol-block dol-leading-none" target ="_blank" href="' . esc_url($site) . '">' . $image_tag . '</a>';
		update_post_meta($post_id, 'wpd_site_screenshot', $image);

		return $screenshot;
	}

	/**
	 * @param null $container_id
	 */
	public function flush_container_details($container_id = null)
	{
		$container = $this->get_current_object($container_id);

		delete_transient('dollie_container_api_request_' . $container->slug . '_get_container_wp_info');
		delete_transient('dollie_container_api_request_' . $container->slug . '_get_container_site_info');
		delete_transient('dollie_site_users_' . $container->slug);
		delete_transient('dollie_site_news_' . $container->slug);
		delete_transient('dollie_site_new_screenshot_' . $this->get_container_url($container_id));

		$this->regenerate_containers_screenshot([$this->get_container_url($container_id)]);
	}

	/**
	 * @return bool
	 */
	public function get_latest_container_url()
	{
		$query = new WP_Query(
			[
				'post_status'    => ['publish', 'draft'],
				'author'         => get_current_user_id(),
				'post_type'      => 'container',
				'posts_per_page' => 1,
			]
		);

		$output = '';

		while ($query->have_posts()) {
			$query->the_post();
			$output = get_the_permalink();
		}

		wp_reset_postdata();

		return $output ?: false;
	}

	/**
	 * @param $length
	 * @param string $keyspace
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function random_string($length, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
	{
		$str = '';
		$max = mb_strlen($keyspace, '8bit') - 1;
		for ($i = 0; $i < $length; ++$i) {
			$str .= $keyspace[random_int(0, $max)];
		}

		return $str;
	}

	/**
	 * @return string
	 */
	public function secret_admin_key()
	{
		return '?' . dollie()->random_string(12);
	}

	/**
	 * @param $size
	 *
	 * @return string
	 */
	public function convert_to_readable_size($size)
	{
		if (!$size) {
			return $size;
		}

		$base   = log($size) / log(1024);
		$suffix = ['', 'KB', 'MB', 'GB', 'TB'];
		$f_base = floor($base);

		return round(1024 ** ($base - floor($base)), 1) . $suffix[$f_base];
	}

	/**
	 * @param $size
	 *
	 * @return string
	 * @deprecated use convert_to_readable_size()
	 * @uses convert_to_readable_size()
	 */
	public function convertToReadableSize($size)
	{
		return $this->convert_to_readable_size($size);
	}

	/**
	 * @return int
	 */
	public function count_customer_containers($user_id = null)
	{
		if (!$user_id) {
			$user_id = get_current_user_id();
		}

		$query = new WP_Query(
			[
				'author'        => $user_id,
				'post_type'     => 'container',
				'post_per_page' => 1000,
				'post_status'   => 'publish',
			]
		);

		$total = $query->found_posts;

		wp_reset_postdata();

		return $total;
	}

	/**
	 * @return int
	 */
	public function count_total_containers()
	{
		$query = new WP_Query(
			[
				'post_type'     => 'container',
				'post_per_page' => 1000,
			]
		);

		$total = $query->found_posts;

		wp_reset_postdata();

		return $total;
	}

	/**
	 * @return int
	 */
	public function has_products($type = null)
	{
		$args = array(
			"post_type" => 'product',
			"status" => "publish",
			"meta_query" => array(
				array(
					'key'     => 'wpd_',
					'compare_key' => 'LIKE',
				),
			),
		);

		$product_arg = apply_filters('dollie_product_query', $args);

		$query = new \WP_Query($product_arg);

		if ($query->have_posts()) {
			if (!$type) {
				return true;
			} else {
				return $query;
			}
		}
	}

	/**
	 * @return int
	 */
	public function get_products()
	{
		//Return the Query
		$query = $this->has_products(1);
		return $query;
	}

	/**
	 * @return int
	 */
	public function count_total_blueprints()
	{
		$query = new WP_Query(
			[
				'post_type'     => 'container',
				'post_per_page' => 1000,
				'meta_query'    => [
					'relation' => 'AND',
					[
						'key'   => 'wpd_blueprint_created',
						'value' => 'yes',
					],
					[
						'key'     => 'wpd_installation_blueprint_title',
						'compare' => 'EXISTS',
					],
				],
			]
		);

		$total = $query->found_posts;

		wp_reset_postdata();

		return $total;
	}

	/**
	 * @return int
	 */
	public function count_customer_staging_sites($user_id = null)
	{
		if (!$user_id) {
			$user_id = get_current_user_id();
		}

		$query = new WP_Query(
			[
				'author'        => $user_id,
				'post_type'     => 'container',
				'post_per_page' => -1,
				'post_status'   => 'publish',
				'meta_query'    => [
					[
						'key'   => 'wpd_has_staging',
						'value' => 'yes',
					],
				],
			]
		);

		$total = $query->found_posts;

		wp_reset_postdata();

		return $total;
	}

	/**
	 * @return bool
	 */
	public function is_live()
	{
		return (bool) get_option('options_wpd_api_domain') && (bool) \Dollie\Core\Utils\Api::get_auth_token();
	}

	/**
	 * @return bool
	 */
	public function is_hosted()
	{
		if ( defined('S5_APP_ID') ) {
			return true;
		}
	}

	/**
	 * @return mixed|void
	 */
	public function get_launch_page_id()
	{
		if (function_exists('pll_get_post')) {
			return (int) pll_get_post(get_option('options_wpd_launch_page_id'));
		} else {
			return (int) get_option('options_wpd_launch_page_id');
		}
	}

	/**
	 * @return false|string
	 */
	public function get_launch_page_url()
	{
		return get_permalink($this->get_launch_page_id());
	}

	/**
	 * @return false|string
	 */
	public function get_launch_page_title()
	{
		return get_the_title($this->get_launch_page_id());
	}

	/**
	 * @return mixed|void
	 */
	public function get_launch_blueprint_page_id()
	{
		if (function_exists('pll_get_post')) {
			return (int) pll_get_post(get_option('options_wpd_launch_blueprint_page_id'));
		} else {
			return (int) get_option('options_wpd_launch_blueprint_page_id');
		}
	}

	/**
	 * @return false|string
	 */
	public function get_launch_blueprint_page_url()
	{
		return get_permalink($this->get_launch_blueprint_page_id());
	}

	/**
	 * @return false|string
	 */
	public function get_launch_blueprint_page_title()
	{
		return get_the_title($this->get_launch_blueprint_page_id());
	}

	/**
	 * @return mixed|void
	 */
	public function get_dashboard_page_id()
	{
		if (function_exists('pll_get_post')) {
			return (int) pll_get_post(get_option('options_wpd_dashboard_page_id'));
		} else {
			return (int) get_option('options_wpd_dashboard_page_id');
		}
	}

	/**
	 * @return false|string
	 */
	public function get_dashboard_page_url()
	{
		return get_permalink($this->get_dashboard_page_id());
	}

	/**
	 * @return false|string
	 */
	public function get_dashboard_page_title()
	{
		return get_the_title($this->get_dashboard_page_id());
	}

	/**
	 * @return mixed|void
	 */
	public function get_login_page_id()
	{
		if (function_exists('pll_get_post')) {
			return (int) pll_get_post(get_option('options_wpd_login_page_id'));
		} else {
			return (int) get_option('options_wpd_login_page_id');
		}
	}

	public function get_login_page_url()
	{
		return get_permalink($this->get_login_page_id());
	}

	/**
	 * @return false|string
	 */
	public function get_login_page_title()
	{
		return get_the_title($this->get_login_page_id());
	}

	/**
	 * @return mixed|void
	 */
	public function get_sites_page_id()
	{
		if (function_exists('pll_get_post')) {
			return (int) pll_get_post(get_option('options_wpd_sites_page_id'));
		} else {
			return (int) get_option('options_wpd_sites_page_id');
		}
	}

	/**
	 * @return false|string
	 */
	public function get_sites_page_url()
	{
		return get_permalink($this->get_sites_page_id());
	}

	/**
	 * @return false|string
	 */
	public function get_sites_page_title()
	{
		return get_the_title($this->get_sites_page_id());
	}

	/**
	 * @return mixed|void
	 */
	public function get_site_template_id()
	{
		return (int) get_option('options_wpd_site_template_id');
	}

	/**
	 * @param $site_id
	 * @param string $page
	 *
	 * @return string
	 */
	public function get_site_url($site_id, $page = '')
	{
		$site = get_post($site_id);

		if (!$site) {
			return '';
		}

		return $page ? get_the_permalink($site_id) . trailingslashit($page) : get_the_permalink($site_id);
	}

	/**
	 *
	 */
	public function could_not_connect_message()
	{
?>
		<div class="dol-border dol-border-solid dol-border-primary-100 dol-rounded dol-overflow-hidden">
			<div class="dol-flex dol-items-center dol-bg-red-600">
				<div class="dol-p-4 lg:dol-px-8 lg:dol-py-4 dol-bg-red-700 dol-flex dol-items-center dol-justify-center">
					<?php echo dollie()->get_icon_alert('dol-text-white dol-text-2xl'); ?>
				</div>
				<h4 class="dol-px-4 lg:dol-px-8 lg:dol-py-4 dol-m-0 dol-p-0 dol-text-white dol-text-base md:dol-text-xl">
					<?php esc_html_e('Sorry, we could not retrieve your site details', 'dollie'); ?>
				</h4>
			</div>
			<div class="dol-px-4 dol-py-2 lg:dol-px-8 lg:dol-py-6 dol-bg-gray-100">
				<div class="dol-mb-4">
					<?php esc_html_e('We could not connect to your site to retrieve its details. This is usually caused by your WordPress site being unavailable or having a site-breaking error.', 'dollie'); ?>
				</div>

				<div>
					<a href="<?php echo esc_url(get_permalink() . '?get-details'); ?>" class="dol-text-sm dol-text-white hover:dol-text-white dol-inline-block dol-px-4 dol-py-2 dol-bg-gray-800 hover:dol-bg-gray-900 dol-rounded">
						<?php esc_html_e('Retry', 'dollie'); ?>
					</a>

					<a href="<?php echo esc_url(get_site_url() . '/support'); ?>" class="dol-text-sm dol-text-white hover:dol-text-white dol-inline-block dol-px-4 dol-py-2 dol-bg-gray-500 hover:dol-bg-gray-600 dol-rounded">
						<?php esc_html_e('Create a support ticket', 'dollie'); ?>
					</a>
				</div>
			</div>
		</div>
<?php
	}

	/**
	 * Get all registered settings groups by name and key
	 *
	 * @return array
	 */
	public function acf_get_database_field_group_keys()
	{
		$keys   = [];
		$groups = acf_get_field_groups();

		foreach ($groups as $group) {
			$keys[$group['title']] = $group['key'];
		}

		return $keys;
	}

	/**
	 * Get customer total backups
	 *
	 * @return mixed
	 * @deprecated
	 *
	 * @use get_site_total_backups
	 */
	public function get_customer_total_backups()
	{
		return Backups::instance()->count();
	}

	/**
	 * Get site total backups
	 *
	 * @param null $container_id
	 *
	 * @return mixed
	 */
	public function get_site_total_backups($container_id = null)
	{
		return Backups::instance()->count($container_id);
	}

	public function has_site_backup_notice($container_id = null)
	{
		$notice = get_post_meta($container_id, 'wpd_installation_backups_outdated', true);

		return $notice === 'yes';
	}

	/**
	 * Get restored sites
	 *
	 * @return mixed
	 */
	public function get_site_restores()
	{
		return Backups::instance()->get_site_restores();
	}

	/**
	 * Get available blueprints
	 *
	 * @param null $container_id
	 *
	 * @return mixed
	 */
	public function get_available_blueprints($container_id = null)
	{
		return Blueprints::instance()->get_available($container_id);
	}

	/**
	 * Return subscription name
	 *
	 * @return string
	 */
	public function subscription_name()
	{
		return Subscription::instance()->subscription_name();
	}

	/**
	 * Return sites available to install
	 *
	 * @return integer
	 */
	public function sites_available()
	{
		return Subscription::instance()->sites_available();
	}

	/**
	 * Return storage available based on the subscription
	 *
	 * @return string
	 */
	public function storage_available()
	{
		return Subscription::instance()->storage_available();
	}

	/**
	 * @return mixed
	 */
	public function size_limit_reached()
	{
		return Subscription::instance()->size_limit_reached();
	}

	/**
	 * @return mixed
	 */
	public function site_limit_reached()
	{
		return Subscription::instance()->site_limit_reached();
	}

	/**
	 * @return bool
	 */
	public function staging_limit_reached()
	{
		return $this->count_total_containers() >= 3 && !$this->is_live();
	}

	/**
	 * @return mixed
	 */
	public function has_subscription()
	{
		return Subscription::instance()->has_subscription();
	}

	/**
	 * @return mixed
	 */
	public function has_staging()
	{
		return Subscription::instance()->has_staging();
	}

	/**
	 * @return mixed
	 */
	public function staging_sites_limit_reached()
	{
		return Subscription::instance()->staging_sites_limit_reached();
	}

	/**
	 * @return boolean
	 */
	public function has_partner_subscription()
	{
		return Subscription::instance()->has_partner_subscription();
	}

	/**
	 * @return boolean
	 */
	public function has_partner_credits()
	{
		return Subscription::instance()->has_partner_credits();
	}

	/**
	 * @return boolean
	 */
	public function is_partner_subscription_trial()
	{
		return Subscription::instance()->is_partner_subscription_trial();
	}

	/**
	 * @return int
	 */
	public function get_partner_subscription_credits()
	{
		return Subscription::instance()->get_partner_subscription_credits();
	}

	/**
	 * @param int $user_id
	 *
	 * @return mixed
	 */
	public function has_bought_product($user_id = 0)
	{
		return Subscription::instance()->has_bought_product($user_id);
	}

	/**
	 * @param null $container_id
	 *
	 * @return mixed
	 */
	public function get_customer_container_details($container_id = null, $force = false)
	{
		return Container::instance()->get_customer_details($container_id, $force);
	}

	/**
	 * @param $url
	 * @param $transient_id
	 * @param $user_auth
	 * @param null $user_pass
	 * @param bool $force
	 *
	 * @return mixed
	 */
	public function container_api_request($url, $transient_id, $user_auth, $user_pass = null, $force = false)
	{
		return Container::instance()->do_api_request($url, $transient_id, $user_auth, $user_pass, $force);
	}

	/**
	 * Get container screenshot
	 *
	 * @param $container_uri
	 * @param bool $regenerate
	 *
	 * @return mixed
	 */
	public function container_screenshot($container_uri, $regenerate = false)
	{
		return Container::instance()->get_screenshot($container_uri, $regenerate);
	}

	/**
	 * Regenerate screenshot
	 *
	 * @param array $containers
	 *
	 * @return array
	 */
	public function regenerate_containers_screenshot($containers = [])
	{
		return Container::instance()->regenerate_screenshots($containers);
	}

	/**
	 * Get plugins
	 *
	 * @param string $container_uri
	 *
	 * @return bool|array
	 */
	public function get_container_plugins($container_uri = null)
	{
		return Container::instance()->get_plugins($container_uri);
	}

	/**
	 * Get plugins bulk
	 *
	 * @param string $containers
	 *
	 * @return bool|array
	 */
	public function get_containers_plugins($containers)
	{
		return Container::instance()->get_plugins_bulk($containers);
	}

	/**
	 * Get themes
	 *
	 * @param string $container_uri
	 *
	 * @return bool|array
	 */
	public function get_container_themes($container_uri = null)
	{
		return Container::instance()->get_themes($container_uri);
	}

	/**
	 * Get themes bulk
	 *
	 * @param string $containers
	 *
	 * @return bool|array
	 */
	public function get_containers_themes($containers)
	{
		return Container::instance()->get_themes_bulk($containers);
	}

	/**
	 * Get allowed bulk commands
	 *
	 * @return array
	 */
	public function get_allowed_bulk_commands()
	{
		return ContainerBulkActions::instance()->get_allowed_commands();
	}

	/**
	 * Get allowed bulk commands in progress
	 *
	 * @return array
	 */
	public function get_allowed_commands_in_progress()
	{
		return ContainerBulkActions::instance()->get_allowed_commands_in_progress();
	}

	/**
	 * Get total container size
	 *
	 * @return int
	 */
	public function get_total_container_size()
	{
		return SiteInsights::instance()->get_total_container_size();
	}

	/**
	 * @return mixed
	 */
	public function get_site_posts()
	{
		return SiteInsights::instance()->get_posts();
	}

	/**
	 * @return mixed
	 */
	public function get_dashboard_posts()
	{
		return SiteInsights::instance()->get_dashboard_news();
	}

	/**
	 * @return mixed
	 */
	public function get_dashboard_kb_articles()
	{
		return SiteInsights::instance()->get_kb_articles();
	}

	/**
	 * @return mixed
	 */
	public function get_latest_container_posts()
	{
		return SiteInsights::instance()->get_latest_container_posts();
	}

	/**
	 * @return mixed
	 */
	public function get_support_link()
	{
		return get_field('wpd_support_link', 'options');
	}

	/**
	 * Get site preview link.
	 *
	 * @param string $type url|path
	 *
	 * @return mixed|string
	 */
	public function get_preview_url($type = 'url')
	{
		$preview_path = get_option('options_wpd_site_preview_path', 'preview');

		if ('url' === $type) {
			return home_url($preview_path);
		}

		return $preview_path;
	}

	/**
	 * Get the link for quick checkout
	 *
	 * @param $product_id
	 * @param $blueprint_id
	 *
	 * @return mixed
	 */
	public function get_woo_checkout_link($product_id, $blueprint_id)
	{
		return Subscription::instance()->get_checkout_link(
			[
				'product_id'   => $product_id,
				'blueprint_id' => $blueprint_id,
			]
		);
	}

	/**
	 * Get customer user role
	 *
	 * @param null $user_id
	 *
	 * @return mixed|void
	 */
	public function get_customer_user_role($user_id = null)
	{
		$user_id = $user_id ?: get_current_user_id();
		$role    = get_user_meta($user_id, 'wpd_client_site_permissions', true);

		if (empty($role)) {
			$role = 'default';
		}

		if ('default' === $role) {
			if (user_can($user_id, 'manage_options')) {
				$role = 'administrator';
			} else {
				$role = get_field('wpd_client_site_permission', 'options');
			}
		}

		return $role ?: 'administrator';
	}

	public function get_user_type_string()
	{
		if ( ! function_exists('dollie_setup_get_string')) {
			return __('Customer', 'dollie-setup');
		}
		$package_string = dollie_setup_get_string('user_type');
		if ($package_string) {
			return dollie_setup_get_string('user_type');
		} else {
			return __('Customer', 'dollie-setup');
		}
	}

	public function get_user_type_plural_string()
	{

		if (!function_exists('dollie_setup_get_string')) {
			return __('Customers', 'dollie-setup');
		}
		if ( function_exists('dollie_setup_get_string')) {
			$package_string = dollie_setup_get_string('user_type_plural');
		}
		if ($package_string) {
			return dollie_setup_get_string('user_type_plural');
		} else {
			return __('Customers', 'dollie-setup');
		}
	}

	public function get_site_type_string()
	{
		if (!function_exists('dollie_setup_get_string')) {
			return __('Site', 'dollie-setup');
		}
		$package_string = dollie_setup_get_string('site_type');
		if ($package_string) {
			return dollie_setup_get_string('site_type');
		} else {
			return __('Site', 'dollie-setup');
		}
	}

	public function get_site_type_plural_string()
	{
		if (!function_exists('dollie_setup_get_string')) {
			return __('Sites', 'dollie-setup');
		}
		$package_string = dollie_setup_get_string('site_type_plural');
		if ($package_string) {
			return dollie_setup_get_string('site_type_plural');
		} else {
			return __('Sites', 'dollie-setup');
		}
	}

	public function get_icon_launch($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_launch', '<i class="fas fa-rocket"></i>') . '</span>';
	}

	public function get_icon_blueprint($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_blueprint', '<i class="fas fa-copy"></i>') . '</span>';
	}

	public function get_icon_staging($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_staging', '<i class="fas fa-clone"></i>') . '</span>';
	}

	public function get_icon_dev_tools($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_dev_tools', '<i class="fas fa-code"></i>') . '</span>';
	}

	public function get_icon_dev_details($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_dev_details', '<i class="fal fa-info"></i>') . '</span>';
	}

	public function get_icon_dev_code_editor($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_dev_code_editor', '<i class="fal fa-codiad"></i>') . '</span>';
	}

	public function get_icon_dev_home($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_dev_home', '<i class="fas fa-home"></i>') . '</span>';
	}

	public function get_icon_dev_sftp($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_dev_sftp', '<i class="fas fa-bookmark"></i>') . '</span>';
	}

	public function get_icon_dev_php($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_dev_php', '<i class="fas fa-tachometer"></i>') . '</span>';
	}

	public function get_icon_dev_database($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_dev_database', '<i class="fal fa-adminer"></i>') . '</span>';
	}

	public function get_icon_dev_cli($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_dev_cli', '<i class="fal fa-shell"></i>') . '</span>';
	}

	public function get_icon_plugins($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_plugins', '<i class="fas fa-plug"></i>') . '</span>';
	}

	public function get_icon_themes($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_themes', '<i class="fas fa-paint-roller"></i>') . '</span>';
	}

	public function get_icon_domains($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_domain', '<i class="fas fa-globe"></i>') . '</span>';
	}

	public function get_icon_backups($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_backups', '<i class="fas fa-history"></i>') . '</span>';
	}

	public function get_icon_migration($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_migration', '<i class="fas fa-truck-moving"></i>') . '</span>';
	}

	public function get_icon_delete($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_delete', '<i class="fas fa-trash-alt"></i>') . '</span>';
	}

	public function get_icon_manage($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_manage', '<i class="fas fa-cog"></i>') . '</span>';
	}

	public function get_icon_dashboard($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_woo_dashboard', '<i class="fas fa-dashboard"></i>') . '</span>';
	}

	public function get_icon_orders($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_orders', '<i class="fas fa-orders"></i>') . '</span>';
	}

	public function get_icon_my_subscription($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_subscriptions', '<i class="fas fa-subscriptions"></i>') . '</span>';
	}

	public function get_icon_downloads($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_downloads', '<i class="fas fa-downloads"></i>') . '</span>';
	}

	public function get_icon_address($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_address', '<i class="fas fa-edit-address"></i>') . '</span>';
	}

	public function get_icon_account_details($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_account_details', '<i class="fas fa-edit-account"></i>') . '</span>';
	}

	public function get_icon_logout($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_logout', '<i class="fas fa-sign-out-alt"></i>') . '</span>';
	}

	public function get_icon_filter($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_filter', '<i class="fas fa-filter"></i>') . '</span>';
	}

	public function get_icon_list($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_list', '<i class="fas fa-list"></i>') . '</span>';
	}

	public function get_icon_grid($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_grid', '<i class="fas fa-th"></i>') . '</span>';
	}

	public function get_icon_preview($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_preview', '<i class="fas fa-desktop"></i>') . '</span>';
	}

	public function get_icon_login($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_login', '<i class="fas fa-tools"></i>') . '</span>';
	}

	public function get_icon_site_view($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_site_view', '<i class="fas fa-external-link-alt"></i>') . '</span>';
	}

	public function get_icon_refresh($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_refresh', '<i class="fas fa-sync-alt"></i>') . '</span>';
	}

	public function get_icon_user_role($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_user_role', '<i class="fas fa-user-gear"></i>') . '</span>';
	}

	public function get_icon_dns($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_dns', '<i class="fas fa-atlas"></i>') . '</span>';
	}

	public function get_icon_site_login($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_site_login', '<i class="fas fa-user-shield"></i>') . '</span>';
	}

	public function get_icon_search($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_search', '<i class="fas fa-search"></i>') . '</span>';
	}

	public function get_icon_notifications($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_notifications', '<i class="fas fa-bell"></i>') . '</span>';
	}

	public function get_icon_close($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_close', '<i class="fas fa-times-circle"></i>') . '</span>';
	}

	public function get_icon_clock($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_clock', '<i class="fas fa-clock"></i>') . '</span>';
	}

	public function get_icon_save($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_save', '<i class="fas fa-save"></i>') . '</span>';
	}

	public function get_icon_task($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_task', '<i class="fas fa-tools"></i>') . '</span>';
	}

	public function get_icon_user($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_user', '<i class="fas fa-user-circle"></i>') . '</span>';
	}

	public function get_icon_users($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_users', '<i class="fas fa-users"></i>') . '</span>';
	}

	public function get_icon_site($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_site', '<i class="fas fa-columns"></i>') . '</span>';
	}

	public function get_icon_live_site($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_live_site', '<i class="fas fa-globe"></i>') . '</span>';
	}

	public function get_icon_dev_site($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_dev_site', '<i class="fas fa-cog"></i>') . '</span>';
	}

	public function get_icon_settings($classes = null)
	{
		return '<span class="dol-icon ' . $classes . '">' . apply_filters('dollie_icon_settings', '<i class="fas fa-cogs"></i>') . '</span>';
	}

	public function get_icon_wordpress($classes = null)
	{
		return '<span class="dol-icon ' . $classes . '">' . apply_filters('dollie_icon_wordpress', '<i class="fab fa-wordpress-simple"></i>') . '</span>';
	}

	public function get_icon_notice($classes = null)
	{
		return '<span class="dol-icon ' . $classes . '">' . apply_filters('dollie_icon_notice', '<i class="fas fa-info"></i>') . '</span>';
	}

	public function get_icon_comments($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_comments', '<i class="fas fa-comments"></i>') . '</span>';
	}

	public function get_icon_email($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_email', '<i class="fas fa-envelope"></i>') . '</span>';
	}

	public function get_icon_disk_space($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_disk_space', '<i class="fas fa-hdd"></i>') . '</span>';
	}

	public function get_icon_blog_post($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_blog_post', '<i class="fas fa-newspaper"></i>') . '</span>';
	}

	public function get_icon_read_more($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_read_more', '<i class="fas fa-long-arrow-right"></i>') . '</span>';
	}

	public function get_icon_updates($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_updates', '<i class="fas fa-download"></i>') . '</span>';
	}

	public function get_icon_version($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_version', '<i class="fas fa-php"></i>') . '</span>';
	}

	public function get_icon_arrow_right($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_arrow_right', '<i class="fas fa-long-arrow-right"></i>') . '</span>';
	}

	public function get_icon_arrow_left($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_arrow_left', '<i class="fas fa-long-arrow-left"></i>') . '</span>';
	}

	public function get_icon_arrow_down($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_arrow_down', '<i class="fas fa-long-arrow-down"></i>') . '</span>';
	}

	public function get_icon_logs($classes = null)
	{
		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_arrow_down', '<i class="fas fa-file-alt"></i>') . '</span>';
	}

	public function get_icon_arrow_up($classes = null)
	{
		return '<span class="dol-icon ' . $classes . '">' . apply_filters('dollie_icon_arrow_up', '<i class="fas fa-long-arrow-up"></i>') . '</span>';
	}

	public function get_icon_site_dashboard($classes = null)
	{
		return '<span class="dol-icon ' . $classes . '">' . apply_filters('dollie_icon_site_dashboard', '<i class="fas fa-columns"></i>') . '</span>';
	}

	public function get_icon_alert($classes = null)
	{

		return '<span class="dol-icon '. $classes.'">' . apply_filters('dollie_icon_arrow_down', '<i class="fas fa-exclamation-circle"></i>') . '</span>';
	}



	/**
	 * @param $needle
	 * @param $haystack
	 * @param bool $strict
	 *
	 * @return bool
	 */
	public function in_array_r($needle, $haystack, $strict = false)
	{
		if (!is_array($haystack)) {
			return false;
		}

		foreach ($haystack as $item) {
			if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && $this->in_array_r($needle, $item, $strict))) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param $array
	 * @param $key
	 * @param $value
	 *
	 * @return mixed
	 */
	public function remove_element_with_value($array, $key, $value)
	{
		foreach ($array as $subKey => $subArray) {
			if ($subArray[$key] === $value) {
				unset($array[$subKey]);
			}
		}

		return $array;
	}

	/**
	 * @param $bytes
	 *
	 * @return int|string
	 */
	public function format_size_units($bytes)
	{
		if ($bytes >= 1073741824) {
			$bytes = number_format($bytes / 1073741824, 2);
		} elseif ($bytes >= 1048576) {
			$bytes = '0.' . number_format($bytes / 1048576);
		} elseif ($bytes >= 1024) {
			$bytes = number_format($bytes / 1024, 2);
		} elseif ($bytes > 1 || 1 === $bytes) {
			return $bytes;
		} else {
			$bytes = '0 bytes';
		}

		return $bytes;
	}

	/**
	 * Get Elementor template types
	 *
	 * @return array
	 */
	public function get_elementor_template_types()
	{
		return [
			'container' => __('Site View', 'dollie'),
		];
	}

	/**
	 * @param $plugin_slug
	 *
	 * @return bool
	 */
	public function is_plugin_active($plugin_slug)
	{
		$active_plugins = apply_filters('active_plugins', get_option('active_plugins'));

		foreach ($active_plugins as $plugin) {
			if ($plugin === $plugin_slug) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param $plugin_path
	 *
	 * @return bool
	 */
	public function is_plugin_installed($plugin_path)
	{
		$plugins = get_plugins();

		return isset($plugins[$plugin_path]);
	}


	/**
	 * @param $plugin_path
	 *
	 * @return bool
	 */
	public function has_dollie_layout_widget()
	{
		$template_id = dollie()->get_site_template_id();
		$meta        = get_post_meta($template_id, '_elementor_data');

		foreach ($meta as $index => $string) {
			if (strpos($string, 'dollie-layout-') !== false) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param $id
	 *
	 * @return bool
	 */
	public function is_blueprint($id = null)
	{

		if ($id === null) {
			$id = get_the_ID();
		}

		return get_post_meta($id, 'wpd_is_blueprint', true) === 'yes';
	}

	/**
	 * @param $id
	 *
	 * @return bool
	 */
	public function is_blueprint_staging($id)
	{

		$blueprint = dollie()->is_blueprint($id);
		$updated   = get_post_meta($id, 'wpd_blueprint_time', true);

		if ($blueprint && $updated == '') {
			return true;
		}

		return false;
	}

	/**
	 * Check valid json.
	 *
	 * on check failure if $return_data is false, false is returned instead of the passed data
	 *
	 * @param $data
	 * @param bool $assoc
	 * @param bool $return_data
	 *
	 * @return array|bool
	 */
	public function maybe_decode_json($data, $assoc = false, $return_data = true)
	{

		$return = $return_data ? $data : false;

		if (is_array($data)) {
			return $data;
		}

		if (!is_string($data)) {
			return $return;
		}

		$data = json_decode($data, $assoc);

		if (is_array($data) && (json_last_error() === JSON_ERROR_NONE)) {
			return $data;
		}

		return $return;
	}

	/**
	 * @param $data
	 * @param null $container_id
	 *
	 * @return mixed
	 */
	public function get_wp_site_data($data, $container_id = null)
	{
		return WP::instance()->get_container_data($data, $container_id);
	}

	/**
	 * @param $domain
	 *
	 * @return false|int
	 */
	public function is_valid_domain($domain)
	{
		return preg_match('/^[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?\.[a-zA-Z]{2,}$/i', $domain);
	}

	/**
	 * Check if is preview
	 *
	 * @return boolean
	 */
	public function is_preview()
	{
		return get_query_var('dollie_route_name') === 'dollie_preview';
	}

	/**
	 * Get current site id
	 *
	 * @return int
	 */
	public function get_current_site_id()
	{
		$current_id = get_the_ID();
		if (!class_exists('\Elementor\Plugin')) {
			return $current_id;
		}

		$elementor_builder = \Elementor\Plugin::instance()->editor->is_edit_mode()
			|| \Elementor\Plugin::instance()->preview->is_preview()
			|| isset($_GET['elementor_library']);

		if ($elementor_builder) {

			$my_sites = get_posts(
				[
					'post_type'      => 'container',
					'author'         => get_current_user_id(),
					'posts_per_page' => 1,
				]
			);

			if (!empty($my_sites)) {
				$current_id = $my_sites[0]->ID;
			}
		}

		return $current_id;
	}

	/**
	 * Get containers data
	 *
	 * @param array $data
	 * @param string $with
	 *
	 * @return array
	 */
	public function get_containers_data($data, $with = 'post_id')
	{
		$args = [
			'post_type'      => 'container',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		];

		if ('post_id' === $with) {
			$ids = [];

			foreach ($data as $container) {
				$ids[] = (int) $container['id'];
			}

			$args['post__in'] = $ids;
		} elseif ('container_id' === $with) {
			$containers_ids = [];

			foreach ($data as $container) {
				$containers_ids[] = $container['container_id'];
			}

			$args['meta_query'] = [
				[
					'key'     => 'wpd_container_id',
					'value'   => $containers_ids,
					'compare' => 'IN',
				],
			];
		}

		if (!current_user_can('manage_options')) {
			$args['author'] = get_current_user_id();
		}

		$posts = new WP_Query($args);
		$posts = $posts->get_posts();

		wp_reset_postdata();

		return $posts;
	}

	/**
	 * Get api instance
	 *
	 * @return Api
	 */
	public function get_api()
	{
		return Api::instance();
	}

	/**
	 * Save execution
	 *
	 * @param int $container_id
	 * @param array $execution
	 *
	 * @return void
	 */
	public static function save_execution($container_id, $execution)
	{
		Api::save_execution($container_id, $execution);
	}

	/**
	 * Get execution status
	 *
	 * @param string $execution_id
	 * @param string $execution_type
	 *
	 * @return int|\WP_Error
	 */
	public function get_execution_status($execution_id, $execution_type = '')
	{
		return Api::get_execution_status($execution_id, $execution_type);
	}

	/**
	 * Get execution
	 *
	 * @param int $container_id
	 * @param string $execution_type
	 *
	 * @return null|string
	 */
	public function get_execution($container_id, $execution_type)
	{
		return Api::get_execution($container_id, $execution_type);
	}

	/**
	 * Remove execution
	 *
	 * @param int $container_id
	 * @param string $execution_type
	 *
	 * @return void
	 */
	public function remove_execution($container_id, $execution_type)
	{
		Api::remove_execution($container_id, $execution_type);
	}

	/**
	 * Get bulk actions
	 *
	 * @return array
	 */
	public function get_bulk_actions()
	{
		return ContainerBulkActions::instance()->get_bulk_actions();
	}

	/**
	 * Check bulk actions
	 *
	 * @return array
	 */
	public function check_bulk_actions()
	{
		return ContainerBulkActions::instance()->check_bulk_actions();
	}

	/**
	 * Load template
	 *
	 * @param string $template
	 * @param array $args
	 * @param boolean $echo
	 *
	 * @return void|string
	 */
	public function load_template($template, $args, $echo = false)
	{
		if ($echo) {
			Tpl::load($template, $args, $echo);
		} else {
			return Tpl::load($template, $args, $echo);
		}
	}

	/**
	 * Get domain allowed caa tags
	 *
	 * @return array
	 */
	public function get_domain_allowed_caa_tags()
	{
		return Domain::instance()->allowed_caa_tags();
	}

	/**
	 * Get domain records
	 *
	 * @param string $container_uri
	 *
	 * @return array|bool
	 */
	public function get_domain_records($container_uri)
	{
		return Domain::instance()->get_records($container_uri);
	}

	/**
	 * Get domain existing records
	 *
	 * @param string $domain
	 *
	 * @return array|bool
	 */
	public function get_domain_existing_records($domain)
	{
		return Domain::instance()->get_existing_records($domain);
	}

	public function can_view_all_sites($user_id = null)
	{
		return AccessControl::instance()->can_view_all_sites($user_id);
	}

	public function can_manage_all_sites($user_id = null)
	{
		return AccessControl::instance()->can_manage_all_sites($user_id);
	}

	public function can_delete_all_sites($user_id = null)
	{
		return AccessControl::instance()->can_delete_all_sites($user_id);
	}
}
