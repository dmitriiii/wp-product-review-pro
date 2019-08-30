<?php
/**
 * The WPPR Pro Related Reviews Widget Class.
 *
 * @package WPPR_Pro
 * @subpackage Widget
 * @copyright   Copyright (c) 2017, Bogdan Preda
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 2.0.0
 */

if ( class_exists( 'WPPR_Widget_Abstract' ) ) {

	/**
	 * Class WPPR_Pro_Related_Reviews_Widget
	 */
	class WPPR_Pro_Related_Reviews_Widget extends WPPR_Widget_Abstract {

		/**
		 * Widget text domain of this plugin
		 *
		 * @since   2.0.0
		 * @access  public
		 * @var     string $text_domain Widget text domain of this plugin.
		 */
		public $text_domain;

		/**
		 * Widget number of posts to show in the widget
		 *
		 * @since   2.0.0
		 * @access  public
		 * @var     string $number_posts Widget number of posts to show in the widget.
		 */
		public $number_posts;

		/**
		 * The post ID
		 *
		 * @since   2.0.0
		 * @access  public
		 * @var     integer $post_id The post ID.
		 */
		public $post_id;

		/**
		 * Is review
		 *
		 * @since   2.0.0
		 * @access  public
		 * @var     boolean $is_review If is review return true or false.
		 */
		public $is_review;

		/**
		 * WPPR_Pro_Related_Reviews_Widget constructor.
		 *
		 * @since   2.0.0
		 * @access  public
		 */
		public function __construct() {
			parent::__construct(
				'WPPR-Related-Reviews-Widget',
				__( 'Related Review', 'wp-product-review' ),
				array(
					'classname'   => 'widget_cwp_latest_products_widget',
					'description' => __( 'Earn more visitors, displaying related reviews for each product.', 'wp-product-review' ),
				)
			);

			$this->number_posts = 5;
		}

		/**
		 * Utility method to register the widget.
		 *
		 * @since   2.0.0
		 * @access  public
		 */
		public function register() {
			register_widget( 'WPPR_Pro_Related_Reviews_Widget' );
		}

		/**
		 * Method for widget form creation
		 *
		 * @since   2.0.0
		 * @access  public
		 * @param   array $instance The form instance.
		 */
		public function form( $instance ) {
			$this->adminAssets();
			if ( ! isset( $instance['title'] ) ) {
				$instance['title'] = __( 'Related Reviews', 'wp-product-review' );
			}

			if ( ! isset( $instance['show_thumb'] ) ) {
				$instance['show_thumb'] = false;
			}

			if ( ! isset( $instance['number_posts'] ) ) {
				$instance['number_posts'] = $this->number_posts;
			}

			$instance = parent::form( $instance );

			include( WPPR_PRO_PATH . '/includes/addons/layouts/widget-admin-tpl.php' );
		}

		/**
		 * Method to updated widget data.
		 *
		 * @since   2.0.0
		 * @access  public
		 * @param   array $new_instance The new form instance.
		 * @param   array $old_instance The old form instance.
		 * @return mixed
		 */
		public function update( $new_instance, $old_instance ) {
			$instance = $old_instance;
			// Fields
			$instance['title_type']         = ( isset( $new_instance['title_type'] ) ) ? (bool) $new_instance['title_type'] : false;
			$instance['title']              = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
			$instance['number_posts']       = ( ! empty( $new_instance['number_posts'] ) ) ? absint( $new_instance['number_posts'] ) : '';
			$instance['show_thumb']         = ( isset( $new_instance['show_thumb'] ) ) ? (bool) $new_instance['show_thumb'] : false;
			$instance['cwp_tp_buynow']      = ( ! empty( $new_instance['cwp_tp_buynow'] ) ) ? strip_tags( $new_instance['cwp_tp_buynow'] ) : '';
			$instance['cwp_tp_readreview']  = ( ! empty( $new_instance['cwp_tp_readreview'] ) ) ? strip_tags( $new_instance['cwp_tp_readreview'] ) : '';
			$instance['cwp_tp_layout']      = ( ! empty( $new_instance['cwp_tp_layout'] ) ) ? strip_tags( $new_instance['cwp_tp_layout'] ) : '';
			$instance['cwp_tp_rating_type'] = ( ! empty( $new_instance['cwp_tp_rating_type'] ) ) ? strip_tags( $new_instance['cwp_tp_rating_type'] ) : '';
			$instance['related_by']         = isset( $new_instance['related_by'] ) ? $new_instance['related_by'] : 'category';
			return $instance;
		}

		/**
		 * Display method for widget
		 *
		 * @since   2.0.0
		 * @access  public
		 * @param   array $args The widget args.
		 * @param   array $instance The widget instance.
		 * @return mixed
		 */
		public function widget( $args, $instance ) {
			// these are the widget options.
			$instance['title'] = apply_filters( 'widget_title', $instance['title'] );
			$number_posts      = ( ! empty( $instance['number_posts'] ) ) ? absint( $instance['number_posts'] ) : $this->number_posts;
			$this->post_id     = ( is_single() ) ? get_the_ID() : false;
			$this->is_review   = ( ( get_post_meta( $this->post_id, 'cwp_meta_box_check', true ) === 'Yes' ) && $this->post_id ) ? true : false;
			if ( isset( $instance['show_thumb'] ) ) {
				$instance['show_thumb'] = apply_filters( 'widget_content', $instance['show_thumb'] );
			} else {
				$instance['show_thumb'] = false;
			}
			$instance['show_image'] = $instance['show_thumb'];
			if ( isset( $instance['title_type'] ) ) {
				$instance['post_type'] = apply_filters( 'widget_content', $instance['title_type'] );
			} else {
				$instance['post_type'] = false;
			}
			if ( ! isset( $instance['cwp_tp_layout'] ) ) {
				$instance['cwp_tp_layout'] = 'default.php';
			}

			// empty if does not exist review
			if ( ! $this->is_review ) {
				return false;
			}

			echo $args['before_widget'];

			// Check if title is set.
			if ( $instance['title'] ) {
				echo $args['before_title'] . $instance['title'] . $args['after_title'];
			}

			// Show reviews.
			$results = $this->get_reviews( $instance );

			if ( ! empty( $results ) ) {
				$first  = reset( $results );
				$first  = isset( $first['ID'] ) ? $first['ID'] : 0;
				$review = new WPPR_Review_Model( $first );
				$this->assets( $review );
			}

			$template = new WPPR_Template();
			$template->render(
				'widget/' . $instance['cwp_tp_layout'],
				array(
					'results'      => $results,
					'title_length' => self::RESTRICT_TITLE_CHARS,
					'instance'     => $instance,
				)
			);

			   echo $args['after_widget'];
		}

		/**
		 * Utility method to get reviews.
		 *
		 * @since   2.0.0
		 * @access  public
		 * @param   WP_Widget $instance The instance.
		 */
		public function get_reviews( $instance ) {
			$related_by   = isset( $instance['related_by'] ) ? $instance['related_by'] : 'category';
			$number_posts = ( ! empty( $instance['number_posts'] ) ) ? absint( $instance['number_posts'] ) : $this->number_posts;
			$show_thumb   = isset( $instance['show_thumb'] ) ? $instance['show_thumb'] : false;

			$reviews = new WPPR_Query_Model();
			$results = array();
			// let's check if what we want from the query model is supported by it or not.
			if ( method_exists( $reviews, 'supports' ) && $reviews->supports( 'post', 'taxonomy' ) ) {
				$attribs = array();
				switch ( $related_by ) {
					case 'category':
						$attribs['taxonomy'] = 'category';
						$attribs['term_ids'] = wp_get_post_categories( $this->post_id );
						break;
					case 'tag':
						$attribs['taxonomy'] = 'post_tag';
						$attribs['term_ids'] = wp_get_post_tags( $this->post_id, array( 'fields' => 'ids' ) );
						break;
				}

				$attribs['exclude'] = array( $this->post_id );
				$order              = array( 'date' => 'DESC' );
				$results            = $reviews->find( apply_filters( 'wppr_related_reviews_attributes', $attribs ), $number_posts, array(), $order );
			} else {
				// new pro, old lite.
				$args = array(
					'posts_per_page' => $number_posts,
					'post_status'    => 'publish',
					'meta_key'       => 'cwp_meta_box_check',
					'meta_value'     => 'Yes',
					'orderby'        => 'date',
					'order'          => 'DESC',
					'post__not_in'   => array( $this->post_id ),
				);

				switch ( $related_by ) {
					case 'category':
						$args['tax_query'] = array(
							array(
								'taxonomy'         => 'category',
								'field'            => 'id',
								'terms'            => wp_get_post_categories( $this->post_id ),
								'include_children' => false,
							),
						);
						break;
					case 'tag':
						$args['tax_query'] = array(
							array(
								'taxonomy' => 'post_tag',
								'field'    => 'id',
								'terms'    => wp_get_post_tags( $this->post_id, array( 'fields' => 'ids' ) ),
							),
						);
						break;
				}

				$reviews = new WP_Query( apply_filters( 'widget_posts_args', $args ) );

				if ( $reviews->have_posts() ) {
					global $post;
					while ( $reviews->have_posts() ) {
						$reviews->the_post();
						$results[] = array( 'ID' => $post->ID );
					}
					wp_reset_postdata();
				}
			}
			return $results;
		}

		/**
		 * Method for when the plugin is activated.
		 *
		 * @since   2.0.0
		 * @access  public
		 */
		public function widget_admin_notice() {
			// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			if ( isset( $_GET['activate'] ) && $_GET['activate'] == true ) {
				$url_widget = admin_url( 'widgets.php' );
				?>
				<div class="updated">
				<p>
				<?php
                    // @codingStandardsIgnoreStart
					/* translators: %s is replaced a url */
					printf( __( 'Great, now go under <a href="%s">Appearance &#8250 Widgets</a> and place your widget in your sidebar.', 'wp-product-review' ), $url_widget );
                    // @codingStandardsIgnoreEnd
				?>
				</p>
				</div>
				<?php
			}
		}

		/**
		 * Load public assets specific to this widget.
		 *
		 * @since   3.0.0
		 * @access  public
		 */
		public function load_assets() {
			// empty.
		}

		/**
		 * Load admin assets specific to this widget.
		 *
		 * @since   3.0.0
		 * @access  public
		 */
		public function load_admin_assets() {
			// empty.
		}

	}

}
