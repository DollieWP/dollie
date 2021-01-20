<?php
$status = \Dollie\Core\Modules\Container::instance()->get_status($current_id); ?>

<?php if ('stop' === $status) : ?>
	<?php $undeploy_at = get_post_meta($current_id, 'wpd_undeploy_container_at', true); ?>
	<div class="dol-bg-gray-200 dol-p-8 md:dol-p-10 lg:dol-p-20 dol-rounded">
		<h2 class="dol-text-xl md:dol-text-3xl dol-mb-4">
			<i class="far fa-pause-circle"></i>
			<?php esc_html_e('Hosting for this site is currently paused.', 'dollie'); ?>
		</h2>
		<div class="py-30">
			<p class="mt-5 mb-5 pl-100 pr-100 h5 font-size-large">
				<?php esc_html_e('If you have cancelled your service subscription or it has expired, please visit', 'dollie'); ?>
				<a href="<?php get_permalink(get_option('woocommerce_myaccount_page_id')); ?>" class="dol-font-bold"><?php esc_html_e('your subscription overview', 'dollie'); ?> </a>
				<?php printf(__('and resolve the issue before <strong>%s</strong> to prevent this site from being removed completely.', 'dollie'), date('F j, Y', $undeploy_at)); ?>
			</p>
			<p class="pl-100 pr-100">
				<?php esc_html_e('Stopped this site by mistake or think something went wrong? Please reach out to our team and
						we\'ll get back to you as soon as we can!', 'dollie'); ?>

				<a class="dol-font-700" href="<?php echo Dollie()->get_support_link(); ?>"><?php esc_html_e('Contact our Support Team', 'dollie'); ?> </a>
			</p>
		</div>
		<?php if (!dollie()->has_subscription()) : ?>

		<?php endif; ?>
	</div>
<?php elseif ($status === 'failed') : ?>
	<div class="dol-bg-gray-200 dol-p-8 md:dol-p-10 lg:dol-p-20 dol-rounded">
		<div class="text-center py-30">
			<?php if (current_user_can('manage_options')) : ?>
				<h2 class="dol-text-xl md:dol-text-3xl dol-mb-4">
					<i class="fas fa-exclamation-circle"></i>
					<?php esc_html_e('Admin Notice - This site has failed to launch', 'dollie'); ?>
				</h2>
				<p class="mt-5 mb-5 pl-100 pr-100 h5 font-size-large">
					When a site fails to deploy it usually means there is a misconfiguration in your
					Dollie settings or a conflict with another plugin or your theme.
					<a class="dol-font-700" href="<?php echo admin_url('wp-admin/edit.php?post_type=dollie-logs'); ?>">Click here to check the Dollie Logs</a>.
					<br><br>Finally you can also or reach out to the Dollie Support
					team via your <a class="dol-font-700 dol-text-dark" href="https://partners.getdollie.com/?redirect=support">Partner
						Dashboard</a>
				</p>
			<?php else : ?>
				<h2 class="dol-text-xl md:dol-text-3xl dol-mb-4">
					<i class="fas fa-exclamation-circle"></i>
					<?php esc_html_e('Sorry, there was an error launching your site.', 'dollie'); ?>
				</h2>
				<p class="mt-5 mb-5 pl-100 pr-100 h5 font-size-large">
					<?php esc_html_e('It seems like we were unable to launch your new site on our platform at this time. Our team has been notified but please reach out to us if the issue persists.', 'dollie'); ?>
					<br><br>
					<a class="dol-font-700" href="<?php echo Dollie()->get_support_link(); ?>"><?php esc_html_e('Contact our Support Team', 'dollie'); ?></a>
				</p>
			<?php endif; ?>
		</div>
	</div>
