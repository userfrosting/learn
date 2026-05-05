---
title: 4.5.x to 4.6.x
metadata:
    description: Upgrade guide from version 4.5.x to 4.6.x
taxonomy:
    category: docs
---

[notice=warning]As of 4.6.3, `site.uri.public` must be explicitly set in your [`production` configuration](https://github.com/userfrosting/UserFrosting/blob/15d713a1fa2e9a67000b2a9a9413473f5c51da4d/app/sprinkles/core/config/production.php#L56) to avoid security issues. If not set, some links and emails will not work properly. See [Going Live](/going-live/vps-production-environment/application-setup#set-the-base-url) for more info.[/notice]

## Overview

UserFrosting 4.6 focuses on adding PHP 8 support and upgrading Laravel dependencies. Individual UserFrositng supporting repository are also now available inside `userfrosting/framework`.

### Changed PHP Requirements
UserFrosting 4.6.0 add support for PHP 8 and remove support for PHP version 7.2. Support for PHP 7.2 was deprecated in 4.5.0 as it had reached [End Of Life](http://php.net/supported-versions.php). **PHP version 8.0 is now recommended**.

### Upgraded Dependencies
 - Upgrade all Laravel packages to ^8.x from ^5.8.
 - Upgrade `vlucas/phpdotenv`to ^5.3 from ^3.4.
 - Upgrade `symfony/console` to ^5.1 from ^4.3.
 - Upgrade `phpunit/phpunit` to ^9.5

### UserFrosting Framework
The following individual UserFrosting supporting repository have been replaced with a monolitic `userfrosting/framework` repository. The usage and namespaces remains the same, so it shoudn't change anything for your Sprinkles. 
 - `userfrosting/Assets`
 - `userfrosting/Cache`
 - `userfrosting/Config`
 - `userfrosting/Fortress`
 - `userfrosting/i18n`
 - `userfrosting/Session`
 - `userfrosting/Support`
 - `userfrosting/UniformResouceLocator`

### Support for built-in PHP Server
Built-in PHP Server can now be used for developement purposes, as long as assets are present (if node is installed locally) and a database available (sqlite file can be used for this). Run the following command in terminal to start localhost web server :

```
php -S localhost:8888 -t public public/index.php
```

UserFrosting will be available at <http://localhost:8888>.

### Per user theme disabled by default
Per user theme, using the `$user->theme` database field, is now deprecated and disabled by default to increase performances. To enable back, change `per_user_theme` config to `true` (See [#1131](https://github.com/userfrosting/UserFrosting/issues/1131)). This feature will be removed in future version.

### Other Misc Changes & Fixes
- Bakery command `execute` method now requires to return an int (Symfony 4.4 upgrade : https://symfony.com/blog/new-in-symfony-4-4-console-improvements).
- `UserFrosting\Sprinkle\Core\Database\EloquentBuilder` now uses `Illuminate\Database\Eloquent\Concerns\QueriesRelationships` Trait instead of manually implementing `withSum`, `withAvg`, `withMin`, `withMax` & `withAggregate`. See Laravel documentation for usage change.

### Complete change Log

See the [Changelog](https://github.com/userfrosting/UserFrosting/blob/master/CHANGELOG.md#v460) for the complete list of changes included in this release.

## Upgrading to 4.6.x

Upgrading UserFrosting to `4.6.x` is as simple as getting the new files and updating dependencies, migrations and assets! If you first installed UserFrosting using git, a simple `git pull` or `git pull upstream master`. Once you have the new files, simply use composer and [Bakery](/cli) to get up to date with everything else:

```bash
$ composer update
$ php bakery bake
```

### Migrating your Sprinkles

#### Dependencies upgrade
While the [updated Composer dependencies](#upgraded-dependencies) have all been tested and integrated into the core UF features and code, they may still introduce a conflict in your your own sprinkle. For example, if you sprinkle requires an additional Composer requirement not compatible with the new dependencies, you may need to update your sprinkle `composer.json` requirements to newer version.

Changes in the dependencies might also introduce unexpected behavior or errors in your sprinkle, even if you're not referencing directly. For example, changes introduced between the old Eloquent version (5.8) and the new version now bundled with UserFrosting (8.x) might break your database queries for some edge cases. Checkout the list of updated dependencies and their respective changelog.

#### Custom bakery command
To comply with changes in Symfony 4.4, all custom bakery command (the `execute` method) are now required to return an *integer* value. A `0` exit status means that the command run successfully and any other number means some error. [See here for more information](https://symfony.com/blog/new-in-symfony-4-4-console-improvements).
