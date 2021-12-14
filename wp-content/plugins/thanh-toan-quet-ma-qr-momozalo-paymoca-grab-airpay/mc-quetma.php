<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://dangngocbinh.com
 * @since             1.0.0
 * @package           Mc_Quetma
 *
 * @wordpress-plugin
 * Plugin Name:       Thanh Toán Quét Mã QR - Momo,Zalo Pay,Moca Grab, AirPay
 * Plugin URI:        https://mecode.pro
 * Description:       Thanh toán quét mã QR, hổ trợ Momo,Zalo Pay,Moca Grab, ShopeePay
 * Version:           1.2.3
 * Author:            MeCode
 * Author URI:        https://mecode.pro
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       mc-quetma
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'MC_QUETMA_VERSION', '1.2.3' );
define( 'MC_QUETMA_PLUGIN_URL', esc_url( plugins_url( '', __FILE__ ) ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-mc-quetma-activator.php
 */
function activate_mc_quetma() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-mc-quetma-activator.php';
	Mc_Quetma_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-mc-quetma-deactivator.php
 */
function deactivate_mc_quetma() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-mc-quetma-deactivator.php';
	Mc_Quetma_Deactivator::deactivate();
}



/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_mc_quetma() {

	$plugin = new Mc_Quetma();
	$plugin->run();

}


if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    
	register_activation_hook( __FILE__, 'activate_mc_quetma' );
	register_deactivation_hook( __FILE__, 'deactivate_mc_quetma' );
	require plugin_dir_path( __FILE__ ) . 'includes/class-mc-quetma.php';

	run_mc_quetma();
}

function mc_quetma_installed_notice() {
	if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ){
	    $class = 'notice notice-error';
		$message = __( 'Plugin Thanh Toán Quét Mã QR cần Woocommerce kích hoạt trước khi sử dụng. Vui lòng kiểm tra Woocommerce', 'qr_auto' );
		printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) ); 
    }
}
add_action( 'admin_notices', 'mc_quetma_installed_notice' );
