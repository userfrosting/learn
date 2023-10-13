---
title: The Sprinkle Recipe
metadata:
    description: 
taxonomy:
    category: docs
---

<!-- TODO : List all Interfaces, explain them, the concept -->

## Basic concept

A basic recipe has some methods which are used to define some Sprinkle properties.

### getName

Returns the name of the Sprinkle.

### getPath

Returns the path of the Sprinkle. 

For example, if your recipe is in `app/src/YourSprinkle.php` and your Sprinkle structure looks like this...

```
├── app/
    ├── assets/
    ├── [...]
    ├── src/
        ├── [...]
        └── YourSprinkle.php
    └── [...]
├── public/
├── vendor/
├── composer.json
├── package.json
└── webpack.config.js
```

...`getPath()` should point to `/app`. 

[notice=note]Reminder, `app/` can actually be named whatever you want.[/notice]

### getSprinkles

Returns an array of dependent sub-sprinkles recipe. The order the sprinkle are listed is important. See [#Dependencies](#dependencies).

For example:
```php
return [
    Core::class,
    Account::class,
    Admin::class,
    AdminLTE::class,
];
```

### getRoutes

Return an array of routes classes.

```php
return [
    MyRoutes::class,
];
```

More details about this will be explored in [Chapter 8](/routes-and-controllers/registering-routes).

### getServices

Return an array of routes classes.

```php
return [
    MyRoutes::class,
];
```

## Full Example

```php
<?php

namespace UserFrosting\App;

use UserFrosting\App\Bakery\HelloCommand;
use UserFrosting\Sprinkle\Account\Account;
use UserFrosting\Sprinkle\Admin\Admin;
use UserFrosting\Sprinkle\BakeryRecipe;
use UserFrosting\Sprinkle\Core\Core;
use UserFrosting\Sprinkle\SprinkleRecipe;
use UserFrosting\Theme\AdminLTE\AdminLTE;

class YourSprinkle implements SprinkleRecipe
{
    public function getName(): string
    {
        return 'My Application';
    }

    public function getPath(): string
    {
        return __DIR__ . '/../';
    }

    public function getSprinkles(): array
    {
        return [
            Core::class,
            Account::class,
            Admin::class,
            AdminLTE::class,
        ];
    }

    public function getRoutes(): array
    {
        return [
            MyRoutes::class,
        ];
    }

    public function getServices(): array
    {
        return [
            MyServices::class,
        ];
    }
}
```

## Dependencies

[notice=warning]The order in which we load our Sprinkles is important. Files in one Sprinkle may override files with the same name and path in previously loaded Sprinkles. For example, if we created `site/templates/pages/about.html.twig`, this would override `core/templates/pages/about.html.twig` because we load the `site` Sprinkle *after* the `core` Sprinkle.[/notice]

## Optional recipes

## Wrapping up 

```php
<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core;

use Lcharette\WebpackEncoreTwig\EntrypointsTwigExtension;
use Lcharette\WebpackEncoreTwig\VersionedAssetsTwigExtension;
use UserFrosting\Event\AppInitiatedEvent;
use UserFrosting\Event\BakeryInitiatedEvent;
use UserFrosting\Event\EventListenerRecipe;
use UserFrosting\Sprinkle\BakeryRecipe;
use UserFrosting\Sprinkle\Core\Bakery\BakeCommand;
use UserFrosting\Sprinkle\Core\Bakery\ClearCacheCommand;
use UserFrosting\Sprinkle\Core\Bakery\DebugCommand;
use UserFrosting\Sprinkle\Core\Bakery\DebugConfigCommand;
use UserFrosting\Sprinkle\Core\Bakery\DebugDbCommand;
use UserFrosting\Sprinkle\Core\Bakery\DebugEventsCommand;
use UserFrosting\Sprinkle\Core\Bakery\DebugLocatorCommand;
use UserFrosting\Sprinkle\Core\Bakery\DebugVersionCommand;
use UserFrosting\Sprinkle\Core\Bakery\LocaleCompareCommand;
use UserFrosting\Sprinkle\Core\Bakery\LocaleDictionaryCommand;
use UserFrosting\Sprinkle\Core\Bakery\LocaleInfoCommand;
use UserFrosting\Sprinkle\Core\Bakery\MigrateCleanCommand;
use UserFrosting\Sprinkle\Core\Bakery\MigrateCommand;
use UserFrosting\Sprinkle\Core\Bakery\MigrateRefreshCommand;
use UserFrosting\Sprinkle\Core\Bakery\MigrateResetCommand;
use UserFrosting\Sprinkle\Core\Bakery\MigrateResetHardCommand;
use UserFrosting\Sprinkle\Core\Bakery\MigrateRollbackCommand;
use UserFrosting\Sprinkle\Core\Bakery\MigrateStatusCommand;
use UserFrosting\Sprinkle\Core\Bakery\RouteListCommand;
use UserFrosting\Sprinkle\Core\Bakery\SeedCommand;
use UserFrosting\Sprinkle\Core\Bakery\SeedListCommand;
use UserFrosting\Sprinkle\Core\Bakery\SetupCommand;
use UserFrosting\Sprinkle\Core\Bakery\SetupDbCommand;
use UserFrosting\Sprinkle\Core\Bakery\SetupEnvCommand;
use UserFrosting\Sprinkle\Core\Bakery\SetupMailCommand;
use UserFrosting\Sprinkle\Core\Bakery\SprinkleListCommand;
use UserFrosting\Sprinkle\Core\Bakery\TestMailCommand;
use UserFrosting\Sprinkle\Core\Bakery\WebpackCommand;
use UserFrosting\Sprinkle\Core\Csrf\CsrfGuardMiddleware;
use UserFrosting\Sprinkle\Core\Database\Migrations\v400\SessionsTable;
use UserFrosting\Sprinkle\Core\Database\Migrations\v400\ThrottlesTable;
use UserFrosting\Sprinkle\Core\Error\ExceptionHandlerMiddleware;
use UserFrosting\Sprinkle\Core\Error\RegisterShutdownHandler;
use UserFrosting\Sprinkle\Core\Event\ResourceLocatorInitiatedEvent;
use UserFrosting\Sprinkle\Core\Listeners\ModelInitiated;
use UserFrosting\Sprinkle\Core\Listeners\ResourceLocatorInitiated;
use UserFrosting\Sprinkle\Core\Listeners\SetRouteCaching;
use UserFrosting\Sprinkle\Core\Middlewares\LocaleMiddleware;
use UserFrosting\Sprinkle\Core\Middlewares\SessionMiddleware;
use UserFrosting\Sprinkle\Core\Middlewares\URIMiddleware;
use UserFrosting\Sprinkle\Core\Routes\AlertsRoutes;
use UserFrosting\Sprinkle\Core\ServicesProvider\AlertStreamService;
use UserFrosting\Sprinkle\Core\ServicesProvider\CacheService;
use UserFrosting\Sprinkle\Core\ServicesProvider\ConfigService;
use UserFrosting\Sprinkle\Core\ServicesProvider\DatabaseService;
use UserFrosting\Sprinkle\Core\ServicesProvider\ErrorHandlerService;
use UserFrosting\Sprinkle\Core\ServicesProvider\I18nService;
use UserFrosting\Sprinkle\Core\ServicesProvider\LocatorService;
use UserFrosting\Sprinkle\Core\ServicesProvider\LoggersService;
use UserFrosting\Sprinkle\Core\ServicesProvider\MailService;
use UserFrosting\Sprinkle\Core\ServicesProvider\MigratorService;
use UserFrosting\Sprinkle\Core\ServicesProvider\RoutingService;
use UserFrosting\Sprinkle\Core\ServicesProvider\SeedService;
use UserFrosting\Sprinkle\Core\ServicesProvider\SessionService;
use UserFrosting\Sprinkle\Core\ServicesProvider\ThrottlerService;
use UserFrosting\Sprinkle\Core\ServicesProvider\TwigService;
use UserFrosting\Sprinkle\Core\ServicesProvider\VersionsService;
use UserFrosting\Sprinkle\Core\ServicesProvider\WebpackService;
use UserFrosting\Sprinkle\Core\Sprinkle\Recipe\MigrationRecipe;
use UserFrosting\Sprinkle\Core\Sprinkle\Recipe\TwigExtensionRecipe;
use UserFrosting\Sprinkle\Core\Twig\Extensions\AlertsExtension;
use UserFrosting\Sprinkle\Core\Twig\Extensions\CoreExtension;
use UserFrosting\Sprinkle\Core\Twig\Extensions\CsrfExtension;
use UserFrosting\Sprinkle\Core\Twig\Extensions\I18nExtension;
use UserFrosting\Sprinkle\Core\Twig\Extensions\RoutesExtension;
use UserFrosting\Sprinkle\MiddlewareRecipe;
use UserFrosting\Sprinkle\SprinkleRecipe;

class Core implements
    SprinkleRecipe,
    TwigExtensionRecipe,
    MigrationRecipe,
    EventListenerRecipe,
    MiddlewareRecipe,
    BakeryRecipe
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Core Sprinkle';
    }

    /**
     * {@inheritdoc}
     */
    public function getPath(): string
    {
        return __DIR__ . '/../';
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function getBakeryCommands(): array
    {
        return [
            BakeCommand::class,
            ClearCacheCommand::class,
            DebugCommand::class,
            DebugConfigCommand::class,
            DebugDbCommand::class,
            DebugEventsCommand::class,
            DebugLocatorCommand::class,
            DebugVersionCommand::class,
            LocaleCompareCommand::class,
            LocaleDictionaryCommand::class,
            LocaleInfoCommand::class,
            MigrateCommand::class,
            MigrateCleanCommand::class,
            MigrateRefreshCommand::class,
            MigrateResetCommand::class,
            MigrateResetHardCommand::class,
            MigrateRollbackCommand::class,
            MigrateStatusCommand::class,
            RouteListCommand::class,
            SeedCommand::class,
            SeedListCommand::class,
            SetupCommand::class,
            SetupDbCommand::class,
            SetupEnvCommand::class,
            SetupMailCommand::class,
            SprinkleListCommand::class,
            TestMailCommand::class,
            WebpackCommand::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getSprinkles(): array
    {
        return [];
    }

    /**
     * Returns a list of routes definition in PHP files.
     *
     * @return string[]
     */
    public function getRoutes(): array
    {
        return [
            AlertsRoutes::class,
        ];
    }

    /**
     * Returns a list of all PHP-DI services/container definitions files.
     *
     * @return string[]
     */
    public function getServices(): array
    {
        return [
            AlertStreamService::class,
            CacheService::class,
            ConfigService::class,
            DatabaseService::class,
            ErrorHandlerService::class,
            I18nService::class,
            LocatorService::class,
            LoggersService::class,
            MailService::class,
            MigratorService::class,
            RoutingService::class,
            SeedService::class,
            SessionService::class,
            ThrottlerService::class,
            TwigService::class,
            VersionsService::class,
            WebpackService::class,
        ];
    }

    /**
     * Returns a list of all Middlewares classes.
     *
     * @return \Psr\Http\Server\MiddlewareInterface[]
     */
    public function getMiddlewares(): array
    {
        return [
            LocaleMiddleware::class,
            CsrfGuardMiddleware::class,
            SessionMiddleware::class,
            URIMiddleware::class,
            ExceptionHandlerMiddleware::class,
        ];
    }

    /**
     * Return an array of all registered Twig Extensions.
     *
     * @return \Twig\Extension\ExtensionInterface[]
     */
    public function getTwigExtensions(): array
    {
        return [
            CoreExtension::class,
            CsrfExtension::class,
            I18nExtension::class,
            AlertsExtension::class,
            RoutesExtension::class,
            EntrypointsTwigExtension::class,
            VersionedAssetsTwigExtension::class,
        ];
    }

    public function getMigrations(): array
    {
        return [
            SessionsTable::class,
            ThrottlesTable::class,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getEventListeners(): array
    {
        return [
            AppInitiatedEvent::class             => [
                RegisterShutdownHandler::class,
                ModelInitiated::class,
                SetRouteCaching::class,
            ],
            BakeryInitiatedEvent::class          => [
                ModelInitiated::class,
                SetRouteCaching::class,
            ],
            ResourceLocatorInitiatedEvent::class => [
                ResourceLocatorInitiated::class,
            ],
        ];
    }
}
```
