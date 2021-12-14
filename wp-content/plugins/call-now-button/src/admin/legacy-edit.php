<?php
require_once dirname( __FILE__ ) . '/partials/admin-functions.php';
require_once dirname( __FILE__ ) . '/partials/admin-header.php';
require_once dirname( __FILE__ ) . '/partials/admin-footer.php';

function cnb_add_header_legacy_edit() {
    echo CNB_NAME . ' <span class="cnb-version">v' . CNB_VERSION . '</span>';
}

function cnb_create_tab_url_legacy($tab) {
    $url = admin_url('admin.php');
    $tab_link =
        add_query_arg(
            array(
                'page' => 'call-now-button',
                'action' => 'edit',
                'tab' => $tab),
            $url );
    return esc_url( $tab_link );
}

function cnb_admin_page_leagcy_edit_render_tracking() {
    global $cnb_options;
    ?>
    <tr>
        <th scope="row">Click tracking <a href="<?php echo CNB_SUPPORT; ?>click-tracking/<?php cnb_utm_params("question-mark", "click-tracking"); ?>" target="_blank" class="cnb-nounderscore">
                <span class="dashicons dashicons-editor-help"></span>
            </a></th>
        <td>
            <div class="cnb-radio-item">
                <input id="tracking3" type="radio" name="cnb[tracking]" value="0" <?php checked('0', $cnb_options['tracking']); ?> />
                <label for="tracking3">Disabled</label>
            </div>
            <div class="cnb-radio-item">
                <input id="tracking4" type="radio" name="cnb[tracking]" value="3" <?php checked('3', $cnb_options['tracking']); ?> />
                <label for="tracking4">Latest Google Analytics (gtag.js)</label>
            </div>
            <div class="cnb-radio-item">
                <input id="tracking1" type="radio" name="cnb[tracking]" value="2" <?php checked('2', $cnb_options['tracking']); ?> />
                <label for="tracking1">Google Universal Analytics (analytics.js)</label>
            </div>
            <div class="cnb-radio-item">
                <input id="tracking2" type="radio" name="cnb[tracking]" value="1" <?php checked('1', $cnb_options['tracking']); ?> />
                <label for="tracking2">Classic Google Analytics (ga.js)</label>
            </div>
            <p class="description">Using Google Tag Manager? Set up click tracking in GTM. <a href="<?php echo CNB_SUPPORT; ?>click-tracking/google-tag-manager-event-tracking/<?php cnb_utm_params("description_link", "google-tag-manager-event-tracking"); ?>" target="_blank">Learn how to do this...</a></p>
        </td>
    </tr>
<?php
}

function cnb_admin_page_leagcy_edit_render_conversions() {
    global $cnb_options;
    ?>
    <tr>
        <th scope="row">Google Ads <a href="<?php echo CNB_SUPPORT; ?>google-ads/<?php cnb_utm_params("question-mark", "google-ads"); ?>" target="_blank" class="cnb-nounderscore">
                <span class="dashicons dashicons-editor-help"></span>
            </a></th>
        <td class="conversions">
            <div class="cnb-radio-item">
                <input id="cnb_conversions_0" name="cnb[conversions]" type="radio" value="0" <?php checked('0', $cnb_options['conversions']); ?> /> <label for="cnb_conversions_0">Off </label>
            </div>
            <div class="cnb-radio-item">
                <input id="cnb_conversions_1"  name="cnb[conversions]" type="radio" value="1" <?php checked('1', $cnb_options['conversions']); ?> /> <label for="cnb_conversions_1">Conversion Tracking using Google's global site tag </label>
            </div>
            <div class="cnb-radio-item">
                <input id="cnb_conversions_2"  name="cnb[conversions]" type="radio" value="2" <?php checked('2', $cnb_options['conversions']); ?> /> <label for="cnb_conversions_2">Conversion Tracking using JavaScript</label>
            </div>
            <p class="description">Select this option if you want to track clicks on the button as Google Ads conversions. This option requires the Event snippet to be present on the page. <a href="<?php echo CNB_SUPPORT; ?>google-ads/<?php cnb_utm_params("question-mark", "google-ads"); ?>" target="_blank">Learn more...</a></p>
        </td>
    </tr>
    <?php
}

