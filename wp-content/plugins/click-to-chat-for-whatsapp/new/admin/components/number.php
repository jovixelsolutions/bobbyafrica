<?php
/**
 * Number
 *
 * @package Click_To_Chat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$component_title = ( isset( $input['title'] ) ) ? esc_attr( $input['title'] ) : '';
$description     = ( isset( $input['description'] ) ) ? esc_attr( $input['description'] ) : '';
$label           = ( isset( $input['label'] ) ) ? esc_attr( $input['label'] ) : '';
$placeholder     = ( isset( $input['placeholder'] ) ) ? esc_attr( $input['placeholder'] ) : '';

$min          = ( isset( $input['min'] ) ) ? esc_attr( $input['min'] ) : '';
$parent_class = ( isset( $input['parent_class'] ) ) ? $input['parent_class'] : '';

$attr = '';

if ( '' !== $min ) {
	$attr .= " min=$min ";
}

?>
<div class="row ctc_component_number <?php echo esc_attr( $parent_class ); ?>">
	<div class="input-field col s12">
		<input name="<?php echo esc_attr( $dbrow ); ?>[<?php echo esc_attr( $db_key ); ?>]" type="number" <?php echo esc_attr( $attr ); ?> value="<?php echo esc_attr( $db_value ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>"/>
		<label for="pre_filled"><?php echo esc_html( $label ); ?></label>
		<p class="description"><?php echo esc_html( $description ); ?></p>
	</div>
</div>
