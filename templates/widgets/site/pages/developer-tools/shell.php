<?php
$shell_url = dollie()->get_customer_secret_url() . '/shell/';

wp_enqueue_script( 'iframe-resizer' );
?>
<script>
	jQuery(document).ready(function ($) {
		$('[data-shell]').on('click', function () {
			var src = '<?php echo esc_url( $shell_url ); ?>';
			var f = document.createElement('iframe');
			f.src = src;
			f.id = 'shell-iframe';
			f.width = '100%';
			f.height = '600px';
			f.allowfullscreen = '';

			$('.dol-shell-wrapper').html(f);
			$('.dol-shell-preloader').addClass('dol-hidden');

			return false;
		});

	});
</script>

<h2 class="dol-title">
	<?php esc_html_e( 'WP CLI', 'dollie' ); ?>
</h2>

<div class="dol-shell-wrapper"></div>
<div class="dol-shell-preloader dol-py-40 md:dol-py-56 dol-rounded dol-overflow-hidden dol-relative dol-z-0">
	<div class="dol-bg-black dol-opacity-25 dol-absolute dol-h-full dol-w-full dol-top-0 dol-left-0 dol--z-1"></div>
	<div class="dol-flex dol-flex-wrap dol-items-center dol-justify-center dol-flex-col">
		<div class="dol-italic dol-text-lg">
			<i class="fal fa-wordpress fa dol-mr-2" aria-hidden="true"></i>
			<?php esc_html_e( 'Have fun, but don\'t forget to make backups!', 'dollie' ); ?>
		</div>

		<div class="dol-mt-4">
			<a href="#"
			   class="dol-inline-block dol-text-white dol-bg-blue-600 hover:dol-text-white hover:dol-bg-blue-700 dol-px-6 dol-py-3 dol-rounded"
			   data-shell="LpKyzSxVhk4">
				<?php esc_html_e( 'Load the WP-CLI Terminal', 'dollie' ); ?>
			</a>
		</div>
	</div>
</div>
