<?php
if ( ! defined('ABSPATH')) exit; // if direct access 



function related_post_recursive_sanitize_arr($array) {

    foreach ( $array as $key => &$value ) {
        if ( is_array( $value ) ) {
            $value = related_post_recursive_sanitize_arr($value);
        }
        else {
            $value = wp_kses_post( $value );
        }
    }

    return $array;
}


add_action('the_content', 'related_post_display_auto');

function related_post_display_auto($content){

    $related_post_settings = get_option('related_post_settings');

    $post_types_display = isset($related_post_settings['post_types_display']) ? $related_post_settings['post_types_display'] : array();

    $post_id = get_the_ID();
    $post_type = get_post_type($post_id);

    //var_dump($post_types_display);

    $posttype = $post_type;
    $enable = isset($post_types_display[$posttype]['enable']) ? $post_types_display[$posttype]['enable'] : 'no';

    //var_dump($enable);

    if($enable == 'yes'){

        $content_position = isset($post_types_display[$posttype]['content_position']) ? $post_types_display[$posttype]['content_position'] : array('');
        $paragraph_positions = isset($post_types_display[$posttype]['paragraph_positions']) ? $post_types_display[$posttype]['paragraph_positions'] : '';
        $view_type = isset($post_types_display[$posttype]['view_type']) ? $post_types_display[$posttype]['view_type'] : '';
        $headline_text = isset($post_types_display[$posttype]['headline_text']) ? $post_types_display[$posttype]['headline_text'] : '';

        $related_post_html  = do_shortcode('[related_post post_id="'.$post_id.'" view_type="'.$view_type.'" headline="'.$headline_text.'"]');

        $paragraph_positions = !empty($paragraph_positions) ? explode(',', $paragraph_positions) : array();


        //var_dump($paragraph_positions);

        $html = '';

        if( in_array('before', $content_position)){

            $html .= do_shortcode('[related_post post_id="'.get_the_id().'" view_type="'.$view_type.'" headline="'.$headline_text.'"]');
        }







        if(!empty($paragraph_positions) && is_singular($post_type)){
            $split_by = "\n";
            $content_blocks = explode( $split_by, $content);
            $content_blocks = array_filter($content_blocks);
            $content_blocks_count = count($content_blocks);

            $positions = array();
            foreach ($paragraph_positions as $position){
                if(strpos($position, 'N') !== false){


                    $position = str_replace('N', $content_blocks_count, $position );

                    if(strpos($position, '-') !== false){

                        $position = explode('-', $position);

                        $max_pos = (int)$position[0];
                        $sub_pos = (int)$position[1];

                        $position = $max_pos - $sub_pos;
                    }

                    $positions[] = $position;
                }else{
                    $positions[] = $position;
                }
            }


            $content_html = '';

            $i = 1;
            foreach ($content_blocks as $content_block){

                if(in_array($i, $positions)){
                    $content_html .= $content_block.$related_post_html;
                }else{
                    $content_html .= $content_block;
                }

                $i++;
            }

            $html .= $content_html;

        }else{
            $html .= $content;
        }











        if( in_array('after', $content_position)){

            $html .= do_shortcode('[related_post post_id="'.get_the_id().'" view_type="'.$view_type.'" headline="'.$headline_text.'"]');

        }

        return $html;

    }else{
        return $content;
    }

    //var_dump($post_types_display);

}








add_action('the_excerpt', 'related_post_display_on_excerpt');

