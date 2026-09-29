<?php
/**
 * Greetings call to action - style - 1
 *
 * @package Click_To_Chat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$opt_in = 'Privacy Policy';

if ( isset( $ht_ctc_greetings ) && isset( $ht_ctc_greetings['opt_in'] ) ) {
	$opt_in = $ht_ctc_greetings['opt_in'];
}

$opt_id = ( isset( $opt_in_id ) ) ? $opt_in_id : 'ctc_opt';

?>
<div class="ctc_opt_in" style="display:none; text-align:center;">
	<div class="<?php echo esc_attr( $opt_id ); ?>" style="display:inline-flex;justify-content:center;align-items:center;padding:0 4px;">
		<input type="checkbox" name="" id="<?php echo esc_attr( $opt_id ); ?>" style="margin: 0 5px;">
		<?php if ( ! empty( $opt_in ) ) { ?>
			<label for="<?php echo esc_attr( $opt_id ); ?>"><?php echo wp_kses_post( $opt_in ); ?></label>
		<?php } ?>
	</div>
</div>
