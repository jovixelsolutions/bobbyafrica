<?php
/**
 * SEO Analyzer class.
 *
 * Performs SEO checks on HTML content with consistent output for UI.
 *
 * @package SureRank\Inc\Analyzer
 */

namespace SureRank\Inc\Analyzer;

use DOMAttr;
use DOMDocument;
use DOMElement;
use DOMNodeList;
use DOMXPath;
use SureRank\Inc\API\Analyzer;
use SureRank\Inc\Functions\Get;
use SureRank\Inc\Modules\Nudges\Utils;
use WP_Error;

/**
 * Class SeoAnalyzer
 *
 * Analyzes HTML content for SEO metrics with standardized output.
 */
class SeoAnalyzer {

	/**
	 * Instance object.
	 *
	 * @var self|null
	 */
	private static $instance = null;

	/**
	 * DOMDocument instance containing parsed HTML.
	 *
	 * @var DOMDocument|null
	 */
	private $dom = null;

	/**
	 * Array of error messages encountered during analysis.
	 *
	 * @var array<string>
	 */
	private $errors = [];

	/**
	 * Base URL being analyzed.
	 *
	 * @var string
	 */
	private $base_url = '';

	/**
	 * Scraper instance for fetching content.
	 *
	 * @var Scraper
	 */
	private $scraper;

	/**
	 * Parser instance for parsing HTML.
	 *
	 * @var Parser
	 */
	private $parser;

	/**
	 * Cached HTML content.
	 *
	 * @var string|WP_Error
	 */
	private $html_content = '';

	/**
	 * Constructor.
	 *
	 * @param string $url The URL to analyze.
	 * @return void
	 */
	public function __construct( string $url ) {
		$this->scraper = Scraper::get_instance();
		$this->parser  = Parser::get_instance();
		$this->initialize( $url );
	}

	/**
	 * Initiator.
	 *
	 * @since 1.0.0
	 * @param string $url The URL to analyze.
	 * @return self initialized object of class.
	 */
	public static function get_instance( $url ) {
		if ( null === self::$instance ) {
			self::$instance = new self( $url );
		}
		return self::$instance;
	}

	/**
	 * Get XPath instance for DOMDocument.
	 *
	 * @return DOMXPath|array<string, mixed>
	 */
	public function get_xpath() {
		if ( $this->dom === null ) {
			return [
				'exists'  => true,
				'status'  => 'error',
				'details' => $this->errors,
				'message' => __( 'Failed to load DOM for analysis.', 'surerank' ),
			];
		}

		return new DOMXPath( $this->dom );
	}

	/**
	 * Analyze page title.
	 *
	 * @param DOMXPath $xpath XPath instance.
	 * @return array<string, mixed>
	 */
	public function analyze_title( DOMXPath $xpath ) {
		$show_static_page = get_option( 'show_on_front' ) === 'page';

		$helptext = [
			__( 'A homepage SEO title is the main headline that appears for your site in search results.', 'surerank' ),
			sprintf(
				"<img class='w-full h-full' src='%s' alt='%s' />",
				esc_attr( 'https://surerank.com/wp-content/uploads/2026/02/homepage-seo-title.webp' ),
				esc_attr( 'Homepage SEO Title' )
			),
			__( 'It is often the first thing people read before deciding whether to click.', 'surerank' ),
			__( 'A clear and well written title helps search engines understand your homepage and improves click through rate by setting the right expectation.', 'surerank' ),

			sprintf(
				'<h6>✅ %s </h6>',
				__( 'Best Practice', 'surerank' )
			),
			[
				'list' => [
					__( 'Good titles are short, specific, and easy to scan.', 'surerank' ),
					__( 'Make sure to keep it between 50 and 60 characters.', 'surerank' ),
					__( 'Include your brand name and main keyword where it makes sense.', 'surerank' ),
					__( 'A simple, honest title usually performs better than a long or generic one.', 'surerank' ),
				],
			],

			sprintf(
				'<h6>🛠️ %s </h6>',
				__( 'Where to Update It', 'surerank' )
			),
		];

		if ( ! $show_static_page ) {
			$helptext[] = sprintf(
				/* translators: %1$s: URL to the SureRank homepage general settings, %2$s: Anchor text "General" */
				__( "From the SureRank Dashboard, go to General ⇾ Home Page ⇾ <a href='%1\$s' target='_blank' rel='noopener'>%2\$s</a>.", 'surerank' ),
				$this->get_homepage_settings_url(),
				__( 'General', 'surerank' )
			);
			$helptext[] = __( 'Here, you can update the title, description, and social image for your home page. SureRank will also show a preview of your website.', 'surerank' );
		} else {
			$helptext[] = sprintf(
				/* translators: %1$s: URL to the Home Page settings, %2$s: Anchor text "Home Page" */
				__( "Go to Pages ⇾ <a href='%1\$s' target='_blank' rel='noopener'>%2\$s</a> ⇾ SureRank ⇾ Optimize ⇾ General.", 'surerank' ),
				$this->get_homepage_settings_url(),
				__( 'Home Page', 'surerank' )
			);
			$helptext[] = __( 'Here, you can update the title, description, and social metadata with image for your home page. SureRank will also show a preview of your website.', 'surerank' );
		}

		$helptext[] = sprintf(
			'<h6>✏️ %s </h6>',
			__( 'Update Here', 'surerank' )
		);

		if ( ! $show_static_page ) {
			$helptext[] = sprintf(
				"<img class='w-full h-full' src='%s' alt='%s' />",
				esc_attr( 'https://surerank.com/wp-content/uploads/2026/02/homepage-seo-title-general-update.webp' ),
				esc_attr( 'Homepage SEO Title General Update' )
			);
		} else {
			$helptext[] = sprintf(
				"<img class='w-full h-full' src='%s' alt='%s' />",
				esc_attr( 'https://surerank.com/wp-content/uploads/2026/02/homepage-seo-title-page-update.webp' ),
				esc_attr( 'Homepage SEO Title Page Update' )
			);
		}

		if ( ! Utils::get_instance()->is_pro_active() ) {
			$helptext[] = sprintf(
				'<h6>💬 %s </h6>',
				__( 'Need Help?', 'surerank' )
			);
			$helptext[] = __( 'SureRank Pro helps you generate SEO titles using AI, following best practices for length, clarity, and click through rate.', 'surerank' );
		}

		$titles = $xpath->query( '//title' );
		if ( ! $titles instanceof DOMNodeList ) {
			return $this->build_error_response(
				__( 'Homepage SEO Title', 'surerank' ),
				$helptext,
				__( 'Search engine title is missing on the homepage.', 'surerank' ),
				'error'
			);
		}

		$exists  = $titles->length > 0;
		$content = '';
		$length  = 0;

		if ( $exists ) {
			$title_node = $titles->item( 0 );
			if ( $title_node instanceof DOMElement ) {
				$content = trim( $title_node->textContent );
				$length  = mb_strlen( $content );
			}
		}

		if ( ! $exists ) {
			$status = 'error';
		} elseif ( $length > Get::TITLE_LENGTH ) {
			$status = 'warning';
		} else {
			$status = 'success';
		}

		return [
			'exists'      => $exists,
			'status'      => $status,
			'description' => $helptext,
			'message'     => $this->get_title_message( $exists, $length, $status ),
			'heading'     => __( 'Homepage SEO Title', 'surerank' ),
		];
	}

