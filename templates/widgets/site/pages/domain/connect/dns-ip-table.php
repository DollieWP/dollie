<div class="dol-border-0 dol-border-b dol-border-t dol-border-solid dol-border-gray-200 dol-py-4 dol-px-10">
	<div class="dol-flex dol-flex-wrap dol-font-bold">
		<div class="dol-w-4/12"><?php esc_html_e( 'TYPE', 'dollie' ); ?></div>
		<div class="dol-w-4/12"><?php esc_html_e( 'CONTENT', 'dollie' ); ?></div>
		<div class="dol-w-4/12"><?php esc_html_e( 'IP ADDRESS', 'dollie' ); ?></div>
	</div>
	<div class="dol-flex dol-flex-wrap">
		<div class="dol-w-4/12">A</div>
		<div class="dol-w-4/12">@</div>
		<div class="dol-w-4/12"><?php echo esc_html( $ip ); ?></div>
	</div>
	<div class="dol-flex dol-flex-wrap">
		<div class="dol-w-4/12">A</div>
		<div class="dol-w-4/12">www</div>
		<div class="dol-w-4/12"><?php echo esc_html( $ip ); ?></div>
	</div>
</div>