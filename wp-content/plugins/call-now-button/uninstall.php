<?php

// Doc: https://developer.wordpress.org/plugins/plugin-basics/uninstall-methods/

// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

// Delete known options
$option_names = array('cnb', 'cnb_cloud_migration_done');

foreach ($option_names as $option_name) {
    // Delete the standard options
    delete_option($option_name);

    // Delete site options in Multisite
    delete_site_option($option_name);
}

// Delete notice dismissals
$options_slug = 'call-now-button';

global $wpdb;
$wpdb->query(
    $wpdb->prepare(
"DELETE FROM $wpdb->options
       WHERE option_name
       LIKE %s",
    $options_slug.'_dismissed_%' )
);
