<?php


if ( ! defined('ABSPATH')) exit;  // if direct access 
	

add_action('related_post_settings_content_general', 'related_post_settings_content_general');

if(!function_exists('related_post_settings_content_general')) {
    function related_post_settings_content_general($tab){


        //delete_option('related_post_settings');

        $pickp_settings_tabs_field = new pickp_settings_tabs_field();

        $related_post_settings = get_option( 'related_post_settings' );

        $display_auto = isset($related_post_settings['display_auto']) ? $related_post_settings['display_auto'] : 'yes';
        $post_types = isset($related_post_settings['post_types']) ? $related_post_settings['post_types'] : array();
        $post_types_display = isset($related_post_settings['post_types_display']) ? $related_post_settings['post_types_display'] : array();

        $headline_text = isset($related_post_settings['headline_text']) ? $related_post_settings['headline_text'] : __('Related Posts','related-post');
        $headline_text_font_size = isset($related_post_settings['headline_text_style']['font_size']) ? $related_post_settings['headline_text_style']['font_size'] : '';
        $headline_text_color = isset($related_post_settings['headline_text_style']['color']) ? $related_post_settings['headline_text_style']['color'] : '';
        $headline_text_custom_css = isset($related_post_settings['headline_text_style']['custom_css']) ? $related_post_settings['headline_text_style']['custom_css'] : '';


        $content_positions = isset($related_post_settings['content_positions']) ? $related_post_settings['content_positions'] : array();
        $excerpt_positions = isset($related_post_settings['excerpt_positions']) ? $related_post_settings['excerpt_positions'] : array();

        $paragraph_positions = isset($related_post_settings['paragraph_positions']) ? $related_post_settings['paragraph_positions'] : array();

        $archives = isset($related_post_settings['archives']) ? $related_post_settings['archives'] : array();

        $archives_array = array('front_page'=>__('Front page','related-post'), 'home' => __('Home','related-post'), 'blog' => __('Blog','related-post'), 'author' => __('Author ','related-post'), 'search' => __('Search','related-post'), 'year' => __('Year','related-post'), 'month' => __('Month','related-post'), 'date' => __('Date','related-post'));
        $all_post_types = get_post_types();
        $taxonomies = get_object_taxonomies( $all_post_types );


        foreach ($taxonomies as $taxonomy){
            $the_taxonomy = get_taxonomy($taxonomy);

            $archives_array[$taxonomy] = $the_taxonomy->labels->name;

        }



       // echo '<pre>'.var_export($related_post_settings, true).'</pre>';

        ?>
        <div class="section">
            <div class="section-title"><?php echo __('General settings', 'related-post'); ?></div>
            <p class="description section-description"><?php echo __('Choose some general option to getting started.', 'related-post'); ?></p>

            <?php


            $post_types_list = get_post_types( '', 'names' );
            $post_types_array = array();

            foreach ( $post_types_list as $post_type ) {

                $obj = get_post_type_object($post_type);
                $singular_name = $obj->labels->singular_name;
                $post_types_array[$post_type] = $singular_name;
            }

            //echo '<pre>'.var_export($post_types_array, true).'</pre>';





            ob_start();


            ?>
            <div class="templates_editor expandable">
                <?php

                //$post_types = apply_filters('wishlist_posttypes', array('post'=>'Post', 'page' => 'Page'));
                //$post_types = $post_types_list;....


                //var_dump($post_types_list);

                unset($post_types_list['nav_menu_item']);
                unset($post_types_list['custom_css']);
                unset($post_types_list['customize_changeset']);
                unset($post_types_list['oembed_cache']);
                unset($post_types_list['user_request']);
                unset($post_types_list['wp_block']);
                unset($post_types_list['revision']);




                if(!empty($post_types_list))
                    foreach($post_types_list as $post_type => $post_name){


                        $enable = isset($post_types_display[$post_type]['enable']) ? $post_types_display[$post_type]['enable'] : 'no';
                        $content_position = isset($post_types_display[$post_type]['content_position']) ? $post_types_display[$post_type]['content_position'] : array();
                        $excerpt_position = isset($post_types_display[$post_type]['excerpt_position']) ? $post_types_display[$post_type]['excerpt_position'] : array();

                        $description = isset($post_types_display[$post_type]['description']) ? $post_types_display[$post_type]['description'] : '';


                        $paragraph_positions = isset($post_types_display[$post_type]['paragraph_positions']) ? $post_types_display[$post_type]['paragraph_positions'] : '';
                        $view_type = isset($post_types_display[$post_type]['view_type']) ? $post_types_display[$post_type]['view_type'] : '';
                        $headline_text = isset($post_types_display[$post_type]['headline_text']) ? $post_types_display[$post_type]['headline_text'] : '';



                        //echo '<pre>'.var_export($enable).'</pre>';

                        ?>
                        <div class="item template <?php //echo $post_type; ?>">
                            <div class="header">
                                <span title="<?php echo __('Click to expand', 'job-board-manager'); ?>" class="expand ">
                                    <i class="fa fa-expand"></i>
                                    <i class="fa fa-compress"></i>
                                </span>

                                <?php
                                if($enable =='yes'):
                                    ?>
                                    <span title="<?php echo __('Enable', 'job-board-manager'); ?>" class="is-enable ">
                                        <i class="fa fa-check-square"></i>
                                    </span>
                                        <?php
                                else:
                                    ?>
                                    <span title="<?php echo __('Disabled', 'job-board-manager'); ?>" class="is-enable ">
                                    <i class="fa fa-times-circle"></i>
                                    </span>
                                    <?php
                                endif;
                                ?>


                                <?php echo $post_name; ?>
                            </div>
                            <input type="hidden" name="wishlist_settings[post_types_display][<?php echo $post_type; ?>][name]" value="<?php echo $post_type; ?>" />
                            <div class="options">
                                <div class="description"><?php echo $description; ?></div><br/><br/>

                                <?php

                                $args = array(
                                    'id'		=> 'enable',
                                    'parent'		=> 'related_post_settings[post_types_display]['.$post_type.']',
                                    'title'		=> __('Enable?','related-post'),
                                    'details'	=> sprintf(__('Enable or disable related post automatically for %s.','related-post'), $post_type),
                                    'type'		=> 'select',
                                    'value'		=> $enable,
                                    'default'		=> 'no',
                                    'style'		=> array('inline' => true),
                                    'args'		=> array('yes' => 'Yes', 'no'=> 'No'),

                                );

                                $pickp_settings_tabs_field->generate_field($args);


                                $args = array(
                                    'id'		=> 'content_position',
                                    'parent'		=> 'related_post_settings[post_types_display]['.$post_type.']',
                                    'title'		=> __('Content positions','related-post'),
                                    'details'	=> __('Display before or after content.','related-post'),
                                    'type'		=> 'checkbox',
                                    'value'		=> $content_position,
                                    'default'		=> array(),
                                    'style'		=> array('inline' => true),
                                    'args'		=> array('before' => 'Before', 'after'=> 'After'),

                                );

                                $pickp_settings_tabs_field->generate_field($args);

                                $args = array(
                                    'id'		=> 'excerpt_position',
                                    'parent'		=> 'related_post_settings[post_types_display]['.$post_type.']',
                                    'title'		=> __('Excerpt positions','related-post'),
                                    'details'	=> __('Display before or after excerpt.','related-post'),
                                    'type'		=> 'checkbox',
                                    'value'		=> $excerpt_position,
                                    'default'		=> array(),
                                    'style'		=> array('inline' => true),
                                    'args'		=> array('before' => 'Before', 'after'=> 'After'),

                                );

                                $pickp_settings_tabs_field->generate_field($args);









                                $args = array(
                                    'id'		=> 'view_type',
                                    'parent'		=> 'related_post_settings[post_types_display]['.$post_type.']',
                                    'title'		=> __('View type','related-post'),
                                    'details'	=> __('Choose view type.','related-post'),
                                    'type'		=> 'select',
                                    'value'		=> $view_type,
                                    'default'		=> array(),
                                    'style'		=> array('inline' => true),
                                    'args'		=> array('grid' => 'Grid', 'slider'=> 'Slider'),

                                );

                                $pickp_settings_tabs_field->generate_field($args);


                                $args = array(
                                    'id'		=> 'paragraph_positions',
                                    'parent'		=> 'related_post_settings[post_types_display]['.$post_type.']',
                                    'title'		=> __('Paragraph positions','related-post'),
                                    'details'	=> __('Display related post after n\'th paragraph. N is total paragraph count, use comma to separate.','related-post'),
                                    'type'		=> 'text',
                                    'value'		=> $paragraph_positions,
                                    'default'		=> '',
                                    'placeholder'		=> '1,2,N-1',
                                );

                                $pickp_settings_tabs_field->generate_field($args);



                                $args = array(
                                    'id'		=> 'headline_text',
                                    'parent'		=> 'related_post_settings[post_types_display]['.$post_type.']',
                                    'title'		=> __('Headline text','related-post'),
                                    'details'	=> __('Custom text for related post headline..','related-post'),
                                    'type'		=> 'text',
                                    'value'		=> $headline_text,
                                    'default'		=> '',
                                    'placeholder'		=> '',
                                );

                                $pickp_settings_tabs_field->generate_field($args);


                                ?>


                            </div>

                        </div>
                        <?php

                    }


                ?>


            </div>
            <?php


            $html = ob_get_clean();




            $args = array(
                'id'		=> 'post_types',
                //'parent'		=> '',
                'title'		=> __('Post types display','job-board-manager'),
                'details'	=> __('Display automatically wishlist under following post types content and excerpt.','job-board-manager'),
                'type'		=> 'custom_html',
                //'multiple'		=> true,
                'html'		=> $html,
            );

            $pickp_settings_tabs_field->generate_field($args);



            $args = array(
                'id'		=> 'headline_text_style',
                'title'		=> __('Headline text style','related-post'),
                'details'	=> __('Customize headline text.','related-post'),
                'type'		=> 'option_group',
                'options'		=> array(
                    array(
                        'id'		=> 'font_size',
                        'parent'		=> 'related_post_settings[headline_text_style]',
                        'title'		=> __('Font size','related-post'),
                        'details'	=> __('Set custom font size, ex: 18px','related-post'),
                        'type'		=> 'text',
                        'value'		=> $headline_text_font_size,
                        'default'		=> '18px',
                        'placeholder'   => '18px',
                    ),

                    array(
                        'id'		=> 'color',
                        'parent'		=> 'related_post_settings[headline_text_style]',
                        'title'		=> __('Color','related-post'),
                        'details'	=> __('Set custom font color, ex: 18px','related-post'),
                        'type'		=> 'colorpicker',
                        'value'		=> $headline_text_color,
                        'default'		=> '#999999',
                        'placeholder'   => '#999999',
                    ),

                    array(
                        'id'		=> 'custom_css',
                        'parent'		=> 'related_post_settings[headline_text_style]',
                        'title'		=> __('Custom CSS','related-post'),
                        'details'	=> __('Set custom css, do not use &lt;style>&lt;/style> tag,use <strong>!important</strong> to override.','related-post'),
                        'type'		=> 'textarea',
                        'value'		=> $headline_text_custom_css,
                        'default'		=> '',
                        'placeholder'   => '.related-post .headline{
border:1px solid #999;
}',
                    ),



                ),

            );

            $pickp_settings_tabs_field->generate_field($args);



            ?>


        </div>
    <?php


    }
}


