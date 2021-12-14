<?php

function flatsome_facebook_oauth_url() {
  $api_version = flatsome_facebook_api_version();
  $uri = get_template_directory_uri();
  $theme = wp_get_theme( get_template() );
  $version = $theme->get( 'Version' );
  $client_id = '380204239234502';
  $base_url = "https://www.facebook.com/$api_version/dialog/oauth";
  $redirect_uri = flatsome_api_url() . '/facebook/authorize/';
  $scope = 'pages_read_engagement,instagram_basic,public_profile';
  $state = urlencode( admin_url( "admin.php?page=optionsframework&tab=of-option-instagram&ver=$version" ) );
  $response_type = 'code';

  return "$base_url?client_id=$client_id&response_type=$response_type&scope=$scope&redirect_uri=$redirect_uri&state=$state";
}

function flatsome_facebook_login_button_html() {
  $url = flatsome_facebook_oauth_url();
  ob_start(); ?>

  <hr />
  <p><?php _e('Login with Facebook to connect an Instagram Business account:')  ?></p>
  <a class="button" style="padding: 5px 15px; height: auto; background-color: #4267b2; border-color: #4267b2; color: #ffffff;" href="<?php echo $url ?>">
    <span class="dashicons dashicons-facebook-alt" style="vertical-align: middle; margin-top: -2px;"></span>
    <?php _e( 'Login with Facebook', 'flatsome-admin' ) ?>
  </a>
  <p>
    <button class="button" name="flatsome_instagram_clear_cache">
      <?php _e( 'Clear Instagram cache', 'flatsome-admin' ) ?>
    </button>
  </p>
  <p>
    <a href="https://docs.uxthemes.com/article/379-how-to-connect-to-instagram-api" target="_blank" rel="noopener noreferrer">
      <?php _e( 'How to setup an Instagram Business account', 'flatsome-admin' ) ?>
    </a>
  </p>
  <?php return ob_get_clean();
}

function flatsome_facebook_accounts_html() {
  $accounts = flatsome_facebook_accounts();

  ob_start(); ?>

  <input type="hidden" value="0" name="facebook_accounts[]">

  <div class="flatsome-instagram-accounts theme-browser">
    <div class="themes wp-clearfix">
      <?php if ( empty( $accounts ) ) : ?>
        <div class="notice notice-info inline">
          <p><?php _e('No accounts connected yet...')  ?></p>
        </div>
      <?php else: ?>
      <?php foreach ( $accounts as $username => $account ) : ?>
      <div class="theme instagram-account instagram-account--<?php echo esc_attr( $username ) ?>" style="width: 46%">
        <input type="hidden" value="<?php echo esc_attr( $account['id'] ) ?>" name="facebook_accounts[<?php echo esc_attr( $username ) ?>]">
        <div class="theme-screenshot">
          <?php if ( ! empty( $account['profile_picture'] ) ) : ?>
          <img src="<?php echo esc_attr( $account['profile_picture'] ) ?>" alt="<?php echo esc_attr( $username ) ?>">
          <?php else : ?>
          <img src="<?php echo get_template_directory_uri() ?>/inc/admin/advanced/assets/images/instagram-profile.png" alt="<?php echo esc_attr( $username ) ?>">
          <?php endif ?>
        </div>
        <!-- <div class="notice inline notice-alt"><p></p></div> -->
        <div class="theme-id-container">
          <h2 class="theme-name">
            <a target="_blank" href="https://www.instagram.com/<?php echo esc_attr( $username ) ?>/">
              <?php echo esc_html( $username ) ?>
            </a>
          </h2>
          <div class="theme-actions">
            <button type="button" class="button button-small" onclick="jQuery(this).closest('.instagram-account').remove()">
              Disconnect
            </button>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <?php return ob_get_clean();
}

/**
 * Get accounts that is associated with an access token.
 *
 * @param string $access_token An access token for a Facebook user.
 *
 * @return WP_Error|array
 */
