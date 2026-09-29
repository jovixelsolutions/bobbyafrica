<?php
/**
 * Content Generation Utils
 *
 * Utils module class for handling content generation functionality.
 *
 * @package SureRank\Inc\Modules\Content_Generation
 * @since 1.4.2
 */

namespace SureRank\Inc\Modules\Content_Generation;

use SureRank\Inc\Traits\Get_Instance;
use SureRank\Inc\Functions\API_Utils;
use SureRank\Inc\ThirdPartyIntegrations\Multilingual\Post_Language_Resolver;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Utils class
 *
 * Main module class for content generation functionality.
 * Extends API_Utils for shared API functionality.
 */
class Utils extends API_Utils {

	use Get_Instance;

	/**
	 * Get API types.
	 *
	 * @return array<int,string> Array of API types.
	 * @since 1.4.2
	 */
	public function get_api_types() {
		return apply_filters(
			'surerank_content_generation_types',
			[
				'page_title',
				'home_page_title',
				'page_description',
				'home_page_description',
				'home_page_social_title',
				'home_page_social_description',
				'social_title',
				'social_description',
				'site_tag_line',
				'page_url_slug',
			]
		);
	}

	/**
	 * Prepare inputs for content generation.
	 *
	 * @param int|null $id Post or term ID (optional).
	 * @param bool     $is_taxonomy Whether the ID is for a taxonomy term.
	 * @return array<string,string> Array of inputs for content generation.
	 * @since 1.4.2
	 */
	public function prepare_content_inputs( $id = null, $is_taxonomy = false ) {
		$title   = '';
		$content = '';
		$type    = '';

		if ( ! empty( $id ) ) {
			if ( $is_taxonomy ) {
				$term = get_term( $id );
				if ( $term && ! is_wp_error( $term ) ) {
					$title   = $term->name;
					$content = $term->description;

					$taxonomy_obj = get_taxonomy( $term->taxonomy );
					if ( $taxonomy_obj ) {
						$readable_name = $taxonomy_obj->labels->singular_name ?? $taxonomy_obj->label;
						$type          = sprintf( 'Taxonomy - %s - %s', $readable_name, $term->taxonomy );
					}
				}
			} else {
				$post = get_post( $id );
				if ( $post ) {
					$title   = get_the_title( $id );
					$content = $post->post_content;

					$post_type_obj = get_post_type_object( $post->post_type );
					if ( $post_type_obj ) {
						$readable_name = $post_type_obj->labels->singular_name ?? $post_type_obj->label;
						$type          = sprintf( 'Post Type - %s - %s', $readable_name, $post->post_type );
					}
				}
			}
		}
		// Limit to 500 words (wp_trim_words handles tag stripping and whitespace).
		if ( ! empty( $content ) ) {
			$content = wp_trim_words( $content, 500, '' );
		}

		$language = $is_taxonomy
			? Post_Language_Resolver::for_term_id( (int) $id )
			: Post_Language_Resolver::for_id( (int) $id );

		return apply_filters(
			'surerank_content_generation_inputs',
			[
				'site_name'     => get_bloginfo( 'name' ),
				'site_tagline'  => get_bloginfo( 'description' ),
				'page_title'    => $title,
				'page_content'  => $content,
				'focus_keyword' => $this->get_focus_keyword( $id, $is_taxonomy ),
				'type'          => $type,
				'language'      => $language,
			]
		);
	}

	/**
	 * Get focus keyword for the post or term.
	 *
	 * @param int|null $post_id Post ID or Term ID.
	 * @param bool     $is_taxonomy Whether it's a taxonomy term.
	 * @return string Focus keyword.
	 * @since 1.4.2
	 */
	private function get_focus_keyword( $post_id = null, $is_taxonomy = false ) {
		if ( empty( $post_id ) ) {
			return '';
		}

		if ( $is_taxonomy ) {
			$term_meta = get_term_meta( $post_id, 'surerank_settings_general', true );
			return $term_meta['focus_keyword'] ?? '';
		}

		$post_meta = get_post_meta( $post_id, 'surerank_settings_general', true );
		return $post_meta['focus_keyword'] ?? '';
	}
}
