---
title: What's New in 6.0
description: Comprehensive changelog of new features, improvements, and breaking changes in UserFrosting 6.0
---

# UserFrosting 6.0 Changelog

This page provides a comprehensive overview of changes in UserFrosting 6.0. It covers new features, improvements, breaking changes, and removals compared to 5.1. Use this as a reference when planning your upgrade to understand the impact on your application and development workflow.

## Major Changes

### Brand new UI

While UserFrosting 5 focused on providing a solid backend framework, the frontend was left mostly untouched, still using older technologies (jQuery, Handlebars, Bootstrap) and a dated design.

UserFrosting 6.0 features a **completely redesigned user interface** built with modern frontend technologies. The new UI is original, modern, and designed with usability and accessibility in mind. It provides a fresh look while maintaining the core functionality users expect.

While the old template was named "AdminLTE" and based on the framework of the same name (itself build with Bootstrap), the new one is called "**PinkCupcake**". It replaces Bootstrap with UIkit 3, offering a more modern and flexible design system. The new UI is built from the ground up to be responsive, accessible, and easy to customize.

**What's changed**:
- **AdminLTE** theme replaced with **PinkCupcake** theme
- Built-in Twig templates replaced with Vue 3 components
- Bootstrap replaced with UIkit 3
- Handlebars client-side templating replaced with Vue's template syntax
- Stylesheet moved from CSS to LESS for better maintainability and customization

**New Features**:
- Vue 3 Single File Components (`.vue`) with `<script setup>` syntax
- Full TypeScript support out of the box

> [!IMPORTANT]
> The new UI is not a drop-in replacement for the old one. If your application has custom Twig templates or relies heavily on the old frontend structure, you will need to update those to work with the new Vue 3 components and UIkit classes. This is a significant change that will require manual updates to your sprinkles and app code.
>
> If your application does not use the old admin dashboard or has minimal frontend customizations, the migration will be easier, as you'll be able to keep your old templates and gradually migrate to the new UI components as needed.

### Vue 3, Vite & TypeScript First-Class Support

UserFrosting 6.0 embraces modern JavaScript development with **Vue 3** and **TypeScript** as first-class citizens. This allows developers to build reactive, component-based applications with better performance and maintainability. The new frontend stack provides a more enjoyable development experience and better integration with modern tools. This means the UI can handle even more complex interactions while remaining responsive and fast, with less code than before.

**What's changed**:
- Webpack Encore is still available but **deprecated** in favor of Vite
- jQuery and Handlebars are **not** included by default but still available to install

**Key Benefits**:
- **Instant dev server**: No more waiting for compilation—changes appear in milliseconds
- **Hot Module Replacement**: Update code without losing application state
- **Optimized builds**: Smaller bundles with automatic code splitting
- **Better Vue support**: Vite is build for Vue.JS and offer better integration than Webpack

**Migration Notes**:
- Twig templates integrating with the old admin dashboard interface need updating / replacing to Vue 3 syntax
- TypeScript optional but strongly encouraged for new code

### New User Verification System

UserFrosting 6.0 introduces a new user verification system that replaces the old email verification process. This new system is more flexible and provides the base for various types of verification beyond just email, such as phone number verification or multi-factor authentication.

Instead of sending a verification email with a token in a clickable link, a one time use code will now be generated and sent to the user via email. The user can then enter this code in the application to verify their account. This approach is more secure and user-friendly, as it doesn't rely on users clicking links in emails, reducing phishing risks.

The new system uses a more generic "verification" concept that can be extended to different types. This allows for greater customization and future-proofing as new verification methods get implemented.

This change requires database migrations, which will be handled automatically when you upgrade. However, if you have custom sprinkles that interact with the old verification system, you will need to update those to work with the new verification logic.

## Dependency and Requirements Updates

While the minimum **required PHP version is still 8.1**, the recommended PHP version is now 8.4. PHP 8.5 is also supported but not required, giving you flexibility in your hosting environment.

On the frontend, **Node.js 18.0 or higher** is still required to use Vite and the new build tools. Node.js 24 (LTS) is recommended for the best performance and compatibility with the latest features.

## What's the same ?

While the frontend has been completely redesigned, the core backend architecture of UserFrosting remains consistent with 5.1. The underlying PHP framework, database structure, and core features are still there, just with improved performance, modernized dependencies and adapted to work with the new frontend.

