---
title: Contents
description: Detailed breakdown of a Sprinkle's contents and how each directory serves a specific purpose.
---

Now that you understand what sprinkles are, let's explore what goes inside them. A sprinkle isn't just a random collection of files—it's an organized package where each directory has a specific purpose. Understanding this structure helps you know exactly where to put your code, templates, assets, and other resources.

Think of a sprinkle like a well-organized kitchen: ingredients (code) go in the pantry, tools (assets) have their drawer, recipes (templates) are in the cookbook, and seasonings (configuration) sit on the spice rack. Everything has its place, making it easy to find what you need and add new items.

This page details each directory in a sprinkle and explains what belongs there.

## Directory Structure

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
└── vite.config.ts
```

> [!NOTE]
> The file structure is *somewhat* flexible. For example, `app/` can be named whatever you want. All contents of `app/src/` can also be customized. However, other directories inside `app/` (or whatever you call it) should use the above naming system to allow your sprinkle to overwrite other sprinkles' resources.

Each of these directories corresponds to specific types of entities that make up your application. UserFrosting has different rules for how each type of entity can extend the entities of the same type loaded in previous sprinkles. A brief description of each one is listed below.

### /composer.json

The `composer.json` file is primarily used in UserFrosting to map PHP classes. As this is Composer, it can *also* be used to reference additional PHP libraries. The `type` key in your sprinkle's `composer.json` file should always be defined as a `userfrosting-sprinkle`. This will make your sprinkles manageable by Composer itself later.

The sprinkle `composer.json` should also define the sprinkles this one depends on.

### /package.json

The `package.json` file is used for retrieving frontend dependencies via [npm](https://www.npmjs.com), like [Vue](https://vuejs.org/) components. Dependencies specified in `package.json` will be downloaded to `/node_modules`.

To download frontend dependencies, from the project root directory:

```bash
$ php bakery bake
```

### /vite.config.ts

The `vite.config.ts` file is used for configuring **Vite** and defining how your assets are built and bundled. See [Chapter 13](/asset-management) for more information about asset management with Vite.

### /app/assets

The `assets` directory contains all of the Javascript, CSS, images, and other static content for your site. See [Chapter 13](/asset-management) for more information about asset management and usage.

### /app/cache

The `cache` directory is used by the [Cache system](/advanced/caching) to store the cached files. This directory is only required for main sprinkles.

### /app/config

`config` contains the configuration parameters for your sprinkle. You can define configuration files for different environments (development, testing, production, etc). For each environment, the configuration files in each sprinkle will be merged together at runtime. See [Chapter 9](/configuration/config-files) for more information.

### /app/locale

The `locale` directory contains [translation files](/i18n) for your sprinkle. Like configuration files, translation files simply return an associative array.

Just as with configuration files, UserFrosting will recursively merge translation files for the currently selected language(s) from each loaded sprinkle. This means that each subsequently loaded sprinkle can override translations from previous sprinkles, or define new ones entirely.

See [Chapter 17](/i18n) for more information on UserFrosting's internationalization and localization system.

### /app/logs

The `logs` directory is used to store UserFrosting debug logs. This directory is only required for main sprinkles.

### /app/schema

The `schema` directory contains the [request schema](/routes-and-controllers/client-input/validation) for your sprinkle. Schema files in other sprinkles can be extended by using a custom loader.

### /app/src

The `src` directory contains the (preferably) [PSR-4](http://www.php-fig.org/psr/psr-4/) compatible PHP code for your sprinkle. This directory will contain your controllers, database models, [migrations](/database/migrations), [routes](/routes-and-controllers), [service providers](/services), [data sprunjers](/database/data-sprunjing), and any other custom classes that your sprinkle uses. This is where your sprinkle's Recipe will be found.

> [!NOTE]
> The content of `app/src/` can be customized and doesn't need to follow any strict convention.

### /app/storage

The `storage` directory is used to store files managed by Filesystem service. This directory is only required for main sprinkles.

### /app/templates

To separate content and logic, UserFrosting uses the popular [Twig](http://twig.symfony.com/) templating engine. Since Twig has its own system for [loading templates](http://twig.symfony.com/doc/api.html#built-in-loaders), UserFrosting builds upon this to allow overriding templates in sprinkles. See [Templating with Twig](/templating-with-twig) for more information on how Twig is integrated into UserFrosting.

### /app/test

The `test` directory is similar to `/src`, but for your [Tests](/testing).

### /app/.env

The `.env` file is used to store your local [environment variables](/configuration/environment-vars). This file is only required for main sprinkles.

### /public

The `public` directory is the web server's document / web root. The `index.php` in this directory serves as the front controller for all HTTP requests. This directory is only required for main sprinkles.

> [!WARNING]
> The public directory *can* technically be renamed to something else. However, some features requires this path to be hardcoded. For example, the asset compiler and locator have this reference hardcoded. To customize the public directory name, further customization will be required in the code and other configuration values.

### /vendor

The `vendor` directory is where all Composer dependency will be loaded.
