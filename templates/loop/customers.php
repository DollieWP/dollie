<?php
if (!isset($view_type)) {
	$view_type = 'list';
}

$list_type      = 'dol-customers-' . $view_type;
$list_item_type = 'dol-customers-' . $view_type . '-item';

?>
<div class="dol-mb-6 dol-widget-customer-search">
	<div class="dol-flex dol-flex-wrap md:dol-justify-between">
		<div class="dol-flex dol-items-center dol-rounded dol-overflow-hidden dol-mb-4 md:dol-mb-0 dol-h-10 md:dol-h-auto">

		</div>
		<div class="dol-relative dol-w-full md:dol-w-auto">
			<div class="dol-absolute dol-left-0 dol-top-0 dol-ml-4 dol-flex dol-items-center dol-h-full">
				<i class="fas fa-search dol-text-ash-500"></i>
			</div>
			<input type="text" name="customer_search" class="dol-search-input dol-search-customer dol-w-full md:dol-w-64" data-list-type="<?php echo esc_attr($view_type); ?>" data-permalink="<?php echo esc_attr($query_data['permalink']); ?>" data-search-term="" placeholder="<?php printf(esc_html__('Search for a  %s', 'dollie'), dollie()->get_user_type_string()); ?>">
		</div>
	</div>
</div>

<div class="dol-customers dol-relative">
	<div class="dol-loader" data-for="pagination">
		<div class="dol-flex dol-items-center dol-justify-center dol-h-full">
			<svg class="dol-animate-spin dol-h-10 dol-w-10 dol-text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
				<circle class="dol-opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
				<path class="dol-opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
			</svg>
		</div>
	</div>
	<?php if (!empty($customers->results)) { ?>
		<div class="dol-customers-container <?php echo esc_attr($list_type); ?>">
			<?php foreach ($customers->results as $customer) { ?>
				<?php
				$data = [
					'name' => $customer->display_name,
				];
				?>
				<div class="dol-customers-item <?php echo esc_attr($list_item_type); ?>">
					<div class="dol-customers-item-inner <?php do_action('dol_add_widget_classes'); ?> dol-divide-y dol-divide-gray-200">
						<div class="dol-customers-image dol-relative">

							<?php echo get_avatar($customer->ID, '100', '', '', ['class' => 'dol-customers-image-box dol-round-lg']); ?>

						</div>
						<div class="dol-customers-name">
							<div class="dol-px-4">
								<div class="dol-font-bold dol-text-lg dol-cursor-default">
									<a class="dol-text-normal dol-leading-normal dol-truncate dol-text-gray-600" href="<?php echo get_author_posts_url($customer->ID); ?>" target="_blank">
										<?php echo $customer->display_name; ?>
									</a>
								</div>
							</div>
						</div>
						<div class="dol-customers-version dol-cursor-default dol-text-sm">
							<div class="dol-font-semibold dol-text-gray-500">
								<?php echo dollie()->get_site_type_plural_string(); ?>
							</div>
							<div class="dol-font-bold ">
								<?php echo dollie()->count_customer_containers($customer->ID); ?>
							</div>
						</div>
						<div class="dol-customers-controls">
							<a class="dol-inline-block dol-text-sm dol-text-white dol-bg-primary dol-rounded dol-px-3 dol-py-2 hover:dol-text-white hover:dol-bg-primary-600" href="<?php echo get_edit_user_link($customer->ID); ?>">
								<i class="fas fa-cog"></i>
								<span class="dol-ml-1"><?php printf(esc_html__('Manage %s', 'dollie-setup'), dollie()->get_user_type_string()); ?></span>
							</a>


							<a class="dol-inline-block dol-text-sm dol-text-gray-500 dol-bg-gray-200 dol-rounded dol-px-3 dol-py-2 hover:dol-text-white hover:dol-bg-secondary" href="<?php echo dollie()->get_sites_page_url(); ?>?customer=<?php echo $customer->ID; ?>">
								<i class="fas fa-wrench"></i>
								<span class="dol-ml-1"><?php printf(esc_html__('View %s', 'dollie-setup'), dollie()->get_site_type_string()); ?></span>
							</a>
						</div>
					</div>
				</div>
			<?php } ?>
		</div>
	<?php
	} else {
		echo 'No users found.';
	}
	?>



	<?php
	if (!empty($customers->results)) {
	?>
		<div class="dol-customers-pages" data-current-page="<?php echo esc_attr($current_page); ?>" data-list-type="<?php echo esc_attr($view_type); ?>">
			<?php
			$current_page = get_query_var('paged') ? (int) get_query_var('paged') : 1; // Example
			$num_pages    = $pages; // Example

			$edge_number_count = 2; // Change this, optional

			$start_number = $current_page - $edge_number_count;
			$end_number   = $current_page + $edge_number_count;

			// Minus one so that we don't split the start number unnecessarily, eg: "1 ... 2 3" should start as "1 2 3"
			if (($start_number - 1) < 1) {
				$start_number = 1;
				$end_number   = min($num_pages, $start_number + ($edge_number_count * 2));
			}

			// Add one so that we don't split the end number unnecessarily, eg: "8 9 ... 10" should stay as "8 9 10"
			if (($end_number + 1) > $num_pages) {
				$end_number   = $num_pages;
				$start_number = max(1, $num_pages - ($edge_number_count * 2));
			}

			if ($start_number > 1) {
				echo ' 1 ... ';
			}

			for ($i = $start_number; $i <= $end_number; $i++) {
				if ($i === $current_page) {
					echo '<a class="page-numbers current dol-bg-primary" href=' . get_permalink() . 'page/' . $i . '>' . $i . '</a>';
				} else {
					echo '<a class="page-numbers" href=' . get_permalink() . 'page/' . $i . '> ' . $i . '</a>';
				}
			}

			if ($end_number < $num_pages) {
				echo " ... {$num_pages} ";
			}
			?>
			<div>
			<?php
		}
			?>
			</div>
