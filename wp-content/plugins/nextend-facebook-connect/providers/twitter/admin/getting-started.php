<?php
defined('ABSPATH') || die();
/** @var $this NextendSocialProviderAdmin */

$lastUpdated = '2021-10-27';

$provider = $this->getProvider();
?>

<div class="nsl-admin-sub-content">
    <div class="nsl-admin-getting-started">
        <h2 class="title"><?php _e('Getting Started', 'nextend-facebook-connect'); ?></h2>

        <p><?php printf(__('To allow your visitors to log in with their %1$s account, first you must create a %1$s App. The following guide will help you through the %1$s App creation process. After you have created your %1$s App, head over to "Settings" and configure the given "%2$s" and "%3$s" according to your %1$s App.', 'nextend-facebook-connect'), "Twitter", "Consumer Key", "Consumer Secret"); ?></p>

        <p><?php do_action('nsl_getting_started_warnings', $provider, $lastUpdated); ?></p>

        <h2 class="title"><?php printf(_x('Create %s', 'App creation', 'nextend-facebook-connect'), 'Twitter App'); ?></h2>

        <ol>
            <li><?php printf(__('Navigate to <b>%s</b>', 'nextend-facebook-connect'), '<a href="https://developer.twitter.com/en/portal/projects-and-apps" target="_blank">https://developer.twitter.com/en/portal/projects-and-apps</a>'); ?></li>
            <li><?php printf(__('Log in with your %s credentials if you are not logged in.', 'nextend-facebook-connect'), 'Twitter'); ?></li>
            <li><?php _e('If you don\'t have a developer account yet, please apply one by filling all the required details! This is required for the next steps!', 'nextend-facebook-connect'); ?></li>
            <li><?php printf(__('Once your developer account is complete, navigate back to <b>%s</b> if you aren\'t already there!', 'nextend-facebook-connect'), '<a href="https://developer.twitter.com/en/portal/projects-and-apps" target="_blank">https://developer.twitter.com/en/portal/projects-and-apps</a>'); ?>
            <li><?php printf(__('Click on "<b>%s</b>"!', 'nextend-facebook-connect'), '+ Create Project'); ?></li>
            <li><?php _e('Name your project, and go through the basic setup. You’ll need to select your use case, give a description and enter a name for the App as well.', 'nextend-facebook-connect'); ?></li>
            <li><?php printf(__('Click "<b>%s</b>"!', 'nextend-facebook-connect'), 'Next'); ?></li>
            <li><?php printf(__('You’ll find your API key and secret on this page. Copy and paste the "<b>%1$s</b>" and the "<b>%2$s</b>" to the corresponding fields at %3$s and press "<b>Save Changes</b>".', 'nextend-facebook-connect'), 'API key', 'API secret key', 'Nextend Social Login > Twitter > Settings'); ?></li>
            <li><?php printf(__('Go back to your Twitter project and on the left side, under the "<b>%s</b>" section click on the name of your App.', 'nextend-facebook-connect'), 'Projects and Apps'); ?></li>
            <li><?php printf(__('Scroll down and click on the "<b>%1$s</b>" button at "<b>%2$s</b>".', 'nextend-facebook-connect'), 'Edit', 'Authentication settings'); ?></li>
            <li><?php printf(__('Switch on the "<b>%s</b>" option.', 'nextend-facebook-connect'), 'Enable 3-legged OAuth'); ?></li>
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
            <li><?php printf(__('Enter your site\'s URL to the "<b>%1$s</b>" field: <b>%2$s</b>', 'nextend-facebook-connect'), 'Website URL', site_url()); ?></li>
            <li><?php printf(__('If you want to get the email address as well, then don’t forget to enable the "<b>%1$s</b>" option. In this case you also need to fill the "<b>%2$s</b>" and the "<b>%3$s</b>" fields with the corresponding URLs!', 'nextend-facebook-connect'), 'Request email address from users', 'Terms of service', 'Privacy policy'); ?></li>
            <li><?php printf(__('Click on "<b>%s</b>".', 'nextend-facebook-connect'), 'Save'); ?></li>
            <li><?php printf(__('Go back to %1$s and <b>Verify</b> your %2$s provider.', 'nextend-facebook-connect'), 'Nextend Social Login', 'Twitter'); ?></li>
        </ol>

        <a href="<?php echo $this->getUrl('settings'); ?>"
           class="button button-primary"><?php printf(__('I am done setting up my %s', 'nextend-facebook-connect'), 'Twitter App'); ?></a>
    </div>

    <br>
    <div class="nsl-admin-embed-youtube">
        <div></div>
        <iframe src="https://www.youtube.com/embed/5m4kD11Ai2w?rel=0" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
    </div>
</div>