function cnb_admin_page_leagcy_edit_render_zoom() {
    global $cnb_options;
    ?>
    <tr class="zoom">
        <th scope="row">Button size <span id="cnb_slider_value"></span></th>
        <td>
            <label class="cnb_slider_value">Smaller&nbsp;&laquo;&nbsp;</label>
            <input type="range" min="0.7" max="1.3" name="cnb[zoom]" value="<?php esc_attr_e($cnb_options['zoom']) ?>" class="slider" id="cnb_slider" step="0.1">
            <label class="cnb_slider_value">&nbsp;&raquo;&nbsp;Bigger</label>
        </td>
    </tr>
    <?php
}

function cnb_admin_page_leagcy_edit_render_zindex() {
    global $cnb_options;
    ?>
    <tr class="z-index">
        <th scope="row">Order (<span id="cnb_order_value"></span>) <a href="https://callnowbutton.com/set-order/" target="_blank" class="cnb-nounderscore">
                <span class="dashicons dashicons-editor-help"></span>
            </a></th>
        <td>
            <label class="cnb_slider_value">Backwards&nbsp;&laquo;&nbsp;</label>
            <input type="range" min="1" max="10" name="cnb[z-index]" value="<?php esc_attr_e($cnb_options['z-index']) ?>" class="slider2" id="cnb_order_slider" step="1">
            <label class="cnb_slider_value">&nbsp;&raquo;&nbsp;Front</label>
            <p class="description">The default (and recommended) value is all the way to the front so the button sits on top of everything else. In case you have a specific usecase where you want something else to sit in front of the Call Now Button (e.g. a chat window or a cookie notice) you can move this backwards one step at a time to adapt it to your situation.</p>
        </td>
    </tr>

    <?php
}

