<?php

require_once dirname( __FILE__ ) . '/api/CnbAppRemote.php';
require_once dirname( __FILE__ ) . '/api/CnbAdminCloud.php';
require_once dirname( __FILE__ ) . '/domain-edit.php';
require_once dirname( __FILE__ ) . '/legacy-edit.php';
require_once dirname( __FILE__ ) . '/partials/admin-functions.php';
require_once dirname( __FILE__ ) . '/partials/admin-header.php';
require_once dirname( __FILE__ ) . '/partials/admin-footer.php';
require_once dirname( __FILE__ ) . '/../utils/notices.php';

function cnb_add_header_settings() {
    echo 'Settings';
}

function cnb_settings_create_tab_url($tab) {
    $url = admin_url('admin.php');
    $tab_link =
        add_query_arg(
            array(
                'page' => 'call-now-button-settings',
                'tab' => $tab),
            $url );
    return esc_url( $tab_link );
}

/**
 * For the Legacy button, disallow setting it to active with a missing phone number
 *
 * @param array $input
 *
 * @return void|WP_Error
 */
function cnb_settings_disallow_active_without_phone_number($input) {
    $number = trim($input['number']);
    $cloud_enabled = array_key_exists('cloud_enabled', $input) ? $input['cloud_enabled'] : 0;
    if ($input['active'] == 1 && $cloud_enabled == 0 && empty($number)) {
        return new WP_Error('CNB_PHONE_NUMBER_MISSING', 'Please enter a phone number before enabling your button.');
    }
}

/**
 * @param array $input
 *
 * @return array The new (fully adjusted) settings for <code>cnb</code>
 */
function cnb_settings_options_validate($input) {
    $original_settings = get_option('cnb');
    // When beta mode is disabled, disable the cloud as well
    if (array_key_exists('cloud_beta_enabled', $input) && $input['cloud_beta_enabled'] == 0) {
        $input['cloud_enabled'] = 0;
        $input['status'] = 'enabled';
        delete_option('cnb_cloud_migration_done');
    }

    // Since "active" and "cloud_enabled" have been merged into "status", we have to deal with that
    if (array_key_exists('status', $input)) {
        switch ($input['status']) {
            case 'disabled':
                $input['active'] = 0;
                $input['cloud_enabled'] = 0;
                break;
            case 'enabled':
                $input['active'] = 1;
                $input['cloud_enabled'] = 0;
                break;
            case 'cloud':
                $input['active'] = 1;
                $input['cloud_enabled'] = 1;
                break;
        }
    }

    $messages = array();

    // Cloud Domain settings can be set here as well
    if(array_key_exists('domain', $_POST) &&
       array_key_exists('cloud_enabled', $input) &&
       $input['cloud_enabled'] == 1) {
        $domain = $_POST['domain'];
        $transient_id = null;
        cnb_admin_page_domain_edit_process_domain($domain, $transient_id);
        $message = get_transient($transient_id);

        // Only add the message to the results if something went wrong
        if (is_array($message) && sizeof($message) === 1 &&
            $message[0] instanceof CnbNotice && $message[0]->type != 'success') {
            $messages = array_merge( $messages, $message);
        }

        // Remove from settings
        unset($input['domain']);
    }

    // If api_key is empty, assume unchanged and unset it (so it uses the old value)
    if (isset($input['api_key']) && empty($input['api_key'])) {
        unset($input['api_key']);
    }

    $updated_options = array_merge($original_settings, $input);

    // If the cloud is enabled, this is a fail-safe to ensure the user ID is set, even if it isn't
    // explicitly set by the user YET. (since the whole "cnb[cloud_use_id] input field doesn't exist yet...
    if (isset($updated_options['cloud_enabled']) && $updated_options['cloud_enabled'] == 1) {
        $cloud_id = CnbAdminCloud::cnb_set_default_option_for_cloud( $updated_options );
        // Normally, this returns null, since there is a cnb[cloud_use_id].
        if ($cloud_id != null) {
            $updated_options['cloud_use_id'] = $cloud_id;
        }
    }

    // Check for legacy button
    $check = cnb_settings_disallow_active_without_phone_number($updated_options);
    if (is_wp_error($check)) {
        if ($check->get_error_code() === 'CNB_PHONE_NUMBER_MISSING') {
            $messages[] = new CnbNotice( 'warning', '<p>Your settings have been updated, but your button could <strong>not</strong> be enabled. Please enter a <i>Phone number</i>.</p>' );
            // Reset enabled/active to false
            $updated_options['active'] = 0;
        } else {
            // This part is VERY generic and should not be reached, since
            // cnb_settings_disallow_active_without_phone_number() returns a single WP_Error.
            // But just in case, this is here for other unseen errors..
            $messages[] = CnbAdminCloud::cnb_admin_get_error_message( 'save', 'settings', $check );
        }
    } else {
        $messages[] = new CnbNotice( 'success', '<p>Your settings have been updated!</p>' );
    }

    $transient_id = 'cnb-options';
    $_GET['tid'] = $transient_id;
    set_transient($transient_id, $messages, HOUR_IN_SECONDS);

    return $updated_options;
}

