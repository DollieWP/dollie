<script type="text/javascript">
    jQuery(document).ready(function ($) {
        var field = $('#field_14_4');
		<?php if (isset( $_COOKIE['dollie_blueprint_id'] )) : ?>
        field.addClass("hidden");
		<?php endif; ?>
        if ($('#input_14_4 li').length === 0) {
            field.addClass("hidden");
        }

        $("#field_14_1 .ginput_container").append("<span class='domain-suffix'><strong><?php echo DOLLIE_DOMAIN; ?></strong></span>");
    });
</script>

<div data-backdrop="static" data-keyboard="false" class="modal" id="modal-large" tabindex="-1" role="dialog"
     aria-labelledby="modal-large" style="display: none;" aria-hidden="true">
    <div class="modal-dialog modal-lg text-center" role="document">
        <div class="modal-content mt-100">
            <div class="block block-themed block-transparent mb-0">
                <div class="loader-wrap bg-primary">

                    <div class="cube-wrapper">
                        <div class="cube-folding">
                            <span class="leaf1"></span>
                            <span class="leaf2"></span>
                            <span class="leaf3"></span>
                            <span class="leaf4"></span>
                        </div>
                    </div>
                </div>

                <div class="block-content mt-50 text-align-center pb-30 pl-50 pr-50 nice-copy-story">
                    <div id="content-1">
						<?php if ( get_field( 'wpd_launch_step_1', 'option' ) ) : ?>
							<?php the_field( 'wpd_launch_step_1', 'option' ); ?>
						<?php else : ?>
                            <h4 class="mt-0">
                                <i class="fab fa-docker"></i>
								<?php _e( 'Launching New Cloud Container', 'dollie' ); ?>
                                <span class="dots"></span>
                            </h4>
                            <p><?php _e( 'We use state-of-the-art technology like PHP7, Nginx, Redis, Memcached and
                                MariaDB inside isolated cloud containers to guarantee excellent performance
                                and security for each and every site on our platform.', 'dollie' ); ?></p>
						<?php endif; ?>
                    </div>
                    <div id="content-2">
						<?php if ( get_field( 'wpd_launch_step_2', 'option' ) ) : ?>
							<?php the_field( 'wpd_launch_step_2', 'option' ); ?>
						<?php else : ?>
                            <h4 class="mt-0">
                                <i class="fab fa-wordpress-simple"></i>
								<?php _e( 'Setting up WordPress', 'dollie' ); ?>
                                <span class="dots"></span>
                            </h4>
                            <p>
								<?php _e( 'We manage important WordPress security updates for you, and notify you when
                                compromised plugins and themes with security issues are found. And of course
                                free SSL certificates for your site are set up automatically.', 'dollie' ); ?>
                            </p>
						<?php endif; ?>
                    </div>

                    <div id="content-3">
						<?php if ( get_field( 'wpd_launch_step_3', 'option' ) ) : ?>
							<?php the_field( 'wpd_launch_step_3', 'option' ); ?>
						<?php else : ?>
                            <h4 class="mt-0">
                                <i class="fal fa-gem"></i>
								<?php _e( 'Testing & Verifying Installation', 'dollie' ); ?>
                                <span class="dots"></span>
                            </h4>
                            <p>
								<?php _e( 'We\'re running some automated tests to make sure everything is set up and
                                ready to go before you start building your brand new site!', 'dollie' ); ?>
                            </p>
						<?php endif; ?>
                    </div>
                    <div id="content-4">
						<?php if ( get_field( 'wpd_launch_step_4', 'option' ) ) : ?>
							<?php the_field( 'wpd_launch_step_4', 'option' ); ?>
						<?php else : ?>
                            <h4 class="mt-0">
                                <i class="fal fa-box-check"></i>
								<?php _e( 'Site Setup Complete', 'dollie' ); ?>
                                <span class="dots"></span>
                            </h4>
                            <p>
								<?php _e( 'Your new site Wordpress site is deployed to our cloud! You\'ll be redirected
                                to the site setup wizard in just a couple of seconds...', 'dollie' ); ?>
                            </p>
						<?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
