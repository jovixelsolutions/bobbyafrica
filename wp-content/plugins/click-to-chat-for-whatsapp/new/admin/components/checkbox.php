<?php
/**
 * Checkbox
 *
 * @package Click_To_Chat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$field_title  = ( isset( $input['title'] ) ) ? esc_attr( $input['title'] ) : '';
$parent_class = ( isset( $input['parent_class'] ) ) ? $input['parent_class'] : '';
$label        = ( isset( $input['label'] ) ) ? $input['label'] : '';
$description  = ( isset( $input['description'] ) ) ? $input['description'] : '';



?>
<div class="row ctc_component_checkbox <?php echo esc_attr( $parent_class ); ?>">
	<div class="input-field col s12">
		<p>
			<label class="ctc_checkbox_label">
				<input name="<?php echo esc_attr( $dbrow ); ?>[<?php echo esc_attr( $db_key ); ?>]" type="checkbox" class="<?php echo esc_attr( $db_key ); ?>" value="1" <?php checked( $db_value, 1 ); ?> />
				<span><?php echo esc_html( $field_title ); ?></span>
			</label>
		</p>
		<?php
		if ( '' !== $description ) {
			?>
			<p class="description"><?php echo wp_kses_post( $description ); ?></p>
			<?php
		}
		?>
	</div>
</div>
