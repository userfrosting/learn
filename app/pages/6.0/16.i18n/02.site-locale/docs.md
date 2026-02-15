---
title: Setting up the site language
description: Configuration options are available to control the overall language presented by UserFrosting.
---

UserFrosting is translated in a variety of languages provided by our community. While a default locale will be used for new visitors, each user can  choose their preferred language.

## The default locale

The site default languages can be set in the [config](/configuration/config-files) parameters. The `site.locales.default` contains the locale to use for global, guest users.

For example, to use _French_ as the default locale :

```php
'default' => 'fr_FR',
```

> [!NOTE]
> When returned by the browser, the browser preferred locale will be used as the default locale for guest user.

## The available user locales

A user can also use their own language, which they can choose in their profile settings. All available locales the users can choose from are defined in the `site.locales.available` config.

By default, UserFrosting 6 provides two locales:

```php
'available' => [
    'en_US' => true,
    'fr_FR' => true,
],
```

To disable a locale, set it to `false` in your sprinkle config. To add additional locales, first [create the custom locale](/i18n/custom-locale) with its translation files, then add it to the available list:

```php
'available' => [
    'en_US' => true,
    'fr_FR' => true,
    'es_ES' => true,  // Add Spanish
    'de_DE' => false, // Disable German (if previously enabled)
],
```

> [!TIP]
> Want to add a new locale to UserFrosting? Feel free to [contribute](/contributing/supporting-userfrosting#contributing-code-and-content) on GitHub