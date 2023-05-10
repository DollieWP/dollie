<?php acf_form_head(); ?>


<div class="dol-min-h-full dol-mt-8 dol-pt-[100px]">

    <main class="dol-pb-8 dol--mt-24 dol-mx-auto sm:dol-px-6 lg:dol-max-w-7xl">
        <div class="dol-top dol-mb-8">
        <?php dollie_setup_get_template_part('welcome-header'); ?>
        </div>
        <div class="dol-content">
            <h1 class="dol-sr-only">Profile</h1>
            <div class="dol-grid dol-items-start dol-grid-cols-1 dol-gap-4 lg:dol-grid-cols-3 lg:dol-gap-8">
                <div class="dol-grid dol-grid-cols-1 dol-gap-4 lg:dol-col-span-2">


                    <div class="dol-bg-white dol-p-5" style="margin-top: 50px;">

                        <?php
                        $args = [
                            'post_id' => 'options',
                            'field_groups' => ['group_5ada1549129fb'], // this is the ID of the field group
                        ];
                        acf_form($args);
                        ?>

                    </div>
                </div>

                <div class="dol-grid dol-grid-cols-1 dol-gap-4">
                    <h4 class="dol-text-gray-500 dol-uppercase dol-tracking-wide dol-mb-1 dol-text-md">
                        Setup Status</h4>
                    <?php dollie_setup_get_template_part('setup-complete'); ?>



                    <h4 class="dol-text-gray-500 dol-uppercase dol-tracking-wide dol-mb-1 dol-text-md">
                        Get Support</h4>
                    <?php dollie_setup_get_template_part('recent-sites'); ?>
                </div>
            </div>
        </div>
    </main>
</div>
