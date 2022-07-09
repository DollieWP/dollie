<?php

$container = dollie()->get_container();

if ( is_wp_error( $container ) || ! $container->is_blueprint() || ! $container->user()->can_manage_options() ) {
	return;
}

$blueprint_time = $container->get_changes_update_time();
$acf_fields     = [];
$groups         = acf_get_field_groups();

foreach ( $groups as $group ) {
	$acf_fields[ $group['title'] ] = $group['key'];
}

?>

<h2 class="dol-text-gray-500 text-s dol-font-small dol-uppercase dol-tracking-wide dol-mb-5 dol-text-xl">
	<?php esc_html_e( 'Manage This Blueprint', 'dollie' ); ?>
</h2>

<?php if ( ! $blueprint_time ) : ?>
	<div class="dol-my-6">
		<?php ob_start(); ?>
		<div class="dol-mb-4">
			<p>
				<?php printf( __( 'Ready to make <strong>%s</strong> available as a Blueprint for your customers?', 'dollie' ), $container->get_url() ); ?>
			</p>
			<p>
				<?php esc_html_e( 'Important - Make sure that the installation is working properly before you take your Blueprint Live. Meaning all your plugins are configured, your theme is set up, and you have double-checked that no accidental sensitive data is included (like test user accounts, private API keys etc.)', 'dollie' ); ?>
			</p>
		</div>

		<div>
			<?php
			acf_form(
				[
					'post_id'      => 'create_update_blueprint_' . get_the_ID(),
					'form'         => true,
					'id'           => 'acf-form-create-update-bp',
					'field_groups' => [
						$acf_fields['Realtime Customizer'],
						$acf_fields['Create or Update Blueprint'],
					],
					'return'       => $container->get_permalink( 'blueprints' ),
					'submit_value' => __( 'Publish Blueprint', 'dollie' ),
				]
			);
			?>
		</div>
		
		<?php
		$message = ob_get_clean();
		dollie()->load_template(
			'notice',
			[
				'type'    => 'info',
				'icon'    => 'fal fa-edit',
				'title'   => __( 'Publish this blueprint', 'dollie' ),
				'message' => $message,
			],
			true
		);
		?>
	</div>
