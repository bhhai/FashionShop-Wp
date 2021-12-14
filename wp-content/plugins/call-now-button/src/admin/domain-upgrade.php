<?php

require_once dirname( __FILE__ ) . '/api/CnbAppRemote.php';
require_once dirname( __FILE__ ) . '/api/CnbAppRemotePayment.php';
require_once dirname( __FILE__ ) . '/api/CnbAdminCloud.php';
require_once dirname( __FILE__ ) . '/partials/admin-functions.php';
require_once dirname( __FILE__ ) . '/partials/admin-header.php';
require_once dirname( __FILE__ ) . '/partials/admin-footer.php';
require_once dirname( __FILE__ ) . '/models/CnbDomain.class.php';

function cnb_add_header_domain_upgrade() {
    echo 'Upgrade the Call Now Button';
}

function cnb_upgrade_create_settings_url() {
    $url = admin_url('admin.php');
    $tab_link =
        add_query_arg(
            array(
                'page' => 'call-now-button-settings'),
            $url );
    return esc_url( $tab_link );
}

/**
 * @return CnbDomain
 */
function cnb_get_domain() {
    $domain_id = filter_input( INPUT_GET, 'id', FILTER_SANITIZE_STRING );
    $domain = new CnbDomain();
    if (strlen($domain_id) > 0 && $domain_id != 'new') {
        $domain = CnbAppRemote::cnb_remote_get_domain( $domain_id );
    }
    return $domain;
}

function cnb_print_domain_upgrade_notice_cache_flush() {
    $upgradeStatus = filter_input( INPUT_GET, 'upgrade', FILTER_SANITIZE_STRING );
    $checkoutSesionId = filter_input( INPUT_GET, 'checkout_session_id', FILTER_SANITIZE_STRING );
    if ($upgradeStatus || $checkoutSesionId) {
        // Increase the cache ID
        CnbAppRemote::cnb_incr_transient_base();
    }
}


/**
 * @param $domain CnbDomain
 *
 * @return CnbNotice
 */
function cnb_print_domain_upgrade_notice($domain) {
    $upgradeStatus = filter_input( INPUT_GET, 'upgrade', FILTER_SANITIZE_STRING );
    $checkoutSesionId = filter_input( INPUT_GET, 'checkout_session_id', FILTER_SANITIZE_STRING );
    if ($upgradeStatus === 'success?payment=success') {
        // Get checkout Session Details
        $session = CnbAppRemotePayment::cnb_remote_get_subscription_session( $checkoutSesionId );
        // This results in a subscription (via ->subscriptionId), get that for ->type
        $subscription = CnbAppRemotePayment::cnb_remote_get_subscription( $session->subscriptionId );

        return new CnbNotice('success', '<p>Your domain <strong>'.esc_html($domain->name).'</strong> has been successfully upgraded to <strong>'.esc_html($subscription->type).'</strong>!</p>');
    }
    return null;
}

function cnb_get_plan($plans, $name) {
    foreach ($plans as $plan) {
        if ($plan->nickname === $name) {
            return $plan;
        }
    }
    return null;
}

