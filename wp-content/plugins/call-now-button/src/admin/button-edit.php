<?php

require_once dirname( __FILE__ ) . '/api/CnbAppRemote.php';
require_once dirname( __FILE__ ) . '/api/CnbAdminCloud.php';
require_once dirname( __FILE__ ) . '/partials/admin-functions.php';
require_once dirname( __FILE__ ) . '/partials/admin-header.php';
require_once dirname( __FILE__ ) . '/partials/admin-footer.php';
require_once dirname( __FILE__ ) . '/models/CnbButton.class.php';
require_once dirname( __FILE__ ) . '/models/CnbAction.class.php';
require_once dirname( __FILE__ ) . '/../utils/utils.php';
require_once dirname( __FILE__ ) . '/action-overview.php';
require_once dirname( __FILE__ ) . '/action-edit.php';

/**
 * Renders the "Edit <type>" header
 *
 * @param $button CnbButton (optional) Used to determine type if available
 */
function cnb_add_header_button_edit($button = null) {
    $type = strtoupper(filter_input(INPUT_GET, 'type', FILTER_SANITIZE_STRING));
    $name = 'New Button';
    if ($button) {
        $type = $button->type;
        $name = $button->name;
    }
    $buttonTypes = cnb_get_button_types();
    $typeName = $buttonTypes[$type];
    echo 'Edit ' . esc_html($typeName) . ': "' . esc_html($name) . '"';
}

function cnb_create_tab_url_button($button, $tab) {
    $url = admin_url('admin.php');
    $tab_link =
        add_query_arg(
            array(
                'page' => 'call-now-button',
                'action' => 'edit',
                'type' => strtolower($button->type),
                'id' => $button->id,
                'tab' => $tab),
            $url );
    return esc_url( $tab_link );
}

/**
 * This is called to update the button
 * via `call-now-button.php#cnb_create_<type>_button`
 */
function cnb_admin_create_button() {
    global $cnb_slug_base;
    $nonce = filter_input( INPUT_POST, '_wpnonce_button', FILTER_SANITIZE_STRING );
    if( isset( $_REQUEST['_wpnonce_button'] ) && wp_verify_nonce( $nonce, 'cnb-button-edit') ) {

        // sanitize the input
        $button = filter_input(
                INPUT_POST,
                'cnb',
                FILTER_SANITIZE_STRING,
                FILTER_REQUIRE_ARRAY | FILTER_FLAG_NO_ENCODE_QUOTES);

        // ensure the position is valid for FULL
        if (strtoupper($button['type']) === 'FULL') {
            if (!empty($button['options']) && !empty($button['options']['placement'])) {
                $placement = $button['options']['placement'];
                if ($placement !== 'BOTTOM_CENTER' && $placement !== 'TOP_CENTER') {
                    $button['options']['placement'] = 'BOTTOM_CENTER';
                }
            } else {
                $button['options']['placement'] = 'BOTTOM_CENTER';
            }
        }

        // Do the processing
        $cnb_cloud_notifications = array();
        $new_button = CnbAdminCloud::cnb_create_button( $cnb_cloud_notifications, $button );

        // redirect the user to the appropriate page
        $tab          = filter_input( INPUT_POST, 'tab', FILTER_SANITIZE_STRING );
        $transient_id = 'cnb-' . wp_generate_uuid4();
        set_transient( $transient_id, $cnb_cloud_notifications, HOUR_IN_SECONDS );

        if ($new_button instanceof WP_Error) {
            $new_button_type = null;
            $new_button_id = null;
        } else {
            $new_button_type = strtolower( $new_button->type );
            $new_button_id = $new_button->id;
        }

        // Create link
        $url           = admin_url( 'admin.php' );
        $redirect_link =
            add_query_arg(
                array(
                    'page'   => 'call-now-button',
                    'action' => 'edit',
                    'type'   => $new_button_type,
                    'id'     => $new_button_id,
                    'tid'    => $transient_id,
                    'tab'    => $tab
                ),
                $url );
        $redirect_url  = esc_url_raw( $redirect_link );
        wp_safe_redirect( $redirect_url );
        exit;
    }
    else {
        wp_die( __( 'Invalid nonce specified', CNB_NAME), __( 'Error', CNB_NAME), array(
            'response' 	=> 403,
            'back_link' => 'admin.php?page=' . $cnb_slug_base,
        ) );
    }
}

