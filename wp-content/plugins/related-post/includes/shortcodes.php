<?php




if ( ! defined('ABSPATH')) exit; // if direct access 


add_shortcode('related_post', 'related_post_display');

function related_post_display($atts,$content = null) {

    $atts = shortcode_atts(
        array(
            'post_id' => "",
            'headline' => "",
            'view_type' => "",


        ), $atts);

    $related_post_settings = get_option( 'related_post_settings' );

    $post_id = isset($atts['post_id']) ? (int) $atts['post_id'] : get_the_ID();
    $post_type = get_post_type($post_id);

    $atts['settings'] = $related_post_settings;
    $atts['post_type'] = $post_type;

    $atts = apply_filters('related_post_atts', $atts);

    $view_type = isset($atts['view_type']) ?  $atts['view_type'] : 'grid';
    $layout_type = !empty($view_type) ? $view_type :  $related_post_settings['layout_type'];

    $font_aw_version = isset($related_post_settings['font_aw_version']) ? $related_post_settings['font_aw_version'] : 'none';


    require_once( related_post_plugin_dir . 'templates/related-post-hook.php');


    ob_start();

    ?>
    <div class="related-post <?php echo $layout_type; ?>">
        <?php

        do_action('related_post_main', $atts);

        ?>
    </div>
    <?php

    if($layout_type == 'slider'){
        wp_enqueue_script('owl.carousel');
        wp_enqueue_style('owl.carousel');
    }


    if($font_aw_version == 'v_5'){
        wp_enqueue_style('font-awesome-5');
    }elseif ($font_aw_version == 'v_4'){
        wp_enqueue_style('font-awesome-4');
    }


    wp_enqueue_style('related-post');

    return ob_get_clean();

}



















