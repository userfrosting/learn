---
title: Setting up the site language
metadata:
    description: TODO
taxonomy:
    category: docs
---

The site default languages can be set in the [config](/configuration/config-files) parameters. The `site.locales.default` contains the locale to use for global, guest users. Multiple locales can be listed, separated by commas, to indicate the locale precedence order. For example, `'default' => 'en_US, fr_FR'` means that the _French_ language will be loaded first and if the requested key doesn't exist in French, it will try to use the _English_ one instead.

A user can also use its own language. This is defined in the user's profile. All available locales are defined in the `site.locales.available` config. To remove one locale from the user profile, simply set the unwanted locale to `null` in your sprinkle config. For example, the following config will only present the English, Spanish and French locale to the user :

```
    'available' => [
        'en_US' => 'English',
        'zh_CN' => null,
        'es_ES' => 'Español',
        'ar'    => null,
        'pt_PT' => null,
        'ru_RU' => null,
        'de_DE' => null,
        'fr_FR' => 'Français',
        'tr'    => null,
        'it_IT' => null,
        'th_TH' => null,
        'fa'    => null,
        'el'    => null,
    ],
```
