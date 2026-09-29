<?php
/**
 * Person Schema
 *
 * This file handles the functionality for the Person schema type.
 *
 * @package SureRank
 * @since 1.0.0
 */

namespace SureRank\Inc\Schema\Types;

use SureRank\Inc\Schema\Base;
use SureRank\Inc\Traits\Get_Instance;

/**
 * Person
 * This class handles the functionality for the Person schema type.
 *
 * @since 1.0.0
 */
class Person extends Base {

	use Get_Instance;

	/**
	 * Get Schema Data
	 *
	 * @return array<string, mixed>|array<int, array<string, mixed>>
	 * @since 1.0.0
	 */
	public function schema_data() {
		return [
			'title'   => 'Person',
			'type'    => 'Person',
			'show_on' => [
				'rules'        => [
					'post|all',
				],
				'specific'     => [],
				'specificText' => [],
			],
			'fields'  => $this->parse_fields( $this->get() ),
		];
	}

	/**
	 * Get Person Schema
	 *
	 * @return array<int, array<string, mixed>>
	 * @since 1.0.0
	 */
	public function get() {
		return apply_filters(
			'surerank_default_schema_type_person',
			[
				[
					'id'      => 'schemaDocs',
					'type'    => 'Hidden',
					'url'     => 'https://schema.org/Person',
					'default' => true,
					'show'    => true,
				],
				[
					'id'      => 'schema_name',
					'label'   => __( 'Schema Title', 'surerank' ),
					'tooltip' => __( 'Give your schema a name to help you identify it later. This title is for internal reference only and won\'t be included in your site\'s structured data.', 'surerank' ),
					'show'    => true,
					'default' => true,
					'type'    => 'Title',
					'std'     => 'Person',
				],
				[
					'id'       => '@id',
					'type'     => 'Hidden',
					'std'      => '%current.url%#%id%',
					'required' => true,
					'default'  => true,
					'show'     => true,
				],
				[
					'id'       => 'name',
					'label'    => __( 'Name', 'surerank' ),
					'required' => true,
					'default'  => true,
					'show'     => true,
					'std'      => '%author.display_name%',
					'type'     => 'Text',
				],
				[
					'id'      => 'url',
					'label'   => __( 'URL', 'surerank' ),
					'default' => true,
					'show'    => true,
					'std'     => '%author.posts_url%',
					'type'    => 'Text',
				],
				[
					'id'      => 'givenName',
					'label'   => __( 'Given name', 'surerank' ),
					'default' => true,
					'show'    => true,
					'tooltip' => __( 'Enter the given name (first name) of the person. This is used in structured data to identify individuals accurately.', 'surerank' ),
					'std'     => '%author.first_name%',
					'type'    => 'Text',
				],
				[
					'id'      => 'familyName',
					'label'   => __( 'Family name', 'surerank' ),
					'default' => true,
					'show'    => true,
					'tooltip' => __( 'Enter the family name (last name) of the person. This helps provide a complete name in structured data for personal identification.', 'surerank' ),
					'std'     => '%author.last_name%',
					'type'    => 'Text',
				],
				[
					'id'      => 'brand',
					'label'   => __( 'Brand', 'surerank' ),
					'default' => true,
					'show'    => true,
					'tooltip' => __( "List any brands linked to the person, product, service, or organization. This brand relationship will be reflected in your site's structured data.", 'surerank' ),
					'std'     => '%site.title%',
					'type'    => 'Text',
				],
				[
					'id'      => 'mainEntityOfPage',
					'label'   => __( 'Main entity of page', 'surerank' ),
					'default' => true,
					'show'    => true,
					'tooltip' => __( 'Identify the primary subject described on the page, such as a person, product, or article. This helps search engines understand what the content is mainly about.', 'surerank' ),
					'type'    => 'Text',
					'std'     => '',
				],
				$this->add_helper_property(
					'description',
					[
						'tooltip' => __( 'Write a short description about the person. This bio or summary is used in structured data to give context about who they are.', 'surerank' ),
						'std'     => '%author.description%',
					]
				),
				[
					'id'      => 'email',
					'label'   => __( 'Email', 'surerank' ),
					'default' => true,
					'show'    => true,
					'tooltip' => __( 'The email address of the person', 'surerank' ),
					'std'     => '%author.email%',
					'type'    => 'Text',
				],
				[
					'id'      => 'image',
					'label'   => __( 'Image', 'surerank' ),
					'default' => true,
					'show'    => true,
					'std'     => '%author.avatar%',
					'type'    => 'Image',
					'tooltip' => __( 'Enter the direct URL of an image representing the person. This image will appear in structured data to provide visual reference.', 'surerank' ),
				],
				[
					'id'      => 'telephone',
					'label'   => __( 'Telephone', 'surerank' ),
					'default' => true,
					'show'    => true,
					'tooltip' => __( 'The telephone number of the person. Be sure to include the country code and area code in the phone number.', 'surerank' ),
				],
				[
					'id'        => 'sameAs',
					'label'     => __( 'Same as', 'surerank' ),
					'default'   => true,
					'show'      => true,
					'tooltip'   => __( 'Provide a reliable link that clearly identifies the person, such as a Wikipedia page, Wikidata entry, or verified profile. This strengthens structured data accuracy.', 'surerank' ),
					'cloneable' => true,
					'std'       => '',
				],
			]
		);
	}
}
