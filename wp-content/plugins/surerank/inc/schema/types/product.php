<?php
/**
 * Product
 *
 * This file handles the schema for the Product type.
 *
 * @package surerank
 * @since 1.0.0
 */

namespace SureRank\Inc\Schema\Types;

use SureRank\Inc\Schema\Base;
use SureRank\Inc\Traits\Get_Instance;

/**
 * Product
 * This class handles the schema for the Product type.
 *
 * @since 1.0.0
 */
class Product extends Base {

	use Get_Instance;

	/**
	 * Get Schema Data
	 *
	 * @return array<string, mixed>|array<int, array<string, mixed>>
	 * @since 1.0.0
	 */
	public function schema_data() {
		return [
			'title'   => 'Product',
			'type'    => 'Product',
			'show_on' => [
				'rules'        => [
					'product|all',
				],
				'specific'     => [],
				'specificText' => [],
			],
			'fields'  => $this->parse_fields( $this->get() ),
		];
	}

	/**
	 * Get Variables
	 *
	 * @return array<int,array<string,mixed>>
	 * @since 1.0.0
	 */
	public function get() {
		$aggregate_ratings                     = $this->add_helper_property( 'aggregateRating', [ 'show' => true ] );
		$aggregate_ratings['fields'][0]['std'] = '%product.rating%';

		return apply_filters(
			'surerank_default_schema_type_product',
			[
				[
					'id'      => 'googleDocs',
					'type'    => 'Hidden',
					'url'     => 'https://developers.google.com/search/docs/advanced/structured-data/product',
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
					'std'     => 'Product',
				],
				[
					'id'       => '@id',
					'type'     => 'Hidden',
					'std'      => '%current.url%#%id%',
					'required' => true,
					'default'  => true,
					'show'     => true,
				],
				$this->add_helper_property(
					'name',
					[
						'required' => true,
						'tooltip'  => __( 'Enter the product’s official name as it should appear in search results. This will be included in your structured data to identify the product.', 'surerank' ),
					]
				),
				$this->add_helper_property(
					'description',
					[
						'show'    => true,
						'tooltip' => __( 'Add a short description of the product’s features or purpose. This summary helps search engines understand what the product is about.', 'surerank' ),
					]
				),
				[
					'label'   => __( 'Brand name', 'surerank' ),
					'id'      => 'brand',
					'default' => true,
					'show'    => true,
					'tooltip' => __( 'Specify the brand associated with the product. This is used in structured data to highlight the brand relationship.', 'surerank' ),
					'type'    => 'Group',
					'fields'  => [
						[
							'id'       => '@type',
							'std'      => 'Brand',
							'type'     => 'Hidden',
							'required' => true,
							'hidden'   => true,
							'default'  => true,
							'show'     => true,
							'parent'   => 'brand',
						],
						[
							'id'      => 'name',
							'label'   => __( 'Name', 'surerank' ),
							'std'     => '%site.title%',
							'default' => true,
							'show'    => true,
							'parent'  => 'brand',
							'type'    => 'Text',
						],
					],
				],
				$this->add_helper_property(
					'url',
					[
						'show'    => true,
						'tooltip' => __( 'Enter the full URL where the product is available or described. This link is used in structured data to direct users and search engines to the product page.', 'surerank' ),
						'std'     => '%post.url%',
						'type'    => 'Text',
					]
				),
				[
					'label'   => __( 'SKU', 'surerank' ),
					'id'      => 'sku',
					'default' => true,
					'show'    => true,
					'std'     => '%product.sku%',
					'tooltip' => __( 'Provide the SKU (Stock Keeping Unit), a unique identifier used by merchants to track the product. This helps search engines associate the product with inventory systems.', 'surerank' ),
					'type'    => 'Text',
				],
				[
					'id'      => 'image',
					'label'   => __( 'Product Image', 'surerank' ),
					'type'    => 'Group',
					'default' => true,
					'show'    => true,
					'fields'  => [
						[
							'id'      => '@id',
							'std'     => '%product.image%',
							'type'    => 'Hidden',
							'parent'  => 'image',
							'default' => true,
							'show'    => true,
						],
						[
							'id'      => 'type',
							'std'     => 'ImageObject',
							'type'    => 'Hidden',
							'parent'  => 'image',
							'default' => true,
							'show'    => true,
						],
						[
							'id'      => 'url',
							'label'   => __( 'Image URL', 'surerank' ),
							'std'     => '%product.image%',
							'default' => true,
							'show'    => true,
							'parent'  => 'image',
							'type'    => 'Text',
						],
						[
							'id'      => 'width',
							'label'   => __( 'Image Width', 'surerank' ),
							'std'     => '%product.image_width%',
							'default' => true,
							'show'    => true,
							'parent'  => 'image',
							'width'   => '1/2',
							'type'    => 'Text',
						],
						[
							'id'      => 'height',
							'label'   => __( 'Image Height', 'surerank' ),
							'std'     => '%product.image_height%',
							'default' => true,
							'show'    => true,
							'parent'  => 'image',
							'width'   => '1/2',
							'type'    => 'Text',
						],
					],
				],
				$this->add_helper_property(
					'mainEntityOfPage',
					[
						'type' => 'Text',
					]
				),
				$aggregate_ratings,
				[
					'id'      => 'offers',
					'type'    => 'Group',
					'label'   => __( 'Offers', 'surerank' ),
					'default' => true,
					'show'    => true,
					'fields'  => [
						[
							'id'       => '@type',
							'label'    => __( 'Type', 'surerank' ),
							'type'     => 'Select',
							'required' => true,
							'default'  => true,
							'show'     => true,
							'std'      => 'Offer',
							'options'  => [
								'Offer'          => __( 'Offer', 'surerank' ),
								'AggregateOffer' => __( 'Aggregate Offer', 'surerank' ),
							],
							'parent'   => 'offers',
						],
						[
							'id'       => 'price',
							'label'    => __( 'Price', 'surerank' ),
							'required' => true,
							'default'  => true,
							'show'     => true,
							'std'      => '%product.price%',
							'tooltip'  => __( 'Enter the products sale price. This price is included in structured data to reflect what users would pay.', 'surerank' ),
							'parent'   => 'offers',
							'main'     => 'Offer',
							'width'    => '1/2',
							'type'     => 'Text',
						],
						[
							'id'       => 'priceCurrency',
							'label'    => __( 'Price currency', 'surerank' ),
							'std'      => '%product.currency%',
							'required' => true,
							'default'  => true,
							'show'     => true,
							'parent'   => 'offers',
							'width'    => '1/2',
							'type'     => 'Text',
						],
						[
							'id'      => 'priceValidUntil',
							'label'   => __( 'Price valid until', 'surerank' ),
							'default' => true,
							'show'    => true,
							'type'    => 'Date',
							'tooltip' => __( 'The date (in ISO 8601 date format) after which the price is no longer available.', 'surerank' ),
							'parent'  => 'offers',
							'main'    => 'Offer',
							'width'   => 'full',
							'std'     => '%product.sale_to%',
						],
						[
							'id'       => 'availability',
							'label'    => __( 'Availability', 'surerank' ),
							'required' => true,
							'default'  => true,
							'show'     => true,
							'std'      => '%product.stock%',
							'parent'   => 'offers',
							'type'     => 'Text',
						],
						[
							'id'       => 'lowPrice',
							'label'    => __( 'Low price', 'surerank' ),
							'required' => true,
							'default'  => true,
							'show'     => true,
							'tooltip'  => __( 'Shows the lowest price available for the product across different sellers. Use a decimal format (e.g., 199.99) for consistency in structured data.', 'surerank' ),
							'main'     => 'AggregateOffer',
							'std'      => '%product.low_price%',
							'width'    => '1/2',
						],
						[
							'id'      => 'highPrice',
							'label'   => __( 'High price', 'surerank' ),
							'main'    => 'AggregateOffer',
							'default' => true,
							'show'    => true,
							'tooltip' => __( 'Show the highest listed price for the product across all offers. Use a decimal format to ensure proper display in structured data.', 'surerank' ),
							'std'     => '%product.high_price%',
							'width'   => '1/2',
						],
						[
							'id'      => 'offerCount',
							'label'   => __( 'Offer count', 'surerank' ),
							'default' => true,
							'show'    => true,
							'tooltip' => __( 'Shows the stock status of the product, such as In Stock or Out of Stock. This information helps search engines show real-time availability.', 'surerank' ),
							'main'    => 'AggregateOffer',
							'std'     => '%product.offer_count%',
						],
					],
				],
			]
		);
	}
}
