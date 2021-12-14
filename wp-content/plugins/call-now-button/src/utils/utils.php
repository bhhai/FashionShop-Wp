<?php
require_once dirname( __FILE__ ) . '/notices.php';
require_once dirname( __FILE__ ) . '/../admin/settings.php';

/**
 * Returns the default values for a (legacy) Button
 * @return array
 */
function cnb_get_defaults() {
    return array(
        'active' => 0,
        'number' => '',
        'text' => '',
        'color' => '#00bb00',
        'iconcolor' => '#ffffff',
        'appearance' => 'right',
        'hideIcon' => 0,
        'limit' => 'include',
        'frontpage' => 0,
        'conversions' => 0,
        'zoom' => 1,
        'z-index' => 10,
        'tracking' => 0,
        'show' => '',
        'version' => CNB_VERSION
    );
}

/**
 * Returns the most current set of options
 * Will update the Wordpress Options if they are missing or outdated
 *
 * @return array
 */
function cnb_get_options() { // Grabbing existing settings and creating them if it's a first time installation
    if(!get_option('cnb')) { // Doesn't exist -> set defaults
        add_option('cnb', cnb_get_defaults());
    } else { // Does exist -> see if update is needed
        $updated = cnb_update_options();
    }
    $cnb_options['options'] = get_option('cnb');
    // Format = options['updated'] = array(true/false [true for "there is an update", false for "CNB is up-to-date" , 0.5.5)
    // Debug example: $cnb_options['updated'] = array(true, '0.0.0');
    $cnb_options['updated'] = isset($updated) ? $updated : array(false, substr(CNB_VERSION, 0, 3));
    return $cnb_options;
}

/**
 * Compares version numbers
 *
 * @param $cnb_options array The result of a basic `get_option('cnb')`
 * @return bool
 */
function cnb_update_needed($cnb_options) {
    $pluginVersion 	= CNB_VERSION;
    $setupVersion 	= array_key_exists('version', $cnb_options) ? $cnb_options['version'] : 0.1;
    if($pluginVersion == $setupVersion) {
        return false;
    } elseif(substr($pluginVersion,0,3) > substr($setupVersion,0,3)) {
        return true;
    } elseif(substr($pluginVersion,0,3) == substr($setupVersion,0,3)) {
        return substr($pluginVersion,-1) > substr($setupVersion,-1);
    } else {
        return false;
    }
}

/**
 * Gets the result of the DB settings combined with the default settings if any setting is missing
 * @return array
 */
function cnb_update_options() {
    $cnb_options = get_option('cnb');
    $cnb_defaults = cnb_get_defaults();
    // Bugfix for v0.4.5 - a wrong semicolon caused this variable to become a boolean(true) instead of a string
    // This fix checks for that exact scenario and forces it back to the default "right" if needed.
    // If this variable is set correctly (i.e. is a string), it will retain that value
    $cnb_options['appearance'] = ($cnb_options['appearance'] === true) ? $cnb_defaults['appearance'] : $cnb_options['appearance'];
    if(cnb_update_needed($cnb_options)) { // Check current version and if it needs an update
        $cnb_options['active'] = isset($cnb_options['active'])
            ? $cnb_options['active'] == 1 ? 1 : 0
            : $cnb_defaults['active'];
        $cnb_options['number'] = isset($cnb_options['number']) ? $cnb_options['number'] : $cnb_defaults['number'];
        $cnb_options['text'] = isset($cnb_options['text']) ? $cnb_options['text'] : $cnb_defaults['text'];
        $cnb_options['color'] = !empty($cnb_options['color']) ? $cnb_options['color'] : $cnb_defaults['color'];
        $cnb_options['iconcolor'] = isset($cnb_options['iconcolor']) ? $cnb_options['iconcolor'] : $cnb_defaults['iconcolor'];
        $cnb_options['appearance'] = !empty($cnb_options['appearance']) ? $cnb_options['appearance']: $cnb_defaults['appearance'];
        $cnb_options['hideIcon'] = isset($cnb_options['hideIcon'])
            ? $cnb_options['hideIcon'] == 1 ? 1 : 0
            : $cnb_defaults['hideIcon'];
        $cnb_options['limit'] = isset($cnb_options['limit']) ? $cnb_options['limit'] : $cnb_defaults['limit'];
        $cnb_options['frontpage'] = isset($cnb_options['frontpage'])
            ? $cnb_options['frontpage'] == 1 ? 1 : 0
            : $cnb_defaults['frontpage'];
        $cnb_options['conversions'] = isset($cnb_options['conversions'])
            ? ($cnb_options['conversions'] == 1 || $cnb_options['conversions'] == 2) ? (int) $cnb_options['conversions'] : 0
            : $cnb_defaults['conversions'];
        $cnb_options['zoom'] = isset($cnb_options['zoom']) ? $cnb_options['zoom'] : $cnb_defaults['zoom'];
        $cnb_options['z-index'] = isset($cnb_options['z-index']) ? $cnb_options['z-index'] : $cnb_defaults['z-index'];
        $cnb_options['tracking']  = !empty($cnb_options['tracking']) ? $cnb_options['tracking'] : $cnb_defaults['tracking'];
        $cnb_options['show']  = !empty($cnb_options['show']) ? $cnb_options['show'] : $cnb_defaults['show'];

        $updated_options = array(
            'active' => $cnb_options['active'],
            'number' => $cnb_options['number'],
            'text' => $cnb_options['text'],
            'color' => $cnb_options['color'],
            'iconcolor' => $cnb_options['iconcolor'],
            'appearance' => $cnb_options['appearance'],
            'hideIcon' => $cnb_options['hideIcon'],
            'limit' => $cnb_options['limit'],
            'frontpage' => $cnb_options['frontpage'],
            'conversions' => $cnb_options['conversions'],
            'zoom' => $cnb_options['zoom'],
            'z-index' => $cnb_options['z-index'],
            'tracking' => $cnb_options['tracking'],
            'show' => $cnb_options['show'],
            'version' => CNB_VERSION
        );

        if(array_key_exists('classic', $cnb_options) && $cnb_options['classic'] == 1 ) {
            $default_options['classic'] = 1;
        }

        update_option('cnb', $updated_options);
        $updated = array(true, $cnb_options['version']);  // Updated and previous version number
    } else {
        $updated = array(false, $cnb_options['version']); // Not updated and current version number
    }
    return $updated;
}

