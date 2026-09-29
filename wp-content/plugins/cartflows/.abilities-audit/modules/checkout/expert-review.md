# Checkout Module — Expert Review Panel

## Scoring Criteria (1-3 per criterion, max 18)
1. **Agent Utility** — How useful is this to an AI agent managing CartFlows?
2. **Input Clarity** — Is the input schema clear and unambiguous?
3. **Output Value** — Does the output provide actionable, structured data?
4. **Safety** — Risk level of the operation (3 = safe read, 1 = dangerous write)
5. **Scope** — Is it appropriately scoped (not too broad, not too narrow)?
6. **WordPress Alignment** — Does it follow WP/WC conventions?

**Auto-reject rule:** ANY expert giving 1 on ANY criterion = REJECTED.

---

## Candidate 1: get-checkout-settings (Read)

| Criterion | UX/AI Expert | WP Expert | API Design Expert | Avg |
|-----------|-------------|-----------|-------------------|-----|
| Agent Utility | 3 | 3 | 3 | 3.0 |
| Input Clarity | 3 | 3 | 3 | 3.0 |
| Output Value | 3 | 3 | 3 | 3.0 |
| Safety | 3 | 3 | 3 | 3.0 |
| Scope | 2 | 2 | 2 | 2.0 |
| WP Alignment | 3 | 3 | 3 | 3.0 |

**Total: 17/18** | **PASS**

**Notes:**
- UX/AI: Essential for understanding checkout configuration before making changes. First thing an agent needs.
- WP: Reads post meta directly, well-aligned with existing get-step pattern.
- API: Scope slightly broad (returns products + fields + design) but agent needs the full picture. Could be split but unified is more practical.

---

## Candidate 2: get-checkout-products (Read)

| Criterion | UX/AI Expert | WP Expert | API Design Expert | Avg |
|-----------|-------------|-----------|-------------------|-----|
| Agent Utility | 3 | 3 | 3 | 3.0 |
| Input Clarity | 3 | 3 | 3 | 3.0 |
| Output Value | 3 | 3 | 3 | 3.0 |
| Safety | 3 | 3 | 3 | 3.0 |
| Scope | 3 | 3 | 3 | 3.0 |
| WP Alignment | 3 | 3 | 3 | 3.0 |

**Total: 18/18** | **PASS**

**Notes:**
- UX/AI: Critical for agents. Products are the core of a checkout step. Knowing what products are configured lets the agent advise on pricing, discounts, and funnel optimization.
- WP: Direct post_meta read via `wcf-checkout-products`, perfectly aligned.
- API: Clean, focused scope. Single purpose, clear I/O.

---

## Candidate 3: get-checkout-fields (Read)

| Criterion | UX/AI Expert | WP Expert | API Design Expert | Avg |
|-----------|-------------|-----------|-------------------|-----|
| Agent Utility | 3 | 3 | 3 | 3.0 |
| Input Clarity | 3 | 3 | 3 | 3.0 |
| Output Value | 3 | 3 | 3 | 3.0 |
| Safety | 3 | 3 | 3 | 3.0 |
| Scope | 3 | 3 | 2 | 2.7 |
| WP Alignment | 3 | 3 | 3 | 3.0 |

**Total: 17.7/18** | **PASS**

**Notes:**
- UX/AI: Agents need to know what fields are shown on checkout to advise on UX, conversion, or troubleshoot "field missing" issues.
- WP: Uses existing `wcf_field_order_billing`/`shipping` meta, well aligned.
- API: Slightly broad (billing + shipping + form toggles) but all are co-located settings. The field_type filter keeps it manageable.

---

## Candidate 4: update-checkout-products (Write)

| Criterion | UX/AI Expert | WP Expert | API Design Expert | Avg |
|-----------|-------------|-----------|-------------------|-----|
| Agent Utility | 3 | 3 | 3 | 3.0 |
| Input Clarity | 2 | 2 | 2 | 2.0 |
| Output Value | 3 | 3 | 3 | 3.0 |
| Safety | 2 | 2 | 2 | 2.0 |
| Scope | 3 | 3 | 3 | 3.0 |
| WP Alignment | 3 | 3 | 3 | 3.0 |

