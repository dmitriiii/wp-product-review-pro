<?php

/*
Widget Name: WP Product Review Widget
Description: Show a particular review
Author: ThemeIsle
Author URI: https://themeisle.com/plugins/wp-product-review/
*/

/**
 * Class WPPR_Pro_SiteOrigin_Widget
 */
class WPPR_Pro_SiteOrigin_Widget extends SiteOrigin_Widget {

	/**
	 * The name of the template file.
	 */
	function get_template_name( $instance ) {
		global $_wppr_siteorigin_settings;
		$_wppr_siteorigin_settings = $instance;

		// for the siteorigin edit mode, we will load the CSS inline.
		if ( isset( $instance['is_preview'] ) && 1 === intval( $instance['is_preview'] ) ) {
			$review = new WPPR_Review_Model( $instance['id'] );
			$plugin = new WPPR();
			$public = new Wppr_Public( $plugin->get_plugin_name(), $plugin->get_version() );

			// this won't do much; for our purposes, this will set the `review` object which will then be used by `amp_styles()`.
			$public->load_review_assets( $review );

			ob_start();
			$public->amp_styles();
			$css = ob_get_clean();

			$instance['css'] = $css;

			add_filter( 'wppr_rating_circle_bar_styles', array( $public, 'rating_circle_bar_styles' ), 10, 2 );
			add_filter( 'wppr_rating_circle_fill_styles', array( $public, 'rating_circle_fill_styles' ), 10, 2 );

			add_filter( 'wppr_template', array( $this, 'show_review_in_a_different_template' ) );
		}

		return 'render';
	}

	/**
	 * Template will be in the same directory.
	 */
	function get_template_dir( $instance ) {
		return '.';
	}

	/**
	 * No style name.
	 */
	function get_style_name( $instance ) {
		return '';
	}

	/**
	 * The constructor.
	 */
	function __construct() {
		$templates = apply_filters( 'wppr_review_templates', array( 'default', 'style1', 'style2' ) );
		// add an element to the beginning of the array.
		$templates = array_merge( array( '' => __( 'Same template as review', 'wp-product-review' ) ), array_combine( $templates, array_map( 'ucwords', $templates ) ) );

		parent::__construct(
			'siteorigin',
			__( 'WP Product Review', 'wp-product-review' ),
			array(
				'description' => __( 'WPPR', 'wp-product-review' ),
			),
			array(),
			array(
				'id'       => array(
					'type'        => 'text',
					'label'       => __( 'Review Post ID', 'wp-product-review' ),
					'placeholder' => __( 'Post ID', 'wp-product-review' ),
				),
				'visual'   => array(
					'type'    => 'select',
					'label'   => __( 'Content to show', 'wp-product-review' ),
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
				),
				'template' => array(
					'type'    => 'select',
					'label'   => __( 'Which template to use?', 'wp-product-review' ),
					'options' => $templates,
				),
			),
			plugin_dir_path( __FILE__ )
		);
	}

	/**
	 * Show review in a different template.
	 *
	 * @param string $template The original template it was created in.
	 *
	 * @return string The new template.
	 */
	function show_review_in_a_different_template( $template ) {
		global $_wppr_siteorigin_settings;
		if ( ! empty( $_wppr_siteorigin_settings['template'] ) ) {
			$template = $_wppr_siteorigin_settings['template'];
		}
		return $template;
	}
}

siteorigin_widget_register( 'siteorigin', __FILE__, 'WPPR_Pro_SiteOrigin_Widget' );
