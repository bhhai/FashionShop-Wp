<?php

use NSL\Notices;
use NSL\Persistent\Persistent;

require_once dirname(__FILE__) . '/provider-admin.php';
require_once dirname(__FILE__) . '/provider-dummy.php';
require_once dirname(__FILE__) . '/user.php';

abstract class NextendSocialProvider extends NextendSocialProviderDummy {

    protected $dbID;
    protected $optionKey;

    protected $enabled = false;

    /** @var NextendSocialAuth */
    protected $client;

    protected $authUserData = array();

    protected $requiredFields = array();

    protected $svg = '';

    protected $sync_fields = array();

    /**
     * NextendSocialProvider constructor.
     *
     * @param $defaultSettings
     */
    public function __construct($defaultSettings) {

        if (empty($this->dbID)) {
            $this->dbID = $this->id;
        }

        $this->optionKey = 'nsl_' . $this->id;

        do_action('nsl_provider_init', $this);

        $this->sync_fields = apply_filters('nsl_' . $this->getId() . '_sync_fields', $this->sync_fields);

        $extraSettings = apply_filters('nsl_' . $this->getId() . '_extra_settings', array(
            'ask_email'      => 'when-empty',
            'ask_user'       => 'never',
            'ask_password'   => 'never',
            'auto_link'      => 'email',
            'disabled_roles' => array(),
            'register_roles' => array(
                'default'
            )
        ));

        foreach ($this->getSyncFields() as $field_name => $fieldData) {

            $extraSettings['sync_fields/fields/' . $field_name . '/enabled']  = 0;
            $extraSettings['sync_fields/fields/' . $field_name . '/meta_key'] = $this->id . '_' . $field_name;
        }

        $this->settings = new NextendSocialLoginSettings($this->optionKey, array_merge(array(
            'settings_saved'        => '0',
            'tested'                => '0',
            'custom_default_button' => '',
            'custom_icon_button'    => '',
            'login_label'           => '',
            'register_label'        => '',
            'link_label'            => '',
            'unlink_label'          => '',
            'user_prefix'           => '',
            'user_fallback'         => '',
            'oauth_redirect_url'    => '',
            'terms'                 => '',

            'sync_fields/link'  => 0,
            'sync_fields/login' => 0
        ), $extraSettings, $defaultSettings));

        $this->admin = new NextendSocialProviderAdmin($this);


        add_action('rest_api_init', array(
            $this,
            'registerRedirectRESTRoute'
        ));

    }

    public function needPro() {
        return false;
    }

    /**
     * @return string
     */
    public function getDbID() {
        return $this->dbID;
    }

    public function getOptionKey() {
        return $this->optionKey;
    }

    public function getRawDefaultButton() {

        return '<div class="nsl-button nsl-button-default nsl-button-' . $this->id . '" style="background-color:' . $this->color . ';"><div class="nsl-button-svg-container">' . $this->svg . '</div><div class="nsl-button-label-container">{{label}}</div></div>';
    }

    public function getRawIconButton() {
        return '<div class="nsl-button nsl-button-icon nsl-button-' . $this->id . '" style="background-color:' . $this->color . ';"><div class="nsl-button-svg-container">' . $this->svg . '</div></div>';
    }

    public function getDefaultButton($label) {
        $button = $this->settings->get('custom_default_button');
        if (!empty($button)) {
            return str_replace('{{label}}', __($label, 'nextend-facebook-connect'), $button);
        }

        return str_replace('{{label}}', __($label, 'nextend-facebook-connect'), $this->getRawDefaultButton());
    }

    public function getIconButton() {
        $button = $this->settings->get('custom_icon_button');
        if (!empty($button)) {
            return $button;
        }

        return $this->getRawIconButton();
    }

    public function getLoginUrl() {
        $args = array('loginSocial' => $this->getId());

        if (isset($_REQUEST['interim-login'])) {
            $args['interim-login'] = 1;
        }

        return add_query_arg($args, NextendSocialLogin::getLoginUrl());
    }

    /**
     * Returns the url where the Provider App should redirect during the OAuth flow.
     *
     * @return string
     */
    public function getRedirectUriForOAuthFlow() {
        if ($this->oauthRedirectBehavior === 'rest_redirect') {

            return rest_url('/nextend-social-login/v1/' . $this->id . '/redirect_uri');
        }

        $args = array('loginSocial' => $this->id);

        return add_query_arg($args, NextendSocialLogin::getLoginUrl());
    }

    /**
     * Returns a single redirect URL that:
     * - we us as default redirect uri suggestion in the Getting Started and Fixed redirect uri pages.
     * - we store to detect the OAuth redirect url changes
     *
     * @return string
     */
    public function getBaseRedirectUriForAppCreation() {

        $redirectUri = $this->getRedirectUriForOAuthFlow();

        if ($this->oauthRedirectBehavior === 'default_redirect_but_app_has_restriction') {
            $parts = explode('?', $redirectUri);

            return $parts[0];
        }

        return $redirectUri;
    }

    /**
     * This function should return an array of URLs generated from getRedirectUri().
     *
     * We display the generated results in the Getting Started section and the Fixed redirect uri pages.
     * Also we use these for the OAuth redirect uri change checking.
     *
     * @return array
     */
    public function getAllRedirectUrisForAppCreation() {
        /**
         * Parameters:
         * 1: Array with an URL that should be added to the App by default.
         *
         * 2: The provider instance
         */
        return apply_filters('nsl_redirect_uri_override', array($this->getBaseRedirectUriForAppCreation()), $this);
    }

