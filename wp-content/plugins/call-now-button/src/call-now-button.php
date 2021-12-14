<?php

require_once dirname( __FILE__ ) . '/utils/utils.php';
require_once dirname( __FILE__ ) . '/admin/admin-ajax.php';
require_once dirname( __FILE__ ) . '/utils/CnbAdminNotices.class.php';
require_once dirname( __FILE__ ) . '/admin/partials/admin-header.php';

// Grabbing the settings and checking for latest version
// OR creating the options file for first time installations
$cnb_settings = cnb_get_options();
$cnb_options = $cnb_settings['options'];
$cnb_slug_base = CNB_SLUG;

$cnb_options['active'] = isset($cnb_options['active']) && $cnb_options['active'] == 1 ? 1 : 0;
$cnb_options['classic'] = isset($cnb_options['classic']) && $cnb_options['classic'] == 1 ? 1 : 0;
$cnb_options['hideIcon'] = isset($cnb_options['hideIcon']) && $cnb_options['hideIcon'] == 1 ? 1 : 0;
$cnb_options['frontpage'] = isset($cnb_options['frontpage']) && $cnb_options['frontpage'] == 1 ? 1 : 0;
$cnb_options['advanced_view'] = isset($cnb_options['advanced_view']) && $cnb_options['advanced_view'] == 1 ? 1 : 0;
$cnb_options['show_all_buttons_for_domain'] = isset($cnb_options['show_all_buttons_for_domain']) && $cnb_options['show_all_buttons_for_domain'] == 1 ? 1 : 0;
$cnb_options['footer_show_traces'] = isset($cnb_options['footer_show_traces']) && $cnb_options['footer_show_traces'] == 1 ? 1 : 0;
$cnb_options['api_caching'] = isset($cnb_options['api_caching']) && $cnb_options['api_caching'] == 1 ? 1 : 0;

$plugin_title = apply_filters('cnb_plugin_title', CNB_NAME);
$cnb_cloud_hosting = isset($cnb_options['cloud_enabled']) && $cnb_options['cloud_enabled'] == 1;

// Used by settings
$cnb_options['status'] = $cnb_cloud_hosting ? 'cloud' : ($cnb_options['active'] ? 'enabled' : 'disabled');

/**
 * Used by cnb_register_admin_page
 */
function cnb_admin_styling() {
    wp_enqueue_style('cnb_styling');
}

function cnb_admin_button_overview() {
    require_once dirname( __FILE__ ) . '/admin/button-overview.php';
    cnb_admin_button_overview_render();
}

function cnb_admin_page_domain_overview() {
    require_once dirname( __FILE__ ) . '/admin/domain-overview.php';
    cnb_admin_page_domain_overview_render();
}

function cnb_admin_page_action_overview() {
    require_once dirname( __FILE__ ) . '/admin/action-overview.php';
    cnb_admin_page_action_overview_render();
}

function cnb_admin_page_condition_overview() {
    require_once dirname( __FILE__ ) . '/admin/condition-overview.php';
    cnb_admin_page_condition_overview_render();
}

function cnb_admin_page_apikey_overview() {
    require_once dirname( __FILE__ ) . '/admin/apikey-overview.php';
    cnb_admin_page_apikey_overview_render();
}

function cnb_admin_settings() {
    require_once dirname( __FILE__ ) . '/admin/settings.php';
    cnb_admin_settings_page();
}

function cnb_admin_page_legacy_edit() {
    require_once dirname( __FILE__ ) . '/admin/legacy-edit.php';
    cnb_admin_page_legacy_edit_render();
}

/**
 * Adds the plugin to the options menu
 */
