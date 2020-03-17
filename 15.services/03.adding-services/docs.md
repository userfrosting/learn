---
title: Adding Services
metadata:
    description: You may extend UserFrosting's default services for additional functionality, or define completely new services in your Sprinkles.
taxonomy:
    category: docs
---

You'll probably want to create your own services to modularize certain aspects of your own project. For example, if your application needs to interact with some third-party API like Google Maps, you might create a `MapBuilder` class that encapsulates all of that functionality. This is a cleaner and more manageable alternative to simply stuffing all of your code directly into your controller classes.

If you want to use a single instance of `MapBuilder` throughout your application, you'll probably end up defining it as a service. To do this, you'll need to create a new global `ServicesProvider` class in your site Sprinkle, or register an independent `MapBuilderService` class.

## In a global services provider class

First, create a class `src/ServicesProvider/ServicesProvider.php` in your Sprinkle:

```
app
└── sprinkles
    └── site
        └── src
            └── ServicesProvider
                └── ServicesProvider.php
```

The skeleton of this file should look like:

```php
<?php
/**
 * Owl Fancy (https://owlfancy.com)
 *
 * @license   All rights reserved.
 */
namespace UserFrosting\Sprinkle\Site\ServicesProvider;

use Psr\Container\ContainerInterface;
use UserFrosting\Sprinkle\Core\Facades\Debug;

/**
 * Registers services for my site Sprinkle
 */
class ServicesProvider
{
    /**
     * Register my site services.
     *
     * @param ContainerInterface $container
     */
    public function register(ContainerInterface $container)
    {

    }
}

```

Notice that we have one method, `register`, which takes the Pimple DIC as its lone parameter. Ok, let's add our `MapBuilder` service in it !

```php
<?php
/**
 * Owl Fancy (https://owlfancy.com)
 *
 * @license   All rights reserved.
 */
namespace UserFrosting\Sprinkle\Site\ServicesProvider;

use Psr\Container\ContainerInterface;
use UserFrosting\Sprinkle\Core\Facades\Debug;
use UserFrosting\Sprinkle\Site\GoogleMaps\MapBuilder;

/**
 * Registers services for my site Sprinkle
 */
class ServicesProvider
{
    /**
     * Register my site services.
     *
     * @param ContainerInterface $container
     */
    public function register(ContainerInterface $container)
    {
       /**
         * Map builder service.
         *
         * Needed to find our owls and track down those delicious voles.
         */
        $container['mapBuilder'] = function ($c) {
            // Do what you need before building the object
            ...

            // Access curent user
            $user = $c->currentUser;

            // Now, actually build the object
            $mapBuilder = new MapBuilder(...);

            return $mapBuilder;
        };
    }
}

```

You'll notice that we've added `use UserFrosting\Sprinkle\Site\GoogleMaps\MapBuilder;` to the top of the file. This means that we don't have to use the fully qualified class name (with the entire namespace) every time we want to refer to the `MapBuilder` class.

Notice that we've defined our closure to return the object that we created. Now, in a controller class, we can do something like:

```php
/**
 * Get the current location of the currently selected owl.
 *
 * Request type: GET
 */
public function getOwlCoordinates(Request $request, Response $response, array $args)
{
    ...

    $mapBuilder = $this->ci->mapBuilder;
    $coordinates = $mapBuilder->getCoordinates($myOwl);

    ...
}
```

As you build your app, you'll be able to add more services to `src/ServicesProvider/ServicesProvider.php`. Simply add all services callabcks inside the `register` function :

```php
public function register(ContainerInterface $container)
{
    /**
     * Map builder service.
     */
    $container['mapBuilder'] = function ($c) {
        ...
    };

    /**
     * Foo service.
     */
    $container['foo'] = function ($c) {
        ...
    };

    /**
     * Bar service.
     */
    $container['bar'] = function ($c) {
        ...
    };
}
```

[notice=warning]It is very important that your class be named `ServicesProvider`, and be in the `ServicesProvider` namespace of your Sprinkle. Otherwise, UserFrosting will be unable to find and automatically register your services![/notice]

## In an independent services class

Alternatively, you can also create different provider classes for each services. This makes it easier to test and debug each of your services. It also makes things easier if you need to extend a service in another sprinkle down the road.

With this setup, each service reside in it's own provider class instead of the global `ServiceProvider` class. For example :

```
app
└── sprinkles
    └── site
        └── src
            └── ServicesProvider
                └── ServicesProvider.php
                └── MapBuilderService.php
                └── FooService.php
```

[notice=tip]This method will eventually be the default method in future version of UserFrosting. You're encouraged to use this method now to avoid necessary code upgrade later.[/notice]

### Create your service

First, we'll create the service class. This class **must** extends a `BaseServicesProvider`. Inside this class, you can register your callback the same way you'll do in a normal ServiceProvider. The only difference is the callback doesn't accept the `$c` parameter. Instead, the DIC is avaiable in the class `ci` property, accesible with `$this->ci`, just like in a controller class.

**app/sprinkles/site/src/ServicesProvider/MapBuilderService.php**:

```php
<?php

namespace UserFrosting\Sprinkle\Site\ServicesProvider;

use UserFrosting\Sprinkle\Core\ServicesProvider\BaseServicesProvider;
use UserFrosting\Sprinkle\Site\GoogleMaps\MapBuilder;

/**
 * MapBuilder service.
 *
 * Registers:
 *  - mapBuilder : \UserFrosting\Sprinkle\Site\GoogleMaps\MapBuilder
 */
class MapBuilderService extends BaseServicesProvider
{
    /**
     * {@inheritdoc}
     */
    public function register(): void
    {
        $this->ci['mapBuilder'] = function () {
            // Do what you need before building the object
            ...

            // Access curent user
            $user = $this->ci->currentUser;

            // Now, actually build the object
            $mapBuilder = new MapBuilder(...);

            return $mapBuilder;
        };
    }
}
```

In most cases, the callback is not necessary and the registration can be shorten to :

```php
$this->ci['mapBuilder'] = new MapBuilder(...);
```

### Register your Service

The next step is to tell UserFrosting to load your service. While `ServicesProvider` is loaded automatically, custom classes need to be manually defined in your [Sprinkle initiator class](). To do so, you only need to list all the service you want to automatically register inside the `$servicesproviders` property of your sprinkle class :

**app/sprinkles/site/src/Site.php** :
```php
<?php

namespace UserFrosting\Sprinkle\Site;

use UserFrosting\Sprinkle\Site\ServicesProvider\MapBuilderService;
use UserFrosting\Sprinkle\Site\ServicesProvider\FooService;
use UserFrosting\System\Sprinkle\Sprinkle;

/**
 * Bootstrapper class for the 'site' sprinkle.
 */
class Site extends Sprinkle
{
    /**
     * @var string[] List of services provider to register
     */
    protected $servicesproviders = [
        MapBuilderService::class,
        FooService::class,
    ];
}
```

[notice=note]Note in the previous example how both `MapBuilderService` and `FooService` are registered as the `Site` service provider.[/notice]