add_action('related_post_settings_content_style', 'related_post_settings_content_style');

if(!function_exists('related_post_settings_content_style')) {
    function related_post_settings_content_style($tab){

        $pickp_settings_tabs_field = new pickp_settings_tabs_field();

        $related_post_settings = get_option( 'related_post_settings' );

        $layout_type = isset($related_post_settings['layout_type']) ? $related_post_settings['layout_type'] : 'grid';
        $grid_item_margin = isset($related_post_settings['grid_item_margin']) ? $related_post_settings['grid_item_margin'] : '10px';
        $grid_item_padding = isset($related_post_settings['grid_item_padding']) ? $related_post_settings['grid_item_padding'] : '0px';
        $grid_item_align = isset($related_post_settings['grid_item_align']) ? $related_post_settings['grid_item_align'] : 'left';
        $font_aw_version = isset($related_post_settings['font_aw_version']) ? $related_post_settings['font_aw_version'] : 'none';

        $item_width_large = isset($related_post_settings['item_width']['large']) ? $related_post_settings['item_width']['large'] : '45%';
        $item_width_medium = isset($related_post_settings['item_width']['medium']) ? $related_post_settings['item_width']['medium'] : '90%';
        $item_width_small = isset($related_post_settings['item_width']['small']) ? $related_post_settings['item_width']['small'] : '90%';

        //echo '<pre>'.var_export($related_post_settings, true).'</pre>';
        //delete_option('related_post_settings');
        ?>
        <div class="section">
            <div class="section-title"><?php echo __('Style settings', 'related-post'); ?></div>
            <p class="description section-description"><?php echo __('Choose & customize style settings.', 'related-post'); ?></p>

            <?php

            $args = array(
                'id'		=> 'layout_type',
                'parent'		=> 'related_post_settings',
                'title'		=> __('Layout type','related-post'),
                'details'	=> __('Choose layout type.','related-post'),
                'type'		=> 'select',
                'value'		=> $layout_type,
                'default'		=> 'grid',
                'args'		=> array('grid'=>__('Grid','related-post'), 'slider'=>__('Slider','related-post')  ),
            );

            $pickp_settings_tabs_field->generate_field($args);





            $args = array(
                'id'		=> 'item_width',
                'title'		=> __('Item width','related-post'),
                'details'	=> __('Set item width.','related-post'),
                'type'		=> 'option_group',
                'options'		=> array(
                    array(
                        'id'		=> 'large',
                        'parent'		=> 'related_post_settings[item_width]',
                        'title'		=> __('In desktop','related-post'),
                        'details'	=> __('min-width: 1200px, ex: 45% or 280px','related-post'),
                        'type'		=> 'text',
                        'value'		=> $item_width_large,
                        'default'		=> '45%',
                        'placeholder'   => '45%',
                    ),
                    array(
                        'id'		=> 'medium',
                        'parent'		=> 'related_post_settings[item_width]',
                        'title'		=> __('In tablet & small desktop','related-post'),
                        'details'	=> __('min-width: 992px, ex: 90% or 280px','related-post'),
                        'type'		=> 'text',
                        'value'		=> $item_width_medium,
                        'default'		=> '90%',
                        'placeholder'   => '90%',
                    ),
                    array(
                        'id'		=> 'small',
                        'parent'		=> 'related_post_settings[item_width]',
                        'title'		=> __('In mobile','related-post'),
                        'details'	=> __('max-width: 768px, ex: 90% or 280px','related-post'),
                        'type'		=> 'text',
                        'value'		=> $item_width_small,
                        'default'		=> '90%',
                        'placeholder'   => '90%',
                    ),
                ),

            );

            $pickp_settings_tabs_field->generate_field($args);









            $args = array(
                'id'		=> 'grid_item_margin',
                'parent'		=> 'related_post_settings',
                'title'		=> __('Item margin','related-post'),
                'details'	=> __('Set item margin. ex: 5px 10px','related-post'),
                'type'		=> 'text',
                'value'		=> $grid_item_margin,
                'default'		=> '5px',
                'placeholder'   => '5px 10px',
            );

            $pickp_settings_tabs_field->generate_field($args);

            $args = array(
                'id'		=> 'grid_item_padding',
                'parent'		=> 'related_post_settings',
                'title'		=> __('Item padding','related-post'),
                'details'	=> __('Set item padding. ex: 5px 10px','related-post'),
                'type'		=> 'text',
                'value'		=> $grid_item_padding,
                'default'		=> '0px',
                'placeholder'   => '5px 10px',
            );

            $pickp_settings_tabs_field->generate_field($args);

            $args = array(
                'id'		=> 'grid_item_align',
                'parent'		=> 'related_post_settings',
                'title'		=> __('Item text align','related-post'),
                'details'	=> __('Set item text align.','related-post'),
                'type'		=> 'select',
                'value'		=> $grid_item_align,
                'default'		=> 'left',
                'args'		=> array('left'=>__('Left','related-post'), 'center'=>__('Center','related-post'), 'right'=>__('Right','related-post')  ),
            );

            $pickp_settings_tabs_field->generate_field($args);

            $args = array(
                'id'		=> 'font_aw_version',
                'parent'		=> 'related_post_settings',
                'title'		=> __('Font-awesome version','related-post'),
                'details'	=> __('Choose font awesome version you want to load.','related-post'),
                'type'		=> 'select',
                'value'		=> $font_aw_version,
                'default'		=> 'none',
                'args'		=> array('v_5'=>__('Version 5+','related-post'), 'v_4'=>__('Version 4+','related-post'), 'none'=>__('None','related-post')  ),
            );

            $pickp_settings_tabs_field->generate_field($args);


            ?>


        </div>
        <?php


    }
}


