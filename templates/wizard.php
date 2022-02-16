<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

$body_classes = [
	'wpd-wizard-page',
];

if (is_rtl()) {
	$body_classes[] = 'rtl';
}

new \Dollie\Core\Modules\Wizard();

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title><?php echo get_bloginfo('name'); ?></title>
	<?php wp_head(); ?>
	<script>
		var ajaxurl = '<?php echo admin_url('admin-ajax.php', 'relative'); ?>';
	</script>

	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
</head>

<body class="<?php echo implode(' ', $body_classes); ?>">
	<?php
	do_action('wp_body_open'); ?>

	<!-- This example requires Tailwind CSS v2.0+ -->
	<!--
  This example requires updating your template:

  ```
  <html class="dol-h-full dol-bg-gray-100">
  <body class="dol-h-full">
  ```
-->
	<div class="dol-min-h-full">
		<nav class="dol-bg-gray-800">
			<div class="dol-max-w-7xl dol-mx-auto dol-px-4 sm:dol-px-6 lg:dol-px-8">
				<div class="dol-flex dol-items-center dol-justify-between dol-h-16">
					<div class="dol-flex dol-items-center">
						<div class="dol-flex-shrink-0">
							<div class="dol-text-white">
								<h3 class="dol-text-white dol-font-semibold"> <img class="dol-h-8 dol-w-8" src="https://getdollie.com/wp-content/uploads/2020/01/cropped-icon-only-180x180.png" alt="Workflow"> Dollie Setup Wizard</h3>
							</div>
						</div>
					</div>
				</div>
			</div>
		</nav>

		<header class="dol-bg-white dol-shadow">
			<div class="dol-max-w-7xl dol-mx-auto dol-py-6 dol-px-4 sm:dol-px-6 lg:dol-px-8">
				<h1 class="dol-text-3xl dol-font-bold dol-text-gray-900">Let's set up your platform!</h1>
			</div>
		</header>
		<main class="dol-bg-gray-100 dol-pt-8 dol-pb-8">
			<div class="dol-max-w-7xl dol-mx-auto dol-py-6 sm:dol-px-6 lg:dol-px-8 dol-bg-white">
				<!-- Replace with your content -->
				<div class="dol-px-4 dol-py-6 sm:dol-px-0">

					<!-- This example requires Tailwind CSS v2.0+ -->
					<div class="dol-flex dol-mb-6">
						<div>
							<h2 class="dol-text-lg dol-font-bold"></h2>
							<p class="dol-mt-1">

								<?php echo do_shortcode('[advanced_form form="form_620b9a5a68c07"]') ?>

							</p>
						</div>
						<div class="dol-ml-12 dol-flex-shrink-0">
							<img class="dol-h-64 dol-w-64" src="https://getdollie.com/wp-content/uploads/2019/10/wordpress.png" alt="Workflow">
						</div>
					</div>

				</div>
				<!-- /End replace -->
			</div>
		</main>
	</div>



	<?php wp_footer();
	?>
</body>

</html>
<?php
die();
