<div class="blockquote-box blockquote-success clearfix">
  <div class="square pull-left">
    <i class="fal fa-truck"></i>
  </div>
  <h4>
    <strong><?php _e('Ready for the easiest migration ever?', DOLLIE_SLUG); ?></strong>
  </h4>
  <p>
    <?php printf(
      __('We are going make an exact copy of your current WordPress install to <strong>%s</strong>', DOLLIE_SLUG),
      $post_slug . DOLLIE_DOMAIN
    ); ?>
  </p>
</div>
<p>
  <span class="alert alert-info"><?php _e('Do not worry; your live site will not be touched or modified in any way!', DOLLIE_SLUG); ?></span>
</p>

<h4><?php _e('Step 1 - Install the Migrate Guru Plugin', DOLLIE_SLUG); ?></h4>
<ol>
  <li>
  <?php _e('Login to the WordPress Admin of the <strong>site you would like to migration</strong> (i.e yoursite.com)', DOLLIE_SLUG); ?>
  </li>
  <li>
    <?php _e('Go to <strong>Plugins > Add New </strong> and search for "Migrate Guru"', DOLLIE_SLUG); ?>
  </li>
  <li>
    <?php _e('Press the <strong>Install Now</strong> button.', DOLLIE_SLUG); ?>
  </li>
  <li>
    <?php _e('Activate the plugin', DOLLIE_SLUG); ?>
  </li>
  <li>
    <?php _e('Click on the Migrate Guru menu link in the WordPress Admin', DOLLIE_SLUG); ?>
  </li>
</ol>

<h4><?php _e('Step 2 - Fill in the Migration Details', DOLLIE_SLUG); ?></h4>
<ol>
  <li><?php _e('On the Settings page you will be asked to first leave your email to keep you up to date about the migration progress.', DOLLIE_SLUG); ?></li>
  <li>
    <?php _e('Click on Migrate Site to continue.', DOLLIE_SLUG); ?>
  </li>
  <li>
    <?php _e('Now choose <strong>FTP</strong> as your migration method, at the bottom right of the screen.', DOLLIE_SLUG); ?>
  </li>
  <li>
    <?php _e('Finally fill in the following settings for your migration.', DOLLIE_SLUG); ?>
    <div class="row p-0 mt-4 mb-4">
      <div class="clearfix"></div>
      <div class="col-sm-8">
        <div class="p-30 alert-info alert row">
          <div class="col-sm-12 mb-2">
            <?php _e('Destination Site URL', DOLLIE_SLUG); ?>
            <br><strong>https://<?php echo $hostname; ?></strong>
          </div>
          <div class="col-sm-12 mb-2">
            <?php _e('FTP Type', DOLLIE_SLUG); ?>
            <br><strong>SFTP</strong>
          </div>
          <div class="col-sm-6 mb-2">
            <?php _e('Destination Server IP/FTP Address', DOLLIE_SLUG); ?>
            <br><strong><?php echo $hostname; ?></strong>
          </div>
          <div class="col-sm-6 mb-2">
            <?php _e('Port:', DOLLIE_SLUG); ?>
            <br><strong><?php echo $request->containerSshPort; ?></strong>
          </div>
          <div class="clearfix"></div>
          <div class="col-sm-6 mb-2">
            <?php _e('FTP Username:', DOLLIE_SLUG); ?>
            <br><strong><?php echo $request->containerSshUsername; ?></strong>
          </div>
          <div class="col-sm-6 mb-2">
            <?php _e('FTP Password:', DOLLIE_SLUG); ?>
            <br><strong><?php echo $request->containerSshPassword; ?></strong><br>
          </div>
          <div class="col-sm-12 mb-0">
            <?php _e('Directory Path:', DOLLIE_SLUG); ?>
            <br><strong>
              <pre>/usr/src/app/</pre></strong><br>
          </div>
          <div class="clearfix"></div>
        </div>
        <div class="clearfix"></div>
      </div>
    </div>

  </li>
</ol>


<h4><?php _e('Step 3 - Sit back and enjoy the show!', DOLLIE_SLUG); ?></h4>

<?php _e('Press <strong>Migrate</strong> and sit back and enjoy the show. Depending on the size of your site and the speed of your current host this process could take up to a couple of hours. Do not worry, this is completely normal! We will send you an email when the migration has completed so you can easily continue to this setup wizard.
', DOLLIE_SLUG); ?>