/**
 * Only true if beta is in the URL or beta mode is enabled via the options
 * @return bool
 */
function cnb_is_beta_user_via_url() {
    return filter_input( INPUT_GET, 'beta', FILTER_SANITIZE_STRING ) !== null;
}

/**
 * @param $cnb_options array Regular 'cnb' options
 *
 * @return bool
 */
function cnb_is_beta_user_via_options($cnb_options) {
    return is_array($cnb_options) && array_key_exists('cloud_beta_enabled', $cnb_options) && $cnb_options['cloud_beta_enabled'] == 1;
}

/**
 * @param $cnb_options array Regular 'cnb' options
 *
 * @return bool
 */
function cnb_is_beta_user_via_url_only($cnb_options) {
    $beta_url = cnb_is_beta_user_via_url();
    $beta_options = cnb_is_beta_user_via_options($cnb_options);
    return $beta_url && !$beta_options;
}
/**
 * Only true if beta is in the URL or beta mode is enabled via the options
 * @param $cnb_options array Regular 'cnb' options
 * @return bool
 */
function cnb_is_beta_user($cnb_options) {
//    $beta_url = cnb_is_beta_user_via_url();
//    $beta_options = cnb_is_beta_user_via_options($cnb_options);
//    return $beta_url || $beta_options;
    return cnb_is_beta_user_via_options($cnb_options);
}

function cnb_admin_settings_create_cloud_domain($cnb_user) {
    $nonce = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
    if( isset( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $nonce, 'cnb_create_cloud_domain') ) {
        return CnbAdminCloud::cnb_wp_create_domain( $cnb_user );
    }
    return null;
}

function cnb_admin_settings_migrate_legacy_to_cloud() {
    $nonce = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
    if( isset( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $nonce, 'cnb_migrate_legacy_button') ) {
        return CnbAdminCloud::cnb_wp_migrate_button();
    }
    return null;
}

function cnb_admin_setting_migrate() {
    // Update the cloud if requested
    $cnb_cloud_notifications = array();

    $action = !empty($_POST['action']) ? sanitize_text_field($_POST['action']) : null;
    switch ($action) {
        case 'cnb_create_cloud_domain':
            $cnb_user = CnbAppRemote::cnb_remote_get_user_info();
            $cnb_cloud_notifications = cnb_admin_settings_create_cloud_domain($cnb_user);
            break;
        case 'cnb_migrate_legacy_button':
            $cnb_cloud_notifications = cnb_admin_settings_migrate_legacy_to_cloud();
            break;
    }

    // redirect the user to the appropriate page
    $transient_id = 'cnb-' . wp_generate_uuid4();
    set_transient($transient_id, $cnb_cloud_notifications, HOUR_IN_SECONDS);

    // Create link
    $url = admin_url('admin.php');
    $redirect_link =
        add_query_arg(
            array(
                'page' => 'call-now-button-settings',
                'tid' => $transient_id,
            ),
            $url );
    $redirect_url = esc_url_raw( $redirect_link );
    wp_safe_redirect($redirect_url);
    exit;
}

