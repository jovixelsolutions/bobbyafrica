<?php
/**
 * Add space / line breaks
 *
 * @package Click_To_Chat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$space_type = ( isset( $input['type'] ) ) ? esc_attr( $input['type'] ) : '';

if ( 'line' === $space_type ) {
	?>
	<br>
	<?php
} elseif ( 'margin' === $space_type ) {

	$margin_bottom = ( isset( $input['margin_bottom'] ) ) ? 'margin-bottom: ' . esc_attr( $input['margin_bottom'] ) . ';' : '';

	?>
	<span style="display:block; <?php echo esc_attr( $margin_bottom ); ?>"></span>
	<?php
}
