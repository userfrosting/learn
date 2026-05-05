---
title: Contents
metadata:
    description: Detailed breakdown of a Sprinkle's contents.
taxonomy:
    category: docs
---

Within each sprinkle, you will find any or all of the following directories and files:

```
├── assets
├── config
├── locale
├── routes
├── schema
├── src
├── templates
├── composer.json
├── package.json
└── asset-bundles.json
```

Each of these directories corresponds to specific types of entities that make up your application. UserFrosting has different rules for how each type of entity can extend the entities of the same type loaded in previous Sprinkles.

### /composer.json

The `composer.json` file is primarily used in UserFrosting to map a Sprinkles classes, but as this is Composer, it can also be used to reference additional PHP libraries. The `type` key in your sprinkle's `composer.json` file should always be defined as a `userfrosting-sprinkle`. This will make your sprinkles manageable by Composer itself later.

The master `composer.json` file in the project root directory (`/`) will automatically merge the `composer.json` file for every Sprinkle when performing:

```bash
$ composer update
```

[notice=warning]The master `composer.json` file will load **all** child `composer.json` files, even in Sprinkles that haven't been loaded in your site's `sprinkles.json`. To change this behavior, you will need to modify the master `composer.json` file.[/notice]

[notice=tip]Always run any composer command from the project root directory (`/`).[/notice]

### /package.json

The `package.json` file is used for retrieving frontend dependencies via [Yarn](https://yarnpkg.com/), like [Bootstrap](http://getbootstrap.com/). Dependencies specified in `package.json` will be downloaded to `/app/assets/node_modules`.

To download frontend dependencies, from the project root directory:

```bash
$ php bakery build-assets
```

### /asset-bundles.json

The `asset-bundles.json` file is used for defining **asset bundles**, that can be referenced by templates. The advantage of using asset bundles (as compared to referencing the specific files) is that multiple files can be quickly referenced by the name of their bundles. In production the individual files in each bundle are merged, reducing the number of HTTP requests that need to be made and thus reducing client latency and server load. See [Chapter 11](/asset-management/asset-bundles) for more information about asset bundles.

### /assets

The `assets` directory contains all of the Javascript, CSS, images, and other static content for your site. See [Chapter 11](/asset-management) for more information about asset management and usage.

### /config

`config` contains the configuration parameters for your Sprinkle. You can define configuration files for different environments (development, testing, production, etc). For each environment, the configuration files in each Sprinkle will be merged together at runtime. See [Chapter 6](/configuration/config-files) for more information.

### /locale

The `locale` directory contains [translation files](/i18n) for your Sprinkle. Like configuration files, translation files simply return an associative array.

Just as with configuration files, UserFrosting will recursively merge translation files for the currently selected language(s) from each loaded Sprinkle. This means that each subsequently loaded Sprinkle can override translations from previous Sprinkles, or define new ones entirely.

See [Chapter 16](/i18n) for more information on UserFrosting's internationalization and localization system.

### /routes

Files in the `routes` directory should contain the Slim [front controller routes](/routes-and-controllers/front-controller) for your Sprinkle. For example, if your website was `http://owlfancy.com`, then the URL at `http://owlfancy.com/supplies/preening` would be defined in a route file as:

```php
$app->get('/supplies/preening', 'UserFrosting\Sprinkle\MySprinkle\Controller\MySprinkleController:pagePreening');
```

As with configuration and translation files, route files can override routes from previous Sprinkles in addition to defining new ones.

Learn more about routes and controllers in [Chapter 9](/routes-and-controllers).

[notice]You may have as many route files as you'd like in a Sprinkle. Within each Sprinkle, route files are loaded in alphabetical order, so in general it is not a good idea to override a route in the same Sprinkle in which it was originally defined.[/notice]

### /schema

`schema` contains the [request schema](/routes-and-controllers/client-input/validation) for your Sprinkle. Schema files in other Sprinkles can be extended by using a custom loader.

### /src

`src` contains the (preferably) [PSR-4](http://www.php-fig.org/psr/psr-4/) compatible PHP code for your Sprinkle. This directory will contain your controllers, database models, [migrations](/database/migrations), [service providers](/services), [data sprunjers](/database/data-sprunjing), and any other custom classes that your Sprinkle uses.

### /templates

To separate content and logic, UserFrosting uses the popular [Twig](http://twig.sensiolabs.org/) templating engine. Since Twig has its own system for [loading templates](http://twig.sensiolabs.org/doc/api.html#built-in-loaders), UserFrosting builds upon this to allow overriding templates in Sprinkles. See [Templating with Twig](/templating-with-twig) for more information on how Twig is integrated into UserFrosting.
