---
title: Entrypoints
description: Entrypoints bundles allow you to group one or more asset references for reuse throughout your application.
---

> [!WARNING]
> **This documentation is obsolete.** UserFrosting 6 uses **Vite** as the default asset bundler, not Webpack Encore.
>
> Please see:
> - [Sprinkle Assets](asset-management/sprinkle-assets) for multi-sprinkle asset architecture
> - [Advanced Usage](asset-management/advanced) for code splitting and optimization
> - [Migration Guide](asset-management/migration) to migrate from Webpack Encore

Most CSS and Javascript resources should be integrated into your pages through **entrypoints**. Entrypoints defines groups of assets for which UserFrosting can automatically render `<link>` or `<script>` tags in your pages.

## Concept

Webpack is a module bundler, which means that you can import other JavaScript files. A single javascript file can require all of the dependencies we needs (e.g. jQuery or React), including any CSS, in a single place. You can see this with a JavaScript require (or [import](https://stackoverflow.com/questions/46677752/the-difference-between-requirex-and-import-x)) statement :

**app/assets/main.js**
```js
// ------ Import Base Theme ------
require('theme-adminlte');
```

Encore's job (via Webpack) is simple: to read and follow all of the import/require statements and create one final `main.js` (and `main.css`) that contains everything you need.

Encore may split the end result into multiple files for performance (using [split chunks feature](https://symfony.com/doc/current/frontend/encore/split-chunks.html)), but all of that code is still downloaded on every page if you were to include the `main.js` file.

What if we have some extra JavaScript or CSS (e.g. for performance) that we only want to include on certain pages? The solution is to create page-specific JavaScript or CSS (e.g. checkout, account, etc.). To handle this, create a new "entry" JavaScript file for each page:

**app/assets/checkout.js**
```js
// custom code for your checkout page
require('./pages/checkout');
require('./utility/carousel');
```

**app/assets/account.js**
```js
// custom code for your account page
require('./pages/account');
require('./utility/carousel');
require('./utility/password');
```

The shared resources (e.g. template resources) can be loaded on every page along side any page or feature specific entries.

## Defining entries

Now that the "entry" files have been created, we need to register them with Encore as entrypoints. Entrypoints are configured in `/webpack.entries.js` by default.

> [!NOTE]
> Entrypoints can also be defined in `/webpack.config.js` directly. However, UserFrosting defines them in a separate file, as it's easier to include other sprinkles entries.

**webpack.entries.js**
```js
const path = require('path');
module.exports = {
    'main': path.resolve(__dirname), './app/assets/main.js',
    'page.checkout': path.resolve(__dirname), './app/assets/checkout.js',
    'page.account': path.resolve(__dirname), './app/assets/account.js',
};
```

This tells Encore to load the `app/assets/main.js` file (for example) and follow all of the `require()` statements. It will then package everything together and - thanks to the first `main` argument - output final `public/assets/main.js` and `public/assets/main.css` files.

Sprinkle's overriding properties also applies to the entries themselves. In other words, naming an entry `page.account` in your own sprinkle will replace the one defined in the `account` sprinkle with yours, for example. In the process, it will probably break core user management functionality. In general, be careful to provide your entries with a unique name to avoid difficult to diagnose runtime errors.

## Rendering entrypoints

To render an entry on a page, simply use the `encore_entry_script_tags()` (Javascript) and `encore_entry_link_tags()` (CSS) Twig helpers:

```html
{{ encore_entry_script_tags('main') }}
```

UserFrosting will automatically generate the `<script>` tags for Javascript bundles, or `<link>` tags for CSS bundles, when it renders the template:

```html
<script src="/assets/app.js"></script>
<script src="/assets/runtime.js"></script>
<script src="/assets/vendors-node_modules_jquery_dist_jquery_js.js"></script>
<script src="/assets/vendors-node_modules_moment_locale_af_js-node_modules_moment_locale_ar-dz_js-node_modules_mom-248d90.js"></script>
<script src="/assets/vendors-node_modules_theme-adminlte_app_assets_main_js.js"></script>
```

## Template blocks for bundles

You can use the `encore_entry_script_tags()` and `encore_entry_link_tags()` helpers anywhere in a Twig template, of course, but best practice dictates that CSS links should go in the `<head>` element of your page, and Javascript tags should go just at the end of your `<body>` element.

To facilitate placement of CSS and Javascript tags, the base abstract template `pages/abstract/base.html.twig` defines a number of template blocks and content files. For CSS, these blocks are:

```html
{% block stylesheets %}
    {# Override this file in a child sprinkle to override site-level stylesheets. #}
    {% include 'content/stylesheets_site.html.twig' %}

    {# Override this block in a child layout template or page template to specify or override stylesheets for groups of similar pages. #}
    {% block stylesheets_page_group %}{% endblock %}

    {# Override this block in a child layout template or page template to specify or override page-level stylesheets. #}
    {% block stylesheets_page %}{% endblock %}
{% endblock %}
```

Similarly, for Javascript assets, we have:

```html
{% block scripts %}
    {# Inject PHP Defined Configuration #}
    <script>
    {% include "pages/partials/config.js.twig" %}
    </script>

    {# Override this file in a child sprinkle to override site-level scripts. #}
    {% include 'content/scripts_site.html.twig' %}

    {# Override this block in a child layout template or page template to specify or override scripts for groups of similar pages. #}
    {% block scripts_page_group %}{% endblock %}

    {# Override this block in a child layout template or page template to specify or override page-level scripts. #}
    {% block scripts_page %}{% endblock %}
{% endblock %}
```

The main idea is for each page to include no more than three different bundles of each type - a **sitewide bundle**, containing assets that every page on your site uses; a **page group bundle**, to share assets among sets of similar pages; and a **page-specific bundle**, for assets that are specific enough to only be needed on one page.

> [!TIP]
> The `content/scripts_site.html.twig` and `content/stylesheets_site.html.twig` pages contains the `{% block scripts_site %}{% endblock %}` and `{% block stylesheets_site %}{% endblock %}` blocks respectively. A content file is used here, as it's easier for Sprinkles to overwrite this smaller file, than the whole base layout.
