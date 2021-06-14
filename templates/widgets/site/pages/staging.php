<?php
$container    = get_post( get_the_ID() );
$staging_data = get_post_meta( get_the_ID(), '_wpd_staging_data', true );
$staging_url  = get_post_meta( get_the_ID(), '_wpd_staging_url', true );

if ( empty( $staging_url ) ) {
	$status = 'disabled';
} else {
	$status = $staging_data[ $staging_url ]['status'];
}

$status_title   = __( 'Staging site is disabled', 'dollie' );
$status_message = sprintf( __( 'Here you can easily manage your staging site for <strong>%s</strong>. Staging allows you to conduct different tests before applying them to your live site.', 'dollie' ), $container->post_name );

if ( 'pending' === $status ) {
	if ( isset( $staging_data[ $staging_url ]['deploy_job'] ) ) {
		$job           = $staging_data[ $staging_url ]['deploy_job'];
		$deploy_status = \Dollie\Core\Modules\Sites\WP::instance()->process_deploy_status( $job );
	}

	$status_title   = __( 'Staging site is being created', 'dollie' );
	$status_message = sprintf( __( 'Please pe patient while we create a staging site for <strong>%s</strong>.', 'dollie' ), $container->post_name );

} elseif ( 'live' === $status ) {
	$status_title = sprintf( __( 'Staging site: %', 'dollie' ), $staging_url );
}

\Dollie\Core\Utils\Tpl::load(
	'notice',
	[
		'type'    => 'info',
		'icon'    => 'fas fa-clone',
		'title'   => $status_title,
		'message' => $status_message,
	],
	true
);

?>

<?php if ( 'live' === $status ) : ?>
	<div class="dol-mt-6">
		<form action="" method="post">
			<input type="hidden" name="staging_change" value="1">

			<button type="submit" class="dol-bg-red-700" id="staging-form-submit">
				<?php esc_html_e( 'Remove Staging Site', 'dollie' ); ?>
			</button>
			<?php wp_nonce_field( 'wpd_staging' ); ?>
		</form>
	</div>
<?php elseif ( 'disabled' === $status ) : ?>
	<?php if ( dollie()->staging_sites_limit_reached() ) : ?>
		<div class="dol-mt-6">
			<?php
				\Dollie\Core\Utils\Tpl::load(
					'notice',
					[
						'icon'  => 'fas fa-exclamation-circle',
						'type'  => 'error',
						'title' => esc_html__( 'You have reached your staging sites limit. Please upgrade your subscription!', 'dollie' ),
					],
					true
				);

				return;
			?>
		</div>
	<?php endif; ?>
<?php endif; ?>


<div class="dol-mt-6">
	<form action="" method="post">
		<input type="hidden" name="staging_change" value="1">

		<button type="submit" id="staging-form-submit">
			<?php esc_html_e( 'Create a Staging Site', 'dollie' ); ?>
		</button>
		<?php wp_nonce_field( 'wpd_staging' ); ?>
	</form>
</div>

<script>
	jQuery(document).ready(function ($) {
		$('#staging-form-submit').on('click', function (e) {
			$(this).attr('disabled', true);

			$(this).parent('form').submit();
		});
	});
</script>
