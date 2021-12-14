<?php

require_once dirname( __FILE__ ) . '/RemoteTrace.php';

/**
 * Used only by CnbAppRemote
 * @private
 */
class CnbGet {
    protected $defaultExpiration = 300; // 5 * MINUTE_IN_SECONDS = 5 * 60 = 300
    protected $isCacheHit = false;
    private $useCache = false;

    public function __construct() {
        global $cnb_options;
        if ($cnb_options['api_caching'] === 1) {
            $this->useCache = true;
        }
    }

    protected function add($url, $response) {
        set_transient( CnbAppRemote::cnb_get_transient_base() . $url, $response, $this->defaultExpiration);
        return $response;
    }

    public function isLastCallCached() {
        return $this->isCacheHit;
    }

    public function get($url, $args) {
        if ($this->useCache) {
            $cache = get_transient( CnbAppRemote::cnb_get_transient_base() . $url );
            if ( $cache ) {
                $this->isCacheHit = true;

                return $cache;
            }
        }
        $this->isCacheHit = false;
        $response = wp_remote_get($url, $args);
        return $this->add($url, $response);
    }
}

class CnbAppRemoteHelper {

    private function cnb_remote_cleanup_properties( $array ) {
        return empty( $array['properties'] ) || ! is_array( $array['properties'] ) ? null : $array['properties'];
    }

    public function convertAction($action) {
        $schedule = null;
        if (isset($action['schedule'])) {
            $schedule = array(
                'showAlways'   => isset( $action['schedule']['showAlways'] ) ? $action['schedule']['showAlways'] : true,
                'daysOfWeek'   => isset( $action['schedule']['daysOfWeek'] ) ? $action['schedule']['daysOfWeek'] : array(),
                'start'        => isset( $action['schedule']['start'] ) ? $action['schedule']['start'] : null,
                'stop'         => isset( $action['schedule']['stop'] ) ? $action['schedule']['stop'] : null,
                'timezone'     => isset( $action['schedule']['timezone'] ) ? $action['schedule']['timezone'] : null,
                'outsideHours' => isset( $action['schedule']['outsideHours'] ) ? $action['schedule']['outsideHours'] : false,
            );
        }

        return array(
            'actionType'      => !empty( $action['actionType'] ) ? $action['actionType'] : 'PHONE',
            'actionValue'     => $action['actionValue'],
            'schedule'        => $schedule,
            'backgroundColor' => !empty( $action['backgroundColor'] ) ? $action['backgroundColor'] : '#009900',
            'iconColor'       => !empty( $action['iconColor'] ) ? $action['iconColor'] : '#ffffff',
            // phpcs:ignore
            'iconEnabled'     => isset( $action['iconEnabled'] ) ? boolval( $action['iconEnabled'] ) : false,
            'labelText'       => isset($action['labelText']) ? $action['labelText'] : null,
//            'iconText'        => isset($action['iconText']) ? $action['iconText'] : null,
            'iconText'        => cnb_actiontype_to_icontext($action['actionType']),
            'properties'      => self::cnb_remote_cleanup_properties($action)
        );
    }

    public function convertButton($button, $actions=null, $conditions=null) {
        if ($actions != null) {
            $button['actions'] = is_array( $actions ) ? cnb_array_column( $actions, 'id' ) : array();
        } else if (isset($button['actions']) && $button['actions'] != null) {
            $button['actions'] = is_array( $button['actions'] ) ? cnb_array_column( $button['actions'], 'id' ) : array();
        }

        if ($conditions != null) {
            $button['conditions'] = is_array( $conditions ) ? cnb_array_column( $conditions, 'id' ) : array();
        } else if (isset($button['conditions']) && $button['conditions'] != null) {
            $button['conditions'] = is_array( $button['conditions'] ) ? cnb_array_column( $button['conditions'], 'id' ) : array();
        }

        if (!isset($button['id'])) {
            $button['id'] = null;
        }

        return array(
            'id'         => $button['id'],
            // phpcs:ignore
            'active'     => isset( $button['active'] ) ? boolval( $button['active'] ) : false,
            'name'       => ! empty( $button['name'] ) ? $button['name'] : 'Button created via Wordpress plugin',
            'domain'     => $button['domain'],
            'actions'    => isset( $button['actions'] ) && is_array( $button['actions'] ) ? $button['actions'] : array(),
            'conditions' => isset( $button['conditions'] ) && is_array( $button['conditions'] ) ? $button['conditions'] : array(),
            'type'       => $button['type'],
            'options'    => $button['options']
        );
    }

