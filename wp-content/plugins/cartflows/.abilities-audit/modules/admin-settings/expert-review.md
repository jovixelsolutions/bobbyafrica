# Admin/Settings Module — Expert Review Panel

**Date:** 2026-03-04
**Candidates reviewed:** 6

---

## Review Cards

---

### cartflows/get-general-settings

**Label:** Get general settings
**Category:** Configuration
**Description:** Returns CartFlows general settings: default page builder, global checkout page, and display/override flags.

#### Expert Scores

| Criterion | UX/AI | WordPress | API Design |
|-----------|-------|-----------|------------|
| Agent Utility | 3 | 3 | 3 |
| Input Clarity | 3 | 3 | 3 |
| Output Value | 3 | 3 | 3 |
| Safety | 3 | 3 | 3 |
| Scope | 3 | 3 | 3 |
| WP Alignment | 3 | 3 | 3 |
| **Subtotal** | **18/18** | **18/18** | **18/18** |

**Average Score:** 18/18
**Verdict:** PASS — EXCELLENT
**Key Reasoning:** Zero-input read with clear, flat, actionable output. AI needs to know the active page builder before creating flows or diagnosing configuration issues. Perfect trigger scenario: "what page builder does this CartFlows site use?" Fully aligned with WP options API pattern.

---

### cartflows/get-permalink-settings

**Label:** Get permalink settings
**Category:** Configuration
**Description:** Returns the CartFlows URL slug configuration for flows and steps.

#### Expert Scores

| Criterion | UX/AI | WordPress | API Design |
|-----------|-------|-----------|------------|
| Agent Utility | 2 | 3 | 3 |
| Input Clarity | 3 | 3 | 3 |
| Output Value | 2 | 2 | 3 |
| Safety | 3 | 3 | 3 |
| Scope | 3 | 3 | 3 |
| WP Alignment | 3 | 3 | 3 |
| **Subtotal** | **16/18** | **17/18** | **18/18** |

**Average Score:** 17/18
**Verdict:** PASS — EXCELLENT
**Key Reasoning:** Clear trigger: AI troubleshooting broken funnel URLs needs to know the permalink base slugs. Zero-input read with well-defined small output. Marginally lower agent utility (fewer workflows need this), but strong overall.

---

### cartflows/get-integration-settings

**Label:** Get integration settings
**Category:** Configuration
**Description:** Returns the active pixel and analytics integration configurations (Facebook, Google Analytics, Google Ads, TikTok, Pinterest, Snapchat).

#### Expert Scores

| Criterion | UX/AI | WordPress | API Design |
|-----------|-------|-----------|------------|
| Agent Utility | 3 | 3 | 2 |
| Input Clarity | 3 | 3 | 3 |
| Output Value | 3 | 3 | 2 |
| Safety | 3 | 3 | 3 |
| Scope | 2 | 2 | 2 |
| WP Alignment | 3 | 3 | 3 |
| **Subtotal** | **17/18** | **17/18** | **15/18** |

**Average Score:** 16.3/18
**Verdict:** PASS — STRONG
**Key Reasoning:** The `integration` filter param makes this composable (AI can fetch only the Facebook settings if auditing a specific tracker). Scope concern: returning 6 sub-groups as one ability is slightly broad, but the enum filter mitigates this. The output for each sub-group is an opaque `object` — acceptable since the actual keys vary per integration and AI can inspect them. Clear trigger: "is Facebook Pixel configured on this store?"

---

### cartflows/update-general-settings

**Label:** Update general settings
**Category:** Configuration
**Description:** Updates CartFlows general settings: default page builder and global checkout page.

#### Expert Scores

| Criterion | UX/AI | WordPress | API Design |
|-----------|-------|-----------|------------|
| Agent Utility | 3 | 3 | 3 |
| Input Clarity | 2 | 3 | 3 |
| Output Value | 2 | 2 | 3 |
| Safety | 3 | 3 | 3 |
| Scope | 2 | 2 | 3 |
| WP Alignment | 3 | 3 | 3 |
| **Subtotal** | **15/18** | **16/18** | **18/18** |

**Average Score:** 16.3/18
**Verdict:** PASS — STRONG
**Key Reasoning:** Option gate protects write access. All-optional input (partial updates) is correct — AI only sends what it wants to change. Scope slightly broad (4 settings in one call) but this matches the actual `_cartflows_common` storage model; splitting would be artificial. Input clarity note: `global_checkout` is documented as a "Post ID" but typed as string — this is because the source stores it as string. Clear enough with the description.

---

### cartflows/get-store-checkout

**Label:** Get store checkout flow
**Category:** Configuration / Relationships
**Description:** Returns the ID and details of the flow configured as the CartFlows global store checkout.

#### Expert Scores

| Criterion | UX/AI | WordPress | API Design |
|-----------|-------|-----------|------------|
| Agent Utility | 3 | 3 | 3 |
| Input Clarity | 3 | 3 | 3 |
| Output Value | 3 | 3 | 3 |
| Safety | 3 | 3 | 3 |
| Scope | 3 | 3 | 3 |
| WP Alignment | 3 | 3 | 3 |
| **Subtotal** | **18/18** | **18/18** | **18/18** |

**Average Score:** 18/18
**Verdict:** PASS — EXCELLENT
**Key Reasoning:** The store checkout is a special singleton entity with a dedicated option (`_cartflows_store_checkout`). AI agents frequently need to know which flow is the store checkout before taking actions (e.g., the list-flows ability excludes it). Zero-input read, flat output with actionable `flow_id`, `url_edit`, and `is_configured` boolean. Perfect composability with other flow abilities.

---

### cartflows/update-permalink-settings

**Label:** Update permalink settings
**Category:** Configuration
**Description:** Updates CartFlows URL slug settings for flows and steps.

#### Expert Scores

| Criterion | UX/AI | WordPress | API Design |
|-----------|-------|-----------|------------|
| Agent Utility | 2 | 2 | 2 |
| Input Clarity | 2 | 3 | 3 |
| Output Value | 2 | 2 | 2 |
| Safety | 3 | 3 | 3 |
| Scope | 3 | 3 | 3 |
| WP Alignment | 3 | 3 | 3 |
| **Subtotal** | **15/18** | **16/18** | **16/18** |

**Average Score:** 15.7/18
**Verdict:** PASS — STRONG (flagged: limited AI trigger scenario)
**Key Reasoning:** Permalink changes are a valid setup operation — "configure CartFlows to use /funnel/ as the base slug". However, this is rarely something an AI agent would do autonomously; it's more of a one-time human setup step. The input is clear and the operation is safe. Included as STRONG, but noted as lower-utility for AI-driven workflows. Option gate required for the write.

---

## Final Summary

| Ability | Average | Verdict |
|---------|---------|---------|
| `cartflows/get-general-settings` | 18/18 | EXCELLENT |
| `cartflows/get-store-checkout` | 18/18 | EXCELLENT |
| `cartflows/get-permalink-settings` | 17/18 | EXCELLENT |
| `cartflows/get-integration-settings` | 16.3/18 | STRONG |
| `cartflows/update-general-settings` | 16.3/18 | STRONG |
| `cartflows/update-permalink-settings` | 15.7/18 | STRONG |

All 6 candidates pass. No auto-rejects (no criterion scored 1 by any expert).