<?php else : ?>
	<div class="dol-my-6">
		<?php ob_start(); ?>

		<div class="dol-mb-4">
			<?php printf( __( 'Before you update this blueprint make sure <strong>%s</strong> is ready for an update. Once you have deployed a new version of this blueprint it will be used next time a customer launches a site based on this blueprint. Make sure you’ve removed all sensitive data like testing user accounts and emails.', 'dollie' ), $container->get_url() ); ?>
		</div>

		<?php if ( '' !== $blueprint_time ) : ?>
			<div class="dol-my-4">
				<?php printf( __( 'Your Blueprint was last updated at %s', 'dollie' ), $blueprint_time ); ?>
			</div>
		<?php endif; ?>

		<div>
			<?php
			$dynamic_fields = get_field( 'wpd_dynamic_blueprint_data', 'create_update_blueprint_' . get_the_ID() );

			if ( ! is_array( $dynamic_fields ) ) {
				$dynamic_fields = [];
			}
			?>
			<div id="dol-blueprint-notification" class="dol-hidden" data-dynamic-fields="<?php echo esc_attr( count( $dynamic_fields ) ); ?>" data-container="<?php echo esc_attr( $container->get_id() ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'check_dynamic_fields_nonce' ) ); ?>" data-ajax-url="<?php echo esc_attr( admin_url( 'admin-ajax.php' ) ); ?>">
				<div class="dol-inline-flex dol-w-full dol-items-center dol-px-4 dol-py-2 dol-text-base dol-leading-6 dol-rounded dol-text-white dol-bg-secondary-600 dol-transition dol-ease-in-out dol-duration-150">
					<svg class="dol-animate-spin dol--ml-1 dol-mr-3 dol-h-5 dol-w-5 dol-text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
						<circle class="dol-opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
						<path class="dol-opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
					</svg>
					<?php esc_html_e( 'Checking field integrity...', 'dollie' ); ?>
				</div>
			</div>
			<?php
			acf_form(
				[
					'post_id'      => 'create_update_blueprint_' . get_the_ID(),
					'form'         => true,
					'id'           => 'acf-form-create-update-bp',
					'field_groups' => [
						$acf_fields['Realtime Customizer'],
						$acf_fields['Create or Update Blueprint'],
					],
					'return'       => $container->get_permalink( 'blueprints' ),
					'submit_value' => __( 'Update Blueprint', 'dollie' ),
				]
			);
			?>
		</div>

		<?php
		$message = ob_get_clean();
		dollie()->load_template(
			'notice',
			[
				'type'    => 'info',
				'icon'    => 'fal fa-edit',
				'title'   => __( 'Update your blueprint', 'dollie' ),
				'message' => $message,
			],
			true
		);
		?>
	</div>

	<div class="dol-border <?php do_action( 'dol_add_widget_classes' ); ?> dol-overflow-hidden dol-widget-blueprint-settings">
		<div class=" dol-flex dol-items-center dol-bg-primary-600 dol-border-0 dol-border-b">
			<div class="dol-p-4 lg:dol-px-8 dol-flex dol-items-center dol-justify-center dol-h-full">
				<?php echo dollie()->icon()->settings( 'dol-text-white dol-text-xl md:dol-text-2xl' ); ?>
			</div>
			<h4 class="dol-m-0 dol-p-0 dol-text-white dol-text-base md:dol-text-xl">
				<?php esc_html_e( 'Blueprint Settings', 'dollie' ); ?>
			</h4>
		</div>
		<div class="dol-p-4 lg:dol-px-8 lg:dol-py-6 ">
			<div class="dol-mb-6">
				<?php
				printf( esc_html__( 'Now that you\'ve created your Blueprint you can use the options below to change how it is listed on the "Launch %s" page. These settings are also used wherever you use the [dollie-blueprints] shortcode.', 'dollie' ), dollie()->string_variants()->get_site_type_string() );
				?>
			</div>

			<div>
				<?php
				acf_form(
					[
						'post_id'      => get_the_ID(),
						'form'         => true,
						'id'           => 'acf-form-bp',
						'field_groups' => [
							$acf_fields['Blueprints'],
						],
						'return'       => $container->get_permalink( 'blueprints' ),
					]
				);
				?>
			</div>
		</div>
	</div>

	<div class="dol-flex dol-flex-wrap dol--mx-4 dol-mt-4">
		<div class="dol-w-full md:dol-w-1/2 lg:dol-w-2/6 dol-p-4">
			<div class="dol-border <?php do_action( 'dol_add_widget_classes' ); ?> dol-overflow-hidden">
				<div class="dol-p-4 lg:dol-px-8 lg:dol-py-4 dol-bg-primary-600 dol-border-0 dol-border-b">
					<h4 class="dol-m-0 dol-p-0 dol-text-white dol-text-base md:dol-text-xl">
						<?php esc_html_e( 'Blueprint history', 'dollie' ); ?>
					</h4>
				</div>
				<div class="dol-p-4 lg:dol-px-8 lg:dol-py-6 ">
					<?php $blueprints = $container->get_available_blueprints(); ?>
					<?php if ( ! empty( $blueprints ) ) : ?>
						<ul class="dol-list-none dol-m-0 dol-p-0">
							<?php foreach ( $blueprints as $blueprint ) : ?>
								<li>
									<?php echo dollie()->icon()->clock(); ?>
									<span><?php printf( __( 'Created on %1$s at %2$s.', 'dollie' ), $blueprint['date'], $blueprint['time'] ); ?></span>
									<span><?php printf( __( 'Size %s.', 'dollie' ), $blueprint['size'] ); ?></span>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php else : ?>
						<span class="dol-text-sm"><?php esc_html_e( 'No Blueprints created yet.', 'dollie' ); ?></span>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<div class="dol-w-full md:dol-w-1/2 lg:dol-w-2/6 dol-p-4">
			<div class="dol-border <?php do_action( 'dol_add_widget_classes' ); ?> dol-overflow-hidden">
				<div class="dol-p-4 lg:dol-px-8 lg:dol-py-4 dol-bg-primary-600 dol-border-0 dol-border-b">
					<h4 class="dol-m-0 dol-p-0 dol-text-white dol-text-base md:dol-text-xl">
						<?php esc_html_e( 'What is included?', 'dollie' ); ?>
					</h4>
				</div>
				<div class="dol-p-4 lg:dol-px-8 lg:dol-py-6 ">
					<div class="dol-font-bold dol-mb-2">
						<?php esc_html_e( 'We copy over:', 'dollie' ); ?>
					</div>
					<ul class="dol-list-bullet dol-m-0 dol-p-0 dol-pl-5 dol-text-sm" ">
						<li><?php esc_html_e( 'Plugins', 'dollie' ); ?></li>
						<li><?php esc_html_e( 'Themes', 'dollie' ); ?></li>
						<li><?php esc_html_e( 'Media Upload', 'dollie' ); ?></li>
						<li><?php esc_html_e( 'The Database*', 'dollie' ); ?></li>
					</ul>
				</div>
			</div>
		</div>
		<div class=" dol-w-full md:dol-w-1/2 lg:dol-w-2/6 dol-p-4">
			<div class="dol-border <?php do_action( 'dol_add_widget_classes' ); ?> dol-overflow-hidden">
				<div class="dol-p-4 lg:dol-px-8 lg:dol-py-4 dol-bg-primary-600 dol-border-0 dol-border-b">
					<h4 class="dol-m-0 dol-p-0 dol-text-white dol-text-base md:dol-text-xl">
						<?php esc_html_e( 'What is excluded?', 'dollie' ); ?>
					</h4>
				</div>
				<div class="dol-p-4 lg:dol-px-8 lg:dol-py-6 ">
					<div class="dol-font-bold dol-mb-2">
						<?php esc_html_e( 'We DO NOT copy:', 'dollie' ); ?>
					</div>
					<ul class="dol-list-bullet dol-m-0 dol-p-0 dol-pl-5 dol-text-sm">
						<li><?php esc_html_e( 'mu-plugins folder', 'dollie' ); ?></li>
						<li><?php esc_html_e( 'All WordPress Core Files', 'dollie' ); ?></li>
						<li><?php esc_html_e( 'WP-Config.php', 'dollie' ); ?></li>
						<li><?php esc_html_e( 'wp_users & wp_usermeta tables', 'dollie' ); ?></li>
						<li><?php esc_html_e( 'All non-core folders and files outside of wp-content', 'dollie' ); ?></li>
					</ul>
				</div>
			</div>
		</div>
	</div>
<?php endif ?>