	/**
	 * Analyze meta description.
	 *
	 * @param DOMXPath $xpath XPath instance.
	 * @return array<string, mixed>
	 */
	public function analyze_meta_description( DOMXPath $xpath ) {
		$heading     = __( 'Homepage SEO Description', 'surerank' );
		$description = [
			__( 'A homepage SEO description is the short text that appears below your site title in search results.', 'surerank' ),
			sprintf(
				"<img class='w-full h-full' src='%s' alt='%s' />",
				esc_attr( 'https://surerank.com/wp-content/uploads/2026/02/homepage-seo-description-visual.webp' ),
				esc_attr( 'Homepage SEO Description' )
			),
			__( 'It gives people a quick idea of what your site is about before they decide to click.', 'surerank' ),
			__( 'A clear and well written description helps search engines understand your homepage and makes your result more appealing.', 'surerank' ),

			sprintf(
				'<h6>✅ %s </h6>',
				__( 'Best Practice', 'surerank' )
			),
			[
				'list' => [
					__( 'Good descriptions are clear and easy to read.', 'surerank' ),
					__( 'Make sure to keep it between 150 and 160 characters.', 'surerank' ),
					__( 'Write it in natural language with your keywords where it makes sense.', 'surerank' ),
					__( 'A simple, helpful description performs better than a vague or overly long one.', 'surerank' ),
				],
			],

			sprintf(
				'<h6>🛠️ %s </h6>',
				__( 'Where to Update It', 'surerank' )
			),
		];

		$show_static_page = get_option( 'show_on_front' ) === 'page';

		if ( ! $show_static_page ) {
			$description[] = sprintf(
				/* translators: %1$s: URL to the SureRank homepage general settings, %2$s: Anchor text "General" */
				__( "From the SureRank Dashboard, go to General ⇾ Home Page ⇾ <a href='%1\$s' target='_blank' rel='noopener'>%2\$s</a>.", 'surerank' ),
				$this->get_homepage_settings_url(),
				__( 'General', 'surerank' )
			);
			$description[] = __( 'Here, you can update the title, description, and image for your home page. SureRank will also show a preview of your website.', 'surerank' );
		} else {
			$description[] = sprintf(
				/* translators: %1$s: URL to the Home Page settings, %2$s: Anchor text "Home Page" */
				__( "Go to Pages ⇾ <a href='%1\$s' target='_blank' rel='noopener'>%2\$s</a> ⇾ SureRank ⇾ Optimize ⇾ General.", 'surerank' ),
				$this->get_homepage_settings_url(),
				__( 'Home Page', 'surerank' )
			);
			$description[] = __( 'Here, you can update the title, description, and image for your home page. SureRank will also show a preview of your website.', 'surerank' );
		}

		$description[] = sprintf(
			'<h6>✏️ %s </h6>',
			__( 'Update Here', 'surerank' )
		);

		if ( ! $show_static_page ) {
			$description[] = sprintf(
				"<img class='w-full h-full' src='%s' alt='%s' />",
				esc_attr( 'https://surerank.com/wp-content/uploads/2026/02/homepage-seo-description-general-update.webp' ),
				esc_attr( 'Homepage SEO Description General Update' )
			);
		} else {
			$description[] = sprintf(
				"<img class='w-full h-full' src='%s' alt='%s' />",
				esc_attr( 'https://surerank.com/wp-content/uploads/2026/02/homepage-seo-description-page-update.webp' ),
				esc_attr( 'Homepage SEO Description Page Update' )
			);
		}

		if ( ! Utils::get_instance()->is_pro_active() ) {
			$description[] = sprintf(
				'<h6>💬 %s </h6>',
				__( 'Need Help?', 'surerank' )
			);
			$description[] = __( 'SureRank Pro helps you generate SEO titles and descriptions using AI, following best practices, so your pages look great in search results.', 'surerank' );
		}

		$meta_desc = $xpath->query( '//meta[@name="description"]/@content' );
		if ( ! $meta_desc instanceof DOMNodeList ) {
			return $this->build_error_response(
				$heading,
				$description,
				__( 'Search engine description is missing on the homepage.', 'surerank' ),
				'warning'
			);
		}

		$exists  = $meta_desc->length > 0;
		$content = '';
		$length  = 0;

		if ( $exists ) {
			$meta_node = $meta_desc->item( 0 );
			if ( $meta_node instanceof DOMAttr ) {
				$content = trim( $meta_node->value );
				$length  = mb_strlen( $content );
			}
		}

		if ( ! $exists ) {
			$status = 'warning';
		} elseif ( $length > Get::DESCRIPTION_LENGTH ) {
			$status = 'warning';
		} else {
			$status = 'success';
		}

		return [
			'exists'      => $exists,
			'status'      => $status,
			'description' => $description,
			'message'     => $this->get_meta_description_message( $exists, $length, $status ),
			'heading'     => $heading,
		];
	}

	/**
	 * Analyze headings (H1).
	 *
	 * @param DOMXPath $xpath XPath instance.
	 * @return array<string, mixed>
	 */
	public function analyze_heading_h1( DOMXPath $xpath ) {
		$h1_analysis = $this->analyze_h1( $xpath );

		$exists = $h1_analysis['exists'];
		$status = 'success';
		$title  = __( 'Homepage contains one H1 heading', 'surerank' );

		$descriptions = [
			__( 'The H1 is the main heading of a page. It tells visitors and search engines what the page is primarily about.', 'surerank' ),
			__( 'When the H1 is missing, duplicated, or unclear, search engines get less context and visitors may struggle to quickly understand your site\'s purpose.', 'surerank' ),

			sprintf(
				'<h6>✅ %s </h6>',
				__( 'Best Practice', 'surerank' )
			),
			[
				'list' => [
					__( 'Keep the H1 short and descriptive.', 'surerank' ),
					__( 'Use plain language instead of marketing buzzwords.', 'surerank' ),
					__( 'Make sure it reflects the main topic of your homepage.', 'surerank' ),
				],
			],

			sprintf(
				'<h6>🛠️ %s </h6>',
				__( 'Where to Update It', 'surerank' )
			),
			[
				'list' => [
					sprintf(
						/* translators: %1$s: URL to the Home Page settings, %2$s: Anchor text "Home Page" */
						__( "Go to Pages ⇾ <a href='%1\$s' target='_blank' rel='noopener'>%2\$s</a>", 'surerank' ),
						$this->get_homepage_settings_url(),
						__( 'Home Page', 'surerank' ),
					),
					__( 'Edit the main heading and set it as an H1', 'surerank' ),
					__( 'Update the page', 'surerank' ),
				],
			],

			sprintf(
				'<h6>✏️ %s </h6>',
				__( 'Update Here', 'surerank' )
			),

			sprintf(
				"<img class='w-full h-full' src='%s' alt='%s' />",
				esc_attr( 'https://surerank.com/wp-content/uploads/2026/03/homepage-h1-heading-is-missing-img.webp' ),
				esc_attr( 'Homepage H1 Heading is Missing' )
			),
		];

		if ( ! Utils::get_instance()->is_pro_active() ) {
			$descriptions[] = sprintf(
				'<h6>💬 %s </h6>',
				__( 'Need Help?', 'surerank' )
			);
			$descriptions[] = __( 'SureRank Pro helps you identify heading issues and suggests improvements using AI, so your pages stay clear and well structured.', 'surerank' );
		}

		if ( ! $h1_analysis['exists'] ) {
			$status  = 'warning';
			$title   = __( 'Your homepage does not currently have a clear H1 heading.', 'surerank' );
			$heading = __( 'Homepage H1 Heading Is Missing', 'surerank' );
		} elseif ( ! $h1_analysis['is_optimized'] ) {
			$status  = 'warning';
			$title   = __( 'Your homepage currently contains multiple H1 headings.', 'surerank' );
			$heading = __( 'Multiple H1 Headings Found', 'surerank' );
		} else {
			$title   = __( 'Your homepage currently contains one H1 heading', 'surerank' );
			$heading = __( 'Homepage H1 Heading Found', 'surerank' );
		}

		return [
			'exists'      => $exists,
			'status'      => $status,
			'description' => $descriptions,
			'message'     => $title,
			'heading'     => $heading,
			'not_fixable' => true,
		];
	}

