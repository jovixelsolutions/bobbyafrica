# Woo Dynamic Flow Module -- User Selections

**Date:** 2026-03-04
**Module:** Woo Dynamic Flow (Module 10)

## Abilities Presented

| # | Ability | Score | Type | User Decision |
|---|---------|-------|------|---------------|
| 1 | `get-product-flow-mapping` | 18/18 | Read | APPROVED |
| 2 | `list-product-flow-mappings` | 17/18 | Read | APPROVED |
| 3 | `update-product-flow-mapping` | 17/18 | Write | APPROVED |

## Notes

- All 3 abilities were approved by the user.
- All require WooCommerce to be active; runtime methods check for WC and throw if absent.
- The module connects WooCommerce products to CartFlows funnels via product meta.
- Write ability validates that the target flow exists before saving.