/**
 * Color functions to calculate borders
 *
 * @param $color
 * @param $direction
 * @return string
 */
function changeColor($color, $direction) {
    preg_match('/^#?([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})$/i', $color, $parts);
    if(!isset($direction) || $direction == "lighter") { $change = 45; } else { $change = -30; }
    for($i = 1; $i <= 3; $i++) {
        $parts[$i] = hexdec($parts[$i]);
        $parts[$i] = round($parts[$i] + $change);
        if($parts[$i] > 255) { $parts[$i] = 255; } elseif($parts[$i] < 0) { $parts[$i] = 0; }
        $parts[$i] = dechex($parts[$i]);
    }

    return '#' . str_pad($parts[1],2,"0",STR_PAD_LEFT) . str_pad($parts[2],2,"0",STR_PAD_LEFT) . str_pad($parts[3],2,"0",STR_PAD_LEFT);
}

function svg($color, $icon) {
    $phone = '<path d="M7.104 14.032l15.586 1.984c0 0-0.019 0.5 0 0.953c0.029 0.756-0.26 1.534-0.809 2.1 l-4.74 4.742c2.361 3.3 16.5 17.4 19.8 19.8l16.813 1.141c0 0 0 0.4 0 1.1 c-0.002 0.479-0.176 0.953-0.549 1.327l-6.504 6.505c0 0-11.261 0.988-25.925-13.674C6.117 25.3 7.1 14 7.1 14" fill="'.esc_attr($color).'"/><path d="M7.104 13.032l6.504-6.505c0.896-0.895 2.334-0.678 3.1 0.35l5.563 7.8 c0.738 1 0.5 2.531-0.36 3.426l-4.74 4.742c2.361 3.3 5.3 6.9 9.1 10.699c3.842 3.8 7.4 6.7 10.7 9.1 l4.74-4.742c0.897-0.895 2.471-1.026 3.498-0.289l7.646 5.455c1.025 0.7 1.3 2.2 0.4 3.105l-6.504 6.5 c0 0-11.262 0.988-25.925-13.674C6.117 24.3 7.1 13 7.1 13" fill="'.esc_attr($icon).'"/>';
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 60 60">' . $phone . '</svg>';
    return base64_encode($svg);
}

/**
 * Returns true if the `active` flag is set and is enabled
 *
 * @param $cnb_options array the current set of Button Options
 * @return bool
 */
function isButtonActive($cnb_options) {
    $active = isset($cnb_options['active']) && $cnb_options['active'] == 1;
    $cloud =  isset($cnb_options['cloud_enabled']) && $cnb_options['cloud_enabled'] == 1;
    return $active || $cloud;
}

function getZindexMap() {
    return array(
        10 => 2147483647,
        9 => 214748365,
        8 => 21474836,
        7 => 2147484,
        6 => 214748,
        5 => 21475,
        4 => 2147,
        3 => 215,
        2 => 21,
        1 => 2
    );
}

