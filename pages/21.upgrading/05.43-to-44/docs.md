---
title: 4.3.x to 4.4.x
metadata:
    description:
taxonomy:
    category: docs
---

## Overview

UserFrosting 4.4 focuses on rewriting the Translator service and locale improvements.

### Key new features
The translator has been completely rewritten, focusing on optimization and . All locales can now defines metadata and configuration options in a required `locale.yaml` file. The current locale identifier (ie. `en_US` or `fr_FR`) can now be accessed from Twig template using the `currentLocale` global variable.

Services providers can now be defined in different files and semi-autoloaded for easier testing. See detailed document page for more info.

### Breaking changes

#### Major (likely to break your project)
- `Interop\Container\ContainerInterface` has been replaced with `Psr\Container\ContainerInterface`.
- `\UserFrosting\I18n\MessageTranslator` is now `\UserFrosting\I18n\Translator`.
- `site.locales.available` config now accept `(string) identifier => (bool) enabled`. Set identifier to false or null to remove it from the list.

#### Minor (should only break heavily customized projects)
- `localePathBuilder` service has been removed. Tasks that used to be handled by this service are now handled by the `locale` and `translator` services.

### PHP 7.1 Deprecation

As of UserFrosting 4.4.0, support for PHP version 7.1 is officially **deprecated** as it has reached [End Of Life](http://php.net/supported-versions.php). Support for PHP 7.1 will be removed i nthe next version. *PHP version 7.3 or higher is now recommended* as official PHP 7.2 support will end in december 2020.

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

Search for `use Interop\Container\ContainerInterface;` and replace all instances with `use Psr\Container\ContainerInterface;`.

[notice]You can alternatively require `container-interop/container-interop` in your sprinkle `composer.json` to quickly reenable `Interop` container interface. However, since `container-interop/container-interop` is now deprecated, using Psr container is preferable.[/notice]

### Available locales configuration

The old configuration for available locale used to be an `identifier => name` key/value pair :

```
'available' => [
    'en_US' => 'English',
    'zh_CN' => '中文',
    'es_ES' => null', // Use null to disable locale
    ...
],
```

The new configuration are now an `identifier => (bool) enabled` key/value pair :

```
'available' => [
    'en_US' => true,
    'zh_CN' => true,
    'es_ES' => false, // False to disable locale
    ...
],
```

While both are compatible with eachother, it is now recommended all sprinkle be update to the new format. The locale name defined in the configuration will not be used anymore as it as been replaced by the locale config.

### Locale upgrade

Custom locales (those not bundled by default with UserFrosting) needs to be updated with a new `locale.yaml` file. See [Custom locale]() for more information..
