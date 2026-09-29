# BSF Analytics Events — Full Session Context

## Branch: `bsf-events`

### PRs

-   **Free plugin:** brainstormforce/cartflows#2214 — "Add BSF Analytics Events tracking infrastructure"
-   **Pro plugin:** brainstormforce/cartflows-pro#1156 — "Add BSF Analytics event flag-setters for Pro features"

---

## What Was Done

### 1. Analytics Class Created (earlier commits)

New file: `classes/class-cartflows-analytics.php` — single entry point for all BSF Analytics tracking.

-   Owns the complete `bsf_core_stats` payload: default stats, `numeric_values`, `boolean_values`, `kpi_records`, `events_record`
-   Replaced the former `Cartflows_Admin::get_specific_stats()` handler
-   `is_admin()` guard in constructor — no frontend overhead
-   Daily transient throttle (`cf_state_events_checked`) for state event detection
-   `BSF_Analytics_Events` instantiated with slug `cf` (options prefixed `cf_`)

### 2. Events Added (28 total)

**Activation & Setup (5):**
| Event | event_value | Properties | Detection |
|-------|------------|------------|-----------|
| `plugin_activated` | Version | `{source}` | State — always fires, dedup handles it |
| `plugin_updated` | New version | `{from_version}` | State — compares `cf_tracked_version` vs `CARTFLOWS_VER`, uses `flush_pushed()` to re-track |
| `onboarding_completed` | — | — | State — reads existing `wcf_setup_complete` option |
| `onboarding_skipped` | — | `{exit_step}` | State — reads existing `wcf_setup_skipped` + `wcf_exit_setup_step` |
| `pro_license_activated` | — | — | State — `_is_cartflows_pro()` function check |

**Feature Usage (9):**
| Event | event_value | Properties | Detection |
|-------|------------|------------|-----------|
| `first_flow_published` ⭐ | Flow ID | `{days_since_install, source, step_count}` | Hook — `transition_post_status` when flow publishes |
| `first_checkout_configured` | Step ID | `{layout}` | State — reads `cf_first_checkout_configured` flag |
| `first_template_imported` | — | `{page_builder}` | State — reads `cf_first_template_imported` flag |
| `first_order_bump_created` | — | — | State — reads `cf_first_order_bump_created` flag |
| `first_upsell_accepted` | — | — | State — reads `cf_first_upsell_accepted` flag |
| `first_downsell_accepted` | — | — | State — reads `cf_first_downsell_accepted` flag |
| `first_ab_test_started` | — | — | State — reads `cf_first_ab_test_started` flag |
| `first_ab_test_winner_declared` | — | — | State — reads `cf_first_ab_test_winner` flag |
| `first_instant_layout_enabled` | — | — | State — reads `cf_first_instant_layout_enabled` flag |

**Configuration Milestones (2):**
| Event | Detection |
|-------|-----------|
| `first_store_checkout_set` | State — `Cartflows_Helper::get_global_setting('_cartflows_store_checkout') > 0` (existing option, no flag needed) |
| `first_webhook_configured` | State — reads `cf_first_webhook_configured` flag |

**Integrations (8):**
| Event | Detection |
|-------|-----------|
| `fb_pixel_connected` | State — reads existing `_cartflows_facebook` setting |
| `ga_connected` | State — reads existing `_cartflows_google_analytics` setting |
| `tiktok_connected` | State — reads existing `_cartflows_tiktok` setting |
| `pinterest_connected` | State — reads existing `_cartflows_pinterest` setting |
| `gads_connected` | State — reads existing `_cartflows_google_ads` setting |
| `snapchat_connected` | State — reads existing `_cartflows_snapchat` setting |
| `stripe_gateway_enabled` | State — scans WC payment gateways for `stripe` in ID |
| `paypal_gateway_enabled` | State — scans WC payment gateways for `paypal` in ID |

**Engagement & UI (4):**
| Event | Detection |
|-------|-----------|
| `pointer_accepted` | State — reads existing `cartflows_pointer_data['accepted']` |
| `pointer_dismissed` | State — reads existing `cartflows_pointer_data['dismissed']` |
| `weekly_report_notice_dismissed` | State — reads `cf_weekly_report_notice_dismissed` flag |
| `instant_checkout_notice_dismissed` | State — reads existing `wcf-instant-checkout-notice-skipped` option |

### 3. Daily KPIs (2)

| KPI            | Query                                                                                        |
| -------------- | -------------------------------------------------------------------------------------------- |
| `order_count`  | Daily completed/processing WC orders with `_wcf_flow_id` or `_cartflows_parent_flow_id` meta |
| `offer_orders` | Daily orders with `_cartflows_offer = 'yes'` meta (upsell/downsell)                          |

Both support HPOS and classic order storage. Queried for last 2 days excluding today.

### 4. Privacy Fixes Applied to Existing Payload

| Field                         | Before                              | After                                                          |
| ----------------------------- | ----------------------------------- | -------------------------------------------------------------- |
| `nps-survey-status`           | Raw NPS survey object               | Boolean `nps_survey_submitted`                                 |
| `learn-tab-completed-modules` | Raw array of module IDs             | Integer `learn_modules_completed`                              |
| `active-gateways`             | Full gateway name/title map         | Integer `active_gateway_count`                                 |
| `social-tracking`             | Full settings objects (6 platforms) | 6 booleans (`fb_pixel_enabled`, `ga_enabled`, etc.)            |
| `documentation-search-terms`  | Kept as-is                          | User confirmed this is intentional (used to find missing docs) |

### 5. Code Quality Fixes

-   Added `is_admin()` guard in analytics class constructor
-   Fixed `get_funnels_with_instant_layout()`: `posts_per_page => 1` + `found_posts` instead of `-1`
-   Replaced `gmdate` with `wp_date` in KPI method
-   Added daily transient throttle for state detection
-   Deactivation survey correctly configured as array-of-arrays in `set_entity()`

