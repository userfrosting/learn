---
title: What's New in 6.0
description: Comprehensive changelog of new features, improvements, and breaking changes in UserFrosting 6.0
wip: true
---

# UserFrosting 6.0 Changelog

This page provides a comprehensive overview of changes in UserFrosting 6.0. For detailed migration instructions, see the [Migration Guide](upgrading/51-to-60/guide).

## Major Changes

### Unified Monorepo Architecture

**What Changed**: UserFrosting has consolidated from six separate repositories into a single monorepo.

**Before (5.1)**:
```
userfrosting/UserFrosting      (Skeleton)
userfrosting/framework          (Core Framework)
userfrosting/sprinkle-core      (Core Sprinkle)
userfrosting/sprinkle-account   (Account Sprinkle)
userfrosting/sprinkle-admin     (Admin Sprinkle)
userfrosting/theme-adminlte     (AdminLTE Theme)
```

**After (6.0)**:
```
userfrosting/monorepo           (Everything)
```

**Impact**:
- Simpler dependency management
- Consistent versioning across all components
- Easier development and contribution workflow
- Faster releases and bug fixes

### Frontend Stack Overhaul

#### Vite Replaces Webpack Encore

**What Changed**: Asset bundling moved from Webpack Encore to Vite.

**Key Benefits**:
- ⚡ **Instant dev server**: No more waiting for compilation—changes appear in milliseconds
- 🔥 **Hot Module Replacement**: Update code without losing application state
- 📦 **Optimized builds**: Smaller bundles with automatic code splitting
- 🎯 **Better DX**: Clearer error messages and simpler configuration

**Breaking Changes**:
- `webpack.config.js` → `vite.config.ts`
- `encore` CLI commands → `php bakery assets:vite` commands
- Different asset URL structure in development
- Asset manifest format changed

#### Vue 3 & TypeScript First-Class Support

**What Changed**: Modern JavaScript development is now the default.

**New Features**:
- Vue 3 Single File Components (`.vue`) with `<script setup>` syntax
- Full TypeScript support out of the box
- Composition API for better code organization
- Better tree-shaking for smaller bundles

**Migration Notes**:
- Vue 2 components need updating to Vue 3 syntax
- Options API still supported but Composition API recommended
- TypeScript optional but strongly encouraged for new code

#### UIkit 3 in AdminLTE Theme

**What Changed**: AdminLTE theme now uses UIkit 3 instead of Bootstrap.

**Why**: UIkit provides:
- Modern component design
- Better LESS integration
- Lightweight and flexible
- Excellent customization options

**Impact**:
- Custom Bootstrap themes need conversion to UIkit
- Some component APIs have changed
- LESS variables and mixins are different

### PHP & Dependency Updates

#### Minimum PHP Version: 8.1

**What Changed**: Dropped support for PHP 8.0.

**Why**: PHP 8.1+ provides:
- Enumerations (enums)
- Readonly properties
- First-class callable syntax
- Better performance
- Security improvements

#### Updated Dependencies

Major dependency updates:
- Laravel Components: 11.x
- PHPUnit: 11.x
- Symfony Components: 7.x
- Eloquent ORM: 11.x

### Development Workflow Improvements

#### Bakery CLI Enhancements

**New Commands**:
```bash
php bakery assets:vite       # Start Vite dev server
php bakery assets:build      # Build assets for production
```

**Improved Commands**:
- `php bakery serve` - Enhanced with better output
- `php bakery bake` - Streamlined installation process

#### Dual Server Development

**New Workflow**:
```bash
# Terminal 1: PHP backend
php bakery serve

# Terminal 2: Vite frontend (with HMR)
npm run vite:dev
```

**Benefits**:
- Frontend changes appear instantly (HMR)
- Backend remains stable and reliable
- Clear separation of concerns
- Better error handling

## API Changes

### Breaking Changes

> [!WARNING]
> These changes require code updates in your application.

