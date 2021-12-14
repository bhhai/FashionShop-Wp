<?php

// Instagram Feed
function ux_instagram_feed( $atts, $content = null ) {
	extract( shortcode_atts( array(
		'_id'                 => 'instagram-' . rand(),
		'photos'              => '10',
		'class'               => '',
		'visibility'          => '',
		'username'            => 'wonderful_places',
		'hashtag'             => '',
		'hashtag_media'       => 'top', // or recent
		'target'              => '_self',
		'caption'             => 'true',
		'link'                => '',
		// Layout.
		'columns'             => '5',
		'columns__sm'         => '',
		'columns__md'         => '',
		'type'                => 'row',
		'col_spacing'         => 'collapse',
		'slider_style'        => '',
		'slider_nav_color'    => '',
		'slider_nav_style'    => '',
		'slider_nav_position' => '',
		'slider_bullets'      => 'false',
		'width'               => '',
		'depth'               => '',
		'depth_hover'         => '',
		'animate'             => '',
		'auto_slide'          => '',
		'infinitive'          => 'true',
		// Image.
		'lightbox'            => '',
		'image_overlay'       => '',
		'image_hover'         => 'overlay-remove',
		'size'                => 'small', // small - thumbnail - original.
	), $atts ) );

	ob_start();

	$limit = $photos;

	if ( $username != '' ) {
		if ( substr( $username, 0, 1 ) === '#' ) {
			$hashtag = substr( $username, 1 );
		}

		$media_array = flatsome_instagram_get_feed( $username, $hashtag, $hashtag_media );

		if ( empty( $media_array ) ) {
			echo esc_html__( 'No images found.', 'flatsome-admin' );
		} elseif ( is_wp_error( $media_array ) ) {
			echo wp_kses_post( $media_array->get_error_message() );
		} else {

			// Slice list down to required limit.
			$media_array = array_slice( $media_array, 0, $limit );

			$repeater['id']                  = $_id;
			$repeater['type']                = $type;
			$repeater['class']               = $class;
			$repeater['visibility']          = $visibility;
			$repeater['style']               = 'overlay';
			$repeater['slider_style']        = $slider_nav_style;
			$repeater['slider_nav_position'] = $slider_nav_position;
			$repeater['slider_nav_color']    = $slider_nav_color;
			$repeater['slider_bullets']      = $slider_bullets;
			$repeater['auto_slide']          = $auto_slide;
			$repeater['infinitive']          = $infinitive;
			$repeater['row_spacing']         = $col_spacing;
			$repeater['row_width']           = $width;
			$repeater['columns']             = $columns;
			$repeater['columns__sm']         = $columns__sm;
			$repeater['columns__md']         = $columns__md;
			$repeater['depth']               = $depth;
			$repeater['depth_hover']         = $depth_hover;

			// Filters for custom classes.
			get_flatsome_repeater_start( $repeater );

			foreach ( $media_array as $item ) {
				echo '<div class="col"><div class="col-inner">';

				$image_url = $item['media_url']
					? set_url_scheme( $item['media_url'] )
					: '';

				if ( $caption ) {
					$caption = $item['description'];
				}
				?>
				<div class="img has-hover no-overflow">
					<div class="dark instagram-image-container image-<?php echo $image_hover; ?> instagram-image-type--<?php echo $item['type']; ?>">
						<a href="<?php echo $item['link']; ?>" target="_blank" rel="noopener noreferrer" class="plain">
							<?php echo flatsome_get_image( $image_url, false, $caption ); ?>
							<?php if ( $image_overlay ) { ?>
								<div class="overlay" style="background-color: <?php echo $image_overlay; ?>"></div>
							<?php } ?>
							<?php if ( $caption ) { ?>
								<div class="caption"><?php echo $caption; ?></div>
							<?php } ?>
						</a>
					</div>
				</div>
				<?php
				echo '</div></div>';
			}

			get_flatsome_repeater_end( $repeater );
		}
	}

	if ( $link != '' ) {
		?>
		<a class="plain uppercase" href="<?php echo trailingslashit( '//instagram.com/' . esc_attr( trim( $username ) ) ); ?>" rel="me"
			 target="<?php echo esc_attr( $target ); ?>"><?php echo get_flatsome_icon( 'icon-instagram' ); ?><?php echo wp_kses_post( $link ); ?></a>
		<?php
	}

	$w = ob_get_contents();

	ob_end_clean();

	return $w;

}

