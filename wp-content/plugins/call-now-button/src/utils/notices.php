<?php

require_once dirname( __FILE__ ) . '/CnbAdminNotices.class.php';
require_once dirname( __FILE__ ) . '/../admin/api/CnbAdminCloud.php';

function cnb_upgrade_notice($cnb_old_version, $cnb_changelog) {

    $message =  '<h3>' . CNB_NAME . ' has been updated!</h3><h4>What\'s new?</h4>';
    // Only on first run after update show list of changes since last update
    foreach ( $cnb_changelog as $key => $value ) {
        if ( $key > $cnb_old_version ) {
            $message .= '<h3>' . esc_html($key) . '</h3>';
            if ( is_array( $value ) ) {
                foreach ( $value as $item ) {
                    $message .= '<p><span class="dashicons dashicons-yes"></span> ' . esc_html($item) . '</p>';
                }
            } else {
                $message .= '<p><span class="dashicons dashicons-yes"></span> ' . esc_html($value) . '</p>';
            }
        }
    }

    $adminNotices = CnbAdminNotices::get_instance();
    $adminNotices->warning($message, 'cnb_update_'.$cnb_old_version);

}

function cnb_settings_get_account_missing_notice() {
    $message = '<h3 class="title">Activate cloud version</h3>
            <p>To activate the cloud version of the Call Now Button, you\'ll need an account at
                <a href="https://app.callnowbutton.com?utm_source=wp-plugin&utm_medium=referral&utm_campaign=beta_tester&utm_term=sign-up-for-api">https://app.callnowbutton.com</a>, get a unique API key and add it to the field below.</p>
                  <p>Let\'s go:</p>
            <ol>
                <li>Create your account on <a href="https://app.callnowbutton.com?utm_source=wp-plugin&utm_medium=referral&utm_campaign=beta_tester&utm_term=sign-up-for-api">https://app.callnowbutton.com</a></li>
                <li>Go to your profile info by clicking on the user icon in the top right corner and then click <strong>Create new API key</strong>.</li>
                <li>Copy the API key, paste it into the field below and click <strong>Save API key</strong>.</li>
            </ol>';
    $message .= cnb_settings_api_key_input();

    $adminNotices = CnbAdminNotices::get_instance();
    $adminNotices->warning($message);
}

function cnb_settings_api_key_invalid_notice() {
    $message = '<h3 class="title">API key is incorrect</h3>
            <p>Your API key seems to be invalid. This might have a few reasons:
            <ol>
                <li>You didn\'t copy the entire API key (it is case-sensative and should be in the form of a UUID).</li>
                <li>
                    You have deleted your API key (check <a href="https://app.callnowbutton.com/app/profile">your profile</a>).
                    <ol>
                    <li>Go to your profile page and click <strong>Create new API key</strong>.</li>
                    <li>Copy the API key into the "API key" field below</li>
                    <li>Click "Save API key"</li>
                    </ol>
                </li>
                <li>As unlikely as it is, our service might be experiencing issues (check <a href="https://status.callnowbutton.com">our status page</a>).</li>
            </ol>';
    $message .= cnb_settings_api_key_input();

    $adminNotices = CnbAdminNotices::get_instance();
    $adminNotices->warning($message);
}

function cnb_generic_error_notice($user) {
    $message = '<h3 class="title">Something went wrong!</h3>
            <p>Something has gone wrong and we do not know why...</p>
            <p>As unlikely as it is, our service might be experiencing issues (check <a href="https://status.callnowbutton.com">our status page</a>).</p>
            <p>If you think you found a bug, please report it at our <a href="https://wordpress.org/support/plugin/call-now-button/">support forum</a>.';
    $message .= CnbAdminCloud::cnb_admin_get_error_message_details($user);

    $adminNotices = CnbAdminNotices::get_instance();
    $adminNotices->warning($message);
}

function cnb_settings_api_key_input() {
    global $cnb_options;

    $message = '<form method="post" action="' . esc_url( admin_url('options.php') ) . '" class="cnb-container">';
    ob_start();
    settings_fields('cnb_options');
    $message .= ob_get_clean();
    $message .= '<input type="hidden" name="page" value="call-now-button-settings" />
            <table class="form-table">
                <tr class="when-cloud-enabled">
                <th scope="row">API key</th>
                <td>
                    <input type="text" class="regular-text" name="cnb[api_key]"
                           placeholder="e.g. b52c3f83-38dc-4493-bc90-642da5be7e39"/>
                    <input type="submit" class="button-primary" value="' . __('Save API key') . '"/>
                </td>
                </table>
            </form>';
    return $message;
}

