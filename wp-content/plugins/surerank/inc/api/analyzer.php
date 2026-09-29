<?php
/**
 * Analyzer API class.
 *
 * Handles SEO-related REST API endpoints for the SureRank plugin.
 *
 * @package SureRank\Inc\API
 */

namespace SureRank\Inc\API;

use DOMXPath;
use SureRank\Inc\Analyzer\Scraper;
use SureRank\Inc\Analyzer\SeoAnalyzer;
use SureRank\Inc\Analyzer\Utils;
use SureRank\Inc\Functions\Get;
use SureRank\Inc\Functions\Requests;
use SureRank\Inc\Functions\Settings;
use SureRank\Inc\Functions\Update;
use SureRank\Inc\GoogleSearchConsole\Controller;
use SureRank\Inc\Modules\Nudges\Utils as Nudge_Utils;
use SureRank\Inc\Traits\Get_Instance;
use SureRank\Inc\Traits\Logger;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Analyzer
 *
 * Handles SEO analysis REST API endpoints.
 */
class Analyzer extends Api_Base {

	use Get_Instance;
	use Logger;
	/**
	 * Route for general SEO checks.
	 *
	 * @var string
	 */
	private $general_checks = '/checks/general';

	/**
	 * Route for settings checks.
	 *
	 * @var string
	 */
	private $settings_checks = '/checks/settings';

	/**
	 * Route for other SEO checks.
	 *
	 * @var string
	 */
	private $other_checks = '/checks/other';

	/**
	 * Route for broken links check.
	 *
	 * @var string
	 */
	private $broken_links_check = '/checks/broken-link';

	/**
	 * Page Seo Status
	 *
	 * @var string
	 */
	private $page_seo_checks = '/checks/page';

	/**
	 * Taxonomy Seo Status
	 *
	 * @var string
	 */
	private $taxonomy_seo_checks = '/checks/taxonomy';

	/**
	 * Route for sitemap check.
	 *
	 * @var string
	 */
	private $ignore_checks = '/checks/ignore-site-check';

	/**
	 * Route for post-specific ignore checks.
	 *
	 * @var string
	 */
	private $ignore_post_checks = '/checks/ignore-page-check';

	/**
	 * Register API routes.
	 *
	 * @return void
	 */
	public function register_routes() {
		$namespace = $this->get_api_namespace();
		$this->register_all_analyzer_routes( $namespace );
	}

	/**
	 * Get page SEO checks.
	 *
	 * @param WP_REST_Request<array<string, mixed>> $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_page_seo_checks( $request ) {
		$post_ids = $request->get_param( 'post_ids' );

		if ( empty( $post_ids ) || ! is_array( $post_ids ) ) {
			return $this->create_error_response( __( 'Invalid Post ID.', 'surerank' ) );
		}

		$data = [];
		foreach ( $post_ids as $p_id ) {
			$checks = $this->get_post_checks_data( $p_id );
			if ( is_wp_error( $checks ) ) {
				continue;
			}
			$checks = $this->consolidate_keyword_checks( $checks );
			if ( isset( $checks['broken_links'] ) && ! isset( $checks['broken_links']['type'] ) ) {
				$checks['broken_links']['type'] = 'page';
			}
			$data[ $p_id ] = [
				'checks' => $checks,
			];
		}

		return rest_ensure_response(
			[
				'status'  => 'success',
				'message' => __( 'SEO checks retrieved.', 'surerank' ),
				'data'    => $data,
			]
		);
	}

	/**
	 * Get taxonomy seo checks.
	 *
	 * @param WP_REST_Request<array<string, mixed>> $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_taxonomy_seo_checks( $request ) {
		$term_ids = $request->get_param( 'term_ids' );

		if ( empty( $term_ids ) || ! is_array( $term_ids ) ) {
			return $this->create_error_response( __( 'Invalid Term ID.', 'surerank' ) );
		}

		$data = [];
		foreach ( $term_ids as $p_id ) {
			$checks = $this->get_term_checks_data( $p_id );
			if ( is_wp_error( $checks ) ) {
				continue;
			}
			$checks = $this->consolidate_keyword_checks( $checks );
			if ( isset( $checks['broken_links'] ) && ! isset( $checks['broken_links']['type'] ) ) {
				$checks['broken_links']['type'] = 'page';
			}
			$data[ $p_id ] = [
				'checks' => $checks,
			];
		}

		return rest_ensure_response(
			[
				'status'  => 'success',
				'message' => __( 'SEO checks retrieved.', 'surerank' ),
				'data'    => $data,
			]
		);
	}

	/**
	 * Get general SEO checks for a URL or homepage.
	 *
	 * @param WP_REST_Request<array<string, mixed>> $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_general_checks( $request ) {
		$url   = $request->get_param( 'url' );
		$force = $request->get_param( 'force' );

		if ( $this->cache_exists( 'general' ) && ! $force ) {
			return rest_ensure_response(
				$this->get_cached_response( 'general' )
			);
		}

		return rest_ensure_response(
			$this->run_general_checks( $url )
		);
	}

	/**
	 * Ignore site-wide checks.
	 *
	 * @param WP_REST_Request<array<string, mixed>> $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function ignore_checks( $request ) {
		$id            = $request->get_param( 'id' );
		$ignore_checks = $this->get_ignore_checks();

		if ( ! in_array( $id, $ignore_checks, true ) ) {
			$ignore_checks[] = $id;
		}

		$seo_checks = Get::option( 'surerank_site_seo_checks', [] );
		foreach ( $seo_checks as $key => $check ) {
			if ( isset( $check[ $id ] ) ) {
				$check[ $id ]['ignore'] = true;
				$seo_checks[ $key ]     = $check;
			}
		}

		Update::option( 'surerank_site_seo_checks', $seo_checks );
		Update::option( 'surerank_ignored_site_checks_list', array_values( $ignore_checks ) );

		return rest_ensure_response(
			[
				'status'  => 'success',
				'message' => __( 'Checks ignored.', 'surerank' ),
			]
		);
	}

	/**
	 * Delete ignore checks.
	 *
	 * @param WP_REST_Request<array<string, mixed>> $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_ignore_checks( $request ) {
		$id            = $request->get_param( 'id' );
		$ignore_checks = $this->get_ignore_checks();

		if ( in_array( $id, $ignore_checks, true ) ) {
			$ignore_checks = array_diff( $ignore_checks, [ $id ] );
		}

		$seo_checks = Get::option( 'surerank_site_seo_checks', [] );
		foreach ( $seo_checks as $key => $check ) {
			if ( isset( $check[ $id ] ) ) {
				if ( isset( $check[ $id ]['ignore'] ) ) {
					unset( $check[ $id ]['ignore'] );
					$seo_checks[ $key ] = $check;
				}
			}
		}

		Update::option( 'surerank_site_seo_checks', $seo_checks );
		Update::option( 'surerank_ignored_site_checks_list', array_values( $ignore_checks ) );

		return rest_ensure_response(
			[
				'success' => true,
				'checks'  => $ignore_checks,
				'status'  => 'success',
				'message' => __( 'Checks unignored.', 'surerank' ),
			]
		);
	}

	/**
	 * Get ignored checks list.
	 *
	 * @param array<string, mixed> $post_checks List of post checks.
	 * @param int                  $post_id Post or term ID.
	 * @param string               $check_type Type of check, either 'post' or 'taxonomy'.
	 * @return array<string, mixed>
	 */
	public function get_updated_ignored_check_list( $post_checks, $post_id, $check_type = 'post' ) {
		$ignored_checks = $this->get_ignored_post_taxo_check( $post_id, $check_type );

		if ( ! empty( $ignored_checks ) && is_array( $ignored_checks ) ) {
			foreach ( $post_checks as $key => $check ) {
				if ( in_array( $key, $ignored_checks, true ) ) {
					$post_checks[ $key ]['ignore'] = true;
				}
			}
		}

		return $post_checks;
	}