add_shortcode( 'ux_instagram_feed', 'ux_instagram_feed' );

function flatsome_instagram_get_feed( $username, $hashtag, $hashtag_media ) {
	$theme             = wp_get_theme( get_template() );
	$accounts          = flatsome_facebook_accounts();
	$username          = strtolower( $username );
	$username          = str_replace( '@', '', $username );
	$account           = array_key_exists( $username, $accounts ) ? $accounts[ $username ] : false;
	$transient_name    = 'flatsome_instagram';

	$transient_name .= '_' . str_replace( '-', '_', sanitize_title_with_dashes( $username ) );
	if ( $hashtag ) {
		$transient_name .= '_' . str_replace( '-', '_', sanitize_title_with_dashes( $hashtag ) );
		$transient_name .= '_' . $hashtag_media;
	}
	$transient_name .= '_' . ( $account ? 'account' : 'scrape' );
	$transient_name .= '_' . str_replace( array( '.', '-' ), '_', $theme['Version'] );

	$instagram = get_transient( $transient_name );

	if ( ! empty( $instagram ) ) {
		return unserialize( base64_decode( $instagram ) );
	}

	if ( $account ) {
		$instagram = flatsome_instagram_account_feed( $username, $account, $hashtag, $hashtag_media );
	} else {
		$instagram = flatsome_instagram_scrape_html( $username, $hashtag );
	}

	if ( is_wp_error( $instagram ) ) {
		return $instagram;
	}

	$instagram_cache = base64_encode( serialize( $instagram ) ); // 100% safe - ignore theme check nag
	set_transient( $transient_name, $instagram_cache, apply_filters( 'null_instagram_cache_time', HOUR_IN_SECONDS * 2 ) );

	return $instagram;
}

function flatsome_instagram_account_feed( $username, $account, $hashtag = '', $hashtag_media = 'top' ) {
	$access_token = array_key_exists( 'user_access_token', $account )
		? $account['user_access_token'] // For accounts connected prior to 31.08.20.
		: $account['access_token'];
	$id           = $account['id'];
	$results      = array();
	$instagram    = array();

	if ( $hashtag ) {
		$results = flatsome_instagram_get_hashtag_media( $hashtag, $hashtag_media, $id, $access_token );
	} else {
		$results = flatsome_instagram_get_media( $id, $access_token );
	}

	if ( is_wp_error( $results ) ) {
		return $results;
	} else if ( ! $results || empty( $results['data'] ) ) {
		return $instagram;
	}

	foreach ( $results['data'] as $item ) {
		$media_type = $item['media_type'];
		$permalink  = $item['permalink'];
		$caption    = ! empty( $item['caption'] )
			? wp_kses( $item['caption'], array() )
			: __( 'Instagram Image', 'flatsome-admin' );

		$timestamp = array_key_exists( 'timestamp', $item )
			? $item['timestamp']
			: null;

		$media_url = array_key_exists( 'media_url', $item )
			? $item['media_url']
			: null;

		if ( $media_type === 'CAROUSEL_ALBUM' && ! empty( $item['children']['data'] ) ) {
			$carousel_item = $item['children']['data'][0];
			$media_type    = $carousel_item['media_type'];
			if ( array_key_exists( 'media_url', $carousel_item ) ) {
				$media_url = $carousel_item['media_url'];
			}
		}

		if ( $media_type === 'VIDEO' || empty( $media_url ) ) {
			$response = flatsome_instagram_get_oembed_thumbnail( $permalink, $access_token );
			if ( is_wp_error( $response ) ) {
				$media_url = '';
			} elseif ( isset( $response['thumbnail_url'] ) ) {
				$media_url = $response['thumbnail_url'];
			}
		}

		$instagram[] = array(
			'type'        => strtolower( $media_type ),
			'description' => $caption,
			'link'        => $permalink,
			'time'        => $timestamp,
			'comments'    => $item['comments_count'],
			'likes'       => $item['like_count'],
			'media_url'   => $media_url,
		);
	}

	return $instagram;
}

