<?php
/**
 * Single Product tabs
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


/**
 * Filter tabs and allow third parties to add their own
 *
 * Each tab is an array containing title, callback and priority.
 * @see woocommerce_default_product_tabs()
 */
$product_tabs = apply_filters( 'woocommerce_product_tabs', array() );

if ( ! empty( $product_tabs ) ) : ?>
<div class="product-page-accordian">
	<div class="accordion" rel="<?php echo get_theme_mod( 'product_display', 'tabs' ) === 'accordian-collapsed' ? 0 : 1; ?>">
		<?php foreach ( $product_tabs as $key => $product_tab ) : ?>
		<div class="accordion-item">
			<a class="accordion-title plain" href="javascript:void();">
				<button class="toggle"><i class="icon-angle-down"></i></button>
				<?php echo apply_filters( 'woocommerce_product_' . $key . '_tab_title', $product_tab['title'], $key ); ?>
			</a>
			<div class="accordion-inner">
				<?php
				if ( isset( $product_tab['callback'] ) ) {
					call_user_func( $product_tab['callback'], $key, $product_tab );
				}
				?>
			</div>
		</div>
		<?php endforeach; ?>
	</div>
</div>
<?php endif; ?>
