---
title: Extending Existing Services
metadata:
    description: You may extend UserFrosting's default services for additional functionality, or define completely new services in your Sprinkles.
taxonomy:
    category: docs
---

## Extending Existing Services

Pimple also allows us to extend services that were defined previously, for example in another Sprinkle.  The `view` service loads UserFrosting's [Twig extensions](/templating-with-twig/filters-and-functions) to expose additional functions, filters, and variables in our templates.  If we want to define more global Twig variables in our site Sprinkle, we can create a new Twig extension and then add it to our `view` service by extending it in our service provider class.

First, create your new Twig extension class in `src/Twig/Extension.php`:

```php
<?php
/**
 * Owl Fancy (https://owlfancy.com)
 *
 * @license   All rights reserved.
 */
namespace UserFrosting\Sprinkle\Site\Twig;

use Psr\Container\ContainerInterface;

/**
 * Extends Twig functionality for my site sprinkle.
 *
 * @author David Attenborough
 */
class Extension extends \Twig_Extension
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

Now, back in `ServicesProvider.php`, we can extend the `view` service to load this extension:

```php
<?php
/**
 * Owl Fancy (https://owlfancy.com)
 *
 * @license   All rights reserved.
 */
namespace UserFrosting\Sprinkle\Site\ServicesProvider;

use UserFrosting\Sprinkle\Core\Facades\Debug;
use UserFrosting\Sprinkle\Site\GoogleMaps\MapBuilder;
use UserFrosting\Sprinkle\Site\Twig\Extension;

/**
 * Registers services for my site Sprinkle
 *
 * @author David Attenborough
 */
class ServicesProvider
{
    /**
     * Register my site services.
     *
     * @param Container $container A DI container implementing ArrayAccess and psr-container.
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
            $extension = new Extension($c);
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

Most of the default services that UserFrosting defines can be completely overridden in your Sprinkle, simply by redefining them as if they were a new service.  The exception is for system services, which have already been invoked before the SprinkleManager can load the Sprinkles.  These services are:

- `eventDispatcher`
- `locator`
- `sprinkleManager`
- `streamBuilder`

### Overwriting existing service class

TODO