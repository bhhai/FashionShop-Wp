<?php

require_once dirname( __FILE__ ) . '/api/CnbAppRemote.php';
require_once dirname( __FILE__ ) . '/api/CnbAdminCloud.php';
require_once dirname( __FILE__ ) . '/partials/admin-functions.php';
require_once dirname( __FILE__ ) . '/partials/admin-header.php';
require_once dirname( __FILE__ ) . '/partials/admin-footer.php';
require_once dirname( __FILE__ ) . '/models/CnbCondition.class.php';
require_once dirname( __FILE__ ) . '/../utils/utils.php';

function cnb_add_header_condition_edit($condition) {
    $id = filter_input( INPUT_GET, 'id', FILTER_SANITIZE_STRING );
    $name = 'New Condition';
    if ($condition && $condition->id !== 'new') {
        $name = $condition->filterType;
        if ($condition->matchValue) {
            $name = $condition->matchValue;
        }
    }
    if (strlen($id) > 0 && $id === 'new') {
        echo 'Add condition';
    } else {
        echo 'Edit condition: "' . esc_html($name) . '"';
    }
}

/**
 * This is called to create the condition
 * via `call-now-button.php#cnb_admin_create_condition`
 */
function cnb_admin_page_condition_create_process() {
    global $cnb_slug_base;
    $nonce  = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
    if( isset( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $nonce, 'cnb_create_condition') ) {

        // sanitize the input
        $conditions = filter_input(
            INPUT_POST,
            'conditions',
            FILTER_SANITIZE_STRING,
            FILTER_REQUIRE_ARRAY | FILTER_FLAG_NO_ENCODE_QUOTES);

        $result = '';
        $cnb_cloud_notifications = array();
        foreach($conditions as $condition) {
            // do the processing
            $result = CnbAdminCloud::cnb_create_condition( $cnb_cloud_notifications, $condition );
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
                        'tab' => 'visibility',
                    ),
                    $url);
            $redirect_url = esc_url_raw($redirect_link);
            wp_safe_redirect($redirect_url);
            exit;
        } else {
            $redirect_link =
                add_query_arg(
                    array(
                        'page' => 'call-now-button-conditions',
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
            'back_link' => 'admin.php?page=' . $cnb_slug_base,
        ) );
    }
}

/**
 * This is called to update the condition
 * via `call-now-button.php#cnb_update_condition`
 */
function cnb_admin_page_condition_edit_process() {
    global $cnb_slug_base;
    $nonce  = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
    if( isset( $_REQUEST['_wpnonce'] ) && wp_verify_nonce( $nonce, 'cnb_update_condition') ) {

        // sanitize the input
        $conditions = filter_input(
            INPUT_POST,
            'conditions',
            FILTER_SANITIZE_STRING,
            FILTER_REQUIRE_ARRAY | FILTER_FLAG_NO_ENCODE_QUOTES);
        $result = '';
        $cnb_cloud_notifications = array();
        foreach($conditions as $condition) {
            // do the processing
            $result = CnbAdminCloud::cnb_update_condition( $cnb_cloud_notifications, $condition );
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
                        'tab' => 'visibility',
                    ),
                    $url);
            $redirect_url = esc_url_raw($redirect_link);
            wp_safe_redirect($redirect_url);
            exit;
        } else {
            $redirect_link =
                add_query_arg(
                    array(
                        'page' => 'call-now-button-conditions',
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
            'back_link' => 'admin.php?page=' . $cnb_slug_base,
        ) );
    }
}