    public function convertDomain($domain) {
        return array(
            'name'               => $domain['name'],
            'timezone'           => $domain['timezone'],
            'trackGA'            => $domain['trackGA'],
            'trackConversion'    => $domain['trackConversion'],
            'renew'              => $domain['renew'],
            'properties'         => self::cnb_remote_cleanup_properties( $domain )
        );
    }

    public function convertCondition($condition) {
        return array(
            'id'            => isset($condition['id']) ? $condition['id'] : null,
            'conditionType' => $condition['conditionType'],
            'filterType'    => $condition['filterType'],
            'matchType'     => $condition['matchType'],
            'matchValue'    => $condition['matchValue'],
        );
    }

    public function convertApiKey($apikey) {
        return array(
            'name' => $apikey['name'],
        );
    }
}

class CnbAppRemote {

    /**
     * By creating a proxy method, we can easily stub this for testing
     *
     * @return string Site URL link with optional path appended.
     */
    function get_site_url() {
        return \get_site_url();
    }
    /**
     * Return a cleaned up version of the Site URL.
     *
     * Removes protocol, port and path (and lowercases it)
     *
     * Example:
     * https://www.TestDomain.com:8080/test becomes www.testdomain.com
     *
     * @return string
     */
    public function cnb_clean_site_url() {
        $siteUrl = $this->get_site_url();

        $url = parse_url($siteUrl, PHP_URL_HOST);
        if ($url) {
            return strtolower($url);
        }

        // Fallback behavior
        // Order:
        // 1: Strip everything after // (so to remove a potential protocol like http(s)://
        // 2: Strip the port if found, via :1234
        // 3: Strip everything after /, so that "example.org/test" becomes "example.org"

        return strtolower(preg_replace( '/\/.*/', '',
            preg_replace( '/:[0-9]+/', '',
            preg_replace( '/.*\/\//', '', $siteUrl, 1 ), 1 ), 1 ));
    }

    /**
     * @return string usually "https://api.callnowbutton.com"
     */
    public static function cnb_get_api_base() {
        global $cnb_options;

        return isset( $cnb_options['api_base'] ) ? $cnb_options['api_base'] : 'https://api.callnowbutton.com';
    }

    /**
     * @return string usually "https://user.callnowbutton.com"
     */
    public static function cnb_get_user_base() {
        return str_replace('api', 'user', CnbAppRemote::cnb_get_api_base());
    }

    /**
     * @return int 0 if not found, otherwise the current cache key
     */
    public static function cnb__get_transient_base() {
        $val = get_transient( self::cnb_get_api_base() );
        if ( $val ) {
            return (int) $val;
        }

        return 0;
    }

    /**
     * Increments the cache key with 1
     */
    public static function cnb_incr_transient_base() {
        set_transient( self::cnb_get_api_base(), self::cnb__get_transient_base() + 1 );
    }

    public static function cnb_get_transient_base() {
        return self::cnb__get_transient_base() . self::cnb_get_api_base();
    }

    public static function cnb_remote_get_args() {
        global $cnb_options;

        $api_key = isset( $cnb_options['api_key'] ) ? $cnb_options['api_key'] : false;
        if ( ! $api_key ) {
            return new WP_Error( 'CNB_API_NOT_SETUP_YET' );
        }
        $header_name  = 'X-CNB-Api-Key';
        $header_value = $api_key;

        return array(
            'headers' => array(
                $header_name   => $header_value,
                'Content-Type' => 'application/json'
            ),
        );
    }

    public static function cnb_remote_handle_response( $response ) {
        if ( $response instanceof WP_Error ) {
            $error = new WP_Error( 'CNB_UNKNOWN_REMOTE_ERROR', 'There was an issue communicating with the CallNowButton API. Please see the detailed error message from the response below.' );
            $error->merge_from( $response );

            return $error;
        }
        if ( $response['response']['code'] == 403 ) {
            return new WP_Error( 'CNB_API_KEY_INVALID', $response['response']['message'] );
        }
        if ( $response['response']['code'] == 404 ) {
            return new WP_Error( 'CNB_ENTITY_NOT_FOUND', $response['response']['message'] );
        }
        // 402 == Payment required
        if ( $response['response']['code'] == 402 ) {
            $body = json_decode( $response['body'] );

            return new WP_Error( 'CNB_PAYMENT_REQUIRED', $response['response']['message'], $body->message );
        }
        if ( $response['response']['code'] != 200 ) {
            return new WP_Error( 'CNB_ERROR', $response['response']['message'], $response['body'] );
        }

        return json_decode( $response['body'] );
    }

