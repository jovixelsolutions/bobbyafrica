<?php
/**
 * PRO Features hub — hero, featured highlights, categorized feature grid,
 * Free vs PRO table, CTA.
 *
 * Marketing content only: this lives in the Free plugin, so it must never
 * reference PRO option keys/ids — it only describes features and links out.
 * Styling is class-based (see css/components/pro-features.css); no inline
 * styles so the tab follows the theme tokens in light and dark.
 *
 * @package Click_To_Chat
 * @since 4.41
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'HT_CTC_Pro_Features_Panel' ) ) {
	/**
	 * Renders the PRO Features panel markup.
	 */
	class HT_CTC_Pro_Features_Panel {
		/**
		 * Echo the panel markup.
		 *
		 * @return void
		 */
		public static function render() {
			/*
			 * The hero and the closing band are the two highest-intent clicks on this
			 * page, and they used to share one untagged-by-content URL — so a campaign
			 * report could not tell "convinced by the pitch" from "convinced after
			 * reading everything". Separate utm_content, same destination.
			 */
			$ctc_hero_url    = HT_CTC_Utils::pro_url( 'pro_tab', 'hero' );
			$ctc_band_url    = HT_CTC_Utils::pro_url( 'pro_tab', 'cta_band' );
			$ctc_install_url = HT_CTC_Utils::pro_url( 'pro_tab', 'install_guide', 'https://holithemes.com/plugins/click-to-chat/installation-of-click-to-chat-pro-plugin/' );

			/*
			 * Feature catalogue grouped by area. Each group renders as a titled section of
			 * cards. `url` deep-links to the matching docs page. Kept as data (not markup)
			 * so it stays easy to align with the PRO plugin.
			 *
			 * `key` is the utm_content id the link is tagged with. It deliberately matches
			 * the id the same feature uses in its inline teaser, so one feature's pull
			 * aggregates across surfaces instead of splitting into per-page names.
			 */
			$ctc_pro_groups = array(
				array(
					'title' => 'Greetings & Lead Capture',
					'icon'  => 'dashicons-forms',
					'items' => array(
						array(
							'icon'  => 'dashicons-forms',
							'title' => 'Greetings Form Filling',
							'desc'  => 'Capture name, email, phone and more before the chat opens — 8 field types including date and international number.',
							'url'   => 'https://holithemes.com/plugins/click-to-chat/greetings-form/',
							'key'   => 'greetings_form',
						),
						// Only the seconds and scroll-percentage triggers are settings. The third
						// is PRO's 'ctc_greetings_now' class on an element, so say a class is needed.
						array(
							'icon'  => 'dashicons-controls-play',
							'title' => 'Auto-Open Triggers',
							'desc'  => 'Open the greeting after a set number of seconds, or at a scroll percentage — or when a section you mark with a CSS class scrolls into view.',
							'url'   => 'https://holithemes.com/plugins/click-to-chat/greetings-actions/',
							'key'   => 'greetings_actions',
						),
						array(
							'icon'  => 'dashicons-calendar-alt',
							'title' => 'Date & Time Picker',
							'desc'  => 'Visitors choose a date and time that suits them, and it arrives with their message. You set how far ahead they can choose, how much notice you need, and how long each slot is.',
							'url'   => 'https://holithemes.com/plugins/click-to-chat/docs/greetings-form/',
							'key'   => 'greetings_scheduler',
						),
						// // Lead Notifications — enable after this feature is added and also make sure it's properly aligned.
						// array(
						// 'icon'  => 'dashicons-email-alt',
						// 'title' => 'Lead Notifications',
						// 'desc'  => 'Every submitted form is emailed to you, with your own subject line, and posted to a webhook.',
						// 'url'   => 'https://holithemes.com/plugins/click-to-chat/docs/greetings-form/',
						// 'key'   => 'greetings_leads',
						// ),
					),
				),
				array(
					'title' => 'Agents & Availability',
					'icon'  => 'dashicons-businessperson',
					'items' => array(
						array(
							'icon'  => 'dashicons-groups',
							'title' => 'Multi-Agent Support',
							'desc'  => 'Add agents with a photo, a role, their own number and their own weekly hours.',
							'url'   => 'https://holithemes.com/plugins/click-to-chat/multi-agent/',
							'key'   => 'multi_agent',
						),
						array(
							'icon'  => 'dashicons-clock',
							'title' => __( 'Business Hours', 'click-to-chat-for-whatsapp' ),
							'desc'  => 'Several time slots a day, in your site\'s timezone. When you close, hide the widget or switch to an offline number.',
							'url'   => 'https://holithemes.com/plugins/click-to-chat/docs/business-hours-online-offline/',
							'key'   => 'business_hours',
						),
						array(
							'icon'  => 'dashicons-randomize',
							'title' => 'Random & Sequential Numbers',
							'desc'  => 'Spread chats over a list of numbers — purely at random, or in a fixed rotation that advances on each click.',
							'url'   => 'https://holithemes.com/plugins/click-to-chat/random-number/',
							'key'   => 'random_number',
						),
					),
				),
				array(
					'title' => 'Smart Display & Targeting',
					'icon'  => 'dashicons-visibility',
					'items' => array(
						array(
							'icon'  => 'dashicons-admin-site-alt3',
							'title' => 'Country-Based Display',
							'desc'  => 'Show or hide the chat widget by each visitor\'s country.',
							'url'   => 'https://holithemes.com/plugins/click-to-chat/display-based-on-country/',
							'key'   => 'country_display',
						),
						array(
							'icon'  => 'dashicons-admin-users',
							'title' => 'Visitor Targeting & Delays',
							'desc'  => 'Choose who sees the chat widget by login status, and delay it by seconds or by scroll depth.',
							'url'   => 'https://holithemes.com/plugins/click-to-chat/display/',
							'key'   => 'schedule_triggers',
						),
						array(
							'icon'  => 'dashicons-admin-page',
							'title' => 'Page-Level Overrides',
							'desc'  => 'Give one page its own style, greeting, custom link, or time and scroll delay, without touching your global settings.',
							'url'   => 'https://holithemes.com/plugins/click-to-chat/change-values-at-page-level/',
							'key'   => 'page_level',
						),
					),
				),
				array(
					'title' => 'Analytics & Tracking',
					'icon'  => 'dashicons-chart-line',
					'items' => array(
						array(
							'icon'  => 'dashicons-chart-bar',
							'title' => __( 'Google Ads Conversion', 'click-to-chat-for-whatsapp' ),
							'desc'  => 'Send a conversion with your conversion ID and label whenever a visitor clicks to chat.',
							'url'   => 'https://holithemes.com/plugins/click-to-chat/google-ads-conversion/',
							'key'   => 'google_ads',
						),
						array(
							'icon'  => 'dashicons-facebook',
							'title' => 'Meta Conversions API',
							'desc'  => 'Server-side events sent with your pixel ID and access token, so ad blockers and cookie limits stop costing you conversions.',
							// point at a dedicated Meta Conversions API docs page once available.
							'url'   => 'https://holithemes.com/plugins/click-to-chat/pricing/',
							'key'   => 'meta_capi',
						),
						array(
							'icon'  => 'dashicons-cloud',
							'title' => 'Dynamic Values',
							'desc'  => 'Read any URL parameter ([gclid], [utm_source]) or cookie into your webhook, Google Analytics and Meta Pixel events.',
							'url'   => 'https://holithemes.com/plugins/click-to-chat/webhooks/',
							'key'   => 'webhooks',
						),
					),
				),
			);

			/*
			 * Free vs PRO comparison, in labelled sections. Eighteen rows in one column is
			 * a wall to scan; grouped, a reader can find the part they care about (forms,
			 * targeting, analytics) and read four rows instead of eighteen.
			 *
			 * `free` / `pro` are booleans; `free_note` and
			 * `pro_note` optionally say WHAT each side gives, which is the whole point of
			 * the table — "Business hours: yes" persuades nobody, "time slots per day, plus
			 * an offline number" does. A note replaces the tick in the Free column and sits
			 * beside it in the PRO one.
			 *
			 * Every note has to be checkable against the two plugins. A row that credits
			 * PRO with something Free already ships (WooCommerce, page-level settings and
			 * webhooks all nearly went that way) costs more than a missing row.
			 */
			$ctc_compare_rows = array(
				array(
					'group' => 'Chat widget & placement',
					'rows'  => array(
						array(
							'label'     => 'WhatsApp widget, styles & positions',
							'free'      => true,
							'free_note' => '',
							'pro'       => true,
							'pro_note'  => '',
						),
						array(
							'label'     => 'WhatsApp numbers',
							'free'      => true,
							'free_note' => 'One number',
							'pro'       => true,
							'pro_note'  => 'Several — at random, in sequence, or one per agent',
						),
						array(
							'label'     => 'Page-level settings',
							'free'      => true,
							'free_note' => 'Number, message, call to action, show/hide',
							'pro'       => true,
							'pro_note'  => 'Plus style, greeting, link, and time or scroll delay',
						),
						array(
							'label'     => 'WooCommerce',
							'free'      => true,
							'free_note' => 'Product and shop pages',
							'pro'       => true,
							'pro_note'  => 'Plus your business hours applied there',
						),
					),
				),
				array(
					'group' => 'Greetings & forms',
					'rows'  => array(
						array(
							'label'     => 'Greetings dialog',
							'free'      => true,
							'free_note' => 'Basic',
							'pro'       => true,
							'pro_note'  => 'A form, an agent list, or both',
						),
						array(
							'label'     => 'Lead capture form',
							'free'      => false,
							'free_note' => '',
							'pro'       => true,
							'pro_note'  => '8 field types, incl. date and international phone',
						),
						array(
							'label'     => 'Preferred date & time',
							'free'      => false,
							'free_note' => '',
							'pro'       => true,
							'pro_note'  => 'Visitor picks a slot; it arrives with their message',
						),
						array(
							'label'     => 'Form submissions',
							'free'      => false,
							'free_note' => '',
							'pro'       => true,
							'pro_note'  => 'Emailed to you, and sent to a webhook',
						),
						array(
							'label'     => 'Open the greeting automatically',
							'free'      => false,
							'free_note' => '',
							'pro'       => true,
							'pro_note'  => 'After a delay, or at a scroll percentage',
						),
					),
				),
				array(
					'group' => 'Agents & availability',
					'rows'  => array(
						array(
							'label'     => 'Multi-agent',
							'free'      => false,
							'free_note' => '',
							'pro'       => true,
							'pro_note'  => 'Photo, role, own number and own hours',
						),
						array(
							'label'     => 'Business hours',
							'free'      => false,
							'free_note' => '',
							'pro'       => true,
							'pro_note'  => 'Time slots per day, plus an offline number',
						),
					),
				),
				array(
					'group' => 'Who sees it, and when',
					'rows'  => array(
						array(
							'label'     => 'Country targeting',
							'free'      => false,
							'free_note' => '',
							'pro'       => true,
							'pro_note'  => 'Show or hide by the visitor\'s country',
						),
						array(
							'label'     => 'Visitor targeting & delays',
							'free'      => false,
							'free_note' => '',
							'pro'       => true,
							'pro_note'  => 'By login status, plus a time or scroll delay',
						),
					),
				),
				array(
					'group' => 'Analytics & tracking',
					'rows'  => array(
						array(
							'label'     => 'Google Analytics & Meta Pixel',
							'free'      => true,
							'free_note' => '',
							'pro'       => true,
							'pro_note'  => '',
						),
						array(
							'label'     => 'Google Ads conversion',
							'free'      => false,
							'free_note' => '',
							'pro'       => true,
							'pro_note'  => '',
						),
						array(
							'label'     => 'Meta Conversions API',
							'free'      => false,
							'free_note' => '',
							'pro'       => true,
							'pro_note'  => 'Server-side, so a blocked pixel still counts',
						),
						array(
							'label'     => 'Webhooks',
							'free'      => true,
							'free_note' => 'URL and your own values',
							'pro'       => true,
							'pro_note'  => 'Plus {url}, {time} and {title}',
						),
						array(
							'label'     => 'Values from cookies & URL parameters',
							'free'      => false,
							'free_note' => '',
							'pro'       => true,
							'pro_note'  => '[gclid], [utm_source] or any cookie value',
						),
					),
				),
			);
			?>
			<div class="ctc-pro">

				<!-- Hero -->
				<div class="ctc-pro-hero">
					<span class="ctc-pro-eyebrow"><span class="dashicons dashicons-star-filled" aria-hidden="true"></span> <?php esc_html_e( 'PRO', 'click-to-chat-for-whatsapp' ); ?></span>
					<h2 class="ctc-pro-hero-title">Turn more visitors into WhatsApp conversations</h2>
					<p class="ctc-pro-hero-sub">Route chats to the right agent, capture a visitor's details before the chat opens, show the widget only while you are open, and see which ads and pages actually produce chats.</p>
					<div class="ctc-pro-hero-actions">
						<a href="<?php echo esc_url( $ctc_hero_url ); ?>" target="_blank" rel="noopener" class="ctc-pro-btn ctc-pro-btn-primary">
							View Pricing &amp; Get PRO
							<span class="dashicons dashicons-external" aria-hidden="true"></span>
							<span class="screen-reader-text">(opens in a new tab)</span>
						</a>
						<a href="#ctc-pro-compare"  class="ctc-pro-btn ctc-pro-btn-ghost">Compare Free vs PRO</a>
					</div>
					<ul class="ctc-pro-trust">
						<li><span class="dashicons dashicons-shield-alt" aria-hidden="true"></span> 14-day money-back guarantee</li>
						<!-- <li><span class="dashicons dashicons-yes-alt" aria-hidden="true"></span> Runs alongside the Free plugin</li> -->
						<li><span class="dashicons dashicons-groups" aria-hidden="true"></span> 600,000+ sites run Click to Chat</li>
					</ul>
				</div>

				<!-- Feature groups -->
				<?php foreach ( $ctc_pro_groups as $ctc_group ) { ?>
					<section class="ctc-pro-section">
						<h3 class="ctc-pro-section-title">
							<span class="dashicons <?php echo esc_attr( $ctc_group['icon'] ); ?>" aria-hidden="true"></span>
							<?php echo esc_html( $ctc_group['title'] ); ?>
						</h3>
						<div class="ctc-pro-grid">
							<?php foreach ( $ctc_group['items'] as $ctc_item ) { ?>
								<a class="ctc-pro-card" href="<?php echo esc_url( HT_CTC_Utils::pro_url( 'pro_tab', isset( $ctc_item['key'] ) ? $ctc_item['key'] : '', $ctc_item['url'] ) ); ?>" target="_blank" rel="noopener">
									<span class="ctc-pro-card-icon" aria-hidden="true"><span class="dashicons <?php echo esc_attr( $ctc_item['icon'] ); ?>"></span></span>
									<span class="ctc-pro-card-body">
										<span class="ctc-pro-card-title">
											<?php echo esc_html( $ctc_item['title'] ); ?>
											<span class="ctc-pro-card-arrow dashicons dashicons-external" aria-hidden="true"></span>
										</span>
										<span class="ctc-pro-card-desc"><?php echo esc_html( $ctc_item['desc'] ); ?></span>
											<span class="screen-reader-text">(opens in a new tab)</span>
									</span>
								</a>
							<?php } ?>
						</div>
					</section>
				<?php } ?>

				<!-- Comparison -->
				<section class="ctc-pro-section" id="ctc-pro-compare">
					<h3 class="ctc-pro-section-title">
						<span class="dashicons dashicons-editor-table" aria-hidden="true"></span>
						Free vs PRO
					</h3>
					<div class="ctc-pro-compare-wrap">
						<table class="ctc-pro-compare">
							<thead>
								<tr>
									<th class="ctc-pro-compare-feature">Feature</th>
									<th>Free</th>
									<th class="ctc-pro-compare-pro"><span class="dashicons dashicons-star-filled" aria-hidden="true"></span> <?php esc_html_e( 'PRO', 'click-to-chat-for-whatsapp' ); ?></th>
								</tr>
							</thead>
							<?php foreach ( $ctc_compare_rows as $ctc_group_row ) { ?>
								<tbody>
									<tr class="ctc-pro-compare-group">
										<th colspan="3" scope="rowgroup"><?php echo esc_html( $ctc_group_row['group'] ); ?></th>
									</tr>
									<?php foreach ( $ctc_group_row['rows'] as $ctc_row ) { ?>
									<tr>
										<td class="ctc-pro-compare-feature"><?php echo esc_html( $ctc_row['label'] ); ?></td>
										<td>
											<?php if ( ! empty( $ctc_row['free_note'] ) ) { ?>
												<span class="ctc-pro-compare-note"><?php echo esc_html( $ctc_row['free_note'] ); ?></span>
											<?php } elseif ( ! empty( $ctc_row['free'] ) ) { ?>
												<span class="dashicons dashicons-yes ctc-pro-yes" role="img" aria-label="Included"></span>
											<?php } else { ?>
												<span class="dashicons dashicons-minus ctc-pro-no" role="img" aria-label="Not included"></span>
											<?php } ?>
										</td>
										<td class="ctc-pro-compare-pro-cell">
											<?php if ( ! empty( $ctc_row['pro'] ) ) { ?>
												<span class="dashicons dashicons-yes ctc-pro-yes ctc-pro-yes-gold" role="img" aria-label="Included"></span>
												<?php if ( ! empty( $ctc_row['pro_note'] ) ) { ?>
													<span class="ctc-pro-compare-detail"><?php echo esc_html( $ctc_row['pro_note'] ); ?></span>
												<?php } ?>
											<?php } else { ?>
												<span class="dashicons dashicons-minus ctc-pro-no" role="img" aria-label="Not included"></span>
											<?php } ?>
										</td>
									</tr>
									<?php } ?>
								</tbody>
							<?php } ?>
						</table>
					</div>
				</section>

				<!-- Closing CTA -->
				<div class="ctc-pro-cta-band">
					<div class="ctc-pro-cta-copy">
						<h3>Ready to do more with WhatsApp?</h3>
						<p>PRO installs alongside the free plugin &mdash; your settings stay exactly as they are, and there is nothing to migrate. If it is not right for you, there is a 14-day money-back guarantee.</p>
					</div>
					<div class="ctc-pro-cta-actions">
						<a href="<?php echo esc_url( $ctc_band_url ); ?>" target="_blank" rel="noopener" class="ctc-pro-btn ctc-pro-btn-primary">
							View Pricing &amp; Get PRO
							<span class="dashicons dashicons-external" aria-hidden="true"></span>
							<span class="screen-reader-text">(opens in a new tab)</span>
						</a>
						<a href="<?php echo esc_url( $ctc_install_url ); ?>" target="_blank" rel="noopener" class="ctc-pro-cta-link">
							Installation guide
						</a>
					</div>
				</div>

			</div>

			<?php
		}
	}
}
