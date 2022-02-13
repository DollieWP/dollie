<?php

$allowed_types = [ 'success', 'error', 'info', 'warning' ];

$args_default = [
	'type'         => '',
	'icon'         => '',
	'title'        => '',
	'message'      => '',
	'links'        => [],
	'bottom_space' => false,
];

if ( isset( $args ) && is_array( $args ) ) {
	$args_default = array_merge( $args_default, $args );
}

if ( ! is_array( $args_default['links'] ) ) {
	$args_default['links'] = [];
}

if ( ! in_array( $args_default['type'], $allowed_types, true ) ) {
	$args_default['type'] = 'success';
}

$box_class     = '';
$icon_class    = 'dol-bg-green-600';
$bar_class     = 'dol-bg-green-500';
$message_class = '';

if ( $args_default['bottom_space'] ) {
	$box_class = 'dol-mb-4';
}

if ( 'error' === $args_default['type'] ) {
	$icon_class = 'dol-bg-red-700';
	$bar_class  = 'dol-bg-red-600';
} elseif ( 'info' === $args_default['type'] ) {
	$icon_class = 'dol-bg-primary-700';
	$bar_class  = 'dol-bg-primary-600';
} elseif ( 'warning' === $args_default['type'] ) {
	$icon_class = 'dol-bg-orange-700';
	$bar_class  = 'dol-bg-orange-600';
}

if ( ! empty( $args_default['links'] ) ) {
	$message_class = 'dol-mb-4';
}

?>

<div class="dol-border <?php do_action( 'dol_add_widget_classes' ); ?> dol-overflow-hidden <?php echo esc_attr( $box_class ); ?>">
	<div class="dol-flex dol-items-center <?php echo esc_attr( $bar_class ); ?>">
		<?php if ( $args_default['icon'] ) : ?>
			<div class="dol-p-4 lg:dol-px-8 lg:dol-py-4 dol-flex dol-items-center dol-justify-center dol-h-full <?php echo esc_attr( $icon_class ); ?>">
				<i class="<?php echo esc_attr( $args_default['icon'] ); ?> dol-text-white dol-text-xl md:dol-text-2xl"></i>
			</div>
		<?php endif; ?>
		<span class="dol-block dol-px-4 lg:dol-px-8 lg:dol-py-2 dol-m-0 dol-p-0 dol-text-white dol-text-sm md:dol-text-xl">
			<?php echo wp_kses_post( $args_default['title'] ); ?>
		</span>
	</div>
	<?php if ( ! empty( $args_default['message'] ) || ! empty( $args_default['links'] ) ) : ?>
		<div class="dol-p-4 lg:dol-px-8 lg:dol-py-6 dol-bg-ash-100">
			<?php if ( ! empty( $args_default['message'] ) ) : ?>
				<div class="dol-text-sm md:dol-text-base dol-text-ash-800 <?php echo esc_attr( $message_class ); ?>">
					<?php echo $args_default['message']; ?>
				</div>
			<?php endif; ?>
			<?php if ( ! empty( $args_default['links'] ) ) : ?>
				<div>
					<?php foreach ( $args_default['links'] as $link ) : ?>
						<?php $link_target = isset( $link['new_tab'] ) && $link['new_tab'] ? '_blank' : '_self'; ?>
						<a href="<?php echo esc_url( $link['url'] ); ?>"
						   target="<?php echo esc_attr( $link_target ); ?>"
						   class="dol-text-sm dol-text-white hover:dol-text-white dol-inline-block dol-px-4 dol-py-2 dol-bg-secondary-500 hover:dol-bg-secondary-700 dol-rounded">
							<?php echo esc_html( $link['title'] ); ?>
						</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</div>
