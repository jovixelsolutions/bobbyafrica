# Phase 2: Expert Review Panel — Flow / Step / Analytics Module

**Module:** Flow, Step, Analytics
**Abilities reviewed:** 12 candidates
**Scoring:** Each expert scores 6 criteria (1-3). Max per expert: 18. Max total: 54.
**Auto-reject:** Any single criterion scored 1 triggers rejection.

---

## Review Cards

---

### cartflows/list-flows
**Label:** List funnels
**Category:** CRUD / Read
**Description:** Returns a paginated list of CartFlows funnels with optional status, search, and date filters.

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
**Verdict:** PASS — Excellent
**Key Reasoning:** Core discovery ability; every agent workflow starts here. Minimal, clear input; structured output with pagination. `cartflows_manage_flows_steps` is correct.

---

### cartflows/get-flow
**Label:** Get funnel
**Category:** CRUD / Read
**Description:** Returns full data for a single CartFlows funnel by ID, including steps, settings, and permalink.

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
**Verdict:** PASS — Excellent
**Key Reasoning:** Core navigation ability; AI needs this to inspect funnel structure before taking any action. Single integer input is crystal clear; output provides steps, links, status.

---

### cartflows/update-flow
**Label:** Update funnel
**Category:** CRUD / Update
**Description:** Updates a CartFlows funnel's title and/or slug.

#### Expert Scores
| Criterion | UX/AI | WordPress | API Design |
|-----------|-------|-----------|------------|
| Agent Utility | 3 | 3 | 3 |
| Input Clarity | 3 | 3 | 3 |
| Output Value | 2 | 2 | 2 |
| Safety | 3 | 3 | 3 |
| Scope | 3 | 3 | 3 |
| WP Alignment | 3 | 3 | 3 |
| **Subtotal** | **17/18** | **17/18** | **17/18** |

**Average Score:** 17/18
**Verdict:** PASS — Excellent
**Key Reasoning:** Common "rename" operation. Output returns only success/message (lower value, but acceptable — the AI already has the new title from input). No safety concern.

---

### cartflows/publish-flow
**Label:** Publish funnel
**Category:** Lifecycle
**Description:** Sets a CartFlows funnel's status to published (active), publishing all steps too.

#### Expert Scores
| Criterion | UX/AI | WordPress | API Design |
|-----------|-------|-----------|------------|
| Agent Utility | 3 | 3 | 3 |
| Input Clarity | 3 | 3 | 3 |
| Output Value | 2 | 2 | 2 |
| Safety | 3 | 3 | 3 |
| Scope | 3 | 3 | 3 |
| WP Alignment | 3 | 3 | 3 |
| **Subtotal** | **17/18** | **17/18** | **17/18** |

**Average Score:** 17/18
**Verdict:** PASS — Excellent
**Key Reasoning:** Clear lifecycle trigger ("activate funnel for visitors"). Single integer input, idempotent. Atomic and composable.

---

### cartflows/unpublish-flow
**Label:** Unpublish funnel
**Category:** Lifecycle
**Description:** Sets a CartFlows funnel's status to draft (inactive), drafting all steps too.

#### Expert Scores
| Criterion | UX/AI | WordPress | API Design |
|-----------|-------|-----------|------------|
| Agent Utility | 3 | 3 | 3 |
| Input Clarity | 3 | 3 | 3 |
| Output Value | 2 | 2 | 2 |
| Safety | 3 | 3 | 3 |
| Scope | 3 | 3 | 3 |
| WP Alignment | 3 | 3 | 3 |
| **Subtotal** | **17/18** | **17/18** | **17/18** |

**Average Score:** 17/18
**Verdict:** PASS — Excellent
**Key Reasoning:** Pair of publish-flow; needed for "take down funnel" workflows. Idempotent, safe.

---

### cartflows/clone-flow
**Label:** Clone funnel
**Category:** Lifecycle
**Description:** Creates a full duplicate of a CartFlows funnel, including all steps and their settings.

#### Expert Scores
| Criterion | UX/AI | WordPress | API Design |
|-----------|-------|-----------|------------|
| Agent Utility | 3 | 3 | 3 |
| Input Clarity | 3 | 3 | 3 |
| Output Value | 3 | 3 | 3 |
| Safety | 3 | 3 | 3 |
| Scope | 3 | 3 | 3 |
| WP Alignment | 3 | 3 | 2 |
| **Subtotal** | **18/18** | **18/18** | **17/18** |

**Average Score:** 17.67/18
**Verdict:** PASS — Excellent
**Key Reasoning:** Very high AI utility ("copy this funnel as a template for a new campaign"). Output returns new funnel ID. The WP alignment note: direct SQL for meta cloning is acceptable but unconventional — not a design concern for the ability itself.

