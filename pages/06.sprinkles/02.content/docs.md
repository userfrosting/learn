---
title: Contents
metadata:
    description: Detailed breakdown of a Sprinkle's contents.
taxonomy:
    category: docs
---

Within each sprinkle, you will find any or all of the following directories and files:

```txt
├── app/
    ├── assets/
    ├── cache/
    ├── config/
    ├── locale/
    ├── logs/
    ├── schema/
    ├── src/
        ├── Bakery/
        ├── Controllers/
        ├── Database/
        ├── [...]
        ├── Routes/
        ├── Sprunje/
        └── YourSprinkle.php
    ├── storage/
    ├── templates/
    ├── tests/
    └── .env
├── public/
├── vendor/
├── composer.json
├── package.json
└── webpack.config.js
```

[notice=note]The file structure is *somewhat* flexible. For example, `app/` can be named whatever you want. All contents of `app/src/` can also be customized. However, other directories inside `app/` (or whatever you call it) should use the above naming system to allow your sprinkle to overwrite other sprinkles' resources.[/notice]

Each of these directories corresponds to specific types of entities that make up your application. UserFrosting has different rules for how each type of entity can extend the entities of the same type loaded in previous Sprinkles. A brief description of each one is listed below.

### /composer.json

The `composer.json` file is primarily used in UserFrosting to map PHP classes, but as this is Composer, it can also be used to reference additional PHP libraries. The `type` key in your sprinkle's `composer.json` file should always be defined as a `userfrosting-sprinkle`. This will make your sprinkles manageable by Composer itself later.

The sprinkle `composer.json` should also define the sprinkles this one depends on.

### /package.json

The `package.json` file is used for retrieving frontend dependencies via [npm](https://www.npmjs.com), like [Bootstrap](http://getbootstrap.com/). Dependencies specified in `package.json` will be downloaded to `/node_modules`.

To download frontend dependencies, from the project root directory:

```bash
$ php bakery webpack
```

### /webpack.config.js

The `webpack.config.js` file is used for defining **asset entries**, that can be referenced by templates. The advantage of using asset entries (as compared to referencing the specific files) is that multiple files can be quickly referenced by the name of their entries. In production the individual files in each bundle are merged by Webpack Encore, reducing the number of HTTP requests that need to be made and thus reducing client latency and server load. See [Chapter 11](/asset-management/asset-bundles) for more information about asset bundles.

### /app/assets

The `assets` directory contains all of the Javascript, CSS, images, and other static content for your site. See [Chapter 11](/asset-management) for more information about asset management and usage.

### /app/cache

The `cache` directory is used by the [Cache system](/advanced/caching) to store the cached files. This directory is only required when the sprinkle is used a main Sprinkle.

### /app/config

`config` contains the configuration parameters for your Sprinkle. You can define configuration files for different environments (development, testing, production, etc). For each environment, the configuration files in each Sprinkle will be merged together at runtime. See [Chapter 9](/configuration/config-files) for more information.

### /app/locale

The `locale` directory contains [translation files](/i18n) for your Sprinkle. Like configuration files, translation files simply return an associative array.

Just as with configuration files, UserFrosting will recursively merge translation files for the currently selected language(s) from each loaded Sprinkle. This means that each subsequently loaded Sprinkle can override translations from previous Sprinkles, or define new ones entirely.

See [Chapter 17](/i18n) for more information on UserFrosting's internationalization and localization system.

### /app/logs

The `logs` directory is used to store the UserFrosting debug logs. This directory is only required when the sprinkle is used a main Sprinkle.

### /app/schema

The `schema` directory contains the [request schema](/routes-and-controllers/client-input/validation) for your Sprinkle. Schema files in other Sprinkles can be extended by using a custom loader.

### /app/src

The `src` directory contains the (preferably) [PSR-4](http://www.php-fig.org/psr/psr-4/) compatible PHP code for your Sprinkle. This directory will contain your controllers, database models, [migrations](/database/migrations), [routes](/routes-and-controllers), [service providers](/services), [data sprunjers](/database/data-sprunjing), and any other custom classes that your Sprinkle uses. This is where your Sprinkle Recipe will be found.

[notice=note]The content of `app/src/` can be customized and doesn't need to follow any strict convention.[/notice]

### /app/storage

The `storage` directory is used to store files managed by Filesystem service. This directory is only required when the sprinkle is used a main Sprinkle.

### /app/templates

To separate content and logic, UserFrosting uses the popular [Twig](http://twig.symfony.com/) templating engine. Since Twig has its own system for [loading templates](http://twig.symfony.com/doc/api.html#built-in-loaders), UserFrosting builds upon this to allow overriding templates in Sprinkles. See [Templating with Twig](/templating-with-twig) for more information on how Twig is integrated into UserFrosting.

### /app/test

The `test` directory is similar to `/src`, but for your [Tests](/testing).

### /app/.env

The `.env` file is used to store your local [environnement variables](/configuration/environment-vars). This file is only required when the sprinkle is used a main Sprinkle.

### /public

The `public` directory is the web server's document / web root. The `index.php` in this directory serves as the front controller for all HTTP requests. This directory is only required when the sprinkle is used a main Sprinkle.

[notice=warning]The public directory *can* technically be renamed to something else. However, some features requires this path to be hardcoded. For example, the asset compiler and locator have this reference hardcoded. To customized the public directory name, further customization would be required in the code and other configuration values.[/notice]

### /vendor

The `vendor` directory is where all Composer dependency will be loaded.
