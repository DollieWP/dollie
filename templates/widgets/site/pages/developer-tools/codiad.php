<?php
$codiad_url = dollie()->get_customer_secret_url() . '/codiad/' . dollie()->secret_admin_key();
$login      = dollie()->get_customer_login_url() . '&redirect_to=' . urlencode($codiad_url);

wp_enqueue_script('iframe-resizer');
?>

<script>
	jQuery(document).ready(function($) {
		$('[data-codiad]').on('click', function() {
			var src = '<?php echo esc_url($codiad_url); ?>';
			var f = document.createElement('iframe');
			f.src = src;
			f.id = 'codiad-iframe';
			f.width = '100%';
			f.setAttribute("name", "<?php echo dollie()->secret_admin_key();?>");
			f.height = '600px';
			f.allowfullscreen = '';

			$('.dol-codiad-wrapper').html(f);
			$('.dol-codiad-preloader').addClass('dol-hidden');

			return false;
		});
	});
</script>

<h2 class="dol-text-gray-500 text-s dol-font-small dol-uppercase dol-tracking-wide dol-mb-5 dol-text-xl">
	<?php esc_html_e('Code Editor', 'dollie'); ?>
</h2>

<div class="dol-codiad-wrapper"></div>
<div class="dol-codiad-preloader dol-py-40 md:dol-py-56 dol-rounded dol-overflow-hidden dol-relative dol-z-0">
	<div class="dol-bg-black dol-bg-gradient-primary dol-absolute dol-h-full dol-w-full dol-top-0 dol-left-0 dol--z-1"></div>
	<div class="dol-flex dol-flex-wrap dol-items-center dol-justify-center dol-flex-col">
		<div class="dol-italic dol-text-lg dol-text-white">
			<i class="fal fa-code fa dol-mr-2" aria-hidden="true"></i>
			<?php esc_html_e('Code is Poetry...', 'dollie'); ?>
		</div>

		<div class="dol-mt-4">
			<a href="#" class="dol-inline-block dol-text-white dol-bg-secondary-500 hover:dol-text-white hover:dol-bg-secondary-600 dol-px-6 dol-py-3 dol-rounded" data-codiad="LpKyzSxVhk4">
				<?php esc_html_e('Load the Code Editor', 'dollie'); ?>
			</a>
		</div>
	</div>
</div>