	/**
	 * Analyze H2 headings.
	 *
	 * @param DOMXPath $xpath XPath instance.
	 * @return array<string, mixed>
	 */
	public function analyze_heading_h2( DOMXPath $xpath ) {

		$descriptions = [
			__( 'Subheadings help break your content into sections and make it easier for visitors to scan and understand your page.', 'surerank' ),
			__( 'They also give search engines more context about the structure and topics covered on your homepage.', 'surerank' ),

			sprintf(
				'<h6>✅ %s </h6>',
				__( 'Best Practice', 'surerank' )
			),
			[
				'list' => [
					__( 'Use H2 headings to introduce major sections', 'surerank' ),
					__( 'Each H2 should describe what the section is about', 'surerank' ),
					__( 'Avoid using H2s just for styling.', 'surerank' ),
					__( 'They should reflect the structure of your content.', 'surerank' ),
				],
			],

			sprintf(
				'<h6>🛠️ %s </h6>',
				__( 'Where to Update It', 'surerank' )
			),
			__( 'Edit your homepage content and add H2 headings where they naturally fit.', 'surerank' ),
			__( 'If your homepage is a page:', 'surerank' ),
			[
				'list' => [
					sprintf(
						/* translators: %1$s: URL to the Home Page settings, %2$s: Anchor text "Home Page" */
						__( "Go to Pages ⇾ <a href='%1\$s' target='_blank' rel='noopener'>%2\$s</a>", 'surerank' ),
						$this->get_homepage_settings_url(),
						__( 'Home Page', 'surerank' ),
					),
					__( 'Add or update section headings and set them as H2', 'surerank' ),
					__( 'Update the page', 'surerank' ),
				],
			],

			sprintf(
				'<h6>✏️ %s </h6>',
				__( 'Update Here', 'surerank' )
			),

			sprintf(
				"<img class='w-full h-full' src='%s' alt='%s' />",
				esc_attr( 'https://surerank.com/wp-content/uploads/2026/03/no-subheadings-found-on-the-homepage-img.webp' ),
				esc_attr( 'No Subheadings Found on the Homepage' )
			),
		];

		if ( ! Utils::get_instance()->is_pro_active() ) {
			$descriptions[] = sprintf(
				'<h6>💬 %s </h6>',
				__( 'Need Help?', 'surerank' )
			);
			$descriptions[] = __( 'SureRank Pro helps you identify heading structure issues and suggests improvements using AI, so your pages stay clear and well organized.', 'surerank' );
		}

		$h2_analysis = $this->analyze_h2( $xpath );

		$exists  = $h2_analysis['exists'];
		$status  = 'success';
		$title   = __( 'Your homepage contains at least one H2 heading', 'surerank' );
		$heading = __( 'Subheadings Found on the Homepage', 'surerank' );

		if ( ! $h2_analysis['exists'] ) {
			$status  = 'warning';
			$title   = __( 'Your homepage does not currently contain any H2 subheadings.', 'surerank' );
			$heading = __( 'No Subheadings Found on the Homepage', 'surerank' );
		}

		return [
			'exists'      => $exists,
			'status'      => $status,
			'description' => $descriptions,
			'message'     => $title,
			'heading'     => $heading,
			'not_fixable' => true,
		];
	}

	/**
	 * Analyze images for ALT attributes.
	 *
	 * @param DOMXPath $xpath XPath instance.
	 * @return array<string, mixed>
	 */
	public function analyze_images( DOMXPath $xpath ) {
		$images = $xpath->query( '//img' );
		if ( ! $images instanceof DOMNodeList ) {
			return [
				'exists'      => false,
				'status'      => 'warning',
				'description' => $this->build_image_description( false, 0, 0, [] ),
				'message'     => __( 'No images found on the homepage.', 'surerank' ),
			];
		}

		$total              = $images->length;
		$missing_alt        = 0;
		$missing_alt_images = [];

		foreach ( $images as $img ) {
			if ( $img instanceof DOMElement ) {
				$src = $img->hasAttribute( 'src' )
					? trim( $img->getAttribute( 'src' ) )
					: '';
				if ( ! $img->hasAttribute( 'alt' ) || empty( trim( $img->getAttribute( 'alt' ) ) ) ) {
					$missing_alt++;
					$missing_alt_images[] = $src;
				}
			}
		}

		$exists       = $total > 0;
		$is_optimized = $missing_alt === 0;

		if ( ! $exists ) {
			$description = [
				__( 'Images help visitors quickly understand what your site is about and make the page feel more engaging.', 'surerank' ),
				__( 'They also play an important role in SEO. Images help break up content, add context, and can appear in image search results when optimized properly.', 'surerank' ),
				sprintf(
					'<h6>🛠️ %s </h6>',
					__( 'Where to Update It', 'surerank' )
				),
				__( 'Edit your homepage content and add images where they naturally fit.', 'surerank' ),
				__( 'If your homepage is a page:', 'surerank' ),
				[
					'list' => [
						sprintf(
							/* translators: %1$s: URL to the Home Page settings, %2$s: Anchor text "Home Page" */
							__( "Go to Pages ⇾ <a href='%1\$s' target='_blank' rel='noopener'>%2\$s</a>", 'surerank' ),
							$this->get_homepage_settings_url(),
							__( 'Home Page', 'surerank' ),
						),
						__( 'Add images', 'surerank' ),
						__( 'Update the page', 'surerank' ),
					],
				],
				sprintf(
					'<h6>✏️ %s </h6>',
					__( 'Update Here', 'surerank' )
				),
				sprintf(
					"<img class='w-full h-full' src='%s' alt='%s' />",
					esc_attr( 'https://surerank.com/wp-content/uploads/2026/02/images-on-homepage.webp' ),
					esc_attr( 'Images on Homepage' )
				),
			];

			if ( ! Utils::get_instance()->is_pro_active() ) {
				$description[] = sprintf(
					'<h6>💬 %s </h6>',
					__( 'Need Help?', 'surerank' )
				);
				$description[] = __( 'SureRank Pro helps you optimize images for SEO, including automatically generating alt text using AI, so your images are clear for both visitors and search engines.', 'surerank' );
			}

			return [
				'exists'      => false,
				'status'      => 'warning',
				'description' => $description,
				'heading'     => __( 'Images on Homepage', 'surerank' ),
				'message'     => __( 'Your homepage does not currently contain any images.', 'surerank' ),
			];
		}

		$title = $is_optimized ? __( 'Images on the homepage have alt text attributes.', 'surerank' ) : __( 'One or more images on your homepage do not have ALT text.', 'surerank' );

		return [
			'exists'      => $exists,
			'status'      => $is_optimized ? 'success' : 'warning',
			'description' => $this->build_image_description( $exists, $total, $missing_alt, $missing_alt_images ),
			'message'     => $title,
			'heading'     => $is_optimized ? __( 'Images on Homepage', 'surerank' ) : __( 'Homepage Image ALT Text Is Missing', 'surerank' ),
		];
	}

	/**
	 * Analyze internal links if any.
	 *
	 * @param DOMXPath $xpath XPath instance.
	 * @return array<string, mixed>
	 */
	public function analyze_links( DOMXPath $xpath ) {
		$links    = $xpath->query( '//a[@href]' );
		$helptext = [
			__( 'Internal links help visitors navigate your website and discover important content. They also help search engines understand how your pages are connected and which ones matter most.', 'surerank' ),
			__( 'When a homepage has no internal links, visitors may not know where to go next. Search engines also have a harder time crawling and prioritizing your pages.', 'surerank' ),
			sprintf(
				'<h6>✅ %s </h6>',
				__( 'Best Practice', 'surerank' )
			),
			[
				'list' => [
					__( 'Link from your homepage to key pages like about, products, etc.', 'surerank' ),
					__( 'Use clear, descriptive link text so visitors know what to expect.', 'surerank' ),
					__( 'Keep links natural and helpful. Avoid adding links just for SEO.', 'surerank' ),
				],
			],
			sprintf(
				'<h6>🛠️ %s </h6>',
				__( 'Where to Update It', 'surerank' )
			),
			__( 'Edit your homepage content and add links to relevant pages.', 'surerank' ),
			[
				'list' => [
					__( 'If your homepage is a page:', 'surerank' ),
					sprintf(
						__( "Go to Pages ⇾ <a href='%1\$s' target='_blank' rel='noopener'>%2\$s</a>", 'surerank' ),
						$this->get_homepage_settings_url(),
						__( 'Home Page', 'surerank' ),
					),
					__( 'Add links within text, buttons, or navigation sections', 'surerank' ),
					__( 'Update the page', 'surerank' ),
				],
			],
			sprintf(
				'<h6>✏️ %s </h6>',
				__( 'Update Here', 'surerank' )
			),
			sprintf(
				"<img class='w-full h-full' src='%s' alt='%s' />",
				esc_attr( 'https://surerank.com/wp-content/uploads/2026/02/no-internal-links-found-on-the-homepage.webp' ),
				esc_attr( 'No Internal Links Found on the Homepage' )
			),
		];

		if ( ! Utils::get_instance()->is_pro_active() ) {
			$helptext[] = sprintf(
				'<h6>💬 %s </h6>',
				__( 'Need Help?', 'surerank' )
			);
			$helptext[] = __( 'SureRank Pro helps you find internal linking opportunities and suggests improvements using AI, so your site stays well connected and easy to explore.', 'surerank' );
		}

		if ( ! $links instanceof DOMNodeList ) {
			return [
				'exists'      => false,
				'status'      => 'warning',
				'description' => $helptext,
				'heading'     => __( 'No Internal Links Found on the Homepage', 'surerank' ),
				'message'     => __( 'Your homepage does not currently link to other pages on your site.', 'surerank' ),
				'not_fixable' => true,
			];
		}

		$internal       = 0;
		$internal_links = [];

		foreach ( $links as $link ) {
			if ( $link instanceof DOMElement ) {
				$href = $link->getAttribute( 'href' );
				if ( empty( $href ) || strpos( $href, '#' ) === 0 ) {
					continue;
				}
				$host = wp_parse_url( $href, PHP_URL_HOST );
				if ( ! is_string( $host ) || $host === $this->base_url ) {
					$internal++;
					$internal_links[] = $href;
				}
			}
		}

		$exists  = $internal > 0;
		$title   = $exists ? __( 'Your homepage currently links to other pages on your site.', 'surerank' ) : __( 'Your homepage does not currently link to other pages on your site.', 'surerank' );
		$heading = $exists ? __( 'Internal Links Found on the Homepage', 'surerank' ) : __( 'No Internal Links Found on the Homepage', 'surerank' );
		return [
			'exists'      => $exists,
			'status'      => $exists ? 'success' : 'warning',
			'description' => $helptext,
			'message'     => $title,
			'heading'     => $heading,
			'not_fixable' => true,
		];
	}