/**
 * This is called to update the button
 * via `call-now-button.php#cnb_update_<type>_button`
 */
function cnb_admin_update_button() {
    global $cnb_slug_base;
    $nonce = filter_input( INPUT_POST, '_wpnonce_button', FILTER_SANITIZE_STRING );
    if( isset( $_REQUEST['_wpnonce_button'] ) && wp_verify_nonce( $nonce, 'cnb-button-edit') ) {

        // sanitize the input
        $button = filter_input(
                INPUT_POST,
                'cnb',
                FILTER_SANITIZE_STRING,
                FILTER_REQUIRE_ARRAY | FILTER_FLAG_NO_ENCODE_QUOTES);
        $actions = filter_input(
            INPUT_POST,
            'actions',
            FILTER_SANITIZE_STRING,
            FILTER_REQUIRE_ARRAY | FILTER_FLAG_NO_ENCODE_QUOTES);
        $conditions = filter_input(
            INPUT_POST,
            'condition',
            FILTER_SANITIZE_STRING,
            FILTER_REQUIRE_ARRAY | FILTER_FLAG_NO_ENCODE_QUOTES);

        if ($conditions === null) {
            $conditions = array();
        }

        // ensure the position is valid for FULL
        if (strtoupper($button['type']) === 'FULL') {
            if (!empty($button['options']) && !empty($button['options']['placement'])) {
                $placement = $button['options']['placement'];
                if ( $placement !== 'BOTTOM_CENTER' && $placement !== 'TOP_CENTER' ) {
                    $button['options']['placement'] = 'BOTTOM_CENTER';
                }
            } else {
                $button['options']['placement'] = 'BOTTOM_CENTER';
            }
        }

        // do the processing
        $processed_actions = array();
        if (is_array($actions)) {
            foreach ( $actions as $action ) {
                $processed_actions[] = cnb_admin_process_action( $action );
            }
        }
        $result = CnbAdminCloud::cnb_update_button_and_conditions( $button, $processed_actions, $conditions );

        // redirect the user to the appropriate page
        $tab = filter_input( INPUT_POST, 'tab', FILTER_SANITIZE_STRING );
        $transient_id = 'cnb-' . wp_generate_uuid4();
        set_transient($transient_id, $result, HOUR_IN_SECONDS);

        // Create link
        $url = admin_url('admin.php');
        $redirect_link =
            add_query_arg(
                array(
                    'page' => 'call-now-button',
                    'action' => 'edit',
                    'type' => strtolower($button['type']),
                    'id' => $button['id'],
                    'tid' => $transient_id,
                    'tab' => $tab),
                $url );
        $redirect_url = esc_url_raw( $redirect_link );
        wp_safe_redirect($redirect_url);
        exit;
    }
    else {
        wp_die( __( 'Invalid nonce specified', CNB_NAME), __( 'Error', CNB_NAME), array(
            'response' 	=> 403,
            'back_link' => 'admin.php?page=' . $cnb_slug_base,
        ) );
    }
}