function related_post_display_on_excerpt($excerpt){

    $related_post_settings = get_option('related_post_settings');

    $post_types_display = isset($related_post_settings['post_types_display']) ? $related_post_settings['post_types_display'] : array();

    global $post;
    $posttype = isset($post->post_type) ? $post->post_type : '';
    $enable = isset($post_types_display[$posttype]['enable']) ? $post_types_display[$posttype]['enable'] : 'no';

    if($enable == 'yes'){

        $excerpt_position = isset($post_types_display[$posttype]['excerpt_position']) ? $post_types_display[$posttype]['excerpt_position'] : array();
        $headline_text = isset($post_types_display[$posttype]['headline_text']) ? $post_types_display[$posttype]['headline_text'] : '';
        $view_type = isset($post_types_display[$posttype]['view_type']) ? $post_types_display[$posttype]['view_type'] : '';


        $html = '';

        if( in_array('before', $excerpt_position)){

            $html .= do_shortcode('[related_post post_id="'.get_the_id().'" view_type="'.$view_type.'" headline="'.$headline_text.'"]');
        }
        $html .= $excerpt;

        if( in_array('after', $excerpt_position)){

            $html .= do_shortcode('[related_post post_id="'.get_the_id().'" view_type="'.$view_type.'" headline="'.$headline_text.'"]');

        }


        return $html;

    }else{
        return $excerpt;
    }

    //var_dump($post_types_display);

}

















add_filter('wp_head','related_post_count_stats');


function related_post_count_stats() {
	
	$related_post_settings = get_option( 'related_post_settings' );	
	$enable_stats = isset($related_post_settings['enable_stats']) ? $related_post_settings['enable_stats'] : 'disable';

	if($enable_stats != 'enable' ) return;

	$gmt_offset = get_option('gmt_offset');
	$date = date('Y-m-d', strtotime('+'.$gmt_offset.' hour'));
	
	
	if(is_singular() && !empty($_GET['related_post_from'])){

        $to_id = get_the_id();
		$related_post_from = sanitize_text_field($_GET['related_post_from']);

		global $wpdb;
		$table = $wpdb->prefix . "related_post_stats";	
		
		$wpdb->query( $wpdb->prepare("INSERT INTO $table 
									( id, from_id, to_id, date )
									VALUES	( %d, %d, %d, %s )",
									array	( '', $related_post_from, $to_id, $date)
									
									));
									
									
		//echo '<pre>'.var_export($_GET).'</pre>';
		}
	
	
	
	
	}





function related_post_is_archive_display($archives){


    if(is_front_page() && is_home()){

        if(in_array('front_page', $archives)){
            return true;
        }

    }elseif( is_front_page()){
        if(in_array('home', $archives)){
            return true;
        }

    }elseif( is_home()){
        if(in_array('blog', $archives)){
            return true;
        }
    }else if( is_tag()){
        if(in_array('post_tag', $archives)){
            return true;
        }
    }else if( is_category()){
        if(in_array('category', $archives)){
            return true;
        }
    }
    else if( is_tax()){

        $queried_object = get_queried_object();
        $taxonomy = $queried_object->taxonomy;
        //echo '<pre>'.var_export($taxonomy, true).'</pre>';
        if(in_array($taxonomy, $archives)){
            return true;
        }
    }


    else if(is_author()){
        if(in_array('author', $archives)){
            return true;
        }
    }else if(is_search()){
        if(in_array('search', $archives)){
            return true;
        }
    }else if(is_year()){
        if(in_array('year', $archives)){
            return true;
        }
    }else if(is_month()){
        if(in_array('month', $archives)){
            return true;
        }
    }else if(is_date()){
        if(in_array('date', $archives)){
            return true;
        }
    }else{
        return false;
    }


}

//add_filter('the_excerpt','related_post_excerpt_display_auto');


function related_post_excerpt_display_auto($excerpt) {


    $post_id = get_the_ID();
    $post_type = get_post_type( $post_id );
    $related_post_settings = get_option( 'related_post_settings' );
    $display_auto = !empty($related_post_settings['display_auto']) ? $related_post_settings['display_auto'] : '';
    $archives = !empty($related_post_settings['archives']) ? $related_post_settings['archives'] : array();

    $post_types = !empty($related_post_settings['post_types']) ? $related_post_settings['post_types'] : array();
    $excerpt_positions = !empty($related_post_settings['excerpt_positions']) ? $related_post_settings['excerpt_positions'] : array();


    $is_archive_display = related_post_is_archive_display($archives);
    //echo '<pre>'.var_export($is_archive_display, true).'</pre>';
    //echo '<pre>'.var_export($display_auto, true).'</pre>';

    $html = '';

    if($display_auto=='yes' && $is_archive_display && in_array($post_type, $post_types) && in_array('before', $excerpt_positions)){
        $html .= do_shortcode('[related_post post_id="'.$post_id.'"]');
    }

    $html .= $excerpt;

    if($display_auto=='yes' && $is_archive_display && in_array($post_type, $post_types) && in_array('after', $excerpt_positions)){
        $html .= do_shortcode('[related_post post_id="'.$post_id.'"]');
    }

    return $html;
}