add_action('related_post_settings_content_query', 'related_post_settings_content_query');

if(!function_exists('related_post_settings_content_query')) {
    function related_post_settings_content_query($tab){

        $pickp_settings_tabs_field = new pickp_settings_tabs_field();

        $related_post_settings = get_option( 'related_post_settings' );

        $orderby = isset($related_post_settings['orderby']) ? $related_post_settings['orderby'] : array('post__in');
        $order = isset($related_post_settings['order']) ? $related_post_settings['order'] : 'DESC';
        $max_post_count = isset($related_post_settings['max_post_count']) ? $related_post_settings['max_post_count'] : 4;

        //echo '<pre>'.var_export($display_auto, true).'</pre>';
        //delete_option('related_post_settings');
        ?>
        <div class="section">
            <div class="section-title"><?php echo __('Post query settings', 'related-post'); ?></div>
            <p class="description section-description"><?php echo __('Choose post query settings.', 'related-post'); ?></p>

            <?php

            $args = array(
                'id'		=> 'orderby',
                'parent'		=> 'related_post_settings',
                'title'		=> __('Query orderby','related-post'),
                'details'	=> __('Choose related post query orderby, this will override by <code>post__in</code> if manually selected post is not empty.','related-post'),
                'type'		=> 'select',
                'value'		=> $orderby,
                'multiple'		=> true,
                'default'		=> array('post__in'),
                'args'		=> array(
                    'ID'=>__('ID','related-post'),
                    'date'=>__('Date','related-post'),
                    'rand'=>__('Random','related-post'),
                    'comment_count'=>__('Comment count','related-post'),
                    'author'=>__('Author','related-post'),
                    'title'=>__('Title','related-post'),
                    'name'=>__('Name','related-post'),
                    'type'=>__('Type','related-post'),
                    'menu_order'=>__('Menu order','related-post'),
                    'post__in'=>__('post__in','related-post'),

                ),
            );

            $pickp_settings_tabs_field->generate_field($args);

            $args = array(
                'id'		=> 'order',
                'parent'		=> 'related_post_settings',
                'title'		=> __('Post order','related-post'),
                'details'	=> __('Choose post query order.','related-post'),
                'type'		=> 'select',
                'value'		=> $order,
                'default'		=> 'DESC',
                'args'		=> array('DESC'=>__('Descending','related-post'), 'ASC'=>__('Ascending','related-post')),
            );

            $pickp_settings_tabs_field->generate_field($args);



            $args = array(
                'id'		=> 'max_post_count',
                'parent'		=> 'related_post_settings',
                'title'		=> __('Max number of post','related-post'),
                'details'	=> __('Maximum number of post to display.','related-post'),
                'type'		=> 'text',
                'value'		=> $max_post_count,
                'default'		=> '5',
            );

            $pickp_settings_tabs_field->generate_field($args);








            ?>


        </div>
        <?php


    }
}


add_action('related_post_settings_content_elements', 'related_post_settings_content_elements');