---

## All New Options Introduced By This PR

### Flag options (set by feature modules, read by detect_state_events) — 12 new rows:

| #   | Option key                          | Value type     | Set in (free plugin)                                | Set in (Pro plugin)                            |
| --- | ----------------------------------- | -------------- | --------------------------------------------------- | ---------------------------------------------- |
| 1   | `cf_first_checkout_configured`      | step ID (int)  | `admin-core/inc/meta-ops.php:267`                   | —                                              |
| 2   | `cf_first_template_imported`        | `true`         | `admin-core/ajax/importer.php:860,1002`             | —                                              |
| 3   | `cf_first_instant_layout_enabled`   | `true`         | `admin-core/ajax/flows.php:172`, `importer.php:671` | —                                              |
| 4   | `cf_first_webhook_configured`       | `true`         | `admin-core/api/webhooks.php:183`                   | —                                              |
| 5   | `cf_first_order_bump_created`       | `true`         | —                                                   | `admin-core/ajax/multiple-order-bump.php:720`  |
| 6   | `cf_first_upsell_accepted`          | `true`         | —                                                   | `modules/upsell/.../upsell-markup.php:138`     |
| 7   | `cf_first_downsell_accepted`        | `true`         | —                                                   | `modules/downsell/.../downsell-markup.php:131` |
| 8   | `cf_first_ab_test_started`          | `true`         | —                                                   | `modules/ab-test/.../ab-test-meta.php:452`     |
| 9   | `cf_first_ab_test_winner`           | `true`         | —                                                   | `modules/ab-test/.../ab-test-meta.php:583`     |
| 10  | `cf_tracked_version`                | version string | `class-cartflows-analytics.php:616`                 | —                                              |
| 11  | `cf_usage_installed_time`           | timestamp      | `class-cartflows-loader.php:690`                    | —                                              |
| 12  | `cf_weekly_report_notice_dismissed` | `true`         | `class-cartflows-admin-notices.php`                 | —                                              |

### Library-managed options (BSF_Analytics_Events internal, cannot be consolidated):

| #   | Option key                | Purpose                                                   |
| --- | ------------------------- | --------------------------------------------------------- |
| 13  | `cf_usage_events_pending` | Temporary event queue (cleared after each analytics send) |
| 14  | `cf_usage_events_pushed`  | Dedup array of event names already sent                   |

---

## Pending Task: Consolidate Flag Options

**Problem:** 12 separate `wp_options` rows for analytics flags.

**Proposed solution:** Merge all 12 into a single `cf_analytics_flags` option (associative array). Reduces 12 rows to 1.

**Files that need updating:**

-   `classes/class-cartflows-analytics.php` — change all `get_option('cf_first_*')` and `get_option('cf_tracked_version')` etc. to read from `cf_analytics_flags` array
-   `classes/class-cartflows-loader.php` — change `cf_usage_installed_time` setter
-   `classes/class-cartflows-admin-notices.php` — change `cf_weekly_report_notice_dismissed` setter
-   `admin-core/inc/meta-ops.php` — change `cf_first_checkout_configured` setter
-   `admin-core/ajax/importer.php` — change `cf_first_template_imported` + `cf_first_instant_layout_enabled` setters
-   `admin-core/ajax/flows.php` — change `cf_first_instant_layout_enabled` setter
-   `admin-core/api/webhooks.php` — change `cf_first_webhook_configured` setter
-   Pro `admin-core/ajax/multiple-order-bump.php` — change `cf_first_order_bump_created` setter
-   Pro `modules/upsell/.../upsell-markup.php` — change `cf_first_upsell_accepted` setter
-   Pro `modules/downsell/.../downsell-markup.php` — change `cf_first_downsell_accepted` setter
-   Pro `modules/ab-test/.../ab-test-meta.php` — change `cf_first_ab_test_started` + `cf_first_ab_test_winner` setters

**Pattern:**

```php
// Setter (in feature modules):
$flags = get_option( 'cf_analytics_flags', array() );
if ( empty( $flags['first_checkout_configured'] ) ) {
    $flags['first_checkout_configured'] = $post_id;
    update_option( 'cf_analytics_flags', $flags );
}

// Reader (in detect_state_events):
$flags = get_option( 'cf_analytics_flags', array() );
if ( ! empty( $flags['first_checkout_configured'] ) ) {
    $events->track( 'first_checkout_configured', strval( $flags['first_checkout_configured'] ), ... );
}
```

---

## Test Results (Dry Run — 2026-04-07)

**Environment:** wp-cartflows.test (Local by Flywheel) | **Result: PASS**

-   27/28 events fired correctly (only `paypal_gateway_enabled` absent — PayPal not enabled on dev site, expected)
-   All 8 new events confirmed: `tiktok_connected`, `pinterest_connected`, `gads_connected`, `snapchat_connected`, `first_downsell_accepted`, `first_instant_layout_enabled`, `first_store_checkout_set`, `first_webhook_configured`
-   KPI structure valid (2 days, `order_count` + `offer_orders`)
-   Zero PHP fatal errors or warnings
-   No analytics-related JS console errors
-   Full `bsf_core_stats` payload generated cleanly

---

## BSF Analytics Library

-   **Version:** 1.1.23 (latest, installed via Composer)
-   **Location:** `libraries/bsf-analytics/`
-   **Events class:** `class-bsf-analytics-events.php` — `BSF_Analytics_Events` with slug-based option storage, client-side dedup, `track()` / `flush_pending()` / `flush_pushed()` API
-   **Deactivation survey:** Correctly configured in `class-cartflows-loader.php:382` as array-of-arrays
