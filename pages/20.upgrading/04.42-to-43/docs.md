---
title: 4.2.x to 4.3.x
metadata:
    description:
taxonomy:
    category: docs
---

## Overview

UserFrosting 4.3 focus on updating dependencies.

### Key new features



### Breaking changes

#### Major (likely to break your project)
-

#### Minor (should only break heavily customized projects)
-

#### Deprecations (still available but may be removed at any time)
-

### PHP 5.6 and 7.0 support dropped

As of UserFrosting 4.3.0, support for PHP version 5.6 and 7.0 is officially dropped as both PHP 5.6 and 7.0 have reached [End Of Life](http://php.net/supported-versions.php).

### Complete change Log

See the [Changelog](https://github.com/userfrosting/UserFrosting/blob/master/CHANGELOG.md#v430) for the complete list of changes included in this release.


## Upgrading to 4.3.x

Upgrading UserFrosting to `4.3.x` is as simple as getting the new files and updating dependencies, migrations and assets! If you first installed UserFrosting using git, a simple `git pull` or `git pull upstream master`. Once you have the new files, simply use composer and [Bakery](/cli) to get up to date with everything else:

```bash
$ composer update
$ php bakery bake
```

### Migrating your Sprinkles

#### Font Awesome

Breaking changes include new [icon prefix](https://fontawesome.com/how-to-use/on-the-web/setup/upgrading-from-version-4#changes), the removal of icon aliases and many icon name changes to align with the new standards. Custom sprinkles will need to be updated to use the new icon names. You can check the [upgrade guide](https://fontawesome.com/how-to-use/on-the-web/setup/upgrading-from-version-4#name-changes) for a comprehensive list of icon name changes.

#### Custom Bake Command




## Common Upgrade Problems

### [...]
