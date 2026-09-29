<?php
/**
 * Template: add element details and summary - end
 *
 * @since 3.35
 * @package Click_To_Chat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$field_title = ( isset( $input['title'] ) ) ? esc_attr( $input['title'] ) : '';
$description = ( isset( $input['description'] ) ) ? $input['description'] : '';
?>

<details class="ctc_details">
	<summary style="margin-bottom:8px;"><?php echo esc_html( $field_title ); ?></summary>