function cnb_admin_page_legacy_edit_render() {
    global $cnb_options;

    add_action('cnb_header_name', 'cnb_add_header_legacy_edit');

    do_action('cnb_header');
?>

    <h2 class="nav-tab-wrapper">
        <a href="<?php echo cnb_create_tab_url_legacy('basic_options') ?>"
           class="nav-tab <?php echo cnb_is_active_tab('basic_options') ?>">Basics</a>
        <a href="<?php echo cnb_create_tab_url_legacy('extra_options') ?>"
           class="nav-tab <?php echo cnb_is_active_tab('extra_options') ?>">Presentation</a>
    </h2>
    <form method="post" action="<?php echo esc_url( admin_url('options.php') ); ?>" class="cnb-container">
        <?php settings_fields('cnb_options'); ?>
        <table class="form-table <?php echo cnb_is_active_tab('basic_options') ?>">
            <tr>
                <th colspan="2"></th>
            </tr>
            <tr>
                <th scope="row">Button status</th>
                <td>
                    <input type="hidden" name="cnb[active]" value="0" />
                    <input id="cnb-active" type="checkbox" name="cnb[active]" value="1" <?php checked('1', $cnb_options['active']); ?>>
                    <label for="cnb-active">Enable</label>
                </td>
            </tr>
            <tr>
                <th scope="row">Phone number <a href="<?php echo CNB_SUPPORT; ?>phone-number/<?php cnb_utm_params("question-mark", "phone-number"); ?>" target="_blank" class="cnb-nounderscore">
                        <span class="dashicons dashicons-editor-help"></span>
                    </a></th>
                <td><input type="text" name="cnb[number]" value="<?php esc_attr_e($cnb_options['number']) ?>" /></td>
            </tr>
            <tr class="button-text">
                <th scope="row">Button text <small style="font-weight: 400">(optional)</small> <a href="<?php echo CNB_SUPPORT; ?>using-text-buttons/<?php cnb_utm_params("question-mark", "using-text-buttons"); ?>" target="_blank" class="cnb-nounderscore">
                        <span class="dashicons dashicons-editor-help"></span>
                    </a></th>
                <td>
                    <input id="buttonTextField" type="text" name="cnb[text]" value="<?php esc_attr_e($cnb_options['text']) ?>" maxlength="30"  />
                    <p class="description">Leave this field empty to only show an icon.</p>
                </td>
            </tr>
        </table>

        <table class="form-table <?php echo cnb_is_active_tab('extra_options') ?>">
          <tr>
              <th colspan="2"></th>
          </tr>

            <tr>
                <th scope="row">Button color</th>
                <td><input name="cnb[color]" type="text" value="<?php esc_attr_e($cnb_options['color']) ?>" class="cnb-color-field" data-default-color="#009900" /></td>
            </tr>
            <tr>
                <th scope="row">Icon color</th>
                <td><input name="cnb[iconcolor]" type="text" value="<?php esc_attr_e($cnb_options['iconcolor']) ?>" class="cnb-iconcolor-field" data-default-color="#ffffff" /></td>
            </tr>
            <tr>
                <th scope="row">Position <a href="<?php echo CNB_SUPPORT; ?>button-position/<?php cnb_utm_params("question-mark", "button-position"); ?>" target="_blank" class="cnb-nounderscore">
                        <span class="dashicons dashicons-editor-help"></span>
                    </a></th>
                <td class="appearance">
                    <div class="appearance-options">
                        <div class="cnb-radio-item">
                            <input type="radio" id="appearance1" name="cnb[appearance]" value="right" <?php checked('right', $cnb_options['appearance']); ?>>
                            <label title="right" for="appearance1">Right corner</label>
                        </div>
                        <div class="cnb-radio-item">
                            <input type="radio" id="appearance2" name="cnb[appearance]" value="left" <?php checked('left', $cnb_options['appearance']); ?>>
                            <label title="left" for="appearance2">Left corner</label>
                        </div>
                        <div class="cnb-radio-item">
                            <input type="radio" id="appearance3" name="cnb[appearance]" value="middle" <?php checked('middle', $cnb_options['appearance']); ?>>
                            <label title="middle" for="appearance3">Center bottom</label>
                        </div>
                        <div class="cnb-radio-item">
                            <input type="radio" id="appearance4" name="cnb[appearance]" value="full" <?php checked('full', $cnb_options['appearance']); ?>>
                            <label title="full" for="appearance4">Full bottom</label>
                        </div>

                        <!-- Extra placement options -->
                        <br class="cnb-extra-placement">
                        <div class="cnb-radio-item cnb-extra-placement <?php echo $cnb_options['appearance'] == "mright" ? "cnb-extra-active" : ""; ?>">
                            <input type="radio" id="appearance5" name="cnb[appearance]" value="mright" <?php checked('mright', $cnb_options['appearance']); ?>>
                            <label title="mright" for="appearance5">Middle right</label>
                        </div>
                        <div class="cnb-radio-item cnb-extra-placement <?php echo $cnb_options['appearance'] == "mleft" ? "cnb-extra-active" : ""; ?>">
                            <input type="radio" id="appearance6" name="cnb[appearance]" value="mleft" <?php checked('mleft', $cnb_options['appearance']); ?>>
                            <label title="mleft" for="appearance6">Middle left </label>
                        </div>
                        <br class="cnb-extra-placement">
                        <div class="cnb-radio-item cnb-extra-placement <?php echo $cnb_options['appearance'] == "tright" ? "cnb-extra-active" : ""; ?>">
                            <input type="radio" id="appearance7" name="cnb[appearance]" value="tright" <?php checked('tright', $cnb_options['appearance']); ?>>
                            <label title="tright" for="appearance7">Top right corner</label>
                        </div>
                        <div class="cnb-radio-item cnb-extra-placement <?php echo $cnb_options['appearance'] == "tleft" ? "cnb-extra-active" : ""; ?>">
                            <input type="radio" id="appearance8" name="cnb[appearance]" value="tleft" <?php checked('tleft', $cnb_options['appearance']); ?>>
                            <label title="tleft" for="appearance8">Top left corner</label>
                        </div>
                        <div class="cnb-radio-item cnb-extra-placement <?php echo $cnb_options['appearance'] == "tmiddle" ? "cnb-extra-active" : ""; ?>">
                            <input type="radio" id="appearance9" name="cnb[appearance]" value="tmiddle" <?php checked('tmiddle', $cnb_options['appearance']); ?>>
                            <label title="tmiddle" for="appearance9">Center top</label>
                        </div>
                        <div class="cnb-radio-item cnb-extra-placement <?php echo $cnb_options['appearance'] == "tfull" ? "cnb-extra-active" : ""; ?>">
                            <input type="radio" id="appearance10" name="cnb[appearance]" value="tfull" <?php checked('tfull', $cnb_options['appearance']); ?>>
                            <label title="tfull" for="appearance10">Full top</label>
                        </div>
                        <a href="#" id="cnb-more-placements">More placement options...</a>
                        <!-- END extra placement options -->
                    </div>

                    <div id="hideIconTR">
                        <br>
                        <input type="hidden" name="cnb[hideIcon]" value="0" />
                        <input id="hide_icon" type="checkbox" name="cnb[hideIcon]" value="1" <?php checked('1', $cnb_options['hideIcon']); ?>>
                        <label title="right" for="hide_icon">Remove icon</label>
                    </div>
                </td>
            </tr>
            <tr class="appearance">
                <th scope="row">Limit appearance <a href="<?php echo CNB_SUPPORT; ?>limit-appearance/<?php cnb_utm_params("question-mark", "limit-appearance"); ?>" target="_blank" class="cnb-nounderscore">
                        <span class="dashicons dashicons-editor-help"></span>
                    </a></th>
                <td>
                    <input type="text" name="cnb[show]" value="<?php esc_attr_e($cnb_options['show']) ?>" placeholder="E.g. 14, 345" />
                    <p class="description">Enter IDs of the posts &amp; pages, separated by commas (leave blank for all). <a href="<?php echo CNB_SUPPORT; ?>limit-appearance/<?php cnb_utm_params("question-mark", "limit-appearance"); ?>">Learn more...</a></p>
                    <div class="cnb-radio-item">
                        <input id="limit1" type="radio" name="cnb[limit]" value="include" <?php checked('include', $cnb_options['limit']);?> />
                        <label for="limit1">Limit to these posts and pages.</label>
                    </div>
                    <div class="cnb-radio-item">
                        <input id="limit2" type="radio" name="cnb[limit]" value="exclude" <?php checked('exclude', $cnb_options['limit']);?> />
                        <label for="limit2">Exclude these posts and pages.</label>
                    </div>
                    <br>
                    <div>
                        <input type="hidden" name="cnb[frontpage]" value="0" />
                        <input id="frontpage" type="checkbox" name="cnb[frontpage]" value="1" <?php checked('1', $cnb_options['frontpage']); ?>>
                        <label title="right" for="frontpage">Hide button on front page</label>
                    </div>
                </td>
            </tr>
        </table>
        <table class="form-table <?php echo cnb_is_active_tab('advanced_options') ?>">
            <tr>
                <th colspan="2"><h2>Advanced Settings</h2></th>
            </tr>
            <?php
            cnb_admin_page_leagcy_edit_render_tracking();
            cnb_admin_page_leagcy_edit_render_conversions();
            cnb_admin_page_leagcy_edit_render_zoom();
            cnb_admin_page_leagcy_edit_render_zindex();

            if($cnb_options['classic'] == 1) { ?>
                <tr class="classic">
                    <th scope="row">Classic button <a href="https://callnowbutton.com/new-button-design/<?php cnb_utm_params("question-mark", "new-button-design"); ?>" target="_blank" class="cnb-nounderscore">
                            <span class="dashicons dashicons-editor-help"></span>
                        </a></th>
                    <td>
                        <input type="hidden" name="cnb[classic]" value="0" />
                        <input id="classic" name="cnb[classic]" type="checkbox" value="1" <?php checked('1', $cnb_options['classic']); ?> /> <label title="Enable" for="classic">Active</label>
                    </td>
                </tr>
            <?php } ?>
        </table>

        <input type="hidden" name="cnb[version]" value="<?php echo CNB_VERSION; ?>" />
        <p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" /></p>
    </form>

    <?php
    do_action('cnb_footer');
}
