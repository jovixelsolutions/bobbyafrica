<?php
/**
 * Organization Schema
 *
 * This file handles the functionality for the Organization schema type.
 *
 * @package SureRank
 * @since 1.0.0
 */

namespace SureRank\Inc\Schema\Types;

use SureRank\Inc\Schema\Base;
use SureRank\Inc\Traits\Get_Instance;

/**
 * Organization
 * This class handles the functionality for the Organization schema type.
 *
 * @since 1.0.0
 */
class Organization extends Base {

	use Get_Instance;

	/**
	 * Get Schema Data
	 *
	 * @return array<string, mixed>|array<int, array<string, mixed>>
	 * @since 1.0.0
	 */
	public function schema_data() {
		return [
			'title'   => 'Organization',
			'type'    => 'Organization',
			'show_on' => [
				'rules'        => [
					'basic-global',
				],
				'specific'     => [],
				'specificText' => [],
			],
			'fields'  => $this->parse_fields( $this->get() ),
		];
	}

	/**
	 * Get Organization Schema
	 *
	 * @return array<int, array<string, mixed>>
	 * @since 1.0.0
	 */
	public function get() {
		return apply_filters(
			'surerank_default_schema_type_organization',
			[
				[
					'id'       => '@id',
					'type'     => 'Hidden',
					'std'      => '%site.url%#%id%',
					'required' => true,
					'default'  => true,
					'show'     => false,
				],
				[
					'id'      => 'schemaDocs',
					'type'    => 'Hidden',
					'url'     => 'https://schema.org/Organization',
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
					'std'     => 'Organization',
				],
				[
					'id'      => '@type',
					'label'   => __( 'Type', 'surerank' ),
					'tooltip' => __( 'Select the type that best describes the organization, such as Corporation, NGO, or EducationalOrganization. This information is used in structured data to define the nature of the entity.', 'surerank' ),
					'show'    => true,
					'default' => true,
					'type'    => 'SelectGroup',
					'std'     => 'Organization',
					'options' => $this->get_schema_type_options(),
				],
				$this->add_helper_property(
					'name',
					[
						'required' => true,
						'tooltip'  => __( 'Enter the full name of the organization as it should appear in structured data. This helps identify the business or entity associated with your website.', 'surerank' ),
						'std'      => '%site.title%',
						'type'     => 'Text',
					]
				),
				[
					'id'      => 'email',
					'label'   => __( 'Email', 'surerank' ),
					'type'    => 'Text',
					'default' => true,
					'show'    => true,
					'tooltip' => __( "Provide a valid email address for the organization. This contact detail will be included in your site's structured data.", 'surerank' ),
				],
				[
					'id'      => 'faxNumber',
					'label'   => __( 'Fax number', 'surerank' ),
					'default' => true,
					'show'    => true,
					'tooltip' => __( 'The fax number of the organization', 'surerank' ),
				],
				$this->add_helper_property(
					'Person',
					[
						'id'               => 'founder',
						'label'            => __( 'Founder', 'surerank' ),
						'tooltip'          => __( 'A person who founded this organization.', 'surerank' ),
						'cloneable'        => true,
						'cloneItemHeading' => __( 'Employee', 'surerank' ),
					]
				),
				[
					'id'      => 'foundingDate',
					'label'   => __( 'Founding date', 'surerank' ),
					'default' => true,
					'show'    => true,
					'tooltip' => __( 'The date that this organization was founded.', 'surerank' ),
					'type'    => 'Date',
				],
				[
					'label'   => __( 'Keywords', 'surerank' ),
					'id'      => 'keywords',
					'default' => true,
					'show'    => true,
					'tooltip' => __( 'Keywords or tags used to describe some item. Multiple textual entries in a keywords list are typically delimited by commas.', 'surerank' ),
				],
				[
					'id'      => 'logo',
					'label'   => __( 'Logo URL', 'surerank' ),
					'default' => true,
					'show'    => true,
					'tooltip' => __( 'Enter the direct URL to the organization\'s official logo image. This logo will be used in structured data to visually represent the brand.', 'surerank' ),
					'type'    => 'Image',
					'std'     => '%site.icon%',
				],
				[
					'id'        => 'sameAs',
					'label'     => __( 'Same as', 'surerank' ),
					'default'   => true,
					'show'      => true,
					'tooltip'   => __( 'Add a URL that clearly identifies the organization, such as a Wikipedia page, Wikidata entry, social media profile, or official site. This helps search engines verify the organization\'s identity.', 'surerank' ),
					'cloneable' => true,
					'std'       => '',
				],
				[
					'label'   => __( 'Slogan', 'surerank' ),
					'id'      => 'slogan',
					'default' => true,
					'show'    => true,
					'tooltip' => __( 'Provide the official slogan or motto associated with the organization or individual. This will be included in structured data for additional context.', 'surerank' ),
					'std'     => '%site.description%',
					'type'    => 'Text',
				],
				[
					'id'      => 'telephone',
					'label'   => __( 'Telephone', 'surerank' ),
					'default' => true,
					'show'    => true,
					'tooltip' => __( 'Enter a working phone number for the organization, including the country and area code. This detail helps complete your structured data profile.', 'surerank' ),
					'std'     => '',
				],
				$this->add_helper_property(
					'url',
					[
						'tooltip' => __( 'Provide the full URL of the specific business location. This should be a working link that represents the official page for that location.', 'surerank' ),
						'std'     => '%site.url% ',
						'show'    => true,
					]
				),
				// People/Personnel Fields.
				$this->add_helper_property(
					'Person',
					[
						'id'               => 'alumni',
						'label'            => __( 'Alumni', 'surerank' ),
						'tooltip'          => __( 'List former students, graduates, or past members who were previously associated with the organization.', 'surerank' ),
						'cloneable'        => true,
						'cloneItemHeading' => __( 'Alumni', 'surerank' ),
						'default'          => false,
						'show'             => true,
					]
				),
				$this->add_helper_property(
					'Person',
					[
						'id'               => 'employee',
						'label'            => __( 'Employees', 'surerank' ),
						'tooltip'          => __( 'Add information about people currently employed by or working for this organization.', 'surerank' ),
						'cloneable'        => true,
						'cloneItemHeading' => __( 'Employee', 'surerank' ),
						'default'          => false,
						'show'             => true,
					]
				),
				$this->add_helper_property(
					'Person',
					[
						'id'               => 'funder',
						'label'            => __( 'Funder', 'surerank' ),
						'tooltip'          => __( 'Specify individuals or organizations providing financial support or funding to this organization.', 'surerank' ),
						'cloneable'        => true,
						'cloneItemHeading' => __( 'Funder', 'surerank' ),
						'default'          => false,
						'show'             => true,
					]
				),
				$this->add_helper_property(
					'Person',
					[
						'id'               => 'sponsor',
						'label'            => __( 'Sponsor', 'surerank' ),
						'tooltip'          => __( 'Identify sponsors who support the organization through commitments, pledges, or financial contributions.', 'surerank' ),
						'cloneable'        => true,
						'cloneItemHeading' => __( 'Sponsor', 'surerank' ),
						'default'          => false,
						'show'             => true,
					]
				),
				// Location/Address Fields.
				$this->add_helper_property(
					'address',
					[
						'default' => false,
						'show'    => true,
					]
				),
				[
					'id'      => 'foundingLocation',
					'label'   => __( 'Founding location', 'surerank' ),
					'type'    => 'Group',
					'tooltip' => __( 'Specify the geographical location where the organization was originally established or founded.', 'surerank' ),
					'default' => false,
					'show'    => true,
					'fields'  => [
						[
							'id'       => '@type',
							'std'      => 'Place',
							'type'     => 'Hidden',
							'required' => true,
						],
						[
							'id'       => 'name',
							'label'    => __( 'Name', 'surerank' ),
							'tooltip'  => __( 'Enter the name of this founding location.', 'surerank' ),
							'required' => true,
						],
						$this->add_helper_property(
							'address',
							[
								'label'    => '',
								'required' => true,
								'tooltip'  => __( 'Provide the complete street address where the organization was founded.', 'surerank' ),
							]
						),
						[
							'id'      => 'url',
							'label'   => __( 'URL', 'surerank' ),
							'tooltip' => __( 'Enter the web address or reference link for this location.', 'surerank' ),
							'show'    => true,
						],
					],
				],
				[
					'id'               => 'location',
					'label'            => __( 'Location', 'surerank' ),
					'tooltip'          => __( 'Define physical or virtual locations where the organization operates, hosts events, or conducts business activities.', 'surerank' ),
					'type'             => 'Group',
					'cloneable'        => true,
					'cloneItemHeading' => __( 'Location', 'surerank' ),
					'default'          => false,
					'show'             => true,
					'fields'           => [
						[
							'id'        => '@type',
							'label'     => __( 'Type', 'surerank' ),
							'type'      => 'DataList',
							'std'       => 'Place',
							'required'  => true,
							'dependant' => true,
							'options'   => [
								'Place'           => __( 'Physical location', 'surerank' ),
								'VirtualLocation' => __( 'Online', 'surerank' ),
							],
						],
						[
							'label'      => __( 'Name', 'surerank' ),
							'id'         => 'name',
							'required'   => true,
							'dependency' => '[@type]:Place',
						],
						$this->add_helper_property(
							'address',
							[
								'label'      => '',
								'dependency' => '[@type]:Place',
								'show'       => true,
							]
						),
						[
							'id'         => 'url',
							'label'      => __( 'URL', 'surerank' ),
							'required'   => true,
							'dependency' => '[@type]:VirtualLocation',
						],
					],
				],
				[
					'id'               => 'hasPOS',
					'label'            => __( 'Has POS', 'surerank' ),
					'type'             => 'Group',
					'tooltip'          => __( 'Add retail locations, stores, or physical point-of-sale terminals operated by the organization.', 'surerank' ),
					'cloneable'        => true,
					'cloneItemHeading' => __( 'POS', 'surerank' ),
					'default'          => false,
					'show'             => true,
					'fields'           => [
						[
							'id'       => '@type',
							'std'      => 'Place',
							'type'     => 'Hidden',
							'required' => true,
						],
						[
							'id'       => 'name',
							'label'    => __( 'Name', 'surerank' ),
							'tooltip'  => __( 'Enter the business name or identifier for this point-of-sale location.', 'surerank' ),
							'required' => true,
						],
						$this->add_helper_property(
							'address',
							[
								'label'    => '',
								'required' => true,
								'tooltip'  => __( 'Provide the complete street address where customers can visit this sales location.', 'surerank' ),
							]
						),
						[
							'id'      => 'url',
							'label'   => __( 'URL', 'surerank' ),
							'tooltip' => __( 'Enter the web page or online reference for this specific location.', 'surerank' ),
						],
					],
				],
				// Contact Information.
				[
					'id'               => 'contactPoint',
					'label'            => __( 'Contact points', 'surerank' ),
					'tooltip'          => __( 'Define various communication channels and methods through which people can reach the organization.', 'surerank' ),
					'type'             => 'Group',
					'cloneable'        => true,
					'cloneItemHeading' => __( 'Contact point', 'surerank' ),
					'default'          => false,
					'show'             => true,
					'fields'           => [
						[
							'id'       => '@type',
							'std'      => 'ContactPoint',
							'type'     => 'Hidden',
							'required' => true,
						],
						[
							'id'      => 'contactOption',
							'label'   => __( 'Contact option', 'surerank' ),
							'tooltip' => __( 'Select special accessibility features or service options available for this contact method.', 'surerank' ),
							'type'    => 'DataList',
							'options' => [
								'HearingImpairedSupported' => __( 'Hearing-impaired supported', 'surerank' ),
								'TollFree'                 => __( 'Toll-free', 'surerank' ),
							],
						],
						[
							'label'   => __( 'Contact type', 'surerank' ),
							'id'      => 'contactType',
							'type'    => 'Text',
							'show'    => true,
							'tooltip' => __( 'Specify the purpose of this contact (e.g., Customer Service, Sales, Technical Support, Public Relations).', 'surerank' ),
						],
						[
							'id'       => 'email',
							'label'    => __( 'Email', 'surerank' ),
							'tooltip'  => __( 'Provide a valid email address for this contact channel.', 'surerank' ),
							'required' => true,
						],
						[
							'id'      => 'telephone',
							'label'   => __( 'Telephone', 'surerank' ),
							'tooltip' => __( 'Enter the contact phone number including country and area codes.', 'surerank' ),
							'show'    => true,
						],
						[
							'id'      => 'faxNumber',
							'label'   => __( 'Fax number', 'surerank' ),
							'tooltip' => __( 'Provide a fax number if the organization accepts fax communications.', 'surerank' ),
						],
						[
							'id'      => 'productSupported',
							'label'   => __( 'Product supported', 'surerank' ),
							'tooltip' => __( 'Specify which products or services this contact point provides support for.', 'surerank' ),
						],
						[
							'id'      => 'areaServed',
							'label'   => __( 'Area served', 'surerank' ),
							'tooltip' => __( 'Indicate the geographical regions or territories covered by this contact point.', 'surerank' ),
						],
					],
				],
				// Business/Commerce Fields.
				[
					'id'      => 'areaServed',
					'label'   => __( 'Area served', 'surerank' ),
					'tooltip' => __( 'Define the geographic regions, countries, or territories where the organization provides its services or products.', 'surerank' ),
					'default' => false,
					'show'    => true,
				],
				[
					'id'      => 'currenciesAccepted',
					'label'   => __( 'Currencies accepted', 'surerank' ),
					'tooltip' => __( 'List accepted currencies using ISO 4217 format (e.g., USD, EUR, GBP) or cryptocurrency ticker symbols (e.g., BTC, ETH).', 'surerank' ),
					'default' => false,
					'show'    => true,
				],
				[
					'label'   => __( 'Payment accepted', 'surerank' ),
					'id'      => 'paymentAccepted',
					'tooltip' => __( 'Specify payment methods available to customers such as Cash, Credit Card, Debit Card, Cryptocurrency, PayPal, etc.', 'surerank' ),
					'default' => false,
					'show'    => true,
				],
				[
					'id'      => 'priceRange',
					'label'   => __( 'Price range', 'surerank' ),
					'tooltip' => __( 'Indicate the general pricing level using currency symbols (e.g., $, $$, $$$) or a numerical range (e.g., $10-50).', 'surerank' ),
					'default' => false,
					'show'    => true,
				],
				[
					'id'      => 'acceptsReservations',
					'label'   => __( 'Accepts reservations', 'surerank' ),
					'tooltip' => __( 'Select whether the organization allows customers to make advance reservations or bookings.', 'surerank' ),
					'type'    => 'DataList',
					'std'     => 'True',
					'options' => [
						'True'  => __( 'True', 'surerank' ),
						'False' => __( 'False', 'surerank' ),
					],
					'default' => false,
					'show'    => true,
				],
				[
					'id'      => 'menu',
					'label'   => __( 'Menu', 'surerank' ),
					'tooltip' => __( 'For food establishments, provide the complete URL where customers can view the menu online.', 'surerank' ),
					'default' => false,
					'show'    => true,
				],
				[
					'id'      => 'servesCuisine',
					'label'   => __( 'Serves cuisine', 'surerank' ),
					'tooltip' => __( 'Describe the style or type of cuisine offered (e.g., Italian, Mexican, Asian, Vegan, Mediterranean).', 'surerank' ),
					'default' => false,
					'show'    => true,
				],
				// Branding/Media Fields.
				$this->add_helper_property(
					'image',
					[
						'default' => false,
						'show'    => true,
					]
				),
				[
					'label'            => __( 'Brand', 'surerank' ),
					'id'               => 'brand',
					'tooltip'          => __( 'Add brand identities associated with products, services, or sub-brands maintained by this organization.', 'surerank' ),
					'type'             => 'Group',
					'cloneable'        => true,
					'cloneItemHeading' => __( 'Brand', 'surerank' ),
					'default'          => false,
					'show'             => true,
					'fields'           => [
						[
							'id'       => '@type',
							'std'      => 'Brand',
							'type'     => 'Hidden',
							'required' => true,
						],
						[
							'id'       => 'name',
							'label'    => __( 'Name', 'surerank' ),
							'std'      => '%site.title%',
							'required' => true,
						],
						[
							'label'   => __( 'Logo', 'surerank' ),
							'id'      => 'logo',
							'tooltip' => __( 'Upload or provide a URL to the logo image representing this brand.', 'surerank' ),
							'type'    => 'Image',
							'show'    => true,
						],
						[
							'id'      => 'url',
							'label'   => __( 'URL', 'surerank' ),
							'show'    => true,
							'tooltip' => __( 'Enter the official web address for this brand.', 'surerank' ),
						],
					],
				],
				// Organizational Structure.
				[
					'id'               => 'department',
					'label'            => __( 'Departments', 'surerank' ),
					'cloneable'        => true,
					'cloneItemHeading' => __( 'Department', 'surerank' ),
					'tooltip'          => __( 'List the various departments, divisions, or functional units that make up the organization.', 'surerank' ),
					'default'          => false,
					'show'             => true,
					'flatten'          => true,
				],
				[
					'id'      => 'parentOrganization',
					'label'   => __( 'Parent organization', 'surerank' ),
					'tooltip' => __( 'Specify the parent or umbrella organization if this entity is a subsidiary, division, or part of a larger company.', 'surerank' ),
					'default' => false,
					'show'    => true,
				],
				// Business Identifiers.
				[
					'id'      => 'duns',
					'label'   => __( 'DUNS', 'surerank' ),
					'tooltip' => __( 'Enter the Dun & Bradstreet DUNS identifier used to verify and identify the organization.', 'surerank' ),
					'default' => false,
					'show'    => true,
				],
				[
					'id'      => 'globalLocationNumber',
					'label'   => __( 'Global location number', 'surerank' ),
					'tooltip' => __( 'Provide the Global Location Number (GLN) or International Location Number (ILN) that uniquely identifies the organization\'s location.', 'surerank' ),
					'default' => false,
					'show'    => true,
				],
				[
					'label'   => __( 'LEI Code', 'surerank' ),
					'id'      => 'leiCode',
					'tooltip' => __( 'Enter the Legal Entity Identifier that uniquely identifies the legal entity as defined in ISO 17442 standard.', 'surerank' ),
					'default' => false,
					'show'    => true,
				],
				[
					'label'   => __( 'Tax ID', 'surerank' ),
					'id'      => 'taxID',
					'tooltip' => __( 'Provide the Tax or Fiscal Identification Number (e.g., TIN in the US, CIF/NIF in Spain).', 'surerank' ),
					'default' => false,
					'show'    => true,
				],
				[
					'label'   => __( 'VAT ID', 'surerank' ),
					'id'      => 'vatID',
					'tooltip' => __( 'Enter the Value-added Tax identification number for the organization.', 'surerank' ),
					'default' => false,
					'show'    => true,
				],
				[
					'label'   => __( 'ISIC V4', 'surerank' ),
					'id'      => 'isicV4',
					'tooltip' => __( 'Specify the International Standard Industrial Classification Revision 4 code that categorizes the organization\'s economic activity.', 'surerank' ),
					'default' => false,
					'show'    => true,
				],
				[
					'label'   => __( 'ISO 6523 Code', 'surerank' ),
					'id'      => 'iso6523Code',
					'tooltip' => __( 'Provide the organization identifier as specified in the ISO 6523(-1) international standard.', 'surerank' ),
					'default' => false,
					'show'    => true,
				],
				[
					'label'   => __( 'NAICS', 'surerank' ),
					'id'      => 'naics',
					'tooltip' => __( 'Enter the North American Industry Classification System code that classifies the organization\'s business type.', 'surerank' ),
					'default' => false,
					'show'    => true,
				],
				// Company Information.
				[
					'label'   => __( 'Legal name', 'surerank' ),
					'id'      => 'legalName',
					'tooltip' => __( 'Enter the official registered company name as it appears on legal documents and business registration.', 'surerank' ),
					'default' => false,
					'show'    => true,
				],
				[
					'id'      => 'numberOfEmployees',
					'label'   => __( 'Number of employees', 'surerank' ),
					'type'    => 'Group',
					'tooltip' => __( 'Specify the total number of people employed by the organization or business.', 'surerank' ),
					'default' => false,
					'show'    => true,
					'fields'  => [
						[
							'id'       => '@type',
							'std'      => 'QuantitativeValue',
							'type'     => 'Hidden',
							'required' => true,
						],
						[
							'id'   => 'value',
							'show' => true,
						],
					],
				],
				// Products/Services.
				[
					'label'            => __( 'Owns', 'surerank' ),
					'id'               => 'owns',
					'tooltip'          => __( 'List products, properties, or assets that are owned and managed by this organization.', 'surerank' ),
					'cloneable'        => true,
					'cloneItemHeading' => __( 'Product', 'surerank' ),
					'default'          => false,
					'show'             => true,
					'flatten'          => true,
				],
				[
					'id'      => 'award',
					'label'   => __( 'Award', 'surerank' ),
					'tooltip' => __( 'List awards, honors, or recognition that has been won by or awarded to this organization.', 'surerank' ),
					'default' => false,
					'show'    => true,
				],
				// Ratings & Reviews.
				$this->add_helper_property(
					'aggregateRating',
					[
						'default' => false,
						'show'    => true,
					]
				),
				$this->add_helper_property(
					'Review',
					[
						'default' => false,
						'show'    => true,
					]
				),
				// Other Metadata.
				[
					'label'   => __( 'Knows language', 'surerank' ),
					'id'      => 'knowsLanguage',
					'tooltip' => __( 'Specify languages in which the organization provides services. Use standard IETF BCP 47 language codes (e.g., en, en-US, fr, es).', 'surerank' ),
					'default' => false,
					'show'    => true,
				],
			]
		);
	}

