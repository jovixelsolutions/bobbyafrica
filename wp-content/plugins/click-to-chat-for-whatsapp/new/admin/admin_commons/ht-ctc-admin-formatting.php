<?php
/**
 * Formatting API - Admin related.
 *
 * Encode emoji..
 *
 * @package Click_To_Chat
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Encoding emoji.
 *
 * To check the charset and run.
 *
 * @uses wp_encode_emoji
 *       On this page other functions. (so keep this function at the top)
 *
 * @since 3.3.5
 * @param string $value Input value to convert emojis to html entity.
 * @return string Encoded value.
 */
/**
 * Encode emoji for database storage.
 *
 * @param string $value Value to encode.
 * @return string Encoded value.
 */
if ( ! function_exists( 'ht_ctc_wp_encode_emoji' ) ) {
	/**
	 * Encode emoji for database storage.
	 *
	 * @param string $value Value to encode.
	 * @return string Encoded value.
	 */
	function ht_ctc_wp_encode_emoji( $value = '' ) {

		if ( defined( 'DB_CHARSET' ) && 'utf8' === DB_CHARSET ) {

			if ( function_exists( 'wp_encode_emoji' ) ) {
				$value = wp_encode_emoji( $value );
			}
		}

		return $value;
	}
}


/**
 * Sanitize text field - basic sanitize.
 *
 * @since 4.11
 * @param string $value Value to sanitize.
 * @return string Sanitized value.
 */
if ( ! function_exists( 'ht_ctc_sanitize_input_fields' ) ) {
	/**
	 * Sanitize input fields.
	 *
	 * @param string $value Value to sanitize.
	 * @return string Sanitized value.
	 */
	function ht_ctc_sanitize_input_fields( $value = '' ) {

		if ( function_exists( 'sanitize_textarea_field' ) ) {
			$value = sanitize_textarea_field( $value );
		} else {
			$value = sanitize_text_field( $value );
		}

		return $value;
	}
}



/**
 * Sanitize text editor.
 *
 * @since 3.9.3
 * @param string $value Value to sanitize.
 * @return string Sanitized value.
 */
if ( ! function_exists( 'ht_ctc_wp_sanitize_text_editor' ) ) {
	/**
	 * Sanitize text editor content.
	 *
	 * @param string $value Value to sanitize.
	 * @return string Sanitized value.
	 */
	function ht_ctc_wp_sanitize_text_editor( $value = '' ) {

		if ( ! empty( $value ) && '' !== $value ) {

			// Decode first to neutralize any previous encoding
			$value = html_entity_decode( $value );

			if ( function_exists( 'ht_ctc_wp_encode_emoji' ) ) {
				$value = ht_ctc_wp_encode_emoji( $value );
			}

			$allowed_html = wp_kses_allowed_html( 'post' );

			// $allowed_html['iframe'] = array(
			// 'src'             => true,
			// 'height'          => true,
			// 'width'           => true,
			// 'frameborder'     => true,
			// 'allowfullscreen' => true,
			// 'title' => true,
			// 'allow' => true,
			// 'autoplay' => true,
			// 'clipboard-write' => true,
			// 'encrypted-media' => true,
			// 'gyroscope' => true,
			// 'picture-in-picture' => true,
			// );

			$new_value = wp_kses( $value, $allowed_html );
			// htmlentities this $new_value (double security ..)

			$new_value = htmlentities( $new_value );
			// or
			// $new_value = htmlentities($new_value, ENT_QUOTES | ENT_HTML5, 'UTF-8');

			// (may not needed - but extra security - encoding and sanitizing)
			if ( function_exists( 'sanitize_textarea_field' ) ) {
				$new_value = sanitize_textarea_field( $new_value );
			} else {
				$new_value = sanitize_text_field( $new_value );
			}
		}

		return $new_value;
	}
}


/**
 * Sanitize custom CSS code.
 *
 * @uses Admin other settings - options_sanitize - custom css code.
 * @since 4.11
 * @param string $value CSS code to sanitize.
 * @return string Sanitized CSS code.
 */
if ( ! function_exists( 'ht_ctc_sanitize_custom_css_code' ) ) {
	/**
	 * Sanitize custom CSS code.
	 *
	 * @param string $value CSS code to sanitize.
	 * @return string Sanitized CSS code.
	 */
	function ht_ctc_sanitize_custom_css_code( $value = '' ) {

		if ( ! empty( $value ) ) {
			$allowed_html = wp_kses_allowed_html( 'post' );
			$value        = wp_kses( $value, $allowed_html );
		}

		if ( function_exists( 'sanitize_textarea_field' ) ) {
			$value = sanitize_textarea_field( $value );
		} else {
			$value = sanitize_text_field( $value );
		}

		// $value = htmlentities( $value );

		return $value;
	}
}