function cnb_settings_get_domain_missing_notice($domain) {
    $message = '<h3 class="title">Domain not found yet</h3>
                <p>You have enabled Cloud Hosting and are logged in,
                    but we need to create the domain remotely.</p>
                <p>
                <form action="' . esc_url( admin_url('admin-post.php') ) . '" method="post">
                    <input type="hidden" name="page" value="call-now-button-settings" />
                    <input type="hidden" name="action" value="cnb_create_cloud_domain" />
                    <input type="hidden" name="_wpnonce" value="' . wp_create_nonce('cnb_create_cloud_domain') .'" />
                    <input type="submit" value="Create domain" class="button button-secondary" />
                </form>
                </p>';
    $message .= CnbAdminCloud::cnb_admin_get_error_message_details( $domain );

    $adminNotices = CnbAdminNotices::get_instance();
    $adminNotices->warning($message);
}

function cnb_settings_get_button_missing_notice() {
    $message = '<h3 class="title">Creating your first button</h3>
            <p>You have enabled Cloud Hosting and have your domain setup,
            so now it\'s time to create your first button.</p>
            <p>To make it easy, we can migrate your existing button to the Cloud.</p>
            <p><form action="'. esc_url( admin_url('admin-post.php') ) .'" method="post">
                <input type="hidden" name="page" value="call-now-button-settings" />
                <input type="hidden" name="action" value="cnb_migrate_legacy_button" />
                <input type="hidden" name="_wpnonce" value="'. wp_create_nonce('cnb_migrate_legacy_button') .'" />
                <input type="submit" value="Migrate button" class="button button-secondary" />
            </form></p>';

    $notice = new CnbNotice('warning', $message);
    $notice->dismiss_option = 'cnb_settings_get_button_missing_notice';
    $adminNotices = CnbAdminNotices::get_instance();
    $adminNotices->notice($notice);
}

function cnb_settings_get_buttons_missing_notice($error) {
    $message = '<h3 class="title">Could not retrieve Buttons</h3>
            <p>Something unexpected went wrong retrieving the Buttons for this API key</p>';
    $message .= CnbAdminCloud::cnb_admin_get_error_message_details( $error );

    $adminNotices = CnbAdminNotices::get_instance();
    $adminNotices->warning($message);
}

function cnb_api_key_invalid_notice($error) {
    $url = admin_url('admin.php');
    $redirect_link =
        add_query_arg(
            array(
                'page' => 'call-now-button-settings',
            ),
            $url );
    $redirect_url = esc_url( $redirect_link );

    $message = '<h3 class="title">API Key invalid</h3>
            <p>You have enabled Cloud Hosting, but you need a valid API key from CallNowButtom</p>
            <p>Go to <a href="'.$redirect_url.'">Settings</a> for instructions.</p>';
    $message .= CnbAdminCloud::cnb_admin_get_error_message_details( $error );

    $adminNotices = CnbAdminNotices::get_instance();
    $adminNotices->renderError($message);

}

function cnb_button_disabled_notice() {
    $url = admin_url('admin.php');
    $redirect_link =
        add_query_arg(
            array(
                'page' => 'call-now-button',
            ),
            $url );
    $redirect_url = esc_url( $redirect_link );

    $message = '<p>Your button is currently <strong>not enabled</strong>. Click <a href="'.$redirect_url.'">here</a> to enable your button.</p>';

    $adminNotices = CnbAdminNotices::get_instance();
    $adminNotices->warning($message);
}

function cnb_button_classic_enabled_but_no_number_notice() {
    $url = admin_url('admin.php');
    $redirect_link =
        add_query_arg(
            array(
                'page' => 'call-now-button',
            ),
            $url );
    $redirect_url = esc_url( $redirect_link );

    $message = '<p>The Call Now Button is currently <strong>active without a phone number</strong>.
        Change the <i>Button status</i> under <a href="'.$redirect_url.'">My button</a> to disable or enter a phone number.</p>';

    $adminNotices = CnbAdminNotices::get_instance();
    $adminNotices->warning($message);
}

function cnb_caching_plugin_warning_notice($caching_plugin_name) {
    $message = '<p><span class="dashicons dashicons-warning"></span>
        Your website is using a <strong><i>Caching Plugin</i></strong> (' . $caching_plugin_name . ').
        If you\'re not seeing your button or your changes, make sure you empty your cache first.</p>';

    $adminNotices = CnbAdminNotices::get_instance();
    $adminNotices->error($message);
}

function cnb_activate_beta_render_notice() {
    $message = '<form method="post" action="' . esc_url( admin_url('options.php') ) . '" class="cnb-container">';
    ob_start();
    settings_fields('cnb_options');
    $message .= ob_get_clean();
    $message .= '<input type="hidden" name="page" value="call-now-button-settings" />
            <table>
                <tr>
                <th scope="row"</th>
                <td>
                    <input type="hidden" name="cnb[cloud_beta_enabled]" value="1" />
                    <input type="submit" class="button-primary" value="' . __('Activate beta mode') . '"/> For testing new unreleased functionality.
                </td>
                </table>
            </form>';
    $adminNotices = CnbAdminNotices::get_instance();
    $adminNotices->renderWarning($message);

}
