<?php
/**
 *  WP Product Review front page layout.
 *
 * @package     WPPR
 * @subpackage  Layouts
 * @global      $review_object WPPR_Review_Model
 * @copyright   Copyright (c) 2017, Bogdan Preda
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0.0
 */
?>
<style type="text/css">
	.wppr-inline-pie-chart {
		position: relative;
		width: 120px;
		height: 120px;
	}

	.wppr-inline-pie-chart .wppr-c100 {
		position: absolute;
		top: 0;
		left: 0;
		-webkit-transform: none;
		-ms-transform: none;
		transform: none;
	}
</style>

<?php wppr_layout_get_rating( $review_object, 'donut', 'default', array( 'review-wu-grade', 'wppr-inline-pie-chart' ) ); ?>
