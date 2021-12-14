<?php
require_once dirname( __FILE__ ) . '/api/CnbAppRemotePayment.php';
require_once dirname( __FILE__ ) . '/api/CnbAppRemote.php';
require_once dirname( __FILE__ ) . '/action-overview.php';

// part of domain-upgrade
function cnb_admin_page_domain_upgrade_get_checkout($arg) {
    $planId  = filter_input( INPUT_POST, 'planId', FILTER_SANITIZE_STRING );
    $domainId  = filter_input( INPUT_POST, 'domainId', FILTER_SANITIZE_STRING );

    $url = admin_url('admin.php');
    $redirect_link =
        add_query_arg(
            array(
                'page' => 'call-now-button-domains',
                'action' => 'upgrade',
                'id' => $domainId,
                'upgrade' => 'success'),
            $url );
    $callbackUri = esc_url_raw( $redirect_link );
    $checkoutSession = CnbAppRemotePayment::cnb_remote_post_subscription( $planId, $domainId, $callbackUri );

    if (is_wp_error($checkoutSession)) {
        $custom_message_data = $checkoutSession->get_error_data('CNB_ERROR');
        if (!empty($custom_message_data)) {
            $custom_message_obj = json_decode( $custom_message_data );
            $message            = $custom_message_obj->message;
            // Strip "request_id"
            if (stripos($message, '; request-id') !== 0) {
                $message = preg_replace('/; request-id.*/i', '', $message);
            }
            // Replace "customer" with "domain"
            $message = str_replace('customer', 'domain', $message);
            wp_send_json( array(
                'status'  => 'error',
                'message' => $message
            ) );
        } else {
            wp_send_json( array(
                'status'  => 'error',
                'message' => $checkoutSession->get_error_message()
            ) );
        }
    } else {
        // Get link based on Stripe checkoutSessionId
        wp_send_json( array(
            'status'  => 'success',
            'message' => $checkoutSession->checkoutSessionId
        ) );
    }
    wp_die();
}
add_action( 'wp_ajax_cnb_get_checkout', 'cnb_admin_page_domain_upgrade_get_checkout' );

function cnb_admin_button_delete_actions()  {
    // Action ID
    $action_id = !empty($_REQUEST['id']) ? sanitize_text_field($_REQUEST['id']) : null;
    $button_id = !empty($_REQUEST['bid']) ? sanitize_text_field($_REQUEST['bid']) : null;

    $result = cnb_delete_action_real($action_id, $button_id);
    wp_send_json($result);
}

add_action( 'wp_ajax_cnb_delete_action', 'cnb_admin_button_delete_actions' );
