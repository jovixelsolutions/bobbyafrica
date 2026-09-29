# Contributing Guide

This guide covers how to contribute to CartFlows — branch strategy, commit conventions, code review process, and the development workflow.

## Prerequisites

Before contributing, ensure you have:

- **PHP** 7.2 or higher
- **Node.js** and **npm** (check `package.json` engines field)
- **Composer**
- **Docker** (for wp-env E2E tests)
- **Git**
- **WordPress** local environment (wp-env or equivalent)

## Development Setup

```bash
# 1. Clone the repository
git clone <repo-url>
cd cartflows

# 2. Install PHP dependencies
composer install

# 3. Install JS dependencies (also installs pre-commit hook)
npm install

# 4. Start development watch mode
npm run start

# 5. (Optional) Start wp-env Docker environment for E2E tests
npm run env:start
```

## Branch Strategy

| Branch | Purpose |
|--------|---------|
| `master` | Production-ready code. All releases are tagged here. |
| `dev` | Main integration branch. PRs target this branch. |
| `feature/<name>` | Feature branches |
| `fix/<name>` | Bug fix branches |
| `chore/<name>` | Build, tooling, dependency updates |
| `docs/<name>` | Documentation-only changes |

### Workflow

1. Branch from `dev`
2. Implement your change
3. Run all linters and tests
4. Build assets: `npm run build`
5. Open PR targeting `dev`
6. After review and approval, merge via squash or merge commit
7. Periodically, `dev` is merged to `master` for releases

## Commit Message Format

CartFlows uses **Conventional Commits**:

```
<type>: <short imperative description>

[optional longer body]

[optional footer, e.g. Closes #123]
```

### Types

| Type | When to use |
|------|------------|
| `feat:` | A new feature |
| `fix:` | A bug fix |
| `docs:` | Documentation only |
| `chore:` | Build process, tooling, dependencies |
| `refactor:` | Code refactoring (no behaviour change) |
| `test:` | Adding or updating tests |
| `style:` | Formatting, whitespace only |

### Examples

```
feat: add A/B testing support for checkout steps
fix: fix order bump not appearing on mobile layouts
docs: document REST API endpoints
chore: update @wordpress/scripts to 19.2.2
refactor: move checkout field rendering to dedicated class
```

## Pre-commit Hook

`npm install` copies a pre-commit hook into `.git/hooks/`. This hook runs linting before every commit.

If the hook blocks your commit, fix the violations it reports — do **not** bypass with `--no-verify` unless explicitly approved.

## Before Opening a PR

Run the complete quality check suite:

```bash
# PHP
composer lint        # PHPCS check
composer phpstan     # Static analysis
composer test        # Unit tests

# JavaScript/CSS
npm run lint-js      # ESLint
npm run lint-css     # Stylelint
npm run pretty       # Prettier

# Build
npm run build        # Rebuild compiled assets
```

**Ensure build artifacts are committed.** If you changed any JS or SCSS files, run `npm run build` and include the updated files in `admin-core/assets/build/` in your PR.

## PR Description

Include in your PR description:
1. **What** — What was changed and why
2. **How to test** — Steps to verify the change manually
3. **Screenshots** — For UI changes, include before/after screenshots
4. **Related issues** — Link to any GitHub issues addressed

## Code Review Standards

Reviewers check for:

- [ ] WordPress coding standards compliance (PHPCS passes)
- [ ] PHPStan static analysis passes
- [ ] All tests pass
- [ ] Build artifacts updated (if JS/CSS changed)
- [ ] Security: nonces verified, inputs sanitized, outputs escaped
- [ ] No debug code (`console.log`, `var_dump`, `error_log`)
- [ ] No hardcoded strings (use `__()` / `_e()` for translatable text)
- [ ] Appropriate PHPDoc comments for new public methods

## Adding New Features

### Adding a PHP Module

1. Create directory: `modules/{feature-name}/`
2. Create main class: `modules/{feature-name}/class-cartflows-{feature}.php`
3. Follow the singleton or loader pattern used by existing modules
4. Register hooks in the constructor
5. Load from `Cartflows_Loader` (or a parent module loader)

### Adding a REST Endpoint

1. Create class in `admin-core/api/` extending `ApiBase`
2. Implement `register_routes()` method
3. Register in `admin-core/api/api-init.php`
4. Add nonce verification and capability checks

### Adding an AJAX Handler

1. Create class in `admin-core/ajax/` extending `AjaxBase`
2. Call `$this->init_ajax_events( ['action_name'] )` in constructor
3. Implement the `action_name()` method
4. Register in `admin-core/ajax/ajax-init.php`

### Adding a React Component

1. Create component file in the appropriate `admin-core/assets/src/` subdirectory
2. Follow existing component patterns (functional components + hooks)
3. Use Tailwind CSS for styling
4. Add to the appropriate app's component tree
5. Run `npm run build` before committing

## Internationalisation

All user-facing strings must be internationalised:

```php
// PHP
__( 'String to translate', 'cartflows' )
_e( 'String to echo', 'cartflows' )
```

```js
// JavaScript
import { __ } from '@wordpress/i18n';
__( 'String to translate', 'cartflows' )
```

Run `npm run i18n` to extract new strings.

## Libraries Directory

`libraries/` contains Composer-installed BSF packages (analytics, notices, nps-survey). These files are **committed** to the repo. Do not delete them.

## Related Pages

- [WordPress-Coding-Standards](WordPress-Coding-Standards)
- [Testing-Guide](Testing-Guide)
- [Build-System](Build-System)
- [Architecture-Overview](Architecture-Overview)
- [Deployment-Guide](Deployment-Guide)
