<?php

require_once dirname( __FILE__ ) . '/api/CnbAppRemote.php';
require_once dirname( __FILE__ ) . '/api/CnbAdminCloud.php';
require_once dirname( __FILE__ ) . '/partials/admin-functions.php';
require_once dirname( __FILE__ ) . '/partials/admin-header.php';
require_once dirname( __FILE__ ) . '/partials/admin-footer.php';
require_once dirname( __FILE__ ) . '/models/CnbAction.class.php';
require_once dirname( __FILE__ ) . '/../utils/utils.php';
require_once dirname( __FILE__ ) . '/button-edit.php';

function cnb_add_header_action_edit($action) {
    $id = filter_input( INPUT_GET, 'id', FILTER_SANITIZE_STRING );
    $name = 'New Action';
    if ($action && $action->id !== 'new') {
        $actionTypes = cnb_get_action_types();
        $name = $actionTypes[$action->actionType];
        if ($action->actionValue) {
            $name = $action->actionValue;
        }
    }
    if (strlen($id) > 0 && $id === 'new') {
        echo 'Add action';
    } else {
        echo 'Edit action: "' . esc_html($name) . '"';
    }
}

/**
 * This is called to create an Action
 * via `call-now-button.php#cnb_create_action`
 */
function cnb_admin_page_action_create_process() {
    global $cnb_slug_base;
    $nonce  = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
    if( isset( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $nonce, 'cnb-action-edit') ) {

        $actions = filter_input(
            INPUT_POST,
            'actions',
            FILTER_SANITIZE_STRING,
            FILTER_REQUIRE_ARRAY | FILTER_FLAG_NO_ENCODE_QUOTES);
        $action_id = filter_input( INPUT_POST, 'action_id', FILTER_SANITIZE_STRING );
        $action = $actions[$action_id];

        // Do the processing
        $cnb_cloud_notifications = array();
        if (isset($action['schedule']['daysOfWeek']) &&
            $action['schedule']['daysOfWeek'] !== null &&
            is_array($action['schedule']['daysOfWeek'])) {
            $action['schedule']['daysOfWeek'] = cnb_create_days_of_week_array($action['schedule']['daysOfWeek']);
        }

        // "Fix" the WHATSAPP values
        if ($action['actionType'] === 'WHATSAPP'
            && isset($action['actionValueWhatsappHidden'])
            && !empty($action['actionValueWhatsappHidden'])) {
            $action['actionValue'] = $action['actionValueWhatsappHidden'];
        }

        // Remove the "display" value
        unset($action['actionValueWhatsapp']);
        unset($action['actionValueWhatsappHidden']);

        $new_action = CnbAdminCloud::cnb_create_action( $cnb_cloud_notifications, $action );
        $new_action_id = $new_action->id;

        $bid = filter_input( INPUT_POST, 'bid', FILTER_SANITIZE_STRING );
        if (!empty($bid)) {
            // Tie this new Action to the provided Button
            $button = CnbAppRemote::cnb_remote_get_button( $bid );
            if (!($button instanceof WP_Error)) {
                $button->actions[] = $new_action_id;
                $button_array = json_decode(json_encode($button), true);
                CnbAdminCloud::cnb_update_button( $cnb_cloud_notifications, $button_array );
            } else {
                // TODO Add error to $cnb_cloud_notifications
            }
        }

        // redirect the user to the appropriate page
        $transient_id = 'cnb-' . wp_generate_uuid4();
        set_transient($transient_id, $cnb_cloud_notifications, HOUR_IN_SECONDS);

        // Create link
        $bid = !empty($_GET['bid']) ? sanitize_text_field($_GET['bid']) : null;
        $url = admin_url('admin.php');

        if (!empty($bid)) {
            $redirect_link =
                add_query_arg(
                    array(
                        'page' => 'call-now-button',
                        'action' => 'edit',
                        'id' => $bid,
                        'tid' => $transient_id,
                    ),
                    $url);
            $redirect_url = esc_url_raw($redirect_link);
            wp_safe_redirect($redirect_url);
            exit;
        } else {
            $redirect_link =
                add_query_arg(
                    array(
                        'page' => 'call-now-button-actions',
                        'action' => 'edit',
                        'id' => $new_action_id,
                        'tid' => $transient_id,
                        'bid' => $bid),
                    $url);
            $redirect_url = esc_url_raw($redirect_link);
            wp_safe_redirect($redirect_url);
            exit;
        }
    }
    else {
        $url = admin_url('admin.php');
        $redirect_link =
            add_query_arg(
                array(
                    'page' => $cnb_slug_base
                ),
                $url );
        $redirect_url = esc_url_raw($redirect_link);
        wp_die( __( 'Invalid nonce specified', CNB_NAME), __( 'Error', CNB_NAME), array(
            'response' 	=> 403,
            'back_link' => $redirect_url,
        ) );
    }
}

