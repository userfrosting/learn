---
title: Step-by-Step Migration Guide
description: Detailed instructions for upgrading your UserFrosting 5.1 application to 6.0
---

This guide walks you through upgrading an existing UserFrosting 5.1 application to version 6.0. Follow these steps carefully and test thoroughly in a development environment before deploying to production.

> [!IMPORTANT]
> **Backup Everything**: Before starting, create backups of your application code, database, and any user-uploaded files. This upgrade includes breaking changes that may require significant testing.

This guide covers the most common upgrade scenarios, but your specific application may have unique customizations that require additional steps. Always refer to the official documentation if you encounter issues. Also consider whether upgrading in place is the best option for your project, or if a fresh installation of UserFrosting 6.0 with manual migration of custom code and data might be more efficient in the long run.

## Prerequisites

Before beginning the upgrade:

- [ ] Running latest UserFrosting 5.1.x
- [ ] PHP 8.1 or higher installed (8.4 recommended)
- [ ] Composer 2 installed
- [ ] Node.js 18.0+ installed (LTS 24 recommended)
- [ ] npm 9.0+ installed
- [ ] Backed up database and application files
- [ ] Development environment ready for testing

## Step 1: Prepare Your Environment

### Update System Requirements

Ensure your development environment meets UserFrosting 6.0 requirements:

```bash
# Check PHP version (must be 8.1+)
php -v

# Check Composer version (must be 2.x)
composer --version

# Check Node.js version (must be 18+)
node --version

# Check npm version (must be 9+)
npm --version
```

If any versions are below requirements, update them before proceeding.

### Create a Testing Branch

```bash
# Create a new branch for the upgrade
git checkout -b upgrade-to-6.0

# Ensure working directory is clean
git status
```

## Step 2: Update Composer Dependencies

### Backup composer.json

```bash
cp composer.json composer.json.backup
```

### Update Dependency Versions

Update all `userfrosting/*` version constraints in `composer.json`:

**Before (5.1)**:
```json
{
    "require": {
        "php": "^8.1",
        "ext-gd": "*",
        "userfrosting/framework": "~5.1.0",
        "userfrosting/sprinkle-core": "~5.1.0",
        "userfrosting/sprinkle-account": "~5.1.0",
        "userfrosting/sprinkle-admin": "~5.1.0",
        "userfrosting/theme-adminlte": "~5.1.0"
    }
}
```

**After (6.0)**:
```json
{
    "require": {
        "php": "^8.1",
        "ext-gd": "*",
        "userfrosting/framework": "^6.0",
        "userfrosting/sprinkle-core": "^6.0",
        "userfrosting/sprinkle-account": "^6.0",
        "userfrosting/sprinkle-admin": "^6.0"
    }
}
```

The key changes are:
- All `userfrosting/*` packages updated from `~5.1.0` to `^6.0`
- `userfrosting/theme-adminlte` removed — the AdminLTE theme is replaced by the new [Pink Cupcake theme](/ui-theming), which is included as part of the default UserFrosting packages

### Run Composer Update

```bash
# Update dependencies
composer update

# Clear Composer cache if you encounter issues
composer clear-cache
composer update
```

> [!WARNING]
> This step may take several minutes as Composer downloads updated dependencies.

## Step 3: Update npm Dependencies

### Backup package.json

```bash
cp package.json package.json.backup
```

### Update npm Dependencies

Replace the contents of your `package.json` to match the 6.0 structure. The changes are significant — UserFrosting 6 ships a full Vue SPA stack as npm dependencies:

**Before (5.1)**:
```json
{
    "dependencies": {
        "@userfrosting/sprinkle-admin": "~5.1.0",
        "@userfrosting/theme-adminlte": "~5.1.0"
    },
    "devDependencies": {
        "@symfony/webpack-encore": "^5.1.0",
        "file-loader": "^6.2.0",
        "sass": "^1.51.0",
        "webpack-notifier": "^1.14.1"
    },
    "scripts": {
        "dev-server": "encore dev-server",
        "dev": "encore dev",
        "watch": "encore dev --watch",
        "build": "encore production --progress"
    }
}
```

