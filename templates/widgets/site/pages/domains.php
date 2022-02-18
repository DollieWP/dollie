<h2 class="dol-text-gray-500 text-s dol-font-small dol-uppercase dol-tracking-wide dol-mb-5 dol-text-xl">
	<?php esc_html_e( 'Domains', 'dollie' ); ?>
</h2>

<?php

$dns_manager            = get_post_meta( get_the_ID(), 'wpd_domain_dns_manager', true );
$domain                 = get_post_meta( get_the_ID(), 'wpd_domains', true );
$domain_wizard_complete = get_post_meta( get_the_ID(), 'wpd_domain_migration_complete', true );

?>

<?php if ( 'pending' === $dns_manager ) : ?>

	<div class="dol-my-6">
		<?php
		dollie()->load_template(
			'notice',
			[
				'type'    => 'info',
				'icon'    => 'fas fa-exclamation-circle',
				'title'   => sprintf( __( 'Please hold on whilst "%s" is getting ready to be used', 'dollie' ), get_post_meta( get_the_ID(), 'wpd_domain_pending', true ) ),
				'message' => __( 'Your domain\'s nameservers are being checked. Once we confirm all your nameservers are set correctly, we will automatically replace your website\'s URL and enable the DNS manager.', 'dollie' ),
			],
			true
		);
		?>

		<div class="dol-rounded dol-overflow-hidden dol-shadow dol-mt-6">
			<div class="dol-p-4 lg:dol-px-8 lg:dol-py-6">
				<h4 class="dol-text-lg"><?php esc_html_e( 'Your domain will get validated once we detect the following nameservers attached to it.', 'dollie' ); ?></h4>
				<ul class="dol-m-0">
					<li>pdns1.stratus5.com</li>
					<li>pdns2.stratus5.com</li>
					<li>pdns3.stratus5.com</li>
				</ul>
				<span class="dol-block dol-mb-4 dol-mt-6"><?php esc_html_e( 'Want to change the domain or have you typed in the wrong domain name? You can cancel at any time and try again!', 'dollie' ); ?></span>
				<form action="<?php echo get_permalink( get_the_ID() ); ?>?remove-domain=<?php echo get_post_meta( get_the_ID(), 'wpd_container_id', true ); ?>"
					method="post">
					<button name="remove_customer_dns" id="remove_customer_dns" type="submit"
							class="dol-px-4 dol-py-2 dol-bg-red-600 dol-text-white dol-rounded">
						<i class="fa fa-trash-o" aria-hidden="true"></i>
						<?php esc_html_e( 'Cancel', 'dollie' ); ?>
					</button>
				</form>
			</div>
		</div>
	</div>

<?php elseif ( ! empty( $domain ) ) : ?>

	<?php if ( 'no' === $domain_wizard_complete ) : ?>

		<div class="dol-my-6">
			<div class="dol-rounded dol-overflow-hidden dol-shadow dol-mb-6">
				<div class="dol-p-4 lg:dol-px-8 lg:dol-py-4 dol-bg-gray-200">
					<h4 class="dol-p-0 dol-m-0 dol-text-base md:dol-text-xl">
						<?php esc_html_e( 'Site URL replacement is not complete ', 'dollie' ); ?>
					</h4>
				</div>
				<div class="dol-p-4 lg:dol-px-8 lg:dol-py-6">
					<span class="dol-block dol-mb-4">
						<?php printf( __( 'Your domain <strong>%s</strong> is connected but the URL replacement wasn\'t complete.', 'dollie' ), $domain ); ?>
					</span>

					<a class="dol-px-4 dol-py-2 dol-bg-red-600 dol-text-white dol-rounded"
					   href="<?php echo get_permalink( get_the_ID() ); ?>?update-domain-url">
						<i class="fa fa-refresh" aria-hidden="true"></i>
						<?php esc_html_e( 'Replace URL now', 'dollie' ); ?>
					</a>
				</div>
			</div>
		</div>

	<?php else : ?>

		<div class="dol-my-6">
			<?php
			dollie()->load_template(
				'notice',
				[
					'icon'    => 'fas fa-exclamation-circle',
					'title'   => sprintf( __( '"%s" is now linked to this %s!', 'dollie' ), $domain, dollie()->get_site_type_string() ),
					'message' => __( 'Congrats! Your are using a live domain for this site.', 'dollie' ),
				],
				true
			);
			?>
		</div>

		<?php
		dollie()->load_template(
			'widgets/site/pages/domain/dns-manager',
			[
				'domain'      => $domain,
				'dns_manager' => $dns_manager,
			],
			true
		);
		?>

	<?php endif; ?>

<?php endif; ?>

<?php if ( ! empty( $domain ) ) : ?>
	<div class="dol-rounded dol-overflow-hidden dol-shadow dol-mb-6">
		<div class="dol-p-4 lg:dol-px-8 lg:dol-py-4 dol-bg-gray-200">
			<h4 class="dol-p-0 dol-m-0 dol-text-base md:dol-text-xl">
				<?php esc_html_e( 'Remove your linked domain', 'dollie' ); ?>
			</h4>
		</div>
		<div class="dol-p-4 lg:dol-px-8 lg:dol-py-6">
			<span class="dol-block dol-mb-4"><?php esc_html_e( 'Have you changed your domain name? You can unlink your current domain by pressing the button below. Once you have removed your current domain you can add your new domain.', 'dollie' ); ?></span>
			<form action="<?php echo get_permalink( get_the_ID() ); ?>?remove-domain=<?php echo get_post_meta( get_the_ID(), 'wpd_container_id', true ); ?>"
				  method="post">
				<button name="remove_customer_domain" id="remove_customer_domain" type="submit"
						class="dol-px-4 dol-py-2 dol-bg-red-600 dol-text-white dol-rounded">
					<i class="fa fa-trash-o" aria-hidden="true"></i>
					<?php esc_html_e( 'Remove Domain', 'dollie' ); ?>
				</button>
			</form>
		</div>
	</div>
	<p class="dol-mt-2">
		<?php esc_html_e( 'Your current domain IP information:', 'dollie' ); ?></p>
	<?php
	dollie()->load_template(
		'widgets/site/pages/domain/connect/dns-ip-table',
		[
			'ip' => dollie()->get_wp_site_data( 'ip', $current_query->id ),
		],
		true
	);
	?>

<?php endif; ?>

<?php if ( ! \Dollie\Core\Modules\Forms\DomainConnect::instance()->is_form_restricted() ) : ?>
	<div class="dol-mt-6">
		<?php echo do_shortcode( '[dollie_form form="form_dollie_domain_connect"]' ); ?>
	</div>
<?php endif; ?>
