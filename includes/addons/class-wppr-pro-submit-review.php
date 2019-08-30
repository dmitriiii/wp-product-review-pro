<?php
/**
 * The file that allows users to submit a review from the front end.
 *
 * @link       https://themeisle.com
 * @since      2.0.0
 *
 * @package    WPPR_Pro
 * @subpackage WPPR_Pro/includes/addons/abstract
 */

/**
 * Class WPPR_Pro_Submit_Review
 *
 * @since       2.0.0
 * @package     WPPR_Pro
 * @subpackage  WPPR_Pro/includes/addons
 */
class WPPR_Pro_Submit_Review extends WPPR_Pro_Addon_Abstract {

	/**
	 * WPPR_Pro_Submit_Review constructor.
	 *
	 * @since   2.0.0
	 * @access  public
	 */
	public function __construct() {
		$this->name    = __( 'Submit Review', 'wp-product-review' );
		$this->slug    = 'wppr-pro-submit-review';
		$this->version = '1.0.0';
	}

	/**
	 * Registers the hooks needed by the addon.
	 *
	 * @since   2.0.0
	 * @access  public
	 */
	public function hooks() {
		add_shortcode( 'wppr_submit_review', array( $this, 'shortcode' ) );
		$this->loader->add_action( 'rest_api_init', $this, 'rest_api_init' );
		$this->loader->add_filter( 'wppr_templates_dir', $this, 'add_template_dir' );

		if ( defined( 'TI_UNIT_TESTING' ) ) {
			add_action( 'rest_api_init', array( $this, 'rest_api_init' ) );
		}
	}

	/**
	 * Add the folder where the template for this addon will be available.
	 *
	 * @access  public
	 */
	public function add_template_dir( $dirs ) {
		$dirs[] = WPPR_PRO_PATH . '/includes/addons/layouts/submit-review/';
		return $dirs;
	}

