<?php
/**
 * Form field view.
 *
 * @package   Category_Image_Video
 * @author    Dmitriy Chekhovkiy <chehovskiy.dima@gmail.com>
 * @license   GPL-2.0+
 * @link      https://github.com/cheh/
 * @copyright 2017 Dmitriy Chekhovkiy
 */
?>

<tr class="form-field cvi-term-<?php echo esc_attr( $key ); ?>-wrap">

	<th scope="row">
		<label for="<?php echo esc_attr( $field['name'] ); ?>"><?php
			echo wp_kses_post( $field['label'] );
		?></label>
	</th>

	<td>
		<?php echo $html; ?>
		<p class="description"><?php echo wp_kses_post( $field['description'] ); ?></p>
	</td>

</tr>
