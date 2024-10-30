<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.michely-web-engineering.de/
 * @since      1.0.0
 *
 * @package    Wpinvoices_itemdescription
 * @subpackage Wpinvoices_itemdescription/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Wpinvoices_itemdescription
 * @subpackage Wpinvoices_itemdescription/includes
 * @author     Marco Michely <marco.michely@michely-web-engineering.de>
 */
class Wpinvoices_itemdescription {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Wpinvoices_itemdescription_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'WPINVOICES_ITEMDESCRIPTION_VERSION' ) ) {
			$this->version = WPINVOICES_ITEMDESCRIPTION_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'wpinvoices_itemdescription';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Wpinvoices_itemdescription_Loader. Orchestrates the hooks of the plugin.
	 * - Wpinvoices_itemdescription_i18n. Defines internationalization functionality.
	 * - Wpinvoices_itemdescription_Admin. Defines all hooks for the admin area.
	 * - Wpinvoices_itemdescription_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wpinvoices_itemdescription-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wpinvoices_itemdescription-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wpinvoices_itemdescription-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wpinvoices_itemdescription-public.php';

		$this->loader = new Wpinvoices_itemdescription_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Wpinvoices_itemdescription_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Wpinvoices_itemdescription_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Wpinvoices_itemdescription_Admin( $this->get_plugin_name(), $this->get_version() );
		$wpinv_ajax = new WPInv_Ajax();

		$this->loader->add_action( 'init', $plugin_admin, 'dp_add_editor' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'wpinv_dp_add_meta_boxes_post', 99);

		$this->loader->add_filter( 'wpinv_admin_invoice_line_item_summary', $plugin_admin, 'wpinv_dp_admin_invoice_line_item_summary', 9, 4 );
		// $this->loader->add_filter( 'wpinv_invoice_items_actions_content', $plugin_admin, 'wpinv_dp_invoice_items_actions_content', 9, 3 );
		$this->loader->add_filter( 'wp_insert_post_data', $plugin_admin, 'wpinv_dp_insert_post_data', 9, 2 );

		$this->loader->add_action( 'wp_ajax_nopriv_wpinv_admin_recalculate_totals', $plugin_admin, 'wpinv_dp_ajax_nopriv_admin_recalculate_totals', 0);
		$this->loader->add_action( 'wp_ajax_wpinv_admin_recalculate_totals', $plugin_admin, 'wpinv_dp_ajax_nopriv_admin_recalculate_totals', 0);
		
		$this->loader->add_action( 'wp_ajax_nopriv_wpinv_dp_add_invoice_item', $plugin_admin, 'wpinv_dp_add_invoice_item', 0);
		$this->loader->add_action( 'wp_ajax_wpinv_dp_add_invoice_item', $plugin_admin, 'wpinv_dp_add_invoice_item', 0);
		
		$this->loader->add_action( 'wp_ajax_nopriv_wpinv_dp_remove_invoice_item', $plugin_admin, 'wpinv_dp_remove_invoice_item', 0);
		$this->loader->add_action( 'wp_ajax_wpinv_dp_remove_invoice_item', $plugin_admin, 'wpinv_dp_remove_invoice_item', 0);
		$this->loader->add_action( 'wp_ajax_nopriv_wpinv_dp_create_invoice_item', $plugin_admin, 'wpinv_dp_create_invoice_item', 0);
		$this->loader->add_action( 'wp_ajax_wpinv_dp_create_invoice_item', $plugin_admin, 'wpinv_dp_create_invoice_item', 0);

		remove_action( 'wpinv_email_invoice_items', 'wpinv_email_invoice_items');
		remove_action( 'wpinv_email_invoice_details', 'wpinv_email_invoice_details');

		$this->loader->add_action( 'wpinv_email_invoice_items', $plugin_admin, 'wpinv_dp_email_invoice_items', 10, 3);
		$this->loader->add_action( 'wpinv_email_invoice_details', $plugin_admin, 'wpinv_dp_email_invoice_details', 10, 3);

		$this->loader->add_filter( 'template_include', $plugin_admin, 'wpinv_dp_template', 99, 1);
		$this->loader->add_filter( 'wpinv_get_invoice_tax', $plugin_admin, 'wpinv_dp_get_invoice_tax', 99, 4);
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Wpinvoices_itemdescription_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Wpinvoices_itemdescription_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
