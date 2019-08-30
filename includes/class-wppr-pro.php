<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://themeisle.com
 * @since      2.0.0
 *
 * @package    WPPR_Pro
 * @subpackage WPPR_Pro/includes
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
 * @since      2.0.0
 * @package    WPPR_Pro
 * @subpackage WPPR_Pro/includes
 * @author     ThemeIsle <friends@themeisle.com>
 */
class WPPR_Pro {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    2.0.0
	 * @access   protected
	 * @var      WPPR_Pro_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    2.0.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    2.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * All the addons available.
	 *
	 * @since   2.0.0
	 * @access  protected
	 * @var     array $addons An array of available addons.
	 */
	protected $addons;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    2.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'wp-product-review-pro';
		$this->version     = '2.5.0';

		$this->addons = array(
			'wppr-pro-amazon',
			'wppr-pro-comparison-table',
			'wppr-pro-custom-icon',
			'wppr-pro-review-preload',
			'wppr-pro-listings',
			'wppr-pro-related-reviews',
			'wppr-pro-single-review-shortcode',
			'wppr-pro-submit-review',
		);

		$this->load_dependencies();
		$this->set_locale();

		if ( class_exists( 'WPPR' ) ) {
			$this->register_addons();
		}

		if ( defined( 'TI_UNIT_TESTING' ) ) {
			$this->loader->add_action( 'wppr_reload_addons', $this, 'register_addons' );
		}

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - WPPR_Pro_Loader. Orchestrates the hooks of the plugin.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    2.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		$this->loader = new WPPR_Pro_Loader();

		$this->loader->add_action( 'after_setup_theme', $this, 'register_required_plugins', 999 );
		$this->loader->add_action( 'elementor/widgets/widgets_registered', $this, 'add_elementor_widget' );
		$this->loader->add_filter( 'siteorigin_widgets_widget_folders', $this, 'add_siteorigin_widget' );
	}

	/**
	 * Require and instantiate Elementor Widget.
	 */
	function add_elementor_widget( $widgets_manager ) {
		$widget = new WPPR_Pro_Elementor_Widget();
		$widgets_manager->register_widget_type( $widget );
	}

	/**
	 * Require and instantiate SiteOrigin Widgets.
	 */
	function add_siteorigin_widget( $folders ) {
		$folders[] = trailingslashit( WPPR_PRO_PATH ) . 'includes/addons/widgets/';
		return $folders;
	}


	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Wp_Product_Review_Pro_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    2.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new WPPR_Pro_I18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Utility method to build addons.
	 *
	 * Uses the WPPR_Pro_Addon_Factory to instantiate modules and
	 * passes instances of WPPR_Query_Model and WPPR_Pro_Loader to the newly created addon.
	 *
	 * @since   2.0.0
	 */
	function register_addons() {
		$addons = $this->wppr_is_business();
		if ( ! is_array( $addons ) && $addons === true ) {
			$addons = $this->addons;
		}
		$model         = new WPPR_Query_Model();
		$addon_factory = new WPPR_Pro_Addon_Factory();
		foreach ( $addons as $addon ) {
			$addon_instance = $addon_factory::build( $addon );
			$addon_instance->register_model( $model );
			$addon_instance->register_loader( $this->get_loader() );
			$addon_instance->hooks();
		}
	}

	/**
	 * Returns the type of PRO available to this install.
	 *
	 * @since   2.0.0
	 * @access  public
	 * @return array|bool
	 */
	public function wppr_is_business() {
		$option = get_option( 'wp_product_review_pro_license_data', false );

		if ( isset( $option->plan ) ) {
			if ( intval( $option->plan ) > 0 ) {
				return true;
			}
		} else {
			if ( isset( $option->license ) ) {
				if ( $option->license === 'valid' ) {
					return true;
				}
			}
		}

		return array(
			'wppr-pro-comparison-table',
			'wppr-pro-custom-icon',
			'wppr-pro-review-preload',
			'wppr-pro-listings',
			'wppr-pro-related-reviews',
			'wppr-pro-single-review-shortcode',
		);
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     2.0.0
	 * @return    WPPR_Pro_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Register lite plugin.
	 */
	public function register_required_plugins() {
		if ( ! function_exists( 'tgmpa' ) ) {
			include_once WPPR_PRO_PATH . '/lib/tgmpa/tgm-plugin-activation/class-tgm-plugin-activation.php';
		}

		if ( function_exists( 'tgmpa' ) ) {
			add_action( 'tgmpa_register', array( $this, 'tgmpa_register' ) );
		}
	}

	/**
	 * Initialize TGM.
	 */
	public function tgmpa_register() {

		$plugins = array(
			array(
				'name'     => 'WP Product Review Lite',
				'slug'     => 'wp-product-review',
				'required' => true,
			),
		);
		$config  = array(
			'id'           => 'wp-product-review',
			// Unique ID for hashing notices for multiple instances of TGMPA.
			'default_path' => '',
			// Default absolute path to bundled plugins.
			'menu'         => 'tgmpa-install-plugins',
			// Menu slug.
			'parent_slug'  => 'plugins.php',
			// Parent menu slug.
			'capability'   => 'manage_options',
			// Capability needed to view plugin install page, should be a capability associated with the parent menu used.
			'has_notices'  => true,
			// Show admin notices or not.
			'dismissable'  => true,
			// If false, a user cannot dismiss the nag message.
			'dismiss_msg'  => '',
			// If 'dismissable' is false, this message will be output at top of nag.
			'is_automatic' => false,
			// Automatically activate plugins after installation or not.
			'message'      => '',
			// Message to output right before the plugins table.
		);
		/**
		 * Used to supress any warning that might come from tgmp function, i.e Undefined $_GLOBAL['tgmpa'].
		 */
		set_error_handler( array( $this, 'error_handler' ) );
		tgmpa( $plugins, $config );
		restore_error_handler();

	}

	/**
	 * Custom exception handler.
	 *
	 * @param Exception $e The esception.
	 */
	function error_handler( $e ) {
		// TODO Implement custom error handler.
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    2.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     2.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     2.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