    /**
     * Enable the selected provider.
     *
     * @return bool
     */
    public function enable() {
        $this->enabled = true;

        do_action('nsl_' . $this->getId() . '_enabled');

        return true;
    }

    /**
     * Check if provider is enabled.
     *
     * @return bool
     */
    public function isEnabled() {
        return $this->enabled;
    }

    /**
     * Check if provider is verified.
     *
     * @return bool
     */
    public function isTested() {
        return !!$this->settings->get('tested');
    }

    /**
     * Check if the current redirect url of the provider matches with the one that we stored when the provider was
     * configured. Returns "false" if they are different, so a new URL needs to be added to the App.
     *
     * @return bool
     */
    public function checkOauthRedirectUrl() {
        $oauth_redirect_url = $this->settings->get('oauth_redirect_url');

        $redirectUrls = $this->getAllRedirectUrisForAppCreation();


        if (is_array($redirectUrls)) {
            /**
             * Before 3.1.2 we saved the default redirect url of the provider ( e.g.:
             * https://example.com/wp-login.php?loginSocial=twitter ) for the OAuth check. However, some providers ( e.g.
             * Microsoft ) can use the REST API URL as redirect url. In these cases if the URL of the OAuth page was changed,
             * we gave a false warning for such providers.
             *
             * We shouldn't throw warnings for users who have the redirect uri stored still with the old format.
             * For this reason we need to push the legacy redirect url into the $redirectUrls array, too!
             */
            $legacyRedirectURL = add_query_arg(array('loginSocial' => $this->getId()), NextendSocialLogin::getLoginUrl());
            if (!in_array($legacyRedirectURL, $redirectUrls)) {
                $redirectUrls[] = $legacyRedirectURL;
            }


            if (in_array($oauth_redirect_url, $redirectUrls)) {
                return true;
            }
        }

        return false;
    }

    public function updateOauthRedirectUrl() {
        $this->settings->update(array(
            'oauth_redirect_url' => $this->getBaseRedirectUriForAppCreation()
        ));
    }

    /**
     * @return array
     */
    public function getRequiredFields() {
        return $this->requiredFields;
    }

    /**
     * Get the current state of a Provider.
     *
     * @return string
     */
    public function getState() {
        foreach ($this->requiredFields as $name => $label) {
            $value = $this->settings->get($name);
            if (empty($value)) {
                return 'not-configured';
            }
        }
        if (!$this->isTested()) {
            return 'not-tested';
        }

        if (!$this->isEnabled()) {
            return 'disabled';
        }

        return 'enabled';
    }

    /**
     * Authenticate and connect with the provider.
     */
    public function connect() {
        try {
            $this->doAuthenticate();
        } catch (NSLContinuePageRenderException $e) {
            // This is not an error. We allow the page to continue the normal display flow and later we inject our things.
            // Used by Theme my login function where we override the shortcode and we display our email request.
        } catch (Exception $e) {
            $this->onError($e);
        }
    }

    /**
     * @return NextendSocialAuth
     */
    protected abstract function getClient();

    public function getTestUrl() {
        return $this->getClient()
                    ->getTestUrl();
    }

