# Starter Templates (Free) Plugin Release Process

You are orchestrating the Starter Templates (astra-sites) free plugin release process interactively. Follow each step below in order. At every step marked with **[ASK]**, stop and ask the user for confirmation or input using AskUserQuestion before proceeding. Never skip an **[ASK]** step.

Working directory: the plugin root (where package.json and astra-sites.php live).

**Error handling**: If any step fails, stop immediately, show the error output to the user, and **[ASK]** them to resolve it manually. Once they confirm the issue is fixed, continue with the remaining steps.

---

## Phase 0: Pre-flight Checks

1. Run `node -v` and `php -v` to verify environment. Report versions to the user.
2. Verify `jq`, `composer`, `grunt`, and `gh` CLI are available. If any are missing, tell the user and stop.
3. Read `package.json` to get the current version. Calculate the next patch version (e.g. 4.4.52 → 4.4.53).
4. Read `readme.txt` to get the current "Tested up to" value.

---

## Phase 1: Gather Inputs & Create Release Branch

**[ASK]** Ask the user:
- "What is the new version number?" — suggest the auto-incremented patch version as default
- "What is the WP 'Tested up to' version?" — show the current value from readme.txt as default
- "Have you finalized the changelog and run `grunt copy-inc` from astra-pro-sites?" — require confirmation before proceeding

After receiving all inputs:

1. Run `git checkout release-candidate` and `git pull origin release-candidate`
2. Create and switch to a new branch: `git checkout -b pre-release-<NEW_VERSION>`
3. Confirm to the user: "Created branch pre-release-<NEW_VERSION> from release-candidate."

---

## Phase 2: Build Steps

Run these steps sequentially, reporting progress after each. If any step fails, stop and **[ASK]** the user to resolve it before continuing.

1. **Update package.json & package-lock.json** — use `sed` to replace the old version string with the new one
2. **npm install** — run `npm install --legacy-peer-deps`
3. **Build React apps** — run `npm run build:onboarding` then `npm run build:preview`
4. **grunt version-bump** — run `npx grunt version-bump --ver=NEW_VERSION`
5. **grunt rtl** — run `npx grunt rtl`
6. **Update readme.txt** — use `sed` to update "Tested up to" to the WP version provided by the user, and "Stable tag" to NEW_VERSION
7. **grunt readme** — run `npx grunt readme` (converts readme.txt to README.md)
8. **PHPCBF** — run `composer run format` (exit code 1 means files were fixed, that's OK; exit code 2+ is an error)
9. **PHPCS** — run `composer run lint`
10. **PHPStan** — run `composer run phpstan`
11. **Update stubs** — run `composer run update-stubs`

After all build steps, show a summary of what succeeded and what failed.

---

## Phase 3: i18n

**[ASK]** Tell the user: "Build steps complete. The next step runs `bin/i18n.sh` which requires the OPENAI_API_KEY for GPT-PO translations. Please export it now (e.g. type `! export OPENAI_API_KEY=sk-...` in the prompt), then confirm here when ready."

Wait for confirmation, then:

1. Run `bash bin/i18n.sh`

If it fails, **[ASK]** the user to resolve and confirm before continuing.

---

## Phase 4: Stubs & Packaging

Run these steps sequentially:

1. **Build full package** — run `npm run build-package`
3. **Create release zip** — run `npx grunt release`

After packaging, show a summary of what succeeded.

**[ASK]** Ask: "Stubs updated and package built. Ready to proceed to commit and PR creation?"

---

## Phase 5: Commit, Push & PR

1. Run `git status` and `git diff --stat` to show all changes.

**[ASK]** Ask: "These are the files that will be committed. Proceed with commit?" (Yes / Review changes first / Abort)

If user says Yes:
2. Stage all changes: `git add -A`
3. Commit with message: `Chore: Release build for version NEW_VERSION`

**[ASK]** Ask: "Commit created. Push branch and create PR to release-candidate?" (Yes / Push only / Abort)

If user says Yes or Push only:
4. Run `git push -u origin pre-release-NEW_VERSION`

If user said Yes (create PR too):
5. **[ASK]** Ask: "Enter the issue number for the PR title (e.g. 1234):" — default to empty if none
6. Create PR using `gh pr create --base release-candidate --head pre-release-NEW_VERSION --title "#XXXX - Release Checklist vNEW_VERSION"` — if no issue number provided, use title `Release Checklist vNEW_VERSION` — with the standard release checklist body (include all checklist items with automated ones checked off). Use the checklist below:

```
### Description
- Final release PR for vNEW_VERSION. Please check all the checklists below before merging.
---
### Checklist:
- [ ] Finalize readme.txt
- [ ] Add doc links in the readme.txt
- [ ] Check if Gutenberg Blocks/Astra Notices library is updated to latest - Run `composer update`
- [x] Run `npm audit fix`
- [x] Run to produce rtl files : `grunt rtl`
- [ ] Run to update the Template Library    : `wp astra-sites sync --force`
- [ ] Run to test the CLI import    : `wp astra-sites import <template id>`
- [x] PHPCS - vendor/bin/phpcs
- [x] Run to update version number  : `grunt version-bump --ver=NEW_VERSION`
- [x] Update - Tested Upto - readme.txt
- [x] Update readme.txt file and then run: `grunt readme`
- [x] Generate POT file             : `grunt i18n`
- [x] Update stubs : `composer run update-stubs`
- [x] Build package : `npm run build-package`
- [ ] Add draft Release notes on github
- [ ] Add draft changelogs to wpastra.com
- [ ] Open PR to backport branch to `next-release`
```

7. Show the PR URL to the user.

---

## Phase 6: Summary

Print a final summary with:
- Version: OLD → NEW
- Branch: pre-release-NEW_VERSION
- All completed steps (with checkmarks)
- All skipped/failed steps (with warnings)
- Manual steps still required:
  - Finalize readme.txt
  - Add doc links in readme.txt
  - Update Template Library
  - Test CLI import
  - Check Gutenberg Blocks/Astra Notices library
  - Add draft Release notes on GitHub
  - Add draft changelogs to wpastra.com
  - Open PR to backport branch to next-release

---

## Important Rules

- The working directory is already the plugin root — do NOT prefix commands with `cd /path/to/plugin &&`. Just run commands directly.
- If any build step fails, report it and **[ASK]** the user to resolve before continuing — do NOT skip steps silently.
- Always show command output to the user so they can spot issues.
- Never force-push or use destructive git operations.
- Use `--legacy-peer-deps` flag for npm install.
- For grunt commands, use `npx grunt` to ensure the local version is used.
- `npm audit fix` may return non-zero — that's usually acceptable, report but continue.
- PHPCBF exit code 1 means files were auto-fixed (OK), exit code 2+ is an actual error.
