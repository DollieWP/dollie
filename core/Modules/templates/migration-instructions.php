<div class="blockquote-box blockquote-success clearfix">
    <div class="square pull-left">
        <i class="fal fa-truck"></i>
    </div>
    <h4>
        <strong><?php _e( 'Ready for the easiest migration ever?', DOLLIE_SLUG ); ?></strong>
    </h4>
    <p>
		<?php printf(
			__( 'We are going make an exact copy of your current WordPress install to <strong>%s</strong>', DOLLIE_SLUG ),
			$post_slug . DOLLIE_DOMAIN
		); ?>
    </p>
</div>
<p>
    <strong><?php _e( 'Do not worry; your live site will not be touched or modified in any way!', DOLLIE_SLUG ); ?></strong>
</p>

<h4><?php _e( 'Step 1 - Install the Easy Migration Plugin', DOLLIE_SLUG ); ?></h4>
<ol>
    <li>
        <a href="<?php echo DOLLIE_PLUGIN_URL; ?>/setup/easy-migration.zip">
			<?php _e( 'Click here to download our Easy Migration Plugin', DOLLIE_SLUG ); ?>
        </a>
    </li>
    <li>
		<?php _e( 'Login to the WordPress Admin of your <strong>current</strong> WordPress site (yoursite.com)', DOLLIE_SLUG ); ?>

    </li>
    <li>
		<?php _e( 'Go to <strong>Plugins > Add New > Upload Plugin</strong> and select the zip file of the Easy Migration plugin you just downloaded.', DOLLIE_SLUG ); ?>
    </li>
    <li>
		<?php _e( 'Press the <strong>Install Now</strong> button.', DOLLIE_SLUG ); ?>
    </li>
    <li>
		<?php _e( 'Activate the plugin!', DOLLIE_SLUG ); ?>
    </li>
</ol>

<h4><?php _e( 'Step 2 - Fill in the Migration Details', DOLLIE_SLUG ); ?></h4>

<?php _e( 'On the Migration Settings you will be asked to fill in your Site Details.', DOLLIE_SLUG ); ?>
<?php _e( 'Copy and paste the values displayed below into the migration fields.', DOLLIE_SLUG ); ?>

<div class="clearfix"></div>
<div class="col-sm-10 p-30 alert-info alert">
    <div class="col-sm-6 margin-bottom-half">
		<?php _e( 'Email', DOLLIE_SLUG ); ?>
        <br><strong><?php echo $user->user_email; ?></strong>
    </div>
    <div class="col-sm-6 margin-bottom-half">
		<?php _e( 'Platform Site URL', DOLLIE_SLUG ); ?>
        <br><strong><?php echo $hostname; ?></strong>
    </div>
    <div class="clearfix"></div>
    <div class="col-sm-4 margin-bottom-half">
		<?php _e( 'SFTP Username:', DOLLIE_SLUG ); ?>
        <br><strong><?php echo $request->containerSshUsername; ?></strong>
    </div>
    <div class="col-sm-4 margin-bottom-half">
		<?php _e( 'Password:', DOLLIE_SLUG ); ?>
        <br><strong><?php echo $request->containerSshPassword; ?></strong><br>
    </div>
    <div class="col-sm-4 margin-bottom-half">
		<?php _e( 'Port:', DOLLIE_SLUG ); ?>
        <br><strong><?php echo $request->containerSshPort; ?></strong>
    </div>
    <div class="clearfix"></div>
</div>
<div class="clearfix"></div>
<h4><?php _e( 'Step 3 - Sit back and enjoy the show!', DOLLIE_SLUG ); ?></h4>

<?php _e( 'Press <strong>Start Site Migration</strong> and sit back and enjoy the show. Depending on the size of your site and the speed of your current host this process could take up to a couple of hours. Do not worry, this is completely normal! We will send you an email when the migration has completed so you can easily continue to this setup wizard.
', DOLLIE_SLUG ); ?>
