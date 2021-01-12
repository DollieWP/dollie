<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$body_classes = [
	'wpd-preview-page',
];

if ( is_rtl() ) {
	$body_classes[] = 'rtl';
}

new \Dollie\Core\Modules\Preview();

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title><?php echo get_bloginfo( 'name' ); ?></title>
	<?php wp_head(); ?>
	<script>
		var ajaxurl = '<?php echo admin_url( 'admin-ajax.php', 'relative' ); ?>';
	</script>

	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>
</head>
<body class="<?php echo implode( ' ', $body_classes ); ?>">
<?php
do_action( 'wp_body_open' );

wp_footer();
?>
</body>
</html>
<?php
die();
