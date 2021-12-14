<?php
defined('ABSPATH') || die();
/** @var $this NextendSocialProviderAdmin */

$provider = $this->getProvider();
?>
<ol>
    <li><?php printf(__('Navigate to <b>%s</b>', 'nextend-facebook-connect'), '<a href="https://developers.facebook.com/apps/" target="_blank">https://developers.facebook.com/apps/</a>'); ?></li>
    <li><?php printf(__('Log in with your %s credentials if you are not logged in', 'nextend-facebook-connect'), 'Facebook'); ?></li>
    <li><?php printf(__('Click on the App with App ID: <b>%s</b>', 'nextend-facebook-connect'), $provider->settings->get('appid')); ?></li>
    <li><?php _e('In the left sidebar, click on "<b>Facebook Login > Settings</b>"', 'nextend-facebook-connect'); ?></li>
    <li><?php
        $loginUrls = $provider->getAllRedirectUrisForAppCreation();
        printf(__('Add the following URL to the "<b>Valid OAuth redirect URIs</b>" field:', 'nextend-facebook-connect'));
        echo "<ul>";
        foreach ($loginUrls as $loginUrl) {
            echo "<li><strong>" . $loginUrl . "</strong></li>";
        }
        echo "</ul>";
        ?>
    </li>
    <li><?php _e('Click on "<b>Save Changes</b>"', 'nextend-facebook-connect'); ?></li>
</ol>