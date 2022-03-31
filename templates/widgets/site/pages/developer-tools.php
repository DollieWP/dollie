<?php

use Dollie\Core\Modules\AccessControl;

if ( empty( $data ) ) : ?>
	<?php dollie()->could_not_connect_message(); ?>
<?php else : ?>

	<?php

	$developer_menu = [
		''            => [
			'title' => __( 'Details', 'dollie' ),
			'icon'  => dollie()->icon()->dev_details( 'md:dol-mr-1' ),
		],
		'code-editor' => [
			'title' => __( 'Code Editor', 'dollie' ),
			'icon'  => dollie()->icon()->dev_code_editor( 'md:dol-mr-1' ),
		],
		'database'    => [
			'title' => __( 'Database', 'dollie' ),
			'icon'  => dollie()->icon()->dev_database( 'md:dol-mr-1' ),
		],
		'shell'       => [
			'title' => __( 'WP CLI', 'dollie' ),
			'icon'  => dollie()->icon()->dev_cli( 'md:dol-mr-1' ),
		],
	];

	$page = isset( $_GET['section'] ) && $_GET['section'] ? sanitize_text_field( $_GET['section'] ) : '';

	foreach ( $developer_menu as $key => $value ) {
		$developer_menu[ $key ]['active'] = $key === $page;
	}

	?>
	<nav class="dol-flex" aria-label="Breadcrumb">
		<ol class="<?php do_action( 'dol_add_widget_classes' ); ?> dol-px-6 dol-flex dol-space-x-4 dol-mb-10 dol-p-0 dol-m-0">
			<li class="dol-flex">
				<div class="dol-flex dol-items-center">
					<a href="<?php $container->get_permalink( 'performance' ); ?>" class="dol-text-gray-400 hover:dol-text-gray-500">
						<svg class="dol-flex-shrink-0 dol-h-5 dol-w-5 dol-transition dol-duration-150 dol-ease-in-out" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
							<path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
						</svg>
						<span class="dol-sr-only">Developer Details</span>
					</a>
				</div>
			</li>
			<?php foreach ( $developer_menu as $slug => $item ) : ?>
				<?php
				$menu_class     = $item['active'] ? 'dol-font-bold' : '';
				$available_tabs = get_field( 'available_features_developers', 'option' );

				if ( $slug !== '' && ! dollie()->in_array_r( $slug, $available_tabs ) ) {
					continue;
				}
				?>
				<li class="dol-flex">
					<div class="dol-flex dol-items-center dol-space-x-4">
						<svg class="dol-flex-shrink-0 dol-w-6 dol-h-full dol-text-gray-200" viewBox="0 0 24 44" preserveAspectRatio="none" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
							<path d="M.293 0l22 22-22 22h1.414l22-22-22-22H.293z" />
						</svg>
						<a href="<?php echo $container->get_permalink( 'developer-tools' ); ?>?section=<?php echo esc_attr( $slug ); ?>" class="dol-leading-5 dol-text-gray-500 hover:dol-text-gray-700 dol-transition dol-duration-150 dol-ease-in-out <?php echo esc_attr( $menu_class ); ?>">
							<?php echo $item['icon']; ?>
							<?php echo esc_html( $item['title'] ); ?>
						</a>
					</div>
				</li>
			<?php endforeach; ?>
		</ol>
	</nav>

	<?php

	$customer_details = \Dollie\Core\Modules\Container::instance()->get_customer_details( get_the_ID() );

	if ( 'code-editor' === $page ) {
		dollie()->load_template(
			'widgets/site/pages/developer-tools/codiad',
			[
				'data' => $data,
			],
			true
		);
	} elseif ( 'database' === $page ) {
		dollie()->load_template(
			'widgets/site/pages/developer-tools/adminer',
			[
				'data'             => $data,
				'customer_details' => $customer_details,
			],
			true
		);
	} elseif ( 'shell' === $page ) {
		dollie()->load_template(
			'widgets/site/pages/developer-tools/shell',
			[
				'data' => $data,
			],
			true
		);
	} else {
		dollie()->load_template(
			'widgets/site/pages/developer-tools/details',
			[
				'data'             => $data,
				'customer_details' => $customer_details,
			],
			true
		);
	}

	?>

	<?php
endif;
