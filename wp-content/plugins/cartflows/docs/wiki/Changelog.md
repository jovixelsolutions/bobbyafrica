# Changelog

Recent release history for CartFlows. For the full changelog, see `readme.txt` in the plugin root.

---

## v2.2.1 — 2026-02-18 (current)

**Bug Fixes**
- Fixed capability check for admin operations (`cartflows_manage_flows_steps`)
- Fixed duplicate product being added to cart in certain flows (`CF-1141`)
- Fixed Tutor LMS product not supported in CartFlows checkout (`CF-955`)
- Fixed non-postname permalink structure support for flow/step URLs (#2033)

**Improvements**
- Added CartFlows pointer library for admin UI tooltips (`CF-1095`)
- Updated allowed pages for side popup display
- Updated asset files for release

**Commits in this release:**
- `85ce08242` — Fixed capability and updated asset files
- `b267670da` — Updated allowed pages for side popup
- `1c8664370` — Updated release date
- `844c52a3a` — Updated changelog, version, stable tag for release v2.2.1

---

## v2.2.0

**Features & Improvements**
- Various enhancements (see `readme.txt` for full v2.2.0 notes)

---

## Generating the Changelog

To generate a formatted changelog from git history:

```bash
# View recent commits grouped by type
git log --oneline --no-merges -50

# View commits since a specific tag
git log v2.2.0..HEAD --oneline --no-merges
```

The conventional commit format used by CartFlows maps to changelog sections:

| Commit prefix | Changelog section |
|--------------|------------------|
| `feat:` | Features |
| `fix:` | Bug Fixes |
| `chore:` | Internal / Dependencies |
| `refactor:` | Improvements |
| `docs:` | Documentation |
| `test:` | Testing |

---

## Version Numbers

CartFlows follows [Semantic Versioning](https://semver.org/):

- **Major** (`X.0.0`) — Breaking changes (rare)
- **Minor** (`2.X.0`) — New features, backward-compatible
- **Patch** (`2.2.X`) — Bug fixes, minor improvements

The version is defined in three places (all must be updated for a release):

| File | Field |
|------|-------|
| `cartflows.php` | `Version:` plugin header |
| `cartflows.php` | `CARTFLOWS_VER` constant |
| `package.json` | `version` field |
| `readme.txt` | `Stable tag:` |

---

## Related Pages

- [Deployment-Guide](Deployment-Guide)
- [Contributing-Guide](Contributing-Guide)
- [Home](Home)
