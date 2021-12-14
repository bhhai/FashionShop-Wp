<?php	
if ( ! defined('ABSPATH')) exit;  // if direct access


$current_tab = isset($_POST['tab']) ? sanitize_text_field($_POST['tab']) : 'general';

$related_post_settings_tab = array();

$related_post_settings_tab[] = array(
    'id' => 'general',
    'title' => sprintf(__('%s General','related-post'),'<i class="fas fa-list-ul"></i>'),
    'priority' => 1,
    'active' => ($current_tab == 'general') ? true : false,

);

$related_post_settings_tab[] = array(
    'id' => 'query',
    'title' => sprintf(__('%s Query','related-post'),'<i class="fas fa-filter"></i>'),
    'priority' => 2,
    'active' => ($current_tab == 'query') ? true : false,
);

$related_post_settings_tab[] = array(
    'id' => 'style',
    'title' => sprintf(__('%s Style','related-post'),'<i class="fas fa-palette"></i>'),
    'priority' => 3,
    'active' => ($current_tab == 'style') ? true : false,
);

$related_post_settings_tab[] = array(
    'id' => 'elements',
    'title' => sprintf(__('%s Elements','related-post'),'<i class="fab fa-buffer"></i>'),
    'priority' => 4,
    'active' => ($current_tab == 'elements') ? true : false,
);

$related_post_settings_tab[] = array(
    'id' => 'slider',
    'title' => sprintf(__('%s Slider','related-post'),'<i class="fas fa-photo-video"></i>'),
    'priority' => 5,
    'active' => ($current_tab == 'slider') ? true : false,
);

$related_post_settings_tab[] = array(
    'id' => 'stats',
    'title' => sprintf(__('%s Stats','related-post'),'<i class="fas fa-tachometer-alt"></i>'),
    'priority' => 6,
    'active' => ($current_tab == 'stats') ? true : false,
);


$related_post_settings_tab[] = array(
    'id' => 'scripts',
    'title' => sprintf(__('%s Scripts','related-post'),'<i class="fas fa-code"></i>'),
    'priority' => 6,
    'active' => ($current_tab == 'scripts') ? true : false,
);

$related_post_settings_tab[] = array(
    'id' => 'help_support',
    'title' => sprintf(__('%s Help & Support','related-post'),'<i class="fas fa-hands-helping"></i>'),
    'priority' => 7,
    'active' => ($current_tab == 'help_support') ? true : false,
);

$related_post_settings_tab[] = array(
    'id' => 'buy_pro',
    'title' => sprintf(__('%s Buy Pro','related-post'),'<i class="fas fa-store"></i>'),
    'priority' => 8,
    'active' => ($current_tab == 'buy_pro') ? true : false,
);


$related_post_settings_tab = apply_filters('related_post_settings_tabs', $related_post_settings_tab);

$tabs_sorted = array();
foreach ($related_post_settings_tab as $page_key => $tab) $tabs_sorted[$page_key] = isset( $tab['priority'] ) ? $tab['priority'] : 0;
array_multisort($tabs_sorted, SORT_ASC, $related_post_settings_tab);


$pickp_settings_tabs_field = new pickp_settings_tabs_field();
$pickp_settings_tabs_field->admin_scripts();


$review_status = isset($_GET['review_status']) ? sanitize_text_field($_GET['review_status']) : '';
$related_post_info = get_option('related_post_info');
$related_post_settings = get_option('related_post_settings');

