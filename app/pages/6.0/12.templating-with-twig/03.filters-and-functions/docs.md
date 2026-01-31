---
title: Twig Filters and Functions
description: ""
wip: true
---

### config

You can access any [configuration value](configuration/config-files) directly in your Twig templates using the `config` helper function. For example, to display `site.title`:

```twig
{{ config('site.title') }}
```

> [!WARNING]
> Use this carefully, as sensitive information (ie. passwords) could be stored in config

### checkAccess

You can perform permission checks in your Twig templates using the `checkAccess` helper function. This is useful when you want to render a portion of a page's content conditioned on whether or not a user has a certain permission. For example, this can be used to hide a navigation menu item for pages that the current user does not have access to:

```twig
{% if checkAccess('uri_users') %}
<li>
    <a href="{% urlFor('uri_users') %}"><i class="fa fa-user fa-fw"></i> {{ translate("USER", 2) }}</a>
</li>
{% endif %}
```

### translate

```twig
{{ translate("ACCOUNT_SPECIFY_USERNAME") }}

{{ translate("ACCOUNT_USER_CHAR_LIMIT", {min: 4, max: 200}) }}
```

### urlFor

You can use `urlFor` in your Twig templates to get the URL for a named route. This Twig function is simply mapped to the Slim routeParser `urlFor(string $routeName, array $data, array $queryParams): string` instance method.

```html
<li>
    <a href="{{ urlFor('awesome-owls' )}}">Owls</a>
</li>
```

## Extending Twig Extensions

The `view` service loads UserFrosting's [Twig extensions](templating-with-twig/filters-and-functions) to expose additional functions, filters, and variables in our templates. If we want to define more global Twig variables in our site Sprinkle, we can create a new Twig extension and then add it to our `view` service by extending it in our service provider class. An extension which adds globals like this must also implement Twig's `GlobalsInterface`.

First, create your new Twig extension class in `src/Twig/Extension.php`:

```php
<?php

namespace UserFrosting\Sprinkle\Site\Twig;

use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use UserFrosting\Config\Config;

class Extension extends AbstractExtension implements GlobalsInterface
{
    /**
     * Inject required services.
     * 
     * @param Config $config The config service
     */
    public function __construct(
        protected Config $config,
    ) {
    }

    /**
     * Adds Twig global variables `site`.
     *
     * @return mixed[]
     */
    public function getGlobals(): array
    {
        return [
            'owls' => $this->config->get('owls'),
        ];
    }
}
```

Now, back in your Sprinkle Recipe, we can register the `Extension` class, via the `TwigExtensionRecipe` sub-recipe.

```php
<?php

namespace UserFrosting\Sprinkle\Site;

// ...
use UserFrosting\Sprinkle\Core\Sprinkle\Recipe\TwigExtensionRecipe; // <-- Add this
use UserFrosting\Sprinkle\Site\Twig\Extension; // <-- Add this
// ...

class Core implements
    SprinkleRecipe,
    TwigExtensionRecipe, // <-- Add this
{
    // ...

    public function getTwigExtensions(): array
    {
        return [
            Extension::class,
        ];
    }
    
    // ...
}
```
