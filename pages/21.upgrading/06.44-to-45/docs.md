---
title: 4.4.x to 4.5.x
metadata:
    description:
taxonomy:
    category: docs
---

## Overview

UserFrosting 4.5 focuses on cleaning up deprecated code, adding Composer 2 support and other small fix and improvements.

### Added Composer 2 Version
The [`wikimedia/composer-merge-plugin`](https://github.com/wikimedia/composer-merge-plugin) dependency was updated to from `^1.4.0` to `^2.1.0` ([#1117](https://github.com/userfrosting/UserFrosting/issues/1117)). This enabled Composer 2 support. Performance should be through the roof!

### PHP 7.1 Support Removal
As of UserFrosting 4.5.0, support for PHP version 7.1 has officially **been removed**. Support for PHP 7.1 was deprecated in 4.4.0 as it had reached [End Of Life](http://php.net/supported-versions.php). *PHP version 7.4 or higher is now recommended*.

#### A Note About PHP 8
UserFrosting does not currently support PHP 8 as many dependencies still need to be updated. Don't hesitate to contribute to the project by submitting a Pull Request on GitHub if you want to contribute to this.

### Minimum node version
As of UserFrosting 4.5.0, the NodeJS minimum requirement is changed from `>=10.12.0` to `^12.17.0 || >=14.0.0`. NPM minimum requirement is also changed from `>=6.0.0` to `>=6.14.4` ([#1138](https://github.com/userfrosting/UserFrosting/issues/1138)).

### Removal of Deprecated Code
The following deprecated class and methods have been removed. This could turn out to be a breaking change for your project if you were still using deprecated methods.

- Removed deprecated `UserFrosting\System\Bakery\Migration` (deprecated in 4.2.0).
- Removed deprecated `UserFrosting\Tests\DatabaseTransactions` (deprecated in 4.2.0).
- Removed deprecated `UserFrosting\Sprinkle\Core\Tests\ControllerTestCase` (deprecated in 4.2.2).
- Removed deprecated `UserFrosting\Sprinkle\Core\Model\UFModel` (deprecated in 4.1).
- Removed deprecated `UserFrosting\Sprinkle\Core\Sprunje\Sprunje::getResults` (deprecated in 4.1.7).
- Removed deprecated `UserFrosting\Sprinkle\Account\Database\Models\User::exists` (deprecated in 4.1.7).
- Removed deprecated `UserFrosting\Sprinkle\Core\Database\Models\Model::export` (deprecated in 4.1.8).
- Removed deprecated `UserFrosting\Sprinkle\Core\Database\Models\Model::queryBuilder` (deprecated in 4.1.8).
- Removed deprecated `UserFrosting\Sprinkle\Core\Database\Relations\Concerns\Unique::withLimit` (deprecated in 4.1.7).
- Removed deprecated `UserFrosting\Sprinkle\Core\Database\Relations\Concerns\Unique::withOffset` (deprecated in 4.1.7).
- Removed deprecated `UserFrosting\Sprinkle\Core\Error\RendererWhoopsRenderer::getResourcesPath`.
- Removed deprecated `UserFrosting\Sprinkle\Core\Error\RendererWhoopsRenderer::setResourcesPath`.
- Removed deprecated Handlebar `ifCond` (Deprecated in 4.1).
- Removed migration seed.
- Removed support for migration with non static `$dependencies` properties.
- Removed support for deprecared `determineRedirectOnLogin` service (deprecated in 4.1.10).

### Other Misc Changes & Fixes
- Implement findInt ([#1117](https://github.com/userfrosting/UserFrosting/issues/1117)).
- Replace `getenv()` with `env()` ([#1121](https://github.com/userfrosting/UserFrosting/issues/1121)). 
- Added more SMTP options in env and `setup:smtp` bakery command ([#1077](https://github.com/userfrosting/UserFrosting/issues/1077).
- Replaced `UserFrosting\Sprinkle\Core\Bakery\Helper\NodeVersionCheck` with new `UserFrosting\Sprinkle\Core\Util\VersionValidator` class.
- Changed `.php_cs` to `.php_cs.dist`.
- Changed `phpunit.xml` to `phpunit.xml.dist`.
- Replaced AdminLTE credit in default footer (old link was dead).

### Complete change Log

See the [Changelog](https://github.com/userfrosting/UserFrosting/blob/master/CHANGELOG.md#v450) for the complete list of changes included in this release.

## Upgrading to 4.5.x

Upgrading UserFrosting to `4.5.x` is as simple as getting the new files and updating dependencies, migrations and assets! If you first installed UserFrosting using git, a simple `git pull` or `git pull upstream master`. Once you have the new files, simply use composer and [Bakery](/cli) to get up to date with everything else:

```bash
$ composer update
$ php bakery bake
```

### Migrating your Sprinkles
Upgrating your sprinkle for UserFrosting 4.5.0 should be as simple as making sure you don't use any of the deprecated code that was removed. This souldn't be an issue if you already upgraded your code when the class/methods where initially deprecated. Otherwise, you can go through the previous version release notes, or search for the list above and upgrade the code.