?>
<div class="wrap">
	<div id="icon-tools" class="icon32"><br></div><h2><?php echo sprintf(__('%s Settings', 'related-post'), related_post_plugin_name)?></h2>


    <?php
    $gmt_offset = get_option('gmt_offset');
    $current_date = date('Y-m-d H:i:s', strtotime('+'.$gmt_offset.' hour'));
    //echo '<pre>'.var_export($current_date, true).'</pre>';


    if($review_status =='remind_later'):

        $related_post_info['review_status'] = 'remind_later';
        $related_post_info['remind_date'] = date('Y-m-d H:i:s', strtotime('+30 days'));


        ?>
        <div class="update-nag is-dismissible">We will remind you later.</div>
        <?php
        update_option('related_post_info', $related_post_info);

    elseif ($review_status =='done'):

        $related_post_info['review_status'] = 'done';
        ?>
        <div class="update-nag notice is-dismissible">Thanks for your time and feedback.</div>
        <?php

        update_option('related_post_info', $related_post_info);

    endif;

    ?>



		<form  method="post" action="<?php echo str_replace( '%7E', '~', esc_url_raw($_SERVER['REQUEST_URI'])); ?>">
	        <input type="hidden" name="related_post_hidden" value="Y">
            <input type="hidden" name="tab" value="<?php echo $current_tab; ?>">

            <?php
            if(!empty($_POST['related_post_hidden'])){

                $nonce = sanitize_text_field($_POST['_wpnonce']);

                if(wp_verify_nonce( $nonce, 'related_post_nonce' ) && $_POST['related_post_hidden'] == 'Y') {


                    $related_post_settings = isset($_POST['related_post_settings']) ?  related_post_recursive_sanitize_arr($_POST['related_post_settings']) : '';
                    update_option('related_post_settings', $related_post_settings);


                    do_action('related_post_settings_save');

                    ?>
                    <div class="updated notice  is-dismissible"><p><strong><?php _e('Changes Saved.', 'related-post' ); ?></strong></p></div>

                    <?php
                }
            }
            ?>

            <div class="settings-tabs-loading" style="">Loading...</div>
            <div class="settings-tabs vertical has-right-panel" style="display: none">


                <div class="settings-tabs-right-panel">
                    <?php
                    foreach ($related_post_settings_tab as $tab) {
                        $id = $tab['id'];
                        $active = $tab['active'];

                        ?>
                        <div class="right-panel-content <?php if($active) echo 'active';?> right-panel-content-<?php echo $id; ?>">
                            <?php

                            do_action('related_post_settings_tabs_right_panel_'.$id);
                            ?>

                        </div>
                        <?php

                    }
                    ?>
                </div>

                <ul class="tab-navs">
                    <?php
                    foreach ($related_post_settings_tab as $tab){
                        $id = $tab['id'];
                        $title = $tab['title'];
                        $active = $tab['active'];
                        $data_visible = isset($tab['data_visible']) ? $tab['data_visible'] : '';
                        $hidden = isset($tab['hidden']) ? $tab['hidden'] : false;
                        $is_pro = isset($tab['is_pro']) ? $tab['is_pro'] : false;
                        $pro_text = isset($tab['pro_text']) ? $tab['pro_text'] : '';


                        ?>
                        <li <?php if(!empty($data_visible)):  ?> data_visible="<?php echo $data_visible; ?>" <?php endif; ?> class="tab-nav <?php if($hidden) echo 'hidden';?> <?php if($active) echo 'active';?>" data-id="<?php echo $id; ?>">
                            <?php echo $title; ?>
                            <?php
                            if($is_pro):
                                ?><span class="pro-feature"><?php echo $pro_text; ?></span> <?php
                            endif;
                            ?>

                        </li>
                        <?php
                    }
                    ?>



                </ul>



                <?php
                foreach ($related_post_settings_tab as $tab){
                    $id = $tab['id'];
                    $title = $tab['title'];
                    $active = $tab['active'];
                    ?>

                    <div class="tab-content <?php if($active) echo 'active';?>" id="<?php echo $id; ?>">
                        <?php
                        do_action('related_post_settings_content_'.$id, $tab);
                        ?>


                    </div>

                    <?php
                }
                ?>

                <div class="clear clearfix"></div>
                <p class="submit">
                    <?php wp_nonce_field( 'related_post_nonce' ); ?>
                    <input class="button button-primary" type="submit" name="Submit" value="<?php _e('Save Changes','related-post' ); ?>" />
                </p>

            </div>


		</form>
</div>