function flatsome_instagram_get_oembed_cache( $permalink ) {
	$cache = get_option( 'flatsome_instagram_oembed_cache', array() );
	$parts = explode( '/', $permalink );
	$parts = array_filter( $parts );
	$id    = array_pop( $parts );

	if ( ! is_array( $cache ) ) $cache = array();

	if ( array_key_exists( $id, $cache ) ) {
		if ( isset( $cache[ $id ]['error'] ) ) {
			return $cache[ $id ]['cached_at'] + 300 > time()
				? new WP_Error( 'site_down', $cache[ $id ]['error'] )
				: false;
		} elseif ( $cache[ $id ]['cached_at'] + DAY_IN_SECONDS > time() ) {
			return $cache[ $id ];
		}
	}

	return false;
}

function flatsome_instagram_set_oembed_cache( $permalink, $data ) {
	$cache = get_option( 'flatsome_instagram_oembed_cache', array() );
	$parts = explode( '/', $permalink );
	$parts = array_filter( $parts );
	$id    = array_pop( $parts );

	if ( ! is_array( $cache ) ) $cache = array();

	if ( array_key_exists( $id, $cache ) ) {
		unset( $cache[ $id ] );
	}

	$data['cached_at'] = time();

	$cache = array_merge( array( $id => $data ), $cache );
	$cache = array_slice( $cache, 0, 500 );

	update_option( 'flatsome_instagram_oembed_cache', $cache, false );
}

function flatsome_instagram_get_oembed_thumbnail( $permalink, $access_token ) {
	$cache = flatsome_instagram_get_oembed_cache( $permalink );

	if ( $cache ) return $cache;

	$version  = flatsome_facebook_api_version();
	$fields   = 'thumbnail_url';
	$url      = "https://graph.facebook.com/$version/instagram_oembed?url=$permalink&fields=$fields&access_token=$access_token";
	$response = wp_remote_get( $url );

	if ( is_wp_error( $response ) ) {
		return new WP_Error( 'site_down', __( 'Unable to communicate with Instagram.', 'flatsome-admin' ) );
	} else {
		$body = json_decode( $response['body'], true );

		if ( array_key_exists( 'error', $body ) ) {
			flatsome_instagram_set_oembed_cache( $permalink, array(
				'error' => $body['error']['message'],
			) );
			return new WP_Error( 'site_down', $body['error']['message'] );
		}

		flatsome_instagram_set_oembed_cache( $permalink, $body );

		return $body;
	}
}

function flatsome_instagram_get_media( $id, $access_token ) {
	$version  = flatsome_facebook_api_version();
	$fields   = 'timestamp,caption,media_type,media_url,thumbnail_url,like_count,comments_count,permalink';
	$url      = "https://graph.facebook.com/$version/$id/media?fields=$fields&access_token=$access_token";
	$response = wp_remote_get( $url );

	if ( is_wp_error( $response ) ) {
		return new WP_Error( 'site_down', __( 'Unable to communicate with Instagram.', 'flatsome-admin' ) );
	} else {
		$body = json_decode( $response['body'], true );

		if ( array_key_exists( 'error', $body ) ) {
			return new WP_Error( 'site_down', $body['error']['message'] );
		}

		return $body;
	}
}

function flatsome_instagram_get_hashtag_id( $hashtag, $user_id, $access_token ) {
	if ( substr( $hashtag, 0, 1 ) === '#' ) {
		$hashtag = substr( $hashtag, 1 );
	}

	$version  = flatsome_facebook_api_version();
	$url      = "https://graph.facebook.com/$version/ig_hashtag_search?user_id=$user_id&q=$hashtag&access_token=$access_token";
	$response = wp_remote_get( $url );

	if ( is_wp_error( $response ) ) {
		return new WP_Error( 'site_down', __( 'Unable to communicate with Instagram.', 'flatsome-admin' ) );
	} else {
		$body = json_decode( $response['body'], true );

		if ( array_key_exists( 'error', $body ) ) {
			return new WP_Error( 'site_down', $body['error']['message'] );
		}

		return $body ;
	}
}