	/**
	 * Analyze canonical tag.
	 *
	 * @param DOMXPath $xpath XPath instance.
	 * @return array<string, mixed>
	 */
	public function analyze_canonical( DOMXPath $xpath ) {
		$helptext = [
			__( 'A canonical URL tells search engines which version of a page should be treated as the main one.', 'surerank' ),
			__( 'Without it, search engines may see multiple versions of your homepage as separate pages, which can dilute SEO signals and cause confusion.', 'surerank' ),

			sprintf(
				'<h6>🛠️ %s </h6>',
				__( 'Where to Update It', 'surerank' )
			),
			__( 'You can set the canonical URL directly from the SureRank meta box.', 'surerank' ),
			[
				'list' => [
					__( 'Edit your homepage', 'surerank' ),
					__( 'Open the Advanced tab in the SureRank meta box', 'surerank' ),
					__( 'Enter the correct URL in the Canonical Tag field', 'surerank' ),
					__( 'Save your changes', 'surerank' ),
				],
			],

			sprintf(
				'<h6>✏️ %s </h6>',
				__( 'Update Here', 'surerank' )
			),

			sprintf(
				"<img class='w-full h-full' src='%s' alt='%s' />",
				esc_attr( 'https://surerank.com/wp-content/uploads/2026/03/no-canonical-url-set-for-homepage-visual.webp' ),
				esc_attr( 'No Canonical URL Set for Homepage' )
			),
		];

		if ( ! Utils::get_instance()->is_pro_active() ) {
			$helptext[] = sprintf(
				'<h6>💬 %s </h6>',
				__( 'Need Help?', 'surerank' )
			);
			$helptext[] = __( 'SureRank Pro helps fix SEO issues across your website using AI, without manual effort.', 'surerank' );
		}

		$canonical = $xpath->query( '//link[@rel="canonical"]/@href' );
		if ( ! $canonical instanceof DOMNodeList ) {
			return $this->build_error_response(
				__( 'No Canonical URL Set for Homepage', 'surerank' ),
				$helptext,
				__( 'Canonical tag is not present on the homepage.', 'surerank' ),
				'warning'
			);
		}

		$exists  = $canonical->length > 0;
		$heading = $exists ? __( 'Canonical URL Set for Homepage', 'surerank' ) : __( 'No Canonical URL Set for Homepage', 'surerank' );
		$title   = $exists ? __( 'Your homepage currently has a canonical URL set.', 'surerank' ) : __( 'Your homepage does not currently have a canonical URL set.', 'surerank' );
		return [
			'exists'      => $exists,
			'status'      => $exists ? 'success' : 'warning',
			'description' => $helptext,
			'message'     => $title,
			'heading'     => $heading,
		];
	}

	/**
	 * Analyze indexing meta tag.
	 *
	 * @param DOMXPath $xpath XPath instance.
	 * @return array<string, mixed>
	 */
	public function analyze_indexing( DOMXPath $xpath ) {
		$robots = $xpath->query( '//meta[@name="robots"]/@content' );

		$description = [
			__( 'This means search engines are not allowed to show your homepage in search results.', 'surerank' ),
			__( 'When a homepage is not indexable, people searching for your site may not find it at all, even if your content is published and live.', 'surerank' ),
			__( 'In most cases, this happens when indexing is turned off by mistake.', 'surerank' ),
		];

		$show_static_page = get_option( 'show_on_front' ) === 'page';

		// Provide different instructions depending on the reading settings.
		if ( $show_static_page ) {
			$description[] = sprintf(
				'<h6>🛠️ %s </h6>',
				__( 'Where to Update It', 'surerank' )
			);
			$description[] = [
				'list' => [
					sprintf(
					/* translators: %1$s: URL to the Home Page settings, %2$s: Anchor text "Home Page" */
						__( "Go to Pages ⇾ <a href='%1\$s' target='_blank' rel='noopener'>%2\$s</a> ⇾ SureRank ⇾ Optimize ⇾ Advanced.", 'surerank' ),
						$this->get_homepage_settings_url(),
						__( 'Home Page', 'surerank' )
					),
					__( 'Make sure the option to prevent indexing of No Index is turned off.', 'surerank' ),
				],
			];
		} else {
			$description[] = sprintf(
				'<h6>🛠️ %s </h6>',
				__( 'Where to Update It', 'surerank' )
			);
			$description[] = [
				'list' => [
					sprintf(
					/* translators: %1$s: URL to the SureRank homepage advanced settings, %2$s: Anchor text "Advanced" */
						__( "From the SureRank Dashboard, go to General ⇾ Home Page ⇾ <a href='%1\$s' target='_blank' rel='noopener'>%2\$s</a>", 'surerank' ),
						$this->get_homepage_settings_url( 'homepage/advanced' ),
						__( 'Advanced', 'surerank' )
					),
					__( 'Here, you can update the Robot Instructions for your Home Page to control how search engines interact with it.', 'surerank' ),
				],
			];
		}

		$description[] = sprintf(
			'<h6>✏️ %s </h6>',
			__( 'Update Here', 'surerank' )
		);
		$description[] = sprintf(
			"<img class='w-full h-full' src='%s' alt='%s' />",
			$show_static_page ? esc_attr( 'https://surerank.com/wp-content/uploads/2026/02/home-page-indexable.webp' ) : esc_attr( 'https://surerank.com/wp-content/uploads/2026/03/homepage-indexable-when-set-as-latest-post.webp' ),
			esc_attr( 'Home Page Indexable' )
		);

		if ( ! Utils::get_instance()->is_pro_active() ) {
			$description[] = sprintf(
				'<h6>💬 %s </h6>',
				__( 'Need Help?', 'surerank' )
			);
			$description[] = __( 'SureRank Pro helps you detect and fix indexing issues using AI assistant, so your pages are visible and searchable.', 'surerank' );
		}

		if ( ! $robots instanceof DOMNodeList ) {
			return [
				'exists'      => false,
				'status'      => 'warning',
				'description' => $description,
				'message'     => __( 'Your homepage is currently not indexable by search engines. ', 'surerank' ),
			];
		}

		$exists = $robots->length > 0;

		$content = '';

		if ( $exists ) {
			$robots_node = $robots->item( 0 );
			if ( $robots_node instanceof DOMAttr ) {
				$content = trim( $robots_node->value );
			}
		}

		$is_indexable = $exists ? strpos( $content, 'noindex' ) === false : true;
		$title        = $is_indexable ? __( 'Your homepage is currently indexable by search engines. ', 'surerank' ) : __( 'Your homepage is currently not indexable by search engines. ', 'surerank' );
		$heading      = __( 'Home Page Indexable', 'surerank' );

		return [
			'exists'      => $exists,
			'status'      => $is_indexable ? 'success' : 'error',
			'description' => $description,
			'message'     => $title,
			'heading'     => $heading,
		];
	}