	/**
	 * Add the REST API endpoints.
	 *
	 * @access  public
	 */
	public function rest_api_init() {
		register_rest_route(
			WPPR_SLUG . '/v' . WPPR_API_VERSION,
			'/submit_review/',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'submit_review' ),
			)
		);
	}

	/**
	 * The submit revivew endpoint.
	 *
	 * @access  public
	 */
	public function submit_review( $request ) {
		$guest = intval( $request->get_param( 'guest' ) );
		if ( 0 === $guest && ! is_user_logged_in() ) {
			wp_die();
		}

		// want to preprocess additional fields, e.g. captcha?
		do_action( 'wppr_submit_review_process_request', $request );

		$id = wp_insert_post(
			array(
				'post_type'    => 'wppr_review',
				'post_title'   => strip_tags( $request->get_param( 'name' ) ),
				'post_content' => strip_tags( $request->get_param( 'content' ) ),
				'post_status'  => 'draft',
			)
		);

		$category = $request->get_param( 'category' );
		if ( is_string( $category ) ) {
			$category = array( $category );
		}
		$category = array_filter( $category );
		if ( ! empty( $category ) ) {
			wp_set_post_terms( $id, $category, 'wppr_category' );
		}

		$links = array(
			strip_tags( $request->get_param( 'affiliate-name-1' ) ) => esc_url_raw( strip_tags( $request->get_param( 'affiliate-link-1' ) ) ),
			strip_tags( $request->get_param( 'affiliate-name-2' ) ) => esc_url_raw( strip_tags( $request->get_param( 'affiliate-link-2' ) ) ),
		);

		$links = array_filter( $links );
		if ( $links ) {
			update_post_meta( $id, 'wppr_links', $links );
		}

		$image = $request->get_param( 'image' );
		if ( ! empty( $image ) ) {
			update_post_meta( $id, 'cwp_rev_product_image', esc_url_raw( strip_tags( $image ) ) );
		}
		update_post_meta( $id, 'cwp_rev_price', strip_tags( $request->get_param( 'price' ) ) );
		update_post_meta( $id, 'cwp_meta_box_check', 'Yes' );

		$options = array_filter( $request->get_param( 'option' ) );
		$grades  = array_filter( $request->get_param( 'grade' ) );
		$x       = 0;
		foreach ( $grades as $grade ) {
			$y = $x + 1;
			update_post_meta( $id, "option_{$y}_grade", strip_tags( $grade ) );
			update_post_meta( $id, "option_{$y}_content", strip_tags( $options[ $x++ ] ) );
		}

		$pros = array_filter( $request->get_param( 'pro' ) );
		$x    = 0;
		foreach ( $pros as $pro ) {
			$y = $x++ + 1;
			update_post_meta( $id, "cwp_option_{$y}_pro", strip_tags( $pro ) );
		}

		$cons = array_filter( $request->get_param( 'con' ) );
		$x    = 0;
		foreach ( $cons as $con ) {
			$y = $x++ + 1;
			update_post_meta( $id, "cwp_option_{$y}_cons", strip_tags( $con ) );
		}

		do_action( 'wppr_submit_review_processed_request', $id, $request );

		if ( ! defined( 'TI_UNIT_TESTING' ) ) {
			wp_send_json_success();
		}
	}

	/**
	 * The shortcode implementation.
	 *
	 * @access  public
	 */
	public function shortcode( $atts = array() ) {
		$atts = shortcode_atts(
			// want to add more parameters to the shortcode?
			apply_filters(
				'wppr_submit_review_shortcode_atts',
				array(
					'title'   => '',
					'message' => __( 'Your review has been submitted.', 'wp-product-review' ),
					'guest'   => false,
				)
			), $atts
		);

		if ( ! $atts['guest'] && ! is_user_logged_in() ) {
			return sprintf( __( 'This is only available for logged in users. Please login %1$shere%2$s.', 'wp-product-review' ), '<a href="' . wp_login_url() . '">', '</a>' );
		}

		if ( ! post_type_exists( 'wppr_review' ) ) {
			return __( 'The Review custom post type is not enabled. You can enable this in the settings page.', 'wp-product-review' );
		}

		$categories = get_categories(
			array(
				'taxonomy'   => 'wppr_category',
				'orderby'    => 'name',
				'order'      => 'ASC',
				'hide_empty' => false,
			)
		);

		$options[''] = __( 'Select Category', 'wp-product-review' );
		if ( $categories ) {
			foreach ( $categories as $cat ) {
				$options[ $cat->term_id ] = esc_html( $cat->name );
			}
		}
		$categories = $options;

		// want to change the categories or the options?
		$categories = apply_filters( 'wppr_submit_review_categories', $categories, $atts );

		$template = new WPPR_Template();

		$fields = array(
			array(
				'type'        => 'text',
				'name'        => 'name',
				'placeholder' => __( 'Product Name', 'wp-product-review' ),
				'class'       => 'wppr-product-name',
			),
			array(
				'type'        => method_exists( 'WPPR_Html_Fields', 'textarea' ) ? 'textarea' : 'text',
				'name'        => 'content',
				'placeholder' => __( 'Product Description', 'wp-product-review' ),
				'class'       => 'wppr-product-content',
			),
			array(
				'type'        => 'select',
				'name'        => 'category',
				'placeholder' => __( 'Product Category', 'wp-product-review' ),
				'class'       => 'wppr-product-category',
				'options'     => $categories,
			),
			array(
				'type'        => 'text',
				'name'        => 'price',
				'placeholder' => __( 'Product Price', 'wp-product-review' ),
				'class'       => 'wppr-product-price',
			),
			array(
				'type'        => 'text',
				'name'        => 'affiliate-name-1',
				'placeholder' => __( 'Button Text', 'wp-product-review' ),
				'class'       => 'wppr-product-affiliate-1',
			),
			array(
				'type'        => 'text',
				'name'        => 'affiliate-link-1',
				'placeholder' => __( 'Affiliate Link', 'wp-product-review' ),
				'class'       => 'wppr-product-link-1',
			),
			array(
				'type'        => 'text',
				'name'        => 'affiliate-name-2',
				'placeholder' => sprintf( __( 'Button Text %s', 'wp-product-review' ), 2 ),
				'class'       => 'wppr-product-affiliate-2',
			),
			array(
				'type'        => 'text',
				'name'        => 'affiliate-link-2',
				'placeholder' => sprintf( __( 'Affiliate Link %s', 'wp-product-review' ), 2 ),
				'class'       => 'wppr-product-link-2',
			),
			array(
				'type'        => 'heading',
				'subtype'     => 'h3',
				'name'        => 'heading1',
				'placeholder' => __( 'Product Options', 'wp-product-review' ),
				'class'       => 'wppr-product-section',
			),
		);

		$model = new WPPR_Query_Model();
		$num   = $model->wppr_get_option( 'cwppos_option_nr' );

		// options and grades.
		for ( $x = 0; $x < $num; $x++ ) {
			$fields[] = array(
				'type'        => 'text',
				'name'        => 'option[]',
				'id'          => 'option' . ( $x + 1 ),
				'placeholder' => __( 'Option', 'wp-product-review' ) . ' ' . ( $x + 1 ),
				'class'       => 'wppr-product-option',
			);
			$fields[] = array(
				'type'        => 'text',
				'name'        => 'grade[]',
				'id'          => 'grade' . ( $x + 1 ),
				'placeholder' => __( 'Grade', 'wp-product-review' ) . ' ' . ( $x + 1 ),
				'class'       => 'wppr-product-grade',
			);
		}

		// pros
		$fields[] = array(
			'type'        => 'heading',
			'subtype'     => 'h3',
			'name'        => 'heading2',
			'placeholder' => __( 'Pro Features', 'wp-product-review' ),
			'class'       => 'wppr-product-section',
		);
		for ( $x = 0; $x < $num; $x++ ) {
			$fields[] = array(
				'type'        => 'text',
				'name'        => 'pro[]',
				'id'          => 'pro' . ( $x + 1 ),
				'placeholder' => __( 'Feature', 'wp-product-review' ) . ' ' . ( $x + 1 ),
				'class'       => 'wppr-product-pro',
			);
		}

		// cons
		$fields[] = array(
			'type'        => 'heading',
			'subtype'     => 'h3',
			'name'        => 'heading2',
			'placeholder' => __( 'Cons Features', 'wp-product-review' ),
			'class'       => 'wppr-product-section',
		);
		for ( $x = 0; $x < $num; $x++ ) {
			$fields[] = array(
				'type'        => 'text',
				'name'        => 'con[]',
				'id'          => 'con' . ( $x + 1 ),
				'placeholder' => __( 'Feature', 'wp-product-review' ) . ' ' . ( $x + 1 ),
				'class'       => 'wppr-product-con',
			);
		}

		if ( current_user_can( 'upload_files' ) ) {
			$fields[] = array(
				'type'        => 'image',
				'name'        => 'image',
				'placeholder' => __( 'Product Image', 'wp-product-review' ),
				'class'       => 'wppr-product-image',
			);
			wp_enqueue_media();
		}

		wp_enqueue_script( 'submit-review', WPPR_PRO_ADDONS_ASSETS . 'js/submit-review.js' );
		wp_localize_script(
			'submit-review', 'sr', array(
				'url'   => get_rest_url( null, WPPR_SLUG . '/v' . WPPR_API_VERSION . '/submit_review/' ),
				'nonce' => wp_create_nonce( 'wp_rest' ),
			)
		);

		wp_enqueue_style( 'submit-review', WPPR_PRO_ADDONS_ASSETS . 'css/submit-review.css' );

		add_filter( 'wppr_field', array( $this, 'modify_html_fields' ), 10, 2 );

		return $template->render(
			'submit-review-shortcode', array(
				'fields' => apply_filters( 'wppr_submit_review_fields', $fields, $atts ),
				'atts'   => $atts,
			),
			false
		);
	}

	/**
	 * Modify the HTML fields type.
	 */
	function modify_html_fields( $html, $args ) {
		// name is mandatory
		if ( 'name' === $args['name'] ) {
			$html = str_replace( 'type="text"', 'type="text" required', $html );
		}

		// grades should be numeric.
		if ( in_array( $args['name'], array( 'grade[]' ), true ) ) {
			$html = str_replace( 'type="text"', 'type="number" min=0 max=100', $html );
		}

		// affiliate links and image should be urls.
		if ( in_array( $args['name'], array( 'image' ), true ) || strpos( $args['name'], '-link' ) !== false ) {
			$html = str_replace( 'type="text"', 'type="url"', $html );
		}

		return $html;
	}

}
