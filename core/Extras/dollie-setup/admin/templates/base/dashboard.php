<?php acf_form_head(); ?>
<div class="dol-min-h-full dol-mt-8 dol-pt-[100px]">
    <main class="dol-pb-8 dol--mt-24">
        <div class="dol-px-4 dol-mx-auto">
            <h1 class="dol-sr-only">Profile</h1>
            <div class="dol-grid dol-items-start dol-grid-cols-1 dol-gap-4 lg:dol-grid-cols-3 lg:dol-gap-8">
                <div class="dol-grid dol-grid-cols-1 dol-gap-4 lg:dol-col-span-2">
                    <?php dollie_setup_get_template_part('welcome-header'); ?>
					<div class="dol-bg-white dol-p-5">
                    <?php
                    $args = [
                        'post_id' => 'options',
                        'field_groups' => ['group_5ada1549129fb'], // this is the ID of the field group
                    ];
                    acf_form($args);
                    ?>
					</div>


                    <?php dollie_setup_get_template_part('recent-sites'); ?>
                </div>



                <div class="dol-grid dol-grid-cols-1 dol-gap-4">
                    <?php dollie_setup_get_template_part('setup-complete'); ?>
                    <?php dollie_setup_get_template_part('recent-customers'); ?>
                    <?php dollie_setup_get_template_part('dollie-plugins'); ?>
                </div>
            </div>
        </div>
    </main>
</div>