---

### cartflows/trash-flow
**Label:** Trash funnel
**Category:** Lifecycle
**Description:** Moves a CartFlows funnel and all its steps to trash.

#### Expert Scores
| Criterion | UX/AI | WordPress | API Design |
|-----------|-------|-----------|------------|
| Agent Utility | 3 | 3 | 3 |
| Input Clarity | 3 | 3 | 3 |
| Output Value | 2 | 2 | 2 |
| Safety | 3 | 3 | 3 |
| Scope | 3 | 3 | 3 |
| WP Alignment | 3 | 3 | 3 |
| **Subtotal** | **17/18** | **17/18** | **17/18** |

**Average Score:** 17/18
**Verdict:** PASS — Excellent
**Key Reasoning:** Soft-delete is the safe default. Clear trigger ("remove this funnel"). Recoverable via restore-flow.

---

### cartflows/restore-flow
**Label:** Restore funnel from trash
**Category:** Lifecycle
**Description:** Restores a trashed CartFlows funnel and all its steps.

#### Expert Scores
| Criterion | UX/AI | WordPress | API Design |
|-----------|-------|-----------|------------|
| Agent Utility | 3 | 3 | 3 |
| Input Clarity | 3 | 3 | 3 |
| Output Value | 2 | 2 | 2 |
| Safety | 3 | 3 | 3 |
| Scope | 3 | 3 | 3 |
| WP Alignment | 3 | 3 | 3 |
| **Subtotal** | **17/18** | **17/18** | **17/18** |

**Average Score:** 17/18
**Verdict:** PASS — Excellent
**Key Reasoning:** Natural pair of trash-flow. "Undo trash" is a clear and expected operation.

---

### cartflows/delete-flow
**Label:** Permanently delete funnel
**Category:** CRUD / Delete
**Description:** Permanently deletes a CartFlows funnel and all its steps. Cannot be undone.

#### Expert Scores
| Criterion | UX/AI | WordPress | API Design |
|-----------|-------|-----------|------------|
| Agent Utility | 2 | 3 | 2 |
| Input Clarity | 3 | 3 | 3 |
| Output Value | 2 | 2 | 2 |
| Safety | 3 | 3 | 3 |
| Scope | 3 | 3 | 3 |
| WP Alignment | 3 | 3 | 3 |
| **Subtotal** | **16/18** | **17/18** | **16/18** |

**Average Score:** 16.33/18
**Verdict:** PASS — Strong (flagged: AI should prefer trash-flow)
**Key Reasoning:** Destructive but necessary. Must be gated behind an option. The description clearly warns "cannot be undone". Output value limited (boolean success). Flag in description that AI should prefer trash unless permanent deletion is explicitly confirmed.

---

### cartflows/reorder-flow-steps
**Label:** Reorder funnel steps
**Category:** Relationships
**Description:** Changes the order of steps within a CartFlows funnel.

#### Expert Scores
| Criterion | UX/AI | WordPress | API Design |
|-----------|-------|-----------|------------|
| Agent Utility | 2 | 3 | 3 |
| Input Clarity | 3 | 3 | 3 |
| Output Value | 3 | 3 | 3 |
| Safety | 3 | 3 | 3 |
| Scope | 3 | 3 | 3 |
| WP Alignment | 3 | 3 | 3 |
| **Subtotal** | **17/18** | **18/18** | **18/18** |

**Average Score:** 17.67/18
**Verdict:** PASS — Excellent
**Key Reasoning:** Clear use case: "move the checkout step before the landing page". Requires the agent to first call get-flow to know current step IDs, making it composable. Input is clear ordered array.

---

### cartflows/export-flow
**Label:** Export funnel data
**Category:** Output/Embedding
**Description:** Exports one or more CartFlows funnels as JSON.

#### Expert Scores
| Criterion | UX/AI | WordPress | API Design |
|-----------|-------|-----------|------------|
| Agent Utility | 2 | 2 | 2 |
| Input Clarity | 3 | 3 | 3 |
| Output Value | 2 | 2 | 2 |
| Safety | 3 | 3 | 3 |
| Scope | 2 | 2 | 2 |
| WP Alignment | 3 | 3 | 3 |
| **Subtotal** | **15/18** | **15/18** | **15/18** |

**Average Score:** 15/18
**Verdict:** PASS — Strong (flagged: limited AI utility; export is more for human backup)
**Key Reasoning:** Returns JSON blob of flow data. Output is a string (double-encoded JSON), which is less composable. Useful for backup workflows, but AI rarely needs to act on the exported JSON.

---