	/**
	 * Get Schema Type Options
	 *
	 * @return array<string, mixed>
	 * @since 1.0.0
	 */
	public function get_schema_type_options() {
		$groups = [
			'general'    => [
				'label'   => __( 'General', 'surerank' ),
				'options' => [
					'Organization' => __( 'Organization', 'surerank' ),
					'Corporation'  => __( 'Corporation', 'surerank' ),
					'NGO'          => __( 'NGO', 'surerank' ),
				],
			],
			'education'  => [
				'label'   => __( 'Educational', 'surerank' ),
				'options' => [
					'EducationalOrganization' => __( 'EducationalOrganization', 'surerank' ),
					'CollegeOrUniversity'     => __( 'CollegeOrUniversity', 'surerank' ),
					'ElementarySchool'        => __( 'ElementarySchool', 'surerank' ),
					'HighSchool'              => __( 'HighSchool', 'surerank' ),
					'MiddleSchool'            => __( 'MiddleSchool', 'surerank' ),
					'Preschool'               => __( 'Preschool', 'surerank' ),
					'School'                  => __( 'School', 'surerank' ),
				],
			],
			'government' => [
				'label'   => __( 'Government', 'surerank' ),
				'options' => [
					'GovernmentOrganization' => __( 'GovernmentOrganization', 'surerank' ),
					'FundingAgency'          => __( 'FundingAgency', 'surerank' ),
				],
			],
			'medical'    => [
				'label'   => __( 'Medical', 'surerank' ),
				'options' => [
					'MedicalOrganization' => __( 'MedicalOrganization', 'surerank' ),
					'DiagnosticLab'       => __( 'DiagnosticLab', 'surerank' ),
					'VeterinaryCare'      => __( 'VeterinaryCare', 'surerank' ),
				],
			],
			'arts'       => [
				'label'   => __( 'Arts & Performance', 'surerank' ),
				'options' => [
					'PerformingGroup' => __( 'PerformingGroup', 'surerank' ),
					'DanceGroup'      => __( 'DanceGroup', 'surerank' ),
					'MusicGroup'      => __( 'MusicGroup', 'surerank' ),
					'TheaterGroup'    => __( 'TheaterGroup', 'surerank' ),
				],
			],
			'media'      => [
				'label'   => __( 'Media', 'surerank' ),
				'options' => [
					'NewsMediaOrganization' => __( 'NewsMediaOrganization', 'surerank' ),
				],
			],
			'research'   => [
				'label'   => __( 'Research', 'surerank' ),
				'options' => [
					'Project'         => __( 'Project', 'surerank' ),
					'ResearchProject' => __( 'ResearchProject', 'surerank' ),
					'Consortium'      => __( 'Consortium', 'surerank' ),
				],
			],
			'sports'     => [
				'label'   => __( 'Sports', 'surerank' ),
				'options' => [
					'SportsOrganization' => __( 'SportsOrganization', 'surerank' ),
					'SportsTeam'         => __( 'SportsTeam', 'surerank' ),
				],
			],
			'services'   => [
				'label'   => __( 'Services', 'surerank' ),
				'options' => [
					'Airline'       => __( 'Airline', 'surerank' ),
					'LibrarySystem' => __( 'LibrarySystem', 'surerank' ),
					'WorkersUnion'  => __( 'WorkersUnion', 'surerank' ),
				],
			],
		];

		return apply_filters( 'surerank_schema_type_organization_options', $groups );
	}
}