	/**
	 * Get ignored checks.
	 *
	 * @param int    $post_id Post or term ID.
	 * @param string $check_type Type of check, either 'post' or 'taxonomy'.
	 * @return array<string, mixed>
	 */
	public function get_ignored_post_taxo_check( $post_id, $check_type = 'post' ) {
		$ignored_checks = null;
		if ( $check_type === 'taxonomy' ) {
			$ignored_checks = $this->get_ignore_taxonomy_checks( $post_id );
		} else {
			$ignored_checks = $this->get_ignore_post_checks( $post_id );
		}
		if ( empty( $ignored_checks ) || ! is_array( $ignored_checks ) ) {
			$ignored_checks = [];
		}
		return $ignored_checks;
	}

	/**
	 * Update ignored post or taxonomy checks.
	 *
	 * @param int           $post_id Post or term ID.
	 * @param string        $check_type Type of check, either 'post' or 'taxonomy'.
	 * @param array<string> $checks List of checks to ignore.
	 * @return void
	 */
	public function update_ignored_post_taxo_check( $post_id, $check_type = 'post', $checks = [] ) {
		if ( $check_type === 'taxonomy' ) {
			Update::term_meta( $post_id, 'surerank_ignored_post_checks', array_values( $checks ) );
		} else {
			Update::post_meta( $post_id, 'surerank_ignored_post_checks', array_values( $checks ) );
		}
	}

	/**
	 * Ignore post-specific checks.
	 *
	 * @param WP_REST_Request<array<string, mixed>> $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function ignore_post_taxo_check( $request ) {
		$id         = $request->get_param( 'id' );
		$post_id    = $request->get_param( 'post_id' );
		$check_type = $request->get_param( 'check_type' );

		$ignore_checks = $this->get_ignored_post_taxo_check( $post_id, $check_type );

		if ( ! in_array( $id, $ignore_checks, true ) ) {
			$ignore_checks[] = $id;
			$this->update_ignored_post_taxo_check( $post_id, $check_type, $ignore_checks );
		}

		return rest_ensure_response(
			[
				'success' => true,
				'status'  => 'success',
				'message' => __( 'Check ignored for post.', 'surerank' ),
				'checks'  => $ignore_checks,
			]
		);
	}

	/**
	 * Delete post-specific ignore checks.
	 *
	 * @param WP_REST_Request<array<string, mixed>> $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_ignore_post_taxo_check( $request ) {
		$id         = $request->get_param( 'id' );
		$post_id    = $request->get_param( 'post_id' );
		$check_type = $request->get_param( 'check_type' );

		$ignore_checks = $this->get_ignored_post_taxo_check( $post_id, $check_type );

		if ( in_array( $id, $ignore_checks, true ) ) {
			$ignore_checks = array_values( array_diff( $ignore_checks, [ $id ] ) );
			$this->update_ignored_post_taxo_check( $post_id, $check_type, $ignore_checks );
		}

		return rest_ensure_response(
			[
				'success' => true,
				'status'  => 'success',
				'message' => __( 'Check unignored for post.', 'surerank' ),
				'checks'  => $ignore_checks,
			]
		);
	}

	/**
	 * Get ignored checks.
	 *
	 * @param WP_REST_Request<array<string, mixed>> $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_ignore_post_taxo_check( $request ) {

		$post_id    = (int) $request->get_param( 'post_id' );
		$check_type = $request->get_param( 'check_type' );

		$ignore_checks = $this->get_ignored_post_taxo_check( $post_id, $check_type );

		return rest_ensure_response(
			[
				'success' => true,
				'status'  => 'success',
				'message' => __( 'Ignored checks retrieved.', 'surerank' ),
				'checks'  => $ignore_checks,
			]
		);
	}

	/**
	 * Get settings checks.
	 *
	 * @param WP_REST_Request<array<string, mixed>> $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_settings_checks( $request ) {
		$force = $request->get_param( 'force' );

		if ( $this->cache_exists( 'settings' ) && ! $force ) {
			return rest_ensure_response(
				$this->get_cached_response( 'settings' )
			);
		}

		return rest_ensure_response(
			$this->run_settings_checks()
		);
	}

	/**
	 * Get other SEO checks for a URL or homepage.
	 *
	 * @param WP_REST_Request<array<string, mixed>> $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_other_checks( $request ) {
		$force = $request->get_param( 'force' );

		if ( $this->cache_exists( 'other' ) && ! $force ) {
			return rest_ensure_response(
				$this->get_cached_response( 'other' )
			);
		}

		return rest_ensure_response(
			$this->run_other_checks()
		);
	}

	/**
	 * Get authentication status.
	 *
	 * @return array<string, mixed>
	 */
	public function get_auth_status() {
		$auth_status       = Controller::get_instance()->get_auth_status() && Settings::get( 'enable_google_console' );
		$working_label     = __( 'Google Search Console is currently connected to your site.', 'surerank' );
		$not_working_label = __( 'Google Search Console is not currently connected to your site.', 'surerank' );

		$helptext = [
			__( 'Search Console helps you understand how your site appears in Google search results. It shows which pages are indexed, how your site is performing, and whether Google is reporting any issues.', 'surerank' ),
			__( 'Without it connected, you miss important visibility into how Google sees your site.', 'surerank' ),

			sprintf(
				'<h6>🛠️ %s </h6>',
				__( 'Where to Update It', 'surerank' )
			),
			__( 'You can connect Google Search Console directly from the SureRank Dashboard.', 'surerank' ),
			[
				'list' => [
					sprintf(
						// translators: %s is the Search Console URL.
						__( 'Go to SureRank ⇾ <a href="%s">Search Console</a>', 'surerank' ),
						$this->get_search_console_url()
					),
					__( 'Sign in with your Google account', 'surerank' ),
					__( 'Select your site and complete the connection', 'surerank' ),
				],
			],

			sprintf(
				'<h6>✏️ %s </h6>',
				__( 'Update Here', 'surerank' )
			),

			sprintf(
				"<img class='w-full h-full' src='%s' alt='%s' />",
				esc_attr( 'https://surerank.com/wp-content/uploads/2026/02/google-search-console-is-not-connected.webp' ),
				esc_attr( 'Search Console Connection' )
			),

			__( 'Once connected, SureRank will start using Search Console data to show search performance and indexing insights.', 'surerank' ),
		];

		if ( ! Nudge_Utils::get_instance()->is_pro_active() ) {
			$helptext[] = sprintf(
				'<h6>💬 %s </h6>',
				__( 'Need More?', 'surerank' )
			);
			$helptext[] = __( 'SureRank Pro unlocks advanced insights and recommendations powered by Google Search Console data.', 'surerank' );
		}

		$heading = $auth_status ? __( 'Google Search Console is connected.', 'surerank' ) : __( 'Google Search Console is not connected.', 'surerank' );

		return [
			'exists'       => true,
			'not_locked'   => true,
			'button_label' => __( 'Connect Now', 'surerank' ),
			'button_url'   => $this->get_search_console_url(),
			'status'       => $auth_status ? 'success' : 'suggestion',
			'description'  => $helptext,
			'message'      => $auth_status ? $working_label : $not_working_label,
			'heading'      => $heading,
		];
	}

