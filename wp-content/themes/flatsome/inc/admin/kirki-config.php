<?php
/**
 * Configuration for the Kirki Customizer
 */

if ( ! function_exists( 'flatsome_kirki_update_url' ) ) {
	function flatsome_kirki_update_url( $config ) {
		$config['url_path'] = get_template_directory_uri() . '/inc/admin/kirki/';

		return $config;
	}
}
add_filter( 'kirki_config', 'flatsome_kirki_update_url' );

/**
 * Disable default Kirki modules.
 *
 * @param array $modules List of default modules.
 *
 * @return array Filtered list of modules.
 */
function flatsome_kirki_modules( $modules ) {
	unset( $modules['css'] );
	unset( $modules['css-vars'] );
	unset( $modules['icons'] );
	unset( $modules['loading'] );
	unset( $modules['selective-refresh'] );
	unset( $modules['gutenberg'] );

	return $modules;
}

add_filter( 'kirki_modules', 'flatsome_kirki_modules' );

/**
 * Custom option sanitize callback.
 */
function flatsome_custom_sanitize( $content ) {
	return $content;
}

Flatsome_Option::add_config( 'option', array(
	'option_type'    => 'theme_mod',
	'capability'     => 'edit_theme_options',
	'disable_output' => true,
) );