	/**
	 * Analyze homepage reachability.
	 *
	 * @return array<string, mixed>
	 */
	public function analyze_reachability() {
		$home_url     = home_url();
		$is_reachable = $this->base_url === wp_parse_url( $home_url, PHP_URL_HOST ) && ! is_wp_error( $this->html_content );

		$working_heading = __( 'Home Page is Reachable', 'surerank' );
		$working_label   = __( 'Your homepage is currently accessible and loading correctly.', 'surerank' );

		$not_working_heading = __( 'Home Page is Not Reachable', 'surerank' );
		$not_working_label   = __( 'Your homepage is currently not accessible or is returning an error.', 'surerank' );

		$description = [
			__( 'Your homepage is usually the first page visitors see and the main entry point search engines use to understand your site. It acts as the starting point for crawling and navigation.', 'surerank' ),
			__( 'When the homepage cannot be reached, visitors may see an error page and leave immediately. Search engines may also struggle to crawl or index your site properly if they cannot access this page.', 'surerank' ),
			__( 'This issue can affect the visibility of your entire site, not just the homepage.', 'surerank' ),

			sprintf(
				'<h6>🛠️ %s </h6>',
				__( 'Where to Update It', 'surerank' )
			),
			__( 'You can review and fix this from your WordPress settings.', 'surerank' ),
			[
				'list' => [
					__( 'Go to Settings ⇾ Reading', 'surerank' ),
					__( 'Check which page is set as your homepage', 'surerank' ),
					__( 'Make sure the selected page exists and is published', 'surerank' ),
				],
			],

			sprintf(
				'<h6>✏️ %s </h6>',
				__( 'Update Here', 'surerank' )
			),

			sprintf(
				"<img class='w-full h-full' src='%s' alt='%s' />",
				esc_attr( 'https://surerank.com/wp-content/uploads/2026/02/homepage-is-not-reachable.webp' ),
				esc_attr( 'Homepage is Not Reachable' )
			),

			__( 'If you are using a static homepage, open the page directly and confirm it loads without errors. Also check that no redirects, maintenance plugins, or access restrictions are blocking it.', 'surerank' ),
		];

		if ( ! Utils::get_instance()->is_pro_active() ) {
			$description[] = sprintf(
				'<h6>💬 %s </h6>',
				__( 'Need Help?', 'surerank' )
			);
			$description[] = __( 'SureRank Pro users get access to our support team, available 24×7, to help identify homepage access and visibility issues.', 'surerank' );
		}

		if ( ! $is_reachable ) {
			$response     = $this->scraper->fetch( $home_url );
			$is_reachable = ! is_wp_error( $response );
		}

		$title = $is_reachable ? $working_label : $not_working_label;
		return [
			'exists'      => true,
			'status'      => $is_reachable ? 'success' : 'error',
			'description' => $description,
			'message'     => $title,
			'heading'     => $is_reachable ? $working_heading : $not_working_heading,
			'not_fixable' => true,
		];
	}

	/**
	 * Analyze secure HTTPS connection and SSL certificate validity.
	 *
	 * @return array<string, mixed>
	 */
	public function analyze_secure_connection(): array {
		$header_url    = $this->fetch_header( 'x-final-url' );
		$effective_url = $header_url !== '' ? $header_url : home_url();
		$is_https      = strpos( $effective_url, 'https://' ) === 0;

		$working_heading = __( 'This site is using HTTPS.', 'surerank' );
		$working_label   = __( 'Your site is currently served over a secure HTTPS connection.', 'surerank' );

		$not_working_heading = __( 'This site is not using HTTPS.', 'surerank' );
		$not_working_label   = __( 'Your site is not currently served over a secure HTTPS connection.', 'surerank' );

		$description = [
			sprintf(
				"<img class='w-full h-full' src='%s' alt='%s' />",
				esc_attr( 'https://surerank.com/wp-content/uploads/2026/02/this-site-is-not-using-https.webp' ),
				esc_attr( 'Secure Connection (HTTPS)' )
			),
			__( 'HTTPS encrypts the data between your site and its visitors. This helps protect sensitive information and keeps your site secure.', 'surerank' ),
			__( 'Search engines also prefer secure websites. Browsers may show a warning for sites without HTTPS, which can make visitors hesitant to continue.', 'surerank' ),
			__( 'Enabling HTTPS is an important step for trust and SEO.', 'surerank' ),

			sprintf(
				'<h6>🛠️ %s </h6>',
				__( 'Where to Update It', 'surerank' )
			),
			__( 'HTTPS is set up at the hosting or server level.', 'surerank' ),
			__( 'Most hosting providers offer free SSL certificates and can enable HTTPS for you.', 'surerank' ),
			__( 'If you are unsure how to set this up, your hosting provider can help. You can send them the message below:', 'surerank' ),

			'<div class="bg-gray-200 p-2 rounded"><pre class="whitespace-pre-wrap break-normal">' .
				esc_html__( 'Hello,', 'surerank' ) . "\n" .
				esc_html__( 'I need help enabling HTTPS on my website: [enter-your-website-URL-here]', 'surerank' ) . "\n" .
				esc_html__( 'Currently, my site is not being served over a secure HTTPS connection.', 'surerank' ) . "\n" .
				esc_html__( 'I would like you to enable an SSL certificate for my domain and ensure that all traffic is redirected from HTTP to HTTPS.', 'surerank' ) . "\n" .
				esc_html__( 'Please feel free to set this up for me directly.', 'surerank' ) . "\n" .
				esc_html__( 'Thank you for your help.', 'surerank' ) .
			'</pre></div>',
		];

		if ( ! Utils::get_instance()->is_pro_active() ) {
			$description[] = sprintf(
				'<h6>💬 %s </h6>',
				__( 'Need Help?', 'surerank' )
			);
			$description[] = __( 'SureRank Pro users get access to our support team, available 24×7, to help with setup and plugin related questions.', 'surerank' );
		}

		$is_secure = $is_https && $this->is_ssl_certificate_valid( $effective_url );
		$title     = $is_secure ? $working_label : $not_working_label;
		$heading   = $is_secure ? $working_heading : $not_working_heading;

		return [
			'exists'      => true,
			'status'      => $is_secure ? 'success' : 'warning',
			'description' => $description,
			'message'     => $title,
			'heading'     => $heading,
			'not_fixable' => true,
		];
	}