    /**
     * DELETE, PATCH support.
     *
     * Includes Trace support
     *
     * @param $url string
     * @param $parsed_args array
     *
     * @return array|WP_Error
     */
    public static function cnb_wp_request( $url, $parsed_args ) {
        $http = _wp_http_get_object();

        $timer    = new RemoteTrace( $url );
        $response = $http->request( $url, $parsed_args );
        $timer->end();

        return $response;
    }

    /**
     * DELETE is missing from WordPress Core.
     *
     * This is inspired by https://developer.wordpress.org/reference/functions/wp_remote_post/
     *
     * @param $url string
     * @param $args array
     *
     * @return array|WP_Error
     */
    public static function wp_remote_delete( $url, $args = array() ) {
        $defaults    = array( 'method' => 'DELETE' );
        $parsed_args = wp_parse_args( $args, $defaults );

        return self::cnb_wp_request( $url, $parsed_args );
    }

    /**
     * PATCH is missing from WordPress Core.
     *
     * This is inspired by https://developer.wordpress.org/reference/functions/wp_remote_post/
     *
     * @param $url string
     * @param $args array
     *
     * @return array|WP_Error
     */
    public static function wp_remote_patch( $url, $args = array() ) {
        $defaults    = array( 'method' => 'PATCH' );
        $parsed_args = wp_parse_args( $args, $defaults );

        return self::cnb_wp_request( $url, $parsed_args );
    }

    public static function cnb_remote_patch( $rest_endpoint, $body ) {
        $args = self::cnb_remote_get_args();
        if ( $args instanceof WP_Error ) {
            return $args;
        }

        if ( $body != null ) {
            $args['body'] = json_encode( $body );
        }

        $url      = self::cnb_get_api_base() . $rest_endpoint;
        $response = self::wp_remote_patch( $url, $args );
        self::cnb_incr_transient_base();

        return self::cnb_remote_handle_response( $response );
    }

    public static function cnb_remote_delete( $rest_endpoint, $body = null ) {
        $args = self::cnb_remote_get_args();
        if ( $args instanceof WP_Error ) {
            return $args;
        }

        if ( $body != null ) {
            $args['body'] = json_encode( $body );
        }

        $url      = self::cnb_get_api_base() . $rest_endpoint;
        $response = self::wp_remote_delete( $url, $args );
        self::cnb_incr_transient_base();

        return self::cnb_remote_handle_response( $response );
    }

    public static function cnb_remote_post( $rest_endpoint, $body = null ) {
        $args = self::cnb_remote_get_args();
        if ( $args instanceof WP_Error ) {
            return $args;
        }

        if ( $body != null ) {
            $args['body'] = json_encode( $body );
        }

        $url      = self::cnb_get_api_base() . $rest_endpoint;
        $timer    = new RemoteTrace( $url );
        $response = wp_remote_post( $url, $args );
        self::cnb_incr_transient_base();
        $timer->end();

        return self::cnb_remote_handle_response( $response );
    }

    public static function cnb_remote_get( $rest_endpoint ) {
        $cnb_get_cache = new CnbGet();
        $args          = self::cnb_remote_get_args();
        if ( $args instanceof WP_Error ) {
            return $args;
        }

        $url      = self::cnb_get_api_base() . $rest_endpoint;
        $timer    = new RemoteTrace( $url );
        $response = $cnb_get_cache->get( $url, $args );
        $timer->setCacheHit( $cnb_get_cache->isLastCallCached() );
        $timer->end();

        return self::cnb_remote_handle_response( $response );
    }

    public static function cnb_remote_get_user_info() {
        $rest_endpoint = '/v1/user';

        return self::cnb_remote_get( $rest_endpoint );
    }

    /**
     * This returns the domain matching the WordPress domain
     * @return mixed|WP_Error
     */
    public static function cnb_remote_get_wp_domain() {
        $cnbAppRemote = new CnbAppRemote();
        $rest_endpoint = '/v1/domain/byName/' . $cnbAppRemote->cnb_clean_site_url();

        return self::cnb_remote_get( $rest_endpoint );
    }

