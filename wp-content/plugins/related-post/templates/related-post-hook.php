<?php

if ( ! defined('ABSPATH')) exit; // if direct access 


add_action('related_post_main' ,'related_post_main_title');

function related_post_main_title($atts){


    $settings = isset($atts['settings']) ? $atts['settings'] : array();

    //echo '<pre>'.var_export($settings, true).'</pre>';


    $post_type = isset($atts['post_type']) ?  $atts['post_type'] : 'post';

    $post_type_settings = $settings['post_types_display'][$post_type];

    //echo '<pre>'.var_export($post_type_settings, true).'</pre>';


    $headline_text= !empty($settings['headline_text']) ? $settings['headline_text'] : '';
    $headline_text= !empty($post_type_settings['headline_text']) ? $post_type_settings['headline_text'] : $headline_text;

    $headline_text = isset($atts['headline']) ?  $atts['headline'] : $headline_text;


    if(!empty($headline_text)):
        ?>
        <div  class="headline" ><?php echo $headline_text; ?></div>
        <?php
    endif;


}

add_action('related_post_main' ,'related_post_main_post_loop');

function related_post_main_post_loop($atts){

    $post_id = isset($atts['post_id']) ? (int) $atts['post_id'] : get_the_ID();
    $settings = isset($atts['settings']) ?  $atts['settings'] : array();


    $view_type = isset($atts['view_type']) ?  $atts['view_type'] : 'grid';

    $layout_type = !empty($view_type) ? $view_type :  $settings['layout_type'];


    $post_type = get_post_type( $post_id );
    $post_ids = pprp_post_ids_by_tax_terms($post_id);

    $orderby = isset($settings['orderby']) ? $settings['orderby'] : array('post__in');

    $order = isset($settings['order']) ? $settings['order'] : 'DESC';
    $max_post_count= isset($settings['max_post_count']) ? $settings['max_post_count'] : 5;

    $related_post_ids = get_post_meta( $post_id, 'related_post_ids', true );

    if(!empty($related_post_ids)){
        $post_ids = array_merge($related_post_ids, $post_ids);
        $orderby = array('post__in');
    }

    $orderby = (!empty($orderby) && is_array($orderby)) ? implode(' ', $orderby) : '';



    $args = array(
        'post_type' => !empty($related_post_ids) ? 'any' : $post_type,
        'post_status' => 'publish',
        'post__in'=> $post_ids,
        'post__not_in' => array($post_id),
        'orderby' => $orderby,
        'order' => $order,
        'showposts' => $max_post_count,
        'ignore_sticky_posts' => 1,
    );

    $args = apply_filters('related_post_query_args', $args);

    $wp_query_new = new WP_Query($args);

    $slider_class = ($layout_type=='slider') ? 'owl-carousel' : '';

    ?>
    <div class="post-list <?php echo $slider_class; ?>">

        <?php

        if ($wp_query_new->have_posts()) {

            while ($wp_query_new->have_posts()) : $wp_query_new->the_post();

                $loop_post_id = get_the_id();
                $atts['loop_post_id'] = get_the_id();

                ?>
                <div class="item">
                    <?php do_action('related_post_loop_item', $atts); ?>
                </div>
                <?php
            endwhile;

            //wp_reset_query();
            wp_reset_postdata();

        }

        ?>

    </div>
    <?php

}


add_action('related_post_loop_item' ,'related_post_loop_item');

function related_post_loop_item($atts){

    $loop_post_id = isset($atts['loop_post_id']) ? (int) $atts['loop_post_id'] : get_the_ID();
    $settings = isset($atts['settings']) ?  $atts['settings'] : array();

    $elements = isset($settings['elements']) ? $settings['elements'] : array();

    foreach ($elements as $elementIndex=> $elementData){

        $hide = isset($elementData['hide']) ? $elementData['hide'] : 'no';
        $elementData['settings'] = $settings;

        if($hide != 'yes'){
            do_action('related_post_loop_item_element_'.$elementIndex, $loop_post_id, $elementData);
        }


    }

}