	/**
	 * Analyze open graph tags.
	 *
	 * @param DOMXPath $xpath XPath instance.
	 * @return array<string, mixed>
	 */
	public function open_graph_tags( DOMXPath $xpath ): array {
		$og_tags           = $xpath->query( "//meta[starts-with(@property, 'og:')]" );
		$heading           = __( 'Homepage Open Graph Tags', 'surerank' );
		$working_label     = __( 'Open Graph tags are set for the homepage.', 'surerank' );
		$not_working_label = __( 'Open Graph tags are not set for the homepage.', 'surerank' );
		$helptext          = [
			__( 'When someone shares your site on social media or in a messaging app, the platform shows a preview of your website.', 'surerank' ),
			__( 'Without Open Graph tags, the platform may pull random content or images for the preview.', 'surerank' ),

			sprintf(
				"<img class='w-full h-full' src='%s' alt='%s' />",
				esc_attr( 'https://surerank.com/wp-content/uploads/2026/03/homepage-open-graph-tags-before-img.webp ' ),
				esc_attr( 'Where to Update Open Graph Tags' )
			),

			__( 'Open Graph tags help you control what people see when your site is shared.', 'surerank' ),

			sprintf(
				"<img class='w-full h-full' src='%s' alt='%s' />",
				esc_attr( 'https://surerank.com/wp-content/uploads/2026/03/homepage-open-graph-tags-before-after-img.webp' ),
				esc_attr( 'Where to Update Open Graph Tags' )
			),

			__( 'A clean and attractive preview makes your site look more trustworthy and encourages people to click.', 'surerank' ),

			sprintf(
				'<h6>🛠️ %s </h6>',
				__( 'Where to Update It', 'surerank' )
			),
		];

		$show_static_page = get_option( 'show_on_front' ) === 'page';

		if ( ! $show_static_page ) {
			$helptext[] = sprintf(
				/* translators: %1$s: URL to the SureRank homepage social settings, %2$s: Anchor text "Social" */
				__( "From the SureRank Dashboard, go to General ⇾ Home Page ⇾ <a href='%1\$s' target='_blank' rel='noopener'>%2\$s</a>.", 'surerank' ),
				$this->get_homepage_settings_url( 'homepage/social' ),
				__( 'Social', 'surerank' )
			);
			$helptext[] = __( 'Here, you can update the title, description, and image for your home page. SureRank will also show a preview of how your website will look on Facebook and X.', 'surerank' );
		} else {
			$helptext[] = sprintf(
				/* translators: %1$s: URL to the Home Page settings, %2$s: Anchor text "Home Page" */
				__( "Go to Pages ⇾ <a href='%1\$s' target='_blank' rel='noopener'>%2\$s</a> ⇾ SureRank ⇾ Optimize ⇾ Social.", 'surerank' ),
				$this->get_homepage_settings_url(),
				__( 'Home Page', 'surerank' )
			);
			$helptext[] = __( 'Here, you can update the title, description, and image for your home page. SureRank will also show a preview of how your website will look on Facebook and X.', 'surerank' );
		}

		$helptext[] = sprintf(
			'<h6>✏️ %s </h6>',
			__( 'Update Here', 'surerank' )
		);

		if ( ! $show_static_page ) {
			$helptext[] = sprintf(
				"<img class='w-full h-full' src='%s' alt='%s' />",
				esc_attr( 'https://surerank.com/wp-content/uploads/2026/02/homepage-open-graph-general-setting.webp' ),
				esc_attr( 'Where to Update Open Graph Tags' )
			);
		} else {
			$helptext[] = sprintf(
				"<img class='w-full h-full' src='%s' alt='%s' />",
				esc_attr( 'https://surerank.com/wp-content/uploads/2026/02/homepage-open-graph-general-on-page.webp' ),
				esc_attr( 'Where to Update Open Graph Tags' )
			);
		}

		if ( ! Utils::get_instance()->is_pro_active() ) {
			$helptext[] = sprintf(
				'<h6>💬 %s </h6>',
				__( 'Need Help?', 'surerank' )
			);
			$helptext[] = __( 'SureRank Pro helps you optimize your homepage for social sharing with AI-powered suggestions and previews.', 'surerank' );
		}

		if ( ! $og_tags instanceof DOMNodeList ) {
			return [
				'exists'      => false,
				'status'      => 'warning',
				'description' => $helptext,
				'message'     => $not_working_label,
				'heading'     => $heading,
			];
		}

		$details        = [];
		$required_tags  = [ 'og:title', 'og:description' ];
		$found_required = [
			'og:title'       => false,
			'og:description' => false,
		];

		foreach ( $og_tags as $tag ) {
			if ( $tag instanceof DOMElement ) {
				$property = $tag->getAttribute( 'property' );
				$content  = $tag->getAttribute( 'content' );

				$details[] = $property . ':' . $content;

				if ( in_array( $property, $required_tags, true ) ) {
					$found_required[ $property ] = true;
				}
			}
		}

		$missing_required = array_keys( array_filter( $found_required, static fn( $found) => ! $found ) );
		if ( ! empty( $missing_required ) ) {
			return [
				'exists'      => ! empty( $details ),
				'status'      => 'warning',
				'description' => $helptext,
				'message'     => $not_working_label,
				'heading'     => $heading,
			];
		}

		return [
			'exists'      => true,
			'status'      => 'success',
			'description' => $helptext,
			'message'     => $working_label,
			'heading'     => $heading,
		];
	}

	/**
	 * Analyze schema meta data.
	 *
	 * @param DOMXPath $xpath XPath instance.
	 * @return array<string, mixed>
	 */
	public function schema_meta_data( DOMXPath $xpath ) {
		$schema_meta_data = $xpath->query( "//script[@type='application/ld+json']" );
		$show_static_page = get_option( 'show_on_front' ) === 'page';

		$working_heading = __( 'Structured Data Found on the Homepage', 'surerank' );
		$working_label   = __( 'The homepage currently includes structured data (schema).', 'surerank' );

		$not_working_heading = __( 'No Structured Data Found on the Homepage', 'surerank' );
		$not_working_label   = __( 'The homepage does not currently include any structured data (schema).', 'surerank' );

		$helptext = [
			__( 'Structured data helps search engines better understand your content and can enable enhanced search results, like rich snippets.', 'surerank' ),
			__( 'SureRank generates structured data automatically when the feature is enabled.', 'surerank' ),
			__( 'Once active, the schema is added to your page behind the scenes and becomes readable by search engines.', 'surerank' ),
		];

		$helptext[] = sprintf(
			'<h6>🛠️ %s </h6>',
			__( 'Where to Update It', 'surerank' )
		);

		if ( $show_static_page ) {
			$helptext[] = [
				'list' => [
					__( 'Go to SureRank → Advanced → Schema', 'surerank' ),
					__( 'Review the Schema or add new', 'surerank' ),
					__( 'Save your changes', 'surerank' ),
				],
			];
			$helptext[] = sprintf(
				'<h6>✏️ %s </h6>',
				__( 'Update Here', 'surerank' )
			);
			$helptext[] = sprintf(
				"<img class='w-full h-full' src='%s' alt='%s' />",
				esc_attr( 'https://surerank.com/wp-content/uploads/2026/03/schema.webp' ),
				esc_attr( 'No Structured Data Found on the Homepage' )
			);
		} else {
			$helptext[] = [
				'list' => [
					__( 'Edit your homepage', 'surerank' ),
					__( 'Open the Schema tab in the SureRank meta box', 'surerank' ),
					__( 'Review the Schema or add new', 'surerank' ),
					__( 'Save your changes', 'surerank' ),
				],
			];
			$helptext[] = sprintf(
				'<h6>✏️ %s </h6>',
				__( 'Update Here', 'surerank' )
			);
			$helptext[] = sprintf(
				"<img class='w-full h-full' src='%s' alt='%s' />",
				esc_attr( 'https://surerank.com/wp-content/uploads/2026/02/no-structured-data-found-on-the-homepage.webp' ),
				esc_attr( 'No Structured Data Found on the Homepage' )
			);
		}

		$helptext[] = sprintf(
			'<h6>🔍 %s </h6>',
			__( 'How to Check It', 'surerank' )
		);
		$helptext[] = __( 'After enabling schema, you can confirm that it is working by checking the live page source and search for schema or application/ld+json.', 'surerank' );
		$helptext[] = __( 'You can also test your page using these tools:', 'surerank' );
		$helptext[] = [
			'list' => [
				sprintf(
					"%s <a href='%s' target='_blank' rel='noopener noreferrer'>%s</a>",
					'Schema Validator:',
					esc_url( 'https://validator.schema.org/' ),
					esc_url( 'https://validator.schema.org/' ),
				),
				sprintf(
					"%s <a href='%s' target='_blank' rel='noopener noreferrer'>%s</a>",
					'Google Rich Results Test:',
					esc_url( 'https://search.google.com/test/rich-results' ),
					esc_url( 'https://search.google.com/test/rich-results' ),
				),
			],
		];
		$helptext[] = __( 'These tools show what structured data search engines can read from your page.', 'surerank' );

		if ( ! Utils::get_instance()->is_pro_active() ) {
			$helptext[] = sprintf(
				'<h6>💬 %s </h6>',
				__( 'Want More?', 'surerank' )
			);
			$helptext[] = __( 'Upgrade to SureRank Pro to unlock advanced schema types like FAQ, How To, and more powerful structured data options to improve your search appearance.', 'surerank' );
		}

		if ( ! $schema_meta_data instanceof DOMNodeList ) {
			return [
				'exists'      => false,
				'status'      => 'suggestion',
				'description' => $helptext,
				'message'     => $not_working_label,
				'heading'     => $not_working_heading,
			];
		}

		if ( ! $schema_meta_data->length ) {
			return [
				'exists'      => false,
				'status'      => 'suggestion',
				'description' => $helptext,
				'message'     => $not_working_label,
				'heading'     => $not_working_heading,
			];
		}

		return [
			'exists'      => true,
			'status'      => 'success',
			'description' => $helptext,
			'message'     => $working_label,
			'heading'     => $working_heading,
		];
	}

