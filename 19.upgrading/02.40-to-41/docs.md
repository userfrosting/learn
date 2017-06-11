---
title: 4.0.x to 4.1.x
metadata:
    description: 
taxonomy:
    category: docs
---

## Overview
!TODO

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

## Breaking changes

### Major (likely to break your project)

- `composer.json` moved to top-level directory
- `sprinkles.json` moved to `app/` directory
- `core` sprinkle must be explicitly listed in `sprinkles.json`
- New CLI tools for installing.  Removed `migrations/install.php`.
- Models moved from `src/Model` to `src/Database/Models`
- Migrations moved from `migrations/` to `src/Database/Migrations`, and must extend the base `Migration` class to implement `up` and `down` methods.
- Sprinkle bootstrap classes completely redesigned.  They are now basically implementations of Symfony's `EventSubscriberInterface`, allowing the class to hook into the UF application lifecycle.
- Service providers are now automatically loaded, but they MUST be named `src/ServicesProvider/ServicesProvider.php`.

### Minor (should only break heavily customized projects)

- Middleware should be loaded in Sprinkle bootstrap classes in `onAddGlobalMiddleware`, instead of `index.php`.
- Exception handlers moved from `src/Handler` to `src/Error/Handler`.
- Interface for `ExceptionHandler` has changed.  There is no longer `ajaxHandler` and `standardHandler` - everything is handled via `handle` now.  Decisions about request type (ajax, standard) and error display mode (settings.displayErrorDetails, site.debug.ajax) are now delegated to the handlers.  Error rendering is delegated to `src/Error/Renderer` classes.
- `src/Facades/Facade` moved to `system/Facade`.
- `ufTable` now uses the pagination widget instead of the plugin, which changes the naming of their options if you happened to override any of the defaults.
- Renamed the following CSS classes:
  - `js-download-table` -> `js-uf-table-download`
  - `uf-table-info-messages` -> `uf-table-info` (styling) and `js-uf-table-info` (functional)
  - `tablesorter-pager` -> `tablesorter-pager` (styling) and `js-uf-table-pager` (functional)
  - `menu-table-column-selector-*` -> `uf-table-cs-*` (styling) and `js-uf-table-cs-*` (functional)
  - `table-search` -> `uf-table-search` (styling)
- Refactor groups and user routes. See [Issue #721](https://github.com/userfrosting/UserFrosting/issues/721). The `admin/` part of all routes in the `admin` sprinkle was removed. 

### Deprecations (still available but may be removed at any time)

- `src/Model/UFModel` deprecated; use `src/Database/Models/Model` now
  
## Upgrading to 4.1.x

Upgrading UserFrosting to `4.1.x` is as simple as getting the new files and updating dependencies, migrations and assets! If you first installed UserFrosting using git, a simple `git pull` or `git pull upstream/master`. Once you have the new files, simply use composer and the new cli tool to get up to date with everything else:

```bash
$ composer update
$ php bakery bake
```

### Migrating your sprinkles
!TODO

## Change Log

See the [Changelog](https://github.com/userfrosting/UserFrosting/blob/master/CHANGELOG.md#v410-alpha) for the complete list of changes included in this release. 