if(!function_exists('related_post_settings_content_elements')) {
    function related_post_settings_content_elements($tab){

        $pickp_settings_tabs_field = new pickp_settings_tabs_field();

        $related_post_settings = get_option( 'related_post_settings' );


        $elements = isset($related_post_settings['elements']) ? $related_post_settings['elements'] : array();
        $elements_index = isset($related_post_settings['elements_index']) ? $related_post_settings['elements_index'] : array();



        //$layout_items= $related_post_settings['layout_items'];

        //delete_option('related_post_settings');
        //echo '<pre>'.var_export($get_intermediate_image_sizes, true).'</pre>';

        ?>
        <div class="section">
            <div class="section-title"><?php echo __('Elements', 'related-post'); ?></div>
            <p class="description section-description"><?php echo __('Customize post elements.', 'related-post'); ?></p>

            <?php
            $get_intermediate_image_sizes =  get_intermediate_image_sizes();
            $get_intermediate_image_sizes[] = 'full';


            $image_sizes = array();
            foreach ($get_intermediate_image_sizes as $size){
                $image_sizes[$size] = ucfirst($size);
            }


            //$wp_get_additional_image_sizes =  wp_get_additional_image_sizes();

            //$get_intermediate_image_sizes = array_merge($get_intermediate_image_sizes,array('full'));
            //echo '<pre>'.var_export($wp_get_additional_image_sizes, true).'</pre>';

            $args = array(
                'id'		    => 'elements',
                'title'		    => __('Elements settings','related-post'),
                'details'	    => __('Customize elements.','related-post'),
                'type'		    => 'option_group_accordion',
                'value'		    => $elements,
                'sortable'		=> true,
                'default'		=> array(),
                'args_index'	=> $elements_index,
                'args_index_default'    => apply_filters('related_post_elements_index', array('post_title', 'post_thumb', 'post_excerpt')),
                'args_index_hide'	=>  array('post_title' => false, 'post_thumb' => false , 'post_excerpt' => false),

                'args'          => apply_filters('related_post_elements_args', array(
                    'post_title'    => array(
                        'title'     =>'Post title',
                        'options'   =>array(
                            array(
                                'id'		    => 'post_title',
                                'parent'		=> 'related_post_settings[elements_index]',
                                'title'		    => '',
                                'details'	    => '',
                                'type'		    => 'hidden',
                                'value'		=> 'post_title',
                                'default'		=> 'post_title',
                            ),
                            array(
                                'id'		=> 'hide',
                                'parent'		=> 'related_post_settings[elements][post_title]',
                                'title'		=> __('Hide','related-post'),
                                'details'	=> __('You can hide this element.','related-post'),
                                'type'		=> 'select',
                                'value'		=> isset($elements['post_title']['hide']) ? $elements['post_title']['hide'] : 'no',
                                //'multiple'		=> true,
                                'default'		=> 'no',
                                'args'		=> array(
                                    'no'=>__('No','related-post'),
                                    'yes'=>__('Yes','related-post'),
                                ),
                            ),

                            array(
                                'id'		    => 'font_size',
                                'parent'		=> 'related_post_settings[elements][post_title]',
                                'title'		    => __('Font size','related-post'),
                                'details'	    => __('Set custom font size. ex: 14px','related-post'),
                                'type'		    => 'text',
                                'value'		=> isset($elements['post_title']['font_size']) ? $elements['post_title']['font_size'] : '',
                                'default'		=> '16px',
                                'placeholder'   => '14px',
                            ),
                            array(
                                'id'		    => 'font_color',
                                'css_id'		    => 'post_title_font_color',
                                'parent'		=> 'related_post_settings[elements][post_title]',
                                'title'		    => __('Font color','related-post'),
                                'details'	    => __('Choose font color.','related-post'),
                                'type'		    => 'colorpicker',
                                'value'		=> isset($elements['post_title']['font_color']) ? $elements['post_title']['font_color'] : '',
                                'default'		=> '#3f3f3f',
                                'placeholder'   => '14px',
                            ),
                            array(
                                'id'		    => 'line_height',
                                'parent'		=> 'related_post_settings[elements][post_title]',
                                'title'		    => __('Line height','related-post'),
                                'details'	    => __('Set line height.','related-post'),
                                'type'		    => 'text',
                                'value'		=> isset($elements['post_title']['line_height']) ? $elements['post_title']['line_height'] : '',
                                'default'		=> '',
                                'placeholder'   => 'normal',
                            ),

                            array(
                                'id'		    => 'margin',
                                'parent'		=> 'related_post_settings[elements][post_title]',
                                'title'		    => __('Margin','related-post'),
                                'details'	    => __('Set margin. ex: 5px 10px','related-post'),
                                'type'		    => 'text',
                                'value'		=> isset($elements['post_title']['margin']) ? $elements['post_title']['margin'] : '',
                                'default'		=> '10px 0px',
                                'placeholder'   => '10px',
                            ),

                            array(
                                'id'		    => 'padding',
                                'parent'		=> 'related_post_settings[elements][post_title]',
                                'title'		    => __('Padding','related-post'),
                                'details'	    => __('Set padding. ex: 5px 10px','related-post'),
                                'type'		    => 'text',
                                'value'		=> isset($elements['post_title']['padding']) ? $elements['post_title']['padding'] : '',
                                'default'		=> '0px',
                                'placeholder'   => '10px',
                            ),
                            array(
                                'id'		    => 'icon',
                                'parent'		=> 'related_post_settings[elements][post_title]',
                                'title'		    => __('Icon','related-post'),
                                'details'	    => __('Set icon. use font awesome icon HTML, ex: <code>&lt;i class="fas fa-dot-circle">&lt;/i></code>','related-post'),
                                'type'		    => 'text',
                                'value'		=> isset($elements['post_title']['icon']) ? $elements['post_title']['icon'] : '',
                                'default'		=> '',
                                'placeholder'   => esc_attr('<i class="fas fa-dot-circle"></i>'),
                            ),
                            array(
                                'id'		    => 'icon_font_size',
                                'parent'		=> 'related_post_settings[elements][post_title]',
                                'title'		    => __('Icon font size','related-post'),
                                'details'	    => __('Set icon font size.','related-post'),
                                'type'		    => 'text',
                                'value'		=> isset($elements['post_title']['icon_font_size']) ? $elements['post_title']['icon_font_size'] : '',
                                'default'		=> '',
                                'placeholder'   => '16px',
                            ),
                            array(
                                'id'		    => 'custom_css',
                                'parent'		=> 'related_post_settings[elements][post_title]',
                                'title'		    => __('Custom CSS','related-post'),
                                'details'	    => __('Write custom CSS, do not write &lt;style>&lt;/style> tag, do not use selector(.class-name{})','related-post'),
                                'type'		    => 'textarea',
                                'value'		=> isset($elements['post_title']['custom_css']) ? $elements['post_title']['custom_css'] : '',
                                'placeholder'   => 'color:#999999;',
                            ),





                        ),
                    ),
                    'post_thumb' => array(
                        'title'=>'Post thumbnail',
                        'options'=>array(
                            array(
                                'id'		    => 'post_thumb',
                                'parent'		=> 'related_post_settings[elements_index]',
                                'title'		    => '',
                                'details'	    => '',
                                'type'		    => 'hidden',
                                'value'		=> 'post_thumb',
                                'default'		=> 'post_thumb',
                            ),
                            array(
                                'id'		=> 'hide',
                                'parent'		=> 'related_post_settings[elements][post_thumb]',
                                'title'		=> __('Hide','related-post'),
                                'details'	=> __('You can hide this element.','related-post'),
                                'type'		=> 'select',
                                'value'		=> isset($elements['post_thumb']['hide']) ? $elements['post_thumb']['hide'] : 'no',
                                //'multiple'		=> true,
                                'default'		=> 'no',
                                'args'		=> array(
                                    'no'=>__('No','related-post'),
                                    'yes'=>__('Yes','related-post'),
                                ),
                            ),

                            array(
                                'id'		    => 'thumb_size',
                                'parent'		=> 'related_post_settings[elements][post_thumb]',
                                'title'		    => __('Thumbnail size','related-post'),
                                'details'	    => __('Choose thumbnail size','related-post'),
                                'type'		    => 'select',
                                'value'		=> isset($elements['post_thumb']['thumb_size']) ? $elements['post_thumb']['thumb_size'] : 'full',
                                'default'		=> 'full',
                                'args'   => $image_sizes,
                            ),
                            array(
                                'id'		    => 'default_img',
                                'parent'		=> 'related_post_settings[elements][post_thumb]',
                                'title'		    => __('Default thumbnail','related-post'),
                                'details'	    => __('Set default thumbnail','related-post'),
                                'type'		    => 'media_url',
                                'value'		=> isset($elements['post_thumb']['default_img']) ? $elements['post_thumb']['default_img'] : '',
                                'default'		=> '',
                            ),

                            array(
                                'id'		    => 'max_height',
                                'parent'		=> 'related_post_settings[elements][post_thumb]',
                                'title'		    => __('Max height','related-post'),
                                'details'	    => __('Set max height','related-post'),
                                'type'		    => 'text',
                                'value'		=> isset($elements['post_thumb']['max_height']) ? $elements['post_thumb']['max_height'] : '',
                                'default'		=> '220px',
                                'placeholder'   => '200px',
                            ),
                            array(
                                'id'		    => 'margin',
                                'parent'		=> 'related_post_settings[elements][post_thumb]',
                                'title'		    => __('Margin','related-post'),
                                'details'	    => __('Set margin. ex: 5px 10px','related-post'),
                                'type'		    => 'text',
                                'value'		=> isset($elements['post_thumb']['margin']) ? $elements['post_thumb']['margin'] : '',
                                'default'		=> '10px 0px',
                                'placeholder'   => '10px',
                            ),

                            array(
                                'id'		    => 'padding',
                                'parent'		=> 'related_post_settings[elements][post_thumb]',
                                'title'		    => __('Padding','related-post'),
                                'details'	    => __('Set padding. ex: 5px 10px','related-post'),
                                'type'		    => 'text',
                                'value'		=> isset($elements['post_thumb']['padding']) ? $elements['post_thumb']['padding'] : '',
                                'default'		=> '0px',
                                'placeholder'   => '10px',
                            ),
                            array(
                                'id'		    => 'custom_css',
                                'parent'		=> 'related_post_settings[elements][post_thumb]',
                                'title'		    => __('Custom CSS','related-post'),
                                'details'	    => __('Write custom CSS, do not write &lt;style>&lt;/style> tag, do not use selector(.class-name{})','related-post'),
                                'type'		    => 'textarea',
                                'value'		=> isset($elements['post_thumb']['custom_css']) ? $elements['post_thumb']['custom_css'] : '',
                                'placeholder'   => 'font-size:16px;',
                            ),

                        ),
                    ),
                    'post_excerpt' => array(
                        'title'=>'Post excerpt',
                        'options'=>array(
                            array(
                                'id'		    => 'post_excerpt',
                                'parent'		=> 'related_post_settings[elements_index]',
                                'title'		    => '',
                                'details'	    => '',
                                'type'		    => 'hidden',
                                'value'		=> 'post_excerpt',
                                'default'		=> 'post_excerpt',
                            ),
                            array(
                                'id'		=> 'hide',
                                'parent'		=> 'related_post_settings[elements][post_excerpt]',
                                'title'		=> __('Hide','related-post'),
                                'details'	=> __('You can hide this element.','related-post'),
                                'type'		=> 'select',
                                'value'		=> isset($elements['post_excerpt']['hide']) ? $elements['post_excerpt']['hide'] : 'no',
                                //'multiple'		=> true,
                                'default'		=> 'no',
                                'args'		=> array(
                                    'no'=>__('No','related-post'),
                                    'yes'=>__('Yes','related-post'),
                                ),
                            ),
                            array(
                                'id'		    => 'word_count',
                                'parent'		=> 'related_post_settings[elements][post_excerpt]',
                                'title'		    => __('Excerpt word count','related-post'),
                                'details'	    => __('Set custom number of word count for excerpt.','related-post'),
                                'type'		    => 'text',
                                'value'		=> isset($elements['post_excerpt']['word_count']) ? $elements['post_excerpt']['word_count'] : '',
                                'default'		=> '20',
                                'placeholder'   => '20',
                            ),

                            array(
                                'id'		    => 'read_more_text',
                                'parent'		=> 'related_post_settings[elements][post_excerpt]',
                                'title'		    => __('Read more text','related-post'),
                                'details'	    => __('Set custom raed more text for excerpt.','related-post'),
                                'type'		    => 'text',
                                'value'		=> isset($elements['post_excerpt']['read_more_text']) ? $elements['post_excerpt']['read_more_text'] : '',
                                'default'		=> __('Read more', 'related-post'),
                                'placeholder'   => __('Read more', 'related-post'),
                            ),


                            array(
                                'id'		    => 'font_size',
                                'parent'		=> 'related_post_settings[elements][post_excerpt]',
                                'title'		    => __('Font size','related-post'),
                                'details'	    => __('Set custom font size. ex: 14px','related-post'),
                                'type'		    => 'text',
                                'value'		=> isset($elements['post_excerpt']['font_size']) ? $elements['post_excerpt']['font_size'] : '',
                                'default'		=> '13px',
                                'placeholder'   => '14px',
                            ),
                            array(
                                'id'		    => 'font_color',
                                'css_id'		    => 'excerpt_font_color',
                                'parent'		=> 'related_post_settings[elements][post_excerpt]',
                                'title'		    => __('Font color','related-post'),
                                'details'	    => __('Choose font color.','related-post'),
                                'type'		    => 'colorpicker',
                                'value'		=> isset($elements['post_excerpt']['font_color']) ? $elements['post_excerpt']['font_color'] : '',
                                'default'		=> '#3f3f3f',
                                'placeholder'   => '14px',
                            ),
                            array(
                                'id'		    => 'line_height',
                                'parent'		=> 'related_post_settings[elements][post_excerpt]',
                                'title'		    => __('Line height','related-post'),
                                'details'	    => __('Set line height.','related-post'),
                                'type'		    => 'text',
                                'value'		=> isset($elements['post_excerpt']['line_height']) ? $elements['post_excerpt']['line_height'] : '',
                                'default'		=> '',
                                'placeholder'   => 'normal',
                            ),

                            array(
                                'id'		    => 'margin',
                                'parent'		=> 'related_post_settings[elements][post_excerpt]',
                                'title'		    => __('Margin','related-post'),
                                'details'	    => __('Set margin. ex: 5px 10px','related-post'),
                                'type'		    => 'text',
                                'value'		=> isset($elements['post_excerpt']['margin']) ? $elements['post_excerpt']['margin'] : '',
                                'default'		=> '10px 0px',
                                'placeholder'   => '10px',
                            ),

                            array(
                                'id'		    => 'padding',
                                'parent'		=> 'related_post_settings[elements][post_excerpt]',
                                'title'		    => __('Padding','related-post'),
                                'details'	    => __('Set padding. ex: 5px 10px','related-post'),
                                'type'		    => 'text',
                                'value'		=> isset($elements['post_excerpt']['padding']) ? $elements['post_excerpt']['padding'] : '',
                                'default'		=> '0px',
                                'placeholder'   => '10px',
                            ),
                            array(
                                'id'		    => 'custom_css',
                                'parent'		=> 'related_post_settings[elements][post_excerpt]',
                                'title'		    => __('Custom CSS','related-post'),
                                'details'	    => __('Write custom CSS, do not write &lt;style>&lt;/style> tag, do not use selector(.class-name{})','related-post'),
                                'type'		    => 'textarea',
                                'value'		=> isset($elements['post_excerpt']['custom_css']) ? $elements['post_excerpt']['custom_css'] : '',
                                'placeholder'   => 'border: 1px solid #ddddd;',
                            ),



                        ),
                    ),

                        ), $elements
                ),
            );

            $pickp_settings_tabs_field->generate_field($args);









            ?>


        </div>
        <?php


    }
}
add_action('related_post_settings_content_slider', 'related_post_settings_content_slider');

