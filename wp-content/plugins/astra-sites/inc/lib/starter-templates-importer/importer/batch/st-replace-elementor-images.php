<?php
/**
 * AI content generator and replacer file.
 *
 * @package {{package}}
 * @since 1.1.13
 */

namespace STImporter\Importer\Batch;

use STImporter\Importer\Batch\ST_Replace_Images;
use STImporter\Importer\ST_Importer_Helper;
use STImporter\Importer\ST_Importer_Log;


if ( ! class_exists( 'ST_Replace_Elementor_Images' ) ) :

	/**
	 * Block Editor Blocks Replacer
	 *
	 * @since 1.1.13
	 */
	class ST_Replace_Elementor_Images {

		/**
		 * Instance
		 *
		 * @since 1.1.13
		 * @access private
		 * @var object Class object.
		 */
		private static $instance = null;

		/**
		 * Initiator
		 *
		 * @since 1.1.13
		 * @return object initialized object of class.
		 */
		public static function get_instance() {

			if ( null === self::$instance ) {
				self::$instance = new self();
				ST_Importer_Log::add( 'ST_Replace_Elementor_Images instance created' );
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @since 1.1.13
		 */
		public function __construct() {}

		/**
		 * Replace the content with AI generated data in Elementor Pages.
		 *
		 * @param object $post Post object.
		 *
		 * @since 1.1.13
		 * @return void
		 */
		public function parse_replace_images( $post ) {

			$post_id        = $post->ID ?? 0;
			$elementor_data = get_post_meta( $post_id, '_elementor_data', true );

			ST_Importer_Log::add( 'Starting Elementor image replacement for post ID: ' . $post_id );

			if ( empty( $elementor_data ) ) {
				ST_Importer_Log::add( 'No Elementor data found for post ID: ' . $post_id . ' - returning early' );
				return;
			}

			$data = is_array( $elementor_data ) ? $elementor_data : json_decode( $elementor_data, true );

			if ( json_last_error() !== JSON_ERROR_NONE ) {
				ST_Importer_Log::add( 'JSON decode error for post ID: ' . $post_id . ' - Error: ' . json_last_error_msg() . ' - returning early' );
				return;
			}

			ST_Importer_Log::add( 'Successfully decoded Elementor data for post ID: ' . $post_id . ' - processing widgets' );

			$this->process_elementor_widgets( $data );

			$updated_data = wp_json_encode( $data );

			if ( json_last_error() === JSON_ERROR_NONE ) {
				update_metadata( 'post', $post_id, '_elementor_data', wp_slash( $updated_data ) );
				ST_Importer_Log::add( 'Successfully updated Elementor data for post ID: ' . $post_id );
			} else { // @phpstan-ignore-line - else branch is reachable if json_encode fails
				ST_Importer_Log::add( 'Failed to encode updated Elementor data for post ID: ' . $post_id . ' - Error: ' . json_last_error_msg() );
			}

			if ( class_exists( '\Elementor\Plugin' ) ) {
				$elementor = \Elementor\Plugin::instance();
				$elementor->files_manager->clear_cache();
				ST_Importer_Log::add( 'Cleared Elementor cache for post ID: ' . $post_id );
			} else {
				ST_Importer_Log::add( 'Elementor Plugin class not found - skipping cache clear for post ID: ' . $post_id );
			}
		}

		/**
		 * Replace placeholder strings in the format {{STRING}} with actual values.
		 *
		 * @param string               $key The key to process.
		 * @param array<string, mixed> $value The value to process.
		 * @return mixed The replaced value.
		 */
		public function replace_widget_image( $key, $value ) {

			ST_Importer_Log::add( 'Processing widget image replacement for key: ' . $key );

			switch ( $key ) {

				case 'background_slideshow_gallery':
				case 'wp_gallery':
					ST_Importer_Log::add( 'Processing gallery images for key: ' . $key . ' - Total images: ' . count( $value ) );
					$replaced_count = 0;
					$skipped_count  = 0;

					foreach ( $value as $index => $image ) {
						if ( ! empty( $image['url'] ) && ! ST_Replace_Images::is_skipable( $image['url'] ) ) {
							ST_Importer_Log::add( 'Replacing gallery image at index ' . $index . ' - Original URL: ' . $image['url'] );
							$attachment             = $this->get_new_image_attachment( $image );
							$value[ $index ]['url'] = $attachment['url'];
							$value[ $index ]['id']  = $attachment['id'];
							ST_Importer_Log::add( 'Gallery image replaced - New URL: ' . $attachment['url'] . ' - New ID: ' . $attachment['id'] );
							$replaced_count++;
						} else {
							$skipped_url = ! empty( $image['url'] ) ? $image['url'] : 'empty URL';
							ST_Importer_Log::add( 'Skipped gallery image at index ' . $index . ' - URL: ' . $skipped_url );
							$skipped_count++;
						}
					}
					ST_Importer_Log::add( 'Gallery processing complete for key: ' . $key . ' - Replaced: ' . $replaced_count . ' - Skipped: ' . $skipped_count );
					break;

				case 'background_image':
				case 'background_overlay_image':
				case 'image':
					if ( ! empty( $value['url'] ) && ! ST_Replace_Images::is_skipable( $value['url'] ) ) {
						ST_Importer_Log::add( 'Replacing single image for key: ' . $key . ' - Original URL: ' . $value['url'] );
						$attachment   = $this->get_new_image_attachment( $value );
						$value['url'] = $attachment['url'];
						$value['id']  = $attachment['id'];
						ST_Importer_Log::add( 'Single image replaced for key: ' . $key . ' - New URL: ' . $attachment['url'] . ' - New ID: ' . $attachment['id'] );
					} else {
						$skipped_url = ! empty( $value['url'] ) ? $value['url'] : 'empty URL';
						ST_Importer_Log::add( 'Skipped single image for key: ' . $key . ' - URL: ' . $skipped_url );
					}
					break;

				case 'wp':
					if ( ! empty( $value['url'] ) && ! ST_Replace_Images::is_skipable( $value['url'] ) ) {
						ST_Importer_Log::add( 'Replacing WP image for key: ' . $key . ' - Original URL: ' . $value['url'] );
						$attachment             = $this->get_new_image_attachment( $value );
						$value['url']           = $attachment['url'];
						$value['attachment_id'] = $attachment['id'];
						ST_Importer_Log::add( 'WP image replaced for key: ' . $key . ' - New URL: ' . $attachment['url'] . ' - New ID: ' . $attachment['id'] );
					} else {
						$skipped_url = ! empty( $value['url'] ) ? $value['url'] : 'empty URL';
						ST_Importer_Log::add( 'Skipped WP image for key: ' . $key . ' - URL: ' . $skipped_url );
					}
					break;

				default:
					ST_Importer_Log::add( 'No image replacement needed for key: ' . $key . ' - using default case' );
					break;

			}

			return $value;

		}

		/**
		 * Replace the image URL with the AI generated image.
		 *
		 * @param array<string, mixed> $old_image The old image URL.
		 * @return array<string, mixed> The replaced image URL.
		 */
		public function get_new_image_attachment( $old_image ) {

			$current_index = ST_Replace_Images::$image_index;
			ST_Importer_Log::add( 'Getting new image attachment - Current image index: ' . $current_index );

			$image = ST_Replace_Images::get_instance()->get_image( ST_Replace_Images::$image_index );

			if ( empty( $image ) || ! is_array( $image ) ) {
				ST_Importer_Log::add( 'Failed to get image from index ' . $current_index . ' - returning old image' );
				return $old_image;
			}

			ST_Importer_Log::add( 'Retrieved image data from index ' . $current_index . ' - attempting download' );

			$image = ST_Importer_Helper::download_image( $image );

			if ( is_wp_error( $image ) ) {
				ST_Importer_Log::add( 'Image download failed for index ' . $current_index . ' - Error: ' . $image->get_error_message() . ' - returning old image' );
				return $old_image;
			}

			ST_Importer_Log::add( 'Image downloaded successfully - Attachment ID: ' . $image );

			$attachment = wp_prepare_attachment_for_js( absint( $image ) );

			if ( ! is_array( $attachment ) || empty( $attachment['url'] ) ) {
				ST_Importer_Log::add( 'Failed to prepare attachment for JS - Attachment ID: ' . $image . ' - returning old image' );
				return $old_image;
			}

			ST_Replace_Images::get_instance()->increment_image_index();
			$new_index = ST_Replace_Images::$image_index;

			ST_Importer_Log::add( 'Successfully created new attachment - ID: ' . $attachment['id'] . ' - URL: ' . $attachment['url'] . ' - Image index incremented from ' . $current_index . ' to ' . $new_index );

			return $attachment;
		}

		/**
		 * Process widgets recursively to replace strings in settings.
		 *
		 * @param array<int, array<mixed>> $widgets The widgets array to process.
		 *
		 * @return void
		 */
		public function process_elementor_widgets( &$widgets ) {

			$widget_count = count( $widgets );
			ST_Importer_Log::add( 'Processing Elementor widgets - Total widgets at this level: ' . $widget_count );

			$processed_count = 0;

			foreach ( $widgets as &$widget ) {
				$widget_type = $widget['widgetType'] ?? $widget['elType'] ?? 'unknown';
				ST_Importer_Log::add( 'Processing widget #' . ( $processed_count + 1 ) . ' - Type: ' . $widget_type );

				if ( isset( $widget['settings'] ) && is_array( $widget['settings'] ) ) {
					$settings_count = count( $widget['settings'] );
					ST_Importer_Log::add( 'Widget has settings array with ' . $settings_count . ' items' );

					foreach ( $widget['settings'] as $key => $value ) {
						if ( is_array( $value ) ) {
							ST_Importer_Log::add( 'Processing setting key: ' . $key . ' (array value)' );
							$widget['settings'][ $key ] = $this->replace_widget_image( $key, $value );
						}
					}
				} else {
					ST_Importer_Log::add( 'Widget has no settings array or settings is not an array' );
				}

				if ( isset( $widget['elements'] ) && is_array( $widget['elements'] ) ) {
					$child_elements = count( $widget['elements'] );
					ST_Importer_Log::add( 'Widget has ' . $child_elements . ' child elements - processing recursively' );
					$this->process_elementor_widgets( $widget['elements'] );
				}

				if ( isset( $widget['widgetType'] ) && 'google_maps' === $widget['widgetType'] ) {
					ST_Importer_Log::add( 'Found Google Maps widget - replacing address' );
					$this->replace_google_map_address( $widget );
				}

				if ( isset( $widget['widgetType'] ) && 'social-icons' === $widget['widgetType'] ) {
					ST_Importer_Log::add( 'Found Social Icons widget - replacing social profiles' );
					$this->replace_social_icons( $widget );
				}

				$processed_count++;
			}

			ST_Importer_Log::add( 'Completed processing ' . $processed_count . ' widgets at this level' );
		}

		/**
		 * Replace the google address.
		 *
		 * @param array<string, mixed> $widget The widget array to process.
		 * @return void
		 */
		public function replace_google_map_address( &$widget ) {

			ST_Importer_Log::add( 'Replacing Google Maps address' );

			$address = ST_Importer_Helper::get_business_details( 'business_address' );
			if ( ! empty( $address ) ) {
				$old_address                   = $widget['settings']['address'] ?? 'not set';
				$widget['settings']['address'] = $address;
				ST_Importer_Log::add( 'Google Maps address replaced - Old: ' . ( is_string( $old_address ) ? $old_address : wp_json_encode( $old_address ) ) . ' - New: ' . ( is_string( $address ) ? $address : wp_json_encode( $address ) ) );
			} else {
				ST_Importer_Log::add( 'No business address available - Google Maps address not replaced' );
			}
		}


		/**
		 * Replace social icons.
		 *
		 * @param array<string, mixed> $widget The widget array to process.
		 * @return void
		 */
		public function replace_social_icons( &$widget ) {
			ST_Importer_Log::add( 'Starting social icons replacement' );

			$social_profile    = ST_Importer_Helper::get_business_details( 'social_profiles' );
			$social_icons_list = $widget['settings']['social_icon_list'];

			if ( ! is_array( $social_profile ) ) {
				$social_profile = [];
				ST_Importer_Log::add( 'No social profiles found or invalid format - using empty array' );
			} else {
				ST_Importer_Log::add( 'Retrieved ' . count( $social_profile ) . ' social profiles from business details' );
			}

			if ( ! is_array( $social_icons_list ) ) {
				$social_icons_list = [];
				ST_Importer_Log::add( 'Social icons list is not an array - using empty array' );
			} else {
				ST_Importer_Log::add( 'Widget has ' . count( $social_icons_list ) . ' existing social icons' );
			}

			$existing_icons = [];
			$profile_map    = [];

			foreach ( $social_profile as $profile ) {
				$profile_map[ 'fab fa-' . $profile['type'] ] = $profile['url'];
			}
			ST_Importer_Log::add( 'Created profile map with ' . count( $profile_map ) . ' entries' );

			$updated_count = 0;
			$removed_count = 0;

			foreach ( $social_icons_list as $index => $icon ) {
				$icon_type = $icon['social_icon']['value'];

				if ( isset( $profile_map[ $icon_type ] ) ) {
					$old_url = $icon['link']['url'] ?? 'not set';
					$widget['settings']['social_icon_list'][ $index ]['link']['url'] = $profile_map[ $icon_type ];
					$existing_icons[] = $icon_type;
					ST_Importer_Log::add( 'Updated social icon: ' . $icon_type . ' - Old URL: ' . $old_url . ' - New URL: ' . $profile_map[ $icon_type ] );
					$updated_count++;
				} else {
					unset( $widget['settings']['social_icon_list'][ $index ] );
					ST_Importer_Log::add( 'Removed social icon (no matching profile): ' . $icon_type );
					$removed_count++;
				}
			}

			$added_count = 0;

			foreach ( $social_profile as $profile ) {
				$icon_class = 'fab fa-' . $profile['type'];

				if ( ! in_array( $icon_class, $existing_icons, true ) ) {
					$widget['settings']['social_icon_list'][] = [
						'_id'         => uniqid(),
						'social_icon' => [
							'value'   => $icon_class,
							'library' => 'fa-brands',
						],
						'link'        => [
							'url'               => $profile['url'],
							'is_external'       => true,
							'nofollow'          => '',
							'custom_attributes' => '',
						],
					];
					ST_Importer_Log::add( 'Added new social icon: ' . $icon_class . ' - URL: ' . $profile['url'] );
					$added_count++;
				}
			}

			$widget['settings']['social_icon_list'] = array_values( $widget['settings']['social_icon_list'] );
			$final_count                            = count( $widget['settings']['social_icon_list'] );

			ST_Importer_Log::add( 'Social icons replacement complete - Updated: ' . $updated_count . ' - Removed: ' . $removed_count . ' - Added: ' . $added_count . ' - Final count: ' . $final_count );
		}
	}
	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	ST_Replace_Elementor_Images::get_instance();

endif;
