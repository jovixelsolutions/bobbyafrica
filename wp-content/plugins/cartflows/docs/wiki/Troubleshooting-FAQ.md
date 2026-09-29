# Troubleshooting & FAQ

Common issues, gotchas, and their solutions for CartFlows development and deployment.

---

## Development Gotchas

### 1. Built assets are committed — always rebuild before committing

**Issue:** React JS/CSS changes look fine locally but aren't reflected after deployment.

**Cause:** `admin-core/assets/build/` is tracked in git. If you change source files under `admin-core/assets/src/` but don't rebuild, the deployed code will have stale compiled assets.

**Fix:**
```bash
npm run build
git add admin-core/assets/build/
```

Always run `npm run build` before committing any JS or SCSS changes.

---

### 2. Two webpack configs — use `npm run all-builds` for full builds

**Issue:** Changes to the setup wizard don't appear after running `npm run build`.

**Cause:** There are two separate webpack configurations:
- Main apps (editor + settings): default `wp-scripts` config
- Setup wizard: `wizard-webpack-config.js`

`npm run build` only builds the main apps.

**Fix:**
```bash
npm run all-builds   # Builds all three (editor, settings, wizard)
# or
npm run wizard-build # Builds the wizard only
```

---

### 3. RTL CSS files were intentionally removed

**Issue:** Build errors or missing RTL file warnings.

**Cause:** `editor-app-rtl.css` and `settings-app-rtl.css` were removed from the build output. These files no longer exist and should not be referenced.

**Fix:** Do not re-add RTL CSS references unless this is an intentional feature addition approved by the team.

---

### 4. `wc_clean` as a sanitizer

**Issue:** PHPCS reports `wc_clean()` as an unrecognized sanitizer.

**Cause:** If running PHPCS without the project's `phpcs.xml.dist` config (e.g., specifying a different config file).

**Fix:** Always run PHPCS using the project's config:
```bash
composer lint
# or
vendor/bin/phpcs --standard=phpcs.xml.dist
```

`wc_clean` is registered as a custom sanitizer in `phpcs.xml.dist`.

---

### 5. Libraries directory — do not delete

**Issue:** Plugin errors after running `composer install --no-dev` or manual cleanup.

**Cause:** `libraries/` contains Composer-installed BSF packages (analytics, notices, nps-survey) that are **committed** to the repo. They may appear as "unused" to some tools.

**Fix:** Never delete `libraries/`. It is a required directory containing vendored packages.

---

### 6. Pre-commit hook blocking commits

**Issue:** `git commit` fails with linting errors.

**Cause:** `npm install` installs a git pre-commit hook in `.git/hooks/pre-commit` that runs linting before every commit.

**Fix:**
```bash
# Fix the reported violations, then commit again
composer lint
npm run lint-js
# After fixing:
git commit -m "fix: resolve linting violations"
```

Only bypass with `--no-verify` if explicitly approved:
```bash
git commit --no-verify -m "..."  # Only with team approval
```

---

### 7. PHPStan stubs out of date

**Issue:** PHPStan reports false positives for CartFlows classes after significant class changes.

**Cause:** The stubs at `tests/php/stubs/cf-stubs.php` reflect an older class structure.

**Fix:**
```bash
composer update-stubs
```

---

## Common Plugin Issues

### Checkout page shows default WooCommerce layout

**Cause:** The step is not correctly assigned to a flow, or the CartFlows frontend is not loading.

**Checks:**
1. Verify the page/post is assigned as a `wcf-step` within a `wcf-flow`
2. Check `Appearance → Themes` — some themes override templates in ways that conflict with CartFlows
3. Check for JavaScript errors in the browser console
4. Ensure WooCommerce is active and up to date

---

### Admin page shows blank or broken React app

**Cause:** JS bundle loading error, usually due to:
- Missing build files
- Conflicting JavaScript from another plugin
- Browser console JavaScript error

**Checks:**
1. Open browser DevTools → Console tab and look for JS errors
2. Verify `admin-core/assets/build/editor-app.js` exists
3. Disable other plugins to check for conflicts
4. Try a different browser or incognito mode

---

### AJAX actions not working (403 or empty response)

**Cause:** Nonce verification failing.

**Checks:**
1. Ensure the nonce is being sent with the AJAX request
2. Check if any security plugin is blocking `admin-ajax.php`
3. Verify the nonce was generated with the correct action name
4. Check server logs for PHP errors

---

### REST API returning 401 Unauthorized

**Cause:** The `X-WP-Nonce` header is missing or expired.

**Fix:**
- Nonces expire after 12–24 hours. Page reloads regenerate the nonce.
- Ensure `@wordpress/api-fetch` is configured with the nonce middleware:

```js
import apiFetch from '@wordpress/api-fetch';
apiFetch.use( apiFetch.createNonceMiddleware( wcfEditorAppData.nonce ) );
```

---

### Template import fails or stalls

**Cause:** The importer uses background processing. Issues arise when:
- PHP `max_execution_time` is too short
- Memory limit is exceeded
- The CartFlows API is unreachable

**Fix:**
1. Check PHP error log
2. Increase `max_execution_time` and `memory_limit` in `php.ini`
3. Try importing a single template instead of a full flow

---

### E2E tests failing on fresh wp-env setup

**Cause:** wp-env environment not fully initialised.

**Fix:**
```bash
# Clean restart
npm run env:clean
npm run env:start

# Then run tests
npm run test:e2e
```

---

## Performance Issues

### Admin React app loads slowly

**Cause:** Large JS bundle or unoptimised assets.

**Checks:**
1. Ensure you're using the production build (`npm run build`), not the dev build
2. Check for unminified assets — `editor-app.js` should be minified in production

---

### Frontend checkout page loads slowly

**Cause:** Excessive WooCommerce hooks or theme conflicts.

**Checks:**
1. Use a performance profiler (Query Monitor plugin)
2. Check for N+1 database queries in the order review section
3. Disable the CartFlows order bump and re-test to isolate

---

## Getting Help

1. Check this wiki — search for your topic
2. Check the [Troubleshooting-FAQ](Troubleshooting-FAQ) (you are here)
3. Review the [WordPress-Coding-Standards](WordPress-Coding-Standards) for code issues
4. Open a GitHub issue with:
   - WordPress version
   - WooCommerce version
   - CartFlows version
   - PHP version
   - Steps to reproduce
   - Error messages / screenshots

---

## Related Pages

- [Environment-Configuration](Environment-Configuration)
- [Build-System](Build-System)
- [Testing-Guide](Testing-Guide)
- [WordPress-Coding-Standards](WordPress-Coding-Standards)
- [Architecture-Overview](Architecture-Overview)