if(!function_exists('related_post_settings_content_slider')) {
    function related_post_settings_content_slider($tab){

        $pickp_settings_tabs_field = new pickp_settings_tabs_field();

        $related_post_settings = get_option( 'related_post_settings' );
        $slider_column_number_desktop = isset($related_post_settings['slider']['column_desktop']) ? $related_post_settings['slider']['column_desktop'] : 3;
        $slider_column_number_tablet = isset($related_post_settings['slider']['column_tablet']) ? $related_post_settings['slider']['column_tablet'] : 2;
        $slider_column_number_mobile = isset($related_post_settings['slider']['column_mobile']) ? $related_post_settings['slider']['column_mobile'] : 1;

        $slider_slide_speed = isset($related_post_settings['slider']['slide_speed']) ? $related_post_settings['slider']['slide_speed'] : 1000;
        $slider_pagination_speed = isset($related_post_settings['slider']['pagination_speed']) ? $related_post_settings['slider']['pagination_speed'] : 1200;


        $slider_auto_play = isset($related_post_settings['slider']['auto_play']) ? $related_post_settings['slider']['auto_play'] : 'true';
        $slider_rewind = isset($related_post_settings['slider']['rewind']) ? $related_post_settings['slider']['rewind'] : 'true';
        $slider_loop = isset($related_post_settings['slider']['loop']) ? $related_post_settings['slider']['loop'] : 'true';
        $slider_center = isset($related_post_settings['slider']['center']) ? $related_post_settings['slider']['center'] : 'true';
        $slider_stop_on_hover = isset($related_post_settings['slider']['stop_on_hover']) ? $related_post_settings['slider']['stop_on_hover'] : 'true';
        $slider_navigation = isset($related_post_settings['slider']['navigation']) ? $related_post_settings['slider']['navigation'] : 'true';
        $navigation_position = isset($related_post_settings['slider']['navigation_position']) ? $related_post_settings['slider']['navigation_position'] : '';

        $slider_pagination = isset($related_post_settings['slider']['pagination']) ? $related_post_settings['slider']['pagination'] : 'true';
        $slider_pagination_count = isset($related_post_settings['slider']['pagination_count']) ? $related_post_settings['slider']['pagination_count'] : 'false';
        $slider_rtl = isset($related_post_settings['slider']['rtl']) ? $related_post_settings['slider']['rtl'] : 'false';

        //echo '<pre>'.var_export($display_auto, true).'</pre>';

        ?>
        <div class="section">
            <div class="section-title"><?php echo __('Slider settings', 'related-post'); ?></div>
            <p class="description section-description"><?php echo __('Choose slider settings.', 'related-post'); ?></p>

            <?php

            $args = array(
                'id'		=> 'slider',
                'title'		=> __('Slider column count ','related-post'),
                'details'	=> __('Set slider column count.','related-post'),
                'type'		=> 'option_group',
                'options'		=> array(
                    array(
                        'id'		=> 'column_desktop',
                        'parent'		=> 'related_post_settings[slider]',
                        'title'		=> __('In desktop','related-post'),
                        'details'	=> __('min-width: 1200px, ex: 3','related-post'),
                        'type'		=> 'text',
                        'value'		=> $slider_column_number_desktop,
                        'default'		=> 3,
                        'placeholder'   => '3',
                    ),
                    array(
                        'id'		=> 'column_tablet',
                        'parent'		=> 'related_post_settings[slider]',
                        'title'		=> __('In tablet & small desktop','related-post'),
                        'details'	=> __('min-width: 992px, ex: 2','related-post'),
                        'type'		=> 'text',
                        'value'		=> $slider_column_number_tablet,
                        'default'		=> 2,
                        'placeholder'   => '2',
                    ),
                    array(
                        'id'		=> 'column_mobile',
                        'parent'		=> 'related_post_settings[slider]',
                        'title'		=> __('In mobile','related-post'),
                        'details'	=> __('min-width: 576px, ex: 1','related-post'),
                        'type'		=> 'text',
                        'value'		=> $slider_column_number_mobile,
                        'default'		=> 1,
                        'placeholder'   => '1',
                    ),
                ),

            );

            $pickp_settings_tabs_field->generate_field($args);




            $args = array(
                'id'		=> 'slide_speed',
                'parent'		=> 'related_post_settings[slider]',
                'title'		=> __('Navigation slide speed','related-post'),
                'details'	=> __('Set slide speed, ex: 1000','related-post'),
                'type'		=> 'text',
                'value'		=> $slider_slide_speed,
                'default'		=> 1000,
                'placeholder'   => '1000',
            );

            $pickp_settings_tabs_field->generate_field($args);

            $args = array(
                'id'		=> 'pagination_speed',
                'parent'		=> 'related_post_settings[slider]',
                'title'		=> __('Dots slide speed','related-post'),
                'details'	=> __('Set dots slide speed, ex: 1200','related-post'),
                'type'		=> 'text',
                'value'		=> $slider_pagination_speed,
                'default'		=> 1200,
                'placeholder'   => '1200',
            );

            $pickp_settings_tabs_field->generate_field($args);



            $args = array(
                'id'		=> 'auto_play',
                'parent'		=> 'related_post_settings[slider]',
                'title'		=> __('Auto play','related-post'),
                'details'	=> __('Choose slider auto play.','related-post'),
                'type'		=> 'select',
                'value'		=> $slider_auto_play,
                'default'		=> 'true',
                'args'		=> array('true'=>__('True','related-post'), 'false'=>__('False','related-post')),
            );

            $pickp_settings_tabs_field->generate_field($args);

            $args = array(
                'id'		=> 'rewind',
                'parent'		=> 'related_post_settings[slider]',
                'title'		=> __('Slider rewind','related-post'),
                'details'	=> __('Choose slider rewind.','related-post'),
                'type'		=> 'select',
                'value'		=> $slider_rewind,
                'default'		=> 'true',
                'args'		=> array('true'=>__('True','related-post'), 'false'=>__('False','related-post')),
            );

            $pickp_settings_tabs_field->generate_field($args);

            $args = array(
                'id'		=> 'loop',
                'parent'		=> 'related_post_settings[slider]',
                'title'		=> __('Slider loop','related-post'),
                'details'	=> __('Choose slider loop.','related-post'),
                'type'		=> 'select',
                'value'		=> $slider_loop,
                'default'		=> 'true',
                'args'		=> array('true'=>__('True','related-post'), 'false'=>__('False','related-post')),
            );

            $pickp_settings_tabs_field->generate_field($args);



            $args = array(
                'id'		=> 'center',
                'parent'		=> 'related_post_settings[slider]',
                'title'		=> __('Slider center','related-post'),
                'details'	=> __('Choose slider center.','related-post'),
                'type'		=> 'select',
                'value'		=> $slider_center,
                'default'		=> 'true',
                'args'		=> array('true'=>__('True','related-post'), 'false'=>__('False','related-post')),
            );

            $pickp_settings_tabs_field->generate_field($args);

            $args = array(
                'id'		=> 'stop_on_hover',
                'parent'		=> 'related_post_settings[slider]',
                'title'		=> __('Slider stop on hover','related-post'),
                'details'	=> __('Choose stop on hover.','related-post'),
                'type'		=> 'select',
                'value'		=> $slider_stop_on_hover,
                'default'		=> 'true',
                'args'		=> array('true'=>__('True','related-post'), 'false'=>__('False','related-post')),
            );

            $pickp_settings_tabs_field->generate_field($args);




            $args = array(
                'id'		=> 'navigation',
                'parent'		=> 'related_post_settings[slider]',
                'title'		=> __('Slider navigation','related-post'),
                'details'	=> __('Choose slider navigation.','related-post'),
                'type'		=> 'select',
                'value'		=> $slider_navigation,
                'default'		=> 'true',
                'args'		=> array('true'=>__('True','related-post'), 'false'=>__('False','related-post')),
            );

            $pickp_settings_tabs_field->generate_field($args);

            $args = array(
                'id'		=> 'navigation_position',
                'parent'		=> 'related_post_settings[slider]',
                'title'		=> __('Slider navigation position','related-post'),
                'details'	=> __('Choose slider navigation position.','related-post'),
                'type'		=> 'select',
                'value'		=> $navigation_position,
                'default'		=> 'topright',
                'args'		=> array('topright'=>__('Top-right','related-post'),  ), //'middle'=>__('Middle','related-post') , 'middle-fixed'=>__('Middle-fixed','related-post')
            );

            $pickp_settings_tabs_field->generate_field($args);



            $args = array(
                'id'		=> 'pagination',
                'parent'		=> 'related_post_settings[slider]',
                'title'		=> __('Slider pagination','related-post'),
                'details'	=> __('Choose slider pagination.','related-post'),
                'type'		=> 'select',
                'value'		=> $slider_pagination,
                'default'		=> 'true',
                'args'		=> array('true'=>__('True','related-post'), 'false'=>__('False','related-post')),
            );

            $pickp_settings_tabs_field->generate_field($args);


            $args = array(
                'id'		=> 'pagination_count',
                'parent'		=> 'related_post_settings[slider]',
                'title'		=> __('Slider pagination count','related-post'),
                'details'	=> __('Choose slider pagination count.','related-post'),
                'type'		=> 'select',
                'value'		=> $slider_pagination_count,
                'default'		=> 'true',
                'args'		=> array('true'=>__('True','related-post'), 'false'=>__('False','related-post')),
            );

            $pickp_settings_tabs_field->generate_field($args);

            $args = array(
                'id'		=> 'rtl',
                'parent'		=> 'related_post_settings[slider]',
                'title'		=> __('Slider rtl','related-post'),
                'details'	=> __('Choose slider rtl.','related-post'),
                'type'		=> 'select',
                'value'		=> $slider_rtl,
                'default'		=> 'false',
                'args'		=> array('true'=>__('True','related-post'), 'false'=>__('False','related-post')),
            );

            $pickp_settings_tabs_field->generate_field($args);




            ?>


        </div>
        <?php


    }
}

