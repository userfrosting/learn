---
title: Extending Existing Services
metadata:
    description: You may extend UserFrosting's default services for additional functionality, or define completely new services in your Sprinkles.
taxonomy:
    category: docs
---

Pimple also allows us to extend services that were defined previously, for example in another Sprinkle.

Most of the default services that UserFrosting defines can be completely overridden in your Sprinkle, simply by redefining them as if they were a new service. The exception is for system services, which have already been invoked before the SprinkleManager can load the Sprinkles. These services are:

- `eventDispatcher`
- `locator`
- `sprinkleManager`

## Overriding Existing Services

Extending a service is done using the same callback used to **register** one, except said callback is registered with the `extend` method instead of adding a new array key inside of the main `register` function :

```php
$container->extend('serviceName', function ($serviceName, $c) {
    ...
});
```

Simply replace `serviceName` with the name of the service you want to extend. For example, if you want to extend the `classMapper` service to add a `Maps` database model :

```php
public function register(ContainerInterface $container)
{
    /*
     * Extend the 'classMapper' service to register model classes.
     */
    $container->extend('classMapper', function ($classMapper, $c) {
        $classMapper->setClassMapping('maps', 'UserFrosting\Sprinkle\Site\Database\Models\Maps');

        return $classMapper;
    });
}
```

[notice=note]When extending a service, UserFrosting will always apply the extension **on top** of the previously defined service. Since each sprinkle is run in the same order they are defined in `sprinkles.json`, it's important to keep in mind you might not aways received the `core` sprinkle definition, for example, and that your own extension can be overwritten down the road by a subsequent sprinkle.[/notice]

<!--## Overwriting existing service class

If your service was registered using an [independent service class](/services/adding-services#in-an-independent-services-class), the process is a little bit different.-->

<!-- NOTE : Waiting for next version for this part, as it may change in the future... -->

## Extending Twig Extensions

The `view` service loads UserFrosting's [Twig extensions](/templating-with-twig/filters-and-functions) to expose additional functions, filters, and variables in our templates. If we want to define more global Twig variables in our site Sprinkle, we can create a new Twig extension and then add it to our `view` service by extending it in our service provider class. An extension which adds globals like this must also implement Twig's `GlobalsInterface`.

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
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

/**
 * Extends Twig functionality for my site sprinkle.
 *
 * @author David Attenborough
 */
class Extension extends AbstractExtension implements GlobalsInterface
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
            'nest'   => $this->services->mapBuilder->getNest()
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

use Psr\Container\ContainerInterface;
use UserFrosting\Sprinkle\Core\Facades\Debug;
use UserFrosting\Sprinkle\Site\GoogleMaps\MapBuilder;
use UserFrosting\Sprinkle\Site\Twig\Extension;

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

When our Sprinkle is loaded, Pimple will use the callback defined in `$container->extend('view', ...` to load our extension **on top of** the `view` service that was originally defined in the core Sprinkle, as well as any modifications made in other Sprinkles' service providers. This is summarized in the following diagram:

![Extending a service multiple times](/images/extending-services.png)