**After (6.0)**:
```json
{
    "type": "module",
    "engines": {
        "node": ">= 18"
    },
    "dependencies": {
        "@userfrosting/sprinkle-account": "^6.0.0",
        "@userfrosting/sprinkle-admin": "^6.0.0",
        "@userfrosting/sprinkle-core": "^6.0.0",
        "@userfrosting/theme-pink-cupcake": "^6.0.0",
        "axios": "^1.12.0",
        "pinia": "^2.1.6",
        "pinia-plugin-persistedstate": "^3.2.0",
        "vue": "^3.3.4",
        "vue-router": "^4.2.4"
    },
    "devDependencies": {
        "@modyfi/vite-plugin-yaml": "^1.1.1",
        "@types/uikit": "^3.14.5",
        "@vitejs/plugin-vue": "^5.0.4",
        "@vitest/coverage-v8": "^3.1.1",
        "@vue/test-utils": "^2.4.6",
        "happy-dom": "^20.0.2",
        "less": "^4.2.0",
        "typescript": "^5.4.5",
        "vite": "^6.4.1",
        "vite-plugin-vue-devtools": "^7.5.4",
        "vitest": "^3.1.1",
        "vue-tsc": "^2.1.10"
    },
    "scripts": {
        "vite:dev": "vite",
        "vite:build": "vue-tsc && vite build",
        "test": "vitest",
        "coverage": "vitest run --coverage",
        "typecheck": "vue-tsc --noEmit",
        "lint": "eslint app/assets/ --fix",
        "format": "prettier --write app/assets/"
    }
}
```

Key changes:
- `@userfrosting/theme-adminlte` removed — replaced by `@userfrosting/theme-pink-cupcake`
- `@userfrosting/sprinkle-core` and `@userfrosting/sprinkle-account` added as npm packages (they now ship frontend assets)
- Full Vue SPA stack added to `dependencies`: Vue 3, Vue Router, Pinia, Axios
- Webpack Encore and Sass removed, replaced by Vite and Less
- Scripts renamed from Encore (`dev`, `watch`, `build`) to Vite (`vite:dev`, `vite:build`)
- `"type": "module"` added to enable native ES modules