    public static function cnb_remote_get_domain( $id ) {
        $rest_endpoint = '/v1/domain/' . $id;

        return self::cnb_remote_get( $rest_endpoint );
    }

    public static function cnb_remote_get_domains() {
        $rest_endpoint = '/v1/domain';

        return self::cnb_remote_get( $rest_endpoint );
    }

    public static function cnb_remote_get_button( $id ) {
        $rest_endpoint = '/v1/button/' . $id;

        return self::cnb_remote_get( $rest_endpoint );
    }

    public static function cnb_remote_get_button_full( $id ) {
        $rest_endpoint = '/v1/button/' . $id . '/full';

        return self::cnb_remote_get( $rest_endpoint );
    }

    public static function cnb_remote_get_buttons() {
        $rest_endpoint = '/v1/button';

        return self::cnb_remote_get( $rest_endpoint );
    }

    public static function cnb_remote_get_buttons_full() {
        $rest_endpoint = '/v1/button/full';

        return self::cnb_remote_get( $rest_endpoint );
    }

    public static function cnb_remote_get_action( $id ) {
        $rest_endpoint = '/v1/action/' . $id;

        return self::cnb_remote_get( $rest_endpoint );
    }

    public static function cnb_remote_get_actions() {
        $rest_endpoint = '/v1/action';

        return self::cnb_remote_get( $rest_endpoint );
    }

    public static function cnb_remote_get_conditions() {
        $rest_endpoint = '/v1/condition';

        return self::cnb_remote_get( $rest_endpoint );
    }

    public static function cnb_remote_get_condition( $id ) {
        $rest_endpoint = '/v1/condition/' . $id;

        return self::cnb_remote_get( $rest_endpoint );
    }

    public static function cnb_remote_get_apikeys() {
        $rest_endpoint = '/v1/apikey';

        return self::cnb_remote_get( $rest_endpoint );
    }

    public static function cnb_remote_update_button( $button ) {
        // Find the ID in the options
        $buttonId = $button['id'];

        if ( ! $buttonId ) {
            return new WP_Error( 'CNB_BUTTON_ID_MISSING', 'buttonId expected, but not found' );
        }

        $rest_endpoint = '/v1/button/' . $buttonId;

        return self::cnb_remote_patch( $rest_endpoint, $button );
    }

    public static function cnb_remote_update_domain( $domain ) {
        // Find the ID in the options
        $domainId = $domain['id'];

        if ( ! $domainId ) {
            return new WP_Error( 'CNB_DOMAIN_ID_MISSING', 'domainId expected, but not found' );
        }

        $rest_endpoint = '/v1/domain/' . $domainId;

        return self::cnb_remote_patch( $rest_endpoint, $domain );
    }

    public static function cnb_remote_delete_button( $button ) {
        // Find the ID in the options
        $buttonId = $button['id'];

        if ( ! $buttonId ) {
            return new WP_Error( 'CNB_BUTTON_ID_MISSING', 'buttonId expected, but not found' );
        }

        $rest_endpoint = '/v1/button/' . $buttonId;

        return self::cnb_remote_delete( $rest_endpoint, $button );
    }

    public static function cnb_remote_delete_domain( $domain ) {
        // Find the ID in the options
        $domainId = $domain['id'];

        if ( ! $domainId ) {
            return new WP_Error( 'CNB_DOMAIN_ID_MISSING', 'domainId expected, but not found' );
        }

        $rest_endpoint = '/v1/domain/' . $domainId;

        return self::cnb_remote_delete( $rest_endpoint );
    }

    public static function cnb_remote_delete_condition( $condition ) {
        // Find the ID in the options
        $entityId = $condition['id'];

        if ( ! $entityId ) {
            return new WP_Error( 'CNB_CONDITION_ID_MISSING', 'conditionId expected, but not found' );
        }

        $rest_endpoint = '/v1/condition/' . $entityId;

        return self::cnb_remote_delete( $rest_endpoint );
    }

    public static function cnb_remote_delete_action( $action ) {
        // Find the ID in the options
        $entityId = $action['id'];

        if ( ! $entityId ) {
            return new WP_Error( 'CNB_ACTION_ID_MISSING', 'actionId expected, but not found' );
        }

        $rest_endpoint = '/v1/action/' . $entityId;

        return self::cnb_remote_delete( $rest_endpoint );
    }

