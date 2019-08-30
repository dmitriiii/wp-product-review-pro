<style id="wppr-partial-custom-css">
	/* default */
	#review-statistics .review-wrap-up {
		margin: 0 !important;
	}

	#review-statistics .review-wrap-up .review-wu-content {
		border: 0 !important;
	}

	#review-statistics .review-wu-bars {
		border: 0 !important;
		padding: 0 !important;
		float: none !important;
	}

	#review-statistics .review-wu-bars span:last-of-type {
		float: right;
	}

	#review-statistics .review-wrap-up .review-wu-right {
		width: 100% !important;
		padding: 0 !important;
		border: 0 !important;
	}

	/* style1 */
	.wppr-template-1 .wppr-review-grade, .wppr-template-1 .wppr-review-pros-cons {
		margin: 0 !important;
	}

	.wppr-template-1 .wppr-review-grade-options {
		padding: 0 !important;
	}

	/* style2 */
	.wppr-template-2 .wppr-review-option {
		margin: 0 !important;
	}

</style>
<?php
switch ( $review_object->get_template() ) {
	case 'default':
		echo '<div id="review-statistics"><div class="review-wrap-up"><div class="review-wu-content">';
		switch ( $type ) {
			case 'pros-cons':
				echo '<div class="review-wu-right">';
				wppr_layout_get_pros( $review_object, '', 'h2', '' );
				wppr_layout_get_cons( $review_object, '', 'h2', '' );
				echo '</div>';
				break;
			case 'rating':
				echo $template->render(
					'rating-pie',
					array(
						'review_object' => $review_object,
					),
					false
				);
				break;
			case 'rating-options':
				wppr_layout_get_options_ratings( $review_object, 'dashes' );
				break;
		}
		echo '</div></div></div>';
		break;
	case 'style1':
		echo '<div class="wppr-template wppr-template-1"><div class="wppr-review-grade">';
		switch ( $type ) {
			case 'pros-cons':
				echo '<div class="wppr-review-pros-cons">';
				wppr_layout_get_pros( $review_object, '', 'h3', 'wppr-review-pros-name' );
				wppr_layout_get_cons( $review_object, '', 'h3', 'wppr-review-cons-name' );
				echo '</div>';
				break;
			case 'rating':
				wppr_layout_get_rating( $review_object, 'stars', 'style1', false );
				echo '<div class="wppr-review-grade-number">';
				wppr_layout_get_rating( $review_object, 'number', 'style1' );
				echo '</div>';
				break;
			case 'rating-options':
				wppr_layout_get_options_ratings( $review_object, 'bars' );
				break;
		}
		echo '</div></div>';
		break;
	case 'style2':
		echo '<div class="wppr-template wppr-template-2"><div class="wppr-review-head">';
		switch ( $type ) {
			case 'pros-cons':
				wppr_layout_get_pros( $review_object, 'wppr-review-pros', 'h3', 'wppr-review-pros-name' );
				wppr_layout_get_cons( $review_object, 'wppr-review-pros', 'h3', 'wppr-review-cons-name' );
				break;
			case 'rating':
				echo '<div class="wppr-review-rating">';
				wppr_layout_get_rating( $review_object, 'number', 'style2' );
				wppr_layout_get_user_rating( $review_object );
				echo '</div>';
				break;
			case 'rating-options':
				echo '<div class="wppr-review-option">';
				wppr_layout_get_options_ratings( $review_object, 'stars' );
				echo '</div>';
				break;
		}
		echo '</div></div>';
		break;
	default:
		// for custom templates.
		apply_filters( 'wppr_shortcode_show_partial', '', $review_object, $type, $template );
}
?>
