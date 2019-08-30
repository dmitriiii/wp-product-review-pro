<div class="wppr-submit-review-wrap">
	<form method="post" class="wppr-submit-review-form">
		<?php
		if ( ! empty( $args['atts'] ) && ! empty( $args['atts']['title'] ) ) {
			?>
		<h3><?php echo esc_html( $args['atts']['title'] ); ?></h3>
			<?php
		}
		?>
		
		<input type="hidden" class="wppr-submit-review-msg" value="<?php echo $args['atts']['message']; ?>">
		<input type="hidden" name="guest" value="<?php echo $args['atts']['guest'] ? 1 : 0; ?>">

		<div class="wppr-spinner"></div>

		<?php
		$fields      = $args['fields'];
		$html_helper = new WPPR_Html_Fields();

		foreach ( $fields as $field ) {
			$type = $field['type'];
			echo '<div class="div-wppr-submit-review div-' . $field['class'] . '">' . $html_helper->$type( $field ) . '</div>';
		}

		// want to add additional fields e.g. captcha?
		do_action( 'wppr_submit_review_additional_fields', $args['atts'] );
		?>
		<div class="div-wppr-submit-review">
			<input type="submit" class="wppr-submit-review wppr-submit-review-bttn" value="<?php _e( 'Submit Review', 'wp-product-review' ); ?>">
		</div>
	</form>
</div>