function cnb_create_tab_url_conditions($button, $tab) {
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
* @param $condition CnbCondition
 */
function cnb_render_form_condition($condition) {
    ?>
    <table class="form-table nav-tab-active">
        <tr>
            <th colspan="2"><h2>Basic Settings</h2>
                <input type="hidden" name="conditions[<?php esc_attr_e($condition->id) ?>][id]" value="<?php if ($condition->id !== null && $condition->id !== 'new') { esc_attr_e($condition->id); } ?>" />
                <input type="hidden" name="conditions[<?php esc_attr_e($condition->id) ?>][delete]" id="cnb_condition_<?php esc_attr_e($condition->id) ?>_delete" value="" />
                <input type="hidden" name="conditions[<?php esc_attr_e($condition->id) ?>][conditionType]" value="<?php esc_attr_e($condition->conditionType) ?>" />
            </th>
        </tr>
        <tr>
            <th scope="row"><label for="cnb_condition_filter_type">Filter Type</label></th>
            <td>
                <select id="cnb_condition_filter_type" name="conditions[<?php esc_attr_e($condition->id) ?>][filterType]">
                    <?php foreach (cnb_get_condition_filter_types() as $condition_filter_type_key => $condition_filter_type_value) { ?>
                        <option value="<?php esc_attr_e($condition_filter_type_key) ?>"<?php selected($condition_filter_type_key, $condition->filterType) ?>>
                            <?php esc_html_e($condition_filter_type_value) ?>
                        </option>
                    <?php } ?>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="cnb_condition_match_type">Match Type</label></th>
            <td>
                <select id="cnb_condition_match_type" name="conditions[<?php esc_attr_e($condition->id) ?>][matchType]">
                    <?php foreach (cnb_get_condition_match_types() as $condition_match_type_key => $condition_match_type_value) { ?>
                        <option value="<?php esc_attr_e($condition_match_type_key) ?>"<?php selected($condition_match_type_key, $condition->matchType) ?>>
                            <?php esc_html_e($condition_match_type_value) ?>
                        </option>
                    <?php } ?>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="cnb_condition_match_value">Match Value</label></th>
            <td>
                <input type="text" id="cnb_condition_match_value" class="regular-text" name="conditions[<?php esc_attr_e($condition->id) ?>][matchValue]" value="<?php esc_attr_e($condition->matchValue) ?>" />
            </td>
        </tr>

    </table>
    <?php
}

function cnb_admin_page_condition_edit_render() {
    $condition_id = cnb_get_button_id();
    $condition = new CnbCondition();
    $button = null;
    if (strlen($condition_id) > 0 && $condition_id !== 'new') {
        $condition = CnbAppRemote::cnb_remote_get_condition( $condition_id );
    } elseif ($condition_id === 'new') {
        $condition->id = 'new';
    }

    add_action('cnb_header_name', function() use($condition) {
        cnb_add_header_condition_edit($condition);
    });

    $bid = !empty($_GET['bid']) ? sanitize_text_field($_GET['bid']) : null;
    if ($bid !== null) {
        $button = CnbAppRemote::cnb_remote_get_button( $bid );
        // Create back link
        $url = admin_url('admin.php');
        $redirect_link = esc_url(
            add_query_arg(
                array(
                    'page' => 'call-now-button',
                    'action' => 'edit',
                    'tab' => 'visibility',
                    'id' => $bid),
                $url ));

        $action_verb = $condition->id === 'new' ? 'adding' : 'editing';
        $mesage = '<strong>You are '.$action_verb.' a Condition.</strong>.
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

    if ($bid !== null) { ?>
    <h2 class="nav-tab-wrapper">
        <a href="<?php echo cnb_create_tab_url_conditions($button, 'basic_options') ?>"
           class="nav-tab <?php echo cnb_is_active_tab('basic_options') ?>">Basics</a>
            <a href="<?php echo cnb_create_tab_url_conditions($button, 'actions') ?>"
               class="nav-tab <?php echo cnb_is_active_tab('actions') ?>">Actions</a>
            <a href="<?php echo cnb_create_tab_url_conditions($button, 'extra_options') ?>"
               class="nav-tab <?php echo cnb_is_active_tab('extra_options') ?>">Presentation</a>
            <a href="<?php echo cnb_create_tab_url_conditions($button, 'visibility') ?>"
               class="nav-tab <?php echo cnb_is_active_tab('visibility') ?>">Visibility</a>
            <a href="<?php echo cnb_create_tab_url_conditions($button, 'advanced_options') ?>"
               class="nav-tab <?php echo cnb_is_active_tab('advanced_options') ?>">Advanced</a>
    </h2>
    <?php } ?>
    <form action="<?php echo $redirect_link; ?>" method="post">
        <input type="hidden" name="page" value="call-now-button-conditions" />
        <input type="hidden" name="bid" value="<?php esc_attr_e($bid) ?>" />
        <input type="hidden" name="condition_id" value="<?php esc_attr_e($condition->id) ?>" />
        <input type="hidden" name="action" value="<?php echo $condition_id === 'new' ? 'cnb_create_condition' :'cnb_update_condition' ?>" />
        <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce($condition->id === 'new' ? 'cnb_create_condition' : 'cnb_update_condition') ?>" />
        <?php
        cnb_render_form_condition($condition);
        submit_button();
        ?>
    </form>
    <?php do_action('cnb_footer');
}
