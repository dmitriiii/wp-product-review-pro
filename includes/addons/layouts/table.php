<?php
/**
 * Layout for the Comparison Table Addon
 *
 * @since   2.0.0
 * @package WPPR_Pro
 */
$options = new WPPR_Options_Model();
global $content_width;
// @codingStandardsIgnoreStart

$show_description = 'force' === $options->wppr_get_option( 'cwppose_view_description' ) || ( 'yes' === $options->wppr_get_option( 'cwppose_view_description' ) && $content_width > $min_width );

$col_index = 1;
?>
<table class='cwppose_reviews_table' id='tablesorter'>
	<thead>
	<tr>
		<th data-col-index="<?php echo $col_index++;?>">#</th>
		<th data-col-index="<?php echo $col_index++;?>"><?php echo __( $options->wppr_get_option( 'cwppose_lang_name' ), 'wp-product-review' ); ?></th>
		<th data-col-index="<?php echo $col_index++;?>"><?php echo __( $options->wppr_get_option( 'cwppose_lang_rating' ), 'wp-product-review' ); ?></th>
		<?php
			if ( $show_description ) {
		?>
		<th data-col-index="<?php echo $col_index++;?>"><?php echo __( $options->wppr_get_option( 'cwppose_lang_description' ), 'wp-product-review' ); ?></th>
		<?php
			}

			if ( $options->wppr_get_option( 'cwppose_view_price' ) == 'yes' ) : ?>
			<th data-col-index="<?php echo $col_index++;?>"><?php echo __( $options->wppr_get_option( 'cwppose_lang_price' ), 'wp-product-review' ); ?></th>
		<?php endif;
		if ( $options->wppr_get_option( 'cwppose_view_options' ) == 'yes' ) : ?>
			<th data-col-index="<?php echo $col_index++;?>" class="option_thead wppr-col-options"><?php echo __( $options->wppr_get_option( 'cwppose_lang_statistics' ), 'wp-product-review' ); ?></th>
		<?php endif;
		if ( $options->wppr_get_option( 'cwppose_view_link' ) !== 'no' ) : ?>
			<th data-col-index="<?php echo $col_index++;?>"><?php echo __( $options->wppr_get_option( 'cwppose_lang_link' ), 'wp-product-review' ); ?></th>
		<?php endif; ?>
	</tr>
	</thead>
	<tbody>
	<?php
	if ( $results ) :
		$n = 1;
		foreach ( $results as $review ) :
			$review_object = new WPPR_Review_Model( $review['ID'] );
			$rating = $review_object->get_rating();
			?>
			<tr>
				<td><?php echo $n ++; ?></td>
				<td>
					<?php if ( isset( $arguments['img'] ) && $arguments['img'] == 'yes' ) :
						if ( $review_object->hide_name() ) :
						?>
						<h2 class="cwppose_hide wppr-comparison-title-with-image"><?php echo $review_object->get_name(); ?></h2>
						<?php else : ?>
						<h2 class="wppr-comparison-title-with-image"><?php echo $review_object->get_name(); ?></h2>
						<?php endif; ?>
						<img class="wppr-comparison-image" src='<?php echo $review_object->get_small_thumbnail(); ?>' alt='<?php echo $review_object->get_image_alt() ?>'>
					<?php else : ?>
						<h2 class="wppr-comparison-title-without-image"><?php echo $review_object->get_name(); ?></h2>
					<?php endif; ?>
				</td>
				<td data-order="<?php echo $rating;?>">
					<?php
					if ( $rating >= 20 ) : ?>
						<?php wppr_display_rating_stars( 'comparison-table', $review_object, false ); ?>
					<?php endif; ?>
					<a href='<?php echo get_permalink( $review_object->get_ID() ); ?>'
					   title='<?php echo $options->wppr_get_option( 'cwppose_lang_read_review' ); ?>' class='review'
					   style='color:<?php echo $options->wppr_get_option( 'cwppose_read_review_color' ); ?>;'><?php echo $options->wppr_get_option( 'cwppose_lang_read_review' ); ?></a>
				</td>
				<?php if ( $show_description ) { ?>
					<td><?php echo $review_object->get_excerpt(); ?></td>
				<?php }

				if ( $options->wppr_get_option( 'cwppose_view_price' ) == 'yes' ) { ?>
					<td data-order="<?php echo $review_object->get_price();?>"><p><?php echo $review_object->get_price_raw(); ?></p></td>
				<?php }
				if ( $options->wppr_get_option( 'cwppose_view_options' ) == 'yes' ) : ?>
					<td>
						<?php
						$options_rates = $review_object->get_options();
						if ( ! empty( $options_rates ) ) {
							foreach ( $options_rates as $option ) {
								$value = round( $option['value'], 2 );
								/* Sett color for option bar by value */
								if ( $value > 0 && $value <= 25 ) {
									$option_color = $options->wppr_get_option( 'cwppos_rating_weak' );
								} elseif ( $value > 25 && $value <= 50 ) {
									$option_color = $options->wppr_get_option( 'cwppos_rating_notbad' );
								} elseif ( $value > 50 && $value <= 75 ) {
									$option_color = $options->wppr_get_option( 'cwppos_rating_good' );
								} elseif ( $value > 75 ) {
									$option_color = $options->wppr_get_option( 'cwppos_rating_very_good' );
								}
								?>
								<div class='option_group cwppose_clearfix'>
									<div class='option'><?php echo $option['name']; ?></div>
									<div class='bar'>
										<div class="grade">
											<div class='color' style='width:<?php echo $value; ?>%; background:<?php echo $option_color; ?>;'></div>
											<div class='noncolor' style='width:<?php echo (100 - $value); ?>%;'></div>
										</div>
										<div class='gradetext'><?php echo $value; ?></div>
									</div>
								</div><!--/div.option_group .cwppose_clearfix-->
								<?php
							}
						}
						?>
					</td>
				<?php endif;
				if ( $options->wppr_get_option( 'cwppose_view_link' ) !== 'no' ) {
				?>
				<td>
					<?php
					$links = $review_object->get_links();
					if ( ! empty( $links ) ) {
						$name = key( $links );
						$link = reset( $links );
						if ( empty( $link ) || '#' === $link ) {
							continue;
						}
						// TODO: this behavior is different from that of the listing table as the name does not supercede here.
						if ( isset( $arguments['button'] ) ) {
							$name = $arguments['button'];
						} elseif ( $options->wppr_get_option( 'cwppose_lang_button' ) ) {
							$name = $options->wppr_get_option( 'cwppose_lang_button' );
						} elseif ( empty( $name ) ) {
							$name = __( 'Buy Now!', 'wp-product-review' );
						}

						?>
						<a href='<?php echo $link; ?>' title='<?php echo $name; ?>' rel='nofollow noopener' target='_blank'
						   class='cwppose_affiliate_button'><?php echo esc_attr( $name ); ?></a>
						<?php
					}
					?>
				</td>
				<?php 
				}
				?>
			</tr>
		<?php endforeach;
	endif; ?>
	</tbody>
</table><!--/table.cwppose_reviews_table-->
<?php
// @codingStandardsIgnoreEnd
?>