    /**
     * @throws NSLContinuePageRenderException
     */
    protected function doAuthenticate() {

        if (!headers_sent()) {
            //All In One WP Security sets a LOCATION header, so we need to remove it to do a successful test.
            if (function_exists('header_remove')) {
                header_remove("LOCATION");
            } else {
                header('LOCATION:', true); //Under PHP 5.3
            }
        }

        //If it is a real login action, add the actions for the connection.
        if (!$this->isTest()) {
            add_action($this->id . '_login_action_before', array(
                $this,
                'liveConnectBefore'
            ));
            add_action($this->id . '_login_action_redirect', array(
                $this,
                'liveConnectRedirect'
            ));
            add_action($this->id . '_login_action_get_user_profile', array(
                $this,
                'liveConnectGetUserProfile'
            ));

            $interim_login = isset($_REQUEST['interim-login']);
            if ($interim_login) {
                Persistent::set($this->id . '_interim_login', 1);
            }
            /**
             * Store the settings for the provider login.
             */
            $display = isset($_REQUEST['display']);
            if ($display && $_REQUEST['display'] == 'popup') {
                Persistent::set($this->id . '_display', 'popup');
            }

        } else { //This is just to verify the settings.
            add_action($this->id . '_login_action_get_user_profile', array(
                $this,
                'testConnectGetUserProfile'
            ));
        }

        // Redirect if the registration is blocked by another Plugin like Cerber.
        if (function_exists('cerber_is_allowed')) {
            $allowed = cerber_is_allowed();
            if (!$allowed) {
                global $wp_cerber;
                $error = $wp_cerber->getErrorMsg();
                Notices::addError($error);
                $this->redirectToLoginForm();
            }
        }

        do_action($this->id . '_login_action_before', $this);

        $client = $this->getClient();

        $accessTokenData = $this->getAnonymousAccessToken();

        $client->checkError();

        do_action($this->id . '_login_action_redirect', $this);

        /**
         * Check if we have an accessToken and a code.
         * If there is no access token and code it redirects to the Authorization Url.
         */
        if (!$accessTokenData && !$client->hasAuthenticateData()) {

            header('LOCATION: ' . $client->createAuthUrl());
            exit;

        } else {
            /**
             * If the code is OK but there is no access token, authentication is necessary.
             */
            if (!$accessTokenData) {

                $accessTokenData = $client->authenticate();

                $accessTokenData = $this->requestLongLivedToken($accessTokenData);

                /**
                 * store the access token
                 */
                $this->setAnonymousAccessToken($accessTokenData);
            } else {
                $client->setAccessTokenData($accessTokenData);
            }
            /**
             * if the login display was in popup window,
             * in the source window the user is redirected to the login url.
             * and the popup window must be closed
             */
            if (Persistent::get($this->id . '_display') == 'popup') {
                Persistent::delete($this->id . '_display');
                ?>
                <!doctype html>
                <html lang=en>
                <head>
                    <meta charset=utf-8>
                    <title><?php _e('Authentication successful', 'nextend-facebook-connect'); ?></title>
                    <script type="text/javascript">
                        try {
                            if (window.opener !== null && window.opener !== window) {
                                var sameOrigin = true;
                                try {
                                    var currentOrigin = window.location.protocol + '//' + window.location.hostname;
                                    if (window.opener.location.href.substring(0, currentOrigin.length) !== currentOrigin) {
                                        sameOrigin = false;
                                    }

                                } catch (e) {
                                    /**
                                     * Blocked cross origin
                                     */
                                    sameOrigin = false;
                                }
                                if (sameOrigin) {
                                    var url = <?php echo wp_json_encode($this->getLoginUrl()); ?>;
                                    if (typeof window.opener.nslRedirect === 'function') {
                                        window.opener.nslRedirect(url);
                                    } else {
                                        window.opener.location = url;
                                    }
                                    window.close();
                                } else {
                                    window.location.reload(true);
                                }
                            } else {
                                window.location.reload(true);
                            }
                        } catch (e) {
                            window.location.reload(true);
                        }
                    </script>
                </head>
                <body><a href="<?php echo esc_url($this->getLoginUrl()); ?>"><?php echo 'Continue...'; ?></a></body>
                </html>
                <?php
                exit;
            }

            /**
             * Retrieves the userinfo trough the REST API and connect with the provider.
             * Redirects to the last location.
             */
            $this->authUserData = $this->getCurrentUserInfo();

            do_action($this->id . '_login_action_get_user_profile', $accessTokenData);
        }
    }

    /**
     * @param $access_token
     * Connect with the selected provider.
     * After a successful login, we no longer need the previous persistent data.
     */
    public function liveConnectGetUserProfile($access_token) {

        $socialUser = new NextendSocialUser($this, $access_token);
        $socialUser->liveConnectGetUserProfile();

        $this->deleteLoginPersistentData();
        $this->redirectToLastLocationOther(true);
    }

    /**
     * @param $user_id
     * @param $providerIdentifier
     * @param $isRegister
     * Insert the userid into the wp_social_users table,
     * in this way a link is created between user accounts and the providers.
     *
     * @return bool
     */
    public function linkUserToProviderIdentifier($user_id, $providerIdentifier, $isRegister = false) {
        /** @var $wpdb WPDB */ global $wpdb;

        $connectedProviderID = $this->getProviderIdentifierByUserID($user_id);

        if ($connectedProviderID !== null) {
            if ($connectedProviderID == $providerIdentifier) {
                // This provider already linked to this user
                return true;
            }

            // User already have this provider attached to his account with different provider id.
            return false;
        }

        if ($isRegister) {
            /**
             * This is a register action.
             */
            $wpdb->insert($wpdb->prefix . 'social_users', array(
                'ID'            => $user_id,
                'type'          => $this->dbID,
                'identifier'    => $providerIdentifier,
                'register_date' => current_time('mysql'),
                'link_date'     => current_time('mysql'),
            ), array(
                '%d',
                '%s',
                '%s',
                '%s',
                '%s'
            ));
        } else {
            /**
             * This is a link action.
             */
            $wpdb->insert($wpdb->prefix . 'social_users', array(
                'ID'         => $user_id,
                'type'       => $this->dbID,
                'identifier' => $providerIdentifier,
                'link_date'  => current_time('mysql'),
            ), array(
                '%d',
                '%s',
                '%s',
                '%s'
            ));
        }

        do_action('nsl_' . $this->getId() . '_link_user', $user_id, $this->getId());

        return true;
    }

    public function getUserIDByProviderIdentifier($identifier) {
        /** @var $wpdb WPDB */ global $wpdb;

        return $wpdb->get_var($wpdb->prepare('SELECT ID FROM `' . $wpdb->prefix . 'social_users` WHERE type = %s AND identifier = %s', array(
            $this->dbID,
            $identifier
        )));
    }

    protected function getProviderIdentifierByUserID($user_id) {
        /** @var $wpdb WPDB */ global $wpdb;

        return $wpdb->get_var($wpdb->prepare('SELECT identifier FROM `' . $wpdb->prefix . 'social_users` WHERE type = %s AND ID = %s', array(
            $this->dbID,
            $user_id
        )));
    }

