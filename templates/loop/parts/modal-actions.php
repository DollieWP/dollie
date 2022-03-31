<div id="dol-modal-id-actions" class="dol-modal">
	<div class="dol-flex dol-justify-center dol-items-center dol-w-full dol-h-full">
		<div class="dol-bg-white dol-rounded dol-w-2/4 dol-overflow-hidden">
			<div class="dol-shadow-sm dol-bg-gray-100">
				<div class="dol-flex dol-justify-between dol-items-center">
					<div class="dol-bg-gray-500 dol-text-white dol-font-medium dol-flex dol-flex-row">
						<div class="dol-tab-action dol-tab-action-initial dol-tab-active dol-text-md dol-cursor-pointer dol-px-8 dol-py-3" data-tab-name="#dol-bulk-actions">
							<?php esc_html_e( 'Bulk Actions', 'dollie' ); ?>
						</div>
						<div class="dol-tab-action dol-text-md dol-cursor-pointer dol-px-8 dol-py-3" data-tab-name="#dol-scheduled-actions" data-tab-callback="getScheduledActions">
							<?php esc_html_e( 'Scheduled Actions', 'dollie' ); ?>
						</div>
						<div class="dol-tab-action dol-text-md dol-cursor-pointer dol-px-8 dol-py-3" data-tab-name="#dol-create-schedule" data-tab-callback="getScheduleTemplate">
							<?php esc_html_e( 'Create New Schedule', 'dollie' ); ?>
						</div>
					</div>
					<div class="dol-px-4">
						<span class="dol-modal-close dol-cursor-pointer"> <?php echo dollie()->icon()->clock(); ?></span>
					</div>
				</div>
			</div>

			<div id="dol-bulk-actions" class="dol-tab-inner dol-tab-active dol-p-8">
				<?php dollie()->load_template( 'loop/parts/modal-actions/bulk-actions', [], true ); ?>
			</div>

			<div id="dol-scheduled-actions" class="dol-tab-inner dol-p-8">
				<?php dollie()->load_template( 'loop/parts/modal-actions/scheduled-actions', [], true ); ?>
			</div>

			<div id="dol-create-schedule" class="dol-tab-inner dol-p-8">
				<?php dollie()->load_template( 'loop/parts/modal-actions/schedule-new-action', [], true ); ?>
			</div>
		</div>
	</div>
</div>
