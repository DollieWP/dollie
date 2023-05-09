<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="dol-container dol-mr-6 dol-mt-6 dol-ml-3">

    <div class="dol-mx-auto dol-overflow-hidden dol-mb-6">
        <div class="dol-flex dol-flex-wrap dol-justify-center md:dol-justify-between dol-items-center">
            <div class="dol-text-center md:dol-text-right">
                <h1 class="dol-block dol-leading-normal dol-my-0"><span class="dol-font-light">Your Hub</span> Templates</h1>
            </div>

        </div>
    </div>

    <?php if ( ! empty( $message ) ) : ?>
    <div class="dol-text-lg dol-text-gray-700 dol-mt-2 dol-text-white dol-bg-blue-300 dol-p-4">
	    <?php echo $message;?>
    </div>
    <?php endif; ?>

    <div class="dol-text-lg dol-text-gray-700 dol-mt-2 ">
		<?php esc_html_e( 'Here you can import ready to use page templates for your preferred builder.', 'dollie' ); ?>
    </div>
    <div class="dol-text-md dol-text-gray-700 dol-mt-2 ">
    Importing our pre-made template will automatically add the Hub Pages to your site. Once imported you can customise them to your needs further.
    </div>
    <div class="dol-mt-5">
        <div class="dol-grid dol-grid-cols-1 md:dol-grid-cols-2 lg:dol-grid-cols-3 dol-gap-4">
			<?php foreach ( $templates as $template ) : ?>
                <div class="dol-rounded dol-shadow hover:dol-shadow-lg dol-overflow-hidden">
                    <div class="dol-aspect-w-16 dol-aspect-h-9">
                        <img alt="Template" class="dol-w-full dol-object-cover" src="<?php echo esc_url( $template['image'] ); ?>">
                    </div>
                    <div class="dol-bg-ash-100 dol-p-4">
                        <div class="dol-text-center dol-mb-8 dol-mt-2">
                            <h3 class="dol-m-0 dol-text-gray-800">
								<?php echo esc_html( $template['name'] ); ?>
                            </h3>
                        </div>

                        <div class="dol-text-center">
							<?php
                            if ( $template['is_imported'] ) {
								$text = esc_html__( 'Re-Import', 'dollie' );
							} else {
								$text = esc_html__( 'Import', 'dollie' );
							}
							?>
                            <div class="">
	                            <?php if (  $template['active'] ) : ?>
                                    <a href="<?php echo esc_url( $template['url'] ); ?>"
                                       onclick="return confirm('Are you sure you want to import the template?')"
                                       class="dol-flex dol-items-center dol-justify-center dol-no-underline dol-bg-gradient-to-r dol-from-green-500 dol-to-green-400 dol-text-md dol-text-white dol-text-xs hover:dol-text-white dol-font-bold dol-py-3 dol-px-4 dol-rounded">
                                        <i class="eicon-download-bold dol-mr-2"></i>
                                        <?php echo $text; ?>
                                    </a>
	                            <?php else:  ?>
                                    <div
                                       class="dol-flex dol-items-center dol-justify-center dol-no-underline dol-bg-gradient-to-r dol-from-gray-900 dol-to-gray-700 dol-text-md dol-text-white dol-text-xs hover:dol-text-white dol-font-bold dol-py-3 dol-px-4 dol-rounded">
			                            <?php echo $template['text_inactive']; ?>
                                    </div>
                                <?php endif; ?>

                            </div>
                        </div>
                    </div>
                </div>
			<?php endforeach; ?>
        </div>
    </div>
</div>