function related_post_ajax_get_post_ids(){

			$response = array();
			$post_id 	= isset($_POST['post_id']) ? (int)sanitize_text_field($_POST['post_id']) : '';
			$title 	= isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
            $any_posttypes 	= isset($_POST['any_posttypes']) ? sanitize_text_field($_POST['any_posttypes']) : '';

			$post_type = get_post_type($post_id);
			$args = array('post_type'=> !empty($any_posttypes) ? 'any' : array($post_type), 's'=> $title, 'post__not_in'=> array($post_id), 'posts_per_page'=>10);
			$wp_query = new WP_Query($args);
			
			ob_start();
			
			if($wp_query->have_posts()):
			
				while ($wp_query->have_posts()) : $wp_query->the_post();

					$post_id = get_the_id();
					$post_title = get_the_title();
				
					?>
                    <div post_id="<?php echo $post_id; ?>" post_title="<?php echo $post_title; ?>" class="item">
                        <span class="icon-plus"><i class="far fa-plus-square"></i></span>
                        <span class="icon-add"><i class="fas fa-plus-square"></i></span>
                        <span class="title-text"><?php echo $post_title; ?></span>
                    </div>
                    <?php
				
				endwhile;
                wp_reset_postdata();
			
			endif;
			
			$response['html'] = ob_get_clean();
			
			echo json_encode($response);

		die();
	}

	add_action('wp_ajax_related_post_ajax_get_post_ids', 'related_post_ajax_get_post_ids');
	add_action('wp_ajax_nopriv_related_post_ajax_get_post_ids', 'related_post_ajax_get_post_ids');






function pprp_post_ids_by_tax_terms($post_id = 0){

    $post_id = !empty($post_id) ? $post_id : get_the_ID();
    $post_ids = array();
    $post_type = get_post_type( $post_id );
    $taxonomy_terms = related_post_get_taxonomy_terms($post_id);
		
    if(!empty($taxonomy_terms)) {
        foreach($taxonomy_terms as $taxonomy => $term_ids){
            foreach($term_ids as $term_id){
                $wp_query = new WP_Query(
                    array(
                        'post_type' => $post_type,
                        'post_status' => 'publish',
                        'tax_query' => array(
                            array(
                               'taxonomy' => $taxonomy,
                               'field' => 'id',
                               'terms' => $term_id,
                            )
                        )
                    )
                );

            if ( $wp_query->have_posts() ) :
                $i = 0;
                while ( $wp_query->have_posts() ) : $wp_query->the_post();
                    $post_ids[$i] = get_the_ID();
                    $i++;
                endwhile;
                wp_reset_postdata();
            endif;

            }

        }

        //remove current post id

        $current_post_id = array(get_the_ID());
        $post_ids = array_diff($post_ids, $current_post_id);

    }

			

        //var_dump($post_ids);
		
		return $post_ids;
		

}



function related_post_get_taxonomy_terms($post_id){

    // get post by post id
    $post = get_post($post_id);

    // get post type by post
    $post_type = $post->post_type;

    // get post type taxonomies
    $taxonomies = get_object_taxonomies($post_type);
	$post_taxonomies_terms = array();

	if(!empty($taxonomies))
    foreach ($taxonomies as $taxonomy) {        

        // get the terms related to post
        $terms = get_the_terms( $post->ID, $taxonomy );
        if ( !empty( $terms ) ) {
            $i = 0;
            foreach ( $terms as $term ){
                $post_taxonomies_terms[$taxonomy][$i] =$term->term_id;
                $i++;
            }
        }
    }

    return $post_taxonomies_terms;
}