add_action('related_post_loop_item_element_post_title', 'related_post_loop_item_element_post_title', 10, 2);
function related_post_loop_item_element_post_title($loop_post_id, $elementData){

    $settings = isset($elementData['settings']) ?  $elementData['settings'] : array();


    $post_link = get_permalink($loop_post_id);
    $post_title = get_the_title($loop_post_id);
    $icon = isset($elementData['icon']) ? $elementData['icon'] : '';

    $enable_stats = isset($settings['enable_stats']) ? $settings['enable_stats'] : 'disable';

    $post_link = ($enable_stats == 'enable') ? $post_link.'?related_post_from='.$loop_post_id : $post_link ;


    ?>

    <a class="title post_title" <?php echo apply_filters('related_post_element_link_attrs', 'post_title', $elementData); ?>  href="<?php echo $post_link; ?>">
        <?php
        if(!empty($icon)):
            ?>
            <span class="icon"><?php echo $icon;?></span>
            <?php
        endif;
        ?>
        <?php echo $post_title; ?>
    </a>

    <?php
}

add_action('related_post_loop_item_element_post_thumb', 'related_post_loop_item_element_post_thumb', 10, 2);
function related_post_loop_item_element_post_thumb($loop_post_id, $elementData){
    $settings = isset($elementData['settings']) ?  $elementData['settings'] : array();

    $thumb_size = isset($elementData['thumb_size']) ? $elementData['thumb_size'] : 'full';

    $post_thumb = wp_get_attachment_image_src( get_post_thumbnail_id($loop_post_id), $thumb_size );
    $thumb_url = isset($post_thumb['0']) ? $post_thumb['0'] : '';
    $post_link = get_permalink($loop_post_id);

    $enable_stats = isset($settings['enable_stats']) ? $settings['enable_stats'] : 'disable';

    $post_link = ($enable_stats == 'enable') ? $post_link.'?related_post_from='.$loop_post_id : $post_link;


    ?>
    <div class="thumb post_thumb">
        <a <?php echo apply_filters('related_post_element_link_attrs', 'post_thumb', $elementData); ?> href="<?php echo $post_link; ?>">
            <?php echo get_the_post_thumbnail($loop_post_id, $thumb_size); ?>
        </a>
    </div>
    <?php
}


add_action('related_post_loop_item_element_post_excerpt', 'related_post_loop_item_element_post_excerpt', 10, 2);
function related_post_loop_item_element_post_excerpt($loop_post_id, $elementData){

    $settings = isset($elementData['settings']) ?  $elementData['settings'] : array();

    //echo '<pre>'.var_export($elementData, true).'</pre>';
    $post_link = get_permalink($loop_post_id);
    $word_count = isset($elementData['word_count']) ? $elementData['word_count'] : 20;
    $read_more_text = !empty($elementData['read_more_text']) ? $elementData['read_more_text'] : __('Read more', 'related-post');
    $after_html = isset($elementData['after_html']) ? $elementData['after_html'] : '';


    $enable_stats = isset($settings['enable_stats']) ? $settings['enable_stats'] : 'disable';

    $post_link = ($enable_stats == 'enable') ? $post_link.'?related_post_from='.$loop_post_id : $post_link;


    $post = get_post($loop_post_id);
    $post_excerpt = $post->post_excerpt;
    $post_content = $post->post_content;
    $post_excerpt = !empty($post_excerpt) ? strip_tags($post_excerpt) : strip_tags($post_content);
    $post_excerpt = wp_trim_words( $post_excerpt , $word_count, ' <a '.apply_filters('related_post_element_link_attrs', 'post_excerpt', $elementData).' class="read-more" href="'.$post_link.'"> '.$read_more_text.'</a>' );



    ?>
    <p class="excerpt post_excerpt">
        <?php echo $post_excerpt; ?>
    </p>
    <?php
}





add_action('related_post_main' ,'related_post_main_css');