	/**
	 * Get list of installed SEO plugins with detection info.
	 *
	 * @return array{active_plugins: array<int, string>, detected_plugins: array<int, array<string, string>>}
	 * @since 1.4.0
	 */
	public function get_installed_seo_plugins_data(): array {
		$seo_plugins = [
			'seo-by-rank-math/rank-math.php'              => [
				'name'     => 'Rank Math',
				'pro_slug' => 'seo-by-rank-math-pro/rank-math-pro.php',
			],
			'wordpress-seo/wp-seo.php'                    => [
				'name'     => 'Yoast SEO',
				'pro_slug' => 'wordpress-seo-premium/wp-seo-premium.php',
			],
			'autodescription/autodescription.php'         => [
				'name'     => 'The SEO Framework',
				'pro_slug' => '',
			],
			'all-in-one-seo-pack/all_in_one_seo_pack.php' => [
				'name'     => 'AIOSEO',
				'pro_slug' => 'all-in-one-seo-pack-pro/all_in_one_seo_pack.php',
			],
			'wp-seopress/seopress.php'                    => [
				'name'     => 'SEOPress',
				'pro_slug' => 'wp-seopress-pro/wp-seopress-pro.php',
			],
			'slim-seo/slim-seo.php'                       => [
				'name'     => 'Slim SEO',
				'pro_slug' => 'slim-seo-pro/slim-seo-pro.php',
			],
			'squirrly-seo/squirrly.php'                   => [
				'name'     => 'Squirrly SEO',
				'pro_slug' => '',
			],
		];

		$active_plugins   = apply_filters( 'active_plugins', get_option( 'active_plugins', [] ) );
		$detected_plugins = [];

		foreach ( $seo_plugins as $file => $data ) {
			if ( in_array( $file, $active_plugins, true ) ) {
				$detected_plugins[] = [
					'name'     => $data['name'],
					'slug'     => $file,
					'pro_slug' => $data['pro_slug'],
				];
			}
		}

		return [
			'active_plugins'   => $active_plugins,
			'detected_plugins' => $detected_plugins,
		];
	}

	/**
	 * Analyze installed SEO plugins.
	 *
	 * @return array<string, mixed>
	 */
	public function get_installed_seo_plugins(): array {

		$plugin_data      = $this->get_installed_seo_plugins_data();
		$detected_plugins = array_map(
			static function( $plugin ) {
				return [ 'name' => $plugin['name'] ];
			},
			$plugin_data['detected_plugins']
		);

		$active_count = count( $detected_plugins );
		$heading      = $active_count > 0 ? __( 'Other SEO Plugin Detected', 'surerank' ) : __( 'No other SEO plugin detected', 'surerank' );
		$title        = __( 'No other SEO plugin detected on your site.', 'surerank' );

		if ( $active_count > 0 ) {
			if ( $active_count > 1 ) {
				$title = __( 'More than one SEO plugin is currently active on your site.', 'surerank' );
			} else {
				/* translators: %s is the list of active plugins */
				$title = sprintf( __( 'Another SEO plugin, %s, is currently active on your site.', 'surerank' ), implode( ', ', array_column( $detected_plugins, 'name' ) ) );
			}
		}

		$description = [
			__( 'SEO plugins manage things like page titles, descriptions, schema, and indexing settings. These signals help search engines understand how your site appears in search results.', 'surerank' ),
			__( 'When multiple SEO plugins are active, they can create duplicate or conflicting signals. This makes it harder for search engines to understand which information to trust.', 'surerank' ),
			__( 'Using a single SEO plugin helps keep everything consistent and easier to manage.', 'surerank' ),

			sprintf(
				'<h6>🛠️ %s </h6>',
				__( 'Where to Update It', 'surerank' )
			),
			__( 'You can manage this from your WordPress plugins list.', 'surerank' ),
			[
				'list' => [
					sprintf(
						// translators: %s is the Plugins menu URL.
						__( 'Go to <a href="%s">Plugins ⇾ Installed Plugins</a>', 'surerank' ),
						admin_url( 'plugins.php' )
					),
					__( 'Identify any other active SEO plugins', 'surerank' ),
					__( 'Deactivate the ones you are not using', 'surerank' ),
				],
			],

			sprintf(
				'<h6>✏️ %s </h6>',
				__( 'Deactivate Here', 'surerank' )
			),

			sprintf(
				"<img class='w-full h-full' src='%s' alt='%s' />",
				esc_attr( 'https://surerank.com/wp-content/uploads/2026/02/other-seo-plugin-detected.webp' ),
				esc_attr( 'Other SEO Plugin Detected' )
			),
		];

		if ( ! Nudge_Utils::get_instance()->is_pro_active() ) {
			$description[] = sprintf(
				'<h6>💬 %s </h6>',
				__( 'Need Help?', 'surerank' )
			);
			$description[] = __( 'SureRank Premium users get access to our support team, available 24×7, to help review plugin conflicts and guide you through cleanup.', 'surerank' );
		}

		return [
			'exists'      => true,
			'status'      => $active_count > 0 ? 'error' : 'success',
			'description' => $description,
			'message'     => $title,
			'heading'     => $heading,
		];
	}

	/**
	 * Analyze site tagline.
	 *
	 * @return array<string, mixed>
	 */
	public function get_site_tag_line(): array {
		$tagline = get_bloginfo( 'description' );
		$is_set  = ! empty( $tagline );

		$heading = __( 'Site Tagline', 'surerank' );
		$title   = $is_set ? __( 'Your site does currently have a tagline set.', 'surerank' ) : __( 'Your site does not currently have a tagline set.', 'surerank' );

		$description = [
			__( 'A site tagline is a short line that describes what your website is about.', 'surerank' ),
			__( 'It often appears alongside your site title and helps visitors quickly understand your purpose.', 'surerank' ),
			__( 'A clear tagline sets context, supports your brand, and makes your site feel more intentional.', 'surerank' ),

			sprintf(
				'<h6>✅ %s </h6>',
				__( 'Best Practice', 'surerank' )
			),
			[
				'list' => [
					__( 'A good tagline is simple and easy to understand.', 'surerank' ),
					__( 'It should describe what you do or who your site is for.', 'surerank' ),
					__( 'Aim for a single clear sentence that feels natural and human.', 'surerank' ),
				],
			],
			__( 'Avoid buzzwords or vague phrases that do not say much about your site.', 'surerank' ),

			sprintf(
				'<h6>🛠️ %s </h6>',
				__( 'Where to Update It', 'surerank' )
			),
			__( 'You can update your site tagline from the WordPress settings.', 'surerank' ),
			[
				'list' => [
					__( 'Go to Settings ⇾ General', 'surerank' ),
					__( 'Update the Tagline field', 'surerank' ),
					__( 'Save your changes', 'surerank' ),
				],
			],

			sprintf(
				'<h6>✏️ %s </h6>',
				__( 'Update Here', 'surerank' )
			),

			sprintf(
				"<img class='w-full h-full' src='%s' alt='%s' />",
				esc_attr( 'https://surerank.com/wp-content/uploads/2026/02/site-tagline.webp' ),
				esc_attr( 'Site Tagline Settings' )
			),
		];

		if ( ! Nudge_Utils::get_instance()->is_pro_active() ) {
			$description[] = sprintf(
				'<h6>💬 %s </h6>',
				__( 'Need Help?', 'surerank' )
			);
			$description[] = __( 'SureRank Pro helps fix SEO issues across your website using AI, without manual effort.', 'surerank' );
		}

		return [
			'exists'      => true,
			'status'      => $is_set ? 'success' : 'warning',
			'description' => $description,
			'message'     => $title,
			'heading'     => $heading,
		];
	}

