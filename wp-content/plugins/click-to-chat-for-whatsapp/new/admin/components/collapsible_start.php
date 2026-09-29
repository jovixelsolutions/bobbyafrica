<?php
/**
 * Collapsible - start code
 *
 * @package Click_To_Chat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$collapsible_title = ( isset( $input['title'] ) ) ? esc_attr( $input['title'] ) : '';

$description = ( isset( $input['description'] ) ) ? $input['description'] : '';

$active      = 'active';
$collapsible = ( isset( $input['collapsible'] ) ) ? $input['collapsible'] : '';
if ( 'no' === $collapsible ) {
	$active = '';
}

$ul_class = ( isset( $input['ul_class'] ) ) ? $input['ul_class'] : '';

?>

<ul class="collapsible <?php echo esc_attr( $ul_class ); ?>">
<li class="<?php echo esc_attr( $active ); ?>">
<div class="collapsible-header" id="showhide_settings"><?php echo esc_html( $collapsible_title ); ?>
	<span class="right_icon dashicons dashicons-arrow-down-alt2"></span>
</div>
<div class="collapsible-body">

<?php
if ( '' !== $description ) {
	?>
	<p class="description"><?php echo wp_kses_post( $description ); ?></p>
	<br>
	<?php
}