    /**
     * @param $user_id
     * Delete the link between the user account and the provider.
     */
    public function removeConnectionByUserID($user_id) {
        /** @var $wpdb WPDB */ global $wpdb;

        $wpdb->query($wpdb->prepare('DELETE FROM `' . $wpdb->prefix . 'social_users` WHERE type = %s AND ID = %d', array(
            $this->dbID,
            $user_id
        )));
    }

    protected function unlinkUser() {
        //Filter to disable unlinking social accounts
        $unlinkAllowed = apply_filters('nsl_allow_unlink', true);

        if ($unlinkAllowed) {
            $user_info = wp_get_current_user();
            if ($user_info->ID) {
                $this->removeConnectionByUserID($user_info->ID);

                do_action('nsl_unlink_user', $user_info->ID, $this->getId());

                return true;
            }
        }

        return false;
    }

    /**
     * If the current user has linked the account with a provider return the user identifier else false.
     *
     * @return bool|null|string
     */
    public function isCurrentUserConnected() {
        /** @var $wpdb WPDB */ global $wpdb;

        $current_user = wp_get_current_user();
        $ID           = $wpdb->get_var($wpdb->prepare('SELECT identifier FROM `' . $wpdb->prefix . 'social_users` WHERE type LIKE %s AND ID = %d', array(
            $this->dbID,
            $current_user->ID
        )));
        if ($ID === null) {
            return false;
        }

        return $ID;
    }

    /**
     * @param $user_id
     * If a user has linked the account with a provider return the user identifier else false.
     *
     * @return bool|null|string
     */
    public function isUserConnected($user_id) {
        /** @var $wpdb WPDB */ global $wpdb;

        $ID = $wpdb->get_var($wpdb->prepare('SELECT identifier FROM `' . $wpdb->prefix . 'social_users` WHERE type LIKE %s AND ID = %d', array(
            $this->dbID,
            $user_id
        )));
        if ($ID === null) {
            return false;
        }

        return $ID;
    }

    public function findUserByAccessToken($access_token) {
        return $this->getUserIDByProviderIdentifier($this->findSocialIDByAccessToken($access_token));
    }

    public function findSocialIDByAccessToken($access_token) {
        $client = $this->getClient();
        $client->setAccessTokenData($access_token);
        $this->authUserData = $this->getCurrentUserInfo();

        return $this->getAuthUserData('id');
    }

    public function getConnectButton($buttonStyle = 'default', $redirectTo = null, $trackerData = false, $labelType = 'login') {
        $arg = array();
        if (!empty($redirectTo)) {
            $arg['redirect'] = urlencode($redirectTo);
        } else if (!empty($_GET['redirect_to'])) {
            $arg['redirect'] = urlencode($_GET['redirect_to']);
        } else {
            $currentPageUrl = NextendSocialLogin::getCurrentPageURL();
            if ($currentPageUrl !== false) {
                $arg['redirect'] = urlencode($currentPageUrl);
            }
        }

        if ($trackerData !== false) {
            $arg['trackerdata']      = urlencode($trackerData);
            $arg['trackerdata_hash'] = urlencode(wp_hash($trackerData));

        }

        $label                  = $this->settings->get('login_label');
        $useCustomRegisterLabel = NextendSocialLogin::$settings->get('custom_register_label');
        if ($labelType == 'register' && $useCustomRegisterLabel) {
            $label = $this->settings->get('register_label');;
        }

        switch ($buttonStyle) {
            case 'icon':

                $button = $this->getIconButton();
                break;
            default:

                $button = $this->getDefaultButton($label);
                break;
        }

        return '<a href="' . esc_url(add_query_arg($arg, $this->getLoginUrl())) . '" rel="nofollow" aria-label="' . esc_attr__($label) . '" data-plugin="nsl" data-action="connect" data-provider="' . esc_attr($this->getId()) . '" data-popupwidth="' . $this->getPopupWidth() . '" data-popupheight="' . $this->getPopupHeight() . '">' . $button . '</a>';
    }

    public function getLinkButton() {

        $args = array(
            'action' => 'link'
        );

        $redirect = NextendSocialLogin::getCurrentPageURL();
        if ($redirect !== false) {
            $args['redirect'] = urlencode($redirect);
        }

        return '<a href="' . esc_url(add_query_arg($args, $this->getLoginUrl())) . '" style="text-decoration:none;display:inline-block;box-shadow:none;" data-plugin="nsl" data-action="link" data-provider="' . esc_attr($this->getId()) . '" data-popupwidth="' . $this->getPopupWidth() . '" data-popupheight="' . $this->getPopupHeight() . '" aria-label="' . esc_attr__($this->settings->get('link_label')) . '">' . $this->getDefaultButton($this->settings->get('link_label')) . '</a>';
    }

    public function getUnLinkButton() {

        $args = array(
            'action' => 'unlink'
        );

        $redirect = NextendSocialLogin::getCurrentPageURL();
        if ($redirect !== false) {
            $args['redirect'] = urlencode($redirect);
        }

        return '<a href="' . esc_url(add_query_arg($args, $this->getLoginUrl())) . '" style="text-decoration:none;display:inline-block;box-shadow:none;" data-plugin="nsl" data-action="unlink" data-provider="' . esc_attr($this->getId()) . '" aria-label="' . esc_attr__($this->settings->get('unlink_label')) . '">' . $this->getDefaultButton($this->settings->get('unlink_label')) . '</a>';
    }