This means most of your existing backend code, database structures, validation schema, and core sprinkles integrations should continue to work with minimal changes. The upgrade process will primarily focus on updating the frontend assets, templates, and any custom sprinkles that interact with the UI.

## Detailed Changelog

### Codebase changes and new features

#### Database Migrations

The new user verification system requires changes to the database schema. When you run the upgrade process (aka run `php bakery bake`), the necessary migrations will be executed automatically to update your database structure to support the new verification logic.

**New Migrations**:
- `DropPasswordResetsTable`: Removes the old `password_resets` table used for email verification tokens
- `DropVerificationsTable`: Removes the old `verifications` table used for email verification tokens
- `UpdateUsersTable`: Adds new fields to the `users` table to support the new verification system: `password_last_set`
- `UserVerificationTable`: Creates a new `user_verifications` table to store verification records for different usages

**The big picture**: Two separate token tables (`password_resets` + `verifications`) are consolidated into one `user_verifications` table. This is a significant simplification of how UF handles email verification and password reset flows.

The associated models and logic have been updated to reflect this new structure, so if you have custom code that interacts with the old tables, you will need to update it to work with the new `user_verifications` table and the new verification logic.

**Changed models**:
- `Verification` and `PasswordReset` models are removed, replaced by a single `UserVerification` model that handles all types of verification records.
- `User` model updated to include new fields and methods related to the new verification system, including a new `isPasswordExpired` method. Also adds a new `getApiDataAttribute()` method to return the user data for the frontend API.

#### Dropped Locales

UserFrosting 6 now ships only with English and French locales. All other locales have been removed from the core distribution to reduce maintenance overhead and encourage community contributions for additional languages. 

If you were using a locale that has been removed, you will need to add it back manually by creating a new locale file in your application or sprinkles. The structure of the locale files has not changed, so you can copy the format from the existing English or French files as a template for your new locale.

> [!TIP]
> If you want to contribute a new locale, you're encouraged to create a new Community Sprinkle with the new locale file. This way, you can maintain it separately from the core and share it with the community without needing to go through the core contribution process. You can also submit a pull request to add it to the core if you think it's widely used and should be included by default.

#### Cached Locale Dictionary

The locale data are now cached. This means that the locale files are read and processed once, and then stored in a cache for faster access on subsequent requests. This improves performance by reducing file I/O and processing time for locale data, especially in applications with many locales or large locale files.

When updating a locale file, you may need to clear the cache to see the changes reflected in your application. This can be done by running `php bakery clear-cache` or by manually deleting the cache files.

#### New Feature : Markdown Support

New in 6.0, UserFrosting now includes built-in support for Markdown parsing using the CommonMark library. This allows you to easily render Markdown content in your application, whether it's user-generated content, documentation, or any other text that benefits from rich formatting.

The Privacy Policy and Terms of Service pages in the new PinkCupcake theme are examples of how Markdown can be used to create rich, formatted content without needing to write complex HTML. The parser configuration can be customized in the `config.php` file under the new `markdown` key.

The markdown parser support files in different locales. Simply add the locale identifier to the filename to add support for that locale (e.g. `markdown.fr_FR.php` for French). 

#### Bakery CLI

**New Commands**:
```bash
php bakery assets:vite       # Start Vite dev server or build assets for production
```

**Improved Commands**:
- `php bakery assets:build` and `php bakery bake` will invoke Vite or Webpack based on configuration

#### Asset Loading in Twig Templates

Twig templates now include Vite's helper functions to load assets instead of Webpack Encore's. This means you need to update your templates to use the new syntax for including JavaScript and CSS files, unless you choose to keep using Webpack Encore for backward compatibility (not recommended for new projects).

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

Vite configuration is now integrated into the main `config.php` file under the `assets` key. This allows you to configure both Vite and Webpack settings in one place, making it easier to manage your asset bundler configuration.

**Before (5.1)**:
```php
// REMOVED in 6.0 (was at top level)
'webpack' => [
    'entrypoints' => 'assets://entrypoints.json',
    'manifest'    => 'assets://manifest.json',
],
```

