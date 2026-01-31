---
title: Step-by-Step Migration Guide
description: Detailed instructions for upgrading your UserFrosting 5.1 application to 6.0
wip: true
---

# Migration Guide: UserFrosting 5.1 → 6.0

This guide walks you through upgrading an existing UserFrosting 5.1 application to version 6.0. Follow these steps carefully and test thoroughly in a development environment before deploying to production.

> [!IMPORTANT]
> **Backup Everything**: Before starting, create backups of your application code, database, and any user-uploaded files. This upgrade includes breaking changes that may require significant testing.

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

### Update Framework Dependency

**Before (5.1)** - `composer.json`:
```json
{
    "require": {
        "userfrosting/framework": "~5.1.0"
    }
}
```

**After (6.0)** - `composer.json`:
```json
{
    "require": {
        "userfrosting/userfrosting": "^6.0"
    }
}
```

> [!NOTE]
> The package name changed from `userfrosting/framework` to `userfrosting/userfrosting` to reflect the monorepo structure.

### Update PHP Version Constraint

Update the minimum PHP version in `composer.json`:

```json
{
    "require": {
        "php": "^8.1"
    }
}
```

### Remove Obsolete Dependencies

The following dependencies are now included in the monorepo and should be removed from your `composer.json`:

```json
// REMOVE these if present:
"userfrosting/sprinkle-core": "*",
"userfrosting/sprinkle-account": "*",
"userfrosting/sprinkle-admin": "*",
"userfrosting/theme-adminlte": "*"
```

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

### Remove Webpack Encore Dependencies

Remove Webpack-related packages from `package.json`:

```json
// REMOVE these from devDependencies:
"@symfony/webpack-encore": "^*",
"webpack": "^*",
"webpack-cli": "^*",
"webpack-dev-server": "^*",
// ... other webpack-related packages
```

### Add Vite Dependencies

Add Vite and related packages to `package.json`:

```json
{
    "devDependencies": {
        "vite": "^6.0.0",
        "@vitejs/plugin-vue": "^5.0.0",
        "vue": "^3.3.0",
        "typescript": "^5.4.0",
        "vue-tsc": "^2.0.0"
    }
}
```

> [!TIP]
> See the [monorepo package.json](https://github.com/userfrosting/monorepo/blob/6.0/package.json) for the complete list of recommended dependencies.

### Update npm Scripts

Update the scripts section in `package.json`:

**Before (5.1)**:
```json
{
    "scripts": {
        "dev": "encore dev",
        "watch": "encore dev --watch",
        "build": "encore production"
    }
}
```

**After (6.0)**:
```json
{
    "scripts": {
        "vite:dev": "vite",
        "vite:build": "vue-tsc && vite build",
        "typecheck": "vue-tsc --noEmit"
    }
}
```

### Install Dependencies

```bash
# Remove old node_modules and lock file
rm -rf node_modules package-lock.json

# Install fresh dependencies
npm install
```

## Step 4: Configure Vite

### Create vite.config.ts

Create a new `vite.config.ts` file in your project root:

```typescript
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
    plugins: [
        vue(),
    ],
    server: {
        host: true,
        strictPort: true,
        port: 5173,
        origin: 'http://localhost:5173',
    },
    root: 'app/assets/',
    base: '/assets/',
    build: {
        outDir: '../../public/assets',
        assetsDir: '',
        emptyOutDir: true,
        manifest: true,
        rollupOptions: {
            input: {
                main: 'app/assets/main.ts',
            }
        }
    },
    optimizeDeps: {
        include: ['uikit', 'uikit/dist/js/uikit-icons']
    }
})
```

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

### Create Main Entry Point

Create `app/assets/main.ts`:

```typescript
import { createApp } from 'vue'
import './theme.less'

// Import your components
// import MyComponent from './components/MyComponent.vue'

// Initialize Vue app
const app = createApp({})

// Register global components
// app.component('MyComponent', MyComponent)

// Mount to DOM when ready
document.addEventListener('DOMContentLoaded', () => {
    app.mount('#app')
})
```

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

Update your `.env` file:

```bash
# Add Vite dev server flag
VITE_DEV_SERVER=true
```

Update configuration in `app/config/default.php` (or your custom config):

**Before (5.1)**:
```php
'assets' => [
    'webpack' => [
        'manifest' => 'manifest.json'
    ]
]
```

**After (6.0)**:
```php
'assets' => [
    'vite' => [
        'dev' => env('VITE_DEV_SERVER', false),
        'manifest' => '.vite/manifest.json'
    ]
]
```

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
{{ vite_js('main.ts') }}
{{ vite_css('main.ts') }}
```

> [!TIP]
> Use find-and-replace in your editor to update all templates at once:
> - Find: `encore_entry_script_tags\('([^']+)'\)`
> - Replace: `vite_js('$1.ts')`

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
> jQuery is deprecated but still supported in 6.0. You can migrate gradually, but new code should use Vue 3.

## Step 9: Update Custom Sprinkles

### Update Sprinkle Structure

If you have custom sprinkles, update their structure:

**Before (5.1)**:
```
app/sprinkles/mysprinkle/
├── composer.json
├── assets/
│   └── webpack.config.js
├── src/
└── templates/
```

**After (6.0)**:
```
app/sprinkles/mysprinkle/
├── composer.json
├── assets/
│   ├── vite.config.ts  # If needed
│   └── main.ts
├── src/
└── templates/
```

### Update Sprinkle Recipe

Verify your sprinkle's Recipe class is compatible with UserFrosting 6.0. Most sprinkle recipes should work without changes, but review the [Sprinkles documentation](sprinkles) for any API changes.

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
VITE_DEV_SERVER=false
```

### Deploy

Follow your normal deployment process, ensuring:

- Built assets are deployed (`public/assets/`)
- Dependencies are installed (`composer install --no-dev`)
- Configuration is set for production
- Database migrations are run

### Clear Caches

```bash
# Clear application caches
php bakery cache:clear

# Clear route cache
php bakery cache:clear-routes
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
- Check `.env` has correct `VITE_DEV_SERVER` setting
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

1. **Review Documentation**: Read through the updated [Asset Management](asset-management) chapter
2. **Optimize Assets**: Learn about [Vite Configuration](asset-management/vite-configuration) for performance tuning
3. **Modernize Code**: Gradually convert jQuery to Vue 3 components
4. **Add TypeScript**: Consider adding type safety to your custom code
5. **Explore Features**: Check out new Vite features like HMR and code splitting

## Getting Help

If you encounter issues:

- **Documentation**: [UserFrosting Learn](/)
- **Community Chat**: [chat.userfrosting.com](https://chat.userfrosting.com)
- **GitHub Issues**: [github.com/userfrosting/monorepo/issues](https://github.com/userfrosting/monorepo/issues)
- **Stack Overflow**: Tag questions with `userfrosting`

Congratulations on upgrading to UserFrosting 6.0! 🎉
