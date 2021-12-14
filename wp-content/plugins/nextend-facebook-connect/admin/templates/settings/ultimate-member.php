<?php
defined('ABSPATH') || die();

$isPRO = NextendSocialLoginAdmin::isPro();

$attr = '';
if (!$isPRO) {
    $attr = ' disabled ';
}

$settings = NextendSocialLogin::$settings;

NextendSocialLoginAdmin::showProBox();
?>
<table class="form-table">
    <tbody>
    <tr>
        <th scope="row"><?php _e('Login form', 'nextend-facebook-connect'); ?></th>
        <td>
            <fieldset>
                <label><input type="radio" name="ultimatemember_login"
                              value="" <?php if ($settings->get('ultimatemember_login') == '') : ?> checked="checked" <?php endif; ?><?php echo $attr; ?>>
                    <span><?php _e('No Connect button in Login form', 'nextend-facebook-connect'); ?></span></label><br>
                <label><input type="radio" name="ultimatemember_login"
                              value="after" <?php if ($settings->get('ultimatemember_login') == 'after') : ?> checked="checked" <?php endif; ?><?php echo $attr; ?>>
                    <span><?php _e('Connect button on', 'nextend-facebook-connect'); ?></span>
                    <code><?php _e('Action:'); ?>
                        um_after_login_fields</code></label>
            </fieldset>
        </td>
    </tr>
    <tr>
        <th scope="row"><?php _e('Login form button style', 'nextend-facebook-connect'); ?></th>
        <td>
            <fieldset>
                <label>
                    <input type="radio" name="ultimatemember_login_form_button_style"
                           value="default" <?php if ($settings->get('ultimatemember_login_form_button_style') == 'default') : ?> checked="checked" <?php endif; ?><?php echo $attr; ?>>
                    <span><?php _e('Default', 'nextend-facebook-connect'); ?></span><br/>
                    <img src="<?php echo plugins_url('images/buttons/default.png', NSL_ADMIN_PATH) ?>"/>
                </label>
                <label>
                    <input type="radio" name="ultimatemember_login_form_button_style"
                           value="fullwidth" <?php if ($settings->get('ultimatemember_login_form_button_style') == 'fullwidth') : ?> checked="checked" <?php endif; ?><?php echo $attr; ?>>
                    <span><?php _e('Fullwidth', 'nextend-facebook-connect'); ?></span><br/>
                    <img src="<?php echo plugins_url('images/buttons/fullwidth.png', NSL_ADMIN_PATH) ?>"/>
                </label>
                <label>
                    <input type="radio" name="ultimatemember_login_form_button_style"
                           value="icon" <?php if ($settings->get('ultimatemember_login_form_button_style') == 'icon') : ?> checked="checked" <?php endif; ?><?php echo $attr; ?>>
                    <span><?php _e('Icon', 'nextend-facebook-connect'); ?></span><br/>
                    <img src="<?php echo plugins_url('images/buttons/icon.png', NSL_ADMIN_PATH) ?>"/>
                </label><br>
            </fieldset>
        </td>
    </tr>
    <tr>
        <th scope="row"><?php _e('Login layout', 'nextend-facebook-connect'); ?></th>
        <td>
            <fieldset>
                <label>
                    <input type="radio" name="ultimatemember_login_form_layout"
                           value="below" <?php if ($settings->get('ultimatemember_login_form_layout') == 'below') : ?> checked="checked" <?php endif; ?><?php echo $attr; ?>>
                    <span><?php _e('Below', 'nextend-facebook-connect'); ?></span><br/>
                    <img src="<?php echo plugins_url('images/layouts/below.png', NSL_ADMIN_PATH) ?>"/>
                </label>
                <label>
                    <input type="radio" name="ultimatemember_login_form_layout"
                           value="below-separator" <?php if ($settings->get('ultimatemember_login_form_layout') == 'below-separator') : ?> checked="checked" <?php endif; ?><?php echo $attr; ?>>
                    <span><?php _e('Below with separator', 'nextend-facebook-connect'); ?></span><br/>
                    <img src="<?php echo plugins_url('images/layouts/below-separator.png', NSL_ADMIN_PATH) ?>"/>
                </label>
                <label>
                    <input type="radio" name="ultimatemember_login_form_layout"
                           value="above" <?php if ($settings->get('ultimatemember_login_form_layout') == 'above') : ?> checked="checked" <?php endif; ?><?php echo $attr; ?>>
                    <span><?php _e('Above', 'nextend-facebook-connect'); ?></span><br/>
                    <img src="<?php echo plugins_url('images/layouts/above.png', NSL_ADMIN_PATH) ?>"/>
                </label>
                <label>
                    <input type="radio" name="ultimatemember_login_form_layout"
                           value="above-separator" <?php if ($settings->get('ultimatemember_login_form_layout') == 'above-separator') : ?> checked="checked" <?php endif; ?><?php echo $attr; ?>>
                    <span><?php _e('Above with separator', 'nextend-facebook-connect'); ?></span><br/>
                    <img src="<?php echo plugins_url('images/layouts/above-separator.png', NSL_ADMIN_PATH) ?>"/>
                </label><br>
            </fieldset>
        </td>
    </tr>

    <tr>
        <th scope="row"><?php _e('Register form', 'nextend-facebook-connect'); ?></th>
        <td>
            <fieldset>
                <label><input type="radio" name="ultimatemember_register"
                              value="" <?php if ($settings->get('ultimatemember_register') == '') : ?> checked="checked" <?php endif; ?><?php echo $attr; ?>>
                    <span><?php _e('No Connect button in Register form', 'nextend-facebook-connect'); ?></span></label><br>
                <label><input type="radio" name="ultimatemember_register"
                              value="after" <?php if ($settings->get('ultimatemember_register') == 'after') : ?> checked="checked" <?php endif; ?><?php echo $attr; ?>>
                    <span><?php _e('Connect button on', 'nextend-facebook-connect'); ?></span>
                    <code><?php _e('Action:'); ?>
                        um_after_register_fields</code></label>
            </fieldset>
        </td>
    </tr>
    <tr>
        <th scope="row"><?php _e('Register form button style', 'nextend-facebook-connect'); ?></th>
        <td>
            <fieldset>
                <label>
                    <input type="radio" name="ultimatemember_register_form_button_style"
                           value="default" <?php if ($settings->get('ultimatemember_register_form_button_style') == 'default') : ?> checked="checked" <?php endif; ?><?php echo $attr; ?>>
                    <span><?php _e('Default', 'nextend-facebook-connect'); ?></span><br/>
                    <img src="<?php echo plugins_url('images/buttons/default.png', NSL_ADMIN_PATH) ?>"/>
                </label>
                <label>
                    <input type="radio" name="ultimatemember_register_form_button_style"
                           value="fullwidth" <?php if ($settings->get('ultimatemember_register_form_button_style') == 'fullwidth') : ?> checked="checked" <?php endif; ?><?php echo $attr; ?>>
                    <span><?php _e('Fullwidth', 'nextend-facebook-connect'); ?></span><br/>
                    <img src="<?php echo plugins_url('images/buttons/fullwidth.png', NSL_ADMIN_PATH) ?>"/>
                </label>
                <label>
                    <input type="radio" name="ultimatemember_register_form_button_style"
                           value="icon" <?php if ($settings->get('ultimatemember_register_form_button_style') == 'icon') : ?> checked="checked" <?php endif; ?><?php echo $attr; ?>>
                    <span><?php _e('Icon', 'nextend-facebook-connect'); ?></span><br/>
                    <img src="<?php echo plugins_url('images/buttons/icon.png', NSL_ADMIN_PATH) ?>"/>
                </label><br>
            </fieldset>
        </td>
    </tr>
    <tr>
        <th scope="row"><?php _e('Register layout', 'nextend-facebook-connect'); ?></th>
        <td>
            <fieldset>
                <label>
                    <input type="radio" name="ultimatemember_register_form_layout"
                           value="below" <?php if ($settings->get('ultimatemember_register_form_layout') == 'below') : ?> checked="checked" <?php endif; ?><?php echo $attr; ?>>
                    <span><?php _e('Below', 'nextend-facebook-connect'); ?></span><br/>
                    <img src="<?php echo plugins_url('images/layouts/below.png', NSL_ADMIN_PATH) ?>"/>
                </label>
                <label>
                    <input type="radio" name="ultimatemember_register_form_layout"
                           value="below-separator" <?php if ($settings->get('ultimatemember_register_form_layout') == 'below-separator') : ?> checked="checked" <?php endif; ?><?php echo $attr; ?>>
                    <span><?php _e('Below with separator', 'nextend-facebook-connect'); ?></span><br/>
                    <img src="<?php echo plugins_url('images/layouts/below-separator.png', NSL_ADMIN_PATH) ?>"/>
                </label>
                <label>
                    <input type="radio" name="ultimatemember_register_form_layout"
                           value="above" <?php if ($settings->get('ultimatemember_register_form_layout') == 'above') : ?> checked="checked" <?php endif; ?><?php echo $attr; ?>>
                    <span><?php _e('Above', 'nextend-facebook-connect'); ?></span><br/>
                    <img src="<?php echo plugins_url('images/layouts/above.png', NSL_ADMIN_PATH) ?>"/>
                </label>
                <label>
                    <input type="radio" name="ultimatemember_register_form_layout"
                           value="above-separator" <?php if ($settings->get('ultimatemember_register_form_layout') == 'above-separator') : ?> checked="checked" <?php endif; ?><?php echo $attr; ?>>
                    <span><?php _e('Above with separator', 'nextend-facebook-connect'); ?></span><br/>
                    <img src="<?php echo plugins_url('images/layouts/above-separator.png', NSL_ADMIN_PATH) ?>"/>
                </label><br>
            </fieldset>
        </td>
    </tr>

    <tr>
        <th scope="row"><?php _e('Account details', 'nextend-facebook-connect'); ?></th>
        <td>
            <fieldset>
                <label><input type="radio" name="ultimatemember_account_details"
                              value="" <?php if ($settings->get('ultimatemember_account_details') == '') : ?> checked="checked" <?php endif; ?><?php echo $attr; ?>>
                    <span><?php _e('No link buttons', 'nextend-facebook-connect'); ?></span></label><br>
                <label><input type="radio" name="ultimatemember_account_details"
                              value="after" <?php if ($settings->get('ultimatemember_account_details') == 'after') : ?> checked="checked" <?php endif; ?><?php echo $attr; ?>>
                    <span><?php _e('Link buttons after account details', 'nextend-facebook-connect'); ?></span>
                    <code><?php _e('Action:'); ?>
                        um_after_account_general_button</code></label><br>
            </fieldset>
        </td>
    </tr>

    <tr>
        <th scope="row"><?php _e('Button alignment', 'nextend-facebook-connect'); ?></th>
        <td>
            <fieldset>
                <label><input type="radio" name="ultimatemember_form_button_align"
                              value="left" <?php if ($settings->get('ultimatemember_form_button_align') == 'left') : ?> checked="checked" <?php endif; ?><?php echo $attr; ?>>
                    <span><?php _e('Left', 'nextend-facebook-connect'); ?></span></label><br>
                <label><input type="radio" name="ultimatemember_form_button_align"
                              value="center" <?php if ($settings->get('ultimatemember_form_button_align') == 'center') : ?> checked="checked" <?php endif; ?><?php echo $attr; ?>>
                    <span><?php _e('Center', 'nextend-facebook-connect'); ?></span></label><br>

                <label><input type="radio" name="ultimatemember_form_button_align"
                              value="right" <?php if ($settings->get('ultimatemember_form_button_align') == 'right') : ?> checked="checked" <?php endif; ?><?php echo $attr; ?>>
                    <span><?php _e('Right', 'nextend-facebook-connect'); ?></span></label><br>
            </fieldset>
        </td>
    </tr>
    </tbody>
</table>
<?php if ($isPRO): ?>
    <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary"
                             value="<?php _e('Save Changes'); ?>"></p>
<?php endif; ?>
