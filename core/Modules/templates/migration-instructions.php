<div class="blockquote-box blockquote-success clearfix">
    <div class="square pull-left">
        <i class="fal fa-truck"></i>
    </div>
    <h4>
        <strong>Ready for the easiest migration ever?</strong>
    </h4>
    <p>We are going make an exact copy of your current WordPress install to <strong>' . $post_slug . DOLLIE_DOMAIN .
            '</strong></p>
</div>
<p>
    <strong>Do not worry; your live site will not be touched or modified in any way!</strong>
</p>

<h4>Step 1 - Install the Easy Migration Plugin</h4>
<ol>
	<li><a href="<?php echo DOLLIE_PLUGIN_URL; ?>/setup/easy-migration.zip">Click here to download our Easy Migration
			Plugin</a></li>
	<li>Login to the WordPress Admin of your <strong>current</strong> WordPress site (yoursite.com)</li>
	<li>Go to <strong>Plugins > Add New > Upload Plugin</strong> and select the zip file of the Easy Migration plugin
		you just downloaded.
	</li>
	<li>Press the <strong>Install Now</strong> button.</li>
	<li>Activate the plugin!</li>
</ol>

<h4>Step 2 - Fill in the Migration Details</h4>

On the Migration Settings you will be asked to fill in your Site Details.

Copy and paste the values displayed below into the migration fields.
<div class="clearfix"></div>
<div class="col-sm-10 p-30 alert-info alert">
    <div class="col-sm-6 margin-bottom-half">
        Email<br> <strong>' . $user->user_email . '</strong>
    </div>
    <div class="col-sm-6 margin-bottom-half">
        Platform Site URL<br><strong>' . $hostname . '</strong>
    </div>
    <div class="clearfix"></div>
    <div class="col-sm-4 margin-bottom-half">
        SFTP Username: <br><strong>' . $request->containerSshUsername . '</strong>
    </div>
    <div class="col-sm-4 margin-bottom-half">
        Password:<br> <strong>' . $request->containerSshPassword . '</strong><br>
    </div>
    <div class="col-sm-4 margin-bottom-half">
        Port: <br><strong>' . $request->containerSshPort . '</strong>
    </div>
    <div class="clearfix"></div>
</div>
<div class="clearfix"></div>
<h4>Step 3 - Sit back and enjoy the show!</h4>

Press <strong>Start Site
    Migration</strong> and sit back and enjoy the show. Depending on the size of your site and the speed of your current host this process could take up to a couple of hours. Do not worry, this is completely normal! We will send you an email when the migration has completed so you can easily continue to this setup wizard.