/**
 * @param $action CnbAction
 *
 * @return array
 */
function cnb_admin_process_action($action) {
    if (isset($action['schedule']['daysOfWeek']) && $action['schedule']['daysOfWeek'] !== null && is_array($action['schedule']['daysOfWeek'])) {
        $action['schedule']['daysOfWeek'] = cnb_create_days_of_week_array($action['schedule']['daysOfWeek']);
    }

    // "Fix" the WHATSAPP values
    if (isset($action['actionType']) && $action['actionType'] === 'WHATSAPP'
        && isset($action['actionValueWhatsappHidden'])
        && !empty($action['actionValueWhatsappHidden'])) {
        $action['actionValue'] = $action['actionValueWhatsappHidden'];
    }

    // Remove the "display" value
    unset($action['actionValueWhatsapp']);
    unset($action['actionValueWhatsappHidden']);

    // Set the correct iconText
    if (isset($action['iconText']) && !empty($action['iconText'])) {
        // Reset the iconText based on type
        $action['iconText'] = cnb_actiontype_to_icontext($action['actionType']);
    }

    return $action;
}
/**
 * This is called to update the action
 * via `call-now-button.php#cnb_update_action`
 */
function cnb_admin_page_action_edit_process() {
    global $cnb_slug_base;
    $nonce  = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
    if( isset( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $nonce, 'cnb-action-edit') ) {

        // sanitize the input
        $actions = filter_input(
            INPUT_POST,
            'actions',
            FILTER_SANITIZE_STRING,
            FILTER_REQUIRE_ARRAY | FILTER_FLAG_NO_ENCODE_QUOTES);
        $result = '';
        $cnb_cloud_notifications = array();

        foreach($actions as $action) {
            $processed_action = cnb_admin_process_action($action);
            // do the processing
            $result = CnbAdminCloud::cnb_update_action( $cnb_cloud_notifications, $processed_action );
        }

        // redirect the user to the appropriate page
        $transient_id = 'cnb-' . wp_generate_uuid4();
        set_transient($transient_id, $cnb_cloud_notifications, HOUR_IN_SECONDS);

        // Create link
        $bid = !empty($_GET["bid"]) ? sanitize_text_field($_GET["bid"]) : null;
        $url = admin_url('admin.php');
        if (!empty($bid)) {
            $redirect_link =
                add_query_arg(
                    array(
                        'page' => 'call-now-button',
                        'action' => 'edit',
                        'id' => $bid,
                        'tid' => $transient_id,
                    ),
                    $url);
            $redirect_url = esc_url_raw($redirect_link);
            wp_safe_redirect($redirect_url);
            exit;
        } else {
            $redirect_link =
                add_query_arg(
                    array(
                        'page' => 'call-now-button-actions',
                        'action' => 'edit',
                        'id' => $result->id,
                        'tid' => $transient_id,
                        'bid' => $bid),
                    $url);
            $redirect_url = esc_url_raw($redirect_link);
            wp_safe_redirect($redirect_url);
            exit;
        }
    }
    else {
        wp_die( __( 'Invalid nonce specified', CNB_NAME), __( 'Error', CNB_NAME), array(
            'response' 	=> 403,
            // TODO Create proper URL with escaping
            'back_link' => 'admin.php?page=' . $cnb_slug_base,
        ) );
    }
}