	/**
	 * Analyze robots.txt.
	 *
	 * @return array<string, mixed>
	 */
	public function robots_txt() {
		$robots_url = home_url( '/robots.txt' );

		$working_heading = __( 'Robots.txt is accessible.', 'surerank' );
		$working_label   = __( 'Your site has an accessible robots.txt file.', 'surerank' );

		$not_working_heading = __( 'Robots.txt is missing or inaccessible.', 'surerank' );
		$not_working_label   = __( 'Your site does not currently have an accessible robots.txt file.', 'surerank' );

		$helptext = [
			__( 'The robots.txt file tells search engines which parts of your site they are allowed to crawl and index.', 'surerank' ),
			__( 'When this file is missing or inaccessible, search engines may have trouble understanding how to properly crawl your site.', 'surerank' ),
			__( 'Having a valid robots.txt file helps avoid crawling issues and ensures search engines focus on the right pages.', 'surerank' ),

			sprintf(
				'<h6>🛠️ %s </h6>',
				__( 'Where to Update It', 'surerank' )
			),
			__( 'You can create or edit your robots.txt file directly from SureRank.', 'surerank' ),
			[
				'list' => [
					__( 'Go to SureRank ⇾ Advanced ⇾ Robots.txt Editor', 'surerank' ),
					__( 'Review or add the required rules', 'surerank' ),
					__( 'Save your changes', 'surerank' ),
				],
			],

			sprintf(
				'<h6>✏️ %s </h6>',
				__( 'Update Here', 'surerank' )
			),
			sprintf(
				"<img class='w-full h-full' src='%s' alt='%s' />",
				esc_attr( 'https://surerank.com/wp-content/uploads/2026/02/robotstxt-is-missing-or-inaccessible.webp' ),
				esc_attr( 'Robots.txt example' )
			),
		];

		if ( ! Nudge_Utils::get_instance()->is_pro_active() ) {
			$helptext[] = sprintf( '<h6>💬 %s </h6>', __( 'Need Help?', 'surerank' ) );
			$helptext[] = __( 'SureRank Pro helps fix SEO issues across your website using AI, without manual effort.', 'surerank' );
		}

		$response = Scraper::get_instance()->fetch_status( $robots_url );
		if ( is_wp_error( $response ) || $response !== 200 ) {
			return [
				'exists'      => false,
				'status'      => 'warning',
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
	 * Analyze site indexed.
	 *
	 * @return array<string, mixed>
	 */
	public function index_status() {
		$index_status = get_option( 'blog_public' );
		$no_index     = $this->settings['no_index'] ?? [];

		$working_heading = __( 'Search engine visibility is enabled.', 'surerank' );
		$working_label   = __( 'Search engine visibility is currently enabled in your WordPress settings.', 'surerank' );

		$not_working_heading = __( 'Search engine visibility is disabled.', 'surerank' );
		$not_working_label   = __( 'Search engine visibility is currently disabled in your WordPress settings.', 'surerank' );

		$helptext = [
			__( 'WordPress includes a setting that tells search engines whether they are allowed to index your site. Indexing means your pages can appear in search results.', 'surerank' ),
			__( 'This setting is commonly used while a site is being built or kept private. When enabled, it asks search engines not to index new pages.', 'surerank' ),
			__( 'If this setting remains enabled by mistake, your site may not appear in search results even if everything else is set up correctly. It can quietly limit visibility without showing obvious errors.', 'surerank' ),

			sprintf(
				'<h6>🛠️ %s </h6>',
				__( 'Where to Update It', 'surerank' )
			),
			__( 'You can change this setting from your WordPress dashboard.', 'surerank' ),

			[
				'list' => [
					sprintf(
						/* translators: %s is the URL of the WordPress Reading settings page */
						__( 'Go to <a href="%s">Settings ⇾ Reading</a>', 'surerank' ),
						$this->get_wordpress_settings_url( 'reading' )
					),
					__( 'Find the option labeled “Search engine visibility”', 'surerank' ),
					__( 'Make sure the checkbox is not selected', 'surerank' ),
					__( 'Save your changes', 'surerank' ),
				],
			],

			sprintf(
				/* translators: %s is the URL of the WordPress Reading settings page */
				'<h6>✏️ %s </h6>',
				__( 'Update Here', 'surerank' )
			),

			sprintf(
				"<img class='w-full h-full' src='%s' alt='%s' />",
				esc_attr( 'https://surerank.com/wp-content/uploads/2026/02/search-engine-visibility-is-disabled.webp' ),
				esc_attr( 'Search engine visibility setting' )
			),
		];

		if ( ! Nudge_Utils::get_instance()->is_pro_active() ) {
			$helptext[] = sprintf(
				'<h6>💬 %s </h6>',
				__( 'Need Help?', 'surerank' )
			);
			$helptext[] = __( 'SureRank Pro users get access to our support team, available 24×7, to help detect visibility and indexing issues before they affect your site.', 'surerank' );
		}

		$sensitive_post_types = [ 'post', 'page', 'product', 'product_variation', 'product_category', 'product_tag' ];
		$noindex_types        = array_intersect( $no_index, $sensitive_post_types );

		if ( ! empty( $noindex_types ) ) {
			return [
				'exists'      => false,
				'status'      => 'error',
				'description' => $helptext,
				'message'     => $not_working_label,
				'heading'     => $not_working_heading,
			];
		}

		if ( ! $index_status ) {
			return [
				'exists'      => false,
				'status'      => 'error',
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
	 * Analyze sitemaps.
	 *
	 * @return array<string, mixed>
	 */
	public function sitemaps(): array {
		$working_heading = __( 'XML sitemap is accessible.', 'surerank' );
		$working_label   = __( 'The XML sitemap for this site is accessible to search engines.', 'surerank' );

		$not_working_heading = __( 'XML sitemap is missing or inaccessible.', 'surerank' );
		$not_working_label   = __( 'The XML sitemap for this site is missing or cannot be accessed.', 'surerank' );

		$helptext = [
			__( 'An XML sitemap helps search engines discover and understand the pages on your site.', 'surerank' ),
			__( 'When the sitemap is missing or cannot be accessed, search engines may take longer to find new or updated pages.', 'surerank' ),
			__( 'Having a sitemap makes it easier for search engines to crawl your site efficiently.', 'surerank' ),
			__( 'It also helps ensure important pages are not missed during indexing.', 'surerank' ),

			sprintf(
				'<h6>🛠️ %s </h6>',
				__( 'Where to Update It', 'surerank' )
			),
			__( 'You can enable and manage your XML sitemap directly from SureRank.', 'surerank' ),
			[
				'list' => [
					__( 'Go to SureRank ⇾ General ⇾ Sitemaps', 'surerank' ),
					__( 'Enable the XML Sitemap toggle', 'surerank' ),
					__( 'Click on Regenerate Button', 'surerank' ),
					__( 'Save your changes', 'surerank' ),
				],
			],

			sprintf(
				'<h6>✏️ %s </h6>',
				__( 'Update Here', 'surerank' )
			),

			sprintf(
				"<img class='w-full h-full' src='%s' alt='%s' />",
				esc_attr( 'https://surerank.com/wp-content/uploads/2026/04/xml-sitemap-is-missing-or-inaccessible-visual.webp' ),
				esc_attr( 'XML Sitemap Settings' )
			),
		];

		if ( ! Nudge_Utils::get_instance()->is_pro_active() ) {
			$helptext[] = sprintf(
				'<h6>💬 %s </h6>',
				__( 'Need More?', 'surerank' )
			);
			$helptext[] = __( 'Upgrade to SureRank Pro to unlock advanced sitemap types like Video, News, HTML, and Author sitemaps for better search visibility.', 'surerank' );
		}

		$sitemap_url = home_url( '/sitemap_index.xml' );
		$sitemap     = Scraper::get_instance()->fetch( $sitemap_url );

		if ( is_wp_error( $sitemap ) || empty( $sitemap ) ) {
			return [
				'exists'      => false,
				'status'      => 'warning',
				'description' => $helptext,
				'message'     => $not_working_label,
				'heading'     => $not_working_heading,
			];
		}

		if ( ! $this->is_valid_xml( $sitemap ) ) {
			return [
				'exists'      => false,
				'status'      => 'warning',
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
	 * Get surerank settings url.
	 *
	 * @param string $page Page slug.
	 * @param string $parent Parent slug.
	 * @return string
	 */
	public function get_surerank_settings_url( string $page = '', string $parent = '' ) {

		if ( ! empty( $parent ) ) {

			return admin_url( 'admin.php?page=surerank' . ( $page ? "#/{$parent}/{$page}" : '' ) );

		}
		return admin_url( 'admin.php?page=surerank' . ( $page ? "#/{$page}" : '' ) );
	}

	/**
	 * Get broken links check.
	 *
	 * @param WP_REST_Request<array<string, mixed>> $request Request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_broken_links_status( $request ) {
		$url     = $request->get_param( 'url' ) ?? '';
		$post_id = $request->get_param( 'post_id' ) ?? 0;
		$urls    = $request->get_param( 'urls' ) ?? [];

		$post = get_post( $post_id );
		if ( ! $post ) {
			return $this->create_broken_link_error_response( __( 'Post not found', 'surerank' ) );
		}

		$response = $this->fetch_url_status( $url );

		if ( is_wp_error( $response ) ) {
			return $this->handle_broken_link_error( $url, $post_id, $urls, $response );
		}

		$status_code = (int) wp_remote_retrieve_response_code( $response );
		if ( $status_code === 404 || $status_code === 410 ) {
			return $this->handle_broken_link_status_error( $url, $post_id, $urls, $status_code, $response );
		}
		$this->remove_broken_links( $url, $post_id, $urls );
		return rest_ensure_response(
			[
				'success' => true,
				'message' => __( 'Link is not broken', 'surerank' ),
			]
		);
	}

	/**
	 * Remove broken links.
	 *
	 * @param string        $url URL.
	 * @param int           $post_id Post ID.
	 * @param array<string> $urls URLs.
	 * @return void
	 */
	public function remove_broken_links( $url, $post_id, $urls ) {
		$seo_checks   = Get::post_meta( $post_id, SURERANK_SEO_CHECKS, true );
		$broken_links = $seo_checks['broken_links'] ?? [];

		$existing_broken_links = Utils::existing_broken_links( $broken_links, $urls );

		foreach ( $existing_broken_links as $key => $existing_link ) {
			if ( is_array( $existing_link ) && isset( $existing_link['url'] ) && $existing_link['url'] === $url ) {
				unset( $existing_broken_links[ $key ] );
			}
		}

		$seo_checks['broken_links'] = $existing_broken_links;
		Update::post_meta( $post_id, SURERANK_SEO_CHECKS, $seo_checks );
	}

	/**
	 * Run checks.
	 *
	 * @param int $post_id Post ID.
	 * @return array<string, mixed>|WP_Error
	 */
	public function run_checks( $post_id ) {
		return Post::get_instance()->run_checks( $post_id );
	}

	/**
	 * Run taxonomy checks.
	 *
	 * @param int $term_id Term ID.
	 * @return array<string, mixed>|WP_Error
	 */
	public function run_taxonomy_checks( $term_id ) {
		return Term::get_instance()->run_checks( $term_id );
	}

	/**
	 * Run general checks.
	 *
	 * @param string $url URL to run checks on.
	 * @return array<string, mixed>|WP_Error
	 */
	public function run_general_checks( string $url ) {
		$analyzer = SeoAnalyzer::get_instance( $url );
		$xpath    = $analyzer->get_xpath();

		if ( ! $xpath instanceof DOMXPath ) {
			return $this->create_analysis_error( $xpath );
		}

		$response = $this->execute_general_checks( $analyzer, $xpath );
		$this->update_site_seo_checks( $response, 'general' );

		return $response;
	}

	/**
	 * Run settings checks.
	 *
	 * @return array<string, mixed>
	 */
	public function run_settings_checks() {
		$ignore_checks = $this->get_ignore_checks();
		$response      = [
			'sitemaps'     => fn() => $this->sitemaps(),
			'index_status' => fn() => $this->index_status(),
			'robots_txt'   => fn() => $this->robots_txt(),
		];

		foreach ( $response as $key => $callback ) {
			$response[ $key ] = array_merge( (array) $callback(), [ 'ignore' => in_array( $key, $ignore_checks, true ) ] );
		}

		$this->update_site_seo_checks( $response, 'settings' );

		return $response;
	}

	/**
	 * Run other checks.
	 *
	 * @return array<string, mixed>
	 */
	public function run_other_checks() {
		$response = [
			'other_seo_plugins' => fn() => $this->get_installed_seo_plugins(),
			'site_tag_line'     => fn() => $this->get_site_tag_line(),
			'auth_status'       => fn() => $this->get_auth_status(),
		];

		foreach ( $response as $key => $callback ) {
			$response[ $key ] = array_merge( (array) $callback(), [ 'ignore' => in_array( $key, $this->get_ignore_checks(), true ) ] );
		}

		$this->update_site_seo_checks( $response, 'other' );

		return $response;
	}

	/**
	 * Sanitize ids.
	 *
	 * @param array<int|string>                     $params IDs.
	 * @param WP_REST_Request<array<string, mixed>> $request Request object.
	 * @param string                                $key Key.
	 * @return array<int>
	 */
	public static function sanitize_ids( $params, $request, $key ) {
		return array_map( 'intval', $params );
	}

	/**
	 * Consolidate keyword checks if all are suggestions (no focus keyword set).
	 *
	 * @param array<string, mixed> $checks List of checks.
	 * @return array<string, mixed>
	 */
	private function consolidate_keyword_checks( $checks ) {
		$keyword_check_keys = [
			'keyword_in_title',
			'keyword_in_description',
			'keyword_in_url',
			'keyword_in_content',
		];

		$all_exist = true;
		foreach ( $keyword_check_keys as $key ) {
			if ( ! isset( $checks[ $key ] ) ) {
				$all_exist = false;
				break;
			}
		}

		if ( ! $all_exist ) {
			return $checks;
		}

		$all_suggestions = true;
		foreach ( $keyword_check_keys as $key ) {
			if ( ! isset( $checks[ $key ]['status'] ) || $checks[ $key ]['status'] !== 'suggestion' ) {
				$all_suggestions = false;
				break;
			}
		}

		if ( ! $all_suggestions ) {
			return $checks;
		}

		foreach ( $keyword_check_keys as $key ) {
			unset( $checks[ $key ] );
		}

		// Add consolidated check.
		$checks['keyword_checks'] = [
			'status'  => 'suggestion',
			'message' => __( 'No focus keyword set. Add one to analyze title, description, URL, and content.', 'surerank' ),
			'type'    => 'keyword',
		];

		return $checks;
	}

	/**
	 * Get term checks data (cached or fresh).
	 *
	 * @param int $term_id Term ID.
	 * @return array<string, mixed>|WP_Error
	 */
	private function get_term_checks_data( $term_id ) {
		if ( $this->is_taxonomy_cache_valid( $term_id ) ) {
			return $this->get_cached_taxonomy_checks( $term_id );
		}

		$term_checks = $this->run_taxonomy_checks( $term_id );
		if ( ! is_wp_error( $term_checks ) ) {
			$term_checks = $this->get_updated_ignored_check_list( $term_checks, $term_id, 'taxonomy' );
		}

		return $term_checks;
	}

	/**
	 * Get post checks data (cached or fresh).
	 *
	 * @param int $post_id       Post ID.
	 * @return array<string, mixed>|WP_Error
	 */
	private function get_post_checks_data( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return new WP_Error( 'invalid_post', __( 'Invalid Post ID.', 'surerank' ) );
		}

		if ( $this->is_post_cache_valid( $post, $post_id ) ) {
			return $this->get_cached_post_checks( $post_id );
		}

		$post_checks = $this->run_checks( $post_id );
		if ( ! is_wp_error( $post_checks ) ) {
			$post_checks = $this->get_updated_ignored_check_list( $post_checks, $post_id, 'post' );
		}

		return $post_checks;
	}

	/**
	 * Register all analyzer routes
	 *
	 * @param string $namespace The API namespace.
	 * @return void
	 */
	private function register_all_analyzer_routes( $namespace ) {
		$this->register_general_checks_route( $namespace );
		$this->register_settings_checks_route( $namespace );
		$this->register_other_checks_route( $namespace );
		$this->register_broken_links_route( $namespace );
		$this->register_page_seo_checks_route( $namespace );
		$this->register_taxonomy_seo_checks_route( $namespace );
		$this->register_ignore_checks_routes( $namespace );
		$this->register_ignore_post_checks_routes( $namespace );
	}

	/**
	 * Register general checks route
	 *
	 * @param string $namespace The API namespace.
	 * @return void
	 */
	private function register_general_checks_route( $namespace ) {
		register_rest_route(
			$namespace,
			$this->general_checks,
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_general_checks' ],
				'permission_callback' => [ $this, 'validate_permission' ],
				'args'                => $this->get_general_checks_args(),
				'role_capability'     => 'global_setting',
			]
		);
	}

	/**
	 * Register settings checks route
	 *
	 * @param string $namespace The API namespace.
	 * @return void
	 */
	private function register_settings_checks_route( $namespace ) {
		register_rest_route(
			$namespace,
			$this->settings_checks,
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_settings_checks' ],
				'permission_callback' => [ $this, 'validate_permission' ],
				'args'                => $this->get_force_args(),
				'role_capability'     => 'global_setting',
			]
		);
	}

	/**
	 * Register other checks route
	 *
	 * @param string $namespace The API namespace.
	 * @return void
	 */
	private function register_other_checks_route( $namespace ) {
		register_rest_route(
			$namespace,
			$this->other_checks,
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_other_checks' ],
				'permission_callback' => [ $this, 'validate_permission' ],
				'args'                => $this->get_force_args(),
				'role_capability'     => 'global_setting',
			]
		);
	}

	/**
	 * Register broken links route
	 *
	 * @param string $namespace The API namespace.
	 * @return void
	 */
	private function register_broken_links_route( $namespace ) {
		register_rest_route(
			$namespace,
			$this->broken_links_check,
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'get_broken_links_status' ],
				'permission_callback' => [ $this, 'validate_permission' ],
				'args'                => $this->get_broken_links_args(),
				'role_capability'     => 'content_setting',
			]
		);
	}

	/**
	 * Register page SEO checks route
	 *
	 * @param string $namespace The API namespace.
	 * @return void
	 */
	private function register_page_seo_checks_route( $namespace ) {
		register_rest_route(
			$namespace,
			$this->page_seo_checks,
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_page_seo_checks' ],
				'permission_callback' => [ $this, 'validate_permission' ],
				'args'                => $this->get_post_id_args(),
				'role_capability'     => 'content_setting',
			]
		);
	}

	/**
	 * Register taxonomy SEO checks route
	 *
	 * @param string $namespace The API namespace.
	 * @return void
	 */
	private function register_taxonomy_seo_checks_route( $namespace ) {
		register_rest_route(
			$namespace,
			$this->taxonomy_seo_checks,
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_taxonomy_seo_checks' ],
				'permission_callback' => [ $this, 'validate_permission' ],
				'args'                => $this->get_term_id_args(),
				'role_capability'     => 'content_setting',
			]
		);
	}

	/**
	 * Register ignore checks routes
	 *
	 * @param string $namespace The API namespace.
	 * @return void
	 */
	private function register_ignore_checks_routes( $namespace ) {
		$this->register_create_ignore_check_route( $namespace );
		$this->register_delete_ignore_check_route( $namespace );
	}

	/**
	 * Register ignore post checks routes
	 *
	 * @param string $namespace The API namespace.
	 * @return void
	 */
	private function register_ignore_post_checks_routes( $namespace ) {
		$this->register_create_ignore_post_check_route( $namespace );
		$this->register_delete_ignore_post_check_route( $namespace );
		$this->register_get_ignore_post_check_route( $namespace );
	}

	/**
	 * Register create ignore check route
	 *
	 * @param string $namespace The API namespace.
	 * @return void
	 */
	private function register_create_ignore_check_route( $namespace ) {
		register_rest_route(
			$namespace,
			$this->ignore_checks,
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'ignore_checks' ],
				'permission_callback' => [ $this, 'validate_permission' ],
				'args'                => $this->get_id_args(),
				'role_capability'     => 'global_setting',
			]
		);
	}

	/**
	 * Register delete ignore check route
	 *
	 * @param string $namespace The API namespace.
	 * @return void
	 */
	private function register_delete_ignore_check_route( $namespace ) {
		register_rest_route(
			$namespace,
			$this->ignore_checks,
			[
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => [ $this, 'delete_ignore_checks' ],
				'permission_callback' => [ $this, 'validate_permission' ],
				'args'                => $this->get_sanitized_id_args(),
				'role_capability'     => 'global_setting',
			]
		);
	}

	/**
	 * Register create ignore post check route
	 *
	 * @param string $namespace The API namespace.
	 * @return void
	 */
	private function register_create_ignore_post_check_route( $namespace ) {
		register_rest_route(
			$namespace,
			$this->ignore_post_checks,
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'ignore_post_taxo_check' ],
				'permission_callback' => [ $this, 'validate_permission' ],
				'args'                => $this->get_ignore_post_check_args(),
				'role_capability'     => 'content_setting',
			]
		);
	}

	/**
	 * Register delete ignore post check route
	 *
	 * @param string $namespace The API namespace.
	 * @return void
	 */
	private function register_delete_ignore_post_check_route( $namespace ) {
		register_rest_route(
			$namespace,
			$this->ignore_post_checks,
			[
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => [ $this, 'delete_ignore_post_taxo_check' ],
				'permission_callback' => [ $this, 'validate_permission' ],
				'args'                => $this->get_ignore_post_check_args(),
				'role_capability'     => 'content_setting',
			]
		);
	}

	/**
	 * Register get ignore post check route
	 *
	 * @param string $namespace The API namespace.
	 * @return void
	 */
	private function register_get_ignore_post_check_route( $namespace ) {
		register_rest_route(
			$namespace,
			$this->ignore_post_checks,
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_ignore_post_taxo_check' ],
				'permission_callback' => [ $this, 'validate_permission' ],
				'args'                => $this->get_post_id_with_check_type_args(),
				'role_capability'     => 'content_setting',
			]
		);
	}

	/**
	 * Create analysis error response.
	 *
	 * @param mixed $xpath XPath error data.
	 * @return WP_Error
	 */
	private function create_analysis_error( $xpath ): WP_Error {
		return new WP_Error(
			'analysis_failed',
			is_array( $xpath ) && isset( $xpath['message'] ) ? $xpath['message'] : 'Analysis failed',
			[
				'status'  => 500,
				'details' => is_array( $xpath ) && isset( $xpath['details'] ) ? $xpath['details'] : [],
			]
		);
	}

	/**
	 * Execute general checks.
	 *
	 * @param SeoAnalyzer $analyzer Analyzer instance.
	 * @param DOMXPath    $xpath    XPath instance.
	 * @return array<string, mixed>
	 */
	private function execute_general_checks( SeoAnalyzer $analyzer, DOMXPath $xpath ): array {
		$checks   = $this->get_general_check_callbacks( $analyzer, $xpath );
		$response = [];

		foreach ( $checks as $key => $callback ) {
			$response[ $key ] = $this->execute_single_check( $key, $callback );
		}

		return $response;
	}

	/**
	 * Get general check callbacks.
	 *
	 * @param SeoAnalyzer $analyzer Analyzer instance.
	 * @param DOMXPath    $xpath    XPath instance.
	 * @return array<string, callable>
	 */
	private function get_general_check_callbacks( SeoAnalyzer $analyzer, DOMXPath $xpath ): array {
		return [
			'title'             => static fn() => $analyzer->analyze_title( $xpath ),
			'meta_description'  => static fn() => $analyzer->analyze_meta_description( $xpath ),
			'headings_h1'       => static fn() => $analyzer->analyze_heading_h1( $xpath ),
			'headings_h2'       => static fn() => $analyzer->analyze_heading_h2( $xpath ),
			'images'            => static fn() => $analyzer->analyze_images( $xpath ),
			'links'             => static fn() => $analyzer->analyze_links( $xpath ),
			'canonical'         => static fn() => $analyzer->analyze_canonical( $xpath ),
			'indexing'          => static fn() => $analyzer->analyze_indexing( $xpath ),
			'reachability'      => static fn() => $analyzer->analyze_reachability(),
			'secure_connection' => static fn() => $analyzer->analyze_secure_connection(),
			'www_canonical'     => static fn() => $analyzer->analyze_www_canonicalization(),
			'open_graph_tags'   => static fn() => $analyzer->open_graph_tags( $xpath ),
			'schema_meta_data'  => static fn() => $analyzer->schema_meta_data( $xpath ),
		];
	}

	/**
	 * Execute a single check.
	 *
	 * @param string   $key      Check key.
	 * @param callable $callback Check callback.
	 * @return array<string, mixed>
	 */
	private function execute_single_check( string $key, callable $callback ): array {
		$result           = (array) $callback();
		$result['ignore'] = $this->is_check_ignored( $key );
		return $result;
	}

	/**
	 * Check if a check should be ignored.
	 *
	 * @param string $key Check key.
	 * @return bool
	 */
	private function is_check_ignored( string $key ): bool {
		return in_array( $key, $this->get_ignore_checks(), true );
	}

	/**
	 * Check if the sitemap is valid XML.
	 *
	 * @param string $sitemap Sitemap content.
	 * @return bool
	 */
	private function is_valid_xml( string $sitemap ): bool {
		/**
		 * Here we are checking if the sitemap is valid XML.
		 * First we supressing the errors.
		 * Then we load the sitemap as simplexml.
		 * Then we clear the errors.
		 * Then we restore the errors suppression.
		 */

		libxml_use_internal_errors( true );
		$xml        = simplexml_load_string( $sitemap );
		$xml_errors = libxml_get_errors();
		libxml_clear_errors();
		libxml_use_internal_errors( false );

		return $xml !== false && empty( $xml_errors );
	}

	/**
	 * Get WordPress settings page url.
	 *
	 * @param string $page Page slug.
	 * @return string
	 */
	private function get_wordpress_settings_url( string $page = 'general' ): string {
		return admin_url( 'options-' . $page . '.php' );
	}

	/**
	 * Get SureRank dashboard url.
	 *
	 * @return string
	 */
	private function get_search_console_url() {
		// Check if Google Search Console feature is enabled.
		if ( ! Settings::get( 'enable_google_console' ) ) {
			return admin_url( 'admin.php?page=surerank#/tools/manage-features' );
		}

		return admin_url( 'admin.php?page=surerank#/search-console' );
	}

	/**
	 * Get ignore checks.
	 *
	 * @return array<string>
	 */
	private function get_ignore_checks() {
		return Get::option( 'surerank_ignored_site_checks_list', [] );
	}

	/**
	 * Save broken links.
	 *
	 * @param string        $url URL.
	 * @param int           $post_id Post ID.
	 * @param array<string> $urls URLs.
	 * @param int|null      $status_code HTTP status code.
	 * @param int|string    $error_message Error message.
	 * @return bool
	 */
	private function save_broken_links( string $url, int $post_id, array $urls, $status_code = null, $error_message = null ) {
		$seo_checks   = Get::post_meta( $post_id, SURERANK_SEO_CHECKS, true );
		$broken_links = $seo_checks['broken_links'] ?? [];

		$existing_broken_links = Utils::existing_broken_links( $broken_links, $urls );

		$broken_link_details = [
			'url'     => $url,
			'status'  => $status_code,
			'details' => $error_message ? $error_message : __( 'The link is broken.', 'surerank' ),
		];

		$url_found = false;
		foreach ( $existing_broken_links as $key => $existing_link ) {
			if ( is_array( $existing_link ) && isset( $existing_link['url'] ) && $existing_link['url'] === $url ) {
				$existing_broken_links[ $key ] = $broken_link_details;
				$url_found                     = true;
				break;
			}
		}

		if ( ! $url_found ) {
			$existing_broken_links[] = $broken_link_details;
		}

		$final_array                 = [];
		$final_array['broken_links'] = [
			'status'      => 'error',
			'type'        => 'page',
			'description' => [
				__( 'These broken links were found on the page: ', 'surerank' ),
				[
					'list' => $existing_broken_links,
				],
			],
			'message'     => __( 'One or more broken links found on the page.', 'surerank' ),
		];

		return Update::post_seo_checks( $post_id, $final_array );
	}

	/**
	 * Get post-specific ignore checks.
	 *
	 * @param int $post_id Post ID.
	 * @return array<string>
	 */
	private function get_ignore_post_checks( $post_id ) {
		return Get::post_meta( $post_id, 'surerank_ignored_post_checks', true );
	}

	/**
	 * Get taxonomy-specific ignore checks.
	 *
	 * @param int $term_id Term ID.
	 * @return array<string>
	 */
	private function get_ignore_taxonomy_checks( $term_id ) {
		return Get::term_meta( $term_id, 'surerank_ignored_post_checks', true );
	}

	/**
	 * Update the site SEO checks.
	 *
	 * @param array<string, mixed> $response Response data.
	 * @param string               $type Type of checks.
	 * @return void
	 */
	private function update_site_seo_checks( array &$response, string $type ) {
		$existing_seo_checks = Get::option( 'surerank_site_seo_checks', [] );
		$seo_checks          = ! is_array( $existing_seo_checks ) ? [] : $existing_seo_checks;
		$seo_checks[ $type ] = $response;
		Update::option( 'surerank_site_seo_checks', $seo_checks );
	}

	/**
	 * Check if the cache exists.
	 *
	 * @param string $type Type of checks.
	 * @return bool
	 */
	private function cache_exists( string $type ) {
		$seo_checks = Get::option( 'surerank_site_seo_checks', [] );
		return isset( $seo_checks[ $type ] ) && ! empty( $seo_checks[ $type ] );
	}

	/**
	 * Get cached response.
	 *
	 * @param string $type Type of checks.
	 * @return array<string, mixed>
	 */
	private function get_cached_response( string $type ) {
		$seo_checks = Get::option( 'surerank_site_seo_checks', [] );
		return $seo_checks[ $type ] ?? [];
	}

	/**
	 * Get general checks route arguments
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private function get_general_checks_args() {
		return [
			'url' => [
				'type'              => 'string',
				'validate_callback' => static function ( $param, $request, $key ) {
					return filter_var( $param, FILTER_VALIDATE_URL );
				},
				'required'          => true,
			],
		];
	}

	/**
	 * Get force arguments
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private function get_force_args() {
		return [
			'force' => [
				'type'     => 'boolean',
				'required' => false,
			],
		];
	}

	/**
	 * Get broken links route arguments
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private function get_broken_links_args() {
		return [
			'url'        => [
				'type'     => 'string',
				'required' => true,
			],
			'user_agent' => [
				'type'     => 'string',
				'required' => true,
			],
			'post_id'    => [
				'type'              => 'integer',
				'required'          => true,
				'validate_callback' => static function ( $param, $request, $key ) {
					return $param > 0;
				},
			],
			'urls'       => [
				'type'     => 'array',
				'required' => true,
			],
		];
	}

	/**
	 * Get post ID arguments
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private function get_post_id_args() {
		return [
			'post_ids' => [
				'type'              => 'array',
				'required'          => true,
				'sanitize_callback' => [ self::class, 'sanitize_ids' ],
				'items'             => [
					'type' => 'integer',
				],
			],
		];
	}

	/**
	 * Get term ID arguments
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private function get_term_id_args() {
		return [
			'term_ids' => [
				'type'              => 'array',
				'required'          => true,
				'sanitize_callback' => [ self::class, 'sanitize_ids' ],
				'items'             => [
					'type' => 'integer',
				],
			],
		];
	}

	/**
	 * Get ID arguments
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private function get_id_args() {
		return [
			'id' => [
				'type'     => 'string',
				'required' => true,
			],
		];
	}

	/**
	 * Get sanitized ID arguments
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private function get_sanitized_id_args() {
		return [
			'id' => [
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
		];
	}

	/**
	 * Get ignore post check arguments
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private function get_ignore_post_check_args() {
		return [
			'id'         => [
				'type'              => 'string',
				'required'          => true,
				'sanitize_callback' => 'sanitize_text_field',
			],
			'post_id'    => [
				'type'     => 'integer',
				'required' => true,
			],
			'check_type' => [
				'type'        => 'string',
				'default'     => 'post',
				'enum'        => [
					'post',
					'taxonomy',
				],
				'description' => __( 'Type of check to delete. Can be "post" or "taxonomy".', 'surerank' ),
			],
		];
	}

	/**
	 * Get post ID with check type arguments
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private function get_post_id_with_check_type_args() {
		return [
			'post_id'    => [
				'type'     => 'integer',
				'required' => true,
			],
			'check_type' => [
				'type'        => 'string',
				'default'     => 'post',
				'enum'        => [
					'post',
					'taxonomy',
				],
				'description' => __( 'Type of check to delete. Can be "post" or "taxonomy".', 'surerank' ),
			],
		];
	}

	/**
	 * Create error response
	 *
	 * @param string $message Error message.
	 * @return WP_REST_Response
	 */
	private function create_error_response( $message ) {
		return rest_ensure_response(
			[
				'status'  => 'error',
				'message' => $message,
			]
		);
	}

	/**
	 * Check if post cache is valid
	 *
	 * @param \WP_Post $post Post object.
	 * @param int      $post_id Post ID.
	 * @return bool
	 */
	private function is_post_cache_valid( $post, $post_id ) {
		$post_modified_time  = $post->post_modified_gmt ? strtotime( $post->post_modified_gmt ) : 0;
		$checks_last_updated = Get::post_meta( $post_id, SURERANK_SEO_CHECKS_LAST_UPDATED, true );
		$settings_updated    = Get::option( SURERANK_SEO_LAST_UPDATED );

		$checks_last_updated = ! empty( $checks_last_updated ) ? (int) $checks_last_updated : 0;
		$settings_updated    = ! empty( $settings_updated ) ? (int) $settings_updated : 0;

		return $checks_last_updated !== 0 &&
			$post_modified_time <= $checks_last_updated &&
			( $settings_updated === 0 || $checks_last_updated >= $settings_updated );
	}

	/**
	 * Get cached post checks
	 *
	 * @param int $post_id Post ID.
	 * @return array<string,mixed>|WP_Error
	 */
	private function get_cached_post_checks( $post_id ) {
		$post_checks = Get::post_meta( $post_id, 'surerank_seo_checks', true );
		if ( ! empty( $post_checks ) ) {
			return $this->get_updated_ignored_check_list( $post_checks, $post_id, 'post' );
		}
		return new WP_Error( 'no_cached_checks', __( 'No cached checks found.', 'surerank' ) );
	}

	/**
	 * Check if taxonomy cache is valid
	 *
	 * @param int $term_id Term ID.
	 * @return bool
	 */
	private function is_taxonomy_cache_valid( $term_id ) {
		$term_modified_time  = Get::term_meta( $term_id, SURERANK_TAXONOMY_UPDATED_AT, true );
		$checks_last_updated = Get::term_meta( $term_id, SURERANK_SEO_CHECKS_LAST_UPDATED, true );
		$settings_updated    = Get::option( SURERANK_SEO_LAST_UPDATED );

		$term_modified_time  = ! empty( $term_modified_time ) ? (int) $term_modified_time : 0;
		$checks_last_updated = ! empty( $checks_last_updated ) ? (int) $checks_last_updated : 0;
		$settings_updated    = ! empty( $settings_updated ) ? (int) $settings_updated : 0;

		return $checks_last_updated !== 0 &&
			$term_modified_time <= $checks_last_updated &&
			( $settings_updated === 0 || $checks_last_updated >= $settings_updated );
	}

	/**
	 * Get cached taxonomy checks
	 *
	 * @param int $term_id Term ID.
	 * @return array<string,mixed>|WP_Error
	 */
	private function get_cached_taxonomy_checks( $term_id ) {
		$term_checks = Get::term_meta( $term_id, 'surerank_seo_checks', true );
		if ( ! empty( $term_checks ) ) {
			return $this->get_updated_ignored_check_list( $term_checks, $term_id, 'taxonomy' );
		}
		return new WP_Error( 'no_cached_checks', __( 'No cached checks found.', 'surerank' ) );
	}

	/**
	 * Fetch URL status
	 *
	 * @param string $url URL to check.
	 * @return array<string, mixed>|WP_Error
	 */
	private function fetch_url_status( $url ) {
		return Requests::get(
			$url,
			apply_filters(
				'surerank_broken_link_request_args',
				[
					'limit_response_size' => 1,
					'timeout'             => 30, // phpcs:ignore WordPressVIPMinimum.Performance.RemoteRequestTimeout.timeout_timeout
				]
			)
		);
	}

	/**
	 * Create broken link error response
	 *
	 * @param string $message Error message.
	 * @return WP_REST_Response
	 */
	private function create_broken_link_error_response( $message ) {
		return rest_ensure_response(
			[
				'success' => false,
				'message' => $message,
			]
		);
	}

	/**
	 * Handle broken link error
	 *
	 * @param string        $url URL.
	 * @param int           $post_id Post ID.
	 * @param array<string> $urls URLs array.
	 * @param WP_Error      $response Error response.
	 * @return WP_REST_Response
	 */
	private function handle_broken_link_error( $url, $post_id, $urls, $response ) {
		$this->save_broken_links( $url, $post_id, $urls, 500, $response->get_error_message() );
		self::log( 'Link is broken: ' . $url . ' with Error: ' . $response->get_error_message() );
		return rest_ensure_response(
			[
				'success' => false,
				'message' => __( 'Link is broken', 'surerank' ),
				'status'  => $response->get_error_code(),
				'details' => $response->get_error_message(),
			]
		);
	}

	/**
	 * Handle broken link status error
	 *
	 * @param string               $url URL.
	 * @param int                  $post_id Post ID.
	 * @param array<string>        $urls URLs array.
	 * @param int                  $status_code HTTP status code.
	 * @param array<string, mixed> $response HTTP response.
	 * @return WP_REST_Response
	 */
	private function handle_broken_link_status_error( $url, $post_id, $urls, $status_code, $response ) {
		$this->save_broken_links( $url, $post_id, $urls, $status_code, wp_remote_retrieve_response_message( $response ) );
		self::log( 'Link is broken: ' . $url . ' with status code: ' . $status_code );
		return rest_ensure_response(
			[
				'success' => false,
				'message' => __( 'Link is broken', 'surerank' ),
				'details' => wp_remote_retrieve_response_message( $response ),
				'status'  => $status_code,
			]
		);
	}
}