function flatsome_facebook_get_accounts( $access_token ) {
  $api_version = flatsome_facebook_api_version();
  $fields   = 'name,access_token,instagram_business_account{id,name,username,profile_picture_url}';
  $url      = "https://graph.facebook.com/$api_version/me/accounts?limit=100&fields=$fields&access_token=$access_token";
  $response = wp_remote_get( $url );

  if ( is_wp_error( $response ) ) {
    return new WP_Error( 'site_down', __( 'Unable to communicate with Instagram.', 'flatsome-admin' ) );
  } else {
    $body = json_decode( $response['body'], true );

    if ( array_key_exists( 'error', $body ) ) {
      return new WP_Error( 'site_down', $body['error']['message'] );
    }

    return $body;
  }
}

/**
 * Renders the popup that shows the accounts that can be connected.
 */
function flatsome_facebook_connect_admin_footer() {
  if ( ! is_array( $_GET ) || ! isset( $_GET['flatsome_facebook_access_token'] ) ) {
    return;
  }

  $access_token = sanitize_text_field( $_GET['flatsome_facebook_access_token'] );
  $all_accounts = flatsome_facebook_get_accounts( $access_token );
  $current_accounts = flatsome_facebook_accounts();
  $accounts = array();

  if ( ! is_wp_error( $all_accounts ) ) {
    $accounts = array_filter( $all_accounts['data'], function( $account ) {
      return ! empty( $account['instagram_business_account'] );
    } );
  }

  ob_start();

  ?>

  <div class="flatsome-instagram-connect">
    <div class="flatsome-instagram-connect-body">
      <h2 class=""><?php _e( 'Connect Instagram Business accounts', 'flatsome' ); ?></h2>
      <?php if ( is_wp_error( $accounts ) ) : ?>
        <div class="notice notice-error inline" style="margin: 0;">
          <p><?php echo $accounts->get_error_message() ?></p>
        </div>
        <div class="tablenav bottom textright">
          <button type="button" class="button" onclick="jQuery(this).closest('.flatsome-instagram-connect').hide()">
            <?php esc_html_e( 'Okay', 'flatsome' ); ?>
          </button>
        </div>
      <?php elseif ( empty( $accounts ) ) : ?>
        <div class="notice notice-info inline" style="margin: 0;">
          <p><?php esc_html_e( 'No associated Instagram Business account was found for your Facebook user.', 'flatsome' ) ?></p>
        </div>
        <div class="tablenav bottom textright">
          <button type="button" class="button" onclick="jQuery(this).closest('.flatsome-instagram-connect').hide()">
            <?php esc_html_e( 'Okay', 'flatsome' ); ?>
          </button>
        </div>
      <?php else : ?>
      <form action="<?php echo admin_url( 'admin-post.php' ); ?>" method="POST">
        <input type="hidden" name="action" value="flatsome_instagram_connect" />
        <?php wp_nonce_field( 'flatsome_instagram_connect', 'flatsome_instagram_connect_nonce' ); ?>
        <table class="widefat striped">
          <tbody>
            <?php foreach ( $accounts as $account ) : ?>
            <tr>
              <th class="check-column">
                <?php if ( ! array_key_exists( $account['instagram_business_account']['username'], $current_accounts ) ) : ?>
                <input type="checkbox" name="instagram_accounts[<?php echo esc_attr( $account['id'] ) ?>][id]" value="<?php echo esc_attr( $account['instagram_business_account']['id'] ) ?>">
                <?php else : ?>
                <input type="checkbox" disabled checked>
                <?php endif ?>
                <?php if ( ! empty( $account['instagram_business_account']['profile_picture_url'] ) ) : ?>
                <input type="hidden" name="instagram_accounts[<?php echo esc_attr( $account['id'] ) ?>][profile_picture]" value="<?php echo esc_attr( $account['instagram_business_account']['profile_picture_url']) ?>">
                <?php endif ?>
                <input type="hidden" name="instagram_accounts[<?php echo esc_attr( $account['id'] ) ?>][username]" value="<?php echo esc_attr( $account['instagram_business_account']['username'] ) ?>">
                <input type="hidden" name="instagram_accounts[<?php echo esc_attr( $account['id'] ) ?>][account_id]" value="<?php echo esc_attr( $account['id'] ) ?>">
                <input type="hidden" name="instagram_accounts[<?php echo esc_attr( $account['id'] ) ?>][account_access_token]" value="<?php echo esc_attr( $account['access_token'] ) ?>">
                <input type="hidden" name="instagram_accounts[<?php echo esc_attr( $account['id'] ) ?>][access_token]" value="<?php echo esc_attr( $access_token ) ?>">
              </th>
              <td width="38">
                <?php if ( ! empty( $account['instagram_business_account']['profile_picture_url'] ) ) : ?>
                <img src="<?php echo esc_attr( $account['instagram_business_account']['profile_picture_url'] ) ?>" width="38" style="border-radius: 100%" alt="<?php echo esc_attr( $account['instagram_business_account']['username'] ) ?>">
                <?php else : ?>
                <img src="<?php echo get_template_directory_uri() ?>/inc/admin/advanced/assets/images/instagram-profile.png" width="38" alt="<?php echo esc_attr( $account['instagram_business_account']['username'] ) ?>">
                <?php endif ?>
              </td>
              <td class="title">
                <strong class="row-title">
                <?php if ( ! empty( $account['instagram_business_account']['name'] ) ) : ?>
                <?php echo esc_html( $account['instagram_business_account']['name'] ) ?>
                <?php elseif ( ! empty( $account['name'] ) ) : ?>
                <?php echo esc_html( $account['name'] ) ?>
                <?php endif ?>
                </strong>
                <br>
                <a target="_blank" href="https://www.instagram.com/<?php echo esc_attr( $account['instagram_business_account']['username'] ) ?>/">
                  <?php echo '@' . esc_html( $account['instagram_business_account']['username'] ) ?>
                </a>
              </td>
            </tr>
            <?php endforeach ?>
          </tbody>
        </table>
        <div class="tablenav bottom textright">
          <button type="button" class="button" onclick="jQuery(this).closest('.flatsome-instagram-connect').hide()">
            <?php esc_html_e( 'Cancel', 'flatsome' ); ?>
          </button>
          <button name="flatsome_instagram_connect" class="button button-primary">
            <?php esc_html_e( 'Connect', 'flatsome' ); ?>
          </button>
        </div>
      </form>
      <?php endif ?>
    </div>
  </div>

  <?php
}
add_action( 'admin_footer-flatsome_page_optionsframework', 'flatsome_facebook_connect_admin_footer' );

