# Optin Module -- Expert Review

**Module:** Optin (Module 7)
**Date:** 2026-03-04
**Reviewers:** UX/AI Expert, WordPress Expert, API Design Expert

---

## Scoring Rubric (1-3 per criterion, max 18)
- Agent Utility, Input Clarity, Output Value, Safety, Scope, WordPress Alignment
- Auto-reject: Any criterion scored 1 by any expert.

---

## 1. get-optin-settings

| Criterion | UX/AI | WordPress | API Design | Notes |
|-----------|-------|-----------|------------|-------|
| Agent Utility | 3 | 3 | 3 | Essential for inspecting optin config before edits |
| Input Clarity | 3 | 3 | 3 | Single required step_id |
| Output Value | 3 | 3 | 3 | Returns product, button text, pass-fields, billing fields |
| Safety | 3 | 3 | 3 | Read-only, no side effects |
| Scope | 3 | 3 | 3 | Right granularity -- one step, all optin settings |
| WordPress Alignment | 3 | 3 | 3 | Uses existing meta getters |

**Scores:** UX/AI: 18, WordPress: 18, API Design: 18
**Final Score:** 18/18 -- EXCELLENT
**Verdict:** PASS

---

## 2. update-optin-product

| Criterion | UX/AI | WordPress | API Design | Notes |
|-----------|-------|-----------|------------|-------|
| Agent Utility | 3 | 3 | 3 | Agent can assign the free product for optin collection |
| Input Clarity | 3 | 3 | 3 | step_id + product_id, very clear |
| Output Value | 3 | 3 | 3 | Confirms assigned product with name/ID |
| Safety | 2 | 3 | 3 | Must validate product is simple, virtual, free |
| Scope | 3 | 3 | 3 | Appropriately scoped |
| WordPress Alignment | 3 | 3 | 3 | Standard meta update + WC product validation |

**Scores:** UX/AI: 17, WordPress: 18, API Design: 18
**Final Score:** 17/18 -- EXCELLENT
**Verdict:** PASS

---

## 3. update-optin-button-text

| Criterion | UX/AI | WordPress | API Design | Notes |
|-----------|-------|-----------|------------|-------|
| Agent Utility | 3 | 2 | 3 | Agent can customize CTA text |
| Input Clarity | 3 | 3 | 3 | step_id + text string |
| Output Value | 3 | 3 | 3 | Returns saved text |
| Safety | 3 | 3 | 3 | Non-destructive, text only |
| Scope | 2 | 2 | 2 | Very narrow -- single field update |
| WordPress Alignment | 3 | 3 | 3 | Standard meta update |

**Scores:** UX/AI: 17, WordPress: 16, API Design: 17
**Final Score:** 16/18 -- STRONG
**Verdict:** PASS

---

## 4. update-optin-pass-fields

| Criterion | UX/AI | WordPress | API Design | Notes |
|-----------|-------|-----------|------------|-------|
| Agent Utility | 3 | 2 | 3 | Agent can configure data passing between funnel steps |
| Input Clarity | 3 | 3 | 3 | step_id + enabled toggle + field names string |
| Output Value | 3 | 3 | 3 | Confirms pass-fields state |
| Safety | 3 | 3 | 3 | Non-destructive, reversible |
| Scope | 3 | 3 | 3 | Groups related toggle and field list |
| WordPress Alignment | 3 | 3 | 3 | Standard meta update |

**Scores:** UX/AI: 18, WordPress: 17, API Design: 18
**Final Score:** 17/18 -- EXCELLENT
**Verdict:** PASS

---

## Summary

| # | Ability | Score | Verdict |
|---|---------|-------|---------|
| 1 | get-optin-settings | 18/18 | PASS (Excellent) |
| 2 | update-optin-product | 17/18 | PASS (Excellent) |
| 3 | update-optin-button-text | 16/18 | PASS (Strong) |
| 4 | update-optin-pass-fields | 17/18 | PASS (Excellent) |

All 4 candidates passed. 0 rejected.