function cnb_admin_settings_page() {
    global $cnb_options;

    add_action('cnb_header_name', 'cnb_add_header_settings');

    // Fix for https://github.com/callnowbutton/wp-plugin/issues/263
    $cnb_options['cloud_enabled'] = isset($cnb_options['cloud_enabled']) ? $cnb_options['cloud_enabled'] : 0;

    $beta = cnb_is_beta_user($cnb_options);
    $beta_tmp = cnb_is_beta_user_via_url_only($cnb_options);
    if ($beta_tmp) {
        cnb_activate_beta_render_notice();
    }

    if ($beta && $cnb_options['cloud_enabled']) {
        $cnbAppRemote = new CnbAppRemote();
        $cnb_user     = CnbAppRemote::cnb_remote_get_user_info();

        if ( ! ( $cnb_user instanceof WP_Error ) ) {
            // Let's check if the domain already exists
            $cnb_cloud_domain   = CnbAppRemote::cnb_remote_get_wp_domain();
            $cnb_cloud_domains  = CnbAppRemote::cnb_remote_get_domains();
            $cnb_clean_site_url = $cnbAppRemote->cnb_clean_site_url();
            CnbDomain::setSaneDefault($cnb_cloud_domain);
        }

        // Set "cloud_use_id" to the user if "cloud_use_id" has not been set yet
        if ( ! array_key_exists( 'cloud_use_id', $cnb_options ) ) {
            $cnb_options['cloud_use_id'] = 0;
            if ( ! ( $cnb_user instanceof WP_Error ) ) {
                $cnb_options['cloud_use_id'] = $cnb_user->id;
            }
        }
    }

    // If beta is not enabled, there are actually no "advanced" tab, so we reset to "basic_options"
    $active_advanced_tab = cnb_is_active_tab('advanced_options');
    if (!$beta && !empty($active_advanced_tab)) {
        $_GET['tab'] = 'basic_options';
    }

    do_action('cnb_header');
    ?>

    <?php
    $show_advanced_view_only = array_key_exists('advanced_view', $cnb_options) && $cnb_options['advanced_view'] === 1;
    if ($show_advanced_view_only) {
        echo '<script type="text/javascript">show_advanced_view_only_set=1</script>';
    }
    ?>

    <h2 class="nav-tab-wrapper">
        <a href="<?php echo cnb_settings_create_tab_url('basic_options') ?>"
           class="nav-tab <?php echo cnb_is_active_tab('basic_options') ?>">General</a>
        <?php if ($cnb_options['status'] === 'cloud') { ?>
            <a href="<?php echo cnb_settings_create_tab_url('account_options') ?>"
               class="nav-tab <?php echo cnb_is_active_tab('account_options') ?>">Account</a>
        <?php } ?>
    <?php if ($beta) { ?>
            <a href="<?php echo cnb_settings_create_tab_url('advanced_options') ?>"
               class="nav-tab <?php echo cnb_is_active_tab('advanced_options') ?>">Advanced</a>
    <?php } ?>
    </h2>
    <form method="post" action="<?php echo esc_url( admin_url('options.php') ); ?>" class="cnb-container">
        <?php settings_fields('cnb_options'); ?>
        <table class="form-table <?php echo cnb_is_active_tab('basic_options'); ?>">
            <?php if ($beta) { ?>
            <tr><th colspan="2"></th></tr>
            <tr>
                <th scope="row">Account type</th>
                <td>
                    <div class="cnb-radio-item">
                        <input type="radio" name="cnb[cloud_enabled]" id="cnb_cloud_disabled" value="0" <?php checked('0', $cnb_options['cloud_enabled']); ?>>
                        <label for="cnb_cloud_disabled">Normal</label>
                        <p class="description">One call button for your website.</p>
                    </div>
                    <div class="cnb-radio-item">
                        <input type="radio" name="cnb[cloud_enabled]" id="cnb_cloud_enabled" value="1" <?php checked('1', $cnb_options['cloud_enabled']); ?>>
                        <label for="cnb_cloud_enabled">Cloud</label>
                        <p class="description">Unlimited buttons for your website for calls, emails, WhatsApp, URLs and locations. Also provides a button scheduler and better page targeting.</p>
                    </div>
                </td>
            </tr>
            <?php } ?>
            <?php if ($cnb_options['status'] !== 'cloud') { ?>
            <tr>
                <th colspan="2"><h2>Tracking</h2></th>
            </tr>
            <?php
            cnb_admin_page_leagcy_edit_render_tracking();
            cnb_admin_page_leagcy_edit_render_conversions();
            ?>
            <tr>
                <th colspan="2"><h2>Button display</h2></th>
            </tr>
            <?php
            cnb_admin_page_leagcy_edit_render_zoom();
            cnb_admin_page_leagcy_edit_render_zindex();

            if($cnb_options['classic'] == 1) { ?>
            <tr class="classic">
                <th scope="row">Classic button: <a href="https://callnowbutton.com/new-button-design/<?php cnb_utm_params("question-mark", "new-button-design"); ?>" target="_blank" class="cnb-nounderscore">
                        <span class="dashicons dashicons-editor-help"></span>
                    </a></th>
                <td>
                    <input type="hidden" name="cnb[classic]" value="0" />
                    <input id="classic" name="cnb[classic]" type="checkbox" value="1" <?php checked('1', $cnb_options['classic']); ?> /> <label title="Enable" for="classic">Active</label>
                </td>
            </tr>
            <?php
            }
        }
            if($cnb_options['status'] === 'cloud' && isset($cnb_cloud_domain) && !($cnb_cloud_domain instanceof WP_Error)) {
                cnb_admin_page_domain_edit_render_form_plan_details($cnb_cloud_domain);
                cnb_admin_page_domain_edit_render_form_tracking($cnb_cloud_domain);
                cnb_admin_page_domain_edit_render_form_button_display($cnb_cloud_domain);
            } ?>
        </table>
        <?php if ($beta && $cnb_options['status'] === 'cloud') { ?>
        <table class="form-table <?php echo cnb_is_active_tab('account_options'); ?>">
            <tr><th colspan="2"></th></tr>
            <tr>
                <th scope="row">API key</th>
                <td>
                    <?php if ($cnb_user instanceof WP_Error || $show_advanced_view_only) { ?>
                        <label><input type="text" class="regular-text" name="cnb[api_key]" id="cnb_api_key"
                               placeholder="e.g. b52c3f83-38dc-4493-bc90-642da5be7e39"/></label>
                        <?php if ($cnb_user instanceof WP_Error) { ?><p class="description">Get you key at <a href="<?php echo CNB_WEBSITE?>"><?php echo CNB_WEBSITE?></a></p><?php } ?>
                    <?php } ?>
                    <?php if (isset($cnb_options['api_key']) && !is_wp_error($cnb_user)) { ?><p class="description"><span class="dashicons dashicons-saved"></span>Your key is set correctly.</p><?php }?>
                </td>
            </tr>
             <?php if ($cnb_user !== null && !$cnb_user instanceof WP_Error) { ?>
                 <tr>
                    <th scope="row">Account owner</th>
                    <td>
                        <?php esc_html_e($cnb_user->name) ?>
                        <?php
                            if ($cnb_user->email !== $cnb_user->name) { esc_html_e(' (' . $cnb_user->email . ')');
                        } ?>
                    </td>
                </tr>
                 <tr>
                    <th scope="row">Account ID</th>
                    <td><code><?php esc_html_e($cnb_user->id) ?></code></td>
                </tr>
            <?php } ?>
        </table>
        <?php } ?>
        <?php if ($beta) { ?>
        <table class="form-table <?php echo cnb_is_active_tab('advanced_options'); ?>">
                <tr><th colspan="2"></th></tr>
                <tr>
                    <th>Beta mode</th>
                    <td><label>
                            <input type="hidden" name="cnb[cloud_beta_enabled]" value="0" />
                            <input id="beta" type="checkbox" name="cnb[cloud_beta_enabled]" value="1" <?php checked('1', $beta); ?> />
                            <label title="Enabled" for="beta">Enabled</label>
                        </label>
                        <p class="description">You can stop being a beta user and return to the original button at any time</p>
                    </td>
                </tr>
            <?php if(isset($cnb_cloud_domain) && !($cnb_cloud_domain instanceof WP_Error) && $cnb_options['status'] === 'cloud') {
                ?>
                <tr>
                    <th colspan="2"><h2>Domain settings</h2></th>
                </tr>
                <?php
                cnb_admin_page_domain_edit_render_form_advanced($cnb_cloud_domain, false);
            } ?>
            <tr class="when-cloud-enabled cnb_advanced_view">
                <th colspan="2"><h2>For power users</h2></th>
            </tr>
            <tr class="when-cloud-enabled cnb_advanced_view">
                <th>Advanced view</th>
                <td>
                  <input type="hidden" name="cnb[advanced_view]" value="0" />
                  <input id="cnb-advanced-view" type="checkbox" name="cnb[advanced_view]" value="1" <?php checked('1', $cnb_options['advanced_view']); ?> />
                  <label for="cnb-advanced-view">Enable</label>
                  <p class="description">For power users only.</p>
                </td>
            </tr>
            <?php if ($cnb_options['status'] === 'cloud') { ?>
                <tr class="cnb_advanced_view">
                    <th>Show traces</th>
                    <td>
                      <input type="hidden" name="cnb[footer_show_traces]" value="0" />
                      <input id="cnb-show-traces" type="checkbox" name="cnb[footer_show_traces]" value="1" <?php checked('1', $cnb_options['footer_show_traces']); ?> />
                      <label for="cnb-show-traces">Enable</label>
                      <p class="description">Display API calls and timings in the footer.</p>
                    </td>
                </tr>
                <?php if (!($cnb_user instanceof WP_Error) && isset($cnb_cloud_domain) && $cnb_options['status'] === 'cloud') { ?>
                    <tr class="when-cloud-enabled cnb_advanced_view">
                        <th scope="row">Domain</th>
                        <td>
                            <div>
                                <p>Your domain: <strong><?php esc_html_e($cnb_clean_site_url) ?></strong></p>
                                <?php if ($cnb_cloud_domain instanceof WP_Error) {
                                    CnbAdminNotices::get_instance()->warning('Almost there! Create your domain using the button at the top of this page.')
                                    ?>
                                <?php } else { ?>
                                    <p class="description">Your domain <strong><?php esc_html_e($cnb_cloud_domain->name) ?></strong> is connected with ID <code><?php esc_html_e($cnb_cloud_domain->id) ?></code></p>
                                <?php }?>
                                <label><select name="cnb[cloud_use_id]">

                                    <optgroup label="Account-wide">
                                        <option
                                                value="<?php esc_attr_e($cnb_user->id) ?>"
                                            <?php selected($cnb_user->id, $cnb_options['cloud_use_id']) ?>
                                        >
                                            Let the button decide
                                        </option>
                                    </optgroup>
                                    <optgroup label="Specific domain">
                                        <?php foreach ($cnb_cloud_domains as $domain) { ?>
                                            <option
                                                    value="<?php esc_attr_e($domain->id) ?>"
                                                <?php selected($domain->id, $cnb_options['cloud_use_id']) ?>
                                            >
                                                <?php esc_html_e($domain->name) ?>
                                            </option>
                                        <?php } ?>
                                    </optgroup>
                                </select></label>
                                <p class="description">The current value is <code><?php esc_html_e($cnb_options['cloud_use_id']) ?></code></p>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
            <tr class="when-cloud-enabled cnb_advanced_view">
                <th>Show all buttons</th>
                <td>
                  <input type="hidden" name="cnb[show_all_buttons_for_domain]" value="0" />
                  <input id="cnb-all-domains" type="checkbox" name="cnb[show_all_buttons_for_domain]" value="1" <?php checked('1', $cnb_options['show_all_buttons_for_domain']); ?> />
                  <label for="cnb-all-domains">Enable</label>
                  <p class="description">When checked, the "All Buttons" overview shows all buttons for this account, not just for the current domain.</p>
                </td>
            </tr>
            <tr class="when-cloud-enabled cnb_advanced_view">
                <th>API endpoint</th>
                <td><label>
                        <input type="text" name="cnb[api_base]" class="regular-text"
                               value="<?php echo CnbAppRemote::cnb_get_api_base() ?>" />
                    </label>
                    <p class="description">The API endpoint to use to communicate with the CallNowButton Cloud service.<br />
                        <strong>Do not change this unless you know what you're doing!</strong>
                    </p>
                </td>
            </tr>
            <tr class="cnb_advanced_view">
              <th>API caching</th>
                <td>
                  <input type="hidden" name="cnb[api_caching]" value="0" />
                  <input id="cnb-api-caching" type="checkbox" name="cnb[api_caching]" value="1" <?php checked('1', $cnb_options['api_caching']); ?> />
                  <label for="cnb-api-caching">Enable</label>
                  <p class="description">Cache API requests (using Wordpress transients)</p>
                </td>
              </tr>
            <?php } // end of beta check ?>
        </table>
        <?php } ?>
        <input type="hidden" name="cnb[version]" value="<?php echo CNB_VERSION; ?>"/>
        <p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>"/></p>
    </form>

    <?php do_action('cnb_footer'); ?>
<?php }
