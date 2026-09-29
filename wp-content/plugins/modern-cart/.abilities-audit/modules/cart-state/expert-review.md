# Expert Review — Module: Cart State

---

## Candidate: `moderncart/get-cart-summary`

**Label:** Get Cart Summary
**Description:** Returns the current WooCommerce session cart state: item count, is_empty flag, cart totals (subtotal, discount, tax, shipping, grand total), applied coupons, and free shipping progress. Useful for verifying free shipping threshold configuration is working as expected.
**Category:** Analytics/Stats (read)
**Input:** none required (optional: `include_items` boolean to include full item list)
**Output:** is_empty, item_count, subtotal (float), discount_total (float), tax_total (float), shipping_total (float), grand_total (float), applied_coupons[], free_shipping { threshold, cart_total, remaining, percent, is_achieved }
**Permission:** manage_options
**Annotations:** readonly=true, idempotent=false (session-specific), destructive=false

### Expert 1 — UX/AI (12/18)
- ✅ Clear trigger: "Is the free shipping bar configured correctly?", "What's the admin cart state?"
- ⚠️ Session-specific: output depends on who's calling and what's in their cart — AI may get an empty cart
- ⚠️ For most admin AI use cases, the cart will be empty, limiting utility
- ✅ Free shipping progress data is highly useful for configuration verification
- ✅ No ambiguity in constructing the call (zero required input)
- Criterion scores: Trigger=2, Construction=3, Output=2, Labels=2, Scope=2, Safety=1 → 12

### Expert 2 — WordPress (13/18)
- ✅ manage_options correct for admin-side cart inspection
- ✅ WC()->cart is always available when WooCommerce is active
- ⚠️ Must guard against null cart (WC()->cart can be null in some contexts)
- ✅ Cart::get_free_shipping_amount() already handles empty cart (returns 0)
- ✅ All reads, no state changes
- Criterion scores: Capability=2, Standards=3, Edge=2, Sanitize=3, Performance=2, N+1=1 → 13

### Expert 3 — API Design (12/18)
- ✅ Clean output schema with nested objects (totals, free_shipping)
- ⚠️ idempotentHint must be false — different sessions yield different results
- ⚠️ Cross-session non-determinism means AI output from this ability can't be cached or repeated reliably
- ✅ The free_shipping sub-object is well-scoped and AI-friendly
- ✅ Composable with update-settings (verify config → adjust → re-verify)
- Criterion scores: InputMin=3, OutputClean=2, Atomic=2, Composable=2, Naming=2, Meta=1 → 12

**Average: 12.3/18 — BORDERLINE**
*Note: Most valuable for free shipping configuration verification. Best used in admin context where the developer can add a test product to their cart first.*

---

## Summary Table

| Ability | Score | Decision |
|---------|-------|----------|
| `moderncart/get-cart-summary` | 12.3 | ⚠️ Borderline |