function cnb_button_edit_form($button_id, $button, $default_domain, $options=array()) {
    $domains = CnbAppRemote::cnb_remote_get_domains();

    $cnb_single_image = esc_url(plugins_url( '../../resources/images/button-new-single.png', __FILE__ ));
    $cnb_multi_image = esc_url(plugins_url( '../../resources/images/button-new-multi.png', __FILE__ ));
    $cnb_full_image = esc_url(plugins_url( '../../resources/images/button-new-full.png', __FILE__ ));

    $show_submit_button = true;
    $submit_button_text = array_key_exists('submit_button_text', $options) ? $options['submit_button_text'] : '';
    $hide_on_modal = array_key_exists('modal_view', $options) && $options['modal_view'] === true;
    $show_advanced_view_only = array_key_exists('advanced_view', $options) && $options['advanced_view'] === 1;
    if($hide_on_modal) {
        echo '<script type="text/javascript">cnb_hide_on_modal_set=1</script>';
    }
    if ($show_advanced_view_only) {
        echo '<script type="text/javascript">show_advanced_view_only_set=1</script>';
    }

    // Create "add Action" link WITH Button association
    $url = admin_url('admin.php');
    $new_action_link =
        add_query_arg(
            array(
                'page' => 'call-now-button-actions',
                'action' => 'new',
                'id' => 'new',
                'tab' => 'actions',
                'bid' => $button->id),
            $url);
    $new_action_url = esc_url($new_action_link);

    // In case the API isn't working properly
    if ($default_domain instanceof WP_Error) {
        $default_domain = array();
        $default_domain['id'] = 0;
    }

    ?>
    <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post" class="cnb-container">
        <input type="hidden" name="page" value="call-now-button" />
        <input type="hidden" name="action" value="<?php echo $button_id === 'new' ? 'cnb_create_'.strtolower($button->type).'_button' :'cnb_update_'.esc_attr(strtolower($button->type)).'_button' ?>" />
        <input type="hidden" name="_wpnonce_button" value="<?php echo wp_create_nonce('cnb-button-edit')?>" />
        <input type="hidden" name="tab" value="<?php esc_attr_e(cnb_get_active_tab_name()) ?>" />

        <input type="hidden" name="cnb[id]" value="<?php esc_attr_e($button->id) ?>" />
        <input type="hidden" name="cnb[type]" value="<?php esc_attr_e($button->type) ?>" id="cnb_type" />
        <input type="hidden" name="cnb[active]"  value="<?php esc_attr_e($button->active) ?>" />
        <input type="hidden" name="cnb[domain]"  value="<?php esc_attr_e($default_domain->id) ?>" />
        <?php
        // Show all the current actions (needed to submit the form)
        foreach($button->actions as $action) { ?>
            <input type="hidden" name="actions[<?php esc_attr_e($action->id) ?>][id]" value="<?php esc_attr_e($action->id) ?>" />
        <?php } ?>

        <div class="cnb-button-name-field <?php if(!$hide_on_modal) { echo cnb_is_active_tab('basic_options'); } else { echo 'nav-tab-only'; } ?>">
            <label for="cnb[name]"><input type="text" name="cnb[name]" id="cnb[name]" class="large-text" placeholder="Button name" required="required" value="<?php esc_attr_e($button->name); ?>" /></label>
        </div>

        <table class="form-table <?php if(!$hide_on_modal) { echo cnb_is_active_tab('basic_options'); } else { echo 'nav-tab-only'; } ?>">
            <tr class="cnb_hide_on_modal">
                <th scope="row">Button status</th>

                <td class="activated">
                    <div class="cnb-radio-item">
                        <input id="cnb-disable" type="radio" name="cnb[active]" value="0" <?php checked(false, $button->active); ?> />
                        <label for="cnb-disable">Disabled</label>
                    </div>
                    <div class="cnb-radio-item">
                        <input id="cnb-enable" type="radio" name="cnb[active]" value="1" <?php checked(true, $button->active); ?> />
                        <label for="cnb-enable">Enabled</label>
                    </div>
                </td>
            </tr>
            <tr class="cnb_hide_on_modal cnb_advanced_view">
                <th scope="row"><label for="cnb[domain]">Domain</label></th>
                <td>
                    <select name="cnb[domain]" id="cnb[domain]">
                        <?php
                        foreach ($domains as $domain) { ?>
                                    <option value="<?php esc_attr_e($domain->id) ?>"<?php selected($domain->id, $button->domain->id) ?>>
                                        <?php esc_html_e($domain->name) ?>
                                <?php if ($domain->id == $default_domain->id) { echo ' (current Wordpress domain)'; } ?>
                            </option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <?php if ($button->type !== 'SINGLE') { ?>
            <tr class="cnb_hide_on_modal">
                <th colspan="2" class="cnb_padding_0">
                    <h2 >Actions <?php echo '<a href="' . $new_action_url . '" class="page-title-action">Add Action</a>'; ?>
                    <a href="https://help.callnowbutton.com/portal/en/kb/articles/adding-actions-to-a-multi-button-or-buttonbar" target="_blank" class="cnb-nounderscore">
                        <span class="dashicons dashicons-editor-help"></span>
                    </a></h2>
                </th>
            </tr>
            <?php } ?>
                <?php
                $is_active_tab_basic_options = cnb_is_active_tab('basic_options');
                if ($button->type === 'SINGLE') {
                    if (!empty($is_active_tab_basic_options)) {
                        // Create a dummy Action
                        $action = new CnbAction();
                        $action->id = 'new';
                        $action->actionType = '';
                        $action->actionValue = '';
                        $action->labelText = '';
                        $action->properties = new CnbActionProperties();

                        if (sizeof($button->actions) > 0) {
                            $action = $button->actions[0];
                        }
                        cnb_admin_page_action_edit_render_main($action, $button, false);
                    }
                } else {
                    // Only render the Actions table if that is the active tab (otherwise it's a pretty expensive operation)
                    if (!empty($is_active_tab_basic_options)) {
                        ?></table>
                        <div class="cnb-button-edit-action-table <?php if(!$hide_on_modal) { echo cnb_is_active_tab('basic_options'); } else { echo 'nav-tab-only'; } ?>">
                            <?php cnb_admin_page_action_overview_render_form(array('button' => $button));
                        ?></div>
                        <table class="form-table <?php if(!$hide_on_modal) { echo cnb_is_active_tab('basic_options'); } else { echo 'nav-tab-only'; } ?>"><?php
                    }
                } ?>
            <?php if ($button_id === 'new') { ?>
                <tr>
                    <th scope="row">Select button type</th>

                </tr>
                <tr>
                    <td scope="row" colspan="2" class="cnb_type_selector">
                      <div class="cnb-flexbox">
                        <div class="cnb_type_selector_item cnb_type_selector_single cnb_type_selector_active" data-cnb-selection="single">
                            <img style="max-width:100%;" alt="Choose a Single button type" src="<?php echo $cnb_single_image ?>">
                            <div style="text-align:center">Single button</div>
                        </div>
                        <div class="cnb_type_selector_item cnb_type_selector_multi" data-cnb-selection="multi">
                            <img style="max-width:100%;" alt="Choose a Multibutton type" src="<?php echo $cnb_multi_image ?>">
                            <div style="text-align:center">Multibutton</div>
                        </div>
                        <div class="cnb_type_selector_item cnb_type_selector_full" data-cnb-selection="full">
                            <img style="max-width:100%;" alt="Choose a Full button type" src="<?php echo $cnb_full_image ?>">
                            <div style="text-align:center">Buttonbar</div>
                        </div>
                      </div>
                    </td>
                </tr>
            <?php } ?>
        </table>
        <table class="form-table <?php echo cnb_is_active_tab('extra_options') ?>">
            <?php if ($button->type === 'FULL') { ?>
                <tr>
                    <th colspan="2">
                        <h2>Colors for the Buttonbar are defined via the Actions.</h2>
                        <input name="cnb[options][iconBackgroundColor]" type="hidden" value="<?php esc_attr_e($button->options->iconBackgroundColor); ?>" />
                        <input name="cnb[options][iconColor]" type="hidden" value="<?php esc_attr_e($button->options->iconColor); ?>" />
                    </th>
                </tr>
            <?php } else { ?>
                <tr class="cnb_hide_on_modal">
                    <th></th>
                    <td></td>
                </tr>
                <tr>
                    <th scope="row"><label for="cnb[options][iconBackgroundColor]">Background color</label></th>
                    <td>
                        <input name="cnb[options][iconBackgroundColor]" id="cnb[options][iconBackgroundColor]" type="text" value="<?php esc_attr_e($button->options->iconBackgroundColor); ?>"
                               class="cnb-iconcolor-field" data-default-color="#009900"/>
                        <?php if ($button->type === 'MULTI') { ?>
                            <p class="description"><span class="dashicons dashicons-info"></span>This color applies to the collapsable button only.</p>
                        <?php } ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="cnb[options][iconColor]">Icon color</label></th>
                    <td>
                        <input name="cnb[options][iconColor]" id="cnb[options][iconColor]" type="text" value="<?php esc_attr_e($button->options->iconColor); ?>"
                               class="cnb-iconcolor-field" data-default-color="#FFFFFF"/>
                        <?php if ($button->type === 'MULTI') { ?>
                            <p class="description"><span class="dashicons dashicons-info"></span>This color applies to the collapsable button only.</p>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
            <tr>
                <th scope="row">Position <a
                        href="<?php echo CNB_SUPPORT; ?>button-position/<?php cnb_utm_params("question-mark", "button-position"); ?>"
                        target="_blank" class="cnb-nounderscore">
                        <span class="dashicons dashicons-editor-help"></span>
                    </a></th>
                <td class="appearance">
                    <div class="appearance-options">
                        <?php if ($button->type === 'FULL') { ?>
                            <div class="cnb-radio-item">
                                <input type="radio" id="appearance1" name="cnb[options][placement]"
                                       value="TOP_CENTER" <?php checked('TOP_CENTER', $button->options->placement); ?>>
                                <label title="top-center" for="appearance1">Top</label>
                            </div>
                            <div class="cnb-radio-item">
                                <input type="radio" id="appearance2" name="cnb[options][placement]"
                                       value="BOTTOM_CENTER" <?php checked('BOTTOM_CENTER', $button->options->placement); ?>>
                                <label title="bottom-center" for="appearance2">Bottom</label>
                            </div>
                        <?php } else { ?>
                            <div class="cnb-radio-item">
                                <input type="radio" id="appearance1" name="cnb[options][placement]"
                                       value="BOTTOM_RIGHT" <?php checked('BOTTOM_RIGHT', $button->options->placement); ?>>
                                <label title="bottom-right" for="appearance1">Right corner</label>
                            </div>
                            <div class="cnb-radio-item">
                                <input type="radio" id="appearance2" name="cnb[options][placement]"
                                       value="BOTTOM_LEFT" <?php checked('BOTTOM_LEFT', $button->options->placement); ?>>
                                <label title="bottom-left" for="appearance2">Left corner</label>
                            </div>
                            <div class="cnb-radio-item">
                                <input type="radio" id="appearance3" name="cnb[options][placement]"
                                       value="BOTTOM_CENTER" <?php checked('BOTTOM_CENTER', $button->options->placement); ?>>
                                <label title="bottom-center" for="appearance3">Center bottom</label>
                            </div>

                            <!-- Extra placement options -->
                            <br class="cnb-extra-placement">
                            <div class="cnb-radio-item cnb-extra-placement <?php echo $button->options->placement == "MIDDLE_RIGHT" ? "cnb-extra-active" : ""; ?>">
                                <input type="radio" id="appearance5" name="cnb[options][placement]"
                                       value="MIDDLE_RIGHT" <?php checked('MIDDLE_RIGHT', $button->options->placement); ?>>
                                <label title="middle-right" for="appearance5">Middle right</label>
                            </div>
                            <div class="cnb-radio-item cnb-extra-placement <?php echo $button->options->placement == "MIDDLE_LEFT" ? "cnb-extra-active" : ""; ?>">
                                <input type="radio" id="appearance6" name="cnb[options][placement]"
                                       value="MIDDLE_LEFT" <?php checked('MIDDLE_LEFT', $button->options->placement); ?>>
                                <label title="middle-left" for="appearance6">Middle left </label>
                            </div>
                            <br class="cnb-extra-placement">
                            <div class="cnb-radio-item cnb-extra-placement <?php echo $button->options->placement == "TOP_RIGHT" ? "cnb-extra-active" : ""; ?>">
                                <input type="radio" id="appearance7" name="cnb[options][placement]"
                                       value="TOP_RIGHT" <?php checked('TOP_RIGHT', $button->options->placement); ?>>
                                <label title="top-right" for="appearance7">Top right corner</label>
                            </div>
                            <div class="cnb-radio-item cnb-extra-placement <?php echo $button->options->placement == "TOP_LEFT" ? "cnb-extra-active" : ""; ?>">
                                <input type="radio" id="appearance8" name="cnb[options][placement]"
                                       value="TOP_LEFT" <?php checked('TOP_LEFT', $button->options->placement); ?>>
                                <label title="top-left" for="appearance8">Top left corner</label>
                            </div>
                            <div class="cnb-radio-item cnb-extra-placement <?php echo $button->options->placement == "TOP_CENTER" ? "cnb-extra-active" : ""; ?>">
                                <input type="radio" id="appearance9" name="cnb[options][placement]"
                                       value="TOP_CENTER" <?php checked('TOP_CENTER', $button->options->placement); ?>>
                                <label title="top-center" for="appearance9">Center top</label>
                            </div>
                            <a href="#" id="cnb-more-placements">More placement options...</a>
                            <!-- END extra placement options -->
                        <?php } ?>
                    </div>
                </td>
            </tr>
        </table>
        <table class="form-table <?php echo cnb_is_active_tab('visibility') ?>">
            <tbody id="cnb_form_table_visibility">
            <tr>
                <th scope="row"><h2>Conditions <input type="button" onclick="return cnb_add_condition();" value="Add New" class="button button-secondary page-title-action"></h2>
            </tr>
            <?php if (empty($button->conditions)) { ?>
                <tr>
                    <td colspan="2">
                        <p>You have no Conditions yet.</p>
                        <p>Conditions allow you to show/hide the Button on page URLs you specify.</p>
                        <p>Click <code>Add Condition</code> below to create your first Condition.</p>
                    </td>
                </tr>
            <?php } else { ?>
                <?php foreach ($button->conditions as $condition) { ?>
                <tr class="appearance" id="cnb_condition_<?php esc_attr_e($condition->id) ?>">
                    <th scope="row"><label for="condition[<?php esc_attr_e($condition->id) ?>][filterType]">Condition
                            <div class="cnb_font_normal cnb_font_90 cnb_advanced_view">ID: <code class="cnb_font_90"><?php esc_html_e($condition->id) ?></code></div></label></th>
                        <td>
                        <input type="hidden" name="condition[<?php esc_attr_e($condition->id) ?>][id]" value="<?php esc_attr_e($condition->id) ?>" />
                        <input type="hidden" name="condition[<?php esc_attr_e($condition->id) ?>][conditionType]" value="<?php esc_attr_e($condition->conditionType) ?>" />
                        <input type="hidden" name="condition[<?php esc_attr_e($condition->id) ?>][delete]" id="cnb_condition_<?php esc_attr_e($condition->id) ?>_delete" value="" />
                        <select name="condition[<?php esc_attr_e($condition->id) ?>][filterType]" id="condition[<?php esc_attr_e($condition->id) ?>][filterType]">
                                <option value="INCLUDE"<?php selected('INCLUDE', $condition->filterType) ?>>Include</option>
                                <option value="EXCLUDE"<?php selected('EXCLUDE', $condition->filterType) ?>>Exclude</option>
                            </select><br />

                            <label>
                            <select name="condition[<?php esc_attr_e($condition->id) ?>][matchType]">
                                    <?php foreach (cnb_get_condition_match_types() as $condition_match_type_key => $condition_match_type_value) { ?>
                                    <option value="<?php esc_attr_e($condition_match_type_key) ?>"<?php selected($condition_match_type_key, $condition->matchType) ?>>
                                        <?php esc_html_e($condition_match_type_value) ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </label><br />

                            <label>
                            <input type="text" name="condition[<?php esc_attr_e($condition->id) ?>][matchValue]" value="<?php esc_attr_e($condition->matchValue); ?>"/>
                            </label>

                            <?php // Match old "Hide button on front page"
                            if ($condition->conditionType === 'URL' && $condition->filterType === 'EXCLUDE' && $condition->matchType === 'EXACT' && $condition->matchValue === get_home_url()) { ?>
                                <p class="description"><span class="dashicons dashicons-info"></span> This condition matches the legacy "<strong>Hide button on front page</strong>" checkbox.</p>
                            <?php } else { ?>
                                <br />
                            <?php }?>

                        <input type="button" onclick="return cnb_remove_condition('<?php echo esc_js($condition->id) ?>');" value="Remove Condition" class="button-link button-link-delete">
                        </td>
                    </tr>
                <?php } } ?>
            <tr id="cnb_form_table_add_condition">
                <th></th>
                <td>

                </td>
            </tr>
            </tbody>
        </table>

        <input type="hidden" name="cnb[version]" value="<?php echo CNB_VERSION; ?>"/>
        <?php if ($show_submit_button) {submit_button($submit_button_text);} ?>
    </form>
    <?php
}

/**
 * Main entrypoint, used by `call-now-button.php`.
 */
function cnb_admin_page_edit_render() {
    global $cnb_options;

    $button_id = cnb_get_button_id();
    $button = new CnbButton();

    // Get the various supported domains
    $default_domain = CnbAppRemote::cnb_remote_get_wp_domain();

    if (strlen($button_id) > 0 && $button_id !== 'new') {
        $button = CnbAppRemote::cnb_remote_get_button_full( $button_id );
    } elseif ($button_id === 'new') {
        $button->type = strtoupper(filter_input(INPUT_GET, 'type', FILTER_SANITIZE_STRING));
        $button->domain = $default_domain;
    }
    if ($button->actions === null) {
        $button->actions = array();
    }

    // Set some sane defaults
    CnbButton::setSaneDefault($button);

    // Create options
    $options = array();
    $options['advanced_view'] = $cnb_options['advanced_view'];

    add_action('cnb_header_name', function() use($button) {
        cnb_add_header_button_edit($button);
    });

    do_action('cnb_header');
    ?>

    <h2 class="nav-tab-wrapper">
        <a href="<?php echo cnb_create_tab_url_button($button, 'basic_options') ?>"
           class="nav-tab <?php echo cnb_is_active_tab('basic_options') ?>">Basics</a>
        <?php if ($button_id !== 'new') { ?>
            <a href="<?php echo cnb_create_tab_url_button($button, 'extra_options') ?>"
               class="nav-tab <?php echo cnb_is_active_tab('extra_options') ?>">Presentation</a>
            <a href="<?php echo cnb_create_tab_url_button($button, 'visibility') ?>"
               class="nav-tab <?php echo cnb_is_active_tab('visibility') ?>">Visibility</a>
        <?php } else { ?>
            <a class="nav-tab"><i>Additional options available after saving</i></a>
        <?php } ?>
    </h2>
    <?php
    cnb_button_edit_form($button_id, $button, $default_domain, $options);
    do_action('cnb_footer');
}
