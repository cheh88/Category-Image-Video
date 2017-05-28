<?php
/**
 * Image view.
 *
 * @package   Category_Image_Video
 * @author    Dmitriy Chekhovkiy <chehovskiy.dima@gmail.com>
 * @license   GPL-2.0+
 * @link      https://github.com/cheh/
 * @copyright 2017 Dmitriy Chekhovkiy
 */
?>
<div id="cvi-image-container" class="cvi-img-container">

	<?php if ( $image_is_set ) { ?>

		<img src="<?php echo esc_url( $image_src[0] ); ?>" alt="">

	<?php } else { ?>

	<div class="cvi-img-comtainer__placeholder"><?php echo esc_html__( 'No image selected', 'category-image-video' ); ?></div>

	<?php } ?>
</div>

<p class="hide-if-no-js">
	<button type="button" id="cvi-image-add" class="button button-secondary<?php if ( $image_is_set  ) { echo ' hidden'; } ?>">
		<?php echo esc_html__( 'Set image', 'category-image-video' ); ?>
	</button>
	<button type="button" id="cvi-image-remove" class="button button-secondary<?php if ( ! $image_is_set  ) { echo ' hidden'; } ?>">
		<?php echo esc_html__( 'Remove image', 'category-image-video' ); ?>
	</button>
</p>

<input type="hidden" id="<?php echo esc_attr( $field_args['name'] ); ?>" name="<?php echo esc_attr( $field_args['name'] ); ?>" value="<?php echo esc_attr( $image_id ); ?>">