#### Asset Loading in Twig Templates

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

#### Configuration Structure

**Changed**: Some configuration keys have been reorganized.

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

#### Sprinkle Asset Structure

**Changed**: Asset organization within sprinkles.

**Before (5.1)**:
```
app/sprinkles/custom/
├── assets/
│   ├── webpack.config.js
│   └── src/
│       └── app.js
```

**After (6.0)**:
```
app/sprinkles/custom/
├── assets/
│   ├── vite.config.ts
│   └── main.ts
```

### Deprecations

#### jQuery & Handlebars

**Status**: Still available but **deprecated**.

**What This Means**:
- Existing jQuery code continues to work
- New development should use Vue 3
- Handlebars client-side templating replaced by Vue templates
- Will be removed in a future major version

**Migration Path**:
- Gradually convert jQuery to Vue 3 components
- Replace Handlebars with Vue's template syntax
- Use TypeScript for new features

#### Webpack Encore Support

**Status**: Maintained for **backward compatibility only**.

**What This Means**:
- Existing Webpack setups continue working
- New projects should use Vite
- Webpack documentation moved to legacy section
- Limited new features for Webpack

**Migration Path**:
- Plan migration to Vite for long-term projects
- Follow [Webpack to Vite migration guide](asset-management/webpack-encore)
- No immediate pressure to migrate stable applications

## New Features

### Asset Management

- **Hot Module Replacement**: Instant updates during development
- **Code Splitting**: Automatic chunking for better performance
- **Tree Shaking**: Unused code automatically removed
- **Source Maps**: Better debugging with mapped TypeScript/Vue
- **Legacy Browser Support**: Automatic polyfills when needed

### Developer Tools

- **Vue DevTools**: First-class debugging support
- **TypeScript Checking**: Type safety across your application
- **ESLint Integration**: Modern linting for Vue/TS/JS
- **Vitest**: Fast, Vite-native testing framework

### Performance Improvements

- **Faster Build Times**: Vite builds 2-10x faster than Webpack
- **Smaller Bundle Sizes**: Better tree-shaking and minification
- **Optimized Dev Server**: Instant startup, no bundling
- **Better Caching**: Improved cache strategies for production

## Removals

### Removed Dependencies

The following are no longer included by default:

- ❌ **jQuery** (deprecated, use Vue 3)
- ❌ **Bootstrap** in AdminLTE (replaced by UIkit)
- ❌ **Handlebars** (deprecated, use Vue templates)
- ❌ **Webpack Encore** (use Vite instead)
- ❌ **Several legacy npm packages** (see package.json diff)

### Removed PHP Support

- ❌ **PHP 7.4**: End of life
- ❌ **PHP 8.0**: Superseded by 8.1

## Database & ORM

### Changes

- Updated to **Eloquent 11.x**
- Better query performance
- Enhanced relationship loading
- Improved migration system

### Breaking Changes

Minor API adjustments in Eloquent 11.x:
- Some method signatures updated
- Deprecated methods removed
- Better type hints

## Testing

### Changes

- **PHPUnit 11.x**: Latest testing framework
- **Vitest**: For frontend unit tests
- Better test helpers and factories
- Improved coverage reporting

### New Testing Features

```bash
# Frontend tests with Vitest
npm run test
npm run coverage

# Backend tests with PHPUnit
vendor/bin/phpunit
```

## Security Updates

- PHP 8.1+ security improvements
- Updated security dependencies
- Improved CSRF protection
- Better session management

## Documentation Updates

- Complete rewrite for 6.0 architecture
- New frontend development guides
- Vite configuration examples
- Vue 3 component patterns
- Migration guides from 5.1

## Known Issues

> [!NOTE]
> Check the [GitHub Issues](https://github.com/userfrosting/monorepo/issues) for the latest known issues and workarounds.

## What's Next?

See [What to Expect](upgrading/51-to-60/what-to-expect) for information about the future roadmap and planned features beyond 6.0.
