<?php

require_once dirname( __FILE__ ) . '/CnbAppRemote.php';

class CnbAppRemotePayment {

    public static function cnb_remote_get_plans() {
        $rest_endpoint = '/v1/stripe/plans';

        return CnbAppRemote::cnb_remote_get( $rest_endpoint );
    }

    public static function cnb_remote_get_stripe_key() {
        $rest_endpoint = '/v1/stripe/key';

        return CnbAppRemote::cnb_remote_get( $rest_endpoint );
    }

    public static function cnb_remote_post_subscription( $planId, $domainId, $callbackUri = null ) {
        $callbackUri = $callbackUri === null
            ? get_site_url()
            : $callbackUri;

        $body = array(
            'plan'        => $planId,
            'domain'      => $domainId,
            'callbackUri' => $callbackUri
        );

        $rest_endpoint = '/v1/subscription';

        return CnbAppRemote::cnb_remote_post( $rest_endpoint, $body );
    }

    public static function cnb_remote_get_subscription_session( $subscriptionSessionId ) {
        $rest_endpoint = '/v1/subscription/session/' . $subscriptionSessionId;

        return CnbAppRemote::cnb_remote_get( $rest_endpoint );
    }

    public static function cnb_remote_get_subscription( $subscriptionId ) {
        $rest_endpoint = '/v1/subscription/' . $subscriptionId;

        return CnbAppRemote::cnb_remote_get( $rest_endpoint );
    }
}