<?php
/**
 * Empty field. hidden type.. useful to save some value in table instead of keeping empty. to prevent some errors
 *
 * @package Click_To_Chat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$db_value = '1';
?>
<input name="<?php echo esc_attr( $dbrow ); ?>[<?php echo esc_attr( $db_key ); ?>]" type="text" hidden style="display:none;" value="<?php echo esc_attr( $db_value ); ?>"/>