	/**
	 * Analyze WWW canonicalization.
	 *
	 * @return array<string, mixed>
	 */
	public function analyze_www_canonicalization(): array {
		$home_url = home_url();
		$parsed   = wp_parse_url( $home_url );

		$helptext = [
			__( 'Your website can usually be accessed in two ways. One with www and one without it.', 'surerank' ),
			__( 'https://example.com', 'surerank' ),
			__( 'https://www.example.com', 'surerank' ),

			__( 'Even though both show the same site, search engines treat them as different versions. This can look like duplicate pages and split your SEO strength across both URLs.', 'surerank' ),
			__( 'To keep things clean, choose one version as your main address.', 'surerank' ),
			__( 'Decide whether you want to use www or non www, then set up a redirect so all traffic goes to that version.', 'surerank' ),

			sprintf(
				'<h6> %s </h6>',
				__( '🔧 Where to set it', 'surerank' )
			),
			__( 'This is usually handled at the server or hosting level.', 'surerank' ),
			__( 'If you are unsure how to set this up, your hosting provider can help. You can send them the message below:', 'surerank' ),

			'<div class="bg-gray-200 p-2 rounded"><pre class="whitespace-pre-wrap break-normal">' .
				esc_html__( 'Hello,', 'surerank' ) . "\n" .
				esc_html__( 'I need help setting a preferred version of my website URL.', 'surerank' ) . "\n" .
				esc_html__( 'Right now, my site loads on both versions:', 'surerank' ) . "\n" .
				esc_html__( 'https://example.com', 'surerank' ) . "\n" .
				esc_html__( 'https://www.example.com', 'surerank' ) . "\n" .
				esc_html__( 'I would like to redirect all traffic from https://www.example.com to https://example.com and use the non WWW version as my main address.', 'surerank' ) . "\n" .
				esc_html__( 'Please feel free to set this up for me directly. Thank you for your help.', 'surerank' ) .
			'</pre></div>',
		];

		if ( ! Utils::get_instance()->is_pro_active() ) {
			$helptext[] = sprintf(
				'<h6> %s </h6>',
				__( '💬 Need Help?', 'surerank' )
			);
			$helptext[] = __( 'SureRank Pro users get access to our support team, available 24×7, for setup and plugin related questions.', 'surerank' );
		}

		$working_heading = __( 'WWW and non-WWW versions are redirecting properly.', 'surerank' );
		$working_label   = __( 'The site correctly redirects between the www and non-www versions.', 'surerank' );

		$not_working_heading = __( 'WWW and non-WWW versions are not redirecting properly.', 'surerank' );
		$not_working_label   = __( 'The site does not correctly redirect between the www and non-www versions.', 'surerank' );

		if ( ! is_array( $parsed ) || ! isset( $parsed['host'], $parsed['scheme'] ) ) {
			return [
				'exists'      => false,
				'status'      => 'error',
				'description' => $helptext,
				'message'     => $not_working_label,
				'heading'     => $not_working_heading,
				'not_fixable' => true,
			];
		}

		$host    = (string) $parsed['host'];
		$scheme  = (string) $parsed['scheme'];
		$timeout = 8;

		// Skip www canonicalization check for subdomain sites (e.g., subdomain.example.com).
		// This check only applies to root domains (example.com vs www.example.com).
		$host_parts   = explode( '.', $host );
		$is_subdomain = count( $host_parts ) > 2 && ! str_starts_with( $host, 'www.' );

		if ( $is_subdomain ) {
			return [
				'exists'      => true,
				'status'      => 'success',
				'description' => $helptext,
				'message'     => $working_label,
				'heading'     => $working_heading,
				'not_fixable' => true,
			];
		}

		$is_www    = str_starts_with( $host, 'www.' );
		$alternate = $is_www ? preg_replace( '/^www\./', '', $host ) : "www.{$host}";
		$test_url  = "{$scheme}://{$alternate}";

		// Pull the Location header (empty string on failure).
		$response = wp_safe_remote_head(
			$test_url,
			[
				'redirection' => 5,
				'timeout'     => $timeout,
			]
		);

		if ( is_wp_error( $response ) ) {
			return [
				'exists'      => false,
				'status'      => 'error',
				'description' => $helptext,
				'message'     => $not_working_label,
				'heading'     => $not_working_heading,
				'not_fixable' => true,
			];
		}

		$status_code  = (int) wp_remote_retrieve_response_code( $response );
		$raw_location = wp_remote_retrieve_header( $response, 'location' );

		$location = is_array( $raw_location )
			? ( $raw_location[0] ?? '' )
			: ( ! empty( $raw_location ) ? $raw_location : '' );

		// Normalize the final URL.
		if ( str_starts_with( $location, '/' ) ) {
			$final_url = "{$scheme}://{$host}{$location}";
		} elseif ( $location !== '' ) {
			$final_url = $location;
		} else {
			$final_url = $test_url;
		}

		$final_host        = (string) ( wp_parse_url( $final_url, PHP_URL_HOST ) ?? '' );
		$redirect_happened = $location !== '';
		$redirect_ok       = $redirect_happened ? $final_host === $host : true;
		$request_ok        = $status_code >= 200 && $status_code < 300;

		$all_good = $redirect_ok && $request_ok;

		$title   = $all_good ? $working_label : $not_working_label;
		$heading = $all_good ? $working_heading : $not_working_heading;
		return [
			'exists'      => true,
			'status'      => $all_good ? 'success' : 'warning',
			'description' => $helptext,
			'message'     => $title,
			'heading'     => $heading,
			'not_fixable' => true,
		];
	}

	/**
	 * Check if SSL certificate is valid for a given URL.
	 *
	 * Uses WordPress HTTP API with sslverify enabled.
	 * If the request fails due to SSL issues, certificate is invalid.
	 *
	 * @since 1.6.4
	 * @param string $url The URL to check.
	 * @return bool True if SSL certificate is valid.
	 */
	private function is_ssl_certificate_valid( string $url ): bool {
		if ( empty( $url ) ) {
			return true;
		}

		$response = wp_safe_remote_head(
			$url,
			[
				'sslverify' => true,
				'timeout'   => 10, // phpcs:ignore WordPressVIPMinimum.Performance.RemoteRequestTimeout.timeout_timeout
			]
		);

		return ! is_wp_error( $response );
	}

	/**
	 * Initialize the analyzer by fetching and parsing the URL.
	 *
	 * @param string $url The URL to analyze.
	 * @return void
	 */
	private function initialize( string $url ) {

		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			$this->errors[] = __( 'Invalid URL.', 'surerank' );
			return;
		}

		$parsed_url         = wp_parse_url( $url, PHP_URL_HOST );
		$this->base_url     = is_string( $parsed_url ) ? $parsed_url : '';
		$this->html_content = $this->scraper->fetch( $url );

		if ( is_wp_error( $this->html_content ) ) {
			$this->errors[] = $this->html_content->get_error_message();
			return;
		}

		$parsed_dom = $this->parser->parse( $this->html_content );
		if ( is_wp_error( $parsed_dom ) ) {
			$this->errors[] = $parsed_dom->get_error_message();
			return;
		}

