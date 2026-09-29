# Deployment Guide

CartFlows is deployed as a standard WordPress plugin. Build artifacts are committed to the repository, so no CI build step is required during deployment.

## Pre-deployment Checklist

Before deploying a new version, complete these steps:

### 1. Code Quality

```bash
# PHP standards
composer lint        # Must pass with 0 errors
composer phpstan     # Must pass with 0 errors
composer test        # All tests must pass

# JavaScript
npm run lint-js      # Must pass
npm run lint-css     # Must pass
npm run pretty       # Must pass
```

### 2. Build Assets

```bash
# Build all compiled assets
npm run all-builds
```

Verify the following files are updated in `admin-core/assets/build/`:
- `editor-app.js`
- `editor-app.css`
- `editor-app.asset.php`
- `settings-app.js`
- `settings-app.css`
- `settings-app.asset.php`

**These files must be committed to the repo before deployment.**

### 3. Version Bump

Update the version number in:

| File | Field |
|------|-------|
| `cartflows.php` | `Version:` plugin header |
| `cartflows.php` | `CARTFLOWS_VER` constant |
| `package.json` | `version` field |
| `readme.txt` | `Stable tag:` |

### 4. Changelog

Update `readme.txt` with the release changelog entry.

### 5. Testing

Run E2E tests against a staging environment:

```bash
npm run env:start
npm run test:e2e
npm run env:stop
```

Manually verify:
- [ ] Flow creation works
- [ ] Step creation (checkout, optin, landing, thank you)
- [ ] Checkout form submits correctly
- [ ] Order bump appears and processes
- [ ] Analytics dashboard loads
- [ ] Global settings save correctly
- [ ] Template import works

---

## Deployment Methods

### Method 1: Direct File Upload (FTP/SFTP)

1. Run `npm run all-builds` locally
2. Commit and push all changes including build assets
3. Upload the plugin directory to `wp-content/plugins/cartflows/` on the server
4. In WordPress admin: deactivate and reactivate the plugin if needed

### Method 2: WordPress Plugin Updater

CartFlows ships with a built-in update mechanism (`class-cartflows-update.php`) that checks for new versions from the CartFlows API. For self-hosted (pro) deployments:

1. Tag the release in git
2. Upload the plugin ZIP to the update server
3. WordPress installs will receive the update notification automatically

### Method 3: Deployment via CI/CD

Since build artifacts are committed, CI deployment simply needs to:

1. Checkout the repository
2. Rsync/deploy files to the server (no build step required)
3. Optionally run `composer install --no-dev` for production dependencies

```yaml
# Example CI step
- name: Deploy plugin
  run: |
    rsync -av --exclude='.git' --exclude='node_modules' --exclude='tests' \
      ./ user@server:/var/www/wp-content/plugins/cartflows/
```

---

## Post-deployment Verification

After deploying:

1. **Admin dashboard** — Verify CartFlows admin menu loads
2. **React apps** — Verify the flow editor and settings app load without console errors
3. **Checkout** — Complete a test purchase through a CartFlows funnel
4. **API** — Verify REST endpoints respond: `/wp-json/cartflows/v1/flows`

---

## Rollback Procedure

If a deployment causes issues:

1. Identify the last working git commit/tag
2. Deploy that version using the same method as above
3. If the database was migrated, check `class-cartflows-update.php` for rollback considerations
4. Deactivate and reactivate the plugin to re-run activation hooks

---

## Production Considerations

### PHP Configuration

Recommended server PHP settings:

| Setting | Minimum | Recommended |
|---------|---------|-------------|
| `max_execution_time` | 60 | 120 |
| `memory_limit` | 128M | 256M |
| `upload_max_filesize` | 32M | 64M |
| `post_max_size` | 32M | 64M |

### Asset Serving

Build assets in `admin-core/assets/build/` are static files. Configure your server/CDN to:
- Set long cache TTL for `.js` and `.css` files (they are versioned via asset.php)
- Serve with gzip/Brotli compression

### Database

CartFlows uses standard WordPress tables (`wp_posts`, `wp_postmeta`, `wp_options`). No custom tables are created. The main performance consideration is:
- Index on `wp_posts.post_type` (standard WP behaviour)
- `wp_postmeta` queries for flow/step settings (cached via object cache if available)

### Caching

CartFlows is compatible with standard WordPress caching plugins. If you use full-page caching:
- Exclude checkout pages (`wcf-step` post type with checkout type)
- Exclude cart and order confirmation pages from page caching

---

## Plugin Activation / Deactivation

### On Activation

`register_activation_hook( CARTFLOWS_FILE, ... )` handles:
- Creating default flows/steps if needed
- Setting default options
- Flushing rewrite rules (for CPT permalinks)

### On Deactivation

`register_deactivation_hook( CARTFLOWS_FILE, ... )` handles:
- Flushing rewrite rules
- Cleaning up transients

### On Uninstall

Uninstall logic (if defined) would remove plugin options and custom post type data.

---

## Related Pages

- [Build-System](Build-System)
- [Testing-Guide](Testing-Guide)
- [Environment-Configuration](Environment-Configuration)
- [Contributing-Guide](Contributing-Guide)
- [Troubleshooting-FAQ](Troubleshooting-FAQ)