function cnb_register_admin_pages() {
    global $plugin_title, $cnb_slug_base, $cnb_cloud_hosting, $cnb_options, $wp_version;

    $menu_page_function = $cnb_cloud_hosting ? 'cnb_admin_button_overview' : 'cnb_admin_page_legacy_edit';

    $menu_page_title = $cnb_cloud_hosting ? 'Buttons' : 'Call Now Button';
    $menu_page_position = $cnb_cloud_hosting ? 30 : 66;

    // Oldest WordPress only has "smartphone", no "phone" (this is added in a later version)
    $icon_url = version_compare($wp_version, '5.5.0', '<') ? 'dashicons-smartphone' : 'dashicons-phone';
    $menu_page = add_menu_page(
        __( 'Call Now Button - Overview', CNB_NAME ),
        $menu_page_title,
        'manage_options',
        $cnb_slug_base,
        $menu_page_function,
        $icon_url,
        $menu_page_position
    );
    add_action('admin_print_styles-' . $menu_page, 'cnb_admin_styling');

    if ($cnb_cloud_hosting) {
        // Button overview
        $button_overview = add_submenu_page( $cnb_slug_base, $plugin_title, 'All buttons', 'manage_options', $cnb_slug_base, 'cnb_admin_button_overview' );
        add_action( 'admin_print_styles-' . $button_overview, 'cnb_admin_styling' );

        $button_overview = add_submenu_page( $cnb_slug_base, $plugin_title, 'Add New', 'manage_options', $cnb_slug_base . '&action=new', 'cnb_admin_button_overview' );
        add_action( 'admin_print_styles-' . $button_overview, 'cnb_admin_styling' );

        if ($cnb_options['advanced_view'] === 1) {
            // Domain overview
            $domain_overview = add_submenu_page($cnb_slug_base, $plugin_title, 'Domains', 'manage_options', $cnb_slug_base . '-domains', 'cnb_admin_page_domain_overview');
            add_action('admin_print_styles-' . $domain_overview, 'cnb_admin_styling');

            // Action overview
            $action_overview = add_submenu_page($cnb_slug_base, $plugin_title, 'Actions', 'manage_options', $cnb_slug_base . '-actions', 'cnb_admin_page_action_overview');
            add_action('admin_print_styles-' . $action_overview, 'cnb_admin_styling');

            // Condition overview
            $condition_overview = add_submenu_page($cnb_slug_base, $plugin_title, 'Conditions', 'manage_options', $cnb_slug_base . '-conditions', 'cnb_admin_page_condition_overview');
            add_action('admin_print_styles-' . $condition_overview, 'cnb_admin_styling');

            // Apikey overview
            $apikey_overview = add_submenu_page($cnb_slug_base, $plugin_title, 'API Keys', 'manage_options', $cnb_slug_base . '-apikeys', 'cnb_admin_page_apikey_overview');
            add_action('admin_print_styles-' . $apikey_overview, 'cnb_admin_styling');
        } else {
            // Fake out Action overview
            if (isset($_GET['page']) && $_GET['page'] === 'call-now-button-actions' && $_GET['action']) {
                $action_overview = add_submenu_page($cnb_slug_base, $plugin_title, 'Edit action', 'manage_options', $cnb_slug_base . '-actions', 'cnb_admin_page_action_overview');
                add_action('admin_print_styles-' . $action_overview, 'cnb_admin_styling');
            }
            // Fake out Domain upgrade page
            if (isset($_GET['page']) && $_GET['page'] === 'call-now-button-domains' && $_GET['action'] === 'upgrade') {
                $domain_overview = add_submenu_page($cnb_slug_base, $plugin_title, 'Upgrade domain', 'manage_options', $cnb_slug_base . '-domains', 'cnb_admin_page_domain_overview');
                add_action('admin_print_styles-' . $domain_overview, 'cnb_admin_styling');
            }
        }
    } else {
        // Legacy edit
        $legacy_edit = add_submenu_page( $cnb_slug_base, $plugin_title, 'My button', 'manage_options', $cnb_slug_base, 'cnb_admin_page_legacy_edit' );
        add_action( 'admin_print_styles-' . $legacy_edit, 'cnb_admin_styling' );
    }

    // Settings pages
    $settings = add_submenu_page($cnb_slug_base, $plugin_title, 'Settings', 'manage_options', $cnb_slug_base.'-settings', 'cnb_admin_settings');
    add_action('admin_print_styles-' . $settings, 'cnb_admin_styling');
}

