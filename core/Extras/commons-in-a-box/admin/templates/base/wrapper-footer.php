		</div>
		<div class="dol-ml-12 dol-flex-shrink-0">
			<img class="dol-h-64 dol-w-64" src="https://getdollie.com/wp-content/uploads/2019/10/wordpress.png" alt="Workflow">
		</div>
		</div>

		</div>
		<!-- /End replace -->
		</div>
		</main>
		</div>


		<ul class="subsubsub">
			<?php if (defined('CBOX_SHOW_PACKAGE_SWITCH') && true === constant('CBOX_SHOW_PACKAGE_SWITCH') && count(cbox_get_packages()) > 1) : ?>
				<li><a class="confirm" href="<?php echo esc_url(wp_nonce_url(self_admin_url('admin.php?page=cbox&amp;cbox-package=0'), 'cbox_select_package')); ?>"><?php esc_html_e('Change packages', 'commons-in-a-box'); ?></a></li>
			<?php endif; ?>
		</ul>