<?php elseif ('pending' === $status) : ?>

	<?php
	if (!\Elementor\Plugin::instance()->editor->is_edit_mode()) {
		wp_enqueue_script('dollie-site-content');
	}
	?>

	<div id="dol-deploying-site" class="dol-hidden" data-container="<?php echo esc_attr($current_id); ?>" data-nonce="<?php echo esc_attr(wp_create_nonce('check_deploy_nonce')); ?>" data-ajax-url="<?php echo esc_attr(admin_url('admin-ajax.php')); ?>"></div>
	<div class="dol-py-32 dol-flex dol-flex-col dol-items-center dol-justify-center">
		<svg class="dol-animate-spin dol-h-16 dol-w-16 dol-text-primary-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
			<circle class="dol-opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
			<path class="dol-opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
		</svg>

		<div class="dol-font-bold dol-mt-6 dol-text-2xl dol-uppercase">
			<?php esc_html_e('Launching Your New Site', 'dollie'); ?>
		</div>

		<div class="dol-mt-1 dol-text-md dol-font-semibold dol-text-gray-500">
			<?php esc_html_e('Your site will ready to use in just a moment.', 'dollie'); ?>
		</div>

		<div class="dol-my-6 dol-w-16 dol-h-1 dol-bg-gray-200"></div>

		<div class="dol-text-gray-400 dol-text-sm">
			<?php esc_html_e('Don\'t worry, we\'ll automatically reload this page once it\'s ready.', 'dollie'); ?>
		</div>
		<div class="dol-text-gray-400 dol-text-sm">
			<?php esc_html_e('If you don\'t want to wait for a few seconds, you can navigate away. We got it covered!', 'dollie'); ?>
		</div>
	</div>
<?php else : ?>

<?php

	$post_id = $current_id;
	$install = get_post($post_id)->post_name;

	// Include ACF editor for blueprints
	if (get_query_var('blueprints') || current_user_can('edit_pages')) {
		acf_form_head();
	}

	$data     = \Dollie\Core\Modules\Container::instance()->get_container_details($current_id);
	$sub_page = get_query_var('sub_page');

	if ('plugins' === $sub_page) {
		\Dollie\Core\Utils\Tpl::load(
			'widgets/site/pages/plugins',
			[
				'data'    => $data,
				'install' => $install,
			],
			true
		);
	} elseif ('themes' === $sub_page) {
		\Dollie\Core\Utils\Tpl::load(
			'widgets/site/pages/themes',
			[
				'data' => $data,
			],
			true
		);
	} elseif ('domains' === $sub_page) {
		\Dollie\Core\Utils\Tpl::load(
			'widgets/site/pages/domains',
			[
				'data' => $data,
			],
			true
		);
	} elseif ('migrate' === $sub_page) {
		\Dollie\Core\Utils\Tpl::load(
			'widgets/site/pages/migrate',
			[
				'data' => $data,
			],
			true
		);
	} elseif ('backups' === $sub_page) {
		\Dollie\Core\Utils\Tpl::load(
			'widgets/site/pages/backups',
			[
				'data' => $data,
			],
			true
		);
	} elseif ('updates' === $sub_page) {
		\Dollie\Core\Utils\Tpl::load(
			'widgets/site/pages/updates',
			[
				'data' => $data,
			],
			true
		);
	} elseif ('developer-tools' === $sub_page) {
		\Dollie\Core\Utils\Tpl::load(
			'widgets/site/pages/developer-tools',
			[
				'data' => $data,
			],
			true
		);
	} elseif ('blueprints' === $sub_page) {
		\Dollie\Core\Utils\Tpl::load(
			'widgets/site/pages/blueprints',
			[
				'data'    => $data,
				'post_id' => $post_id,
			],
			true
		);
	} elseif ('delete' === $sub_page) {
		\Dollie\Core\Utils\Tpl::load(
			'widgets/site/pages/delete',
			[
				'data' => $data,
			],
			true
		);
	} else {
		\Dollie\Core\Utils\Tpl::load(
			'widgets/site/pages/dashboard',
			[
				'data'    => $data,
				'install' => $install,
				'post_id' => $post_id,
			],
			true
		);
	}

endif;