add_action('admin_menu', 'cnb_register_admin_pages');

function cnb_enqueue_color_picker() {
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('cnb-script-handle', plugins_url('../resources/js/call-now-button.js', __FILE__), array('wp-color-picker'), CNB_VERSION, true);
}

add_action('admin_enqueue_scripts', 'cnb_enqueue_color_picker'); // add the color picker

/**
 * Used for the modal in Button edit -> Actions edit
 */
function cnb_enqueue_script_dialog() {
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script( 'jquery-ui-dialog' );
    wp_enqueue_style( 'wp-jquery-ui-dialog' );
}

add_action('admin_enqueue_scripts', 'cnb_enqueue_script_dialog');

function cnb_plugin_meta($links, $file) {
    global $cnb_cloud_hosting;
    if ($file == CNB_BASENAME) {
        $link_name = $cnb_cloud_hosting ? 'All buttons' : 'My button';

        $url = admin_url('admin.php');

        $button_link =
            add_query_arg(
                array(
                    'page' => 'call-now-button'),
                $url);
        $button_url = esc_url($button_link);

        $settings_link =
            add_query_arg(
                array(
                    'page' => 'call-now-button-settings'),
                $url);
        $settings_url = esc_url($settings_link);

        $cnb_new_links = array(
            sprintf(
                '<a href="%s">%s</a>', $button_url, __($link_name)),
            sprintf(
                '<a href="%s">%s</a>', $settings_url, __('Settings')),
            sprintf(
                '<a href="%s">%s</a>', CNB_SUPPORT, __('Support'))
        );
        array_push(
            $links,
            $cnb_new_links[0],
            $cnb_new_links[1],
            $cnb_new_links[2]
        );
    }
    return $links;
}

add_filter('plugin_row_meta', 'cnb_plugin_meta', 10, 2);

function cnb_plugin_add_action_link($links) {
    global $cnb_cloud_hosting;
    $link_name = $cnb_cloud_hosting ? 'All buttons' : 'My button';

    $url = admin_url( 'admin.php' );
    $button_link =
        add_query_arg(
            array(
                'page' => 'call-now-button'
            ),
            $url );
    $button_url  = esc_url( $button_link );


    $button = sprintf(
        '<a href="%s">%s</a>', $button_url, __( $link_name ) );
    array_unshift($links, $button);
    return $links;
}

add_filter('plugin_action_links_' . CNB_BASENAME, 'cnb_plugin_add_action_link');


function cnb_options_validate($input) {
    require_once dirname( __FILE__ ) . '/admin/settings.php';
    return cnb_settings_options_validate($input);
}

function cnb_options_init() {
    register_setting( 'cnb_options', 'cnb', 'cnb_options_validate' );
    wp_register_style('cnb_styling', plugins_url('../resources/style/call-now-button.css', __FILE__), false, CNB_VERSION, 'all');
}

add_action('admin_init', 'cnb_options_init');

/**
 * Called when a Single/Multi/ButtonBar Button is created via POST
 */
function cnb_admin_post_create_button() {
    require_once dirname( __FILE__ ) . '/admin/button-edit.php';
    cnb_admin_create_button();
}
add_action( 'admin_post_cnb_create_single_button', 'cnb_admin_post_create_button' );
add_action( 'admin_post_cnb_create_multi_button', 'cnb_admin_post_create_button' );
add_action( 'admin_post_cnb_create_full_button', 'cnb_admin_post_create_button' );

/**
 * Called when a Single Button is saved via POST
 */