/**
 * @param $value int 1-10
 * @return int
 */
function zindex($value) {
    $zindexMap = getZindexMap();
    return $zindexMap[$value];
}

function zindexToOrder($zindex) {
    // This starts at the higher number
    foreach (getZindexMap() as $order => $value) {
        if ($zindex >= $value) return $order;
    }
    return 1;
}

function cnb_actiontype_to_icontext($actionType) {
    switch ($actionType) {
        case 'PHONE': return 'call';
        case 'ANCHOR': return 'anchor';
        case 'WHATSAPP': return 'whatsapp';
        case 'EMAIL': return 'email';
        case 'LINK': return 'link';
        case 'MAP': return 'directions';
        case 'HOURS': return 'access_time';
    }
}

function cnb_check_for_caching() {
    $caching_plugins = array(
        'autoptimize/autoptimize.php',
        'breeze/breeze.php',
        'cache-control/cache-control.php',
        'cache-enabler/cache-enabler.php',
        'comet-cache/comet-cache.php',
        'fast-velocity-minify/fvm.php',
        'hyper-cache/plugin.php',
        'litespeed-cache/litespeed-cache.php',
        'simple-cache/simple-cache.php',
        'w3-total-cache/w3-total-cache.php',
        'wp-fastest-cache/wpFastestCache.php',
        'wp-super-cache/wp-cache.php'
    );
    $active = FALSE; //Default is false
    $name = 'none'; // Default name is none
    foreach ($caching_plugins as $plugin) {
        if ( is_plugin_active( $plugin ) ) {
            $active = TRUE;
            $name = explode('/', $plugin);
            $name = $name[0];
            break;
        }
    }
    return array($active,$name);
}
function cnb_utm_params($element, $page) {
    $output  = "?utm_source=wp-plugin";
    $output .= "&utm_medium=referral";
    $output .= "&utm_campaign=" . $element;
    $output .= "&utm_term=" . $page;
    echo $output;
}

/**
 * Returns the filtered ID from the GET parameter
 * @return mixed
 */
function cnb_get_button_id() {
    return filter_input( INPUT_GET, 'id', FILTER_SANITIZE_STRING );
}

/**
 * @return CnbNotice[]
 */
function cnb_get_cloud_notices() {
    global $cnb_options;

    $notices = array();
    if (isset($cnb_options['cloud_enabled']) && $cnb_options['cloud_enabled'] == 1) {

        $cnb_user = CnbAppRemote::cnb_remote_get_user_info();
        $cnb_cloud_buttons_this_domain = array();

        if ($cnb_user instanceof WP_Error) {
            if ($cnb_user->get_error_code() === 'CNB_API_NOT_SETUP_YET') {
                // Notice: You're almost there! (enter API key)
                cnb_settings_get_account_missing_notice();
            } else if ($cnb_user->get_error_code() === 'CNB_API_KEY_INVALID') {
                // Notice: API key is incorrect
                cnb_settings_api_key_invalid_notice();
            } else {
                // Notice: something went wrong
                cnb_generic_error_notice( $cnb_user );
            }
        } else {
            $cnb_cloud_domain = CnbAppRemote::cnb_remote_get_wp_domain();
            if ( $cnb_cloud_domain instanceof WP_Error ) {
                // Notice: Domain not found yet
                //cnb_settings_get_domain_missing_notice( $cnb_cloud_domain );

                // Instead, create the domain
                $_notices = CnbAdminCloud::cnb_wp_create_domain( $cnb_user );
                $notices = array_merge($notices, $_notices);

                // Fix for https://github.com/callnowbutton/wp-plugin/issues/295
                // We also need to refetch the domain since it is a WP_Error at the moment,
                // Since it as just created, we can just refetch it.
                $cnb_cloud_domain = CnbAppRemote::cnb_remote_get_wp_domain();
            }

            // Check if any buttons are for the current WP domain
            $cnb_cloud_buttons = CnbAppRemote::cnb_remote_get_buttons();
            if ( $cnb_cloud_buttons instanceof WP_Error ) {
                // Could not retrieve Buttons
                cnb_settings_get_buttons_missing_notice( $cnb_cloud_buttons );
            } else if ( $cnb_cloud_buttons !== null ) {
                $cnb_cloud_buttons_this_domain = array_filter( $cnb_cloud_buttons, function ( $button ) use ( $cnb_cloud_domain ) {
                    // Fix for https://github.com/callnowbutton/wp-plugin/issues/295
                    if (is_wp_error($cnb_cloud_domain)) return false;
                    return $button->domain === $cnb_cloud_domain->id;
                } );
            }
        }

        $cnb_cloud_domain = CnbAppRemote::cnb_remote_get_wp_domain();
        $migration_done = get_option('cnb_cloud_migration_done');
        if ( !is_wp_error($cnb_cloud_domain)
             && count($cnb_cloud_buttons_this_domain) === 0
             && !$migration_done) {
            // Notice: Creating your first button
            //cnb_settings_get_button_missing_notice();

            // Instead, migrate the button
            $_notices = CnbAdminCloud::cnb_wp_migrate_button();
            $notices = array_merge($notices, $_notices);

            // We should really only do this once, so we need to save something in the settings to stop continious migration.
            add_option('cnb_cloud_migration_done', true);
        }
    }
    return $notices;
}

