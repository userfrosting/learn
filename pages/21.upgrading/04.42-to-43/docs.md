---
title: 4.2.x to 4.3.x
metadata:
    description:
taxonomy:
    category: docs
---

## Overview

UserFrosting 4.3 focuses on updating dependencies and removing support for older versions of PHP.

### Updated Composer Dependencies
- Updated Laravel Illuminate packages to 5.8
- Updated Twig to 2.11
- Updated PHPUnit to 7.5
- Updated Mockery to 1.2
- Updated nikic/php-parser to 4.2.2
- Updated PHPMailer/PHPMailer to 6.0.7
- Updated league/csv to 9.2.1
- Updated symfony/console to 4.3
- Updated vlucas/phpdotenv to 3.4.0

### Updated Frontend Dependencies
- Updated handlebar from 3.0.x to 4.1.2
- Updated AdminLTE theme to 2.4.15
- Updated Font Awesome to 5.9

### Key new features
- Users are no longer required to be in a group.
- Added option to set password manually when creating a new user through the admin dashboard.
- Separated `BakeCommand` class into multiple methods to make it easier for sprinkle to add custom command to the `bake` command.

### Breaking changes

#### Major (likely to break your project)
- Updated Font Awesome to 5.9. [See Details Here](#font-awesome).
- Removed `league/flysystem-aws-s3-v3` and `league/flysystem-rackspace` dependencies from the `core` sprinkle. If you are using `s3` or `rackspace` for File Storage, you must now include these packages in your custom Sprinkle.

#### Minor (should only break heavily customized projects)
- Updated AdminLTE to `2.4.x`. Reference the [AdminLTE upgrade guide](https://adminlte.io/docs/2.4/upgrade-guide) for change details.
- PostgreSQL now performs case-insensitive comparison when creating new users. (You can no longer create two different users with the same email address E.g. user@userfrosting.com and User@userfrosting.com)

### PHP 5.6 and 7.0 support dropped

As of UserFrosting 4.3.0, support for PHP version 5.6 and 7.0 is officially dropped as both versions have reached [End Of Life](http://php.net/supported-versions.php). The minimum PHP version is now **7.1**, but 7.2 or higher is recommended.

### Complete change Log

See the [Changelog](https://github.com/userfrosting/UserFrosting/blob/master/CHANGELOG.md#v430) for the complete list of changes included in this release.


## Upgrading to 4.3.x

Upgrading UserFrosting to `4.3.x` is as simple as getting the new files and updating dependencies, migrations and assets! If you first installed UserFrosting using git, a simple `git pull` or `git pull upstream master`. Once you have the new files, simply use composer and [Bakery](/cli) to get up to date with everything else:

```bash
$ composer update
$ php bakery bake
```

### Migrating your Sprinkles

#### New Composer Dependencies

While the [updated Composer dependencies](#updated-composer-dependencies) have all been tested and integrated into the core UF features and code, they may still introduce a conflict in your your own sprinkle. For example, if you sprinkle requires an additional Composer requirement not compatible with the new dependencies, you may need to update your sprinkle `composer.json` requirements to newer version.

Changes in the dependencies might also introduce unexpected behavior or errors in your sprinkle, even if you're not referencing directly. For example, changes introduced between the old Eloquent version (5.4) and the new version now bundled with UserFrosting (5.8) might break your database queries [for some edge cases](https://laravel.com/docs/5.8/upgrade#model-names-ending-with-irregular-plurals). Checkout the list of updated dependencies and their respective changelog.

#### Font Awesome

UserFrosting 4.3 now include **Font Awesome 5.9** by default instead of 4.7 (Font Awesome 4 is so 2017). Breaking changes include new [icon prefix](https://fontawesome.com/how-to-use/on-the-web/setup/upgrading-from-version-4#changes), the removal of icon aliases and many icon name changes to align with the new standards. Custom sprinkles will need to be updated to use the new icon names. You can check the [upgrade guide](https://fontawesome.com/how-to-use/on-the-web/setup/upgrading-from-version-4#name-changes) for a comprehensive list of icon name changes.

#### Custom Bake Command

Any sprinkle extending the default `bake` Bakery command may need updating. Since the `account` sprinkle now [extend the `BakeCommand` class from the `core` Sprinkle](https://github.com/userfrosting/UserFrosting/blob/4b8b1289c6adeab2c68979286e59dcd15366b1ec/app/sprinkles/account/src/Bakery/BakeCommand.php), any custom sprinkle extending the `BakeCommand` class from the `core` sprinkle should now extend same class from the `Account` Sprinkle instead. Otherwise, the `create-admin` command won't be part of `bake` anymore because of PHP class inheritance.

Also, note the `BakeCommand` class has been split into multiple methods to make it easier for your sprinkle to add custom sub-command to the _bake_ command at the right place in the _baking_ process. See the `create-admin` command [in the `account` sprinkle for an example](https://github.com/userfrosting/UserFrosting/blob/4b8b1289c6adeab2c68979286e59dcd15366b1ec/app/sprinkles/account/src/Bakery/BakeCommand.php).


<!-- ## Common Upgrade Problems -->

<!-- ### [...] -->
