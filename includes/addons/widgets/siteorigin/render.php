<?php
if ( isset( $instance['css'] ) ) {
	echo '<style id="wppr-siteorigin-editor">' . $instance['css'] . '</style>';
}

echo do_shortcode( '[P_REVIEW post_id="' . $instance['id'] . '" visual="' . $instance['visual'] . '" template="' . $instance['template'] . '"]' );