		$this->dom = $parsed_dom;
	}

	/**
	 * Get title analysis message.
	 *
	 * @param bool   $exists Whether title exists.
	 * @param int    $length Title length.
	 * @param string $status Status of the analysis.
	 * @return string
	 */
	private function get_title_message( bool $exists, int $length, string $status ) {
		if ( ! $exists ) {
			return __( 'Search engine title is missing on the homepage.', 'surerank' );
		}

		if ( $status === 'warning' ) {
			/* translators: %1$d is the maximum recommended length of the title. */
			$message = __( 'Search engine title of the home page exceeds %1$d characters.', 'surerank' );
			return sprintf( $message, Get::TITLE_LENGTH );
		}

		if ( $status === 'success' ) {
			return __( 'Search engine title of the home page is present and under 60 characters.', 'surerank' );
		}

		return __( 'Search engine title is present and under 60 characters.', 'surerank' );
	}

	/**
	 * Get meta description analysis message.
	 *
	 * @param bool   $exists Whether meta description exists.
	 * @param int    $length Meta description length.
	 * @param string $status Status of the analysis.
	 * @return string
	 */
	private function get_meta_description_message( bool $exists, int $length, string $status ) {
		if ( ! $exists ) {
			return __( 'Search engine description is missing on the homepage.', 'surerank' );
		}

		if ( $status === 'warning' ) {
			/* translators: %1$d is the maximum length of the meta description. */
			$message = __( 'Search engine description of the home page exceeds %1$d characters.', 'surerank' );
			return sprintf( $message, Get::DESCRIPTION_LENGTH );
		}

		if ( $status === 'success' ) {
			return __( 'Search engine description of the home page is present and under 160 characters.', 'surerank' );
		}

		return __( 'Search engine description is missing on the homepage.', 'surerank' );
	}

	/**
	 * Analyze H1 headings.
	 *
	 * @param DOMXPath $xpath XPath instance.
	 * @return array{
	 *     exists: bool,
	 *     is_optimized: bool,
	 *     details: array{
	 *         count: int,
	 *         contents: array<string>
	 *     }
	 * }
	 */
	private function analyze_h1( DOMXPath $xpath ): array {
		$h1s = $xpath->query( '//h1' );
		if ( ! $h1s instanceof DOMNodeList ) {
			return [
				'exists'       => false,
				'is_optimized' => false,
				'details'      => [
					'count'    => 0,
					'contents' => [],
				],
			];
		}

		$exists   = $h1s->length > 0;
		$count    = $h1s->length;
		$contents = [];

		if ( $exists ) {
			foreach ( $h1s as $h1_node ) {
				if ( $h1_node instanceof DOMElement ) {
					$contents[] = trim( $h1_node->textContent );
				}
			}
		}

		return [
			'exists'       => $exists,
			'is_optimized' => $count === 1,
			'details'      => [
				'count'    => $count,
				'contents' => $contents,
			],
		];
	}

	/**
	 * Analyze H2 headings.
	 *
	 * @param DOMXPath $xpath XPath instance.
	 * @return array<string, mixed>
	 */
	private function analyze_h2( DOMXPath $xpath ) {
		$h2s = $xpath->query( '//h2' );
		if ( ! $h2s instanceof DOMNodeList ) {
			return [
				'exists'       => false,
				'is_optimized' => false,
				'details'      => [
					'count'    => 0,
					'contents' => [],
				],
			];
		}

		$exists   = $h2s->length > 0;
		$count    = $h2s->length;
		$contents = [];

		if ( $exists ) {
			foreach ( $h2s as $h2_node ) {
				if ( $h2_node instanceof DOMElement ) {
					$contents[] = trim( $h2_node->textContent );
				}
			}
		}

		return [
			'exists'       => $exists,
			'is_optimized' => $count >= 1,
			'details'      => [
				'count'    => $count,
				'contents' => $contents,
			],
		];
	}

	/**
	 * Build image analysis description.
	 *
	 * @param bool          $exists Whether images exist.
	 * @param int           $total Total number of images.
	 * @param int           $missing_alt Number of images missing ALT.
	 * @param array<string> $missing_alt_images Images missing ALT attributes.
	 *  @return array<int, array<string, array<int, string>|string>|string>
	 */
	private function build_image_description( bool $exists, int $total, int $missing_alt, array $missing_alt_images ) {
		$list = [];
		if ( $missing_alt !== 0 ) {
			foreach ( $missing_alt_images as $image ) {
				if ( ! in_array( $image, $list ) ) {
					$list[] = esc_html( $image );
				}
			}
		}

		$grid_html = '';
		if ( ! empty( $missing_alt_images ) ) {
			$grid_html = '<div class="columns-3 gap-2 my-4">';
			foreach ( $missing_alt_images as $src ) {
				$grid_html .= sprintf(
					'<img src="%s" alt="%s" class="w-full h-auto object-cover border border-solid border-border-subtle rounded mb-4 break-inside-avoid" />',
					esc_attr( $src ),
					esc_attr( __( 'Image missing ALT text', 'surerank' ) )
				);
			}
			$grid_html .= '</div>';
		}

		$description = [
			__( 'Images help communicate your message visually, but search engines and screen readers cannot understand images on their own. ALT text is a short written description that explains what an image shows.', 'surerank' ),
			__( 'When ALT text is missing, visitors who rely on assistive tools may not understand the content of the image. Search engines also lose helpful context about what the image represents.', 'surerank' ),
			__( 'On the homepage, where images often support your main message, missing ALT text can affect both accessibility and clarity.', 'surerank' ),

			sprintf(
				'<h6>📷 %s </h6>',
				__( 'Images Missing ALT Text', 'surerank' )
			),
			__( 'The following images on your homepage do not currently have ALT text:', 'surerank' ),
		];

		if ( ! empty( $grid_html ) ) {
			$description[] = $grid_html;
		}

		$additional_help = [];
		if ( ! Utils::get_instance()->is_pro_active() ) {
			$additional_help = [
				sprintf(
					'<h6>💬 %s </h6>',
					__( 'Need Help?', 'surerank' )
				),
				__( 'SureRank Pro users get access to our support team, available 24×7, to help review accessibility and content clarity issues across your site.', 'surerank' ),
			];
		}

		return array_merge(
			$description,
			[
				sprintf(
					'<h6>🔧 %s </h6>',
					__( 'Where to Update It', 'surerank' )
				),
				__( 'You can add or update ALT text when editing images in WordPress.', 'surerank' ),
				[
					'list' => [
						__( 'Open the page or post where the image appears', 'surerank' ),
						__( 'Click on the image block', 'surerank' ),
						__( 'Add a short, clear description in the ALT text field', 'surerank' ),
					],
				],

				sprintf(
					'<h6>✏️ %s </h6>',
					__( 'Update Here', 'surerank' )
				),

				sprintf(
					"<img class='w-full h-full' src='%s' alt='%s' />",
					esc_attr( 'https://surerank.com/wp-content/uploads/2026/02/homepage-image-alt-text-is-missing.webp' ),
					esc_attr( 'Homepage Image ALT Text is Missing' )
				),
			],
			$additional_help
		);
	}

	/**
	 * Build error response for invalid queries.
	 *
	 * @param string                                           $title Error title.
	 * @param array<string|array<string>|array<string, mixed>> $helptext Error description (HTML).
	 * @param string                                           $message Error message.
	 * @param string                                           $status Error status.
	 * @return array<string, mixed>
	 */
	private function build_error_response( string $title, array $helptext, string $message, string $status = 'error' ): array {
		$response = [
			'exists'      => false,
			'status'      => $status,
			'description' => $helptext,
			'message'     => $message,
		];
		if ( ! empty( $title ) ) {
			$response['heading'] = $title;
		}

		return $response;
	}

	/**
	 * Get the given header from the last fetched response.
	 *
	 * @param string $header The header name to retrieve.
	 * @return string        Header value, or '' if unavailable.
	 */
	private function fetch_header( string $header ): string {
		if ( is_wp_error( $this->html_content ) || empty( $this->scraper->get_body() ) ) {
			return '';
		}

		$value = $this->scraper->get_header( $header );
		return is_string( $value ) ? $value : '';
	}

	/**
	 * Get the URL of the home page social settings page.
	 *
	 * @param string $target_page Optional. Specific target page within the settings (e.g., 'homepage/social'). Defaults to ''.
	 * @return string URL to the home page settings or specific section within it.
	 */
	private function get_homepage_settings_url( string $target_page = '' ) {
		$page_on_front = intval( Get::option( 'page_on_front' ) );
		if ( get_edit_post_link( $page_on_front ) ) {
			return get_edit_post_link( $page_on_front );
		}
		return Analyzer::get_instance()->get_surerank_settings_url( ! empty( $target_page ) ? $target_page : 'homepage', 'general' );
	}
}
