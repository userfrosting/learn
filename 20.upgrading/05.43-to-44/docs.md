---
title: 4.3.x to 4.4.x
metadata:
    description:
taxonomy:
    category: docs
---

## Overview

UserFrosting 4.4 focuses on rewriting the Translator service and locale improvements.

### Updated Composer Dependencies
-

### Updated Frontend Dependencies
-

### Key new features
-

### Breaking changes

#### Major (likely to break your project)
- `Interop\Container\ContainerInterface` has been replaced with `Psr\Container\ContainerInterface`.

#### Minor (should only break heavily customized projects)
-

### PHP 5.6 and 7.0 support dropped

As of UserFrosting 4.4.0, support for PHP version 7.1 is officially **deprecated** it has reached [End Of Life](http://php.net/supported-versions.php). *PHP version 7.3 or higher is recommended*.

### Complete change Log

See the [Changelog](https://github.com/userfrosting/UserFrosting/blob/master/CHANGELOG.md#v440) for the complete list of changes included in this release.


## Upgrading to 4.4.x

Upgrading UserFrosting to `4.4.x` is as simple as getting the new files and updating dependencies, migrations and assets! If you first installed UserFrosting using git, a simple `git pull` or `git pull upstream master`. Once you have the new files, simply use composer and [Bakery](/cli) to get up to date with everything else:

```bash
$ composer update
$ php bakery bake
```

### Migrating your Sprinkles

#### Container interface

Search for `use Psr\Container\ContainerInterface;` and replace all instances with `use Psr\Container\ContainerInterface;`.

[notice]You can alternatively require `psr-container/psr-container` in your sprinkle `composer.json` to quickly reenable `Interop` container interface. However, since `psr-container/psr-container` is now deprecated, using Psr container is preferable.[/notice]


<!-- ## Common Upgrade Problems -->

<!-- ### [...] -->
