<?php
/**
 *  Widget layout for the admin dashboard.
 *
 * @package     WPPR
 * @subpackage  Layouts
 * @copyright   Copyright (c) 2017, Bogdan Preda
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0.0
 */

// @codingStandardsIgnoreStart
?>
<p>
	<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'wp-product-review' ); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
</p>
<?php
		$related_by   = isset( $instance['related_by'] ) ? $instance['related_by'] : 'category';
		$relations = array(
			'tag'		=> __( 'Tags', 'wp-product-review' ),
			'category'	=> __( 'Category', 'wp-product-review' ),
		);
?>
<p>
	<label for="<?php echo $this->get_field_id( 'related_by' ); ?>"><?php _e( 'Related by', 'wp-product-review' ); ?></label>
	<select class="widefat" id="<?php echo $this->get_field_id( 'related_by' ); ?>" name="<?php echo $this->get_field_name( 'related_by' ); ?>">
	<?php
		foreach ( $relations as $relation => $label ) {
	?>
	<option value="<?php echo $relation;?>" <?php selected( $relation, $related_by );?> ><?php echo $label; ?></option>
	<?php
		}
	?>
	</select>
</p>
<p>
	<label for="<?php echo $this->get_field_id( 'number_posts' ); ?>"><?php _e( 'Number of posts to show:', 'wp-product-review' ); ?></label>
	<input id="<?php echo $this->get_field_id( 'number_posts' ); ?>" name="<?php echo $this->get_field_name( 'number_posts' ); ?>" size="3" type="text" value="<?php echo esc_attr( $instance['number_posts'] ); ?>" />
</p>
<p>
	<?php $cwp_tp_layout = esc_attr( $instance['cwp_tp_layout'] ); ?>
	<label for="<?php echo $this->get_field_id( 'cwp_tp_layout' ); ?>"><?php _e( 'Layout:', 'wp-product-review' ); ?></label>
	<?php
	$layouts            = array();
	$customLayoutFiles  = glob( WPPR_PATH . '/includes/public/layouts/widget/*.php' );
	foreach ( $customLayoutFiles as $file ) {
		$layouts[ basename( $file ) ] = ucwords( basename( $file, '.php' ) );
	}
	foreach ( $layouts as $key => $val ) :
		$extra      = '';
		if ( $key == $cwp_tp_layout ) { $extra = 'checked'; }
		$styleName  = strtolower( str_replace( ' ', '', $val ) );
		$id         = $this->get_field_id( $styleName );
		?>
		<br>
		<input type="radio" name="<?php echo $this->get_field_name( 'cwp_tp_layout' ); ?>" value="<?php echo $key;?>" id="<?php echo $id . 'style'?>" <?php echo $extra;?> class="wppr-stylestyle"><label for="<?php echo $id . 'style';?>" class="wppr-stylestyle"><?php echo $val;?></label>
		<span class="wppr-styleimg" id="<?php echo $id . 'style'?>img">
			<img src="<?php echo WPPR_URL . '/assets/img/' . $styleName . '.png';?>">
		</span>
		<?php
	endforeach;
	?>
</p>
<p class="wppr-customField" style="display: none">
	<?php $cwp_tp_buynow = esc_attr( $instance['cwp_tp_buynow'] ); ?>
	<label for="<?php echo $this->get_field_id( 'cwp_tp_buynow' ); ?>"><?php _e( 'Buy Now text:', 'wp-product-review' ); ?></label>
	<input id="<?php echo $this->get_field_id( 'cwp_tp_buynow' ); ?>" name="<?php echo $this->get_field_name( 'cwp_tp_buynow' ); ?>" class="widefat" type="text" value="<?php echo $cwp_tp_buynow; ?>" />
</p>
<p class="wppr-customField" style="display: none">
	<?php $cwp_tp_readreview = esc_attr( $instance['cwp_tp_readreview'] ); ?>
	<label for="<?php echo $this->get_field_id( 'cwp_tp_readreview' ); ?>"><?php _e( 'Read Review text:', 'wp-product-review' ); ?></label>
	<input id="<?php echo $this->get_field_id( 'cwp_tp_readreview' ); ?>" name="<?php echo $this->get_field_name( 'cwp_tp_readreview' ); ?>" class="widefat" type="text" value="<?php echo $cwp_tp_readreview; ?>" />
</p>
<p>
	<label for="<?php echo $this->get_field_id( 'title_type' ); ?>"><?php _e( 'Display Product Titles :', 'wp-product-review' ); ?></label>
	<input  id="<?php echo $this->get_field_id( 'title_type' ); ?>" name="<?php echo $this->get_field_name( 'title_type' ); ?>"  type="checkbox" <?php checked( $instance['title_type'] ); ?>  />
</p>
<p>
	<label for="<?php echo $this->get_field_id( 'show_thumb' ); ?>"><?php _e( 'Display Product Image :', 'wp-product-review' ); ?></label>
	<input  id="<?php echo $this->get_field_id( 'show_thumb' ); ?>" name="<?php echo $this->get_field_name( 'show_thumb' ); ?>"  type="checkbox" <?php checked( $instance['show_thumb'] ); ?>  />
</p>
<?php // @codingStandardsIgnoreEnd ?>
