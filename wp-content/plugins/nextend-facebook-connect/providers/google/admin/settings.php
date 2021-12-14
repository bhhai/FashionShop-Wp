<?php
defined('ABSPATH') || die();
/** @var $this NextendSocialProviderAdmin */

$provider = $this->getProvider();

$settings = $provider->settings;
?>

<div class="nsl-admin-sub-content">

    <?php
    $this->renderSettingsHeader();
    ?>

    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" novalidate="novalidate">

        <?php wp_nonce_field('nextend-social-login'); ?>
        <input type="hidden" name="action" value="nextend-social-login"/>
        <input type="hidden" name="view" value="provider-<?php echo $provider->getId(); ?>"/>
        <input type="hidden" name="subview" value="settings"/>
        <input type="hidden" name="settings_saved" value="1"/>
        <input type="hidden" name="tested" id="tested" value="<?php echo esc_attr($settings->get('tested')); ?>"/>
        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row"><label for="client_id"><?php _e('Client ID', 'nextend-facebook-connect'); ?>
                        - <em>(<?php _e('Required', 'nextend-facebook-connect'); ?>)</em></label>
                </th>
                <td>
                    <input name="client_id" type="text" id="client_id"
                           value="<?php echo esc_attr($settings->get('client_id')); ?>" class="regular-text"
                           style="width:40em;">
                    <p class="description"
                       id="tagline-client_id"><?php printf(__('If you are not sure what is your %1$s, please head over to <a href="%2$s">Getting Started</a>', 'nextend-facebook-connect'), 'Client ID', $this->getUrl()); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label
                            for="client_secret"><?php _e('Client Secret', 'nextend-facebook-connect'); ?>
                        - <em>(<?php _e('Required', 'nextend-facebook-connect'); ?>)</em></label></th>
                <td><input name="client_secret" type="text" id="client_secret"
                           value="<?php echo esc_attr($settings->get('client_secret')); ?>" class="regular-text">
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('Select account on each login', 'nextend-facebook-connect'); ?></th>
                <td>
                    <label for="select_account">
                        <input type="hidden" name="select_account" value="0">
                        <input type="checkbox" name="select_account" id="select_account"
                               value="1" <?php if ($settings->get('select_account') == 1) : ?> checked="checked" <?php endif; ?>>
                        <?php _e('Enabled', 'nextend-facebook-connect'); ?>
                    </label>
                    <p class="description"
                       id="tagline-select_account"><?php _e('Disable, when you don\'t want to see the account select prompt on each login.', 'nextend-facebook-connect'); ?></p>
                </td>
            </tr>
            </tbody>
        </table>
        <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary"
                                 value="<?php _e('Save Changes'); ?>"></p>

        <?php
        $this->renderOtherSettings();

        $this->renderProSettings();
        ?>
    </form>
</div>