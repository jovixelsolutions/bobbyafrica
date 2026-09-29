# Phase 3: User Selections — Flow / Step / Analytics Module

**Date:** 2026-03-04

## Selected (14 abilities)

| # | Ability | Score | Category |
|---|---------|-------|----------|
| 1 | `cartflows/list-flows` | 18/18 | CRUD / Read |
| 2 | `cartflows/get-flow` | 18/18 | CRUD / Read |
| 3 | `cartflows/get-step` | 18/18 | CRUD / Read |
| 4 | `cartflows/get-flow-stats` | 18/18 | Analytics / Stats |
| 5 | `cartflows/publish-flow` | 17/18 | Lifecycle |
| 6 | `cartflows/unpublish-flow` | 17/18 | Lifecycle |
| 7 | `cartflows/clone-flow` | 17.67/18 | Lifecycle |
| 8 | `cartflows/trash-flow` | 17/18 | Lifecycle |
| 9 | `cartflows/restore-flow` | 17/18 | Lifecycle |
| 10 | `cartflows/update-flow` | 17/18 | CRUD / Update |
| 11 | `cartflows/reorder-flow-steps` | 17.67/18 | Relationships |
| 12 | `cartflows/clone-step` | 17.33/18 | Lifecycle |
| 13 | `cartflows/update-step-title` | 16.33/18 | CRUD / Update |
| 14 | `cartflows/export-flow` | 15/18 | Output / Embedding |

## Not Selected (user explicitly excluded)

| Ability | Reason |
|---------|--------|
| `cartflows/delete-flow` | User skipped — destructive |
| `cartflows/delete-step` | User skipped — destructive |

## Implementation Target

- `cartflows/abilities/class-cartflows-ability-config.php`
- `cartflows/abilities/class-cartflows-ability-runtime.php`
- `cartflows/abilities/class-cartflows-abilities-loader.php`
- Hook wired in: `cartflows/classes/class-cartflows-loader.php` (inside `load_core_files()`)
