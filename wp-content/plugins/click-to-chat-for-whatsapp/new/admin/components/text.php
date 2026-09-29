<?php
/**
 * Text
 *
 * @package Click_To_Chat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$field_title  = ( isset( $input['title'] ) ) ? $input['title'] : '';
$description  = ( isset( $input['description'] ) ) ? $input['description'] : '';
$label        = ( isset( $input['label'] ) ) ? $input['label'] : '';
$placeholder  = ( isset( $input['placeholder'] ) ) ? $input['placeholder'] : '';
$parent_class = ( isset( $input['parent_class'] ) ) ? $input['parent_class'] : '';

?>
<div class="row ctc_component_text <?php echo esc_attr( $parent_class ); ?>">
	<div class="input-field col s12">
		<input name="<?php echo esc_attr( $dbrow ); ?>[<?php echo esc_attr( $db_key ); ?>]" type="text" value="<?php echo esc_attr( $db_value ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>"/>
		<label for="pre_filled"><?php echo esc_html( $label ); ?></label>
		<?php
		// if description is set..
		if ( '' !== $description ) {
			?>
			<p class="description"><?php echo wp_kses_post( $description ); ?></p>
			<?php
		}
		?>
	</div>
</div>