add_action('related_post_settings_content_stats', 'related_post_settings_content_stats');

if(!function_exists('related_post_settings_content_stats')) {
    function related_post_settings_content_stats($tab){

        $pickp_settings_tabs_field = new pickp_settings_tabs_field();

        $related_post_settings = get_option( 'related_post_settings' );

        $enable_stats = isset($related_post_settings['enable_stats']) ? $related_post_settings['enable_stats'] : 'no';
        $custom_css = isset($related_post_settings['custom_css']) ? $related_post_settings['custom_css'] : 'no';

        //echo '<pre>'.var_export($display_auto, true).'</pre>';

        ?>
        <div class="section">
            <div class="section-title"><?php echo __('Post query settings', 'related-post'); ?></div>
            <p class="description section-description"><?php echo __('Choose post query settings.', 'related-post'); ?></p>

            <?php



            $args = array(
                'id'		=> 'enable_stats',
                'parent'		=> 'related_post_settings',
                'title'		=> __('Enable stats','related-post'),
                'details'	=> __('Enable trace click on related post.','related-post'),
                'type'		=> 'select',
                'value'		=> $enable_stats,
                'default'		=> 'yes',
                'args'		=> array('enable'=>__('Enable','related-post'), 'disable'=>__('Disable','related-post')),
            );

            $pickp_settings_tabs_field->generate_field($args);


            ob_start();
            ?>
            <ul>
            <?php

            global $wpdb;
            $table = $wpdb->prefix . "related_post_stats";
            $to_id = 'to_id';

            //$entries = $wpdb->get_results( "SELECT * FROM $table ORDER BY id DESC LIMIT 0, 10" );
            $entries = $wpdb->get_results("SELECT * FROM $table GROUP BY $to_id ORDER BY COUNT($to_id) DESC LIMIT 10", ARRAY_A);
            $count_to_id = $wpdb->get_results("SELECT to_id, COUNT(*) AS to_id FROM $table GROUP BY to_id ORDER BY COUNT(to_id) DESC LIMIT 10", ARRAY_A);
            //echo '<pre>'.var_export($entries, true).'</pre>';

            $i = 0;
            if(!empty($entries)):
                foreach($entries as $entry){
                    $to_id = $entry['to_id'];
                    $title = get_the_title($to_id);
                    ?>
                    <li>
                        <span><?php echo $count_to_id[$i]['to_id']; ?></span> <a href="#"><?php echo $title; ?></a>
                    </li>
                    <?php
                    $i++;
                }

            else:
                ?>
                <li>
                    No stats yet.
                </li>
                <?php

            endif;
            ?>
            </ul>
            <?php

            $top_10_html = ob_get_clean();

            $args = array(
                'id'		=> 'top_10',
                'parent'		=> 'related_post_settings',
                'title'		=> __('Top 10 visited post today','related-post'),
                'details'	=> '',
                'type'		=> 'custom_html',
                'html'		=> $top_10_html,

            );

            $pickp_settings_tabs_field->generate_field($args);


            ?>


        </div>
        <?php


    }
}





add_action('related_post_settings_tabs_right_panel_general', 'related_post_settings_tabs_right_panel_general');

add_action('related_post_settings_tabs_right_panel_query', 'related_post_settings_tabs_right_panel_general');
add_action('related_post_settings_tabs_right_panel_style', 'related_post_settings_tabs_right_panel_general');
add_action('related_post_settings_tabs_right_panel_elements', 'related_post_settings_tabs_right_panel_general');
add_action('related_post_settings_tabs_right_panel_slider', 'related_post_settings_tabs_right_panel_general');
add_action('related_post_settings_tabs_right_panel_stats', 'related_post_settings_tabs_right_panel_general');
add_action('related_post_settings_tabs_right_panel_buy_pro', 'related_post_settings_tabs_right_panel_general');




