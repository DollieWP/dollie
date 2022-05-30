<div class="dol-rounded dol-overflow-hidden dol-shadow dol-mb-6">
	<div class="dol-p-4 lg:dol-px-8 lg:dol-py-4 dol-bg-gray-200">
		<h4 class="dol-p-0 dol-m-0 dol-text-base md:dol-text-xl">
			<?php if ( $routes_active ) : ?>
				<?php esc_html_e( 'Live domain linked', 'dollie' ); ?>
			<?php else : ?>
				<?php esc_html_e( 'Your domain setup is not complete', 'dollie' ); ?>
			<?php endif; ?>
		</h4>
	</div>
	<div class="dol-p-4 lg:dol-px-8 lg:dol-py-6">
		<?php if ( $routes_active ) : ?>
			<div class="dol-mb-4"><?php esc_html_e( 'Congrats! You have linked your domain. You can always change your domain name by removing the current one and adding a new one.', 'dollie' ); ?></div>
		<?php else : ?>
			<div class="dol-mb-4"><?php esc_html_e( 'You\'re almost done. Your domain is missing the "A" record. Once you set that up correctly, your old domain will be replace with the new one!', 'dollie' ); ?></div>
		<?php endif; ?>
		
		<div class="dol-font-bold"><?php esc_html_e( 'Your linked domain:', 'dollie' ); ?></div>
		<ul class="dol-m-0 dol-p-0 dol-list-none dol-mb-6">
			<?php foreach ( $routes as $route ) : ?>
				<li class="dol-flex dol-items-center">
					<?php if ( ! $route['status'] ) : ?>
						<span class="dol-text-yellow-600"><?php echo dollie()->icon()->alert(); ?></span>
					<?php else : ?>
						<span class="dol-text-green-600"><?php echo dollie()->icon()->check(); ?></span>
					<?php endif; ?>
					<span class="dol-ml-2"><?php echo $route['name']; ?></span>
				</li>
			<?php endforeach; ?>
		</ul>

		<?php if ( $routes_active ) : ?>
			<p class="dol-mt-2"><?php esc_html_e( 'Please note that your linked domain will always have to point to the following IP, otherwise your site will be innacesible:', 'dollie' ); ?></p>
		<?php else : ?>
			<p class="dol-mt-2"><?php esc_html_e( 'Please make sure your domain is setup correctly:', 'dollie' ); ?></p>
		<?php endif; ?>
		<div class="dol-border-0 dol-border-b dol-border-t dol-border-solid dol-border-gray-200 dol-py-4 dol-px-10 dol-mb-6 dol-text-sm">
			<div class="dol-flex dol-flex-wrap dol-font-bold">
				<div class="dol-w-4/12"><?php esc_html_e( 'TYPE', 'dollie' ); ?></div>
				<div class="dol-w-4/12"><?php esc_html_e( 'CONTENT', 'dollie' ); ?></div>
				<div class="dol-w-4/12"><?php esc_html_e( 'IP ADDRESS', 'dollie' ); ?></div>
			</div>
			<div class="dol-flex dol-flex-wrap">
				<div class="dol-w-4/12">A</div>
				<div class="dol-w-4/12">@</div>
				<div class="dol-w-4/12"><?php echo esc_html( $credentials['ip'] ); ?></div>
			</div>
			<div class="dol-flex dol-flex-wrap">
				<div class="dol-w-4/12">A</div>
				<div class="dol-w-4/12">www</div>
				<div class="dol-w-4/12"><?php echo esc_html( $credentials['ip'] ); ?></div>
			</div>
		</div>

		<form action="<?php echo get_permalink( get_the_ID() ); ?>" method="post">
			<button name="remove_route" id="remove_route" type="submit" class="dol-px-4 dol-py-2 dol-bg-red-600 dol-text-white dol-rounded">
				<?php echo dollie()->icon()->delete(); ?>
				<?php esc_html_e( 'Remove Domain', 'dollie' ); ?>
			</button>
		</form>
	</div>
</div>
