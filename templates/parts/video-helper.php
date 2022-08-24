

<div id="dol-modal-<?php echo $modal_id;?>" class="dol-custom-modal">
	<div class="dol-flex dol-justify-center dol-items-center dol-w-full dol-h-full">
		<div class="dol-bg-white dol-rounded dol-w-1/2 dol-p-6">
			<div class="dol-mb-4">
				<div class="dol-flex dol-justify-between dol-items-center">
					<div class="dol-font-bold dol-text-2xl">
						<?php echo $title;?>
					</div>
					<div class="">
						<span class="dol-modal-close dol-cursor-pointer">
							<?php echo dollie()->icon()->close(); ?>
						</span>
					</div>
				</div>
			</div>

			<div class="dol-g">
				<div class="dol-aspect-w-16 dol-aspect-h-9">
				<iframe src="https://www.youtube.com/embed/<?php echo $embed_id;?>" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
				</div>
			</div>

		</div>
	</div>
</div>

