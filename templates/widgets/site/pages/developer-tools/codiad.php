<?php

if ( ! isset( $container ) ) {
	$container = dollie()->get_container();
}

$credentials = $container->get_credentials();

if ( empty( $credentials ) ) {
	return;
}

wp_enqueue_script( 'iframe-resizer' );

$sftp_url   = "sftp://{$credentials['username']}:{$credentials['password']}@{$credentials['ip']}:{$credentials['port']}";
$codiad_url = "{$container->get_url()}/{$credentials['secret']}/codiad}";

?>

<script>
	jQuery(function($) {
		$('[data-codiad]').on('click', function() {

			var is_safari = navigator.userAgent.indexOf("Safari") > -1;
			var src = '<?php echo esc_url( $codiad_url ); ?>';

			if (is_safari) {
				return;
			} else {
				var f = document.createElement('iframe');
				f.src = src;
				f.id = 'codiad-iframe';
				f.width = '100%';
				f.setAttribute("name", "<?php echo $credentials['secret']; ?>");
				f.height = '600px';
				f.allowfullscreen = '';

				$('.dol-codiad-wrapper').html(f);
				$('.dol-codiad-preloader').addClass('dol-hidden');
			}

			return false;
		});
	});
</script>

<h2 class="dol-text-gray-500 dol-uppercase dol-tracking-wide dol-mb-5 dol-text-xl">
	<?php esc_html_e( 'Code Editor', 'dollie' ); ?>
</h2>

<div class="dol-codiad-wrapper"></div>
<div class="dol-codiad-preloader dol-py-40 md:dol-py-56 dol-rounded dol-overflow-hidden dol-relative dol-z-0">
	<div class="dol-bg-black dol-bg-gradient-primary dol-absolute dol-h-full dol-w-full dol-top-0 dol-left-0 dol--z-1"></div>
	<div class="dol-flex dol-flex-wrap dol-items-center dol-justify-center dol-flex-col">
		<div class="dol-italic dol-text-lg dol-text-white">
			<?php echo dollie()->icon()->dev_code_editor(); ?>
			<?php esc_html_e( 'Code is Poetry...', 'dollie' ); ?>
		</div>

		<div class="dol-mt-4">
			<a href="<?php echo esc_url( $codiad_url ); ?>" target="_blank" class="dol-inline-block dol-text-white dol-bg-secondary-500 hover:dol-text-white hover:dol-bg-secondary-600 dol-px-6 dol-py-3 dol-rounded" data-codiad="LpKyzSxVhk4">
				<?php esc_html_e( 'Load the Code Editor', 'dollie' ); ?>
			</a>
		</div>
	</div>
</div>
