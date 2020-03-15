---
title: Exporting Variables to Javascript
metadata:
    description: The server-side variables that UserFrosting automatically exports to your pages as Javascript variables, and suggestions for exporting additional variables in your application.
taxonomy:
    category: docs
---

We often need to access the value of some server-side variable in our client-side code. For example, we use the value `site.uri.public` throughout our Javascript code when building urls for AJAX requests. The value of this variable is taken directly from UserFrosting's configuration variable of the same name, and embedded into a Javascript variable on every page.

There are a number of ways to get the values of specific variables from the server to the client. For example:

- Embedding data in the page DOM (HTML attributes), and then retrieving it from the DOM using `.prop`, `.val`, `.data`, or some other Javascript method
- Making a separate AJAX request for the value of the variable
- Printing the value of a PHP/Twig variable directly into a `<script>` tag and assigning it to a Javascript variable

Depending on the context of your application, one of these options might be more appropriate than the others. For the purposes of this section however, we will focus on the last option, and the "global" Javascript variables that UserFrosting makes available on each page. This is the best choice when you have miscellaneous values that aren't closely associated with any specific element of the DOM, and are generic enough that they don't warrant the trouble of making a separate AJAX request for their values.

No matter how you go about it, it's important to keep in mind that **you're not directly accessing server-side variables in Javascript.** Keep in mind our [discussion on the client-server conversation](/background/the-client-server-conversation) - the client makes a **request** for the page and receives a fixed piece of HTML in the **response**.

Any PHP variables we receive in the response are really just **copies of their server-side values** when the page was rendered. Our page can't change these values on the server without making another request - and even then, changes won't persist from one request to the next unless we use some sort of persistence mechanism (sessions, database, etc).

## Site variables

By default, UserFrosting will create a global `site` Javascript variable on every page. `site` is a JSON object that contains a copy of the main `site` config array, [defined in PHP](/configuration/config-files) (ie. `app/sprinkles/core/config/default.php`). The following values are among the ones contained in the `site` object :

- `site.uri.public`: Base url of your site (e.g., `https://example.com`).
- `site.debug.ajax`: `true` if AJAX debugging mode is enabled, false otherwise.
- `site.csrf.keys.name`: The name of the CSRF `name` attribute expected by the CSRF middleware (Defaults to `csrf_name`).
- `site.csrf.keys.value`: The name of the CSRF `value` attribute expected by the CSRF middleware (Defaults to `csrf_value`).
- `site.csrf.name`: The value of the CSRF `name` attribute.
- `site.csrf.value`: The value of the CSRF `value` attribute.

Notice that all of these variables are nested under a single, top-level `site` object which is constructed in the `core/templates/pages/partials/config.js.twig` template. By formatting these as keys in a JSON object, rather than making each one an individual variable, we avoid polluting Javascript's global namespace with too many identifiers.

To add, remove, or modify the contents of the `site` object, simply extends the `site` configuration [in your Sprinkle](/configuration/config-files#file-structure).

Alternatively, you can override `config.js.twig` in your Sprinkle. `config.js.twig` itself pulls its values from Twig's global variables (`site`, `current_user`, etc). Keep in mind that you can add global variables to Twig by [creating a Twig extension](https://twig.sensiolabs.org/doc/2.x/advanced.html#creating-an-extension) and then loading your extension by [extending the `view` service](/services/extending-services#extending-existing-services). This process is summarized in this diagram:

![Extending UserFrosting's client-side site variable](/images/extending-site-variable.png)

[notice=warning]Remember, any data you place in the `site` variable will be visible to the end-user - all they have to do is "View source"! Don't put any sensitive or private information in this variable.[/notice]

## Page-specific variables

For your convenience, the `core/templates/pages/partials/page.js.twig` template will generate a `page` JSON object. This is similar to `site`, but is populated by passing an array of data to the `page` key when rendering a template:

```php
// In account/src/Controller/AccountController.php

public function pageRegister($request, $response, $args)
{
    // ...

    // Load validation rules
    $schema = new RequestSchema("schema://requests/register.yaml");
    $validatorRegister = new JqueryValidationAdapter($schema, $this->ci->translator);

    // Pass them to the `page` key of the template placeholders
    return $this->ci->view->render($response, 'pages/register.html.twig', [
        "page" => [
            "validators" => [
                "register" => $validatorRegister->rules()
            ]
        ]
    ]);
}
```

You can then include the `page.js.twig` component in your page template's `scripts_page` block, before any page-specific asset bundles:

```twig
{# In account/templates/pages/register.html.twig #}

{% block scripts_page %}
    <!-- Include page-specific variables -->
    <script>
        {% include "pages/partials/page.js.twig" %}
    </script>

    <!-- Include page-specific asset bundles -->
    {{ assets.js('js/pages/register') | raw }}
{% endblock %}
```

Twig will automatically convert the array you passed to `render` in your page controller method to a JSON object:

```js

// Appears in the rendered page DOM for /account/register

var page = {
    "validators": {
        "register": {
            "rules": {
                "user_name": {
                    "rangelength": [
                        1,
                        50
                    ],
                    "noLeadingWhitespace": true,
                    "noTrailingWhitespace": true,
                    "required": true,
                    "username": true
                },
                ...
            },
            "messages": {
                "user_name": {
                    "rangelength": "Username must be between 1 and 50 characters in length.",
                    "noLeadingWhitespace": "The value for 'Username' cannot begin with spaces, tabs, or other whitespace.",
                    "noTrailingWhitespace": "The value for 'Username' cannot end with spaces, tabs, or other whitespace.",
                    "required": "Please specify a value for 'Username'.",
                    "username": "Username may consist only of lowercase letters, numbers, '.', '-', and '_'."
                },
                ...
            }
        }
    }
};
```

## Dynamically extending JSON objects

Occasionally, you will want to dynamically modify the contents of `site`, `page`, or some other JSON variable. For example, you might want to override a variable on a specific page. To do this, you can use jQuery's `extend` method:

```js
<script>
    site = $.extend(
        true, // deep extend
        {
            "debug" : {
                "ajax" : false // Disable AJAX debugging on this page only
            }
        },
        site
    );
</script>
```

You should do this in your page template's `scripts_page` block, after loading the original versions of the variables but before loading any page asset bundles.
