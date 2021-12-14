<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       http://dangngocbinh.com
 * @since      1.0.0
 *
 * @package    Mc_Quetma
 * @subpackage Mc_Quetma/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Mc_Quetma
 * @subpackage Mc_Quetma/includes
 * @author     MeCode <dangngocbinh.dnb@gmail.com>
 */
class Mc_Quetma_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'mc-quetma',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