**Total: 16/18** | **PASS**

**Notes:**
- UX/AI: Agents need to set up checkout products as part of funnel creation workflows. High utility.
- WP: Writes to `wcf-checkout-products` post_meta. Standard pattern.
- API: Input requires product array with nested objects. Complex but necessary. Schema must validate product IDs exist.
- Safety: Write operation but gated behind `cartflows_abilities_api_edit`. Replaces product list but is idempotent (same input = same output).

---

## Candidate 5: update-checkout-layout (Write)

| Criterion | UX/AI Expert | WP Expert | API Design Expert | Avg |
|-----------|-------------|-----------|-------------------|-----|
| Agent Utility | 3 | 3 | 3 | 3.0 |
| Input Clarity | 3 | 3 | 3 | 3.0 |
| Output Value | 3 | 3 | 3 | 3.0 |
| Safety | 3 | 3 | 3 | 3.0 |
| Scope | 3 | 3 | 3 | 3.0 |
| WP Alignment | 3 | 3 | 3 | 3.0 |

**Total: 18/18** | **PASS**

**Notes:**
- UX/AI: Simple, high-value action. Changing checkout layout is a common task.
- WP: Single post_meta update (`wcf-checkout-layout`). Very safe.
- API: Perfectly scoped. Enum-validated, single-purpose. Free layouts only (Pro layouts gated).

---

## Candidate 6: update-checkout-place-order-button (Write)

| Criterion | UX/AI Expert | WP Expert | API Design Expert | Avg |
|-----------|-------------|-----------|-------------------|-----|
| Agent Utility | 2 | 2 | 2 | 2.0 |
| Input Clarity | 3 | 3 | 3 | 3.0 |
| Output Value | 3 | 3 | 3 | 3.0 |
| Safety | 3 | 3 | 3 | 3.0 |
| Scope | 2 | 2 | 2 | 2.0 |
| WP Alignment | 3 | 3 | 3 | 3.0 |

**Total: 16/18** | **PASS**

**Notes:**
- UX/AI: Useful for CTA optimization. Agents can update button text for A/B testing, localization, or conversion optimization.
- Scope: Narrow but well-defined. Could be part of a broader "update-checkout-design" but keeping it separate is clearer for agents.

---

## Candidate 7: update-checkout-form-settings (Write)

| Criterion | UX/AI Expert | WP Expert | API Design Expert | Avg |
|-----------|-------------|-----------|-------------------|-----|
| Agent Utility | 3 | 3 | 3 | 3.0 |
| Input Clarity | 2 | 2 | 2 | 2.0 |
| Output Value | 3 | 3 | 3 | 3.0 |
| Safety | 2 | 2 | 3 | 2.3 |
| Scope | 2 | 2 | 2 | 2.0 |
| WP Alignment | 3 | 3 | 3 | 3.0 |

**Total: 15.3/18** | **PASS**

**Notes:**
- UX/AI: Agents need to toggle form settings (coupon, shipping, etc.) as part of checkout setup.
- Input: Many optional fields but all are simple yes/no toggles. Clear schema.
- Scope: Groups related toggles together. Splitting into 8 separate abilities would be worse.
- Safety: Multiple meta keys updated but all are toggle values. Gated behind write permission.

---

## Summary

| # | Candidate | Score | Status |
|---|-----------|-------|--------|
| 1 | get-checkout-settings | 17/18 | PASS |
| 2 | get-checkout-products | 18/18 | PASS |
| 3 | get-checkout-fields | 17.7/18 | PASS |
| 4 | update-checkout-products | 16/18 | PASS |
| 5 | update-checkout-layout | 18/18 | PASS |
| 6 | update-checkout-place-order-button | 16/18 | PASS |
| 7 | update-checkout-form-settings | 15.3/18 | PASS |

All 7 candidates pass. Proceeding to user selection.