function cnb_admin_page_domain_upgrade_render() {

    $domain = cnb_get_domain();
    $notice = cnb_print_domain_upgrade_notice($domain);

    add_action('cnb_header_name', 'cnb_add_header_domain_upgrade');

    do_action('cnb_header');


    // If the type is missing, we assume FREE
    if (empty($domain->type)) {
        $domain->type = 'FREE';
    }

    // Stripe integration
    ?>
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        const stripe = Stripe("<?php echo esc_js( CnbAppRemotePayment::cnb_remote_get_stripe_key()->key) ?>");
    </script>

    <p>Your domain <?php esc_html_e($domain->name) ?> is currently on the <code><?php esc_html_e($domain->type) ?></code> cloud plan.</p>


    <?php if ($domain->type == 'PRO') {
        // Render notice if JUST upgraded and show general information about domain (instead of upgrade form)
        if ($notice) {
            CnbAdminNotices::get_instance()->renderNotice( $notice );
        }
        $portal_url = CnbAppRemote::cnb_remote_create_billing_portal();
        if (!empty($domain->expires)) { ?>
            <p class="description" id="domain_expires-description">
                Your subscription will
                <?php echo $domain->renew == 1 ? ' renew automatically ' : ' expire '; ?>
                on <?php echo date('F d, Y', strtotime(esc_html($domain->expires))); ?>.
            </p>
        <?php } ?>
        <p>You can change this on the <a href="<?php esc_html_e(cnb_upgrade_create_settings_url()) ?>">settings page</a>.</p>
        <p>If you have any questions about your subscriptions, you can see <a href="<?php esc_html_e($portal_url->url); ?>">your invoice dashboard</a> here or contact us via the <a href="https://help.callnowbutton.com/">support forums</a>.</p>

        <?php
        } else {
        // Render upgrade form
        $plans = CnbAppRemotePayment::cnb_remote_get_plans();
        ?>

    <form id="wp_domain_upgrade" method="post">
    <input type="hidden" name="cnb_domain_id" id="cnb_domain_id" value="<?php esc_attr_e($domain->id) ?>">

    <h2>Select a plan to remove the "Powered by Call Now Button" message from your buttons</h2>


                <h2 class="nav-tab-wrapper">
                  <a href="#" data-cnb-currency="eur" class="cnb-currency-select cnb-currency-eur nav-tab nav-tab-active">Euro (&euro;)</a>
                  <a href="#" data-cnb-currency="usd" class="cnb-currency-select cnb-currency-usd nav-tab ">US Dollar ($)</a>
                </h2>
        <div class="cnb-message"><p class="cnb-error-message"></p></div>
    <div class="cnb-price-plans">
      <div class="currency-box currency-box-eur currency-box-active cnb-flexbox">
          <?php $plan = cnb_get_plan($plans, 'powered-by-eur-yearly'); ?>
        <div class="pricebox">
          <h3 class="yearly">Yearly Plan</h3>
          <div class="benefit">All branding removed</div>
          <div class="plan-amount"><span class="currency">€</span><span class="euros">1</span><span class="cents">.67</span><span class="timeframe">/month</span></div>
          <div class="billingprice">
            Billed at €19.99 annually
          </div>
          <a class="button button-primary" href="#" onclick="cnb_get_checkout('<?php _e($plan->id) ?>')">Upgrade</a>
        </div>

          <?php $plan = cnb_get_plan($plans, 'powered-by-eur-monthly'); ?>
        <div class="pricebox">
          <h3 class="">Monthly Plan</h3>
          <div class="benefit">All branding removed</div>
          <div class="plan-amount"><span class="currency">€</span><span class="euros">4</span><span class="cents">.99</span><span class="timeframe">/month</span></div>
          <div class="billingprice">
            Billed at €4.99 monthly
          </div>
          <a class="button button-secondary" href="#" onclick="cnb_get_checkout('<?php _e($plan->id) ?>')">Upgrade</a>
        </div>
      </div>
      <div class="currency-box currency-box-usd cnb-flexbox">
          <?php $plan = cnb_get_plan($plans, 'powered-by-usd-yearly'); ?>
        <div class="pricebox">
          <h3 class="yearly">Yearly Plan</h3>
          <div class="benefit">All branding removed</div>
          <div class="plan-amount"><span class="currency">$</span><span class="euros">1</span><span class="cents">.99</span><span class="timeframe">/month</span></div>
          <div class="billingprice">
            Billed at $23.88 annually
          </div>
          <a class="button button-primary" href="#" onclick="cnb_get_checkout('<?php _e($plan->id) ?>')">Upgrade</a>
        </div>
          <?php $plan = cnb_get_plan($plans, 'powered-by-usd-monthly'); ?>
        <div class="pricebox">
          <h3 class="">Monthly Plan</h3>
          <div class="benefit">All branding removed</div>
          <div class="plan-amount"><span class="currency">$</span><span class="euros">5</span><span class="cents">.99</span><span class="timeframe">/month</span></div>
          <div class="billingprice">
            Billed at $5.99 monthly
          </div>
          <a class="button button-secondary" href="#" onclick="cnb_get_checkout('<?php _e($plan->id) ?>')">Upgrade</a>
        </div>
      </div>

    </div>
    </form>
    <?php } ?>

    <p class="cnb-center">All <u>cloud</u> plans (free and paid) contain the following features:</p>
    <div  class="cnb-center" style="margin-bottom:50px;">
      <div><b>&check;</b> Phone, Email, Location, WhatsApp, Links</div>
      <div><b>&check;</b> Unlimited buttons</div>
      <div><b>&check;</b> Multibutton</div>
      <div><b>&check;</b> Buttonbar (full width with multiple actions)</div>
      <div><b>&check;</b> Advanced page targeting options</div>
      <div><b>&check;</b> Scheduling</div>
    </div>
    <?php

    do_action('cnb_footer');
}
