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
!TODO

## Breaking changes

- `composer.json` moved to top-level directory
- `sprinkles.json` moved to `app/` directory
- `core` sprinkle must be explicitly listed in `sprinkles.json`
- New CLI tools for installing.  Removed `migrations/install.php`.
- Models moved from `src/Model` to `src/Database/Models`
- `src/Model/UFModel` deprecated; use `src/Database/Models/Model` now
- Migrations moved from `migrations/` to `src/Database/Migrations`, and must extend the base `Migration` class to implement `up` and `down` methods.
- Sprinkle bootstrap classes completely redesigned.  They are now basically implementations of Symfony's `EventSubscriberInterface`, allowing the class to hook into the UF application lifecycle.
- Middleware should be loaded in Sprinkle bootstrap classes in `onAddGlobalMiddleware`, instead of `index.php`.
- Service providers are now automatically loaded, but they MUST be named `src/ServicesProvider/ServicesProvider.php`.
- Exception handlers moved from `src/Handler` to `src/Error/Handler`.
- Interface for `ExceptionHandler` has changed.  There is no longer `ajaxHandler` and `standardHandler` - everything is handled via `handle` now.  Decisions about request type (ajax, standard) and error display mode (settings.displayErrorDetails, site.debug.ajax) are now delegated to the handlers.  Error rendering is delegated to `src/Error/Renderer` classes.
- `src/Facades/Facade` moved to `system/Facade`.
- `ufTable` now uses the pagination widget instead of the plugin, which changes the naming of their options if you happened to override any of the defaults.

## Change Log

See the [Changelog](https://github.com/userfrosting/UserFrosting/blob/master/CHANGELOG.md#v410-alpha) for the complete list of changes included in this release. 