function related_post_main_css($atts){

    $settings = isset($atts['settings']) ? $atts['settings'] : array();

    $view_type = isset($atts['view_type']) ?  $atts['view_type'] : 'grid';
    $layout_type = !empty($view_type) ? $view_type :  $settings['layout_type'];

    $elements = isset($settings['elements']) ? $settings['elements'] : array();
    $item_width = isset($settings['item_width']) ? $settings['item_width'] : array();
    $grid_item_margin = isset($settings['grid_item_margin']) ? $settings['grid_item_margin'] : '10px';
    $grid_item_padding = isset($settings['grid_item_padding']) ? $settings['grid_item_padding'] : '0px';
    $grid_item_align = isset($settings['grid_item_align']) ? $settings['grid_item_align'] : 'left';

    $headline_text_font_size = isset($settings['headline_text_style']['font_size']) ? $settings['headline_text_style']['font_size'] : '';
    $headline_text_color = isset($settings['headline_text_style']['color']) ? $settings['headline_text_style']['color'] : '';
    $headline_text_custom_css = isset($settings['headline_text_style']['custom_css']) ? $settings['headline_text_style']['custom_css'] : '';

    //var_dump($item_width);
    $custom_css = isset($settings['custom_css']) ? $settings['custom_css'] : '';
    $custom_js = isset($settings['custom_js']) ? $settings['custom_js'] : '';



    ?>

    <script>

        <?php if(!empty($custom_js)): ?>
            <?php echo $custom_js; ?>
        <?php endif; ?>

    </script>
    <style type="text/css">
        .related-post{}
        .related-post .post-list{
        <?php if(!empty($grid_item_align)):?>
            text-align:<?php echo $grid_item_align; ?>;
        <?php endif; ?>
        }
        .related-post .post-list .item{
        <?php if(!empty($grid_item_width) && $layout_type == 'grid'):?>
            width:<?php echo $grid_item_width; ?>;
        <?php endif; ?>
        <?php if(!empty($grid_item_margin)):?>
            margin:<?php echo $grid_item_margin; ?>;
        <?php endif; ?>
        <?php if(!empty($grid_item_padding)):?>
            padding:<?php echo $grid_item_padding; ?>;
        <?php endif; ?>
        }
        .related-post .headline{
        <?php if(!empty($headline_text_font_size)): ?>
            font-size:<?php echo $headline_text_font_size; ?> !important;
        <?php endif; ?>
        <?php if(!empty($headline_text_color)): ?>
            color:<?php echo $headline_text_color; ?> !important;
        <?php endif; ?>
        }

        <?php if(!empty($headline_text_custom_css)): ?>
        <?php echo $headline_text_custom_css; ?>
        <?php endif; ?>

        <?php if(!empty($custom_css)): ?>
        <?php echo $custom_css; ?>
        <?php endif; ?>

        <?php



        if(!empty($elements)):
            foreach ($elements as $elementIndex  => $elementData){

                $font_size = isset($elementData['font_size']) ? $elementData['font_size'] : '14px';
                $font_color = isset($elementData['font_color']) ? $elementData['font_color'] : '#999';
                $margin = isset($elementData['margin']) ? $elementData['margin'] : '10px';
                $padding = isset($elementData['padding']) ? $elementData['padding'] : '0px';
                $line_height = isset($elementData['line_height']) ? $elementData['line_height'] : '';

                $custom_css = isset($elementData['custom_css']) ? $elementData['custom_css'] : '';


                if($elementIndex == 'post_thumb'){
                     $max_height = isset($elementData['max_height']) ? $elementData['max_height'] : '';
                    ?>
                    .related-post .post-list .item .<?php echo $elementIndex; ?>{
                        <?php if(!empty($max_height)): ?>
                            max-height:<?php echo $max_height; ?>;
                        <?php endif; ?>
                        <?php if(!empty($margin)): ?>
                            margin:<?php echo $margin; ?>;
                        <?php endif; ?>
                        <?php if(!empty($padding)): ?>
                            padding:<?php echo $padding; ?>;
                        <?php endif; ?>
                        <?php if(!empty($line_height)): ?>
                            line-height:<?php echo $line_height; ?>;
                        <?php endif; ?>
                        display: block;
                        <?php echo $custom_css; ?>
                    }
                    <?php

                }elseif ($elementIndex == 'post_title'){

                    ?>
                    .related-post .post-list .item .<?php echo $elementIndex; ?>{
                        <?php if(!empty($font_size)): ?>
                            font-size:<?php echo $font_size; ?>;
                        <?php endif; ?>
                        <?php if(!empty($font_color)): ?>
                            color:<?php echo $font_color; ?>;
                        <?php endif; ?>
                        <?php if(!empty($margin)): ?>
                            margin:<?php echo $margin; ?>;
                        <?php endif; ?>
                        <?php if(!empty($padding)): ?>
                            padding:<?php echo $padding; ?>;
                        <?php endif; ?>
                        <?php if(!empty($line_height)): ?>
                            line-height:<?php echo $line_height; ?>;
                        <?php endif; ?>
                        display: block;
                        text-decoration: none;
                        <?php echo $custom_css; ?>
                    }
                    <?php

                }elseif ($elementIndex == 'post_excerpt'){
                    ?>
                    .related-post .post-list .item .<?php echo $elementIndex; ?>{
                        <?php if(!empty($font_size)): ?>
                            font-size:<?php echo $font_size; ?>;
                        <?php endif; ?>
                        <?php if(!empty($font_color)): ?>
                            color:<?php echo $font_color; ?>;
                        <?php endif; ?>
                        <?php if(!empty($margin)): ?>
                            margin:<?php echo $margin; ?>;
                        <?php endif; ?>
                        <?php if(!empty($padding)): ?>
                            padding:<?php echo $padding; ?>;
                        <?php endif; ?>
                        <?php if(!empty($line_height)): ?>
                            line-height:<?php echo $line_height; ?>;
                        <?php endif; ?>
                        display: block;
                        text-decoration: none;
                        <?php echo $custom_css; ?>
                    }
                    <?php
                }else{
                    do_action('related_post_element_css_'.$elementIndex, $elementData );
                }
            }
        endif;

        ?>

        <?php

        if($layout_type=='slider'):

            ?>
            .related-post .owl-dots .owl-dot {
            <?php if(!empty($slider_pagination_bg)):?>
                background:<?php echo $slider_pagination_bg; ?>;
            <?php endif; ?>
            <?php if(!empty($slider_pagination_text_color)):?>
                color:<?php echo $slider_pagination_text_color; ?>;
            <?php endif; ?>
            }
            <?php
        endif;


        if($layout_type == 'grid' || $layout_type == 'list'){

            ?>
            @media only screen and (min-width: 1024px ){
                .related-post .post-list .item{
                    width: <?php echo isset($item_width['large']) ?  $item_width['large'] : ''; ?>;
                }
            }

            @media only screen and ( min-width: 768px ) and ( max-width: 1023px ) {
                .related-post .post-list .item{
                    width: <?php echo isset($item_width['medium']) ?  $item_width['medium'] : ''; ?>;
                }
            }

            @media only screen and ( min-width: 0px ) and ( max-width: 767px ){
                .related-post .post-list .item{
                    width: <?php echo isset($item_width['small']) ?  $item_width['small'] : ''; ?>;
                }
            }

            <?php



        }



        ?>



    </style>
    <?php

}