/**
 * @return CnbNotice[]
 */
function cnb_get_notices() {
    $transient_id = filter_input( INPUT_GET, 'tid', FILTER_SANITIZE_STRING );

    $notices = array();
    if ($transient_id) {
        $notices_cloud = get_transient($transient_id);
        if (is_array($notices_cloud)) {
            $notices = array_merge($notices, $notices_cloud);
        }
        delete_transient($transient_id);
    }

    $options_notice = get_transient('cnb-options');
    if ($options_notice) {
        $notices = array_merge($notices, $options_notice);
        delete_transient('cnb-options');
    }

    return $notices;
}

/**
 * Based on https://www.php.net/manual/en/function.array-column.php#123045
 *
 * For PHP < 5.5.0
 */
function array_column_ext($array, $column_key, $index_key = null) {
    $result = array();
    foreach ($array as $subarray => $value) {
        if (array_key_exists($column_key,$value)) { $val = $value[$column_key]; }
        else if ($column_key === null) { $val = $value; }
        else { continue; }

        if ($index_key === null) { $result[] = $val; }
        elseif ($index_key == -1 || array_key_exists($index_key,$value)) {
            $result[($index_key == -1)?$subarray: $value[$index_key]] = $val;
        }
    }
    return $result;
}

/**
 * For PHP < 5.5.0
 */
if (!function_exists('boolval')) {
    function boolval($val) {
        return (bool) $val;
    }
}

/**
 * For Wordpress < 4.0
 */
if (!function_exists('wp_generate_uuid4')) {
    function wp_generate_uuid4() {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand( 0, 0xffff ),
            mt_rand( 0, 0xffff ),
            mt_rand( 0, 0xffff ),
            mt_rand( 0, 0x0fff ) | 0x4000,
            mt_rand( 0, 0x3fff ) | 0x8000,
            mt_rand( 0, 0xffff ),
            mt_rand( 0, 0xffff ),
            mt_rand( 0, 0xffff )
        );
    }
}

function cnb_array_column($array, $column_key, $index_key = null) {
    if (version_compare(PHP_VERSION, '7.0.0', '>=')) {
        // phpcs:ignore
        return array_column($array, $column_key, $index_key);
    } else {
        // Convert objects to array, since PHP < 7 cannot deal with objects as the first argument
        $array_arr = array();
        foreach ($array as $key => $value) {
            $array_arr[$key] = (array) $value;
        }
        if(!function_exists('array_column')) {
            return array_column_ext($array_arr, $column_key, $index_key);
        }
        // phpcs:ignore
        return array_column($array_arr, $column_key, $index_key);
    }
}

function cnb_timestamp_to_string($timestamp){
    if ( $timestamp instanceof stdClass ) {
        return date("r", $timestamp->seconds);
    }
    return $timestamp;
}

/***
 * To ensure arrays are properly sanitized to WordPress Codex standards,
 * they encourage usage of sanitize_text_field(). That only works with a single
 * variable (string). This function allows for a full blown array to get sanitized
 * properly, while sanitizing each individual value in a key -> value pair.
 *
 * Source: https://wordpress.stackexchange.com/questions/24736/wordpress-sanitize-array
 * Author: Broshi, answered Feb 5 '17 at 9:14
 * Via:    https://developer.wordpress.org/reference/functions/sanitize_text_field/
 */
function cnb_wporg_recursive_sanitize_text_field( $array ) {
    foreach ( $array as $key => &$value ) {
        if ( is_array( $value ) ) {
            $value = cnb_wporg_recursive_sanitize_text_field( $value );
        } else {
            $value = sanitize_text_field( $value );
        }
    }
    return $array;
}

/**
 * Same as check_ajax_referer, but does not die() by default
 * @param string $action
 * @param bool $query_arg
 * @param bool $die
 * @return false|int|mixed|void
 */
function cnb_check_ajax_referer($action, $query_arg=false, $die=false) {
    return check_ajax_referer($action, $query_arg, $die);
}
