# Expert Review — Module: Settings Extended

---

## Candidate: `moderncart/get-available-options`

**Label:** Get Available Setting Options
**Description:** Returns all valid enumerable choices for Modern Cart settings: cart styles, theme variants, image sizes, section styling modes, floating cart positions, coupon field modes, and text alignment values. Use this before calling update-settings to know which values are valid for enum-type fields.
**Category:** Configuration (read)
**Input:** none required (optional: `groups[]` to filter to specific setting groups)
**Output:** per-group objects with per-key arrays of valid `{ value, label }` choices
**Permission:** manage_options
**Annotations:** readonly=true, idempotent=true, destructive=false

### Expert 1 — UX/AI (15/18)
- ✅ Perfect companion to update-settings: "What cart styles can I choose?" → set one
- ✅ Prevents invalid values being set (cart_theme_style: 'style99' etc.)
- ✅ AI can discover valid enum values without hardcoding them
- ✅ Zero-input simplicity
- ✅ Output gives human-readable labels for each option (good for presenting choices)
- Criterion scores: Trigger=3, Construction=3, Output=3, Labels=3, Scope=2, Safety=1 → 15

### Expert 2 — WordPress (16/18)
- ✅ manage_options appropriate (admin-only configuration metadata)
- ✅ Data source: Helper::get_defaults(true) plus Settings_Fields::get_fields() for labels
- ✅ Purely read-only, zero risk
- ✅ Can be cached (static defaults array)
- Criterion scores: Capability=3, Standards=3, Edge=3, Sanitize=3, Performance=2, N+1=2 → 16

### Expert 3 — API Design (15/18)
- ✅ Fills a real gap: other abilities assume you know valid enum values, this teaches them
- ✅ Clean nested output per group per key
- ✅ idempotentHint=true correct (static data)
- ✅ Completes the settings trilogy: get-settings → get-available-options → update-settings
- Criterion scores: InputMin=3, OutputClean=3, Atomic=3, Composable=3, Naming=2, Meta=1 → 15

**Average: 15.3/18 — STRONG ✅**

---

## Summary Table

| Ability | Score | Decision |
|---------|-------|----------|
| `moderncart/get-available-options` | 15.3 | ✅ Strong |