/**
 * Saves the connected accounts data.
 */
function flatsome_facebook_connect_accounts() {
  check_admin_referer( 'flatsome_instagram_connect', 'flatsome_instagram_connect_nonce' );

  if ( ! empty( $_POST['instagram_accounts'] ) ) {
    $accounts = flatsome_facebook_accounts();

    foreach ( $_POST['instagram_accounts'] as $values ) {
      $account = array_map( 'sanitize_text_field', $values );
      if ( isset( $account['id'] ) ) {
        $accounts[ $account['username'] ] = $account;
      }
    }

    set_theme_mod( 'facebook_accounts', $accounts );
  }

  wp_safe_redirect( admin_url( 'admin.php?page=optionsframework&tab=of-option-instagram' ) );
}
add_action( 'admin_post_flatsome_instagram_connect', 'flatsome_facebook_connect_accounts' );

function flatsome_facebook_set_theme_mod( $values, $old_values ) {
  $result = array();

  foreach ( $values as $username => $id ) {
    if ( is_array( $old_values ) && array_key_exists( $username, $old_values ) ) {
      $result[ $username ] = $old_values[ $username ];
    } else {
      $result[ $username ] = $id;
    }
  }

  return $result;
}
add_filter( 'pre_set_theme_mod_facebook_accounts', 'flatsome_facebook_set_theme_mod', 10, 2 );

/**
 * Deletes the Instagram oEmbed cache and transients.
 *
 * @return void
 */
function flatsome_facebook_clear_cache() {
  global $wpdb;

  if ( isset( $_POST['flatsome_instagram_clear_cache'] ) ) {
    delete_option( 'flatsome_instagram_oembed_cache' );
    $wpdb->query( "DELETE FROM {$wpdb->options} WHERE `option_name` LIKE ('%\_transient\_flatsome\_instagram%');" );
    $wpdb->query( "DELETE FROM {$wpdb->options} WHERE `option_name` LIKE ('%\_transient\_timeout\_flatsome\_instagram%');" );
  }
}
add_action( 'of_save_options_before', 'flatsome_facebook_clear_cache' );