**After (6.0)**:
```php
'assets' => [
    'bundler' => env('ASSETS_BUNDLER'), // Either 'vite' or 'webpack'
    'vite' => [
        'manifest' => 'assets://.vite/manifest.json',
        'dev'      => env('VITE_DEV_ENABLED', true),
        'base'     => '/assets/',
        'server'   => 'http://[::1]:' . env('VITE_PORT', 5173),
    ],
    'webpack' => [
        'entrypoints' => 'assets://entrypoints.json',
        'manifest'    => 'assets://manifest.json',
    ],
],
```

**Added in 6.0: markdown block**
Completely new, for the CommonMark parser:
```php
<?php
'markdown' => [
    'html_input'         => 'strip',
    'allow_unsafe_links' => false,
    'max_nesting_level'  => 100,
],
```

**Changed: `site.locales.available defaults`**
To reflect the new default locales included in the core, the default value for `site.locales.available` has been changed :
```php
// 6.0 — 2 locales
'available' => [
    'en_US' => true,
    'fr_FR' => true,
]
``` 

**Removed in 6.0: password_reset block**
The dedicated password reset config is gone, replaced by the generic otp system:
```php
<?php
// REMOVED in 6.0
'password_reset' => [
    'algorithm' => 'sha512',
    'timeouts'  => [
        'create' => 86400,
        'reset'  => 10800,
    ],
],
```

**Removed in 6.0: verification block**
Also replaced by otp:
```php
<?php
// REMOVED in 6.0
'verification' => [
    'algorithm' => 'sha512',
    'timeout'   => 10800,
],
```

**Removed in 6.0: per_user_theme**
```php
<?php
// REMOVED (was already deprecated with @deprecated 4.6.0 notice)
'per_user_theme' => false,
```

**Added in 6.0: otp block**
Unified One Time Password config for both email verification and password reset:
```php
<?php
'otp' => [
    'timeout' => 600, // 10 minutes
],
```

**Added in 6.0: password.expiration block**
New password expiration feature:
```php
<?php
'password' => [
    'expiration' => [
        'timeout' => 0, // In days, zero = no expiration
    ],
],
```

**Renamed throttle keys**
| 5.1 key                  | 6.0 key                                                          |
|--------------------------|------------------------------------------------------------------|
| `password_reset_request` | `account.password.reset.request`                                 |
| `verification_request`   | `account.verify.request`                                         |
| ❌                        | `account.verify.email` ← NEW (split from `verification_request`) |

**Misc removed values**:
- `session.keys.csrf`
- `site.debug.ajax`
- `site.uf_table.use_loading_transition`

#### Sprinkle Asset Structure

**Changed**: Asset organization within sprinkles.

**Before (5.1)**:
```
app/
├── assets/
│   ├── webpack.config.js
│   └── src/
│       └── app.js
```

**After (6.0)**:
```
app/
├── assets/
│   ├── vite.config.ts
│   └── main.ts
```

See the new [Assets & Vite](/assets-vite) chapter for details on how to configure and use Vite in your sprinkles.

### Developer Tools & Experience

The skeleton now ships with pre-configured VSCode tasks to help you run developer tasks from the IDE.

Are also included by default in the `devDependencies` of the skeleton's `package.json`:
- **Vue DevTools**: First-class debugging support
- **TypeScript Checking**: Type safety across your application
- **ESLint Integration**: Modern linting for Vue/TS/JS
- **Vitest**: Fast, Vite-native testing framework

#### Dual Server Development

Since the frontend is now served by Vite and the backend by PHP's built-in server, the development workflow has changed. Assets requires compilation, thus requiring a separate process to run the Vite dev server alongside the PHP server.

**New Workflow**:
```bash
# Terminal 1: PHP backend
php bakery serve

# Terminal 2: Vite frontend (with HMR)
npm run vite:dev
```

The key benefit is Hot Module Replacement (HMR) for frontend development, allowing you to see changes instantly without refreshing the page. 

#### Testing

Vitest is now the default testing framework for frontend tests. It offers fast test execution, native support for Vue components, and seamless integration with Vite's build system.

```bash
# Frontend tests with Vitest
npm run test
npm run coverage

# Backend tests are still executed with PHPUnit
vendor/bin/phpunit
```

### Deprecations

#### Alert Stream

The alert stream service has been deprecated in favor of using Vue's reactive state management for handling alerts and notifications in the frontend. 

