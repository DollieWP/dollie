<?php acf_form_head(); ?>


<div class="dol-min-h-full dol-mt-8 dol-pt-[100px]">

	<main class="dol-pb-8 dol--mt-24 dol-mx-auto sm:dol-px-6">

		<?php if ( get_option( 'options_wpd_wizard_completed' ) !== '1' ) { ?>

		<div class="dol-overflow-hidden dol-bg-white dol-rounded-lg dol-shadow dol-max-w-4xl">
			<div class="dol-p-6 dol-bg-white">
				<div class="sm:dol-flex sm:dol-items-center sm:dol-justify-between">
					<div class="sm:dol-flex sm:dol-space-x-5">
						<div class="dol-flex-shrink-0s">
							<img class="dol-w-20 dol-h-20 dol-mx-auto dol-rounded-full" src="<?php echo esc_url( DOLLIE_ASSETS_URL . 'wizard/agency.svg' ); ?>"
								alt="">
						</div>
						<div class="dol-mt-4 dol-text-center sm:dol-mt-0 sm:dol-pt-1 sm:dol-text-left">
							<h1 class="dol-text-sm dol-font-medium dol-text-gray-600">
								Please let us know a few things about how you would like to use your brand new Hub.
							</h1>



						</div>
					</div>
				</div>

				<?php
				$args = array(
					'post_id'      => 'options',
					'submit_value' => __( 'Continue', 'acf' ),
					'field_groups' => array( 'group_649b0ff9945a3deferjk' ), // this is the ID of the field group
				);
				acf_form( $args );
				?>

			</div>

		</div>

		<?php } else { ?>
		<div class="dol-top dol-mb-8">
			<?php dollie_setup_get_template_part( 'welcome-header' ); ?>

		</div>
		<div class="dol-content">
			<h1 class="dol-sr-only">Profile</h1>
			<div class="dol-grid dol-items-start dol-grid-cols-1 dol-gap-4 lg:dol-grid-cols-3 lg:dol-gap-8">
				<div class="dol-grid dol-grid-cols-1 dol-gap-4 lg:dol-col-span-2">

					<div class="dol-bg-white dol-p-5" style="margin-top: 50px;">

						<?php
						$args = array(
							'post_id'      => 'options',
							'field_groups' => array( 'group_5ada1549129fb' ), // this is the ID of the field group
						);
						acf_form( $args );
						?>
					</div>
				</div>

				<div class="dol-grid dol-grid-cols-1 dol-gap-4">
								<h4 class="dol-text-gray-500 dol-uppercase dol-tracking-wide dol-mb-1 dol-text-md">
						Watch our Onboarding Videos</h4>
						<?php dollie_setup_get_template_part( 'setup-complete' ); ?>
					<h4 class="dol-text-gray-500 dol-uppercase dol-tracking-wide dol-mb-1 dol-text-md">
						Watch our Onboarding Videos</h4>
					<iframe width="100%" height="320px"
						src="https://www.youtube.com/embed/videoseries?list=PL7IwOO3Ymw_kafLSDaOka790RMeMcr2QC"
						title="YouTube video player" frameborder="0"
						allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
						allowfullscreen></iframe>
				</div>
			</div>
		</div>

		<?php } ?>



	</main>
</div>
