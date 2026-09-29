# HFE (UAE Lite) — Analytics Data Reference

## Static Data (sent every 2-day cycle)

| Key | Example Value | Source |
|---|---|---|
| `free_version` | `2.8.5` | `HFE_VER` constant |
| `pro_version` | `1.44.1` or `""` | `UAEL_VERSION` constant (empty if Pro not active) |
| `site_language` | `en_US` | `get_locale()` |
| `elementor_version` | `3.35.8` | `ELEMENTOR_VERSION` constant |
| `elementor_pro_version` | `""` | `ELEMENTOR_PRO_VERSION` constant |
| `onboarding_triggered` | `yes` / `no` | `hfe_onboarding_triggered` option |
| `uaelite_subscription` | `yes` / `no` | `uaelite_subscription` option |
| `active_theme` | `astra` | `get_template()` |
| `is_theme_supported` | `true` / `false` | `hfe_is_theme_supported` option |

## Numeric Values (sent every 2-day cycle)

| Key | Description |
|---|---|
| `total_hfe_templates` | Count of published `elementor-hf` posts |
| Per-widget usage counts | e.g. `hfe-nav-menu: 3`, `hfe-site-logo: 5` (from `uae_widgets_usage_data_option`) |

## KPI Records (last 2 days, per day)

| Key | Description |
|---|---|
| `total_templates` | Published template count on that date |
| `total_hfe_widget_instances` | Sum of all HFE widget instances across templates |
| `modified_templates` | Templates modified on that date |

## Learn Progress

| Key | Description |
|---|---|
| `learn_chapters_completed` | Array of chapter IDs completed by any user on the site |

## One-Time Milestone Events

Events are queued in `hfe_usage_events_pending` and flushed into the `events_record` key in the payload. After sending, event names move to `hfe_usage_events_pushed` for dedup. Each event fires only once per site.

| Event Name | Event Value | Properties | Trigger |
|---|---|---|---|
| `plugin_activated` | Plugin version (e.g. `2.8.5`) | `{"source": "self"}` or `{"source": "astra"}` | `register_activation_hook` |
| `onboarding_completed` | `""` | `{}` | State-detected when `hfe_onboarding_triggered = 'yes'` |
| `first_template_published` | Post ID (e.g. `123`) | `{"template_type": "type_header"}` | `transition_post_status` — first `elementor-hf` post published |
| `learn_completed` | `""` | `{}` | REST `update-learn-progress` — when ALL chapters and ALL steps are completed |
| `theme_compat_changed` | `"1"` or `"2"` | `{}` | `save_theme_compatibility_option` AJAX handler |

## Event Flow

```
1. Action triggers → HFE_Analytics_Events::track() → saved to hfe_usage_events_pending (wp_options)
2. Every 2 days → bsf_core_stats filter → flush_pending() → events added to payload as events_record
3. wp_remote_post → analytics.brainstormforce.com/api/analytics/ → ClickHouse
4. Event names moved to hfe_usage_events_pushed (dedup) → pending cleared
```

## Files

| File | Role |
|---|---|
| `inc/class-hfe-analytics-events.php` | Analytics Events class (track, flush, dedup) |
| `inc/class-hfe-analytics.php` | BSF Analytics integration, static data, event hooks |
| `header-footer-elementor.php` | `plugin_activated` event in activation hook |
| `admin/class-hfe-addons-actions.php` | `theme_compat_changed` event |
| `inc/class-hfe-learn-api.php` | `learn_completed` event |

## GitHub

- Issue: https://github.com/brainstormforce/header-footer-elementor/issues/1598
- PR: https://github.com/brainstormforce/header-footer-elementor/pull/1599