add_action('related_post_main' ,'related_post_main_slider_scripts');

function related_post_main_slider_scripts($atts){

    $settings = isset($atts['settings']) ? $atts['settings'] : array();

    $view_type = isset($atts['view_type']) ?  $atts['view_type'] : 'grid';
    $layout_type = !empty($view_type) ? $view_type :  $settings['layout_type'];

    $slider_column_number_desktop = isset($settings['slider']['column_desktop']) ? $settings['slider']['column_desktop'] : 3;
    $slider_column_number_tablet = isset($settings['slider']['column_tablet']) ? $settings['slider']['column_tablet'] : 2;
    $slider_column_number_mobile = isset($settings['slider']['column_mobile']) ? $settings['slider']['column_mobile'] : 1;
    $slider_slide_speed = isset($settings['slider']['slide_speed']) ? $settings['slider']['slide_speed'] : 1000;
    $slider_pagination_speed = isset($settings['slider']['pagination_speed']) ? $settings['slider']['pagination_speed'] : 1200;
    $slider_auto_play = isset($settings['slider']['auto_play']) ? $settings['slider']['auto_play'] : 'true';
    $slider_rewind = isset($settings['slider']['rewind']) ? $settings['slider']['rewind'] : 'true';
    $slider_loop = isset($settings['slider']['loop']) ? $settings['slider']['loop'] : 'true';
    $slider_center = isset($settings['slider']['center']) ? $settings['slider']['center'] : 'true';
    $slider_stop_on_hover = isset($settings['slider']['stop_on_hover']) ? $settings['slider']['stop_on_hover'] : 'true';
    $slider_navigation = isset($settings['slider']['navigation']) ? $settings['slider']['navigation'] : 'true';
    $slider_pagination = isset($settings['slider']['pagination']) ? $settings['slider']['pagination'] : 'true';
    $slider_pagination_count = isset($settings['slider']['pagination_count']) ? $settings['slider']['pagination_count'] : 'true';
    $slider_rtl = isset($settings['slider']['rtl']) ? $settings['slider']['rtl'] : 'true';

    $font_aw_version = isset($settings['font_aw_version']) ? $settings['font_aw_version'] : 'none';


    if($font_aw_version == 'v_5'){
        $navigation_text_prev = '<i class="fas fa-chevron-left"></i>';
        $navigation_text_next = '<i class="fas fa-chevron-right"></i>';
    }elseif ($font_aw_version == 'v_4'){
        $navigation_text_prev = '<i class="fa fa-chevron-left"></i>';
        $navigation_text_next = '<i class="fa fa-chevron-right"></i>';
    }else{
        $navigation_text_prev = '<i class="fas fa-chevron-left"></i>';
        $navigation_text_next = '<i class="fas fa-chevron-right"></i>';
    }


    if($layout_type=='slider'):
        ?>
        <script>
        jQuery(document).ready(function($){
            $(".related-post .post-list").owlCarousel({
                items :<?php echo $slider_column_number_desktop; ?>,
                responsiveClass:true,
                responsive:{
                    0:{
                        items:<?php echo $slider_column_number_mobile; ?>,
                    },
                    768:{
                        items:<?php echo $slider_column_number_tablet; ?>,
                    },
                    1200:{
                        items:<?php echo $slider_column_number_desktop; ?>,
                    }
                },
                <?php if(!empty($slider_rewind)): ?>
                rewind: <?php echo $slider_rewind; ?>,
                <?php endif;?>
                <?php if(!empty($slider_loop)): ?>
                loop: <?php echo $slider_loop; ?>,
                <?php endif;?>
                <?php if(!empty($slider_center)): ?>
                center: <?php echo $slider_center; ?>,
                <?php endif;?>
                <?php if(!empty($slider_auto_play)): ?>
                autoplay: <?php echo $slider_auto_play; ?>,
                autoplayHoverPause: <?php echo $slider_stop_on_hover; ?>,
                <?php endif;?>
                <?php if(!empty($slider_navigation)): ?>
                nav: <?php echo $slider_navigation; ?>,
                navSpeed: <?php echo $slider_slide_speed; ?>,
                navText : ['<?php echo $navigation_text_prev; ?>','<?php echo $navigation_text_next; ?>'],
                <?php endif;?>
                <?php if(!empty($slider_pagination)): ?>
                dots: <?php echo $slider_pagination; ?>,
                dotsSpeed: <?php echo $slider_pagination_speed; ?>,
                <?php endif;?>
                <?php if(!empty($slider_touch_drag)): ?>
                touchDrag: <?php echo $slider_touch_drag; ?>,
                <?php endif;?>
                <?php if(!empty($slider_mouse_drag)): ?>
                mouseDrag: <?php echo $slider_mouse_drag; ?>,
                <?php endif;?>
                <?php if(!empty($slider_rtl)): ?>
                rtl: <?php echo $slider_rtl; ?>,
                <?php endif;?>

            });
        });
        </script>
    <?php
    endif;

}

