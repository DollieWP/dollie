<h2 class="dol-title">
	<?php esc_html_e( 'Domains', 'dollie' ); ?>
</h2>

<?php

$domain                 = get_post_meta( get_the_ID(), 'wpd_domains', true );
$domain_wizard_complete = get_post_meta( get_the_ID(), 'wpd_domain_migration_complete', true );

?>

<?php if ( ! empty( $domain ) ) : ?>

	<?php if ( empty( $domain_wizard_complete ) ) : ?>

		<div class="dol-my-6">

			<div class="dol-border dol-border-solid dol-border-cobalt-100 dol-rounded dol-overflow-hidden dol-mb-6">
				<div class="dol-px-4 lg:dol-px-8 lg:dol-py-4 dol-bg-cobalt-100">
					<h4 class="dol-p-0 dol-m-0 dol-font-bold dol-text-cobalt-500 dol-text-base md:dol-text-xl">
						<?php esc_html_e( 'Site URL replacement is not complete ', 'dollie' ); ?>
					</h4>
				</div>
				<div class="dol-px-4 lg:dol-px-8 lg:dol-py-4 dol-bg-white">
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
			\Dollie\Core\Utils\Tpl::load(
				DOLLIE_WIDGETS_PATH . 'templates/notice',
				[
					'icon'    => 'fal fa-exclamation-circle',
					'title'   => sprintf( __( '%s is linked to this site!', 'dollie' ), $domain ),
					'message' => __( 'Congrats! Your are using a live domain for this site.' ),
				],
				true
			);
			?>
		</div>

	<?php endif; ?>

	<div class="dol-border dol-border-solid dol-border-cobalt-100 dol-rounded dol-overflow-hidden dol-mb-6">
		<div class="dol-px-4 lg:dol-px-8 lg:dol-py-4 dol-bg-cobalt-100">
			<h4 class="dol-p-0 dol-m-0 dol-font-bold dol-text-cobalt-500 dol-text-base md:dol-text-xl">
				<?php esc_html_e( 'Remove your linked domain', 'dollie' ); ?>
			</h4>
		</div>
		<div class="dol-px-4 lg:dol-px-8 lg:dol-py-4 dol-bg-white">
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

<?php endif; ?>

<div class="dol-mt-6">
	<?php

	echo do_shortcode( '[dollie_form form="form_dollie_domain_connect"]' );

	?>
</div>
