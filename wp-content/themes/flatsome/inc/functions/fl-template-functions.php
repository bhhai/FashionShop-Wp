<?php

/**
 * Display placeholder with tooltip message on header elements when they miss a resource.
 *
 * @param string $resource Name of the resource.
 */
function fl_header_element_error( $resource ) {
	$title = '';
	switch ( $resource ) {
		case 'woocommerce':
			$title = 'WooCommerce needed';
	}
	echo '<li><a class="element-error tooltip" title="' . esc_attr( $title ) . '">-</a></li>';
}

/**
 * Get flatsome_breadcrumb hooked content.
 *
 * @param string|array $class   One or more classes to add to the class list.
 * @param bool         $display Whether to display the breadcrumb (true) or return it (false).
 */
function flatsome_breadcrumb( $class = '', $display = true ) {
	do_action( 'flatsome_breadcrumb', $class, $display );
}

/**
 * Outputs the beginning markup of a sticky column.
 *
 * Outputs the markup directly if no theme modification name has been
 * given. Else based on the return value of the mod.
 *
 * @param string $name Theme modification name.
 */
function flatsome_sticky_column_open( $name = '' ) {
	if ( empty( $name ) || get_theme_mod( $name ) ) {
		echo '<div class="is-sticky-column">';
		echo '<div class="is-sticky-column__inner">';
	}
}

/**
 * Outputs the end markup of a sticky column.
 *
 * Outputs the markup directly if no theme modification name has been
 * given. Else based on the return value of the mod.
 *
 * @param string $name Theme modification name.
 */
function flatsome_sticky_column_close( $name = '' ) {
	if ( empty( $name ) || get_theme_mod( $name ) ) {
		echo '</div></div>';
	}
}

/**
 * @deprecated 3.7
 */
function get_flatsome_breadcrumbs() {
	_deprecated_function( __FUNCTION__, '3.7', 'flatsome_breadcrumb' );
	flatsome_breadcrumb();
}