function flatsome_instagram_get_hashtag_media( $name, $type, $user_id, $access_token ) {
	$hashtag = flatsome_instagram_get_hashtag_id( $name, $user_id, $access_token );

	if ( is_wp_error( $hashtag ) ) {
		return $hashtag;
	}

	$tag_id = $hashtag['data'][ 0 ]['id'];
	$version  = flatsome_facebook_api_version();
	$endpoint = $type === 'recent' ? 'recent_media' : 'top_media';
	$fields   = 'caption,media_type,media_url,like_count,comments_count,permalink';
	$url      = "https://graph.facebook.com/$version/$tag_id/$endpoint?user_id=$user_id&fields=$fields&access_token=$access_token";
	$response = wp_remote_get( $url );

	if ( is_wp_error( $response ) ) {
		return new WP_Error( 'site_down', __( 'Unable to communicate with Instagram.', 'flatsome-admin' ) );
	} else {
		$body = json_decode( $response['body'], true );

		if ( array_key_exists( 'error', $body ) ) {
			return new WP_Error( 'site_down', $body['error']['message'] );
		}

		return $body;
	}
}

function flatsome_instagram_scrape_html( $username, $hashtag ) {
	$req_param = ( $hashtag ) ? 'explore/tags/' . $hashtag : trim( $username );
	$remote    = wp_remote_get( 'https://instagram.com/' . $req_param );
	$instagram = array();

	if ( is_wp_error( $remote ) ) {
		return new WP_Error( 'site_down', esc_html__( 'Unable to communicate with Instagram.', 'flatsome-admin' ) );
	}

	if ( 200 != wp_remote_retrieve_response_code( $remote ) ) {
		return new WP_Error( 'invalid_response', esc_html__( 'Instagram did not return a 200.', 'flatsome-admin' ) );
	}

	$shards      = explode( 'window._sharedData = ', $remote['body'] );
	$insta_json  = explode( ';</script>', $shards[1] );
	$insta_array = json_decode( $insta_json[0], true );

	if ( ! $insta_array ) {
		return new WP_Error( 'bad_json', esc_html__( 'Instagram has returned invalid data.', 'flatsome-admin' ) );
	}

	if ( isset( $insta_array['entry_data']['ProfilePage'][0]['graphql']['user']['edge_owner_to_timeline_media']['edges'] ) ) {
		$edges = $insta_array['entry_data']['ProfilePage'][0]['graphql']['user']['edge_owner_to_timeline_media']['edges'];
	} elseif ( $hashtag && isset( $insta_array['entry_data']['TagPage'][0]['graphql']['hashtag']['edge_hashtag_to_media']['edges'] ) ) {
		$edges = $insta_array['entry_data']['TagPage'][0]['graphql']['hashtag']['edge_hashtag_to_media']['edges'];
	} else {
		return new WP_Error( 'bad_json_2', esc_html__( 'Instagram has returned invalid data.', 'flatsome-admin' ) );
	}

	if ( ! is_array( $edges ) ) {
		return new WP_Error( 'bad_array', esc_html__( 'Instagram has returned invalid data.', 'flatsome-admin' ) );
	}

	foreach ( $edges as $edge ) {
		$edge['node']['thumbnail_src'] = preg_replace( '/^https?\:/i', '', $edge['node']['thumbnail_src'] );
		$edge['node']['display_url']   = preg_replace( '/^https?\:/i', '', $edge['node']['display_url'] );

		if ( isset( $edge['node']['thumbnail_resources'] ) && is_array( $edge['node']['thumbnail_resources'] ) ) {
			$edge['node']['thumbnail'] = $edge['node']['thumbnail_resources'][0]['src']; // 150x150
			$edge['node']['small']     = $edge['node']['thumbnail_resources'][2]['src']; // 320x320
		} else {
			$edge['node']['thumbnail'] = $edge['node']['small'] = $edge['node']['thumbnail_src'];
		}

		$edge['node']['large'] = $edge['node']['thumbnail_src'];

		if ( $edge['node']['is_video'] == true ) {
			$type = 'video';
		} else {
			$type = 'image';
		}

		$caption = __( 'Instagram Image', 'flatsome-admin' );
		if ( ! empty( $edge['node']['edge_media_to_caption']['edges'][0]['node']['text'] ) ) {
			$caption = wp_kses( $edge['node']['edge_media_to_caption']['edges'][0]['node']['text'], array() );
		}

		$instagram[] = array(
			'type'        => $type,
			'description' => $caption,
			'link'        => trailingslashit( '//instagram.com/p/' . $edge['node']['shortcode'] ),
			'time'        => $edge['node']['taken_at_timestamp'],
			'comments'    => $edge['node']['edge_media_to_comment']['count'],
			'likes'       => $edge['node']['edge_liked_by']['count'],
			'media_url'   => $edge['node']['display_url'],
		);
	}

	return $instagram;
}
