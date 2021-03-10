<?php $blueprint_time = get_post_meta( $post_id, 'wpd_blueprint_time', true ); ?>

<h2 class="dol-text-gray-500 text-s dol-font-small dol-uppercase dol-tracking-wide dol-mb-5 dol-text-xl">
	<?php esc_html_e( 'Blueprints', 'dollie' ); ?>
</h2>

<div class="dol-my-6">
	<?php ob_start(); ?>
	<div class="dol-mb-4">
		<?php printf( __( '<strong>%s</strong> is the source of your Blueprint. Make sure that this installation is working properly and is set up according before you deploy your Blueprint.', 'dollie' ), dollie()->get_container_url() ); ?>
	</div>

	<?php if ( '' !== $blueprint_time ) : ?>
		<div class="dol-my-4 dol-font-bold">
			<?php printf( __( 'Your last Blueprint was deployed at %s', 'dollie' ), $blueprint_time ); ?>
		</div>
	<?php endif; ?>

	<div>
		<?php echo do_shortcode( '[dollie_form form="form_dollie_create_blueprint"]' ); ?>
	</div>
	<?php
	$message = ob_get_clean();
	\Dollie\Core\Utils\Tpl::load(
		'notice',
		[
			'type'    => 'info',
			'icon'    => 'fas fa-question',
			'title'   => __( 'Create or update your blueprint', 'dollie' ),
			'message' => $message,
		],
		true
	);
	?>
</div>

<?php if ( '' !== $blueprint_time && current_user_can( 'manage_options' ) ) : ?>

	<div class="dol-border <?php do_action( 'dol_add_widget_classes' ); ?> dol-overflow-hidden dol-widget-blueprint-settings">
		<div class="dol-p-4 lg:dol-px-8 lg:dol-py-4 dol-bg-primary-600 dol-border-0 dol-border-b">
			<h4 class="dol-m-0 dol-p-0 dol-font-bold dol-text-white dol-text-base md:dol-text-xl">
				<?php esc_html_e( 'Blueprint Settings', 'dollie' ); ?>
			</h4>
		</div>
		<div class="dol-p-4 lg:dol-px-8 lg:dol-py-6 ">
			<div class="dol-mb-6">
				<?php esc_html_e( 'Now that created your blueprint you can use the options below to change how it is listed on the "Launch Site" page and wherever you use the [dollie-blueprints] shortcode.', 'dollie' ); ?>
			</div>

			<div>
				<?php
				$acf_fields = dollie()->acf_get_database_field_group_keys();

				acf_form(
					[
						'form'         => true,
						'id'           => 'acf-form-bp',
						'field_groups' => [ $acf_fields['Blueprints'] ],
						'return'       => dollie()->get_site_url( get_the_ID(), 'blueprints' ),
					]
				);
				?>
			</div>
		</div>
	</div>

<?php endif ?>

<div class="dol-flex dol-flex-wrap dol--mx-4 dol-mt-4">
	<div class="dol-w-full md:dol-w-1/2 lg:dol-w-2/6 dol-p-4">
		<div class="dol-border <?php do_action( 'dol_add_widget_classes' ); ?> dol-overflow-hidden">
			<div class="dol-p-4 lg:dol-px-8 lg:dol-py-4 dol-bg-primary-600 dol-border-0 dol-border-b">
				<h4 class="dol-m-0 dol-p-0 dol-font-bold dol-text-white dol-text-base md:dol-text-xl">
					<?php esc_html_e( 'Blueprint history', 'dollie' ); ?>
				</h4>
			</div>
			<div class="dol-p-4 lg:dol-px-8 lg:dol-py-6 ">
				<?php $blueprints = dollie()->get_available_blueprints(); ?>
				<?php if ( ! empty( $blueprints ) ) : ?>
					<ul class="dol-list-none dol-m-0 dol-p-0">
						<?php foreach ( $blueprints as $blueprint ) : ?>
							<li>
								<i class="fas fa-calendar"></i>
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
				<h4 class="dol-m-0 dol-p-0 dol-font-bold dol-text-white dol-text-base md:dol-text-xl">
					<?php esc_html_e( 'What is included?', 'dollie' ); ?>
				</h4>
			</div>
			<div class="dol-p-4 lg:dol-px-8 lg:dol-py-6 ">
				<div class="dol-font-bold dol-mb-2">
					<?php esc_html_e( 'We copy over:', 'dollie' ); ?>
				</div>
				<ul class="dol-list-bullet dol-m-0 dol-p-0 dol-pl-5 dol-text-sm"">
					<li><?php esc_html_e( 'Plugins', 'dollie' ); ?></li>
					<li><?php esc_html_e( 'Themes', 'dollie' ); ?></li>
					<li><?php esc_html_e( 'Media Upload', 'dollie' ); ?></li>
					<li><?php esc_html_e( 'The Database*', 'dollie' ); ?></li>
				</ul>
			</div>
		</div>
	</div>
	<div class="dol-w-full md:dol-w-1/2 lg:dol-w-2/6 dol-p-4">
		<div class="dol-border <?php do_action( 'dol_add_widget_classes' ); ?> dol-overflow-hidden">
			<div class="dol-p-4 lg:dol-px-8 lg:dol-py-4 dol-bg-primary-600 dol-border-0 dol-border-b">
				<h4 class="dol-m-0 dol-p-0 dol-font-bold dol-text-white dol-text-base md:dol-text-xl">
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