    public static function cnb_remote_delete_apikey( $apikey ) {
        // Find the ID in the options
        $apikeyId = $apikey['id'];

        if ( ! $apikeyId ) {
            return new WP_Error( 'CNB_APIKEY_ID_MISSING', 'apikeyId expected, but not found' );
        }

        $rest_endpoint = '/v1/apikey/' . $apikeyId;

        return self::cnb_remote_delete( $rest_endpoint );
    }

    public static function cnb_remote_update_action( $action ) {
        // Find the action ID in the options
        $actionId = $action['id'];

        if ( ! $actionId ) {
            return new WP_Error( 'CNB_ACTION_ID_MISSING', 'actionId expected, but not found' );
        }

        $helper = new CnbAppRemoteHelper();
        $body = $helper->convertAction($action);

        $rest_endpoint = '/v1/action/' . $actionId;

        return self::cnb_remote_patch( $rest_endpoint, $body );
    }

    /**
     * TODO See if we can make this cleaner (without actions/conditions passed in?)
     *
     * @param $button array Single Button object
     * @param $actions array Action objects
     * @param $conditions array Conditions objects
     *
     * @return mixed|WP_Error
     */
    public static function cnb_remote_update_wp_button( $button, $actions, $conditions ) {
        $buttonId = $button['id'];

        if ( ! $buttonId ) {
            return new WP_Error( 'CNB_BUTTON_ID_MISSING', 'buttonId expected, but not found' );
        }

        $helper = new CnbAppRemoteHelper();
        $body = $helper->convertButton($button, $actions, $conditions);

        return self::cnb_remote_update_button( $body );
    }

    public static function cnb_remote_create_domain( $domain ) {
        $domainId = isset( $domain['id'] ) && $domain['id'];

        if ( $domainId ) {
            return new WP_Error( 'CNB_DOMAIN_ID_FOUND', 'no domainId expected, but one was given' );
        }

        $helper = new CnbAppRemoteHelper();
        $body = $helper->convertDomain($domain);

        $rest_endpoint = '/v1/domain';

        return self::cnb_remote_post( $rest_endpoint, $body );
    }

    /**
     * @param $button array Single Button object
     *
     * @return CnbButton|WP_Error
     */
    public static function cnb_remote_create_button( $button ) {
        $buttonId = isset($button['id']) && $button['id'];

        if ( $buttonId ) {
            return new WP_Error( 'CNB_BUTTON_ID_FOUND', 'no buttonId expected, but one was given' );
        }

        $helper = new CnbAppRemoteHelper();
        $body = $helper->convertButton($button);

        $rest_endpoint = '/v1/button';

        return self::cnb_remote_post( $rest_endpoint, $body );
    }

    public static function cnb_remote_create_action( $action ) {
        $actionId = isset($action['id']) && $action['id'];

        if ( $actionId ) {
            return new WP_Error( 'CNB_ACTION_ID_FOUND', 'no actionId expected, but one was given' );
        }

        $helper = new CnbAppRemoteHelper();
        $body = $helper->convertAction($action);

        $rest_endpoint = '/v1/action';

        return self::cnb_remote_post( $rest_endpoint, $body );
    }

    public static function cnb_remote_create_condition( $condition ) {
        $conditionId = isset($condition['id']) && $condition['id'];

        if ( $conditionId ) {
            return new WP_Error( 'CNB_CONDITION_ID_FOUND', 'no conditionId expected, but one was given' );
        }

        $helper = new CnbAppRemoteHelper();
        $body = $helper->convertCondition($condition);

        $rest_endpoint = '/v1/condition';

        return self::cnb_remote_post( $rest_endpoint, $body );
    }

    public static function cnb_remote_update_condition( $condition ) {
        if ( ! $condition['id'] ) {
            return new WP_Error( 'CNB_CONDITION_ID_MISSING', 'conditionId expected, but not found' );
        }

        $helper = new CnbAppRemoteHelper();
        $body = $helper->convertCondition($condition);

        $rest_endpoint = '/v1/condition/' . $condition['id'];

        return self::cnb_remote_patch( $rest_endpoint, $body );
    }

    public static function cnb_remote_create_apikey( $apikey ) {
        $helper = new CnbAppRemoteHelper();
        $body = $helper->convertApiKey($apikey);

        $rest_endpoint = '/v1/apikey';

        return self::cnb_remote_post( $rest_endpoint, $body);
    }

    public static function cnb_remote_create_billing_portal() {
        $rest_endpoint = '/v1/stripe/createBillingPortal';

        return self::cnb_remote_post( $rest_endpoint);

    }
}