> [!TIP]
> See the [skeleton package.json](https://github.com/userfrosting/monorepo/blob/main/packages/skeleton/package.json) for the complete up-to-date list of dependencies.

### Install Dependencies

```bash
# Remove old node_modules and lock file
rm -rf node_modules package-lock.json

# Install fresh dependencies
npm install
```

## Step 4: Configure Vite

### Create vite.config.ts

Create a new `vite.config.ts` file in your project root. Use the [skeleton's `vite.config.ts`](https://github.com/userfrosting/monorepo/blob/main/packages/skeleton/vite.config.ts) as your starting point — copy it directly into your project root.

### Remove Webpack Configuration

```bash
# Remove old Webpack config files
rm webpack.config.js
rm webpack.mix.js  # if present
rm -rf app/assets/webpack  # if present
```

## Step 5: Migrate Asset Files

### Restructure Asset Directory

**Before (5.1)** structure:
```
app/assets/
├── webpack.config.js
├── src/
│   ├── app.js
│   └── app.scss
└── public/
    └── images/
```

**After (6.0)** structure:
```
app/assets/
├── main.ts          # Main entry point
├── theme.less       # Main stylesheet
├── components/      # Vue components
│   └── MyComponent.vue
├── css/            # Additional styles
└── public/         # Static assets
    └── images/
```

### Create Main Entry Point and App Structure

The `app/assets/` directory needs several new files to bootstrap the Vue SPA. Copy the following from the skeleton as your starting point:

- [`app/assets/main.ts`](https://github.com/userfrosting/monorepo/blob/main/packages/skeleton/app/assets/main.ts) — Vue app entry point, sets up Pinia, Router and all sprinkle plugins
- [`app/assets/App.vue`](https://github.com/userfrosting/monorepo/blob/main/packages/skeleton/app/assets/App.vue) — Root Vue component
- [`app/assets/router/index.ts`](https://github.com/userfrosting/monorepo/blob/main/packages/skeleton/app/assets/router/index.ts) — Vue Router configuration, includes routes from all sprinkles

> [!NOTE]
> UserFrosting 6 uses Vue Router and Pinia for state management. Each sprinkle registers its own Vue plugin that provides routes, stores, and components. Your custom sprinkle code should extend this setup rather than replace it.

You will likely also want to copy the skeleton's layout and view files as a baseline:
- [`app/assets/layouts/`](https://github.com/userfrosting/monorepo/blob/main/packages/skeleton/app/assets/layouts/) — Page and dashboard layout components
- [`app/assets/views/`](https://github.com/userfrosting/monorepo/blob/main/packages/skeleton/app/assets/views/) — Default home and about views
- [`app/assets/components/`](https://github.com/userfrosting/monorepo/blob/main/packages/skeleton/app/assets/components/) — Navbar, sidebar, and footer content placeholders

### Rename/Convert Style Files

```bash
# Rename SCSS to LESS (or convert if needed)
mv app/assets/src/app.scss app/assets/theme.less

# Or create a new theme.less file
touch app/assets/theme.less
```

> [!TIP]
> UIkit uses LESS, so converting your styles to LESS provides better integration. However, Vite also supports SCSS if you prefer to keep it.

## Step 6: Update Configuration

### Update Configuration Files

The Vite integration is configured through the `assets.vite` config key. The Core Sprinkle sets sensible defaults, so you only need to customize if your setup differs. The key environment variables are:

```bash
# Enable/disable Vite dev server (default: true in development, false in production)
VITE_DEV_ENABLED=true

# Port for the Vite dev server (default: 5173)
VITE_PORT=5173
```

If you previously had Webpack-related config in `app/config/default.php`, remove it:

**Before (5.1)** - remove this:
```php
'assets' => [
    'webpack' => [
        'manifest' => 'manifest.json'
    ]
]
```

The Vite config is handled automatically by the Core Sprinkle. You do not need to add any `assets.vite` entries to your own config unless you need to customize the server URL or manifest path.

## Step 7: Update Twig Templates

### Replace Asset Helper Functions

Find and replace all asset loading functions in your Twig templates:

**Before (5.1)**:
```twig
{{ encore_entry_script_tags('app') }}
{{ encore_entry_link_tags('app') }}
```

**After (6.0)**:
```twig
{# In your scripts template: #}
{{ vite_js('main.ts') }}

{# In your stylesheets template: #}
{{ vite_css('main.ts') }}
{{ vite_preload('main.ts') }}
```

> [!TIP]
> Use find-and-replace in your editor to update all templates at once. Run two separate replacements:
> - Find: `encore_entry_script_tags\('([^']+)'\)` → Replace: `vite_js('$1.ts')`
> - Find: `encore_entry_link_tags\('([^']+)'\)` → Replace: `vite_css('$1.ts')` (then add `{{ vite_preload('$1.ts') }}` on the next line)

### Search All Templates

```bash
# Find all templates using Encore helpers
grep -r "encore_entry" app/templates/

# Verify no Encore references remain
grep -r "encore" app/templates/
```

## Step 8: Migrate Custom JavaScript

### Convert to ES Modules

If you have custom JavaScript, convert it to ES modules:

**Before (5.1)** - `custom.js`:
```javascript
// CommonJS or global scope
var MyModule = {
    init: function() {
        // ...
    }
};
```

**After (6.0)** - `custom.ts`:
```typescript
// ES Module with TypeScript
export class MyModule {
    static init(): void {
        // ...
    }
}
```

### Update jQuery Code (Optional but Recommended)

If you have jQuery code, consider migrating to Vue 3:

**Before (5.1)** - jQuery:
```javascript
$(document).ready(function() {
    $('#myButton').on('click', function() {
        $('#myDiv').toggle();
    });
});
```

**After (6.0)** - Vue 3:
```vue
<template>
    <button @click="toggleDiv">Toggle</button>
    <div v-if="showDiv">Content</div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

const showDiv = ref(false)
const toggleDiv = () => {
    showDiv.value = !showDiv.value
}
</script>
```

> [!NOTE]
> jQuery is not included in UserFrosting 6. If you need it during migration, install it manually (`npm install jquery`). New code should use Vue 3 instead of jQuery for better performance and maintainability.

## Step 9: Update Custom Sprinkles

### Update Sprinkle Recipe

Verify your sprinkle's Recipe class is compatible with UserFrosting 6.0. Most sprinkle recipes should work without changes, but review the [Sprinkles documentation](/sprinkles) for any API changes.

## Step 10: Database Migrations

### Run Migrations

UserFrosting 6.0 includes database updates:

```bash
# Run migrations
php bakery migrate

# If you encounter issues, rollback and retry
php bakery migrate:rollback
php bakery migrate
```

### Verify Database

Check that all tables are present and properly structured:

```bash
# Check migration status
php bakery migrate:status
```

## Step 11: Build and Test

### Build Assets

```bash
# Build assets for production
npm run vite:build

# Verify build output
ls -la public/assets/
```

### Start Development Servers

Open two terminals:

**Terminal 1** - PHP backend:
```bash
php bakery serve
```

**Terminal 2** - Vite frontend:
```bash
npm run vite:dev
```

### Test Your Application

Visit `http://localhost:8080` and thoroughly test:

- [ ] Homepage loads correctly
- [ ] Login/logout functionality
- [ ] User registration
- [ ] Admin panel access
- [ ] All custom features
- [ ] API endpoints
- [ ] Forms and validation
- [ ] Asset loading (check browser console for errors)

### Check Browser Console

Open browser DevTools (F12) and verify:
- No JavaScript errors
- Assets loading correctly (200 status codes)
- No 404 errors for missing files

## Step 12: Production Deployment

### Build Production Assets

```bash
# Build optimized production assets
npm run vite:build

# Verify build output
ls -la public/assets/.vite/
```

### Update Environment Variables

In production `.env`:

```bash
# Disable Vite dev server
VITE_DEV_ENABLED=false
```

### Deploy

Follow your normal deployment process, ensuring:

- Built assets are deployed (`public/assets/`)
- Dependencies are installed (`composer install --no-dev`)
- Configuration is set for production
- Database migrations are run

### Clear Caches

```bash
# Clear all application caches (Illuminate, Twig, and Router cache)
php bakery clear-cache
```

## Troubleshooting Common Issues

### "Cannot find module" Errors

**Symptom**: Vite can't find imported modules

**Solution**:
```bash
# Clear node_modules and reinstall
rm -rf node_modules package-lock.json
npm install
```

### Assets Not Loading

**Symptom**: 404 errors for assets in browser

**Solution**:
- Verify `vite.config.ts` paths are correct
- Check `.env` has correct `VITE_DEV_ENABLED` setting
- Ensure Vite dev server is running (development)
- Verify assets are built (`public/assets/`) (production)

### Vue Components Not Rendering

**Symptom**: Vue components don't appear on page

**Solution**:
- Check browser console for Vue errors
- Verify Vue is imported in `main.ts`
- Ensure components are properly registered
- Check that mounting point (`#app`) exists in template

### Database Migration Errors

**Symptom**: Migrations fail to run

**Solution**:
```bash
# Reset and re-run migrations (DEVELOPMENT ONLY)
php bakery migrate:reset
php bakery migrate

# Check migration status
php bakery migrate:status
```

### Performance Issues in Development

**Symptom**: Slow page loads during development

**Solution**:
- Ensure Vite dev server is running (`npm run vite:dev`)
- Check `optimizeDeps` in `vite.config.ts`
- Clear Vite cache: `rm -rf node_modules/.vite`

## Rollback Plan

If you need to rollback to 5.1:

```bash
# Restore backups
git checkout main
cp composer.json.backup composer.json
cp package.json.backup package.json

# Reinstall old dependencies
composer install
npm install

# Restore database from backup
# (use your database backup restore process)
```

## Next Steps

After successfully upgrading:

1. **Review Documentation**: Read through the updated [Asset Management](/assets-vite) chapter
2. **Optimize Assets**: Learn about [Vite Configuration](/assets-vite/vite-configuration) for performance tuning
3. **Modernize Code**: Gradually convert jQuery to Vue 3 components
4. **Add TypeScript**: Consider adding type safety to your custom code
5. **Explore Features**: Check out new Vite features like HMR and code splitting

If you encounter issues:

- **Documentation**: [UserFrosting Learn](/)
- **Community Chat**: [chat.userfrosting.com](https://chat.userfrosting.com)
- **GitHub Issues**: [github.com/userfrosting/userfrosting/issues](https://github.com/userfrosting/monorepo/issues)

Congratulations on upgrading to UserFrosting 6.0! 🎉
