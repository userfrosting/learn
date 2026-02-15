---
title: Twig Filters and Functions
description: UserFrosting provides custom Twig functions, filters, and global variables to simplify common tasks in your templates.
---

### config

You can access any [configuration value](/configuration/config-files) directly in your Twig templates using the `config` helper function. For example, to display `site.title`:

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

### Vite Asset Functions

UserFrosting uses [Vite](https://vitejs.dev) for modern asset bundling. Three Twig functions help you include Vite-compiled assets in your templates:

#### vite_js

Includes JavaScript entry points compiled by Vite. Pass the entry file path relative to your `app/assets/` directory:

```twig
{{ vite_js('main.ts') }}
```

This generates the appropriate `<script>` tag with the correct path to your compiled JavaScript bundle, including cache-busting hashes in production.

#### vite_css

Includes CSS entry points compiled by Vite:

```twig
{{ vite_css('main.ts') }}
```

Note that you pass the TypeScript/JavaScript entry file, and Vite automatically includes any CSS imported by that entry. This generates `<link>` tags for all stylesheets associated with the entry point.

#### vite_preload

Preloads assets for better performance by telling the browser to fetch them early:

```twig
{{ vite_preload('main.ts') }}
```

This generates `<link rel="modulepreload">` tags for JavaScript modules and `<link rel="preload">` tags for other assets, allowing the browser to fetch them in parallel with the page load.

> [!NOTE]
> Further information on how to use these functions and how they work under the hood will be covered in the [Assets and Vite](/assets-vite) chapter.

## Filters

Twig filters modify variables before outputting them. UserFrosting provides custom filters for common formatting tasks.

### phone

Formats phone numbers into a standardized format:

```twig
{{ "5551234567"|phone }}
{# Output: (555) 123-4567 #}
```

### unescape

Decodes HTML entities back to their original characters. This is the opposite of Twig's built-in `e` (escape) filter:

```twig
{{ "&lt;strong&gt;Bold&lt;/strong&gt;"|unescape|raw }}
{# Output: <strong>Bold</strong> #}
```

> [!WARNING]
> Use `unescape|raw` carefully. Only use it on trusted content to avoid XSS vulnerabilities.

## Global Variables

Global variables are automatically available in all Twig templates without needing to pass them from your controller.

### site

Contains site-wide configuration from your `config/default.php` file. Access any value from the `site` configuration array:

```twig
{{ site.title }}
{{ site.author }}
{{ site.uri.public }}
```

This is equivalent to calling `config('site.title')` but provides direct object-style access to site configuration.

### csrf

Provides CSRF (Cross-Site Request Forgery) protection tokens for forms:

```twig
<form method="POST" action="{{ urlFor('profile.settings') }}">
    <input type="hidden" name="{{ csrf.keys.name }}" value="{{ csrf.name }}">
    <input type="hidden" name="{{ csrf.keys.value }}" value="{{ csrf.value }}">
    
    {# Your form fields #}
    <button type="submit">Submit</button>
</form>
```

The `csrf` object contains:
- `csrf.keys.name` - The name of the CSRF token name field
- `csrf.keys.value` - The name of the CSRF token value field  
- `csrf.name` - The actual CSRF token name
- `csrf.value` - The actual CSRF token value

> [!IMPORTANT]
> Always include CSRF tokens in forms that modify data (POST, PUT, DELETE requests) to protect against CSRF attacks.

### current_user

Contains the currently authenticated user object, or `null` if no user is logged in:

```twig
{% if current_user %}
    <p>Welcome, {{ current_user.first_name }}!</p>
    <p>Email: {{ current_user.email }}</p>
{% else %}
    <p>Please log in.</p>
{% endif %}
```

Access any property of the User model:

```twig
{{ current_user.user_name }}
{{ current_user.first_name }}
{{ current_user.last_name }}
{{ current_user.email }}
{{ current_user.group.name }}
```

### currentLocale

Contains the identifier of the currently active locale (e.g., `en_US`, `fr_FR`):

```twig
<html lang="{{ currentLocale }}">
```

This is useful for setting the page language or displaying the current language to users.

### user_agent

Provides information about the user's browser and device:

```twig
{{ user_agent.browser }}   {# e.g., "Safari" #}
{{ user_agent.version }}   {# e.g., "18.3.1" #}
{{ user_agent.platform }}  {# e.g., "Macintosh" #}
{{ user_agent.ip }}        {# e.g., "127.0.0.1" #}
```

This can be useful for displaying browser-specific messages or logging user information:

```twig
{% if user_agent.browser == "Internet Explorer" %}
    <div class="alert alert-warning">
        For the best experience, please use a modern browser.
    </div>
{% endif %}
```

## Extending Twig Extensions

The `view` service loads UserFrosting's [Twig extensions](/pages-and-layout/filters-and-functions) to expose additional functions, filters, and variables in our templates. If we want to define more global Twig variables in our site Sprinkle, we can create a new Twig extension and then add it to our `view` service by extending it in our service provider class. An extension which adds globals like this must also implement Twig's `GlobalsInterface`.

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