function cnb_admin_post_update_button() {
    require_once dirname( __FILE__ ) . '/admin/button-edit.php';
    cnb_admin_update_button();
}
add_action( 'admin_post_cnb_update_single_button', 'cnb_admin_post_update_button' );
add_action( 'admin_post_cnb_update_multi_button', 'cnb_admin_post_update_button' );
add_action( 'admin_post_cnb_update_full_button', 'cnb_admin_post_update_button' );

/**
 * Called when a Domain is created via POST
 */
function cnb_admin_create_domain() {
    require_once dirname( __FILE__ ) . '/admin/domain-edit.php';
    cnb_admin_page_domain_create_process();
}

add_action( 'admin_post_cnb_create_domain', 'cnb_admin_create_domain' );

/**
 * Called when a Domain is saved via POST
 */
function cnb_admin_update_domain() {
    require_once dirname( __FILE__ ) . '/admin/domain-edit.php';
    cnb_admin_page_domain_edit_process();
}

add_action( 'admin_post_cnb_update_domain', 'cnb_admin_update_domain' );

/**
 * Called when an Action is created via POST
 */
function cnb_admin_create_action() {
    require_once dirname( __FILE__ ) . '/admin/action-edit.php';
    cnb_admin_page_action_create_process();
}

add_action( 'admin_post_cnb_create_action', 'cnb_admin_create_action' );

/**
 * Called when an Action is saved via POST
 */
function cnb_admin_update_action() {
    require_once dirname( __FILE__ ) . '/admin/action-edit.php';
    cnb_admin_page_action_edit_process();
}

add_action( 'admin_post_cnb_update_action', 'cnb_admin_update_action' );

/**
 * Called when a condition is saved via POST
 */
function cnb_admin_create_condition() {
    require_once dirname( __FILE__ ) . '/admin/condition-edit.php';
    cnb_admin_page_condition_create_process();
}

add_action( 'admin_post_cnb_create_condition', 'cnb_admin_create_condition' );

/**
 * Called when a condition is saved via POST
 */
function cnb_admin_update_condition() {
    require_once dirname( __FILE__ ) . '/admin/condition-edit.php';
    cnb_admin_page_condition_edit_process();
}

add_action( 'admin_post_cnb_update_condition', 'cnb_admin_update_condition' );

/**
 * Called when an API key is created via POST
 */
function cnb_admin_create_apikey() {
    require_once dirname( __FILE__ ) . '/admin/apikey-overview.php';
    cnb_admin_page_apikey_create_process();
}

add_action( 'admin_post_cnb_create_apikey', 'cnb_admin_create_apikey' );

/**
 * Called when the Settings page is migrating from Legacy to the cloud
 */
function cnb_admin_migrate_to_cloud() {
    require_once dirname( __FILE__ ) . '/admin/settings.php';
    cnb_admin_setting_migrate();
}

add_action( 'admin_post_cnb_create_cloud_domain', 'cnb_admin_migrate_to_cloud' );
add_action( 'admin_post_cnb_migrate_legacy_button', 'cnb_admin_migrate_to_cloud' );

add_action( 'cnb_in_admin_header', 'cnb_admin_header_no_args' );
add_action('cnb_header', 'cnb_admin_header');
add_action( 'cnb_footer', 'cnb_admin_footer');

/**
 * Initialize the permanent is-dismissible notices code
 */
$adminNotices = CnbAdminNotices::get_instance();

// Render the Frontend
if (!is_admin() && isButtonActive($cnb_options)) {
    $cnb_has_text = ($cnb_options['text'] == '') ? false : true;
    $cnb_is_classic = isset($cnb_options['classic']) && $cnb_options['classic'] == 1 && !$cnb_has_text;

    $renderer = $cnb_cloud_hosting ? 'cloud' : ($cnb_is_classic ? 'classic' : 'modern');

    require_once dirname( __FILE__ ) . "/renderers/$renderer/wp_head.php";
    require_once dirname( __FILE__ ) . "/renderers/$renderer/wp_foot.php";
}
