---
title: 4.5.x to 4.6.x
metadata:
    description:
taxonomy:
    category: docs
---

## Overview

UserFrosting 4.6 focuses on adding PHP 8 support and upgrading Laravel dependencies. Individual supporting repository are also now available inside `userfrosting/framework`.

### Changed PHP Requirements
As of UserFrosting 4.6.0, support for PHP version 7.2 has officially **been removed**. Support for PHP 7.2 was deprecated in 4.5.0 as it had reached [End Of Life](http://php.net/supported-versions.php). *PHP version 8.0 is now available and recommended*.

### Upgraded Dependencies
(!TODO)

### UserFrosting Framework
(!TODO)

### Support for built-in PHP Server
Built-in PHP Server can now be used for developement purposes, as long as assets are present (if node is installed locally) and a database available (sqlite file can be used for this). Run the following command in terminal to start localhost web server :

```
php -S localhost:8888 -t public public/index.php
```

UserFrosting will be available at <http://localhost:8888>.

### Other Misc Changes & Fixes
- (!TODO)

### Complete change Log

See the [Changelog](https://github.com/userfrosting/UserFrosting/blob/master/CHANGELOG.md#v460) for the complete list of changes included in this release.

## Upgrading to 4.6.x

Upgrading UserFrosting to `4.6.x` is as simple as getting the new files and updating dependencies, migrations and assets! If you first installed UserFrosting using git, a simple `git pull` or `git pull upstream master`. Once you have the new files, simply use composer and [Bakery](/cli) to get up to date with everything else:

```bash
$ composer update
$ php bakery bake
```

### Migrating your Sprinkles
Upgrating your sprinkle for UserFrosting 4.6.0... (!TODO)
