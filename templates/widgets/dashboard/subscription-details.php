<?php if ( dollie()->access()->get_user_groups( get_current_user_id() ) ) : ?>
	<?php
	// Count the number of items
	$num_items = count( $items );

	// Determine the number of grid columns
	$grid_columns = '3'; // default to 3
	if ( $num_items < 3 ) {
		$grid_columns = strval( $num_items ); // if less than 3 items, set columns to the number of items
	}
	?>
	<div class="dol-grid dol-grid-cols-<?php echo $grid_columns; ?> dol-gap-4">
		<?php do_action( 'dollie/before/subscription/list' ); ?>

		<?php foreach ( $items as $item ) : ?>

			<div class="dol-widget-custom dol-overflow-hidden dol-bg-white dol-shadow dol-rounded-md dol-px-5 dol-py-3">
							<div class="dol-flex dol-items-center dol-py-4">
								<div class="dol-flex-shrink-0">
									<span class="dol-icon dol-mr-2 dol-text-secondary dol-text-xl">
									<?php
										echo '<i class="' . esc_attr( $item['icon'] ) . '"></i>';
									?>
									</span>
								</div>
								<div class="dol-ml-5 dol-w-0 dol-flex-1">
									<dl>
										<dt class="dol-text-sm dol-leading-5 dol-font-medium dol-text-cool-gray-500 dol-truncate">
											<?php echo esc_html( $item['title'] ); ?>
										</dt>
										<dd class="dol-m-0">
											<div class="dol-text-lg dol-leading-7 dol-font-medium dol-text-cool-gray-900">
												<?php echo esc_html( $item['value'] ); ?>
											</div>
										</dd>
									</dl>
								</div>
							</div>
						</div>

		<?php endforeach; ?>

		<?php do_action( 'dollie/after/subscription/list' ); ?>
	</div>
<?php else : ?>

<?php endif; ?>
