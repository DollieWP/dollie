<?php if ( $zone['status'] ) : ?>
	<div class="dol-rounded dol-overflow-hidden dol-shadow dol-mb-6">
		<div class="dol-p-4 lg:dol-px-8 lg:dol-py-4 dol-bg-gray-200">
			<h4 class="dol-p-0 dol-m-0 dol-text-base md:dol-text-xl">
				<?php esc_html_e( 'DNS manager', 'dollie' ); ?>
			</h4>
		</div>
		<div class="dol-p-4 lg:dol-px-8 lg:dol-py-6">
			<h5 class="dol-p-0 dol-m-0 dol-mb-4 dol-text-lg">
				<?php echo dollie()->icon()->dns( 'dol-mr-1' ); ?>
				<?php esc_html_e( 'Create new record', 'dollie' ); ?>
			</h5>

			<ul class="dol-dns-tabs">
				<li data-tab="#tab-a" class="dol-dns-menu-item dol-dns-menu-item-active">A</li>
				<li data-tab="#tab-aaaa" class="dol-dns-menu-item">AAAA</li>
				<li data-tab="#tab-cname" class="dol-dns-menu-item">CNAME</li>
				<li data-tab="#tab-mx" class="dol-dns-menu-item">MX</li>
				<li data-tab="#tab-txt" class="dol-dns-menu-item">TXT</li>
			</ul>
			<div class="dol-dns-tabs-content dol-relative">
				<div class="dol-loader dol-mt-0" data-for="add-dns-records">
					<div class="dol-flex dol-items-center dol-justify-center dol-h-full">
						<svg class="dol-animate-spin dol-h-10 dol-w-10 dol-text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
							<circle class="dol-opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
							<path class="dol-opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
						</svg>
					</div>
				</div>
				<div id="tab-a" class="dol-dns-tab dol-dns-tab-active">
					<div class="dol-text-sm dol-mb-2"><?php esc_html_e( 'Use @ to create the record at the root of the domain or enter a hostname to create it elsewhere. A records are for IPv4 addresses only and tell a request where your domain should direct to.', 'dollie' ); ?></div>

					<?php dollie()->load_template( 'widgets/site/pages/domain/manager/forms/a-record-form', [], true ); ?>
				</div>
				<div id="tab-aaaa" class="dol-dns-tab">
					<div class="dol-text-sm dol-mb-2"><?php esc_html_e( 'Use @ to create the record at the root of the domain or enter a hostname to create it elsewhere. AAAA records are for IPv6 addresses only and tell a request where your domain should direct to.', 'dollie' ); ?></div>

					<?php dollie()->load_template( 'widgets/site/pages/domain/manager/forms/aaaa-record-form', [], true ); ?>
				</div>
				<div id="tab-cname" class="dol-dns-tab">
					<div class="dol-text-sm dol-mb-2"><?php esc_html_e( 'CNAME records act as an alias by mapping a hostname to another hostname.', 'dollie' ); ?></div>
					<?php dollie()->load_template( 'widgets/site/pages/domain/manager/forms/cname-record-form', [], true ); ?>
				</div>
				<div id="tab-mx" class="dol-dns-tab">
					<div class="dol-text-sm dol-mb-2"><?php esc_html_e( 'MX records specify the mail servers responsible for accepting emails on behalf of your domain, and priority value if your provider has a number of mail servers for contingency.', 'dollie' ); ?></div>

					<?php dollie()->load_template( 'widgets/site/pages/domain/manager/forms/mx-record-form', [], true ); ?>
				</div>
				<div id="tab-txt" class="dol-dns-tab">
					<div class="dol-text-sm dol-mb-2"><?php esc_html_e( 'TXT records are used to associate a string of text with a hostname. These are primarily used for verification.', 'dollie' ); ?></div>
					<?php dollie()->load_template( 'widgets/site/pages/domain/manager/forms/txt-record-form', [], true ); ?>
				</div>
			</div>

			<h5 class="dol-p-0 dol-mt-6 dol-mb-4 dol-text-lg">
				<?php echo dollie()->icon()->dns( 'dol-mr-1' ); ?>
				<?php esc_html_e( 'Records', 'dollie' ); ?>
			</h5>

			<div id="dol-dns-manager-list">
				<?php

				dollie()->load_template(
					'widgets/site/pages/domain/manager/records',
					[
						'container' => $container,
					],
					true
				);
				?>
			</div>
		</div>
	</div>

	<form action="<?php echo $container->get_permalink( 'domains', [ 'remove-domain' => 'yes' ] ); ?>" method="post">
		<button name="remove_customer_dns" id="remove_customer_dns" type="submit" class="dol-px-4 dol-py-2 dol-bg-red-600 dol-text-white dol-rounded">
			<?php echo dollie()->icon()->close( 'dol-mr-1' ); ?>
			<?php esc_html_e( 'Remove domain', 'dollie' ); ?>
		</button>
	</form>
<?php else : ?>
	<div class="dol-my-6">
		<?php
		dollie()->load_template(
			'notice',
			[
				'type'    => 'info',
				'icon'    => 'fas fa-exclamation-circle',
				'title'   => sprintf( __( 'Please hold on whilst "%s" is getting ready to be used', 'dollie' ), $zone['domain'] ),
				'message' => __( 'Your domain\'s nameservers are being checked. Once we confirm all your nameservers are set correctly, we will automatically replace your website\'s URL and enable the DNS manager.', 'dollie' ),
			],
			true
		);
		?>

		<div class="dol-rounded dol-overflow-hidden dol-shadow dol-mt-6">
			<div class="dol-p-4 lg:dol-px-8 lg:dol-py-6">
				<h4 class="dol-text-lg"><?php esc_html_e( 'Your domain will get validated once we detect the following nameservers attached to it.', 'dollie' ); ?></h4>
				<ul class="dol-m-0">
					<li>pdns1.stratus5.com</li>
					<li>pdns2.stratus5.com</li>
					<li>pdns3.stratus5.com</li>
				</ul>
				<span class="dol-block dol-mb-4 dol-mt-6"><?php esc_html_e( 'Want to change the domain or have you typed in the wrong domain name? You can cancel at any time and try again!', 'dollie' ); ?></span>
				<form action="<?php echo $container->get_permalink( 'domains', [ 'remove-domain' => 'yes' ] ); ?>" method="post">
					<button name="remove_customer_dns" id="remove_customer_dns" type="submit" class="dol-px-4 dol-py-2 dol-bg-red-600 dol-text-white dol-rounded">
						<?php echo dollie()->icon()->close( 'dol-mr-1' ); ?>
						<?php esc_html_e( 'Cancel', 'dollie' ); ?>
					</button>
				</form>
			</div>
		</div>
	</div>
<?php endif; ?>
