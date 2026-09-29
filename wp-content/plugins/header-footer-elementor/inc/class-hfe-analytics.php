<?php
/**
 * HFE Analytics.
 *
 * @package HFE
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * HFE Analytics.
 *
 * HFE Analytics. handler class is responsible for rolling back HFE to
 * previous version.
 *
 * @since 2.3.0
 */
if ( ! class_exists( 'HFE_Analytics' ) ) {

	class HFE_Analytics {

		/**
		 * HFE Analytics constructor.
		 *
		 * Initializing HFE Analytics.
		 *
		 * @since 2.3.0
		 * @access public
		 *
		 * @param array $args Optional. HFE Analytics arguments. Default is an empty array.
		 */
		public function __construct() {
			add_action( 'admin_init', [ $this, 'maybe_migrate_analytics_tracking' ] );

			// Load analytics events class.
			if ( ! class_exists( 'HFE_Analytics_Events' ) ) {
				require_once HFE_DIR . 'inc/class-hfe-analytics-events.php';
			}

			// BSF Analytics Tracker.
			if ( ! class_exists( 'BSF_Analytics_Loader' ) ) {
				require_once HFE_DIR . 'admin/bsf-analytics/class-bsf-analytics-loader.php';
			}

			$bsf_analytics = BSF_Analytics_Loader::get_instance();

			$bsf_analytics->set_entity(
				[
					'uae' => [
						'product_name'        => 'Ultimate Addons for Elementor',
						'path'                => HFE_DIR . 'admin/bsf-analytics',
						'author'              => 'Ultimate Addons for Elementor',
						'time_to_display'     => '+24 hours',
						'deactivation_survey' => [
							[
								'id'                => 'deactivation-survey-header-footer-elementor',
								'popup_logo'        => HFE_URL . 'assets/images/settings/logo.svg',
								'plugin_slug'       => 'header-footer-elementor',
								'plugin_version'    => HFE_VER,
								'popup_title'       => 'Quick Feedback',
								'support_url'       => 'https://ultimateelementor.com/contact/',
								'popup_description' => 'If you have a moment, please share why you are deactivating Ultimate Addons for Elementor:',
								'show_on_screens'   => [ 'plugins' ],
							],
						],
						'hide_optin_checkbox' => true,
					],
				]
			);

			add_filter( 'bsf_core_stats', [ $this, 'add_uae_analytics_data' ] );

			// Event tracking hooks.
			add_action( 'transition_post_status', [ $this, 'track_first_template_published' ], 10, 3 );
			if ( ! HFE_Analytics_Events::is_tracked( 'first_widget_used' ) ) {
				add_action( 'elementor/editor/after_save', [ $this, 'track_first_widget_on_save' ], 10, 2 );
			}

			// Detect state-based events only in admin context, throttled to once per day.
			if ( is_admin() && false === get_transient( 'hfe_state_events_checked' ) ) {
				$this->detect_state_events();
				set_transient( 'hfe_state_events_checked', 1, DAY_IN_SECONDS );
			}
		}

		/**
		 * Migrates analytics tracking option from 'bsf_usage_optin' to 'uae_usage_optin'.
		 *
		 * Checks if the old analytics tracking option ('bsf_usage_optin') is set to 'yes'
		 * and if the new option ('uae_usage_optin') is not already set.
		 * If so, updates the new tracking option to 'yes' to maintain user consent during migration.
		 *
		 * @since 2.3.2
		 * @access public
		 *
		 * @return void
		 */
		public function maybe_migrate_analytics_tracking() {
			// Skip if already migrated or new option already set.
			if ( false !== get_site_option( 'uae_usage_optin', false ) ) {
				return;
			}

			$old_tracking = get_site_option( 'bsf_usage_optin', false );
			if ( 'yes' === $old_tracking ) {
				update_site_option( 'uae_usage_optin', 'yes' );
				$time = get_site_option( 'bsf_usage_installed_time' );
				if ( false !== $time ) {
					update_site_option( 'uae_usage_installed_time', $time );
				}
			}
		}
        
        /**
         * Callback function to add specific analytics data.
         *
         * @param array $stats_data existing stats_data.
         * @since 2.3.0
         * @return array
         */
        public function add_uae_analytics_data( $stats_data ) {
			 // Check if $stats_data is empty or not an array.
			 if ( empty( $stats_data ) || ! is_array( $stats_data ) ) {
				$stats_data = []; // Initialize as an empty array.
			}
		
            $stats_data['plugin_data']['uae']		= [
                'free_version'  => HFE_VER,
                'pro_version' => ( defined( 'UAEL_VERSION' ) ? UAEL_VERSION : '' ),
                'site_language' => get_locale(),
                'elementor_version' => ( defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '' ),
                'elementor_pro_version' => ( defined( 'ELEMENTOR_PRO_VERSION' ) ? ELEMENTOR_PRO_VERSION : '' ),
                'onboarding_triggered' => ( 'yes' === get_option( 'hfe_onboarding_triggered' ) ) ? 'yes' : 'no',
				'uaelite_subscription' => ( 'done' === get_option( 'uaelite_subscription' ) ) ? 'yes' : 'no',
				'active_theme'         => get_template(),
				'is_theme_supported'   => (bool) get_option( 'hfe_is_theme_supported', false ),
				'onboarding_analytics' => get_option( 'hfe_onboarding_analytics', [] ),
            ];

            $template_counts = wp_count_posts( 'elementor-hf' );
            $stats_data['plugin_data']['uae']['numeric_values'] = [
                'total_hfe_templates' => isset( $template_counts->publish ) ? (int) $template_counts->publish : 0,
            ];

			$widgets_usage = $this->hfe_get_widgets_usage();
			foreach ( $widgets_usage as $key => $value ) {
				$stats_data['plugin_data']['uae']['numeric_values'][ $key ] = $value;
			}

			$learn_progress = $this->get_learn_progress_analytics_data();
			if ( ! empty( $learn_progress ) ) {
				$stats_data['plugin_data']['uae']['learn_chapters_completed'] = $learn_progress;
			}

			// Add KPI tracking data.
			$kpi_data = $this->get_kpi_tracking_data();
			if ( ! empty( $kpi_data ) ) {
				$stats_data['plugin_data']['uae']['kpi_records'] = $kpi_data;
			}

			// Flush pending events into payload (only if any exist).
			$pending_events = HFE_Analytics_Events::flush_pending();
			if ( ! empty( $pending_events ) ) {
				$stats_data['plugin_data']['uae']['events_record'] = $pending_events;
			}

            return $stats_data;
        }

		/**
		 * Track first time a template is published.
		 *
		 * @param string   $new_status New post status.
		 * @param string   $old_status Old post status.
		 * @param \WP_Post $post       Post object.
		 * @since 2.8.6
		 * @return void
		 */
		public function track_first_template_published( $new_status, $old_status, $post ) {
			if ( 'publish' !== $new_status || 'publish' === $old_status || 'elementor-hf' !== $post->post_type ) {
				return;
			}

			$template_type      = get_post_meta( $post->ID, 'ehf_template_type', true );
			$install_time       = get_option( 'uae_usage_installed_time', 0 );
			$days_since_install = 0;
			if ( $install_time > 0 ) {
				$days_since_install = (int) floor( ( time() - (int) $install_time ) / DAY_IN_SECONDS );
			}

			HFE_Analytics_Events::track(
				'first_template_published',
				(string) $post->ID,
				[
					'template_type'      => ! empty( $template_type ) ? $template_type : 'unknown',
					'days_since_install' => (string) $days_since_install,
				]
			);
		}

		/**
		 * Track first HFE widget usage on Elementor post save.
		 *
		 * Fires on elementor/editor/after_save. Checks if the saved post
		 * contains any HFE widget and tracks the first_widget_used event
		 * immediately instead of waiting for the daily cron scan.
		 *
		 * @since 2.8.7
		 * @param int   $post_id Post ID.
		 * @param array $editor_data Elementor editor data.
		 * @return void
		 */
		public function track_first_widget_on_save( $post_id, $editor_data ) {
			// Skip if already tracked — zero overhead after first detection.
			if ( HFE_Analytics_Events::is_tracked( 'first_widget_used' ) ) {
				return;
			}

			$allowed_widgets = [
				'hfe-breadcrumbs-widget',
				'hfe-cart',
				'copyright',
				'navigation-menu',
				'page-title',
				'post-info-widget',
				'retina',
				'hfe-search-button',
				'site-logo',
				'hfe-site-tagline',
				'hfe-site-title',
				'hfe-infocard',
				'hfe-woo-product-grid',
				'hfe-basic-posts',
				'hfe-counter',
			];

			$elementor_data = get_post_meta( $post_id, '_elementor_data', true );
			if ( empty( $elementor_data ) ) {
				return;
			}

			$first_widget = '';
			foreach ( $allowed_widgets as $widget ) {
				if ( false !== strpos( $elementor_data, '"widgetType":"' . $widget . '"' ) ) {
					$first_widget = $widget;
					break;
				}
			}

			if ( empty( $first_widget ) ) {
				return;
			}

			$install_time       = get_option( 'uae_usage_installed_time', 0 );
			$days_since_install = 0;
			if ( $install_time > 0 ) {
				$days_since_install = (int) floor( ( time() - (int) $install_time ) / DAY_IN_SECONDS );
			}

			HFE_Analytics_Events::track(
				'first_widget_used',
				$first_widget,
				[
					'days_since_install' => (string) $days_since_install,
				]
			);
		}

		/**
		 * Detect state-based events that can't use direct hooks.
		 * Uses dedup in HFE_Analytics_Events::track() — safe to call repeatedly.
		 *
		 * @since 2.8.6
		 * @return void
		 */
		private function detect_state_events() {
			// Read pushed + pending once to avoid repeated get_option calls per event.
			$pushed  = get_option( 'hfe_usage_events_pushed', [] );
			$pushed  = is_array( $pushed ) ? $pushed : [];
			$pending = get_option( 'hfe_usage_events_pending', [] );
			$pending = is_array( $pending ) ? $pending : [];

			$tracked_names = array_merge( $pushed, array_column( $pending, 'event_name' ) );

			// onboarding_completed: detect completed or early-exit state from the analytics blob.
			if ( ! in_array( 'onboarding_completed', $tracked_names, true ) ) {
				$onboarding_analytics = get_option( 'hfe_onboarding_analytics', [] );
				$onboarding_done      = 'yes' === get_option( 'hfe_onboarding_triggered' );
				$onboarding_skipped   = ! empty( $onboarding_analytics['exited_early'] ) && empty( $onboarding_analytics['completed'] );

				if ( $onboarding_done || $onboarding_skipped ) {
					HFE_Analytics_Events::track(
						'onboarding_completed',
						$onboarding_skipped ? 'no' : 'yes',
						[ 'skipped' => (string) (int) $onboarding_skipped ]
					);
				}
			}

			// first_widget_used: tracked in real-time via elementor/editor/after_save hook.

			// post_duplicator_used: fires once when the post duplicator feature has been used.
			if ( ! in_array( 'post_duplicator_used', $tracked_names, true ) ) {
				if ( (int) get_option( 'uae_duplicator_count', 0 ) > 0 ) {
					$install_time       = get_option( 'uae_usage_installed_time', 0 );
					$days_since_install = 0;
					if ( $install_time > 0 ) {
						$days_since_install = (int) floor( ( time() - (int) $install_time ) / DAY_IN_SECONDS );
					}
					HFE_Analytics_Events::track(
						'post_duplicator_used',
						'yes',
						[ 'days_since_install' => (string) $days_since_install ]
					);
				}
			}
		}

		/**
		 * Fetch Elementor data.
		 */
		private function hfe_get_widgets_usage() {
			return get_option( 'uae_widgets_usage_data_option', [] );
		}

		/**
		 * Get UAE learn progress analytics data.
		 *
		 * @return array
		 */
		private function get_learn_progress_analytics_data() {
			global $wpdb;

			$results = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT meta_value FROM {$wpdb->usermeta} WHERE meta_key = %s LIMIT 100",
					'hfe_learn_progress'
				),
				ARRAY_A
			);

			if ( empty( $results ) ) {
				return [];
			}

			if ( ! class_exists( '\HFE\API\HFE_Learn_API' ) ) {
				return [];
			}

			$chapters = \HFE\API\HFE_Learn_API::get_chapters_structure();

			$completed_chapters = [];

			foreach ( $results as $row ) {
				$progress_data = maybe_unserialize( $row['meta_value'] );

				// Guard against object injection — only accept arrays.
				if ( ! is_array( $progress_data ) || is_object( $progress_data ) ) {
					continue;
				}

				foreach ( $chapters as $chapter ) {

					$chapter_id = $chapter['id'];

					// Skip already counted.
					if ( in_array( $chapter_id, $completed_chapters, true ) ) {
						continue;
					}

					// Skip invalid chapters.
					if ( empty( $chapter['steps'] ) || ! is_array( $chapter['steps'] ) ) {
						continue;
					}

					// Skip if not present in user data.
					if ( empty( $progress_data[ $chapter_id ] ) || ! is_array( $progress_data[ $chapter_id ] ) ) {
						continue;
					}

					$all_steps_completed = true;

					foreach ( $chapter['steps'] as $step ) {
						$step_id = $step['id'];

						if (
							! isset( $progress_data[ $chapter_id ][ $step_id ] ) ||
							! $progress_data[ $chapter_id ][ $step_id ]
						) {
							$all_steps_completed = false;
							break;
						}
					}

					if ( $all_steps_completed ) {
						$completed_chapters[] = $chapter_id;
					}
				}
			}

			return array_values( array_unique( $completed_chapters ) );
		}

		/**
		 * Get KPI tracking data for the last 2 days (excluding today).
		 *
		 * Uses stored snapshots for state-based metrics (total_templates,
		 * widgets_count, total_widget_instances) and computes modified_templates
		 * fresh for each completed past day to ensure accurate counts.
		 *
		 * @since 2.8.4
		 * @return array KPI data organized by date.
		 */
		private function get_kpi_tracking_data() {
			$snapshots = get_option( 'hfe_kpi_daily_snapshots', [] );

			if ( empty( $snapshots ) || ! is_array( $snapshots ) ) {
				return [];
			}

			$kpi_data = [];
			$today    = current_time( 'Y-m-d' );

			// Only send data for dates that have actual per-day snapshots.
			for ( $i = 1; $i <= 2; $i++ ) {
				$date = wp_date( 'Y-m-d', strtotime( $today . ' -' . $i . ' days' ) );

				if ( ! isset( $snapshots[ $date ]['numeric_values'] ) ) {
					continue;
				}

				$kpi_data[ $date ] = [
					'numeric_values' => array_merge(
						$snapshots[ $date ]['numeric_values'],
						[ 'modified_templates' => $this->get_modified_template_count( $date ) ]
					),
				];
			}

			return $kpi_data;
		}

		/**
		 * Get count of HFE templates modified on a given date.
		 *
		 * @since 2.8.4
		 * @param string $date Date in Y-m-d format.
		 * @return int Modified template count for the date.
		 */
		private function get_modified_template_count( $date ) {
			$query = new WP_Query(
				[
					'post_type'      => 'elementor-hf',
					'post_status'    => 'publish',
					'posts_per_page' => 1,
					'no_found_rows'  => false,
					'fields'         => 'ids',
					'date_query'     => [
						[
							'column'    => 'post_modified',
							'after'     => $date . ' 00:00:00',
							'before'    => $date . ' 23:59:59',
							'inclusive' => true,
						],
					],
				]
			);

			return (int) $query->found_posts;
		}
	}
}
new HFE_Analytics();
