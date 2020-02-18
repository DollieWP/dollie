<?php

namespace Dollie\Core\Shortcodes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dollie\Core\Singleton;
use WP_Query;

/**
 * Class Sites
 * @package Dollie\Core\Shortcodes
 */
final class Sites extends Singleton implements Base {

	/**
	 * Sites constructor.
	 */
	public function __construct() {
		parent::__construct();
		add_action( 'init', [ $this, 'register' ] );
	}

	/**
	 * Add shortcode
	 *
	 * @return mixed|void
	 */
	public function register() {
		add_shortcode( 'dollie-sites', [ $this, 'shortcode' ] );
	}

	/**
	 * Shortcode logic
	 *
	 * @param $atts
	 *
	 * @return bool|false|mixed|string
	 */
	public function shortcode( $atts ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		$a = shortcode_atts(
			[
				'amount'  => '15',
				'columns' => 1,
			],
			$atts
		);

		$query = new WP_Query( [
			'post_type'      => 'container',
			'posts_per_page' => $a['amount'],
			'paged'          => get_query_var( 'paged' ) ?: 1
		] );

		ob_start();

		if ( $query->have_posts() ) {
			echo '<div class="row fw-blueprint-listing">';

			while ( $query->have_posts() ) {
				$query->the_post();
				include( locate_template( '/loop-templates/sites.php' ) );
			}

			echo '</div>';
		}

		?>
		<?php if ( function_exists( 'wp_pagenavi' ) ) : ?>
			<?php wp_pagenavi( [ 'query' => $query ] ); ?>
		<?php else : ?>
            <div class="alignleft">
				<?php next_posts_link( __( '&laquo; Older Entries' ) ); ?>
            </div>
            <div class="alignright">
				<?php previous_posts_link( __( 'Newer Entries &raquo;' ) ); ?>
            </div>
		<?php endif; ?>
		<?php

		wp_reset_query();

		return ob_get_clean();
	}

}