    public function redirectToLoginForm() {
        self::redirect(__('Authentication error', 'nextend-facebook-connect'), NextendSocialLogin::enableNoticeForUrl(NextendSocialLogin::getLoginUrl()));
    }

    /**
     * -Allows for logged in users to unlink their account from a provider, if it was linked, and
     * redirects to the last location.
     * -During linking process, store the action as link. After the linking process is finished,
     * delete this stored info and redirects to the last location.
     */
    public function liveConnectBefore() {

        if (is_user_logged_in() && $this->isCurrentUserConnected()) {

            if (isset($_GET['action']) && $_GET['action'] == 'unlink') {
                if ($this->unlinkUser()) {
                    Notices::addSuccess(__('Unlink successful.', 'nextend-facebook-connect'));
                } else {
                    Notices::addError(__('Unlink is not allowed!', 'nextend-facebook-connect'));
                }
            }

            $this->redirectToLastLocationOther(true);
            exit;
        }

        if (isset($_GET['action']) && $_GET['action'] == 'link') {
            Persistent::set($this->id . '_action', 'link');
        }

        if (is_user_logged_in() && Persistent::get($this->id . '_action') != 'link') {
            $this->deleteLoginPersistentData();

            $this->redirectToLastLocationOther();
            exit;
        }
    }

    /**
     * Store where the user logged in.
     */
    public function liveConnectRedirect() {
        if (!empty($_GET['trackerdata']) && !empty($_GET['trackerdata_hash'])) {
            if (wp_hash($_GET['trackerdata']) === $_GET['trackerdata_hash']) {
                Persistent::set('trackerdata', $_GET['trackerdata']);
            }
        }
        if (!empty($_GET['redirect'])) {
            Persistent::set('redirect', $_GET['redirect']);
        }
    }

    public function redirectToLastLocation($notice = false) {
        $url = $this->getLastLocationRedirectTo();

        if (Persistent::get($this->id . '_interim_login') == 1) {
            $this->deleteLoginPersistentData();
            $args['interim_login'] = 'nsl';

            $url = add_query_arg($args, NextendSocialLogin::getLoginUrl('login'));
            if ($notice) {
                $url = NextendSocialLogin::enableNoticeForUrl($url);
            }

            self::redirect(__('Authentication successful', 'nextend-facebook-connect'), $url);

            exit;
        }

        if ($notice) {
            $url = NextendSocialLogin::enableNoticeForUrl($url);
        }
        self::redirect(__('Authentication successful', 'nextend-facebook-connect'), $url);
    }

    /**
     * @param bool $notice
     */
    protected function redirectToLastLocationOther($notice = false) {
        $this->redirectToLastLocation($notice);
    }

    protected function validateRedirect($location) {
        $location = wp_sanitize_redirect($location);

        return wp_validate_redirect($location, apply_filters('wp_safe_redirect_fallback', admin_url(), 302));
    }

    public function hasFixedRedirect() {
        if (NextendSocialLogin::$WPLoginCurrentFlow == 'register') {
            $fixedRedirect = NextendSocialLogin::$settings->get('redirect_reg');
            $fixedRedirect = apply_filters($this->id . '_register_redirect_url', $fixedRedirect, $this);
            if (!empty($fixedRedirect)) {
                return true;
            }

        } else if (NextendSocialLogin::$WPLoginCurrentFlow == 'login') {
            $fixedRedirect = NextendSocialLogin::$settings->get('redirect');
            $fixedRedirect = apply_filters($this->id . '_login_redirect_url', $fixedRedirect, $this);
            if (!empty($fixedRedirect)) {
                return true;
            }
        }

        return false;
    }

    /**
     * If fixed redirect url is set, redirect to fixed redirect url.
     * If fixed redirect url is not set, but redirect is in the url redirect to the $_GET['redirect'].
     * If fixed redirect url is not set and there is no redirect in the url, redirects to the default redirect url if it
     * is set.
     * Else redirect to the site url.
     *
     * @return mixed|void
     */
    protected function getLastLocationRedirectTo() {
        $redirect_to           = '';
        $requested_redirect_to = '';
        $fixedRedirect         = '';

        if (NextendSocialLogin::$WPLoginCurrentFlow == 'register') {

            $fixedRedirect = NextendSocialLogin::$settings->get('redirect_reg');
            $fixedRedirect = apply_filters($this->id . '_register_redirect_url', $fixedRedirect, $this);

        } else if (NextendSocialLogin::$WPLoginCurrentFlow == 'login') {

            $fixedRedirect = NextendSocialLogin::$settings->get('redirect');
            $fixedRedirect = apply_filters($this->id . '_login_redirect_url', $fixedRedirect, $this);

        }

        if (!empty($fixedRedirect)) {
            $redirect_to = $fixedRedirect;
        } else {
            $requested_redirect_to = Persistent::get('redirect');

            if (!empty($requested_redirect_to)) {
                if (empty($requested_redirect_to) || !NextendSocialLogin::isAllowedRedirectUrl($requested_redirect_to)) {
                    if (!empty($_GET['redirect']) && NextendSocialLogin::isAllowedRedirectUrl($_GET['redirect'])) {
                        $requested_redirect_to = $_GET['redirect'];
                    } else {
                        $requested_redirect_to = '';
                    }
                }

                if (empty($requested_redirect_to)) {
                    $redirect_to = site_url();
                } else {
                    $redirect_to = $requested_redirect_to;
                }
                $redirect_to = wp_sanitize_redirect($redirect_to);
                $redirect_to = wp_validate_redirect($redirect_to, site_url());

                $redirect_to = $this->validateRedirect($redirect_to);
            } else if (!empty($_GET['redirect']) && NextendSocialLogin::isAllowedRedirectUrl($_GET['redirect'])) {
                $redirect_to = $_GET['redirect'];

                $redirect_to = wp_sanitize_redirect($redirect_to);
                $redirect_to = wp_validate_redirect($redirect_to, site_url());

                $redirect_to = $this->validateRedirect($redirect_to);
            }

            if (empty($redirect_to)) {
                $defaultRedirect = '';

                if (NextendSocialLogin::$WPLoginCurrentFlow == 'register') {
                    $defaultRedirect = NextendSocialLogin::$settings->get('default_redirect_reg');
                    $defaultRedirect = apply_filters($this->id . '_default_register_redirect_url', $defaultRedirect, $this);

                } else if (NextendSocialLogin::$WPLoginCurrentFlow == 'login') {
                    $defaultRedirect = NextendSocialLogin::$settings->get('default_redirect');
                    $defaultRedirect = apply_filters($this->id . '_default_[login_redirect_url', $defaultRedirect, $this);
                }

                if ((!empty($defaultRedirect))) {
                    $redirect_to = $defaultRedirect;
                }
            }

            $redirect_to = apply_filters('nsl_' . $this->getId() . 'default_last_location_redirect', $redirect_to, $requested_redirect_to);
        }

        if ($redirect_to == '' || $redirect_to == $this->getLoginUrl()) {
            $redirect_to = site_url();
        }

        Persistent::delete('redirect');

        return apply_filters('nsl_' . $this->getId() . 'last_location_redirect', $redirect_to, $requested_redirect_to);
    }