Previous versions of UserFrosting used an alert stream to send messages from the backend to the frontend, which were then displayed as alerts in the UI **after fetching them in a separate HTTP request**. The errors were not returned in the same response as the API call, which made it difficult to handle errors in a consistent way across the application, at the added benefit of allowing non-API calls to also trigger alerts, and obscuring the alert details for security reasons. This wasn't fully compliant with RESTful API design principles, as it separated the error handling from the main API response.

Now, all errors are returned in the same response as the API call, allowing for more consistent error handling and better compliance with RESTful API design principles. With the new Vue 3 frontend, API errors are sent directly to the frontend store and displayed to the users. It also allows for more flexibility in how alerts are displayed and managed in the frontend, as you can now use Vue's reactive data properties to control the display of alerts based on the API response, as well as the ability for the frontend to trigger its own alerts without needing to go through the backend.

The Alert Stream service is still available for backward compatibility, but it is deprecated and it is recommended to migrate to the new error handling approach for better performance and maintainability. Any alerts sent to it won't be displayed on the frontend. The Alert Stream will be removed in a future major version.

#### jQuery & Handlebars

The following are no longer included by default:

- ❌ **jQuery** (deprecated, use Vue 3)
- ❌ **Bootstrap** in AdminLTE (replaced by UIkit)
- ❌ **Handlebars** (deprecated, use Vue templates)
- ❌ **Several legacy npm packages** (see package.json diff)

**What This Means**:
- Existing jQuery code continues to work, but you **must** install jQuery manually if you want to keep using it (not recommended for new projects)

**Migration Path**:
- New development should use Vue 3
- Gradually convert jQuery to Vue 3 components
- Replace Handlebars with Vue's template syntax
- Use TypeScript for new features

#### Webpack Encore Support

**Status**: Maintained for **backward compatibility only**.

**What This Means**:
- Existing Webpack setups continue working, but you must explicitly configure your application to use Webpack instead of Vite (not recommended for new projects)
- New projects should use Vite
- Limited new features for Webpack

**Migration Path**:
- Plan migration to Vite for long-term projects
- Follow [Webpack to Vite migration guide](/advanced/webpack-encore)
- No immediate pressure to migrate stable applications
- Webpack support will eventually be removed in a future major version, so it's recommended to migrate to Vite as soon as possible.

## Unified Monorepo Architecture

While this change primarily affects the development of UserFrosting itself rather than your application, it's worth noting that the consolidation into a monorepo has streamlined the development process for contributors and maintainers. This means faster releases, better coordination between components, and a more consistent versioning strategy across all parts of UserFrosting.

**Before (5.1)**:
```
userfrosting/UserFrosting       (Skeleton)
userfrosting/framework          (Core Framework)
userfrosting/sprinkle-core      (Core Sprinkle)
userfrosting/sprinkle-account   (Account Sprinkle)
userfrosting/sprinkle-admin     (Admin Sprinkle)
userfrosting/theme-adminlte     (AdminLTE Theme)
```

**After (6.0)**:
```
userfrosting/monorepo           (Everything)
userfrosting/UserFrosting       (Skeleton - READ ONLY)
userfrosting/framework          (Core Framework - READ ONLY)
userfrosting/sprinkle-core      (Core Sprinkle - READ ONLY)
userfrosting/sprinkle-account   (Account Sprinkle - READ ONLY)
userfrosting/sprinkle-admin     (Admin Sprinkle - READ ONLY)
userfrosting/theme-pinkcupcake  (PinkCupcake Theme - READ ONLY)
```

**Impact**:
- **No impact for most users**, as this only affects development of UserFrosting itself, not your application
- Easier contribution workflow for those interested in developing UserFrosting
- Faster releases and bug fixes due to streamlined development process
- Simpler dependency management
- Consistent versioning across all components

The `monorepo` repository is primarily for development and contribution to UserFrosting itself, while the `userfrosting/userfrosting` repository serves as the main entry point for users to report issues, request features, and access discussions.

All Pull Requests should now be made against the `monorepo` repository, and the release process will handle publishing updates to all components together using pre-configured GitHub Actions. 

Issues should still be reported in the main `userfrosting/userfrosting` repository, as that is the public-facing issue tracker for users of the framework. 
