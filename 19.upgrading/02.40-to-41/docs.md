---
title: 4.0.x to 4.1.x
metadata:
    description: 
taxonomy:
    category: docs
---

## Overview

UserFrosting 4.1 brings improved organization of the codebase, factoring out the Sprinkle management system from the `core` Sprinkle itself.  The **Bakery** makes it easier than ever to get UserFrosting up and running, as well as handling other command-line tasks from a unified interface.

We've also switched to an event-driven application lifecycle that uses [Symfony's event dispatcher](http://symfony.com/doc/current/components/event_dispatcher.html) to allow each Sprinkle to hook into critical points in the UserFrosting lifecycle.

## New features

### CLI tools

New tools for setting up UF, checking requirements, installing, and asset management.  They're great!

### Hooking into the main UF application lifecycle

The main lifecycle logic (Sprinkle loading) has been moved out of the `core` Sprinkle and into `app/system`.  Sprinkles can hook into the main UF application lifecycle via their bootstrap class.  Available methods so far:

- onAppInitialize
- onAddGlobalMiddleware
- onSprinklesInitialized
- onSprinklesAddResources
- onSprinklesRegisterServices

### Support for unit testing!

### Custom error rendering

With a new WhoopsRenderer for attractive, informative debugging pages!

### Listable sprunjing

Sprunjes can now generate enumerations of unique values for a specific field.  Very useful in generating lists of options, for example, in tablesorter selection menus.

### Sprunje now supports `_all` filter, which will search all filterable fields by default.

### Per user cache instance & Redis cache driver

See [Cache](http://learn.local/advanced/cache/usage) for more info.

## Breaking changes

### Major (likely to break your project)

- `composer.json` moved to top-level directory
- `sprinkles.json` moved to `app/` directory
- `bundle.config.json` renamed to `asset-bundles.json`
- `core` sprinkle must be explicitly listed in `sprinkles.json`
- New CLI tools for installing.  Removed `migrations/install.php`.
- Models moved from `src/Model` to `src/Database/Models`
- Migrations moved from `migrations/` to `src/Database/Migrations`, and must extend the base `Migration` class to implement `up` and `down` methods.
- Sprinkle bootstrap classes completely redesigned.  They are now basically implementations of Symfony's `EventSubscriberInterface`, allowing the class to hook into the UF application lifecycle.
- Service providers are now automatically loaded, but they MUST be named `src/ServicesProvider/ServicesProvider.php`.
- Default assets have been reorganized.  `assets/local` has been renamed `assets/userfrosting`, and subdirectories in this directory have been rearranged as well.

### Minor (should only break heavily customized projects)

- Refactor groups and user routes. See [Issue #721](https://github.com/userfrosting/UserFrosting/issues/721). The `admin/` part of all routes in the `admin` sprinkle was removed. 
- Middleware should be loaded in Sprinkle bootstrap classes in `onAddGlobalMiddleware`, instead of `index.php`.
- Exception handlers moved from `src/Handler` to `src/Error/Handler`.
- Interface for `ExceptionHandler` has changed.  There is no longer `ajaxHandler` and `standardHandler` - everything is handled via `handle` now.  Decisions about request type (ajax, standard) and error display mode (settings.displayErrorDetails, site.debug.ajax) are now delegated to the handlers.  Error rendering is delegated to `src/Error/Renderer` classes.
- The member variables in `HttpException` have been renamed.  `http_error_code => httpErrorCode`, and `default_message => defaultMessage`.  If you have any custom child exceptions that extend `HttpException`, you'll need to update these variable names to work properly with the `HttpExceptionHandler`.
- `src/Facades/Facade` moved to `system/Facade`.
- All loggers log to a common `userfrosting.log` now
- `ufTable` now uses the pagination widget instead of the plugin, which changes the naming of their options if you happened to override any of the defaults.
- Renamed the following CSS classes:
  - `js-download-table` -> `js-uf-table-download`
  - `uf-table-info-messages` -> `uf-table-info` (styling) and `js-uf-table-info` (functional)
  - `tablesorter-pager` -> `tablesorter-pager` (styling) and `js-uf-table-pager` (functional)
  - `menu-table-column-selector-*` -> `uf-table-cs-*` (styling) and `js-uf-table-cs-*` (functional)
  - `table-search` -> `uf-table-search` (styling)
- `ufForm` now sets a `dataType` in the call to `.ajax` - we've customized it to handle malformed JSON responses, but it could still cause problems for some people
- Refactored the cache config values.

### Deprecations (still available but may be removed at any time)

- `src/Model/UFModel` deprecated; use `src/Database/Models/Model` now
- `ifCond` Handlebars helper deprecated; use `ifx` instead
  
## Upgrading to 4.1.x

Upgrading UserFrosting to `4.1.x` is as simple as getting the new files and updating dependencies, migrations and assets! If you first installed UserFrosting using git, a simple `git pull` or `git pull upstream/master`. Once you have the new files, simply use composer and the new cli tool to get up to date with everything else:

```bash
$ composer update
$ php bakery bake
```

### Migrating your Sprinkles

1. Rename your Sprinkle's `bundle.config.json` to `asset-bundles.json`.
2. Rename your service provider class (if you have one) to `src/ServicesProvider/ServicesProvider.php`.  This will let UserFrosting load it automatically.
3. Change your Sprinkle's bootstrapper class (if you have one) - for example, `src/Site.php`.  This should now extend `UserFrosting\System\Sprinkle\Sprinkle`.  Get rid of the `init` method.  Instead, you should create methods to [hook into the UserFrosting lifecycle](/advanced/application-lifecycle), if necessary.  Otherwise, you can leave this class empty, or delete it entirely.
4. Move your models from `src/Model` to `src/Database/Models`, and change them to extend the base 'UserFrosting\Sprinkle\Core\Database\Models\Model' class.  Keep in mind that when you move your classes to a different directory, you need to change their `namespace` as well to comply with PSR-4 autoloading rules.
5. For each database table you create in your `migrations/*` file, create a new class in `src/Database/Migrations` instead.  This should extend the base `UserFrosting\System\Bakery\Migration` class to implement `up` and `down` methods.  See [Migrations](/database/migrations) for more information.
6. If you reference any default UserFrosting assets in your templates or asset bundles, you will need to update their paths (see Major Changes above).
7. Check the "minor breaking changes" section above, to see if there are any other changes that might affect your Sprinkle.

The database schema have not changed from UF 4.0 - there is no need to upgrade your database.

## Change Log

See the [Changelog](https://github.com/userfrosting/UserFrosting/blob/master/CHANGELOG.md#v410-alpha) for the complete list of changes included in this release. 