### cartflows/get-step
**Label:** Get step
**Category:** CRUD / Read
**Description:** Returns full data for a single CartFlows step by ID.

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
**Verdict:** PASS — Excellent
**Key Reasoning:** Essential read operation; AI needs this to inspect a step before editing, cloning, or building context. Returns type, links, settings in one call.

---

### cartflows/clone-step
**Label:** Clone step
**Category:** Lifecycle
**Description:** Creates a duplicate of a CartFlows step within the same funnel.

#### Expert Scores
| Criterion | UX/AI | WordPress | API Design |
|-----------|-------|-----------|------------|
| Agent Utility | 2 | 3 | 3 |
| Input Clarity | 3 | 3 | 3 |
| Output Value | 3 | 2 | 3 |
| Safety | 3 | 3 | 3 |
| Scope | 3 | 3 | 3 |
| WP Alignment | 3 | 3 | 3 |
| **Subtotal** | **17/18** | **17/18** | **18/18** |

**Average Score:** 17.33/18
**Verdict:** PASS — Excellent
**Key Reasoning:** Useful for "create a variation of this checkout step". Input requires both flow_id and step_id which is clear. Output returns new step ID.

---

### cartflows/delete-step
**Label:** Permanently delete step
**Category:** CRUD / Delete
**Description:** Permanently deletes a step from a CartFlows funnel. Cannot be undone.

#### Expert Scores
| Criterion | UX/AI | WordPress | API Design |
|-----------|-------|-----------|------------|
| Agent Utility | 2 | 2 | 2 |
| Input Clarity | 3 | 3 | 3 |
| Output Value | 2 | 2 | 2 |
| Safety | 3 | 3 | 3 |
| Scope | 3 | 3 | 3 |
| WP Alignment | 3 | 3 | 3 |
| **Subtotal** | **16/18** | **16/18** | **16/18** |

**Average Score:** 16/18
**Verdict:** PASS — Strong (flagged: destructive; must be option-gated)
**Key Reasoning:** Necessary for cleanup workflows. Requires both flow_id and step_id which is a good safety pattern. Must be option-gated.

---

### cartflows/update-step-title
**Label:** Update step title
**Category:** CRUD / Update
**Description:** Renames a CartFlows step.

#### Expert Scores
| Criterion | UX/AI | WordPress | API Design |
|-----------|-------|-----------|------------|
| Agent Utility | 2 | 3 | 2 |
| Input Clarity | 3 | 3 | 3 |
| Output Value | 2 | 2 | 2 |
| Safety | 3 | 3 | 3 |
| Scope | 3 | 3 | 3 |
| WP Alignment | 3 | 3 | 3 |
| **Subtotal** | **16/18** | **17/18** | **16/18** |

**Average Score:** 16.33/18
**Verdict:** PASS — Strong
**Key Reasoning:** Simple rename; useful when AI is setting up a new funnel. Moderate AI utility (agents mostly need to rename during initial setup).

---

### cartflows/get-flow-stats
**Label:** Get funnel analytics
**Category:** Analytics/Stats
**Description:** Returns revenue and order analytics for CartFlows funnels within a date range.

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
**Verdict:** PASS — Excellent
**Key Reasoning:** High-value analytics ability; AI can answer "how much revenue did this funnel generate last month?" or "what were our top-performing funnels this week?". Date-range input is clear and well-typed.

---

## Summary Table

| Ability | Avg Score | Verdict |
|---------|-----------|---------|
| cartflows/list-flows | 18/18 | PASS — Excellent |
| cartflows/get-flow | 18/18 | PASS — Excellent |
| cartflows/update-flow | 17/18 | PASS — Excellent |
| cartflows/publish-flow | 17/18 | PASS — Excellent |
| cartflows/unpublish-flow | 17/18 | PASS — Excellent |
| cartflows/clone-flow | 17.67/18 | PASS — Excellent |
| cartflows/trash-flow | 17/18 | PASS — Excellent |
| cartflows/restore-flow | 17/18 | PASS — Excellent |
| cartflows/delete-flow | 16.33/18 | PASS — Strong (destructive) |
| cartflows/reorder-flow-steps | 17.67/18 | PASS — Excellent |
| cartflows/export-flow | 15/18 | PASS — Strong |
| cartflows/get-step | 18/18 | PASS — Excellent |
| cartflows/clone-step | 17.33/18 | PASS — Excellent |
| cartflows/delete-step | 16/18 | PASS — Strong (destructive) |
| cartflows/update-step-title | 16.33/18 | PASS — Strong |
| cartflows/get-flow-stats | 18/18 | PASS — Excellent |

**All 16 abilities passed. No auto-rejects.**

---

## Phase 2 Status: COMPLETE
