<?php
/**
 * Deprecated heading component.
 *
 * @deprecated 4.15 Favor new/admin/components/content.php - $title
 * @package Click_To_Chat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$heading_title = ( isset( $input['title'] ) ) ? $input['title'] : '';
$parent_class  = ( isset( $input['parent_class'] ) ) ? $input['parent_class'] : '';

?>

<div class="row ctc_component_heading <?php echo esc_attr( $parent_class ); ?>">
	<p class="description ht_ctc_subtitle"><?php echo esc_html( $heading_title ); ?> </p>
</div>
