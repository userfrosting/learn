---
title: 4.1.x to 4.2.x
metadata:
    description:
taxonomy:
    category: docs
---

## Overview

UserFrosting 4.2 brings improved organization of the codebase, a new migrator and seeder as well as improved assets management.

## New features

- Include [Vagrant and Homestead](/installation/environment/homestead) integration directly inside UF (#829)
- Added `migrate:status` Bakery command
- Added `RefreshDatabase` test Trait to use a fresh database for a test
- Added `TestDatabase` test Trait to use the in memory database for a test
- `migrate` and `migrate:*` Bakery command now require confirmation before execution when in production mode.
- Implement `withRaw`, `withSum`, `withAvg`, `withMin`, `withMax` (see https://github.com/laravel/framework/pull/16815)
- Added arguments to the `create-admin` and `setup` Bakery commands so it can be used in a non-interactive way (#808)

## Breaking changes

### Major (likely to break your project)

### Minor (should only break heavily customized projects)
- The console IO instance is not available anymore in migrations
- Removed the `io` property from migration classes
- Removed Bakery `projectRoot` property. Use the `\UserFrosting\ROOT_DIR` constant instead
- Removed `pretend` option from Bakery `migrate:refresh` and `migrate:reset` commands

### Deprecations (still available but may be removed at any time)
- Move User registration out of the `AccountController` (#793)
- Makes the `semantic versioning` part of a migration class optional. Migrations classes can now have the `UserFrosting\Sprinkle\{sprinkleName}\Database\Migrations` namespace, or any other sub-namespace
- Migrations should now extends `UserFrosting\Sprinkle\Core\Database\Migration` instead of `UserFrosting\System\Bakery\Migration`
- Migrations dependencies property should now be a static property
- Deprecated migration `seed` method. Database seeding should now be done using the new Seeder
- Trait `\UserFrosting\Tests\DatabaseTransactions` has been deprecated. Tests should now use the `\UserFrosting\Sprinkle\Core\Tests\DatabaseTransactions` trait instead. (#826)


## Upgrading to 4.2.x

Upgrading UserFrosting to `4.2.x` is as simple as getting the new files and updating dependencies, migrations and assets! If you first installed UserFrosting using git, a simple `git pull` or `git pull upstream master`. Once you have the new files, simply use composer and [Bakery](/cli) to get up to date with everything else:

```bash
$ composer update
$ php bakery bake
```

### Migrating your Sprinkles

#### Migrations

1. Migrations should be updated to extends `UserFrosting\Sprinkle\Core\Database\Migration` instead of `UserFrosting\System\Bakery\Migration`.

2. Any migrations using the `io` property to interact with the user through the command line needs to be updated. Since migrations can now be run outside of the CLI, migrations can't make use of the `io` anymore. Any task requiring user input should be moved to a [custom Bakery command](/cli/custom-commands).

#### Seeds

Database seeding should now be perform using the new Seeder class and `seed` bakery command. This new seeder allows for multiple seeding, detached from the migration system. It can also be used along Unit Tests.

Seeding performed in migrations will still work, but support for database seeding inside migrations will be removed in future versions. See the [Database Seeding](/database/seeding) documentation on how to move your seeds to the new Seeder class.

#### Additional tasks

## Change Log

See the [Changelog](https://github.com/userfrosting/UserFrosting/blob/master/CHANGELOG.md#v420) for the complete list of changes included in this release.
