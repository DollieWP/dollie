<?php

if ( ! isset( $targets ) ) {
	$targets = [];
}

?>

<div class="dol-recurring-success dol-hidden dol-text-sm dol-text-white dol-bg-green-500 dol-px-4 dol-py-2 dol-rounded dol-mb-3">
	<?php esc_html_e( 'Schedule created successfully! You can see all the scheduled actions in the "Scheduled actions" tab.', 'dollie' ); ?>
</div>
<div class="dol-loader dol-mt-0" data-for="recurring-actions-create">
	<div class="dol-flex dol-items-center dol-justify-center dol-h-full">
		<svg class="dol-animate-spin dol-h-10 dol-w-10 dol-text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
			<circle class="dol-opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
			<path class="dol-opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
		</svg>
	</div>
</div>
<form action="<?php echo esc_attr( admin_url( 'admin-ajax.php' ) ); ?>" method="POST" id="dol-schedule-form" data-nonce="<?php echo esc_attr( wp_create_nonce( 'dollie_create_recurring_action' ) ); ?>">
	<div class="dol-flex dol-flex-wrap dol--mx-2">
		<div class="dol-w-4/12 dol-px-2">
			<div class="dol-border dol-border-solid dol-border-gray-300 dol-rounded dol-p-4">
				<div class="dol-mb-4">
					<label for="dol-schedule-name" class="dol-font-bold dol-mb-2 dol-text-sm"><?php echo dollie()->icon()->task(); ?> <?php esc_html_e( 'Scheduled Task Name', 'dollie' ); ?></label>
					<input type="text" name="schedule-name" value="" placeholder="<?php esc_html_e( 'My schedule\'s name', 'dollie' ); ?>" id="dol-schedule-name" class="dol-px-2 dol-py-2 dol-border dol-border-solid dol-border-gray-300 dol-rounded" required>
				</div>
				<div class="dol-mb-4">
					<label for="dol-action" class="dol-font-bold dol-mb-2 dol-text-sm"><?php echo dollie()->icon()->task(); ?> <?php esc_html_e( 'Action', 'dollie' ); ?></label>
					<select name="action" id="dol-action" class="dol-py-2 dol-pl-4 dol-pr-6 dol-rounded" required>
						<option value="" selected disabled><?php esc_html_e( 'Select Action', 'dollie' ); ?></option>
						<?php foreach ( dollie()->bulk_actions()->get_allowed_commands() as $type => $label ) : ?>
							<option value="<?php echo $type; ?>"><?php echo $label; ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div>
					<label for="dol-interval" class="dol-font-bold dol-mb-2 dol-text-sm"><?php echo dollie()->icon()->clock(); ?><?php esc_html_e( 'Interval', 'dollie' ); ?></label>
					<select name="interval" id="dol-interval" class="dol-py-2 dol-pl-4 dol-pr-6 dol-rounded" required>
						<option value="" selected disabled><?php esc_html_e( 'Select Interval', 'dollie' ); ?></option>
						<?php foreach ( dollie()->recurring_actions()->get_allowed_intervals() as $interval_name => $interval_text ) : ?>
							<option value="<?php echo $interval_name; ?>"><?php echo $interval_text; ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
		</div>
		<div class="dol-w-8/12 dol-px-2">
			<div class="dol-border dol-border-solid dol-border-gray-300 dol-rounded dol-overflow-hidden">
				<div class="dol-check-wrap dol-p-4 dol-bg-gray-800">
					<label class="dol-flex dol-items-center">
						<input type="checkbox" class="dol-select-all-schedule dol-scale-125 checked:dol-bg-blue-500 dol-mr-2" />
						<span class="dol-text-white dol-text-sm"><?php esc_html_e( 'Select all', 'dollie' ); ?></span>
					</label>
				</div>
				<ul class="dol-schedule-create-list dol-list-none dol-m-0 dol-p-0">
					<?php foreach ( $targets as $target ) : ?>
						<li class="dol-schedule-list-item dol-px-4 dol-py-2 odd:dol-bg-white even:dol-bg-gray-100 dol-flex dol-items-center">
							<div class="dol-check-wrap dol-inline-block dol-mr-2">
								<label class="dol-flex dol-items-center">
									<input type="checkbox" name="containers[]" value="<?php echo esc_attr( $target['id'] ); ?>" class="dol-scale-125 checked:dol-bg-blue-500" required />
								</label>
							</div>
							<div class="dol-ml-2">
								<div class="dol-font-bold"><?php echo $target['name']; ?></div>
								<div class="dol-text-xs dol-truncate">
									<a href="<?php echo esc_url( $target['url'] ); ?>" target="_blank"><?php echo $target['url']; ?></a>
								</div>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
	</div>

	<button type="submit" class="dol-create-schedule dol-mt-3 dol-px-4 dol-py-2 dol-bg-primary-500 hover:dol-bg-primary-600 dol-border-0 dol-rounded dol-text-white dol-text-sm focus:dol-outline-none focus:dol-bg-primary-600">
		<?php echo dollie()->icon()->save(); ?> <?php esc_html_e( 'Save', 'dollie' ); ?>
	</button>
</form>
