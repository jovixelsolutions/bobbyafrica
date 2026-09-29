<?php
/**
 * Collapsible - end code
 *
 * @package Click_To_Chat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$description = ( isset( $input['description'] ) ) ? $input['description'] : '';

if ( '' !== $description ) {
	?>
	<p class="description"><?php echo wp_kses_post( $description ); ?></p>
	<?php
}
?>

</div>
</li>
</ul>
