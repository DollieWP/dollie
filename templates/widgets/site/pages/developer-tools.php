<?php

if ( ! isset( $container ) ) {
	$container = dollie()->get_container();
}

$pages = array(
	''            => array(
		'title' => __( 'Details', 'dollie' ),
		'icon'  => dollie()
			->icon()
			->dev_details( 'md:dol-mr-1' ),
		'slug'  => 'details',
	),
	'code-editor' => array(
		'title' => __( 'Code Editor', 'dollie' ),
		'icon'  => dollie()
			->icon()
			->dev_code_editor( 'md:dol-mr-1' ),
		'slug'  => 'codiad',
	),
	'database'    => array(
		'title' => __( 'Database', 'dollie' ),
		'icon'  => dollie()
			->icon()
			->dev_database( 'md:dol-mr-1' ),
		'slug'  => 'adminer',
	),
	'shell'       => array(
		'title' => __( 'WP CLI', 'dollie' ),
		'icon'  => dollie()
			->icon()
			->dev_cli( 'md:dol-mr-1' ),
		'slug'  => 'shell',
	),
);

$page = isset( $_GET['section'] ) && $_GET['section'] ? sanitize_text_field( $_GET['section'] ) : '';

foreach ( $pages as $slug => $value ) {
	$pages[ $slug ]['active'] = $slug === $page;
}

?>

<nav class="dol-flex" aria-label="Breadcrumb">
	<ol class="<?php do_action( 'dol_add_widget_classes' ); ?> dol-px-6 dol-flex dol-space-x-4 dol-mb-10 dol-p-0 dol-m-0">
		<li class="dol-flex">
			<div class="dol-flex dol-items-center">
				<a href="<?php $container->get_permalink( 'performance' ); ?>" class="dol-text-gray-400 hover:dol-text-gray-500">
					<svg class="dol-flex-shrink-0 dol-h-5 dol-w-5 dol-transition dol-duration-150 dol-ease-in-out"
						xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
						<path
							d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
					</svg>
				</a>
			</div>
		</li>
		<?php
		$hooks              = \Dollie\Core\Modules\AccessGroups\AccessGroups::instance();
		$available_sections = $hooks->get_customer_developer_features();

		foreach ( $pages as $slug => $item ) :
			?>
			<?php
			$menu_class = $item['active'] ? 'dol-font-bold' : '';
			// Only continue if the slug is in the array of available sections
			if ( ! empty( $slug ) && ! in_array( $slug, $available_sections ) ) {
				continue;
			}
			?>
		<li class="dol-flex">
			<div class="dol-flex dol-items-center dol-space-x-4">
				<svg class="dol-flex-shrink-0 dol-w-6 dol-h-full dol-text-gray-200" viewBox="0 0 24 44"
					preserveAspectRatio="none" fill="currentColor" xmlns="http://www.w3.org/2000/svg"
					aria-hidden="true">
					<path d="M.293 0l22 22-22 22h1.414l22-22-22-22H.293z" />
				</svg>
				<a href="<?php echo $container->get_permalink( 'developer-tools', array( 'section' => esc_attr( $slug ) ) ); ?>"
					class="dol-leading-5 dol-text-gray-500 hover:dol-text-gray-700 dol-transition dol-duration-150 dol-ease-in-out <?php echo esc_attr( $menu_class ); ?>">
					<?php echo $item['icon']; ?>
					<?php echo esc_html( $item['title'] ); ?>
				</a>
			</div>
		</li>
		<?php endforeach; ?>
	</ol>
</nav>

<?php

if ( array_key_exists( $page, $pages ) ) {
	if (
		! dollie()
			->subscription()
			->has_partner_verified()
	) {
		dollie()->load_template(
			'notice',
			array(
				'type'    => 'error',
				'icon'    => 'fas fa-exclamation-circle',
				'title'   => __( 'Verify your Dollie Control HQ account to use the Developer Tools', 'dollie' ),
				'message' => 'Unlock full access to all Dollie Hub features like the Developer Tools by verifying your Dollie Control HQ account. These restrictions are in place to prevent abuse of Dollie Control HQ and will automatically be removed once you verify your account.',
				'links'   => array(
					array(
						'title'   => __( 'Verify Your Account', 'dollie' ),
						'url'     => 'https://control.getdollie.com/verify-my-account/',
						'new_tab' => true,
					),
				),
			),
			true,
		);
		return;
	} else {
		dollie()->load_template(
			"widgets/site/pages/developer-tools/{$pages[$page]['slug']}",
			array(
				'container' => $container,
			),
			true,
		);
	}
}

?>
