<?php
defined('ABSPATH') || die();
/** @var $this NextendSocialProviderAdmin */

$lastUpdated = '2021-09-09';

$provider = $this->getProvider();
?>
<div class="nsl-admin-sub-content">
    <?php if (substr($provider->getLoginUrl(), 0, 8) !== 'https://'): ?>
        <div class="error">
            <p><?php printf(__('%1$s allows HTTPS OAuth Redirects only. You must move your site to HTTPS in order to allow login with %1$s.', 'nextend-facebook-connect'), 'Facebook'); ?></p>
            <p>
                <a href="https://nextendweb.com/nextend-social-login-docs/facebook-api-changes/#enforce-https" target="_blank"><?php _e('How to get SSL for my WordPress site?', 'nextend-facebook-connect'); ?></a>
            </p>
        </div>
    <?php else: ?>
        <div class="nsl-admin-getting-started">
            <h2 class="title"><?php _e('Getting Started', 'nextend-facebook-connect'); ?></h2>

            <p><?php printf(__('To allow your visitors to log in with their %1$s account, first you must create a %1$s App. The following guide will help you through the %1$s App creation process. After you have created your %1$s App, head over to "Settings" and configure the given "%2$s" and "%3$s" according to your %1$s App.', 'nextend-facebook-connect'), "Facebook", "App ID", "App secret"); ?></p>

            <p><?php do_action('nsl_getting_started_warnings', $provider, $lastUpdated); ?></p>

            <h2 class="title"><?php printf(_x('Create %s', 'App creation', 'nextend-facebook-connect'), 'Facebook App'); ?></h2>

            <ol>
                <li><?php printf(__('Navigate to %s', 'nextend-facebook-connect'), '<a href="https://developers.facebook.com/apps/" target="_blank">https://developers.facebook.com/apps/</a>'); ?></li>
                <li><?php printf(__('Log in with your %s credentials if you are not logged in.', 'nextend-facebook-connect'), 'Facebook'); ?></li>
                <li><?php _e('Click on the "<b>Create App</b>" button and in the Popup choose the "<b>Consumer</b>" App type!', 'nextend-facebook-connect'); ?></li>
                <li><?php _e('If you see the message "<b>Become a Facebook Developer</b>", then you need to click on the green "<b>Register Now</b>" button, fill the form then finally verify your account.', 'nextend-facebook-connect'); ?></li>
                <li><?php printf(__('Fill "<b>App Display Name</b>", "<b>App Contact Email</b>". The specified "App Display Name" will appear on your %s!', 'nextend-facebook-connect'), '<a href="https://developers.facebook.com/docs/facebook-login/permissions/overview/" target="_blank">Consent Screen</a>'); ?></li>
                <li><?php _e('<b>Optional</b>: choose a "<b>Business Manager Account</b>" in the popup, if you have any.', 'nextend-facebook-connect'); ?></li>
                <li><?php _e('Click the "<b>Create App</b>" button and complete the Security Check.', 'nextend-facebook-connect'); ?></li>
                <li><?php printf(__('Find "<b>%1$s</b>" and click "<b>Set Up</b>".', 'nextend-facebook-connect'), 'Facebook Login', 'Settings') ?></li>
                <li><?php printf(__('Select "<b>Web</b>" and enter the following URL to the "<b>Site URL</b>" field: <b>%s</b>', 'nextend-facebook-connect'), site_url()); ?></li>
                <li><?php _e('Press “<b>Save</b>”.', 'nextend-facebook-connect'); ?></li>
                <li><?php printf(__('Click on the “<b>%1$s</b>” option what you find on the left side, under “<b>%2$s</b> - <b>%3$s</b>”', 'nextend-facebook-connect'), 'Settings', 'Products', 'Facebook Login') ?></li>
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
                <li><?php _e('Click on “<b>Save Changes</b>”.', 'nextend-facebook-connect'); ?></li>
                <li><?php printf(__('On the top left side, click on the “<b>%1$s</b>” menu point, then click “<b>%2$s</b>”.', 'nextend-facebook-connect'), 'Settings', 'Basic') ?></li>
                <li><?php printf(__('Enter your domain name to the "<b>App Domains</b>" field, probably: <b>%s</b>', 'nextend-facebook-connect'), str_replace('www.', '', $_SERVER['HTTP_HOST'])); ?></li>
                <li><?php _e('Fill up the "<b>Privacy Policy URL</b>" field. Provide a publicly available and easily accessible privacy policy that explains what data you are collecting and how you will use that data.', 'nextend-facebook-connect'); ?></li>
                <li><?php _e('At "<b>User Data Deletion</b>", choose the "<b>Data Deletion Instructions URL</b>" option, and enter the <i>URL of your page</i>* with the instructions on how users can delete their accounts on your site.', 'nextend-facebook-connect'); ?>
                    <ul>
                        <li><?php _e('To comply with GDPR, you should already offer possibility to delete accounts on your site, either by the user or by the admin:', 'nextend-facebook-connect'); ?></li>
                        <li>
                            <ul>
                                <li><?php _e('<u>If each user has an option to delete the account</u>: the URL should point to a guide showing the way users can delete their accounts.', 'nextend-facebook-connect'); ?></li>
                                <li><?php _e('<u>If the accounts are deleted by an admin</u>: then you should have a section - usually in the Privacy Policy - with the contact details, where users can send their account erasure requests. In this case the URL should point to this section of the document.', 'nextend-facebook-connect'); ?></li>
                            </ul>
                        </li>
                    </ul>
                </li>
                <li><?php _e('Select a “<b>Category</b>”, an “<b>App Icon</b>” and pick the “<b>App Purpose</b>” option that describes your App the best, then press "<b>Save Changes</b>".', 'nextend-facebook-connect'); ?></li>
                <li><?php _e('Your application is currently private, which means that only you can log in with it. In the top bar switch the "<b>App Mode</b>" from "<b>Development</b>" to "<b>Live</b>".', 'nextend-facebook-connect'); ?></li>
                <li><?php printf(__('By default, your application only has Standard access for the permissions, which is not enough for %1$s.<br>On the left side, click on <strong>%2$s</strong> then click <strong>%3$s</strong>. In the table you will find the "<strong>%4$s</strong>" and "<strong>%5$s</strong>" permissions and you should click on the <strong>%6$s</strong> buttons next to them. ', 'nextend-facebook-connect'), 'Facebook Login', 'App Review', 'Permissions and Features', 'public_profile', 'email', 'Get Advanced Access'); ?></li>
                <li><?php printf(__('On the top left side, click on the “<b>%1$s</b>” menu point, then click “<b>%2$s</b>”.', 'nextend-facebook-connect'), 'Settings', 'Basic') ?></li>
                <li><?php printf(__('At the top of the page you can find your "<b>%1$s</b>" and you can see your "<b>%2$s</b>" if you click on the "Show" button. These will be needed in plugin’s settings.', 'nextend-facebook-connect'), 'App ID', 'App secret'); ?></li>
            </ol>

            <p><?php printf(__('<b>WARNING:</b> <u>Don\'t replace your Facebook App with another!</u> Since WordPress users with linked Facebook accounts can only login using the %1$s App, that was originally used at the time, when the WordPress account was linked with a %1$s Account.<br>
If you would like to know the reason of this, or you really need to replace the Facebook App, then please check our %2$sdocumentation%3$s.', 'nextend-facebook-connect'), 'Facebook', '<a href="https://nextendweb.com/nextend-social-login-docs/provider-facebook/#app_scoped_user_id" target="_blank">', '</a>'); ?></p>

            <br>
            <h2 class="title"><?php _e('Maintaining the Facebook App:', 'nextend-facebook-connect'); ?></h2>
            <p><?php printf(__('<strong><u>Facebook Data Use Checkup:</u></strong> To protecting people\'s privacy, Facebook might requests you to fill some forms, so they can ensure that your API access and data use comply with the Facebook policies.
If Facebook displays the "%1$s" modal for your App, then in our %2$sdocumentation%3$s you can find more information about the permissions that we need.', 'nextend-facebook-connect'), 'Data Use Checkup', '<a href="https://nextendweb.com/nextend-social-login-docs/provider-facebook/#data_use_checkup" target="_blank">', '</a>'); ?></p>

            <a href="<?php echo $this->getUrl('settings'); ?>"
               class="button button-primary"><?php printf(__('I am done setting up my %s', 'nextend-facebook-connect'), 'Facebook App'); ?></a>
        </div>

        <br>
        <div class="nsl-admin-embed-youtube">
            <div></div>
            <iframe src="https://www.youtube.com/embed/giHaGhjuh2A?rel=0" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
        </div>
    <?php endif; ?>
</div>