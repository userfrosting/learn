---
title: Twig Filters and Functions
metadata:
    description:
taxonomy:
    category: docs
---
[plugin:content-inject](/modular/_update5.0)

### checkAccess

You can perform permission checks in your Twig templates using the `checkAccess` helper function. This is useful when you want to render a portion of a page's content conditioned on whether or not a user has a certain permission. For example, this can be used to hide a navigation menu item for pages that the current user does not have access to:

```twig
{% if checkAccess('uri_users') %}
<li>
    <a href="{{site.uri.public}}/users"><i class="fa fa-user fa-fw"></i> {{ translate("USER", 2) }}</a>
</li>
{% endif %}
```

### translate

```twig
{{ translate("ACCOUNT_SPECIFY_USERNAME") }}

{{ translate("ACCOUNT_USER_CHAR_LIMIT", {min: 4, max: 200}) }}
```

### path_for

You can use `path_for` in your Twig templates to get the URL for a named route. This Twig function is simply mapped to the Slim router `pathFor(string $name, array $data, array $queryParams)` instance method.

```html
<li>
    <a href="{{ path_for('awesome-owls' )}}">Owls</a>
</li>
```
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

To contribute to this documentation, please submit a pull request to our [learn repository](https://github.com/userfrosting/learn/tree/master/pages).
