# CartFlows Free -- Abilities Audit Master Tracking

**Plugin:** CartFlows Free v2.2.2
**Audit started:** 2026-03-04
**Audit completed:** 2026-03-04

---

## Overall Progress

| Phase | Status |
|-------|--------|
| Phase 0: Discovery & Scoping | COMPLETE |
| Phase 1: Ability Mining | COMPLETE |
| Phase 2: Expert Review Panel | COMPLETE |
| Phase 3: User Selection | COMPLETE |
| Phase 4: Implementation | COMPLETE |

---

## Module Processing Order (Priority)

| # | Module | Priority | Mining | Review | Selection | Implementation |
|---|--------|----------|--------|--------|-----------|----------------|
| 1 | Flow (Funnel) | P0 | COMPLETE | COMPLETE | COMPLETE | COMPLETE |
| 2 | Step | P0 | COMPLETE | COMPLETE | COMPLETE | COMPLETE |
| 3 | Analytics/Stats | P0 | COMPLETE | COMPLETE | COMPLETE | COMPLETE |
| 4 | Admin/Settings | P1 | COMPLETE | COMPLETE | COMPLETE | COMPLETE |
| 5 | Checkout | P1 | COMPLETE | COMPLETE | COMPLETE | COMPLETE |
| 6 | Thank You | P1 | COMPLETE | COMPLETE | COMPLETE | COMPLETE |
| 7 | Optin | P1 | COMPLETE | COMPLETE | COMPLETE | COMPLETE |
| 8 | Landing | P1 | COMPLETE | COMPLETE | COMPLETE | COMPLETE |
| 9 | Email Report | P2 | COMPLETE | COMPLETE | COMPLETE | COMPLETE |
| 10 | Woo Dynamic Flow | P2 | COMPLETE | COMPLETE | COMPLETE | COMPLETE |

---

## Ability Count

| Status | Count |
|--------|-------|
| Mined | 42 |
| Passed review | 42 |
| User selected | 42 |
| Implemented | 42 |

---

## Implemented Abilities (by module)

### Flow / Step / Analytics (modules 1-3)
- `cartflows/list-flows`
- `cartflows/get-flow`
- `cartflows/get-step`
- `cartflows/get-flow-stats`
- `cartflows/publish-flow`
- `cartflows/unpublish-flow`
- `cartflows/clone-flow`
- `cartflows/trash-flow`
- `cartflows/restore-flow`
- `cartflows/update-flow`
- `cartflows/reorder-flow-steps`
- `cartflows/export-flow`
- `cartflows/clone-step`
- `cartflows/update-step-title`

### Admin / Settings (module 4) -- implemented 2026-03-04
- `cartflows/get-general-settings`
- `cartflows/get-store-checkout`
- `cartflows/get-permalink-settings`
- `cartflows/get-integration-settings`
- `cartflows/update-general-settings`
- `cartflows/update-permalink-settings`

### Checkout (module 5) -- implemented 2026-03-04
- `cartflows/get-checkout-settings` (read)
- `cartflows/get-checkout-products` (read)
- `cartflows/get-checkout-fields` (read)
- `cartflows/update-checkout-products` (write)
- `cartflows/update-checkout-layout` (write)
- `cartflows/update-checkout-place-order-button` (write)
- `cartflows/update-checkout-form-settings` (write)

### Thank You (module 6) -- implemented 2026-03-04
- `cartflows/get-thankyou-settings` (read)
- `cartflows/update-thankyou-layout` (write)
- `cartflows/update-thankyou-sections` (write)
- `cartflows/update-thankyou-redirect` (write)
- `cartflows/update-thankyou-custom-text` (write)

### Optin (module 7) -- implemented 2026-03-04
- `cartflows/get-optin-settings` (read)
- `cartflows/update-optin-product` (write)
- `cartflows/update-optin-button-text` (write)
- `cartflows/update-optin-pass-fields` (write)

### Landing (module 8) -- implemented 2026-03-04
- `cartflows/get-landing-settings` (read)

### Email Report (module 9) -- implemented 2026-03-04
- `cartflows/get-email-report-settings` (read)
- `cartflows/update-email-report-settings` (write)

### Woo Dynamic Flow (module 10) -- implemented 2026-03-04
- `cartflows/get-product-flow-mapping` (read)
- `cartflows/list-product-flow-mappings` (read)
- `cartflows/update-product-flow-mapping` (write)

---

## Implementation Files

- Config class: `cartflows/abilities/class-cartflows-ability-config.php`
- Runtime class: `cartflows/abilities/class-cartflows-ability-runtime.php`

---

## Notes

- Custom capability: `cartflows_manage_flows_steps` is used for all flow/step and settings read operations.
- Custom capability: `cartflows_manage_settings` is used for the settings AJAX handler (not used in abilities -- abilities use `cartflows_manage_flows_steps` for consistency).
- Write gate: `cartflows_abilities_api_write` option (boolean) must be enabled to call write abilities.
- WooCommerce dependency: analytics, checkout, thankyou, optin, and woo-dynamic-flow modules require WC active.
- Pro check: The plugin has `_is_cartflows_pro()` and `is_wcf_pro_plan()` functions for gating. Do NOT expose Pro abilities from Free.
- All 10 modules processed across 3 batches (P0, P1, P2). Audit is complete.