    /**
     * @param $user_id
     * @param $provider     NextendSocialProvider
     * @param $access_token string
     */
    public function syncProfile($user_id, $provider, $access_token) {
    }

    /**
     * Check if a logged in user with manage_options capability, want to verify their provider settings.
     *
     * @return bool
     */
    public function isTest() {
        if (is_user_logged_in() && current_user_can('manage_options')) {
            if (isset($_REQUEST['test'])) {
                Persistent::set('test', 1);

                return true;
            } else if (Persistent::get('test') == 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Make the current provider in verified mode, and update the oauth_redirect_url.
     */
    public function testConnectGetUserProfile() {

        $this->deleteLoginPersistentData();

        $this->settings->update(array(
            'tested'             => 1,
            'oauth_redirect_url' => $this->getBaseRedirectUriForAppCreation()
        ));

        Notices::addSuccess(__('The test was successful', 'nextend-facebook-connect'));

        ?>
        <!doctype html>
        <html lang=en>
        <head>
            <meta charset=utf-8>
            <title><?php _e('The test was successful', 'nextend-facebook-connect'); ?></title>
            <script type="text/javascript">
                window.opener.location.reload(true);
                window.close();
            </script>
        </head>
        </html>
        <?php
        exit;
    }

    /**
     * @param $accessToken
     * Store the accessToken data.
     */
    protected function setAnonymousAccessToken($accessToken) {
        Persistent::set($this->id . '_at', $accessToken);
    }

    protected function getAnonymousAccessToken() {
        return Persistent::get($this->id . '_at');
    }

    public function deleteLoginPersistentData() {
        Persistent::delete($this->id . '_at');
        Persistent::delete($this->id . '_interim_login');
        Persistent::delete($this->id . '_display');
        Persistent::delete($this->id . '_action');
        Persistent::delete('test');
    }

    /**
     * @param $e Exception
     */
    protected function onError($e) {
        if (NextendSocialLogin::$settings->get('debug') == 1 || $this->isTest()) {
            header('HTTP/1.0 401 Unauthorized');
            echo "Error: " . $e->getMessage() . "\n";
        } else {
            //@TODO we might need to make difference between user cancelled auth and error and redirect the user based on that.
            $url = $this->getLastLocationRedirectTo();
            ?>
            <!doctype html>
            <html lang=en>
            <head>
                <meta charset=utf-8>
                <title><?php echo __('Authentication failed', 'nextend-facebook-connect'); ?></title>
                <script type="text/javascript">
                    try {
                        if (window.opener !== null && window.opener !== window) {
                            var sameOrigin = true;
                            try {
                                var currentOrigin = window.location.protocol + '//' + window.location.hostname;
                                if (window.opener.location.href.substring(0, currentOrigin.length) !== currentOrigin) {
                                    sameOrigin = false;
                                }

                            } catch (e) {
                                /**
                                 * Blocked cross origin
                                 */
                                sameOrigin = false;
                            }
                            if (sameOrigin) {
                                window.close();
                            }
                        }
                    } catch (e) {
                    }
                    window.location = <?php echo wp_json_encode($url); ?>;
                </script>
                <meta http-equiv="refresh" content="0;<?php echo esc_attr($url); ?>">
            </head>
            <body>
            </body>
            </html>
            <?php
        }
        $this->deleteLoginPersistentData();
        exit;
    }

    protected function saveUserData($user_id, $key, $data) {
        update_user_meta($user_id, $this->id . '_' . $key, $data);
    }

    protected function getUserData($user_id, $key) {
        return get_user_meta($user_id, $this->id . '_' . $key, true);
    }

    public function getAccessToken($user_id) {
        return $this->getUserData($user_id, 'access_token');
    }

    /**
     * @param $user_id
     *
     * @return bool
     * @deprecated
     *
     */
    public function getAvatar($user_id) {

        return false;
    }

    /**
     * @return array
     */
    protected function getCurrentUserInfo() {
        return array();
    }

    protected function requestLongLivedToken($accessTokenData) {
        return $accessTokenData;
    }

    /**
     * @param $key
     *
     * @return string
     */
    public function getAuthUserData($key) {
        return '';
    }

    /**
     * @param $title
     * @param $url
     * Redirect the source of the popup window to a specified url.
     */
    public static function redirect($title, $url) {
        ?>
        <!doctype html>
        <html lang=en>
        <head>
            <meta charset=utf-8>
            <title><?php echo $title; ?></title>
            <script type="text/javascript">
                try {
                    if (window.opener !== null && window.opener !== window) {
                        var sameOrigin = true;
                        try {
                            var currentOrigin = window.location.protocol + '//' + window.location.hostname;
                            if (window.opener.location.href.substring(0, currentOrigin.length) !== currentOrigin) {
                                sameOrigin = false;
                            }

                        } catch (e) {
                            /**
                             * Blocked cross origin
                             */
                            sameOrigin = false;
                        }
                        if (sameOrigin) {
                            window.opener.location = <?php echo wp_json_encode($url); ?>;
                            window.close();
                        }
                    }
                } catch (e) {
                }
                window.location = <?php echo wp_json_encode($url); ?>;
            </script>
            <meta http-equiv="refresh" content="0;<?php echo esc_attr($url); ?>">
        </head>
        <body>
        </body>
        </html>
        <?php
        exit;
    }

    public function getSyncFields() {
        return $this->sync_fields;
    }

    public function hasSyncFields() {
        return !empty($this->sync_fields);
    }

    public function validateSettings($newData, $postedData) {

        return $newData;
    }

    protected function needUpdateAvatar($user_id) {
        return apply_filters('nsl_avatar_store', NextendSocialLogin::$settings->get('avatar_store'), $user_id, $this);
    }

    protected function updateAvatar($user_id, $url) {
        do_action('nsl_update_avatar', $this, $user_id, $url);
    }

    public function exportPersonalData($userID) {
        $data = array();

        $socialID = $this->isUserConnected($userID);
        if ($socialID !== false) {
            $data[] = array(
                'name'  => $this->getLabel() . ' ' . __('Identifier', 'nextend-facebook-connect'),
                'value' => $socialID,
            );
        }

        $accessToken = $this->getAccessToken($userID);
        if (!empty($accessToken)) {
            $data[] = array(
                'name'  => $this->getLabel() . ' ' . __('Access token', 'nextend-facebook-connect'),
                'value' => $accessToken,
            );
        }

        $profilePicture = $this->getUserData($userID, 'profile_picture');
        if (!empty($profilePicture)) {
            $data[] = array(
                'name'  => $this->getLabel() . ' ' . __('Profile Picture'),
                'value' => $profilePicture,
            );
        }

        foreach ($this->getSyncFields() as $fieldName => $fieldData) {
            $meta_key = $this->settings->get('sync_fields/fields/' . $fieldName . '/meta_key');
            if (!empty($meta_key)) {
                $value = get_user_meta($userID, $meta_key, true);
                if (!empty($value)) {
                    $data[] = array(
                        'name'  => $this->getLabel() . ' ' . $fieldData['label'],
                        'value' => $value
                    );
                }
            }
        }


        return $data;
    }

    protected function storeAccessToken($userID, $accessToken) {
        if (NextendSocialLogin::$settings->get('store_access_token') == 1) {
            $this->saveUserData($userID, 'access_token', $accessToken);
        }
    }

    public function getSyncDataFieldDescription($fieldName) {
        return '';
    }

    /**
     * @param $user_id
     * Update social_users table with login date of the user.
     */
    public function logLoginDate($user_id) {
        /** @var $wpdb WPDB */ global $wpdb;
        $wpdb->update($wpdb->prefix . 'social_users', array('login_date' => current_time('mysql'),), array(
            'ID'   => $user_id,
            'type' => $this->dbID
        ), array(
            '%s',
            '%s'
        ));
    }

    public function registerRedirectRESTRoute() {
        if ($this->oauthRedirectBehavior === 'rest_redirect') {
            register_rest_route('nextend-social-login/v1', $this->id . '/redirect_uri', array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array(
                    $this,
                    'redirectToProviderEndpointWithStateAndCode'
                ),
                'args'                => array(
                    'state' => array(
                        'required' => true,
                    ),
                    'code'  => array(
                        'required' => true,
                    )
                ),
                'permission_callback' => '__return_true',
            ));
        }
    }

    /**
     * @param WP_REST_Request $request Full details about the request.
     *
     * Registers a REST API endpoints for a provider. This endpoint handles the redirect to the login endpoint of the
     * currently used provider. The state and code GET parameters will be added to the login URL, so we can imitate as
     * if the provider would already returned the state and code parameters to the original login url.
     *
     * @return WP_Error|WP_REST_Response
     */
    public function redirectToProviderEndpointWithStateAndCode($request) {
        $params       = $request->get_params();
        $errorMessage = '';

        if (!empty($params['state']) && !empty($params['code'])) {

            $provider = NextendSocialLogin::$allowedProviders[$this->id];

            try {
                $providerEndpoint = $provider->getLoginUrl();

                if (defined('WPML_PLUGIN_BASENAME')) {
                    $providerEndpoint = $provider->getTranslatedLoginURLForRestRedirect();
                }

                $providerEndpointWithStateAndCode = add_query_arg(array(
                    'state' => $params['state'],
                    'code'  => $params['code']
                ), $providerEndpoint);
                wp_safe_redirect($providerEndpointWithStateAndCode);
                exit;

            } catch (Exception $e) {
                $errorMessage = $e->getMessage();
            }
        } else {
            if (empty($params['state']) && empty($params['code'])) {
                $errorMessage = 'The code and state parameters are empty!';
            } else if (empty($params['state'])) {
                $errorMessage = 'The state parameter is empty!';
            } else {
                $errorMessage = 'The code parameter is empty!';
            }
        }

        return new WP_Error('error', $errorMessage);
    }

    /**
     * Generates a single translated login URL where the REST /redirect_uri endpoint of the currently used provider
     * should redirect to instead of the original login url.
     *
     * @return string
     */
    public function getTranslatedLoginURLForRestRedirect() {
        $originalLoginUrl = $this->getLoginUrl();

        /**
         * We should attempt to generate translated login URLs only if WPML is active and there is a language code defined.
         */
        if (defined('WPML_PLUGIN_BASENAME') && defined('ICL_LANGUAGE_CODE')) {

            global $sitepress;

            $languageCode = ICL_LANGUAGE_CODE;


            if ($sitepress && method_exists($sitepress, 'get_active_languages') && $languageCode) {

                $WPML_active_languages = $sitepress->get_active_languages();

                if (count($WPML_active_languages) > 1) {
                    /**
                     * Fix:
                     * When WPML has the language URL format set to "Language name added as a parameter",
                     * we can not pass that parameter in the Authorization request in some cases ( e.g.: Microsoft ).
                     * In these cases the user will end up redirected to the redirect URL without language parameter,
                     * so after the login we won't be able to redirect them to registration flow page of the corresponding language.
                     * In these cases we need to use the language code according to the url where we should redirect after the login.
                     */
                    $WPML_language_url_format = false;
                    if (method_exists($sitepress, 'get_setting')) {
                        $WPML_language_url_format = $sitepress->get_setting('language_negotiation_type');
                    }
                    if ($WPML_language_url_format && $WPML_language_url_format == 3) {
                        $persistentRedirect = Persistent::get('redirect');
                        if ($persistentRedirect) {
                            $persistentRedirectQueryParams = array();
                            $persistentRedirectQueryString = parse_url($persistentRedirect, PHP_URL_QUERY);
                            parse_str($persistentRedirectQueryString, $persistentRedirectQueryParams);
                            if (isset($persistentRedirectQueryParams['lang']) && !empty($persistentRedirectQueryParams['lang'])) {
                                $languageParam = sanitize_text_field($persistentRedirectQueryParams['lang']);
                                if (in_array($languageParam, array_keys($WPML_active_languages))) {
                                    /**
                                     * The language code that we got from the persistent redirect url is a valid language code for WPML,
                                     * so we can use this code.
                                     */
                                    $languageCode = $languageParam;
                                }
                            }
                        }
                    }


                    $args      = array('loginSocial' => $this->getId());
                    $proxyPage = NextendSocialLogin::getProxyPage();

                    if ($proxyPage) {
                        //OAuth flow handled over OAuth redirect uri proxy page
                        $convertedURL = get_permalink(apply_filters('wpml_object_id', $proxyPage, 'page', false, $languageCode));
                        if ($convertedURL) {
                            $convertedURL = add_query_arg($args, $convertedURL);

                            return $convertedURL;
                        }

                    } else {
                        //OAuth flow handled over wp-login.php

                        if ($WPML_language_url_format && $WPML_language_url_format == 3 && (!class_exists('\WPML\UrlHandling\WPLoginUrlConverter') || (class_exists('\WPML\UrlHandling\WPLoginUrlConverter') && (!get_option(\WPML\UrlHandling\WPLoginUrlConverter::SETTINGS_KEY, false))))) {
                            /**
                             * We need to display the original redirect url when the
                             * Language URL format is set to "Language name added as a parameter and:
                             * -when the WPLoginUrlConverter class doesn't exists, since that case it is an old WPML version that can not translate the /wp-login.php page
                             * -if "Login and registration pages - Allow translating the login and registration pages" is disabled
                             */
                            return $originalLoginUrl;
                        } else {
                            global $wpml_url_converter;
                            /**
                             * When the language URL format is set to "Different languages in directories" or "A different domain per language", then the Redirect URI will be different for each languages
                             * Also when the language URL format is set to "Language name added as a parameter" and the "Login and registration pages - Allow translating the login and registration pages" setting is enabled, the urls will be different.
                             */
                            if ($wpml_url_converter && method_exists($wpml_url_converter, 'convert_url')) {

                                $convertedURL = $wpml_url_converter->convert_url(site_url('wp-login.php'), $languageCode);

                                $convertedURL = add_query_arg($args, $convertedURL);


                                return $convertedURL;

                            }
                        }
                    }
                }
            }
        }

        return $originalLoginUrl;
    }
}