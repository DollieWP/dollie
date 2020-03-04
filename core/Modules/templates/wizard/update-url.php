<div class="blockquote-box blockquote-success clearfix">
    <div class="square pull-left">
        <i class="fal fa-wordpress"></i>
    </div>
    <h4>
		<?php _e( 'Final Step: Updating Your WordPress URL!', DOLLIE_SLUG ); ?>
    </h4>
    <p>
		<?php
		printf( __( 'We have almost completed setting up your domain! The last step is updating the temporary site URL <strong>%s to your live domain %s</strong>.', DOLLIE_SLUG ),
			$platform_url . DOLLIE_DOMAIN,
			$has_domain
		);
		?>
        <br><br>
		<?php _e( 'Just click on "Update My Domain" and our migration minions will do all the heavy lifting behind the scenes.', DOLLIE_SLUG ); ?>
    </p>
</div>
