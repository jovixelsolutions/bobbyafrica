<?php
/**
 * SureCart blocks batch import tasks.
 *
 * @package Astra Sites
 * @since 1.1.26
 */

namespace STImporter\Importer\Batch;

use STImporter\Importer\ST_Importer_Helper;

if ( class_exists( 'ST_Batch_SureCart_Blocks' ) ) {
	return;
}

/**
 * ST_Batch_SureCart_Blocks
 *
 * @since 1.1.26
 */
class ST_Batch_SureCart_Blocks {

	/**
	 * Instance
	 *
	 * @since 1.1.26
	 * @var object Class object.
	 * @access private
	 */
	private static $instance = null;

	/**
	 * Initiator
	 *
	 * @since 1.1.26
	 * @return object initialized object of class.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 *
	 * @since 1.1.26
	 */
	public function __construct() {
		// Hook into SureCart product sync to handle block replacements.
		add_action( 'surecart/product/sync/created', array( $this, 'on_surecart_product_synced' ), 10, 2 );
	}

	/**
	 * Handle SureCart product sync
	 *
	 * This runs when each SureCart product is synced/created.
	 *
	 * @param \WP_Post                 $post    The synced post.
	 * @param \SureCart\Models\Product $product The product model.
	 * @return void
	 */
	public function on_surecart_product_synced( $post, $product ) { // phpcs:ignore Generic.Functions.OpeningFunctionBraceKernighanRitchie.ContentAfterBrace, @phpstan-ignore-line -- product type exists.
		// Get the mapping data.
		$surecart_mapping_data = get_option( 'astra_sites_surecart_mapping_data', array() );

		if ( empty( $surecart_mapping_data ) || ! is_array( $surecart_mapping_data ) ) {
			return;
		}

		$product_slug = $product->slug ?? $post->post_name; // @phpstan-ignore-line -- slug property exists on product model.

		// Check if this product is in our mapping.
		if ( empty( $product_slug ) || ! isset( $surecart_mapping_data[ $product_slug ] ) ) {
			return;
		}

		$mapping_info = $surecart_mapping_data[ $product_slug ];
		$old_post_id  = $mapping_info['old_post_id'] ?? null;
		$old_sc_id    = $mapping_info['old_sc_id'] ?? null;
		$new_post_id  = $post->ID;
		$new_sc_id    = get_post_meta( $new_post_id, 'sc_id', true );

		// Build the ID mappers.
		$mappers = array(
			'post_ids'  => array(),
			'sc_ids'    => array(),
			'price_ids' => array(),
		);

		if ( ! empty( $old_post_id ) ) {
			$mappers['post_ids'][ $old_post_id ] = $new_post_id;
		}

		if ( ! empty( $old_sc_id ) ) {
			$mappers['sc_ids'][ $old_sc_id ] = $new_sc_id;
		}

		$prices = $product->getAttribute( 'prices' ); // @phpstan-ignore-line -- prices attribute exists.
		if ( is_array( $prices->data ) ) {
			foreach ( $prices->data as $index => $price ) {
				$old_price_id = $mapping_info['prices'][ $index ]['old_price_id'] ?? null;
				$new_price_id = $price->getAttribute( 'id' ) ?? null;
				if ( ! empty( $old_price_id ) && ! empty( $new_price_id ) ) {
					$mappers['price_ids'][ $old_price_id ] = $new_price_id;
				}
			}
		}

		$this->replace_surecart_ids( $mappers );
	}

	/**
	 * Replace SureCart IDs in imported posts
	 *
	 * @param array<string, array<int|string, int|string>> $mappers Array containing 'post_ids', 'sc_ids', and 'price_ids' mappers.
	 *
	 * @return void
	 */
	private function replace_surecart_ids( $mappers ) {
		// If no mappings, nothing to do.
		if ( empty( $mappers['post_ids'] ) && empty( $mappers['sc_ids'] ) && empty( $mappers['price_ids'] ) ) {
			return;
		}

		// Get only imported posts that contain SureCart blocks.
		global $wpdb;
		$imported_posts = $wpdb->get_results(
			"SELECT p.ID, p.post_content
			FROM {$wpdb->posts} p
			INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
			WHERE pm.meta_key = '_astra_sites_imported_post'
			AND p.post_content LIKE '%wp:surecart/%'"
		);

		// Process each imported post.
		foreach ( $imported_posts as $post ) {
			$content = $post->post_content;

			// Parse blocks and process them.
			$blocks          = parse_blocks( $content );
			$content_updated = $this->process_blocks_recursive( $blocks, $mappers );

			// Update post content if changes were made.
			if ( $content_updated ) {
				$new_content = serialize_blocks( $blocks );
				// Preserve \uXXXX JSON unicode escapes so they survive stripslashes() inside wp_update_post().
				$new_content = ST_Importer_Helper::preserve_block_unicode_escapes( $new_content );

				wp_update_post(
					array(
						'ID'           => $post->ID,
						'post_content' => $new_content,
					)
				);
			}
		}
	}

	/**
	 * Process blocks recursively to replace IDs
	 *
	 * @param array<int, array<string, mixed>>             $blocks  The blocks array.
	 * @param array<string, array<int|string, int|string>> $mappers Array containing 'post_ids' and 'price_ids' mappers.
	 *
	 * @return bool Whether any changes were made.
	 */
	private function process_blocks_recursive( &$blocks, $mappers ) {
		$content_updated = false;

		foreach ( $blocks as &$block ) {
			// Process surecart/product-list blocks.
			if ( 'surecart/product-list' === $block['blockName'] && ! empty( $mappers['post_ids'] ) ) {
				if ( isset( $block['attrs']['query']['include'] ) && is_array( $block['attrs']['query']['include'] ) && ! empty( $block['attrs']['query']['include'] ) ) {
					$updated_include = array();
					foreach ( $block['attrs']['query']['include'] as $old_id ) {
						$updated_include[] = $mappers['post_ids'][ $old_id ] ?? $old_id;
					}

					// Check if there were actual changes.
					if ( $updated_include !== $block['attrs']['query']['include'] ) {
						$block['attrs']['query']['include'] = $updated_include;
						$content_updated                    = true;
					}
				}
			}

			// Process surecart/buy-button blocks.
			if ( 'surecart/buy-button' === $block['blockName'] && ! empty( $mappers['price_ids'] ) ) {
				if ( isset( $block['attrs']['line_items'] ) && is_array( $block['attrs']['line_items'] ) ) {
					foreach ( $block['attrs']['line_items'] as &$line_item ) {
						if ( isset( $line_item['id'] ) && isset( $mappers['price_ids'][ $line_item['id'] ] ) ) {
							$line_item['id'] = $mappers['price_ids'][ $line_item['id'] ];
							$content_updated = true;
						}
					}
				}
			}

			// Recursively process inner blocks.
			if ( ! empty( $block['innerBlocks'] ) ) {
				$inner_updated   = $this->process_blocks_recursive( $block['innerBlocks'], $mappers );
				$content_updated = $content_updated || $inner_updated;
			}
		}

		return $content_updated;
	}
}

/**
 * Kicking this off by calling 'get_instance()' method
 */
ST_Batch_SureCart_Blocks::get_instance();
