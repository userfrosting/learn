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
- New `sprinkle:list` Bakery command
- New `test:mail` Bakery Command
- New `NoCache` middleware to prevent caching of routes with dynamic content
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
- Add support for other config['mailer'] options ([#872]; Thanks @apple314159 !)
- Improved `route:list` Bakery command
- Added support for npm dependencies on the frontend with auditing for known vulnerabilities
- Rewrote asset processing to minimize file sizes, drastically reduce IO, and improve maintainability
- Rewrote frontend dependency installation to prevent duplication and detect incompatibilities
- Added Greek locale (Thanks @lenasterg!; [#940])

### Breaking changes

#### Major (likely to break your project)
- Removed the `io` property from migration classes. The console IO instance is not available anymore in migrations
- Removed Bakery `projectRoot` property. Use the `\UserFrosting\ROOT_DIR` constant instead
- Removed `UserFrosting\System\Bakery\DatabaseTest` trait for custom Bakery command, use `UserFrosting\Sprinkle\Core\Bakery\Helper\DatabaseTest` instead.
- Removed `UserFrosting\System\Bakery\ConfirmableTrait` trait for custom Bakery command, use `UserFrosting\Sprinkle\Core\Bakery\Helper\ConfirmableTrait` instead.

#### Minor (should only break heavily customized projects)
- `dev` environment changed to `debug` ([#653])
- Move User registration out of the `AccountController` ([#793])

#### Deprecations (still available but may be removed at any time)
- Migrations should now extends `UserFrosting\Sprinkle\Core\Database\Migration` instead of `UserFrosting\System\Bakery\Migration`
- Migrations dependencies property should now be a static property
- Deprecated migration `seed` method. Database seeding should now be done using the new [Seeder](/database/seeding).
- Trait `\UserFrosting\Tests\DatabaseTransactions` has been deprecated. Tests should now use the `\UserFrosting\Sprinkle\Core\Tests\DatabaseTransactions` trait instead. ([#826])
- Makes the `semantic versioning` part of a migration class optional. Migrations classes can now have the `UserFrosting\Sprinkle\{sprinkleName}\Database\Migrations` namespace, or any other sub-namespace

### New Node.js and NPM requirements

UserFrosting 4.2.x now requires [Node.js](https://nodejs.org/en/) 10.12.0 or above and NPM 6.0.0 or above.

To update Node.js and NPM on most UNIX system, you can use the [n](https://www.npmjs.com/package/n) NPM Package :
```
sudo npm cache clean -f
sudo npm install -g n
sudo n -q lts
```

This will install the latest LTS (Long Term Support) version of Node.js.

### Bower deprecation, new NPM support

Bower has been deprecated since 2017, and with NPM support landing in UserFrosting, Bower is now deprecated here as well. In the future, support for it will be removed.

Frontend dependencies used by UserFrosting are now retrieved from [NPM](https://www.npmjs.com/). If you depend on these you may find certain files have shifted around, verifying references are correct is recommended. UserFrosting can help in this endeavor, as missing resources referenced using the locator infrastructure will produce a server error.

To facilitate an easier transition and accommodate the complexities associated with the node module resolution logic that permits duplicate indirect dependencies, compatible main entry points will be run through [Browserify](https://www.npmjs.com/package/browserify) to resolve `require` imports.

Dependency conflicts between individual sprinkles for `bower.json` may now occur. If this occurs you'll need to update the [semver](https://semver.org/) range to resolve or force a specific version using the `resolutions` attribute.

Finally, frontend dependencies are now located at `app/assets/bower_components` for Bower and `app/assets/node_modules` for NPM. Running `php bakery assets-install` will automatically remove dependencies in the old location. This does not affect asset resolution.

### PHP 5.6 and 7.0 support deprecation

As of UserFrosting 4.2.0, support for PHP version 5.6 and 7.0 is officially deprecated. While you can still use UserFrosting 4.2.x with PHP 7.0 or earlier, upgrading to PHP 7.2 or above is highly recommended as both PHP 5.6 and 7.0 have reached [End Of Life](http://php.net/supported-versions.php) since Jan. 1st 2019.

[notice=warning]**The next major version of UserFrosting (4.3.x) won't support PHP 5.6 or 7.0**[/notice]

### Complete change Log

See the [Changelog](https://github.com/userfrosting/UserFrosting/blob/master/CHANGELOG.md#v420) for the complete list of changes included in this release.


## Upgrading to 4.2.x

Upgrading UserFrosting to `4.2.x` is as simple as getting the new files and updating dependencies, migrations and assets! If you first installed UserFrosting using git, a simple `git pull` or `git pull upstream master`. Once you have the new files, simply use composer and [Bakery](/cli) to get up to date with everything else:

```bash
$ composer update
$ php bakery bake
```

### Migrating your Sprinkles

#### Frontend Assets

Since Bower is now deprecated, it is recommended to migrate your sprinkle third party frontend dependencies to [**Yarn**](https://yarnpkg.com/). This can be accomplished by renaming your sprinkle `bower.json` file to Yarn's `package.json`.

Some package can be named differently between Bower and Yarn. For example, the **Bootstrap 3 Typeahead** package is named `bootstrap3-typeahead` in Bower, but `bootstrap-3-typeahead` in Yarn. Refer to [**Yarn**](https://yarnpkg.com/) website to see if your sprinkle third party dependencies need updating.

#### Migrations

Migrations should be updated to extends `UserFrosting\Sprinkle\Core\Database\Migration` instead of `UserFrosting\System\Bakery\Migration`. Migration that have already been run don't need to be run again.

Any migrations using the `io` property to interact with the user through the command line needs to be updated. Since migrations can now be run outside of the CLI, migrations can't make use of the `io` anymore. Any task requiring user input should be moved to a [custom Bakery command](/cli/custom-commands) or seed.

Finally, the [`$dependencies` property](/database/migrations#dependencies) should now be static. Non-static property will still work, but the support for it is deprecated which means it might be removed in a future update. To make sure your migrations are compatible with future version, simply change `public $dependencies = [ ... ];` to `public static $dependencies = [ ... ];` in your migration file. This can also be applied safely to migrations which have already been run, as it doesn't affect the data or table structure.

#### Seeds

Database seeding should now be perform using the new Seeder class and `seed` bakery command. This new seeder allows for multiple seeding, detached from the migration system. It can also be used along Unit Tests.

Seeding performed in migrations will still work, but support for database seeding inside migrations will be removed in future versions. See the [Database Seeding](/database/seeding) documentation on how to move your seeds to the new Seeder class.


## Common Upgrade Problems

### Assets installation has failed

If assets installation fail, simply delete the `build/package-lock.json` file and the `build/node_modules/` directory. You can then run the Bake or `build-assets` command again.

### Foreign key constraint errors in migrations

Due to changes in the logic used to load migrations classes in 4.2, the order in which they are run may differ from previous releases. This may result in new **foreign key constraint errors**. If this happens in your own custom migrations, it may be a sign of [missing dependencies](/database/migrations#dependencies) (Those migrations working under 4.1 may just be a coincidence!)

To fix this, make sure your migrations define the [appropriate dependencies](/database/migrations#dependencies). Also make sure those dependencies are up to date. You might also want to change the `$dependencies` property [to a static one at the same time](/upgrading/41-to-42#migrations). Don't hesitate to [seek help](/troubleshooting/getting-help) if you need assistance on this or encounter a different migration related issue.


[#653]: https://github.com/userfrosting/UserFrosting/issues/653
[#793]: https://github.com/userfrosting/UserFrosting/issues/793
[#808]: https://github.com/userfrosting/UserFrosting/issues/808
[#826]: https://github.com/userfrosting/UserFrosting/issues/826
[#829]: https://github.com/userfrosting/UserFrosting/issues/829
[#838]: https://github.com/userfrosting/UserFrosting/issues/838
[#869]: https://github.com/userfrosting/UserFrosting/issues/869
[#872]: https://github.com/userfrosting/UserFrosting/issues/872
