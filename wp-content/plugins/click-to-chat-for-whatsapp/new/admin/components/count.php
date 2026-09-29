<?php
/**
 * Count field
 * useful to update the settings each time when save changes.. (even if settings or not changed) - to clear cache, ..
 *
 * @package Click_To_Chat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$db_value = intval( $db_value );
$db_value = ++$db_value;
?>
<div class="ctc_count">
	<input name="<?php echo esc_attr( $dbrow ); ?>[count]" value="<?php echo esc_attr( $db_value ); ?>" type="hidden" class="hide">
</div>
