---
title: The Sprinkle Recipe
metadata:
    description: 
taxonomy:
    category: docs
---

The Sprinkle Recipe dictates how your sprinkle is built, like a blueprint. UserFrosting services and framework will use the information from the recipe to initiate some services, and expose classes your sprinkle provides for other servicea to use.

Each sprinkle **must have** a recipe. It's not possible for a sprinkle to exist without a recipe, as it won't be possible to expose its class and service to the framework. It's possible however to customize other sprinkles, as we'll see later on this page. 

## The `SprinkleRecipe` Interface

The Sprinkle Recipe is a simple PHP class that provides standard methods which will be called by services to retrieve information about your sprinkle structure and the class it's registering. Every sprinkle recipe **MUST** implement the `UserFrosting\Sprinkle\SprinkleRecipe` interface. If you started from the [Skeleton](/structure/introduction#the-app-skeleton-your-project-s-template), you already have a basic recipe.

This interface requires you to implement the following method in your recipe:  
- [`getName`](#name): Returns the name of the sprinkle.
- [`getPath`](#path): Returns the path of the sprinkle. 
- [`getSprinkles`](#dependent-sprinkles): Returns an array of dependent sub-sprinkles recipe. 
- [`getRoutes`](#routes): Return an array of routes classes.
- [`getServices`](#services): Return an array of services classes.

[notice=note]Since the class must implement the `SprinkleRecipe` interface, all of those methods are mandatory. Failure to implement the interface will result in an exception being thrown. However, it doesn't mean a method must return data. It's perfectly fine for a method to return an empty string or empty array.[/notice]

### Name

This method returns the name identifier of the sprinkle. This name is mostly used in debug interfaces to identify resources and classes registered by the sprinkle. 

The method should return a string. For example: 

```php
public function getName(): string
{
    return 'My Application';
}
```

### Path

This method returns the path of the sprinkle. This path should point where the `src/`, `assets/`, etc. folder is located, typically `app/`. For example, if your recipe is in `app/src/YourSprinkle.php` and your sprinkle structure looks like this...

```
├── app/
    ├── assets/
    ├── logs/
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

...`getPath()` should point to `/app`, or in this case the parent directory of where the recipe file is located :

```php
public function getPath(): string
{
    return __DIR__ . '/../';
}
```

[notice=note]`app/` can actually be named whatever you want. As long as the recipe point to the folder containing all the sprinkle static resources.[/notice]

### Dependent sprinkles

This methods returns the sub-sprinkles your sprinkle depends on. This makes it easier to integrate other sprinkles' classes and resources into your app without having to copy everything inside your own recipe.

The order the sprinkles are loaded is important. Files in one sprinkle may override files with the same name and path in previously loaded sprinkles. For example, if we created `site/templates/pages/about.html.twig`, this would override `core/templates/pages/about.html.twig` because we load the `site` sprinkle *after* the `core` sprinkle.

Sprinkles will be loaded in the order you list them (top one first), but also based on their respective dependencies. For example:
```php
public function getSprinkles(): array
{
    return [
        Core::class,
        Account::class,
        Admin::class,
        AdminLTE::class,
    ];
}
```

Since `Admin` depends on `Core`, `Account` and `AdminLTE`, it's not mandatory to relist them in your recipe. In fact, the code above is equivalent to this, since the other one will be registered by `Admin`: 
```php
public function getSprinkles(): array
{
    return [
        Admin::class,
    ];
}
```

This also mean removing `AdminLTE` as a dependency for example **cannot be done by simply removing it from your recipe**! It's impossible to remove `AdminLTE` without removing `Admin`, since `Admin` cannot work without its dependency.

However, it also means the next example **is also equivalent**:
```php
public function getSprinkles(): array
{
    return [
        AdminLTE::class,
        Admin::class,
        Account::class,
        Core::class,
    ];
}
```

Let's look at the process for the above code : 

1. AdminLTE will be loaded first. AdminLTE depends on Core first, and Account second. Core doesn't depend on anything. So **Core** is the first sprinkle loaded;
2. Account is then checked. It depends on Core, which is already loaded, so **Account** is the second loaded sprinkle;
3. AdminLTE doesn't have any more dependencies, so **AdminLTE** is loaded in third;
4. Admin is now checked. It depends on Core, Account and AdminLTE. All are already loaded, so everything is good. **Admin** is loaded fourth;
5. This sprinkle's dependencies are all good, so it is loaded last.

Because of sprinkle dependencies, in all three examples the order will be `Core -> Account -> AdminLTE -> Admin -> YOUR APP`.

[notice=tip]An easy way to see the final order sprinkles are loaded is via the command line `php bakery sprinkle:list` command. The registered sprinkles will be displayed in the order they are registered.[/notice]

### Routes

Return an array of routes classes. More details about this will be explored in [Chapter 8 - Routes and Controllers](/routes-and-controllers). 

For example, to register `MyRoutes` class:
```php
public function getRoutes(): array
{
    return [
        MyRoutes::class,
    ];
}
```

### Services

Return an array of services definitions. Theses will be explored in [Chapter 7 - Dependency Injection](/dependency-injection)

Example: 
```php
public function getServices(): array
{
    return [
        AlertStreamService::class,
        CacheService::class,
    ];
}
```

## The main sprinkle

Since your sprinkle is the last loaded sprinkle, it becomes ***the main sprinkle***. This is important, as the **main sprinkle** is the entry point to the app. The main sprinkle class must be referenced in two *entry files* : `/public/index.php` (web app/page entry) and `/bakery` (CLI App).

For example, if your main sprinkle class fully qualified name is `UserFrosting\App\MyApp` :

**/public/index.php**
```php
// [...]

use UserFrosting\App\MyApp; // <--- Import here
use UserFrosting\UserFrosting;

$uf = new UserFrosting(MyApp::class); // <-- Reference here
$uf->run();
```

**/bakery**
```php
// [...]

use UserFrosting\App\MyApp; // <--- Import here
use UserFrosting\Bakery\Bakery;

$bakery = new Bakery(MyApp::class); // <-- Reference here
$bakery->run();
```

[notice]The main sprinkle class can be named whatever you want. You can rename the default one from App Skeleton, but it's important to remember to also update its reference in both locations.[/notice]

## Optional recipes

The sprinkle recipe power comes from its modularity. To avoid having one huge recipe with empty content, optional features can be added only when necessary. 

The available sub-recipes includes: 

| Recipe                                      | Features                                                                                            |
| ------------------------------------------- | --------------------------------------------------------------------------------------------------- |
| [BakeryRecipe](#bakeryrecipe)               | Registering [Bakery commands](/cli/custom-commands)                                                 |
| [MigrationRecipe](#migrationrecipe)         | Registering [Migrations](/database/migrations)                                                      |
| [SeedRecipe](#seedrecipe)                   | Registering [Seeds](database/seeding)                                                               |
| [MiddlewareRecipe](#middlewarerecipe)       | Registering [Middlewares](advanced/middlewares)                                                     |
| [EventListenerRecipe](#eventlistenerrecipe) | Registering [Event Listeners](/advanced/events)                                                     |
| [TwigExtensionRecipe](#twigextensionrecipe) | Registering [Twig Extension](/templating-with-twig/filters-and-functions#extending-twig-extensions) |

Your recipe simply needs to implement the corresponding interface. Classes may implement more than one interface if desired by separating each interface with a comma. For example :

```php
class MyApp implements
    SprinkleRecipe,
    TwigExtensionRecipe,
    MigrationRecipe,
    EventListenerRecipe,
    MiddlewareRecipe,
    BakeryRecipe
{
```

[notice=tip]Your sprinkle could even define its own recipe that you or other sprinkles could implement![/notice]

### BakeryRecipe
Interface : `UserFrosting\Sprinkle\BakeryRecipe`

Methods to implements : 
- `getBakeryCommands` : Return a list of [Bakery commands](/cli/custom-commands) classes

    **Example:**
    ```php
    public function getBakeryCommands(): array
    {
        return [
            BakeCommand::class,
            ClearCacheCommand::class,
        ];
    }
    ```

### MigrationRecipe
Interface : `UserFrosting\Sprinkle\Core\Sprinkle\Recipe\MigrationRecipe`

Methods to implement :
- `getMigrations` : Return a list of [Migrations](/database/migrations) classes

    **Example:**
    ```php
    public function getMigrations(): array
    {
        return [
            SessionsTable::class,
            ThrottlesTable::class,
        ];
    }
    ```

### SeedRecipe
Interface : `UserFrosting\Sprinkle\Core\Sprinkle\Recipe\SeedRecipe`

Methods to implement : 
- `getSeeds` : Return a list of [Seeds](/database/seeding) classes

    **Example:**
    ```php 
    public function getSeeds(): array
    {
        return [
            DefaultGroups::class,
            DefaultPermissions::class,
            DefaultRoles::class,
        ];
    }
    ```

### MiddlewareRecipe
Interface : `UserFrosting\Sprinkle\MiddlewareRecipe`

Methods to implement : 
- `getMiddlewares` : Return a list of [Middlewares](/advanced/middlewares) classes

    **Example:**
    ```php
    public function getMiddlewares(): array
    {
        return [
            CsrfGuardMiddleware::class,
            SessionMiddleware::class,
        ];
    }
    ```

### EventListenerRecipe
Interface : `UserFrosting\Event\EventListenerRecipe`

Methods to implement : 
- `getEventListeners` : Allows to register [Event Listeners](/advanced/events#listener)

    **Example:**
    ```php
    public function getEventListeners(): array
        {
            return [
                AppInitiatedEvent::class => [
                    RegisterShutdownHandler::class,
                    ModelInitiated::class,
                    SetRouteCaching::class,
                ],
                BakeryInitiatedEvent::class => [
                    ModelInitiated::class,
                    SetRouteCaching::class,
                ],
                ResourceLocatorInitiatedEvent::class => [
                    ResourceLocatorInitiated::class,
                ],
            ];
        }
    ```

### TwigExtensionRecipe
Interface : `UserFrosting\Sprinkle\Core\Sprinkle\Recipe\TwigExtensionRecipe`

Methods to implement : 
- `getTwigExtensions` : Return a list of [Twig Extension](/templating-with-twig/filters-and-functions#extending-twig-extensions) classes

    **Example:**
    ```php
    public function getTwigExtensions(): array
    {
        return [
            CoreExtension::class,
            AlertsExtension::class,
        ];
    }
    ```

## Removing default sprinkles

A default install, from the Skeleton, enables every [default sprinkle](/structure/sprinkles#bundled-sprinkles). But your app may not require every feature provided by these default sprinkles. For example, you might not need the Admin sprinkle if you don't need any user management features.

In this case, two files need to be edited : `composer.json` and the Sprinkle Recipe.

1. In **/composer.json**, remove the sprinkle from the Composer requirements :
    ```json
    "userfrosting/sprinkle-admin": "^5.1",
    ```

2. Since changes were made to *composer.json*, composer need to be updated (`composer update`).

3. In the Sprinkle Recipe, `Admin:class` can be removed from the `getSprinkles()` method:
    ```php 
    public function getSprinkles(): array
    {
        return [
            Core::class,
            Account::class,
            //Admin::class,
            AdminLTE::class,
        ];
    }
    ```

[notice=note]Technically, the **Core** sprinkle IS optional. However, remember it provides pretty much every base feature of UserFrosting, including database support. Without any sprinkles, i.e. only the UserFrosting Framework, your app would be a very basic Slim Application with routes support.[/notice]

## Customizing a dependent sprinkle

Sometimes you may want to customize one of the dependent sprinkles. For example, you may want to remove all routes defined in the Account sprinkle. Or use only one migrations from the `AwesomeStuff` sprinkle. There's two easy way to customize dependent sprinkles, either by cherry-picking resources or extending the dependent sprinkle's recipe.

### Cherry-picking resources

This method is best when you want a small number of resources from a dependent sprinkle. For example, when you want one migration from the `AwesomeStuff` sprinkle. The drawback is if the dependent sprinkle is updated, you may need to manually update your code. If you want to import many resources (but not all of them) from a dependent sprinkle, it's best to use the other method.

In this case, instead of adding the dependent sprinkle (in `getSprinkles`), you open the dependent sprinkle recipe and copy the code you want into your recipe.

### Extending dependent recipe

This method is best used when you want to *remove* a small number of resources from a dependent sprinkle. As with the previous method, if the dependent sprinkle is updated, you may need to manually update your code. If you want to only one resource from a dependent sprinkle, it's best to use the previous method to import one, than to remove everything else. 

For example, you may want to remove all routes defined in the Account sprinkle : 
```php

namespace UserFrosting\App;

use UserFrosting\Sprinkle\Account\Account;

/**
 * Overwrite main Account Sprinkle Class, to remove routes.
 */
class CustomAccount extends Account
{
    /**
     * {@inheritDoc}
     */
    public function getRoutes(): array
    {
        return [];
    }
}
```

In this case, instead of depending on `Account` in `getSprinkles`, you'll add `CustomAccount` in your sprinkle `getSprinkles`. All other methods from `Account` will be included via `CustomAccount`. 

You'll then have **two recipes** in your sprinkle, e.g.: `MyApp` and `CustomAccount`, side by side. `MyApp` will still be *main sprinkle*, referenced in `index.php` and `bakery`, since `CustomAccount` is a dependency of `MyApp`.