if(!function_exists('related_post_settings_tabs_right_panel_general')) {
    function related_post_settings_tabs_right_panel_general($tab){

        ?>
        <h3>Help & Support</h3>
        <p><?php echo __('Ask question for free on our forum and get quick reply from our expert team members.', 'related-post'); ?></p>
        <a class="button" href="https://www.pickplugins.com/create-support-ticket/"><?php echo __('Create support ticket', 'related-post'); ?></a>

        <p><?php echo __('Read our documentation before asking your question.', 'related-post'); ?></p>
        <a class="button" href="https://www.pickplugins.com/documentation/related-post/"><?php echo __('Documentation', 'related-post'); ?></a>

        <p><?php echo __('Watch video tutorials.', 'related-post'); ?></p>
        <a class="button" href="https://www.youtube.com/playlist?list=PL0QP7T2SN94aXEA_fguVn2ZpdizEeNmsx"><i class="fab fa-youtube"></i> <?php echo __('All tutorials', 'related-post'); ?></a>

        <ul>
            <li><i class="far fa-dot-circle"></i> <a href="https://www.youtube.com/watch?v=9SZKa0QYgsc">How to install & setup</a></li>
            <li><i class="far fa-dot-circle"></i> <a href="https://www.youtube.com/watch?v=tXBLwC3PQBI">Display on archive pages</a></li>
            <li><i class="far fa-dot-circle"></i> <a href="https://www.youtube.com/watch?v=_kWh4mP-eso">Customize elements</a></li>
            <li><i class="far fa-dot-circle"></i> <a href="https://www.youtube.com/watch?v=5G7o_zFKUhE">Manually selected post</a></li>
            <li><i class="far fa-dot-circle"></i> <a href="https://www.youtube.com/watch?v=KUtBCyFoARk">Related post slider layout</a></li>
            <li><i class="far fa-dot-circle"></i> <a href="https://www.youtube.com/watch?v=qudCJcqjlCk">Customize column count</a></li>
            <li><i class="far fa-dot-circle"></i> <a href="https://www.youtube.com/watch?v=uo2v9U9kUCc">Related posts as list layout</a></li>
            <li><i class="fas fa-dot-circle"></i> <a href="https://www.youtube.com/watch?v=pztzF9R2yRQ">Custom html after elements</a> [Premium]</li>
            <li><i class="fas fa-dot-circle"></i> <a href="https://www.youtube.com/watch?v=siMFvhy95Wo">Display on popups</a> [Premium]</li>
            <li><i class="fas fa-dot-circle"></i> <a href="https://www.youtube.com/watch?v=qFZPMoqEHxs">Customize link target</a> [Premium]</li>
        </ul>

        <h3>Submit Reviews</h3>
        <p class="">We wish your 2 minutes to write your feedback about the related post plugin. give us <span style="color: #ffae19"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></span></p>

        <a target="_blank" href="https://wordpress.org/support/plugin/related-post/reviews/#new-post" class="button"><i class="fab fa-wordpress"></i> Write a review</a>


        <?php

    }
}


add_action('related_post_settings_content_scripts', 'related_post_settings_content_scripts');

if(!function_exists('related_post_settings_content_scripts')) {
    function related_post_settings_content_scripts($tab){
        $pickp_settings_tabs_field = new pickp_settings_tabs_field();

        $related_post_settings = get_option( 'related_post_settings' );

        $custom_css = isset($related_post_settings['custom_css']) ? $related_post_settings['custom_css'] : '';
        $custom_js = isset($related_post_settings['custom_js']) ? $related_post_settings['custom_js'] : '';


        ?>
        <div class="section">
            <div class="section-title"><?php echo __('Custom scripts and CSS', 'related-post'); ?></div>
            <p class="description section-description"><?php echo __('Add your custom CSS and scripts here.', 'related-post'); ?></p>



            <?php

            $args = array(
                'id'		=> 'custom_css',
                'css_id'		=> 'custom_css_1',

                'parent'		=> 'related_post_settings',
                'title'		=> __('Custom CSS','related-post'),
                'details'	=> __('Add your custom CSS, do not use <code> &ltstyle>&lt/style> tag.</code>','related-post'),
                'type'		=> 'scripts_css',
                'value'		=> $custom_css,

            );

            $pickp_settings_tabs_field->generate_field($args);



            $args = array(
                'id'		=> 'custom_js',
                'parent'		=> 'related_post_settings',
                'title'		=> __('Custom JS','related-post'),
                'details'	=> __('Add your custom javascript, do not use <code> &ltscript>&lt/script> tag.</code>','related-post'),
                'type'		=> 'scripts_js',
                'value'		=> $custom_js,

            );

            $pickp_settings_tabs_field->generate_field($args);





            ?>

        </div>
        <?php
    }
}


add_action('related_post_settings_content_help_support', 'related_post_settings_content_help_support');

if(!function_exists('related_post_settings_content_help_support')) {
    function related_post_settings_content_help_support($tab){

        $pickp_settings_tabs_field = new pickp_settings_tabs_field();

        ?>
        <div class="section">
            <div class="section-title"><?php echo __('Get support', 'related-post'); ?></div>
            <p class="description section-description"><?php echo __('Use following to get help and support from our expert team.', 'related-post'); ?></p>

            <?php


            ob_start();
            ?>

            <p><?php echo __('Shortcode for php file', 'related-post'); ?></p>
            <textarea onclick="this.select()">&#60;?php echo do_shortcode( '&#91;related_post&#93;' ); ?&#62;</textarea>
            <p class="description" ><?php echo __('Shortcode inside loop by dynamic post id you can use anywhere inside loop on .php files.', 'related-post'); ?></p>

            <p><?php echo __('Short-code for content', 'related-post'); ?></p>
            <textarea onclick="this.select()">[related_post]</textarea>

            <p class="description"><?php echo __('Short-code inside content for fixed post id you can use anywhere inside content.', 'related-post'); ?></p>
            <?php

            $html = ob_get_clean();

            $args = array(
                'id'		=> 'shortcodes',
                'parent'		=> 'related_post_settings',
                'title'		=> __('Shortcodes','related-post'),
                'details'	=> '',
                'type'		=> 'custom_html',
                'html'		=> $html,

            );

            $pickp_settings_tabs_field->generate_field($args);



            ob_start();
            ?>

            <p><?php echo __('Ask question for free on our forum and get quick reply from our expert team members.', 'related-post'); ?></p>
            <a class="button" href="https://www.pickplugins.com/create-support-ticket/"><?php echo __('Create support ticket', 'related-post'); ?></a>

            <p><?php echo __('Read our documentation before asking your question.', 'related-post'); ?></p>
            <a class="button" href="https://www.pickplugins.com/documentation/related-post/"><?php echo __('Documentation', 'related-post'); ?></a>

            <p><?php echo __('Watch video tutorials.', 'related-post'); ?></p>
            <a class="button" href="https://www.youtube.com/playlist?list=PL0QP7T2SN94aXEA_fguVn2ZpdizEeNmsx"><i class="fab fa-youtube"></i> <?php echo __('All tutorials', 'related-post'); ?></a>

            <ul>
                <li><i class="far fa-dot-circle"></i> <a href="https://www.youtube.com/watch?v=9SZKa0QYgsc">How to install & setup</a></li>
                <li><i class="far fa-dot-circle"></i> <a href="https://www.youtube.com/watch?v=tXBLwC3PQBI">Display on archive pages</a></li>
                <li><i class="far fa-dot-circle"></i> <a href="https://www.youtube.com/watch?v=_kWh4mP-eso">Customize elements</a></li>
                <li><i class="far fa-dot-circle"></i> <a href="https://www.youtube.com/watch?v=5G7o_zFKUhE">Manually selected post</a></li>
                <li><i class="far fa-dot-circle"></i> <a href="https://www.youtube.com/watch?v=KUtBCyFoARk">Related post slider layout</a></li>
                <li><i class="far fa-dot-circle"></i> <a href="https://www.youtube.com/watch?v=qudCJcqjlCk">Customize column count</a></li>
                <li><i class="far fa-dot-circle"></i> <a href="https://www.youtube.com/watch?v=uo2v9U9kUCc">Related posts as list layout</a></li>
                <li><i class="fas fa-dot-circle"></i> <a href="https://www.youtube.com/watch?v=pztzF9R2yRQ">Display custom html after elements</a> [ Premium ]</li>
                <li><i class="fas fa-dot-circle"></i> <a href="https://www.youtube.com/watch?v=siMFvhy95Wo">Display on popups</a> [ Premium ]</li>
                <li><i class="fas fa-dot-circle"></i> <a href="https://www.youtube.com/watch?v=qFZPMoqEHxs">Customize link target</a> [ Premium ]</li>
            </ul>



            <?php

            $html = ob_get_clean();

            $args = array(
                'id'		=> 'get_support',
                'parent'		=> 'related_post_settings',
                'title'		=> __('Ask question','related-post'),
                'details'	=> '',
                'type'		=> 'custom_html',
                'html'		=> $html,

            );

            $pickp_settings_tabs_field->generate_field($args);


            ob_start();
            ?>

            <p class="">We wish your 2 minutes to write your feedback about the related post plugin. give us <span style="color: #ffae19"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></span></p>

            <a target="_blank" href="https://wordpress.org/support/plugin/related-post/reviews/#new-post" class="button"><i class="fab fa-wordpress"></i> Write a review</a>


            <?php

            $html = ob_get_clean();

            $args = array(
                'id'		=> 'reviews',
                'parent'		=> 'related_post_settings',
                'title'		=> __('Submit reviews','related-post'),
                'details'	=> '',
                'type'		=> 'custom_html',
                'html'		=> $html,

            );

            $pickp_settings_tabs_field->generate_field($args);

            ?>


        </div>
        <?php


    }
}



