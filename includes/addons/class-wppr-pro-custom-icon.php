<?php
/**
 * The file that defines Custom Icon Addon.
 *
 * @link       https://themeisle.com
 * @since      2.0.0
 *
 * @package    WPPR_Pro
 * @subpackage WPPR_Pro/includes/addons/abstract
 */

/**
 * Class WPPR_Pro_Custom_Icon
 *
 * @since       2.0.0
 * @package     WPPR_Pro
 * @subpackage  WPPR_Pro/includes/addons
 */
class WPPR_Pro_Custom_Icon extends WPPR_Pro_Addon_Abstract {

	/**
	 * WPPR_Pro_Custom_Icon constructor.
	 *
	 * @since   2.0.0
	 * @access  public
	 */
	public function __construct() {
		$this->name    = __( 'Pro Custom Icon', 'wp-product-review' );
		$this->slug    = 'wppr-pro-custom-icon';
		$this->version = '1.1.1';
	}

	/**
	 * Registers the hooks needed by the addon.
	 *
	 * @since   2.0.0
	 * @access  public
	 */
	public function hooks() {
		$this->loader->add_filter( 'wppr_settings_fields', $this, 'add_fields', 10, 1 );
		$this->loader->add_filter( 'wppr_get_old_option', $this, 'get_old_option', 10, 2 );
		$this->loader->add_filter( 'wppr_option_custom_icon', $this, 'wppr_add_custom_icon', 99 );
		$this->loader->add_filter( 'wppr_global_style', $this, 'wppr_add_icon_style', 99 );
		$this->loader->add_action( 'admin_enqueue_scripts', $this, 'wppr_custom_bar_icon_scripts' );
		$this->loader->add_action( 'wp_enqueue_scripts', $this, 'front_end_scripts' );
	}

	/**
	 * Method to filter old value from DB.
	 *
	 * @since   2.0.0
	 * @access  public
	 *
	 * @param   string $value The value passed by the filter.
	 * @param   string $key The key passed by the filter.
	 *
	 * @return mixed
	 */
	public function get_old_option( $value, $key ) {
		$allowed_options = array(
			'cwppos_change_bar_icon',
		);
		// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		if ( in_array( $key, $allowed_options, true ) && $value == false ) {
			$global_settings_fields = WPPR_Global_Settings::instance()->get_fields();
			$value                  = get_option( $key, isset( $global_settings_fields['pro_listings'][ $key ]['default'] ) ? $global_settings_fields['pro_listings'][ $key ]['default'] : '' );
		}

