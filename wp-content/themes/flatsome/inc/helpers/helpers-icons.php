<?php

function get_flatsome_icon( $name, $size = null ) {
	if ( $size ) $size = 'style="font-size:' . $size . ';"';

	return '<i class="' . $name . '" ' . $size . '></i>';
}

function flatsome_add_icons_css() {
	$theme     = wp_get_theme( get_template() );
	$version   = $theme->get( 'Version' );
	$fonts_url = get_template_directory_uri() . '/assets/css/icons';

	wp_add_inline_style(
		'flatsome-main',
		'@font-face {
				font-family: "fl-icons";
				font-display: block;
				src: url(' . $fonts_url . '/fl-icons.eot?v=' . $version . ');
				src:
					url(' . $fonts_url . '/fl-icons.eot#iefix?v=' . $version . ') format("embedded-opentype"),
					url(' . $fonts_url . '/fl-icons.woff2?v=' . $version . ') format("woff2"),
					url(' . $fonts_url . '/fl-icons.ttf?v=' . $version . ') format("truetype"),
					url(' . $fonts_url . '/fl-icons.woff?v=' . $version . ') format("woff"),
					url(' . $fonts_url . '/fl-icons.svg?v=' . $version . '#fl-icons) format("svg");
			}'
	);
}

add_action( 'wp_enqueue_scripts', 'flatsome_add_icons_css', 150 );