function cnb_action_edit_create_tab_url($button, $tab) {
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
* @param $action CnbAction
* @param $button CnbButton
 * @param $show_table boolean
 */
function cnb_render_form_action($action, $button=null, $show_table=true) {
    /**
     * @global WP_Locale $wp_locale WordPress date and time locale object.
     */
    global $wp_locale;
    // CNB week starts on Monday
    $cnb_days_of_week_order = array(1,2,3,4,5,6,0);

    if ($button) {
        $url = admin_url('admin.php');
        $upgrade_link =
            add_query_arg(array(
                'page' => 'call-now-button-domains',
                'action' => 'upgrade',
                'id' => $button->domain->id
            ),
                $url);
        $upgrade_url = esc_url($upgrade_link);
    }
    ?>
    <input type="hidden" name="actions[<?php esc_attr_e($action->id) ?>][id]" value="<?php if ($action->id !== null && $action->id !== 'new') { esc_attr_e($action->id); } ?>" />
    <input type="hidden" name="actions[<?php esc_attr_e($action->id) ?>][delete]" id="cnb_action_<?php esc_attr_e($action->id) ?>_delete" value="" />
    <input type="hidden" name="actions[<?php esc_attr_e($action->id) ?>][iconText]" value="<?php if (isset($action->iconText)) { esc_attr_e($action->iconText); } ?>" />
    <?php if ($show_table) { ?>
    <table class="form-table nav-tab-active">
    <?php } ?>
    <?php if (!$button) { ?>
        <tr>
            <th colspan="2"><h2>Action Settings</h2>
            </th>
        </tr>
    <?php } ?>
        <tr class="cnb_hide_on_modal">
            <th></th>
            <td></td>
        </tr>
        <tr class="cnb_hide_on_modal">
            <th scope="row"><label for="cnb_action_type">Button type</label></th>
            <td>
                <select id="cnb_action_type" name="actions[<?php esc_attr_e($action->id) ?>][actionType]">
                    <?php foreach (cnb_get_action_types() as $action_type_key => $action_type_value) { ?>
                        <option value="<?php esc_attr_e($action_type_key) ?>"<?php selected($action_type_key, $action->actionType) ?>>
                            <?php esc_html_e($action_type_value) ?>
                        </option>
                    <?php } ?>
                </select>
        </tr>
        <tr class="cnb-action-value cnb_hide_on_modal">
            <th scope="row">
                <label for="cnb_action_value_input">
                    <span id="cnb_action_value">Action value</span>
                </label>
                <a href="<?php echo CNB_SUPPORT; ?>phone-number/<?php cnb_utm_params("question-mark", "phone-number"); ?>"
                        target="_blank" class="cnb-nounderscore">
                    <span class="dashicons dashicons-editor-help"></span>
                </a>
            </th>
            <td>
                <input type="text" id="cnb_action_value_input" name="actions[<?php esc_attr_e($action->id) ?>][actionValue]" value="<?php esc_attr_e($action->actionValue) ?>"/>
                <p class="description cnb-action-properties-map">Preview via <a href="#" onclick="cnb_action_update_map_link(this)" target="_blank">Google Maps</a></p>

            </td>
        </tr>
        <tr class="cnb-action-properties-whatsapp">
            <th scope="row"><label for="cnb_action_value_input_whatsapp">Whatsapp Number</label></th>
            <td>
                <input type="tel" id="cnb_action_value_input_whatsapp" name="actions[<?php esc_attr_e($action->id) ?>][actionValueWhatsapp]" value="<?php esc_attr_e($action->actionValue) ?>"/>
                <p class="description" id="cnb-valid-msg">✓ Valid</p>
                <p class="description" id="cnb-error-msg"></p>
            </td>
        </tr>
        <tr class="button-text cnb_hide_on_modal">
            <th scope="row"><label for="buttonTextField">Button label</label><a
                        href="<?php echo CNB_SUPPORT; ?>using-text-buttons/<?php cnb_utm_params("question-mark", "using-text-buttons"); ?>"
                        target="_blank" class="cnb-nounderscore">
                    <span class="dashicons dashicons-editor-help"></span>
                </a></th>
            <td>
                <input id="buttonTextField" type="text" name="actions[<?php esc_attr_e($action->id) ?>][labelText]"
                       value="<?php esc_attr_e($action->labelText) ?>" maxlength="30" placeholder="optional" />
                <p class="description">Leave this field empty to only show an icon.</p>
            </td>
        </tr>
        <tr class="cnb-action-properties-email">
            <th></th>
            <td><a class="cnb_cursor_pointer" onclick="jQuery('.cnb-action-properties-email-extra').show();jQuery(this).parent().parent().hide()">Extra email settings...</a></td>
        </tr>
        <tr class="cnb-action-properties-email-extra">
            <th colspan="2"><hr /></th>
        </tr>
        <tr class="cnb-action-properties-email-extra">
            <th scope="row"><label for="action-properties-subject">Subject</label></th>
            <td><input id="action-properties-subject" name="actions[<?php esc_attr_e($action->id) ?>][properties][subject]" type="text" value="<?php if (isset($action->properties) && isset($action->properties->subject)) { esc_attr_e($action->properties->subject); } ?>" /></td>
        </tr>
        <tr class="cnb-action-properties-email-extra">
            <th scope="row"><label for="action-properties-body">Body</label></th>
            <td><textarea id="action-properties-body" name="actions[<?php esc_attr_e($action->id) ?>][properties][body]" class="large-text code" rows="3"><?php if (isset($action->properties) && isset($action->properties->body)) { echo esc_textarea($action->properties->body); } ?></textarea></td>

        </tr>
        <tr class="cnb-action-properties-email-extra">
            <th scope="row"><label for="action-properties-cc">CC</label></th>
            <td><input id="action-properties-cc" name="actions[<?php esc_attr_e($action->id) ?>][properties][cc]" type="text" value="<?php if (isset($action->properties) && isset($action->properties->cc)) { esc_attr_e($action->properties->cc); } ?>" /></td>
        </tr>
        <tr class="cnb-action-properties-email-extra">
            <th scope="row"><label for="action-properties-bcc">BCC</label></th>
            <td><input id="action-properties-bcc" name="actions[<?php esc_attr_e($action->id) ?>][properties][bcc]" type="text" value="<?php if (isset($action->properties) && isset($action->properties->bcc)) { esc_attr_e($action->properties->bcc); } ?>" /></td>
        </tr>
        <tr class="cnb-action-properties-email-extra">
            <th colspan="2"><hr /></th>
        </tr>

        <tr class="cnb-action-properties-whatsapp">
            <th></th>
            <td><a class="cnb_cursor_pointer" onclick="jQuery('.cnb-action-properties-whatsapp-extra').show();jQuery(this).parent().parent().hide()">Extra Whatsapp settings...</a></td>
        </tr>
        <tr class="cnb-action-properties-whatsapp-extra">
            <th colspan="2"><hr /></th>
        </tr>
        <tr class="cnb-action-properties-whatsapp-extra">
            <th scope="row"><label for="action-properties-message">Default message</label></th>
            <td>
                <textarea id="action-properties-message" name="actions[<?php esc_attr_e($action->id) ?>][properties][message]" class="large-text code" rows="3"><?php if (isset($action->properties) && isset($action->properties->message)) { echo esc_textarea($action->properties->message); } ?></textarea>
            </td>
        </tr>
        <tr class="cnb-action-properties-whatsapp-extra">
            <th colspan="2"><hr /></th>
        </tr>

        <?php if ($button && $button->type === 'SINGLE') { ?>
        <tr class="cnb_hide_on_modal cnb_advanced_view">
            <th colspan="2">
                <h2>Colors for a Single button are defined on the Button, not the action.</h2>
                <input name="actions[<?php esc_attr_e($action->id) ?>][backgroundColor]" type="hidden" value="<?php esc_attr_e($action->backgroundColor) ?>" />
                <input name="actions[<?php esc_attr_e($action->id) ?>][iconColor]" type="hidden" value="<?php esc_attr_e($action->iconColor) ?>" />
                <!-- We always enable the icon when the type if SINGLE, original value is "<?php esc_attr_e($action->iconEnabled) ?>" -->
                <input name="actions[<?php esc_attr_e($action->id) ?>][iconEnabled]" type="hidden" value="1" />
            </th>
        </tr>
        <?php } else { ?>
        <tr class="cnb_hide_on_modal">
            <th></th>
            <td></td>
        </tr>    
        <tr>
            <th scope="row"><label for="actions[<?php esc_attr_e($action->id) ?>][backgroundColor]">Background color</label></th>
            <td>
                <input name="actions[<?php esc_attr_e($action->id) ?>][backgroundColor]" id="actions[<?php esc_attr_e($action->id) ?>][backgroundColor]" type="text" value="<?php esc_attr_e($action->backgroundColor) ?>"
                       class="cnb-color-field" data-default-color="#009900"/>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="actions[<?php esc_attr_e($action->id) ?>][iconColor]">Icon color</label></th>
            <td>
                <input name="actions[<?php esc_attr_e($action->id) ?>][iconColor]" id="actions[<?php esc_attr_e($action->id) ?>][iconColor]" type="text" value="<?php esc_attr_e($action->iconColor) ?>"
                       class="cnb-iconcolor-field" data-default-color="#FFFFFF"/>
            </td>
        </tr>
            <?php if ($button && $button->type === 'MULTI') { ?>
                <input name="actions[<?php esc_attr_e($action->id) ?>][iconEnabled]" type="hidden" value="1" />
            <?php } else { ?>
            <tr>
                <th scope="row"></th>
                <td>
                    <input type="checkbox" name="actions[<?php esc_attr_e($action->id) ?>][iconEnabled]" id="actions[<?php esc_attr_e($action->id) ?>][iconEnabled]" value="true" <?php checked(true, $action->iconEnabled); ?>>
                    <label title="right" for="actions[<?php esc_attr_e($action->id) ?>][iconEnabled]">Show icon</label>
                </td>
            </tr>
            <?php } // End Multi/Buttonbar ?>
        <?php } ?>

        <tr class="cnb_hide_on_modal">
            <th scope="row"><h3>Scheduling</h3> </th>
            <td>
                <div class="cnb-radio-item">
                       <?php if (!isset($button) || $button->domain->type !== 'FREE') { ?>
                    <input name="actions[<?php esc_attr_e($action->id) ?>][schedule][showAlways]" type="hidden" value="false" />
                    <input id="actions_schedule_show_always"  onchange="return cnb_hide_on_show_always();" name="actions[<?php esc_attr_e($action->id) ?>][schedule][showAlways]" type="checkbox"
                           value="true" <?php checked(true, $action->id === 'new' || $action->schedule->showAlways); ?> />
                           <label title="Show always" for="actions_schedule_show_always">Show always</label>
                       <?php } else { ?>
                    <input id="actions_schedule_show_always"  name="actions[<?php esc_attr_e($action->id) ?>][schedule][showAlways]" type="hidden" value="true" />
                    <label title="Show always" for="actions_schedule_show_always">Show always.
                        <?php
                        if ($button->domain->type !== 'PRO') {
                            echo '<a href="' . $upgrade_url . '">Upgrade!</a>';
                        }
                        ?>
                     to enable scheduling.</label>
                       <?php } ?>
                </div>
            </td>
        </tr>
        <?php if (!isset($button) || $button->domain->type !== 'FREE') { ?>
        <tr class="cnb_hide_on_show_always">
            <th>Show on these days</th>
            <td>
                <?php
                foreach ($cnb_days_of_week_order as $cnb_days_of_week) {
                    $selected = '';
                    if (isset($action->schedule) && isset($action->schedule->daysOfWeek)) {
                        $selected = ($action->schedule->daysOfWeek[$cnb_days_of_week] == true) ? 'checked="checked"' : '';
                    }
                    echo "<input type='checkbox' name='actions[" . esc_attr($action->id) . "][schedule][daysOfWeek][" . esc_attr($cnb_days_of_week) . "]' value='true' $selected>" . $wp_locale->get_weekday($cnb_days_of_week) . '<br/>';
                }
                ?>
            </td>
        </tr>
        <tr class="cnb_hide_on_show_always">
            <th><label for="actions[<?php esc_attr_e($action->id) ?>][schedule][start]">Start time</label></th>
            <td><input type="time" name="actions[<?php esc_attr_e($action->id) ?>][schedule][start]" id="actions[<?php esc_attr_e($action->id) ?>][schedule][start]" value="<?php if (isset($action->schedule)) { esc_attr_e($action->schedule->start); } ?>"></td>
        </tr>
        <tr class="cnb_hide_on_show_always">
            <th><label for="actions[<?php esc_attr_e($action->id) ?>][schedule][stop]">End time</label></th>
            <td><input type="time" name="actions[<?php esc_attr_e($action->id) ?>][schedule][stop]" id="actions[<?php esc_attr_e($action->id) ?>][schedule][stop]" value="<?php if (isset($action->schedule)) { esc_attr_e($action->schedule->stop); } ?>"></td>
        </tr>
        <tr class="cnb_hide_on_show_always">
            <th><label for="actions[<?php esc_attr_e($action->id) ?>][schedule][timezone]">Timezone</label></th>
            <td>
                <select name="actions[<?php esc_attr_e($action->id) ?>][schedule][timezone]" id="actions[<?php esc_attr_e($action->id) ?>][schedule][timezone]">
                    <?php
                    $timezone = (isset($action->schedule) && isset($action->schedule->timezone)) ? $action->schedule->timezone : null;
                    echo wp_timezone_choice($timezone);
                    ?>
                </select>
                <p class="description" id="domain_timezone-description">
                    <?php if (empty($timezone)) { ?>
                        Please select your timezone.
                    <?php } else { ?>
                        Currently set to <code><?php esc_html_e($timezone)?></code>.
                    <?php } ?>
                </p>
            </td>
        </tr>
        <tr class="cnb_hide_on_show_always">
            <th></th>
            <td>
                <input id="actions_schedule_outside_hours" name="actions[<?php esc_attr_e($action->id) ?>][schedule][outsideHours]" type="checkbox"
                       value="true" <?php checked(true, isset($action->schedule) && $action->schedule->outsideHours); ?> />
                <label title="Show always" for="actions_schedule_outside_hours">Show button outside these hours</label>
            </td>
        </tr>
        <?php } ?>
        <?php if ($show_table) { ?>
    </table>
    <?php } ?>
    <?php
}

/**
 * @param $action CnbAction
 * @param $button CnbButton
 * @param $show_table boolean
 */
function cnb_admin_page_action_edit_render_main($action, $button, $show_table=true) {
    $bid = !empty($_GET["bid"]) ? sanitize_text_field($_GET["bid"]) : null;
    // Set some sane defaults
    $action->backgroundColor = !empty($action->backgroundColor)
        ? $action->backgroundColor
        : '#009900';
    $action->iconColor = !empty($action->iconColor)
        ? $action->iconColor
        : '#FFFFFF';
    $action->iconEnabled = isset($action->iconEnabled)
        // phpcs:ignore
        ? boolval($action->iconEnabled)
        : true;
    ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.12/js/intlTelInput.min.js" integrity="sha512-OnkjbJ4TwPpgSmjXACCb5J4cJwi880VRe+vWpPDlr8M38/L3slN5uUAeOeWU2jN+4vN0gImCXFGdJmc0wO4Mig==" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.12/css/intlTelInput.min.css" integrity="sha512-yye/u0ehQsrVrfSd6biT17t39Rg9kNc+vENcCXZuMz2a+LWFGvXUnYuWUW6pbfYj1jcBb/C39UZw2ciQvwDDvg==" crossorigin="anonymous" />
    <input type="hidden" name="bid" value="<?php echo $bid ?>" />
    <input type="hidden" name="action_id" value="<?php echo $action->id ?>" />
    <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce('cnb-action-edit')?>" />
    <?php
    cnb_render_form_action($action, $button, $show_table);
}

function cnb_admin_page_action_edit_render() {
    $action_id = cnb_get_button_id();
    $action = new CnbAction();
    $action->id = 'new';
    $action->actionType = 'PHONE';
    $action->actionValue = null;
    $action->labelText = null;

    if (strlen($action_id) > 0 && $action_id !== 'new') {
        $action = CnbAppRemote::cnb_remote_get_action( $action_id );
    }

    add_action('cnb_header_name', function() use($action) {
        cnb_add_header_action_edit($action);
    });

    $button = null;
    $bid = !empty($_GET["bid"]) ? sanitize_text_field($_GET["bid"]) : null;
    if ($bid !== null) {
        $button = CnbAppRemote::cnb_remote_get_button_full( $bid );

        // Create back link
        $url = admin_url('admin.php');
        $redirect_link = esc_url(
            add_query_arg(
                array(
                    'page' => 'call-now-button',
                    'action' => 'edit',
                    'id' => $bid),
                $url ));

        $action_verb = $action->id === 'new' ? 'adding' : 'editing';
        $mesage = '<strong>You are '.$action_verb.' an Action</strong>.
                    Click <a href="'.$redirect_link.'">here</a> to go back to continue configuring the Button.';
        CnbAdminNotices::get_instance()->renderInfo($mesage);
    }

    $url = admin_url('admin-post.php');
    $form_action = esc_url( $url );
    $redirect_link = add_query_arg(
        array(
            'bid' => $bid
        ),
        $form_action
    );

    do_action('cnb_header');
    ?>

    <?php if ($bid !== null) { ?>
    <h2 class="nav-tab-wrapper">
        <a href="<?php echo cnb_action_edit_create_tab_url($button, 'basic_options') ?>"
           class="nav-tab">Basics</a>
            <a href="<?php echo cnb_action_edit_create_tab_url($button, 'extra_options') ?>"
               class="nav-tab ">Presentation</a>
            <a href="<?php echo cnb_action_edit_create_tab_url($button, 'visibility') ?>"
               class="nav-tab ">Visibility</a>
    </h2>
    <?php } ?>
    <form action="<?php echo $redirect_link; ?>" method="post">
        <input type="hidden" name="page" value="call-now-button-actions" />
        <input type="hidden" name="action" value="<?php echo $action->id === 'new' ? 'cnb_create_action' :'cnb_update_action' ?>" />
        <?php
        cnb_admin_page_action_edit_render_main($action, $button);
        submit_button();
        ?>
    </form>
    <?php do_action('cnb_footer');
}
