# Modern Cart Abilities — Overview

Modern Cart exposes a set of **abilities** — structured, permission-gated server-side operations — through the [WordPress Abilities API](https://developer.wordpress.org/apis/abilities/). Abilities are the primary integration surface for AI agents (e.g. via MCP) and programmatic tooling that needs to read or write Modern Cart configuration.

---

## What is an Ability?

An ability is a named, self-describing operation with:

- A **unique ID** (e.g. `moderncart/get-settings`)
- A **JSON Schema** input definition consumed by callers
- An **execute callback** that runs the operation and returns structured data
- A **permission callback** that enforces WordPress capability checks
- **MCP annotations** (`readOnlyHint`, `destructiveHint`, `idempotentHint`) that help AI agents reason about safety

---

## Architecture

### Class Hierarchy

```
Abstract_Ability  (inc/abilities/abstract-ability.php)
├── Settings\Get_Settings           (moderncart/get-settings)
├── Settings\Set_Settings           (moderncart/update-settings)
├── Settings\Get_Available_Options  (moderncart/get-available-options)
├── Settings\Reset_Settings         (moderncart/reset-settings)
├── Plugin\Get_Plugin_Status        (moderncart/get-plugin-status)
├── Plugin\Complete_Onboarding      (moderncart/complete-onboarding)
└── Cart\Get_Cart_Summary           (moderncart/get-cart-summary)
```

### `Abstract_Ability` Contract

Every ability must implement three methods:

| Method | Purpose |
|--------|---------|
| `configure()` | Set `$id`, `$label`, `$description`, `$capability`, `$is_destructive` |
| `get_input_schema()` | Return a JSON Schema array describing accepted parameters |
| `execute( $args )` | Run the operation; return a plain `array` on success or `WP_Error` on failure |

The base class provides:

- `handle_execute( $args )` — re-checks the user capability before delegating to `execute()`, and wraps PHP exceptions in `WP_Error`
- `get_final_input_schema()` — injects a `dry_run` boolean into the schema for any ability where `$is_destructive = true`
- `get_annotations()` — derives MCP hint booleans from `$is_destructive`
- `check_permission( $request )` — calls `current_user_can( $this->capability )`

### `Response` Helper

All ability `execute()` methods use `Response` for consistent output:

```php
// Success — return the data array directly
return Response::success( [ 'key' => 'value' ] );

// Failure — return a WP_Error
return Response::error( 'Human-readable message', 'error_code' );
```

`Response::success()` returns the data array unchanged. `Response::error()` wraps the message in a `WP_Error`, which the Abilities API converts to an appropriate error response for the caller.

---

## Registration

Abilities are registered in `Register_Abilities` (`inc/abilities/register-abilities.php`) on two WordPress actions:

| Action | Callback | Purpose |
|--------|----------|---------|
| `wp_abilities_api_categories_init` | `register_category()` | Registers the `moderncart` category |
| `wp_abilities_api_init` | `register_abilities()` | Instantiates and registers each ability class |

Each ability is passed to `wp_register_ability()` with:

```php
wp_register_ability( $ability->get_id(), [
    'label'               => $ability->get_label(),
    'description'         => $ability->get_description(),
    'category'            => $ability->get_category(),
    'input_schema'        => $ability->get_final_input_schema(),
    'execute_callback'    => [ $ability, 'handle_execute' ],
    'permission_callback' => [ $ability, 'check_permission' ],
    'meta'                => [
        'annotations'  => $ability->get_annotations(),
        'show_in_rest' => true,
        'mcp'          => [ 'public' => true, 'type' => 'tool' ],
    ],
] );
```

---

## Extending via Filter

Third-party plugins (including Modern Cart Pro) can register additional abilities using the `moderncart_abilities` filter:

```php
add_filter( 'moderncart_abilities', function ( $abilities ) {
    $abilities['my-ability'] = [
        'file'  => '/path/to/my-ability.php',
        'class' => 'MyPlugin\Inc\Abilities\My_Ability',
    ];
    return $abilities;
} );
```

**Security**: Only classes whose fully-qualified name begins with `ModernCart\Inc\Abilities\` or `ModernCartPro\Inc\Abilities\` are instantiated. All others are silently skipped.

---

## Setting Groups Reference

All settings-related abilities operate on one or more of these four WordPress option keys:

| Constant | Option Key | Purpose |
|----------|-----------|---------|
| `MODERNCART_MAIN_SETTINGS` | `moderncart_setting` | Plugin activation scope, AJAX, free shipping bar, express checkout |
| `MODERNCART_SETTINGS` | `moderncart_cart` | Cart style, theme, labels, coupon field, section styling |
| `MODERNCART_FLOATING_SETTINGS` | `moderncart_floating` | Floating cart position and colors |
| `MODERNCART_APPEARANCE_SETTINGS` | `moderncart_appearance` | Global colors, typography, header alignment |

---

## Abilities Index

| Ability ID | Category | Type | Page |
|-----------|----------|------|------|
| `moderncart/get-settings` | Settings | Read | [Ability-Get-Settings](Ability-Get-Settings) |
| `moderncart/update-settings` | Settings | Write | [Ability-Update-Settings](Ability-Update-Settings) |
| `moderncart/get-available-options` | Settings | Read | [Ability-Get-Available-Options](Ability-Get-Available-Options) |
| `moderncart/reset-settings` | Settings | Write (destructive) | [Ability-Reset-Settings](Ability-Reset-Settings) |
| `moderncart/get-plugin-status` | Plugin | Read | [Ability-Get-Plugin-Status](Ability-Get-Plugin-Status) |
| `moderncart/complete-onboarding` | Plugin | Write (idempotent) | [Ability-Complete-Onboarding](Ability-Complete-Onboarding) |
| `moderncart/get-cart-summary` | Cart | Read | [Ability-Get-Cart-Summary](Ability-Get-Cart-Summary) |

---

## Required Capability

All abilities require the WordPress `manage_options` capability (administrator). An ability whose `$capability` is not overridden will automatically reject requests from non-admin users with a `moderncart_ability_forbidden` error.

---

## Dry Run Support

Abilities marked `$is_destructive = true` automatically receive a `dry_run` parameter. When `dry_run: true` is passed, the ability simulates the operation and returns a preview without applying any changes. See individual ability pages for dry run response shapes.

**Destructive abilities:** `moderncart/update-settings`, `moderncart/reset-settings`
