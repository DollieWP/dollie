<div class="dol-rounded dol-overflow-hidden">
	<div class="dol-bg-gray-100 dol-rounded dol-p-4">
		<h5 class="dol-text-base dol-font-bold dol-mt-0 dol-mb-2">
			<?php esc_html_e( 'Set your domain\'s nameservers', 'dollie' ); ?>
		</h5>
		<ul class="dol-text-sm dol-list-inside dol-p-0">
			<li>
				<?php esc_html_e( 'Login to your domain registrar account.', 'dollie' ); ?>
			</li>
			<li>
				<?php esc_html_e( 'Set the following nameservers for the chosen domain name:', 'dollie' ); ?>
			</li>
		</ul>

		<ul class="dol-font-bold dol-list-none dol-p-0 dol-mt-3">
			<li>pdns1.stratus5.com</li>
			<li>pdns2.stratus5.com</li>
			<li>pdns3.stratus5.com</li>
		</ul>
		<div class="dol-mt-3 dol-text-sm">
			<?php
			esc_html_e( 'Please note that this process takes time. Once you set your domain\'s namesevers it can take up to 24-48 hours for them to propagate. Your website URL will not be changed until this process is confirmed!', 'dollie' );
			?>
		</div>
	</div>
</div>





