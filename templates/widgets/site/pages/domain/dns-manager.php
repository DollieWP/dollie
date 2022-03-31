<?php if ( 'active' === $dns_manager ) : ?>

	<div class="dol-rounded dol-overflow-hidden dol-shadow dol-mb-6">
		<div class="dol-p-4 lg:dol-px-8 lg:dol-py-4 dol-bg-gray-200">
			<h4 class="dol-p-0 dol-m-0 dol-text-base md:dol-text-xl">
				<?php esc_html_e( 'DNS manager', 'dollie' ); ?>
			</h4>
		</div>
		<div class="dol-p-4 lg:dol-px-8 lg:dol-py-6">
			<h5 class="dol-p-0 dol-m-0 dol-mb-4 dol-text-lg">
				<?php echo dollie()->icon()->dns(); ?>
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

					<?php dollie()->load_template( 'widgets/site/pages/domain/forms/a-record-form', [], true ); ?>
				</div>
				<div id="tab-aaaa" class="dol-dns-tab">
					<div class="dol-text-sm dol-mb-2"><?php esc_html_e( 'Use @ to create the record at the root of the domain or enter a hostname to create it elsewhere. AAAA records are for IPv6 addresses only and tell a request where your domain should direct to.', 'dollie' ); ?></div>

					<?php dollie()->load_template( 'widgets/site/pages/domain/forms/aaaa-record-form', [], true ); ?>
				</div>
				<div id="tab-cname" class="dol-dns-tab">
					<div class="dol-text-sm dol-mb-2"><?php esc_html_e( 'CNAME records act as an alias by mapping a hostname to another hostname.', 'dollie' ); ?></div>
					<?php dollie()->load_template( 'widgets/site/pages/domain/forms/cname-record-form', [], true ); ?>
				</div>
				<div id="tab-mx" class="dol-dns-tab">
					<div class="dol-text-sm dol-mb-2"><?php esc_html_e( 'MX records specify the mail servers responsible for accepting emails on behalf of your domain, and priority value if your provider has a number of mail servers for contingency.', 'dollie' ); ?></div>

					<?php dollie()->load_template( 'widgets/site/pages/domain/forms/mx-record-form', [], true ); ?>
				</div>
				<div id="tab-txt" class="dol-dns-tab">
					<div class="dol-text-sm dol-mb-2"><?php esc_html_e( 'TXT records are used to associate a string of text with a hostname. These are primarily used for verification.', 'dollie' ); ?></div>
					<?php dollie()->load_template( 'widgets/site/pages/domain/forms/txt-record-form', [], true ); ?>
				</div>
			</div>

			<h5 class="dol-p-0 dol-mt-6 dol-mb-4 dol-text-lg">
				<?php echo dollie()->icon()->dns( 'dol-mr-2' ); ?>
				<?php esc_html_e( 'Records', 'dollie' ); ?>
			</h5>

			<div id="dol-dns-manager-list">
				<?php $records = dollie()->get_domain_records( dollie()->get_wp_site_data( 'uri', get_the_ID() ) ); ?>
				<?php
				dollie()->load_template(
					'widgets/site/pages/domain/records',
					[
						'records'      => $records,
						'container_id' => get_the_ID(),
					],
					true
				);
				?>
			</div>
		</div>
	</div>

<?php endif; ?>
