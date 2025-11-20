---
title: Adding Services
metadata:
    description: You may extend UserFrosting's default services for additional functionality, or define completely new services in your Sprinkles.
taxonomy:
    category: docs
---

You'll probably want to create your own services to modularize certain aspects of your own project. For example, if your application needs to interact with some third-party API like Google Maps, you might create a `MapBuilder` class that encapsulates all of that functionality. This is a cleaner and more manageable alternative to simply stuffing all of your code directly into your controller classes.

If you want to use a single instance of `MapBuilder` throughout your application, you'll probably end up defining it as a service. To do this, you'll need to create a new  `MapBuilderService` class in your site sprinkle and register it in your [Sprinkle Recipe](/sprinkles/recipe#services).

You can actually create one big service provider for all your services, but it's best to create different provider classes for each service. This makes it easier to test and debug each of your services. It also makes things easier if you need to extend or disable a service in another sprinkle down the road. With this setup, each service resides in its own provider class instead of the global `ServiceProvider` class. For example :

```
app
└── src
    └── ServicesProvider
        ├── GenericServices.php
        ├── MapBuilderService.php
        └── FooService.php
```

### Create your service

First, we'll create the service class. This class **must** implement the `UserFrosting\ServicesProvider\ServicesProviderInterface` interface. It must contain the `register` method, which returns an array of [service definitions](/dependency-injection/the-di-container#service-providers-definitions). 

**app/src/ServicesProvider/MapBuilderService.php**:

```php
<?php

namespace UserFrosting\Sprinkle\Site\ServicesProvider;

use UserFrosting\ServicesProvider\ServicesProviderInterface;
use UserFrosting\Sprinkle\Site\GoogleMaps\MapBuilder;

/**
 * MapBuilder service.
 *
 * Registers:
 *  - \UserFrosting\Sprinkle\Site\GoogleMaps\MapBuilder
 */
class MapBuilderService implements ServicesProviderInterface
{
   public function register(): array
    {
        return [
            MapBuilder::class => function () {
                // Do what you need before building the object
                ...

                // Now, actually build the object
                $mapBuilder = new MapBuilder(...);

                return $mapBuilder;
            },
        ];
    }
}
```

[notice=tip]You'll notice that we've added `use UserFrosting\Sprinkle\Site\GoogleMaps\MapBuilder;` to the top of the file. This means that we don't have to use the fully qualified class name (with the entire namespace) every time we want to refer to the `MapBuilder` class.[/notice]

If you need to pull in another service, for example the config to retrieve an API key, you can add them as the parameter, and the dependency injector will automatically pick it up.

```php
...
MapBuilder::class => function (Config $config) {
    $apiKey = $config['api.key'];

    // Now, actually build the object
    $mapBuilder = new MapBuilder($apiKey);

    return $mapBuilder;
},
...
```

### Register your service

The next step is to tell UserFrosting to load your service in your [Sprinkle Recipe](/sprinkles/recipe#getservices). To do so, you only need to list all the service providers you want to automatically register inside the `$getServices` property of your sprinkle class :

**app/src/MyApp.php** :
```php
<?php

namespace UserFrosting\Sprinkle\Site;

use UserFrosting\Sprinkle\Site\ServicesProvider\MapBuilderService;
use UserFrosting\Sprinkle\Site\ServicesProvider\FooService;
use UserFrosting\System\Sprinkle\Sprinkle;

class MyApp implements SprinkleRecipe
{
    // ...

    public function getServices(): array
    {
        return [
            MapBuilderService::class,
            FooService::class,
        ];
    }

    // ...
}
```

That's it! Behind the scenes, UserFrosting will register every definition from each service provider with the DI container, following the sprinkle [dependency tree](/sprinkles/recipe#dependent-sprinkles) during the [application lifecycle](/advanced/application-lifecycle).
