---
title: Extending Services
metadata:
    description: You may extend UserFrosting's default services for additional functionality, or define completely new services in your Sprinkles.
taxonomy:
    category: docs
---

## Adding Services

You'll probably want to create your own services to modularize certain aspects of your own project.  For example, if your application needs to interact with some third-party API like Google Maps, you might create a `MapBuilder` class that encapsulates all of that functionality.  This is a cleaner and more manageable alternative to simply stuffing all of your code directly into your controller classes.

If you want to use a single instance of `MapBuilder` throughout your application, you'll probably end up defining it as a service.  To do this, you'll need to create a new service provider class in your site Sprinkle.

First, create a class `src/ServicesProvider/SiteServicesProvider.php` in your Sprinkle:

```
app
└── sprinkles
    └── site
        └── src
            └── ServicesProvider
                └── SiteServicesProvider.php
```

The skeleton of this file should look like:

```php
<?php
/**
 * Owl Fancy (https://owlfancy.com)
 *
 * @copyright Copyright (c) 2016 David Attenborough
 * @license   proprietary
 */
namespace UserFrosting\Sprinkle\Site\ServicesProvider;

use UserFrosting\Sprinkle\Core\Facades\Debug;

/**
 * Registers services for my site Sprinkle
 *
 * @author David Attenborough
 */
class SiteServicesProvider
{
    /**
     * Register my site services.
     *
     * @param Container $container A DI container implementing ArrayAccess and container-interop.
     */
    public function register($container)
    {

    }
}

```

Notice that we have one method, `register`, which takes the Pimple DIC as its lone parameter.  Ok, let's add our `MapBuilder` service!

```php
<?php
/**
 * Owl Fancy (https://owlfancy.com)
 *
 * @copyright Copyright (c) 2016 David Attenborough
 * @license   proprietary
 */
namespace UserFrosting\Sprinkle\Site\ServicesProvider;

use UserFrosting\Sprinkle\Core\Facades\Debug;
use UserFrosting\Sprinkle\Site\GoogleMaps\MapBuilder;

/**
 * Registers services for my site Sprinkle
 *
 * @author David Attenborough
 */
class SiteServicesProvider
{
    /**
     * Register my site services.
     *
     * @param Container $container A DI container implementing ArrayAccess and container-interop.
     */
    public function register($container)
    {
       /**
         * Map builder service.
         *
         * Needed to find our owls and track down those delicious voles.
         */        
        $container['mapBuilder'] = function ($c) {
            // Do what you need before building the object
            ...
            
            // Now, actually build the object
            $mapBuilder = new MapBuilder(...);
            return $mapBuilder;
        };
    }
}

```

You'll notice that we've added `use UserFrosting\Sprinkle\Site\GoogleMaps\MapBuilder;` to the top of the file.  This means that we don't have to use the fully qualified class name (with the entire namespace) every time we want to refer to the `MapBuilder` class.

Notice that we've defined our closure to return the object that we created.  Now, in a controller class, we can do something like:

```php
/**
 * Get the current location of the currently selected owl.
 *
 * Request type: GET
 */
public function getOwlCoordinates($request, $response, $args)
{
    ...
    
    $mapBuilder = $this->ci->mapBuilder;
    $coordinates = $mapBuilder->getCoordinates($myOwl);
    
    ...
}
```

## Extending Existing Services

Pimple also allows us to extend services that were defined previously, for example in another Sprinkle.  The `view` service loads UserFrosting's [Twig extensions]() to expose additional functions, filters, and variables in our templates.  If we want to define more global Twig variables in our site Sprinkle, we can create a new Twig extension and then add it to our `view` service by extending it in our service provider class.

First, create your new Twig extension class in `src/Twig/SiteExtension.php`:

```php
<?php
/**
 * Owl Fancy (https://owlfancy.com)
 *
 * @copyright Copyright (c) 2016 David Attenborough
 * @license   proprietary
 */
namespace UserFrosting\Sprinkle\Site\Twig;

use Interop\Container\ContainerInterface;

/**
 * Extends Twig functionality for my site sprinkle.
 *
 * @author David Attenborough
 */
class SiteExtension extends \Twig_Extension
{

    /**
     * @var ContainerInterface The global container object, which holds all your services.
     */
    protected $services;

    /**
     * Constructor.
     *
     * @param ContainerInterface $services The global container object, which holds all your services.
     */
    public function __construct(ContainerInterface $services)
    {
        $this->services = $services;
    }

    /**
     * Get the name of this extension.
     *
     * @return string
     */
    public function getName()
    {
        return 'userfrosting/site';
    }

    /**
     * Adds Twig global variable `nest`.
     *
     * @return array[mixed]
     */
    public function getGlobals()
    {
        return array(
            'pelletCounter'   => $this->services->mapBuilder->getNest()
        );
    }
}

```

Now, back in `SiteServicesProvider.php`, we can extend the `view` service to load this extension:

```php
<?php
/**
 * Owl Fancy (https://owlfancy.com)
 *
 * @copyright Copyright (c) 2016 David Attenborough
 * @license   proprietary
 */
namespace UserFrosting\Sprinkle\Site\ServicesProvider;

use UserFrosting\Sprinkle\Core\Facades\Debug;
use UserFrosting\Sprinkle\Site\GoogleMaps\MapBuilder;
use UserFrosting\Sprinkle\Site\Twig\SiteExtension;

/**
 * Registers services for my site Sprinkle
 *
 * @author David Attenborough
 */
class SiteServicesProvider
{
    /**
     * Register my site services.
     *
     * @param Container $container A DI container implementing ArrayAccess and container-interop.
     */
    public function register($container)
    {
        /**
         * Extends the 'view' service with the SiteExtension for Twig.
         *
         * Adds global variables to Twig for my site Sprinkle.
         */
        $container->extend('view', function ($view, $c) {
            $twig = $view->getEnvironment(); 
            $extension = new SiteExtension($c);
            $twig->addExtension($extension);

            return $view;
        });

        /**
         * Map builder service.
         *
         * Needed to find our owls and track down those delicious voles.
         */        
        $container['mapBuilder'] = function ($c) {
            // Do what you need before building the object
            ...
            
            // Now, actually build the object
            $mapBuilder = new MapBuilder(...);
            return $mapBuilder;
        };
    }
}

```

When our Sprinkle is loaded, Pimple will use the callback defined in `$container->extend('view', ...` to load our extension **on top of** the `view` service that was originally defined in the core Sprinkle, as well as any modifications made in other Sprinkles' service providers.  This is summarized in the following diagram:

![Extending a service multiple times](/images/extending-services.png)

## Overriding Existing Services

Most of the default services that UserFrosting defines can be completely overridden in your Sprinkle, simply by redefining them as if they were a new service.  The exception is for services that have already been invoked before the SprinkleManager has finished loading all of the Sprinkles.  These services are:

- `config`
- `locator`
- `shutdownHandler`
- `sprinkleManager`
