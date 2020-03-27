<div class="blockquote-box blockquote-success clearfix">
    <div class="square pull-left">
        <i class="fal fa-truck"></i>
    </div>
    <h4>
        <strong><?php esc_html_e( 'Ready for the easiest migration ever?', 'dollie' ); ?></strong>
    </h4>
    <p>
		<?php echo wp_kses_post( sprintf(
			__( 'We are going make an exact copy of your current WordPress install to <strong>%s</strong>', 'dollie' ),
			$post_slug . DOLLIE_DOMAIN
		) ); ?>
    </p>
</div>
<p>
    <span class="alert alert-info"><?php esc_html_e( 'Do not worry; your live site will not be touched or modified in any way!', 'dollie' ); ?></span>
</p>

<h4><?php esc_html_e( 'Step 1 - Install the Migrate Guru Plugin', 'dollie' ); ?></h4>
<ol>
    <li>
		<?php echo wp_kses_post( __( 'Login to the WordPress Admin of the <strong>site you would like to migrate</strong> (i.e yoursite.com)', 'dollie' ) ); ?>
    </li>
    <li>
		<?php echo wp_kses_post( __( 'Go to <strong>Plugins > Add New </strong> and search for "Migrate Guru"', 'dollie' ) ); ?>
    </li>
    <li>
		<?php echo wp_kses_post( __( 'Press the <strong>Install Now</strong> button.', 'dollie' ) ); ?>
    </li>
    <li>
		<?php esc_html_e( 'Activate the plugin', 'dollie' ); ?>
    </li>
    <li>
		<?php esc_html_e( 'Click on the Migrate Guru menu link in the WordPress Admin', 'dollie' ); ?>
    </li>
</ol>

<h4><?php esc_html_e( 'Step 2 - Fill in the Migration Details', 'dollie' ); ?></h4>
<ol>
    <li><?php esc_html_e( 'On the Settings page you will be asked to first leave your email to keep you up to date about the migration progress.', 'dollie' ); ?></li>
    <li>
		<?php esc_html_e( 'Click on Migrate Site to continue.', 'dollie' ); ?>
    </li>
    <li>
		<?php echo wp_kses_post( __( 'Now choose <strong>FTP</strong> as your migration method, at the bottom right of the screen.', 'dollie' ) ); ?>
    </li>
    <li>
		<?php esc_html_e( 'Finally fill in the following settings for your migration.', 'dollie' ); ?>
        <div class="row p-0 mt-4 mb-4">
            <div class="clearfix"></div>
            <div class="col-sm-8">
                <div class="p-30 alert-info alert row">
                    <div class="col-sm-12 mb-2">
						<?php esc_html_e( 'Destination Site URL', 'dollie' ); ?>
                        <br><strong>https://<?php echo $hostname; ?></strong>
                    </div>
                    <div class="col-sm-12 mb-2">
						<?php esc_html_e( 'FTP Type', 'dollie' ); ?>
                        <br><strong>SFTP</strong>
                    </div>
                    <div class="col-sm-6 mb-2">
						<?php esc_html_e( 'Destination Server IP/FTP Address', 'dollie' ); ?>
                        <br><strong><?php echo $hostname; ?></strong>
                    </div>
                    <div class="col-sm-6 mb-2">
						<?php esc_html_e( 'Port:', 'dollie' ); ?>
                        <br><strong><?php echo $request->containerSshPort; ?></strong>
                    </div>
                    <div class="clearfix"></div>
                    <div class="col-sm-6 mb-2">
						<?php esc_html_e( 'FTP Username:', 'dollie' ); ?>
                        <br><strong><?php echo $request->containerSshUsername; ?></strong>
                    </div>
                    <div class="col-sm-6 mb-2">
						<?php esc_html_e( 'FTP Password:', 'dollie' ); ?>
                        <br><strong><?php echo $request->containerSshPassword; ?></strong><br>
                    </div>
                    <div class="col-sm-12 mb-0">
						<?php esc_html_e( 'Directory Path:', 'dollie' ); ?>
                        <br><strong>
                            <pre>/usr/src/app/</pre>
                        </strong><br>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>

    </li>
</ol>

<h4><?php esc_html_e( 'Step 3 - Sit back and enjoy the show!', 'dollie' ); ?></h4>

<?php
echo wp_kses_post( __( 'Press <strong>Migrate</strong> and sit back and enjoy the show. Depending on the size of your site and the speed of your current host this process could take up to a couple of hours. Do not worry, this is completely normal! We will send you an email when the migration has completed so you can easily continue to this setup wizard.', 'dollie' ) );
