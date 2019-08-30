<?php
/**
 * The WPPR Pro Elementor Widget Class.
 *
 * @package WPPR_Pro
 * @subpackage Widget
 * @copyright   Copyright (c) 2017, Bogdan Preda
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 2.0.0
 */

/**
 * Class WPPR_Pro_Elementor_Widget
 */
class WPPR_Pro_Elementor_Widget extends \Elementor\Widget_Base {

	/**
	 * Widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'WP Product Review', 'wp-product-review' );
	}

	/**
	 * Widget icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'dashicons dashicons-star-filled';
	}

	/**
	 * Widget name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'wppr-elementor-widget';
	}

	/**
	 * Register Elementor Controls.
	 */
	protected function _register_controls() {
		$this->start_controls_section(
			'content_section',
			array(
				'label' => __( 'Configure', 'wp-product-review' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);
		$this->add_control(
			'id',
			array(
				'label'       => __( 'Review Post ID', 'wp-product-review' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'input_type'  => 'number',
				'placeholder' => __( 'Post ID', 'wp-product-review' ),
			)
		);
		$this->add_control(
			'visual',
			array(
				'label'   => __( 'Content to show', 'wp-product-review' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => array(
					// deliberately not including full, yes and no options as that can be used from the shortcode.
					'name'           => __( 'Name of the review', 'wp-product-review' ),
					'price'          => __( 'Price of the review (if it exists)', 'wp-product-review' ),
					'image'          => __( 'Image of the review (if it exists)', 'wp-product-review' ),
					'schema'         => __( 'The json-ld schema', 'wp-product-review' ),
					'pros-cons'      => __( 'Pros and cons', 'wp-product-review' ),
					'rating'         => __( 'Overall rating', 'wp-product-review' ),
					'rating-options' => __( 'Rating options', 'wp-product-review' ),
				),
			)
		);

		$templates = apply_filters( 'wppr_review_templates', array( 'default', 'style1', 'style2' ) );
		// add an element to the beginning of the array.
		$templates = array_merge( array( '' => __( 'Same template as review', 'wp-product-review' ) ), array_combine( $templates, array_map( 'ucwords', $templates ) ) );

		$this->add_control(
			'template',
			array(
				'label'   => __( 'Which template to use?', 'wp-product-review' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => $templates,
			)
		);
		$this->end_controls_section();
	}

	/**
	 * Show the widget output.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		global $_wppr_elementor_settings;
		$_wppr_elementor_settings = $settings;

		// for the elementor edit mode, we will load the CSS inline.
		if ( \Elementor\Plugin::$instance->editor->is_edit_mode() === true ) {
			$review = new WPPR_Review_Model( $settings['id'] );
			$plugin = new WPPR();
			$public = new Wppr_Public( $plugin->get_plugin_name(), $plugin->get_version() );

			add_filter( 'wppr_rating_circle_bar_styles', array( $public, 'rating_circle_bar_styles' ), 10, 2 );
			add_filter( 'wppr_rating_circle_fill_styles', array( $public, 'rating_circle_fill_styles' ), 10, 2 );

			add_filter( 'wppr_template', array( $this, 'show_review_in_a_different_template' ) );

			// this won't do much; for our purposes, this will set the `review` object which will then be used by `amp_styles()`.
			$public->load_review_assets( $review );

			// capture the CSS.
			ob_start();
			$public->amp_styles();
			echo '<style id="wppr-elementor-editor">' . ob_get_clean() . '</style>';
		}

		echo do_shortcode( '[P_REVIEW post_id="' . $settings['id'] . '" visual="' . $settings['visual'] . '" template="' . $settings['template'] . '"]' );
	}

	/**
	 * Show review in a different template.
	 *
	 * @param string $template The original template it was created in.
	 *
	 * @return string The new template.
	 */
	function show_review_in_a_different_template( $template ) {
		global $_wppr_elementor_settings;
		if ( ! empty( $_wppr_elementor_settings['template'] ) ) {
			$template = $_wppr_elementor_settings['template'];
		}
		return $template;
	}
}
