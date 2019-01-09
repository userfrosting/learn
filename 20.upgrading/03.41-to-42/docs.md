---
title: 4.1.x to 4.2.x
metadata:
    description:
taxonomy:
    category: docs
---

## Overview

UserFrosting 4.2 brings improved organization of the codebase, a new migrator and seeder as well as improved assets management.

### Key new features

- Include [Vagrant and Homestead](/installation/environment/homestead) integration directly inside UF ([#829])
- New `migrate:status` Bakery command
- `migrate` and `migrate:*` Bakery command now require confirmation before execution when in production mode.
- Implement `withRaw`, `withSum`, `withAvg`, `withMin`, `withMax` (see https://github.com/laravel/framework/pull/16815)
- Added arguments to the `create-admin` and `setup` Bakery commands so it can be used in a non-interactive way ([#808])
- Added new `filesystem` service ([#869])
- Added new `Seeder` and `seed` Bakery command
- Added `withTestUser` trait for helper methods when running tests requiring a user
- Added `ControllerTestCase` special test case to help testing controllers
- Improved overall test coverage and added coverage config to `phpunit.xml`
- Added code style config (`.php_cs`) and instructions for PHP-CS-Fixer in Readme
- Add cache facade (Ref [#838])
- Added `test:mail` Bakery Command
- Add support for other config['mailer'] options ([#872]; Thanks @apple314159 !)
- Improved `route:list` Bakery command
- Added support for npm dependencies on the frontend with auditting for known vulnerabilities
- Rewrote asset processing to minimise file sizes, drastically reduce IO, and improve maintainability
- Rewrote frontend dependency installation to prevent duplication and detect incompatibilities

### Breaking changes

#### Major (likely to break your project)
- The console IO instance is not available anymore in migrations
- Removed the `io` property from migration classes
- Removed Bakery `projectRoot` property. Use the `\UserFrosting\ROOT_DIR` constant instead

#### Minor (should only break heavily customized projects)
- `dev` environment changed to `debug`  ([#653])
- Move User registration out of the `AccountController` ([#793])

#### Deprecations (still available but may be removed at any time)
- Migrations should now extends `UserFrosting\Sprinkle\Core\Database\Migration` instead of `UserFrosting\System\Bakery\Migration`
- Migrations dependencies property should now be a static property
- Deprecated migration `seed` method. Database seeding should now be done using the new Seeder
- Trait `\UserFrosting\Tests\DatabaseTransactions` has been deprecated. Tests should now use the `\UserFrosting\Sprinkle\Core\Tests\DatabaseTransactions` trait instead. (#826)
- Makes the `semantic versioning` part of a migration class optional. Migrations classes can now have the `UserFrosting\Sprinkle\{sprinkleName}\Database\Migrations` namespace, or any other sub-namespace

## New Node.js and NPM requirements

UserFrosting 4.2.x now requires [Node.js](https://nodejs.org/en/) 10.12.0 or above and NPM 6.0.0 or above.

To update Node.js and NPM on most UNIX system, you can use the [n](https://www.npmjs.com/package/n) NPM Package :
```
sudo npm cache clean -f
sudo npm install -g n
sudo n -q lts
```

This will install the latest LTS (Long Term Support) version of Node.js.

## PHP 5.6 and 7.0 Support Deprecation

As of UserFrosting 4.2.0, support for PHP version 5.6 and 7.0 is officially deprecated. While you can still use UserFrosting 4.2.x with PHP 7.0 or earlier, upgrading to PHP 7.2 or above is highly recommended as both PHP 5.6 and 7.0 have reach [End Of Life](http://php.net/supported-versions.php) since Jan. 1st 2019.

**The next version of UserFrosting (4.3.x) won't support PHP 5.6 or 7.0**


## Upgrading to 4.2.x

Upgrading UserFrosting to `4.2.x` is as simple as getting the new files and updating dependencies, migrations and assets! If you first installed UserFrosting using git, a simple `git pull` or `git pull upstream master`. Once you have the new files, simply use composer and [Bakery](/cli) to get up to date with everything else:

```bash
$ composer update
$ php bakery bake
```

### Assets Packages

!TODO

### Migrating your Sprinkles

#### Migrations

1. Migrations should be updated to extends `UserFrosting\Sprinkle\Core\Database\Migration` instead of `UserFrosting\System\Bakery\Migration`.

2. Any migrations using the `io` property to interact with the user through the command line needs to be updated. Since migrations can now be run outside of the CLI, migrations can't make use of the `io` anymore. Any task requiring user input should be moved to a [custom Bakery command](/cli/custom-commands).

#### Seeds

Database seeding should now be perform using the new Seeder class and `seed` bakery command. This new seeder allows for multiple seeding, detached from the migration system. It can also be used along Unit Tests.

Seeding performed in migrations will still work, but support for database seeding inside migrations will be removed in future versions. See the [Database Seeding](/database/seeding) documentation on how to move your seeds to the new Seeder class.


## Common Upgrade Problems

### Assets installation has failed

If assets installation fail with a similar message :

```
Error: Cannot find module 'acorn'
    at Object.<anonymous> (/home/vagrant/userfrosting/build/node_modules/acorn-dynamic-import/lib/index.js:18:14)
```

Simply delete the `build/package-lock.json` file and the `build/node_modules/` directory. You can then run the Bake or `build-assets` command again.

## Complete change Log

See the [Changelog](https://github.com/userfrosting/UserFrosting/blob/master/CHANGELOG.md#v420) for the complete list of changes included in this release.

[#653]: https://github.com/userfrosting/UserFrosting/issues/653
[#793]: https://github.com/userfrosting/UserFrosting/issues/793
[#808]: https://github.com/userfrosting/UserFrosting/issues/808
[#826]: https://github.com/userfrosting/UserFrosting/issues/826
[#829]: https://github.com/userfrosting/UserFrosting/issues/829
[#838]: https://github.com/userfrosting/UserFrosting/issues/838
[#869]: https://github.com/userfrosting/UserFrosting/issues/869
[#872]: https://github.com/userfrosting/UserFrosting/issues/872
