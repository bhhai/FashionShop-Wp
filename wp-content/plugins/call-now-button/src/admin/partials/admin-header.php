<?php
require_once dirname( __FILE__ ) . '/../../utils/notices.php';
require_once dirname( __FILE__ ) . '/admin-functions.php';

function cnb_admin_header_no_args() {
    global $cnb_options, $cnb_settings;

    $cnb_notices = cnb_get_notices();
    $cnb_changelog = cnb_get_changelog();

    cnb_admin_header_args($cnb_options, $cnb_settings, $cnb_notices, $cnb_changelog);
    do_action('cnb_admin_notices');
}

function cnb_admin_header_args( $cnb_options, $cnb_settings, $cnb_cloud_notifications = array(), $cnb_changelog = array() ) {
    echo '<div class="wrap">'; // This is closed in cnb_admin_footer

    echo '<!--## NOTIFICATION BARS ##  -->';
    $cnb_cloud_notifications = array_merge($cnb_cloud_notifications, cnb_get_cloud_notices());

    // Display notification that the button is active or inactive
    if ( $cnb_options['active'] != 1 && !empty($cnb_options['number']) && $cnb_options['status'] != 'cloud' ) {
        cnb_button_disabled_notice();
    }

    if ( $cnb_options['active'] == 1 && $cnb_options['status'] == 'enabled' && empty($cnb_options['number'])) {
        cnb_button_classic_enabled_but_no_number_notice();
    }

    // Display notification that there's a caching plugin active
    if ( isset( $_GET['settings-updated'] ) ) {
        $cnb_caching_check = cnb_check_for_caching();
        if ( $cnb_caching_check[0] == true ) {
            cnb_caching_plugin_warning_notice($cnb_caching_check[1]);
        }
    }

    // Show the notifications after updating the cloud
    if ( is_array( $cnb_cloud_notifications ) ) {
        $adminNotices = CnbAdminNotices::get_instance();
        foreach ( $cnb_cloud_notifications as $cnb_cloud_notification ) {
            if (is_string($cnb_cloud_notification)) {
                $adminNotices->info($cnb_cloud_notification);
            } else {
                $adminNotices->notice( $cnb_cloud_notification);
            }
        }
    }

    // inform existing users about updates to the button
    if ( $cnb_settings['updated'][0] ) {
        cnb_upgrade_notice($cnb_settings['updated'][1], $cnb_changelog);
    }
}

function cnb_admin_header() {
    do_action('cnb_in_admin_header');
    echo '<h1>';
    do_action( 'cnb_header_name' );
    do_action( 'cnb_after_header' );
    echo '</h1>';
}
