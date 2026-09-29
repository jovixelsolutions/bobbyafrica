# Modern Cart Settings Architecture Documentation

**Version**: 1.0.6
**Last Updated**: 2026-01-14
**Purpose**: Comprehensive settings storage, read/write paths, and AI integration guidelines

---

## Table of Contents

1. [Settings Storage Summary](#1-settings-storage-summary)
2. [Read Path Analysis](#2-read-path-analysis)
3. [Write Path Analysis](#3-write-path-analysis)
4. [Defaults & Backward Compatibility](#4-defaults--backward-compatibility)
5. [Canonical Settings Schema](#5-canonical-settings-schema)
6. [SSOT Helper Recommendation](#6-ssot-helper-recommendation)
7. [Risks & Edge Cases for AI Writes](#7-risks--edge-cases-for-ai-writes)
8. [Appendix: Key File References](#appendix-key-file-references)

---

## 1. Settings Storage Summary

### Storage Mechanism
- **Type**: WordPress `wp_options` table (standard site options, NOT network options)
- **Number of Options**: **4 separate option keys**
- **Serialization**: Each option stores a PHP array (serialized by WordPress)
- **No Custom Tables**: All settings use WordPress core options table

### Option Keys (Defined as Constants in `modern-cart.php`)

| Constant | Option Name | Purpose | Typical Keys Count |
|----------|-------------|---------|-------------------|
| `MODERNCART_MAIN_SETTINGS` | `moderncart_setting` | Main feature toggles (enable/disable, AJAX, free shipping bar, express checkout) | 5 |
| `MODERNCART_SETTINGS` | `moderncart_cart` | Cart behavior & content (style, text labels, animation, coupon field) | 15+ |
| `MODERNCART_FLOATING_SETTINGS` | `moderncart_floating` | Floating cart icon position and display | 1+ |
| `MODERNCART_APPEARANCE_SETTINGS` | `moderncart_appearance` | Visual styling (colors, fonts, alignment) | 5 |

### Non-Settings Options (Metadata)
- `moderncart_is_onboarding_complete` - String ('yes'/'no') - Plugin activation state
- `moderncart_version` - Array with 'current' and 'previous' keys - Version tracking
- **Transient**: `moderncart_redirect_to_onboarding` - Temporary onboarding redirect flag
- **Transient**: `moderncart_knowledge_base_data` - Cached API response (12 hour TTL)

### Storage Shape
Each option stores a **flat associative array** (NOT nested):
```php
[
    'enable_moderncart' => 'all',
    'enable_powered_by' => true,
    'enable_ajax_add_to_cart' => true,
    // ... more keys
]
```

---

## 2. Read Path Analysis

### Primary Read Method: `Helper::get_option( $option )`

**Location**: `inc/helper.php:193-217`

**Signature**:
```php
public function get_option( $option )
```

**Flow**:
1. Calls `get_option( $option, [] )` to retrieve DB value
2. Converts result to array via `Helper::convert_to_array()`
3. Retrieves defaults for this option key from `get_defaults()`
4. **CONDITIONAL LOGIC**: If Astra theme is active AND option is appearance/floating settings:
   - Merges Astra color variables into defaults (line 199-203)
5. Applies filter `moderncart_settings_db_values` with intersected keys (line 214)
6. Merges DB values with defaults using `wp_parse_args()`
7. Returns merged array

**Caching**:
- Static variable `$_defaults` in `get_defaults()` method (helper.php:164-181)
- Defaults are cached **per request** after first call
- No persistent cache (transients/object cache)

### Secondary Wrapper: `Cart::get_option( $option, $section, $default )`

**Location**: `inc/cart.php:34-53`

**Purpose**: Convenience wrapper for child classes (Scripts, Slide_Out, Floating)

**Signature**:
```php
public function get_option( $option, $section, $default = '' )
```

**Flow**:
1. Calls `Helper::get_instance()->get_option( $section )`
2. Checks if specific key exists in returned array
3. Returns key value OR provided default

**Inheritance Chain**:
```
Cart (base class)
├── Scripts extends Cart
├── Slide_Out extends Cart
│   └── Slide_Out_Ajax extends Slide_Out
└── Floating extends Cart
    └── Floating_Ajax extends Floating
```

### Direct `get_option()` Usage

**Only 2 locations bypass Helper**:
1. `inc/scripts.php:152` - Reads `MODERNCART_MAIN_SETTINGS` directly for localize script
   - **Reason**: Needs raw array structure, not merged with defaults
2. WooCommerce native options (`woocommerce_tax_total_display`, etc.) - Not plugin settings

### Filters Applied After Read

| Filter Name | Location | Purpose | Default Behavior |
|-------------|----------|---------|------------------|
| `moderncart_default_settings` | helper.php:158 | Modify defaults before merge | Returns defaults unchanged |
| `moderncart_settings_db_values` | helper.php:214 | Modify DB values pre-merge | Returns intersected DB values |
| `moderncart_override_is_global_enabled` | cart.php:105 | Override enable/disable logic | Returns null (no override) |

### Astra Theme Integration (Conditional Defaults)

**Trigger**: `get_template() === 'astra'` AND option is `MODERNCART_APPEARANCE_SETTINGS` or `MODERNCART_FLOATING_SETTINGS`

**Behavior** (helper.php:199-203):
- In **admin**: Uses actual Astra color values from `astra_get_option('global-color-palette')`
- On **frontend**: Uses CSS custom property references (`var(--ast-global-color-0)`)
- Affects 14 color keys (primary_color, heading_color, body_color, icon_color, etc.)

**Example Frontend Values**:
```php
[
    'primary_color' => 'var(--ast-global-color-0)',
    'heading_color' => 'var(--ast-global-color-1)',
    'body_color' => 'var(--ast-global-color-2)',
    // ... etc
]
```

---

## 3. Write Path Analysis

### Single Write Entry Point: `Admin_Menu::moderncart_update_settings()`

**Location**: `admin-core/admin-menu.php:200-247`

**Trigger**: AJAX action `wp_ajax_moderncart_update_settings`

**Flow**:
1. **Security**: `check_ajax_referer( 'moderncart_update_settings', 'security' )`
2. **Capability**: `current_user_can( 'manage_options' )`
3. **Accepts**: POST data with 1-4 option keys (JSON strings)
4. **Processing** (per key):
   - JSON decode → `json_decode( $_POST[$key], true )`
   - Sanitize → `Admin_Menu::sanitize_data( $key, $data )` (line 266-311)
   - Merge with existing → `wp_parse_args( $sanitized, $existing )`
   - Update → `update_option( $key, $merged_data )`
5. **Response**: JSON success/error

### Sanitization Logic: `Admin_Menu::sanitize_data( $key, $data )`

**Location**: `admin-core/admin-menu.php:281-311`

**Process**:
1. Fetches schema from `Helper::get_defaults( true )` (with 'type' field)
2. Iterates input data
3. Sanitizes per type:

| Type | Sanitization Function | Example Values |
|------|----------------------|----------------|
| `boolean` | `rest_sanitize_boolean()` | true, false |
| `number` | `absint()` | 300, 22, 20 |
| `hex` | `sanitize_hex_color()` | #0284C7, #1F2937 |
| `string` (default) | `sanitize_text_field()` | 'slideout', 'center' |

4. **Key filtering**: Only keys present in schema are saved (unknown keys ignored)
5. Returns sanitized array

**Code Reference**:
```php
foreach ( $data as $key => $value ) {
    if ( ! isset( $defaults[ $option_name ][ $key ] ) ) {
        continue; // Skip unknown keys
    }

    $type = $defaults[ $option_name ][ $key ]['type'] ?? 'string';

    switch ( $type ) {
        case 'boolean':
            $sanitized_data[ $key ] = rest_sanitize_boolean( $value );
            break;
        case 'number':
            $sanitized_data[ $key ] = absint( $value );
            break;
        case 'hex':
            $sanitized_data[ $key ] = sanitize_hex_color( $value );
            break;
        default:
            $sanitized_data[ $key ] = sanitize_text_field( $value );
    }
}
```

### Onboarding Write Path: `Admin_Menu::complete_onboarding()`

**Location**: `admin-core/admin-menu.php:357-437`

**Trigger**: AJAX action `wp_ajax_moderncart_complete_onboarding`

**Special Behavior**:
- Reads JSON from `php://input` (not `$_POST`)
- Maps onboarding keys to actual setting keys (line 475-557)
- Batch updates all 4 option keys
- Sets `moderncart_is_onboarding_complete` to 'yes'
- May install WordPress plugins via `Helper::install_wordpress_plugins()` (line 423)
- Sends user data to external webhook (line 412-420) for newsletter signup

### No REST API Endpoints

**Confirmed**: Plugin uses AJAX exclusively, no `register_rest_route()` calls exist

**Implications**:
- All writes require admin authentication
- No public API for settings modification
- AI/MCP integrations must use AJAX or direct DB writes

---

## 4. Defaults & Backward Compatibility

### Defaults Definition: `Helper::get_defaults( $with_schema = false )`

**Location**: `inc/helper.php:29-184`

**Structure**:
```php
[
    'moderncart_setting' => [
        'enable_moderncart' => [
            'value' => 'all',
            'type'  => 'string',
        ],
        // ...
    ],
    // ... other option keys
]
```

**When `$with_schema = false` (default)**:
- Strips 'type' field
- Returns only 'value' field as array value
- **Caches in static variable** `$_defaults` (line 164)

**When `$with_schema = true`**:
- Returns full structure with 'type' and 'value' fields
- Used by sanitization logic to determine type handling

### Default Values by Option Key

#### `MODERNCART_MAIN_SETTINGS` (5 keys)

| Key | Default | Type | Description |
|-----|---------|------|-------------|
| `enable_moderncart` | `'all'` | string | Enable cart globally. Enum: 'disabled', 'wc_pages', 'all' |
| `enable_powered_by` | `true` | boolean | Show "Powered by Modern Cart" branding |
| `enable_ajax_add_to_cart` | `true` | boolean | Enable AJAX add to cart functionality |
| `enable_free_shipping_bar` | `false` | boolean | Show free shipping progress bar |
| `enable_express_checkout` | `false` | boolean | Enable express checkout buttons (PayPal, Apple Pay, etc.) |

#### `MODERNCART_SETTINGS` (15 keys)

| Key | Default | Type | Description |
|-----|---------|------|-------------|
| `cart_style` | `'slideout'` | string | Cart display style. Enum: 'slideout', 'popup' (Pro) |
| `cart_theme_style` | `'style1'` | string | Theme variant. Enum: 'style1', 'style2' |
| `product_image_size` | `'medium'` | string | Product image size. Enum: 'small', 'medium', 'large' |
| `enable_coupon_field` | `'minimize'` | string | Coupon field display. Enum: 'disabled', 'minimize', 'traditional' |
| `cart_item_padding` | `20` | number | Padding around cart items in pixels |
| `animation_speed` | `300` | number | Animation duration in milliseconds |
| `section_styling` | `'accordian'` | string | Section display style (typo: should be 'accordion') |
| `main_title` | Translatable | string | Cart header title. Default: "Review Your Cart" |
| `recommendation_title` | Translatable | string | Recommendations section title |
| `empty_cart_recommendation_title` | Translatable | string | Empty cart recommendations title |
| `coupon_title` | Translatable | string | Coupon field label |
| `coupon_placeholder` | Translatable | string | Coupon input placeholder |
| `checkout_button_label` | Translatable | string | Checkout button text |
| `free_shipping_bar_text` | Translatable | string | Progress bar text. Supports `{amount}` placeholder |
| `free_shipping_success_text` | Translatable | string | Success message when threshold met |
| `on_sale_percentage_text` | Translatable | string | Discount label. Supports `{percent}` placeholder |

#### `MODERNCART_FLOATING_SETTINGS` (1+ keys)

| Key | Default | Type | Description |
|-----|---------|------|-------------|
| `floating_cart_position` | `'bottom-right'` | string | Icon position. Enum: 'bottom-left', 'bottom-right', 'disabled' |

#### `MODERNCART_APPEARANCE_SETTINGS` (5 keys)

| Key | Default | Type | Description |
|-----|---------|------|-------------|
| `primary_color` | `'#0284C7'` | hex | Primary brand color |
| `heading_color` | `'#1F2937'` | hex | Heading text color |
| `body_color` | `'#374151'` | hex | Body text color |
| `cart_header_text_alignment` | `'center'` | string | Header text alignment. Enum: 'left', 'center', 'right' |
| `cart_header_font_size` | `22` | number | Header font size in pixels |

### Backward Compatibility

**NO MIGRATION LOGIC FOUND** - Confirmed via grep for:
- `migration`, `backward`, `compat`, `upgrade`, `legacy` terms

**Version Tracking**:
- `Plugin_Loader::save_version_info()` stores version in `moderncart_version` option
- Tracks current and previous versions
- **NOT used for migrations** - no conditional logic based on version exists

**Settings Evolution Strategy**:
- **New keys**: Automatically appear via `wp_parse_args` merge with defaults
- **Removed keys**: Persist in DB (no cleanup) but ignored by schema validation
- **Type changes**: Would break (no casting/migration exists)
- **Renamed keys**: Would break (no mapping exists)

**Implications**: Settings schema must remain stable. Breaking changes require manual migration code.

---

## 5. Canonical Settings Schema

### Data Type Summary

| Type | Count | Sanitization | Example Keys |
|------|-------|--------------|--------------|
| `string` | 15 | `sanitize_text_field()` | cart_style, enable_moderncart, main_title |
| `boolean` | 4 | `rest_sanitize_boolean()` | enable_powered_by, enable_ajax_add_to_cart |
| `number` | 3 | `absint()` | cart_item_padding, animation_speed, cart_header_font_size |
| `hex` | 3 | `sanitize_hex_color()` | primary_color, heading_color, body_color |

### Hidden/Undocumented Settings

**Found in code but NOT in `Settings_Fields` UI**:

| Key | Option | Default | Type | Status |
|-----|--------|---------|------|--------|
| `slide_out_width_desktop` | MODERNCART_SETTINGS | 450 | number | Used in CSS vars |
| `slide_out_width_mobile` | MODERNCART_SETTINGS | 80 | number | Used in CSS vars |
| `enable_shipping` | MODERNCART_SETTINGS | true | boolean | Feature flag |
| `order_summary_style` | MODERNCART_SETTINGS | 'style1' | string | Visual variant |
| `empty_cart_button_text` | MODERNCART_SETTINGS | Translatable | string | Text label |
| `recommendation_types` | MODERNCART_SETTINGS | true | boolean/string | Pro feature |
| `empty_cart_recommendation` | MODERNCART_SETTINGS | 'disabled' | string | Pro feature |
| `display_floating_cart_icon` | MODERNCART_FLOATING_SETTINGS | true | boolean | Visibility toggle |
| `enable_floating_if_empty` | MODERNCART_FLOATING_SETTINGS | false | boolean | Empty cart behavior |
| `floating_cart_icon` | MODERNCART_FLOATING_SETTINGS | 0 | number | Icon index |

**Status**: These may be:
- Pro plugin features
- Legacy keys from older versions
- Programmatically set only (no UI control)
- Planned features not yet released

### Enum Values Reference

| Setting | Allowed Values | Default |
|---------|----------------|---------|
| `enable_moderncart` | `'disabled'`, `'wc_pages'`, `'all'` | `'all'` |
| `cart_style` | `'slideout'`, `'popup'` | `'slideout'` |
| `cart_theme_style` | `'style1'`, `'style2'` | `'style1'` |
| `product_image_size` | `'small'`, `'medium'`, `'large'` | `'medium'` |
| `enable_coupon_field` | `'disabled'`, `'minimize'`, `'traditional'` | `'minimize'` |
| `cart_header_text_alignment` | `'left'`, `'center'`, `'right'` | `'center'` |
| `floating_cart_position` | `'bottom-left'`, `'bottom-right'`, `'disabled'` | `'bottom-right'` |

**Note**: No runtime validation exists for enum values. Invalid values will be saved and may cause unexpected behavior.

---

## 6. SSOT Helper Recommendation

### Current State Assessment

#### ✅ STRENGTHS
1. **Helper class exists**: `inc/helper.php` already serves as central settings manager
2. **Single read method**: `Helper::get_option()` is de facto SSOT for reads
3. **Consistent defaults**: Schema with types exists in one location
4. **Filter integration**: Properly uses WordPress filter system
5. **Type safety**: Sanitization tied to schema type field

#### ❌ WEAKNESSES
1. **No write abstraction**: Writes only happen in Admin_Menu, tightly coupled to AJAX
2. **No validation layer**: Sanitization exists, but no semantic validation (e.g., "is 'style3' a valid cart_theme_style?")
3. **Scattered option key knowledge**: Constants used, but no central registry
4. **No bulk operations**: Cannot get/set multiple keys atomically
5. **No change tracking**: No way to detect what changed during an update
6. **No rollback mechanism**: Updates are immediate, no transaction safety

### Insertion Point for Enhanced SSOT

**RECOMMENDED APPROACH**: Extend `Helper` class with new methods

**Location**: `inc/helper.php`

**Proposed New Methods (Future Enhancement)**:
```php
// Cross-option key retrieval
Helper::get_setting( $key, $default = null )

// Bulk fetch all 4 options
Helper::get_all_settings()

// Get schema for specific key
Helper::get_setting_schema( $key )

// Semantic validation (check enum values, ranges)
Helper::validate_setting_value( $key, $value )

// Cross-option key update (with validation)
Helper::update_setting( $key, $value )

// Bulk update with transaction-like behavior
Helper::update_settings( $array )

// Reset option to defaults
Helper::reset_settings( $option_key )

// Utility: Map 'primary_color' → MODERNCART_APPEARANCE_SETTINGS
Helper::get_option_key_for_setting( $key )

// Utility: Diff detection for change tracking
Helper::get_changed_settings( $old, $new )
```

**Rationale**:
- Preserves existing `Helper::get_option()` public API (backwards compatible)
- Adds cross-option abstraction layer
- Enables AI tooling to work with logical keys ('primary_color') instead of option keys
- Provides validation layer missing from current implementation

### Existing Helper as SSOT

**VERDICT**: `Helper` class IS the current SSOT for:
- ✅ Defaults definition
- ✅ Read operations
- ✅ Schema storage
- ✅ Conditional logic (Astra theme)

**NOT SSOT for**:
- ❌ Write operations (handled by Admin_Menu)
- ❌ Validation rules (only sanitization exists)
- ❌ Cross-option key mapping

---

## 7. Risks & Edge Cases for AI Writes

### 🔴 CRITICAL RISKS

#### 1. Translatable String Overwrite
**Risk**: AI writes hardcoded English strings, breaking user translations

**Example**:
```php
// ❌ WRONG (AI writes)
[ 'main_title' => 'Review Your Cart' ]

// ✅ CORRECT (preserves i18n)
[ 'main_title' => __( 'Review Your Cart', 'modern-cart' ) ]
```

**Mitigation**: AI MUST NOT write to text label settings:
- `main_title`
- `recommendation_title`
- `empty_cart_recommendation_title`
- `coupon_title`
- `coupon_placeholder`
- `checkout_button_label`
- `free_shipping_bar_text`
- `free_shipping_success_text`
- `on_sale_percentage_text`

#### 2. Option Key Confusion
**Risk**: AI writes to wrong option key (e.g., `moderncart_cart` instead of `moderncart_setting`)

**Example**:
```php
// ❌ WRONG
update_option( 'moderncart_cart', [ 'enable_moderncart' => 'all' ] );

// ✅ CORRECT
update_option( 'moderncart_setting', [ 'enable_moderncart' => 'all' ] );
```

**Mapping**:
- `enable_moderncart` → `MODERNCART_MAIN_SETTINGS` (`moderncart_setting`)
- `primary_color` → `MODERNCART_APPEARANCE_SETTINGS` (`moderncart_appearance`)
- `cart_style` → `MODERNCART_SETTINGS` (`moderncart_cart`)
- `floating_cart_position` → `MODERNCART_FLOATING_SETTINGS` (`moderncart_floating`)

#### 3. Type Mismatch Corruption
**Risk**: AI writes string where boolean expected (or vice versa)

**Example**:
```php
// ❌ WRONG
[ 'enable_powered_by' => 'true' ]  // String, not boolean

// ✅ CORRECT
[ 'enable_powered_by' => true ]
```

**Type Validation Required**:
- Boolean: `true`/`false` (NOT `1`/`0`, `'true'`/`'false'`)
- Number: Positive integers only (absint enforces this)
- Hex: Must match `#[0-9A-Fa-f]{3,6}` pattern
- String: Any sanitized text

#### 4. Partial Update Overwrites
**Risk**: `update_option()` with partial array loses existing keys

**Example**:
```php
// ❌ WRONG (loses other 4 keys in moderncart_setting)
update_option( 'moderncart_setting', [ 'enable_moderncart' => 'disabled' ] );

// ✅ CORRECT (merge with existing)
$existing = get_option( 'moderncart_setting', [] );
$updated = wp_parse_args( [ 'enable_moderncart' => 'disabled' ], $existing );
update_option( 'moderncart_setting', $updated );
```

**Current code**: Admin_Menu DOES merge (line 268) ✅

#### 5. Astra Theme Color Injection
**Risk**: AI reads CSS variables (`var(--ast-global-color-0)`) and tries to save them

**Scenario**: User has Astra theme, AI reads `primary_color` from frontend context

**Example**:
```php
// ❌ WRONG (saves CSS var to DB)
[ 'primary_color' => 'var(--ast-global-color-0)' ]

// ✅ CORRECT (saves actual hex value)
[ 'primary_color' => '#0284C7' ]
```

**Mitigation**:
- AI must detect CSS variable pattern `var(--*)`
- Read from admin context to get resolved colors
- Use `Helper::get_compatible_colors()` to resolve Astra colors

### ⚠️ HIGH RISKS

#### 6. Invalid Enum Values
**Risk**: AI writes value not in allowed enum list

**Example**:
```php
// ❌ WRONG (not in options)
[ 'enable_moderncart' => 'only_homepage' ]

// ✅ CORRECT (must be one of)
[ 'enable_moderncart' => 'all' ] // OR 'wc_pages' OR 'disabled'
```

**Current Validation**: NONE - code does not reject invalid enum values ❌

**Allowed Enums** (see section 5 for complete list):
- `enable_moderncart`: 'disabled', 'wc_pages', 'all'
- `cart_theme_style`: 'style1', 'style2'
- `product_image_size`: 'small', 'medium', 'large'
- `enable_coupon_field`: 'disabled', 'minimize', 'traditional'
- `cart_header_text_alignment`: 'left', 'center', 'right'
- `floating_cart_position`: 'bottom-left', 'bottom-right', 'disabled'

#### 7. Pro Feature Lockout
**Risk**: AI enables Pro-only settings in free version

**Pro-Only Keys** (inferred from code):
- `cart_style` → `'popup'` (Pro only, free only supports 'slideout')
- `recommendation_types` → Upsells/cross-sells (Pro feature)
- `empty_cart_recommendation` → Advanced recommendations (Pro feature)

**Mitigation**:
- Check `Helper::get_pro_status()` before enabling Pro features
- Returns: 'not-installed', 'inactive', or 'active'
- Only enable Pro settings when status is 'active'

#### 8. Number Range Violations
**Risk**: AI writes nonsensical numeric values

**Example**:
```php
// ❌ WRONG (negative animation speed)
[ 'animation_speed' => -100 ]

// ❌ WRONG (font size too large)
[ 'cart_header_font_size' => 999 ]

// ✅ CORRECT (reasonable ranges)
[ 'animation_speed' => 300 ]      // 0-2000ms reasonable
[ 'cart_header_font_size' => 22 ] // 10-50px reasonable
```

**Current Validation**: `absint()` prevents negatives, but no max limits ⚠️

**Recommended Ranges**:
- `cart_item_padding`: 0-100px
- `animation_speed`: 100-2000ms
- `cart_header_font_size`: 10-50px

### 💡 MODERATE RISKS

#### 9. Settings Fields UI Mismatch
**Risk**: AI modifies undocumented settings that don't appear in UI

**Hidden Keys**: See section 5 (slide_out_width_desktop, recommendation_types, etc.)

**Impact**: User cannot see/verify AI changes in admin UI

**Mitigation**: AI should only modify documented settings with UI controls

#### 10. Onboarding Bypass
**Risk**: AI modifies settings before onboarding completes

**Check**: Must verify `get_option( 'moderncart_is_onboarding_complete' ) === 'yes'`

**Mitigation**: AI should respect onboarding flow and not skip it

#### 11. Filter Override Conflicts
**Risk**: AI writes value that gets immediately overridden by active filter

**Example**:
```php
// AI writes
update_option( 'moderncart_setting', [ 'enable_moderncart' => 'all' ] );

// But active theme has
add_filter( 'moderncart_settings_db_values', function( $values ) {
    $values['enable_moderncart'] = 'disabled';
    return $values;
});
```

**Mitigation**: AI should detect active filters on `moderncart_settings_db_values` and `moderncart_default_settings`

### ✅ SAFE OPERATIONS (AI-Friendly)

**These settings are SAFE for AI to modify**:
- ✅ Boolean feature flags (enable_free_shipping_bar, enable_ajax_add_to_cart)
- ✅ Color hex values (primary_color, heading_color, body_color) - if hex validated
- ✅ Numeric settings with absint() protection (cart_item_padding, animation_speed)
- ✅ Enum settings if AI validates against allowed list

**UNSAFE for AI (NEVER write)**:
- ❌ Text labels with translatable strings
- ❌ Settings without schema definition
- ❌ Settings dependent on external plugins (Pro features)
- ❌ Hidden/undocumented settings

---

## Appendix: Key File References

### Core Files

| Component | File Path | Key Lines | Description |
|-----------|-----------|-----------|-------------|
| Defaults Definition | `inc/helper.php` | 29-184 | Complete schema with types and defaults |
| Read Method | `inc/helper.php` | 193-217 | Primary get_option() implementation |
| Write AJAX Handler | `admin-core/admin-menu.php` | 200-247 | Update settings via AJAX |
| Sanitization Logic | `admin-core/admin-menu.php` | 281-311 | Type-based sanitization |
| Settings Schema (UI) | `admin-core/inc/settings-fields.php` | 96-391 | React admin field definitions |
| Wrapper Read Method | `inc/cart.php` | 34-53 | Convenience wrapper for subclasses |
| Constants Definition | `modern-cart.php` | 113-124 | Option key constants |
| Version Tracking | `plugin-loader.php` | 96-110 | Version storage (not used for migrations) |

### Usage Locations

| File | Read Calls | Write Calls | Description |
|------|-----------|-------------|-------------|
| `inc/scripts.php` | 11 | 0 | Reads for CSS vars and localized script |
| `inc/slide-out.php` | 10 | 0 | Reads for cart rendering |
| `inc/floating.php` | 3 | 0 | Reads for floating cart icon |
| `inc/cart.php` | 5 | 0 | Reads for common cart logic |
| `admin-core/admin-menu.php` | 1 | 2 | Reads for localize, writes via AJAX |

---

**AUDIT COMPLETED**: 2026-01-14
**Plugin Version Audited**: 1.0.6
**Total Settings Keys**: 25 (documented) + ~10 (undocumented/hidden)
**Option Keys**: 4 primary, 2 metadata
**Write Paths**: 1 AJAX endpoint (+ 1 onboarding endpoint)
**Read Paths**: 1 primary method + 1 wrapper
**Migration Logic**: None found

**For questions or updates to this documentation, reference the plugin version and commit hash.**
