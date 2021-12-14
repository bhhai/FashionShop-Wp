<?php
defined('ABSPATH') || die();
/** @var $this NextendSocialProviderAdmin */

$provider = $this->getProvider();
?>
<ol>
    <li><?php printf(__('Navigate to <b>%s</b>', 'nextend-facebook-connect'), '<a href="https://developer.twitter.com/en/portal/projects-and-apps" target="_blank">https://developer.twitter.com/en/portal/projects-and-apps</a>'); ?></li>
    <li><?php printf(__('Log in with your %s credentials if you are not logged in', 'nextend-facebook-connect'), 'Twitter'); ?></li>
    <li><?php _e('Find your App and click on the <b>App settings</b> icon. (The one that looks like a gear.)', 'nextend-facebook-connect'); ?></li>
    <li><?php _e('Click on the <b>Edit</b> button at <b>Authentication settings</b>.', 'nextend-facebook-connect'); ?></li>
    <li><?php
        $loginUrls = $provider->getAllRedirectUrisForAppCreation();
        printf(__('Add the following URL to the "<b>%1$s</b>" field:', 'nextend-facebook-connect'), 'Callback URLs');
        echo "<ul>";
        foreach ($loginUrls as $loginUrl) {
            echo "<li><strong>" . $loginUrl . "</strong></li>";
        }
        echo "</ul>";
        ?>
    </li>
    <li><?php printf(__('Make sure the "<b>%1$s</b>" field contains the following URL: <b>%2$s</b>', 'nextend-facebook-connect'), 'Website URL', site_url()); ?></li>
    <li><?php _e('Click on "<b>Save</b>"', 'nextend-facebook-connect'); ?></li>
</ol>