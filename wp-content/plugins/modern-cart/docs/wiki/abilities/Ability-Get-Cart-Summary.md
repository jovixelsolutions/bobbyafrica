# Ability: `moderncart/get-cart-summary`

Returns the current WooCommerce session cart state: item count, emptiness flag, order totals, applied coupons, and free shipping bar progress. Most useful for verifying free shipping threshold configuration — add a test product to the cart, then call this to confirm the threshold and progress values.

---

## Metadata

| Field | Value |
|-------|-------|
| **ID** | `moderncart/get-cart-summary` |
| **Category** | `moderncart` |
| **Label** | Get Cart Summary |
| **Capability** | `manage_options` |
| **Type** | Read-only |
| **Destructive** | No |
| **Idempotent** | No (session-specific — result varies per cart state) |
| **Source** | `inc/abilities/cart/get-cart-summary.php` |

---

## Parameters

None. This ability reads the current WooCommerce session cart and takes no input.

```json
{}
```

---

## Return Value

```json
{
  "is_empty": false,
  "item_count": 3,
  "totals": {
    "subtotal": 59.97,
    "discount_total": 10.00,
    "shipping_total": 5.99,
    "tax_total": 4.50,
    "grand_total": 60.46,
    "currency": "USD",
    "currency_symbol": "$"
  },
  "applied_coupons": ["SAVE10"],
  "free_shipping": {
    "is_enabled": true,
    "threshold": 75.00,
    "cart_total": 49.97,
    "remaining": 25.03,
    "percent": 67,
    "is_achieved": false
  }
}
```

### Field Reference

#### `is_empty` _(boolean)_

`true` if the WooCommerce cart contains no items.

#### `item_count` _(integer)_

Total number of items in the cart (sum of quantities across all line items).

#### `totals` _(object)_

All monetary values are raw floats rounded to 2 decimal places.

| Field | Source | Description |
|-------|--------|-------------|
| `subtotal` | `WC_Cart::get_displayed_subtotal()` | Cart subtotal before discounts and shipping |
| `discount_total` | `WC_Cart::get_discount_total()` | Total discount applied by coupons |
| `shipping_total` | `WC_Cart::get_shipping_total()` | Shipping cost |
| `tax_total` | `WC_Cart::get_taxes_total( false, false )` | Total taxes (excluding compound/shipping taxes) |
| `grand_total` | `WC_Cart::get_total( 'number' )` | Final order total |
| `currency` | `get_woocommerce_currency()` | ISO currency code (e.g. `USD`) |
| `currency_symbol` | `get_woocommerce_currency_symbol()` | Display symbol (e.g. `$`) |

#### `applied_coupons` _(array of strings)_

List of coupon codes currently applied to the cart. Empty array `[]` if no coupons are applied.

#### `free_shipping` _(object)_

Progress toward the free shipping threshold configured in Modern Cart settings.

| Field | Type | Description |
|-------|------|-------------|
| `is_enabled` | boolean | Whether the free shipping bar feature is enabled in settings |
| `threshold` | float | Minimum cart total required to unlock free shipping (0 if not configured) |
| `cart_total` | float | Current qualifying cart total: `subtotal - discount_total` (floored at 0) |
| `remaining` | float | Amount still needed to reach the threshold (0 when achieved) |
| `percent` | integer | Progress percentage (0–100), capped at 100 when achieved |
| `is_achieved` | boolean | `true` when `cart_total >= threshold` |

> **When `threshold` is 0**: All `free_shipping` fields except `is_enabled` remain at their zero/false defaults. No progress calculation is performed.

---

## Error Responses

| Error Code | Condition |
|-----------|-----------|
| `moderncart_cart_unavailable` | WooCommerce is not active, or `WC()->cart` is `null` (no session initialized) |
| `moderncart_ability_forbidden` | Current user lacks `manage_options` capability |

---

## Usage Examples

### Read current cart state

```json
{}
```

### Verify free shipping bar setup

1. Add a product to the WooCommerce cart via the storefront
2. Call `moderncart/get-cart-summary`
3. Check `free_shipping.threshold` matches your configured value
4. Verify `free_shipping.percent` and `free_shipping.remaining` are calculated correctly

---

## Notes on Session Context

Cart data is scoped to the **current authenticated session**. When called via an AI agent or MCP tool:

- The cart reflects the admin user's session
- This may differ from a customer's active cart
- The ability is most useful for verifying configuration (threshold, currency) rather than live customer cart monitoring

---

## Related Abilities

- [Ability-Get-Settings](Ability-Get-Settings) — check `enable_free_shipping_bar` setting
- [Ability-Update-Settings](Ability-Update-Settings) — enable/disable the free shipping bar
- [Abilities-Overview](Abilities-Overview) — full abilities index