add_action('related_post_settings_content_buy_pro', 'related_post_settings_content_buy_pro');

if(!function_exists('related_post_settings_content_buy_pro')) {
    function related_post_settings_content_buy_pro($tab){

        $pickp_settings_tabs_field = new pickp_settings_tabs_field();


        ?>
        <div class="section">
            <div class="section-title"><?php echo __('Get Premium', 'related-post'); ?></div>
            <p class="description section-description"><?php echo __('Thansk for using our plugin, if you looking for some advance feature please buy premium version.', 'related-post'); ?></p>

            <?php


            ob_start();
            ?>

            <p><?php echo __('If you love our plugin and want more feature please consider to buy pro version.', 'related-post'); ?></p>
            <a class="button" href="https://www.pickplugins.com/item/related-post-for-wordpress/"><?php echo __('Buy premium', 'related-post'); ?></a>

            <h2>See the differences</h2>

            <table class="pro-features">
                <thead>
                <tr>
                    <th class="col-features">Features</th>
                    <th class="col-free">Free</th>
                    <th class="col-pro">Premium</th>
                </tr>
                </thead>

                <tr>
                    <td class="col-features">Popup related post</td>
                    <td><i class="fas fa-times"></i></td>
                    <td><i class="fas fa-check"></i></td>
                </tr>

                <tr>
                    <td class="col-features">Custom HTML after post title</td>
                    <td><i class="fas fa-times"></i></td>
                    <td><i class="fas fa-check"></i></td>
                </tr>

                <tr>
                    <td class="col-features">Custom HTML after post excerpt</td>
                    <td><i class="fas fa-times"></i></td>
                    <td><i class="fas fa-check"></i></td>
                </tr>

                <tr>
                    <td class="col-features">Custom HTML after post thumbnail</td>
                    <td><i class="fas fa-times"></i></td>
                    <td><i class="fas fa-check"></i></td>
                </tr>


                <tr>
                    <td class="col-features">Link target for post title</td>
                    <td><i class="fas fa-times"></i></td>
                    <td><i class="fas fa-check"></i></td>
                </tr>

                <tr>
                    <td class="col-features">Link target for post excerpt</td>
                    <td><i class="fas fa-times"></i></td>
                    <td><i class="fas fa-check"></i></td>
                </tr>

                <tr>
                    <td class="col-features">Link target for post thumbnail</td>
                    <td><i class="fas fa-times"></i></td>
                    <td><i class="fas fa-check"></i></td>
                </tr>

                <tr>
                    <td class="col-features">4 Popup visible action</td>
                    <td><i class="fas fa-times"></i></td>
                    <td><i class="fas fa-check"></i></td>
                </tr>

                <tr>
                    <td class="col-features">8 Popup positions</td>
                    <td><i class="fas fa-times"></i></td>
                    <td><i class="fas fa-check"></i></td>
                </tr>

                <tr>
                    <td class="col-features">Popup custom width</td>
                    <td><i class="fas fa-times"></i></td>
                    <td><i class="fas fa-check"></i></td>
                </tr>


                <tr>
                    <td class="col-features">Popup custom delay</td>
                    <td><i class="fas fa-times"></i></td>
                    <td><i class="fas fa-check"></i></td>
                </tr>

                <tr>
                    <td class="col-features">Display on custom post type</td>
                    <td><i class="fas fa-check"></i></td>
                    <td><i class="fas fa-check"></i></td>
                </tr>

                <tr>
                    <td class="col-features">Display on categories</td>
                    <td><i class="fas fa-check"></i></td>
                    <td><i class="fas fa-check"></i></td>
                </tr>

                <tr>
                    <td class="col-features">Display on tags</td>
                    <td><i class="fas fa-check"></i></td>
                    <td><i class="fas fa-check"></i></td>
                </tr>
                <tr>
                    <td class="col-features">Display on author page</td>
                    <td><i class="fas fa-check"></i></td>
                    <td><i class="fas fa-check"></i></td>
                </tr>

                <tr>
                    <td class="col-features">Display on month page</td>
                    <td><i class="fas fa-check"></i></td>
                    <td><i class="fas fa-check"></i></td>
                </tr>
                <tr>
                    <td class="col-features">Display on date page</td>
                    <td><i class="fas fa-check"></i></td>
                    <td><i class="fas fa-check"></i></td>
                </tr>
                <tr>
                    <td class="col-features">Display on year page</td>
                    <td><i class="fas fa-check"></i></td>
                    <td><i class="fas fa-check"></i></td>
                </tr>

                <tr>
                    <td class="col-features">Display on Front page</td>
                    <td><i class="fas fa-check"></i></td>
                    <td><i class="fas fa-check"></i></td>
                </tr>

                <tr>
                    <td class="col-features">Display on Blog page</td>
                    <td><i class="fas fa-check"></i></td>
                    <td><i class="fas fa-check"></i></td>
                </tr>

                <tr>
                    <td class="col-features">Display on Home page</td>
                    <td><i class="fas fa-check"></i></td>
                    <td><i class="fas fa-check"></i></td>
                </tr>

                <tr>
                    <td class="col-features">Display on custom taxonomies</td>
                    <td><i class="fas fa-check"></i></td>
                    <td><i class="fas fa-check"></i></td>
                </tr>

                <tr>
                    <td class="col-features">Display before/after content</td>
                    <td><i class="fas fa-check"></i></td>
                    <td><i class="fas fa-check"></i></td>
                </tr>

                <tr>
                    <td class="col-features">Display before/after excerpt</td>
                    <td><i class="fas fa-check"></i></td>
                    <td><i class="fas fa-check"></i></td>
                </tr>

                <tr>
                    <td class="col-features">N'th paragraph on content</td>
                    <td><i class="fas fa-check"></i></td>
                    <td><i class="fas fa-check"></i></td>
                </tr>

                <tr>
                    <td class="col-features">Custom headline text</td>
                    <td><i class="fas fa-check"></i></td>
                    <td><i class="fas fa-check"></i></td>
                </tr>
                <tr>
                    <td class="col-features">Click tracking</td>
                    <td><i class="fas fa-check"></i></td>
                    <td><i class="fas fa-check"></i></td>
                </tr>

                <tr>
                    <td class="col-features">Related post on slider</td>
                    <td><i class="fas fa-check"></i></td>
                    <td><i class="fas fa-check"></i></td>
                </tr>

                <tr>
                    <td class="col-features">Slider custom column number</td>
                    <td><i class="fas fa-check"></i></td>
                    <td><i class="fas fa-check"></i></td>
                </tr>
                <tr>
                    <th class="col-features">Features</th>
                    <th class="col-free">Free</th>
                    <th class="col-pro">Premium</th>
                </tr>
                <tr>
                    <td class="col-features">Buy now</td>
                    <td> </td>
                    <td><a class="button" href="https://www.pickplugins.com/item/related-post-for-wordpress/"><?php echo __('Buy premium', 'related-post'); ?></a></td>
                </tr>

            </table>



            <?php

            $html = ob_get_clean();

            $args = array(
                'id'		=> 'get_pro',
                'parent'		=> 'related_post_settings',
                'title'		=> __('Get pro version','related-post'),
                'details'	=> '',
                'type'		=> 'custom_html',
                'html'		=> $html,

            );

            $pickp_settings_tabs_field->generate_field($args);


            ?>


        </div>

        <style type="text/css">
            .pro-features{
                margin: 30px 0;
                border-collapse: collapse;
                border: 1px solid #ddd;
            }
            .pro-features th{
                width: 120px;
                background: #ddd;
                padding: 10px;
            }
            .pro-features tr{
            }
            .pro-features td{
                border-bottom: 1px solid #ddd;
                padding: 10px 10px;
                text-align: center;
            }
            .pro-features .col-features{
                width: 230px;
                text-align: left;
            }

            .pro-features .col-free{
            }
            .pro-features .col-pro{
            }

            .pro-features i.fas.fa-check {
                color: #139e3e;
                font-size: 16px;
            }
            .pro-features i.fas.fa-times {
                color: #f00;
                font-size: 17px;
            }
        </style>
        <?php


    }
}