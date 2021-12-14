<?php
/**
 * The overlay menu.
 *
 * @package flatsome
 */

$flatsome_mobile_overlay         = get_theme_mod( 'mobile_overlay' );
$flatsome_mobile_sidebar_classes = array( 'mobile-sidebar', 'no-scrollbar', 'mfp-hide' );
$flatsome_nav_classes            = array( 'nav', 'nav-sidebar', 'nav-vertical', 'nav-uppercase' );
$flatsome_levels                 = 0;

if ( 'center' == $flatsome_mobile_overlay ) {
	$flatsome_nav_classes[] = 'nav-anim';
}

if (
	'center' != $flatsome_mobile_overlay &&
	'slide' == get_theme_mod( 'mobile_submenu_effect' )
) {
	$flatsome_levels = (int) get_theme_mod( 'mobile_submenu_levels', '1' );

	$flatsome_mobile_sidebar_classes[] = 'mobile-sidebar-slide';
	$flatsome_nav_classes[]            = 'nav-slide';

	for ( $level = 1; $level <= $flatsome_levels; $level++ ) {
		$flatsome_mobile_sidebar_classes[] = "mobile-sidebar-levels-{$level}";
	}
}
?>
<div id="main-menu" class="<?php echo esc_attr( implode( ' ', $flatsome_mobile_sidebar_classes ) ); ?>"<?php echo $flatsome_levels ? ' data-levels="' . esc_attr( $flatsome_levels ) . '"' : ''; ?>>
	<div class="sidebar-menu no-scrollbar <?php if ( $flatsome_mobile_overlay == 'center') echo 'text-center'; ?>">
		<ul class="<?php echo esc_attr( implode( ' ', $flatsome_nav_classes ) ); ?>">
			<?php flatsome_header_elements( 'mobile_sidebar', 'sidebar' ); ?>
		</ul>
	</div>
</div>
