<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://www.michely-web-engineering.de/
 * @since      1.0.0
 *
 * @package    Wpinvoices_itemdescription
 * @subpackage Wpinvoices_itemdescription/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Wpinvoices_itemdescription
 * @subpackage Wpinvoices_itemdescription/includes
 * @author     Marco Michely <marco.michely@michely-web-engineering.de>
 */
class Wpinvoices_itemdescription_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'wpinvoices_itemdescription',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