		return $value;
	}

	/**
	 * Get the icons that can be used for the rating bars.
	 */
	private function get_icons() {
		return apply_filters( 'wppr_custom_icons', array( '', '&#xf155', '&#xf154', '&#xf487', '&#xf147', '&#xf529', '&#xf520', '&#xf158', '&#xf335', '&#xf227', '&#xf127', '&#xf542', '&#xf313', '&#xf111', '&#xf330', '&#xf331', '&#xf174', '&#xf482', '&#xf312' ) );
	}

	/**
	 * Registers a new fields list for the section defined in add_section().
	 *
	 * @since   2.0.0
	 * @access  public
	 *
	 * @param   array $fields The fields array.
	 *
	 * @return mixed
	 */
	public function add_fields( $fields ) {

		$pos        = array_search( 'cwppos_option_nr', array_keys( $fields['general'] ), true );
		$new_fields = array();
		if ( ! method_exists( 'WPPR_Html_Fields', 'icon' ) ) {
			// cannot use this feature in new pro and old lite.
			$new_fields = array(
				'heading-temp' => array(
					'type'        => 'heading',
					'subtype'     => 'h4',
					'name'        => __( 'Rating Icon', 'wp-product-review' ),
					'description' => __( 'Please upgrade to the latest version of WP Product Review Lite to use this feature.', 'wp-product-review' ),
				),
			);
		} else {
			$new_fields = array(
				'cwppos_change_bar_icon' => array(
					'id'          => 'change_bar_icon',
					'name'        => __( 'Rating Icon', 'wp-product-review' ),
					'description' => __( 'Choose which icon you would like to use for the rating bar. Select the empty icon to go back to the default bars.', 'wp-product-review' ),
					'type'        => 'icon',
					'default'     => '',
					'options'     => $this->get_icons(),
				),
				// to indicate what type of icon this is, in order to differentiate it from previously-chosen fontawesome icons.
				'cwppos_bar_icon_type'   => array(
					'id'          => 'bar_icon_type',
					'name'        => 'bar_icon_type',
					'description' => '',
					'type'        => 'hidden',
					'value'       => 'dashicons',
				),
			);
		}

		$start_part = array_slice( $fields['general'], 0, $pos + 1, true );
		$end_part   = array_slice( $fields['general'], $pos, null, true );

		$fields['general'] = array_merge( $start_part, $new_fields, $end_part );

		return $fields;
	}

	/**
	 * Add custom icon.
	 *
	 * @since   2.0.0
	 * @access  public
	 */
	public function wppr_add_custom_icon() {
		$options = new WPPR_Options_Model();
		$icon    = $options->wppr_get_option( 'cwppos_change_bar_icon' );

		if ( ! empty( $icon ) ) {
			return ' wppr-custom-icon';
		}
	}

	/**
	 * Style for custom icon.
	 *
	 * @since   2.0.0
	 * @access  public
	 */
	public function wppr_add_icon_style( $style ) {
		$options = new WPPR_Options_Model();
		$icon    = $options->wppr_get_option( 'cwppos_change_bar_icon' );

		if ( ! empty( $icon ) ) {
			$style .= '#review-statistics .review-wu-bars ul.wppr-custom-icon li:before {
				content: "\\' . substr( $icon, 2 ) . '";
			}';
			$style .= '.wppr-template .wppr-review-option ul.wppr-custom-icon li:before {
				content: "\\' . substr( $icon, 2 ) . '";
			}';
		}

		return $style;
	}

	/**
	 * Register scripts and styles for this addon.
	 *
	 * @since   2.0.0
	 * @access  public
	 */
	public function wppr_custom_bar_icon_scripts() {
		wp_enqueue_script( $this->slug, WPPR_PRO_ADDONS_ASSETS . 'js/wppr-pro-custom-icon.js', false, $this->version, 'all' );
		wp_enqueue_style( $this->slug, WPPR_PRO_ADDONS_ASSETS . 'css/wppr-pro-custom-icon.css', array( 'dashicons' ), $this->version, 'all' );

		$css   = '';
		$icons = $this->get_icons();
		foreach ( $icons as $icon ) {
			$css .= '.cwp_bar_icon_field i#' . substr( $icon, 3 ) . ':before {
				content: "\\' . substr( $icon, 3 ) . '";
			}';
		}

		wp_add_inline_style( $this->slug, $css );
	}

	/**
	 * Register scripts and styles for the front-end.
	 *
	 * @since   2.4.0
	 * @access  public
	 */
	public function front_end_scripts() {
		$options   = new WPPR_Options_Model();
		$icon      = $options->wppr_get_option( 'cwppos_change_bar_icon' );
		$icon_type = $options->wppr_get_option( 'cwppos_bar_icon_type' );

		// new free and new pro after removing fontawesome with an font awesome icon selected.
		if ( version_compare( WPPR_LITE_VERSION, '3.6.0', '<' ) && ! empty( $icon ) && empty( $icon_type ) ) {
			$plugin = new WPPR();
			wp_enqueue_style( $plugin->get_plugin_name() . 'fa', WPPR_URL . '/assets/css/font-awesome.min.css', array(), $plugin->get_version() );
			wp_enqueue_style( $plugin->get_plugin_name() . '-fa-compat', WPPR_URL . '/assets/css/fontawesome-compat.css', array(), $plugin->get_version() );
		}

	}

}
