---
title: Asset Bundles
metadata:
    description: Asset bundles allow you to group one or more asset references for reuse throughout your application.
taxonomy:
    category: docs
---

Most CSS and Javascript resources should be integrated into your pages through **asset bundles**.  Asset bundles are groups of assets for which UserFrosting can automatically render `<link>` or `<script>` tags in your pages, using the `assets` Twig helper.  Each Sprinkle can define asset bundles in its `asset-bundles.json` file.

## Defining asset bundles

UserFrosting ships with a number of predefined bundles.  If you look the `core` Sprinkle's `asset-bundles.json` file, you will see, for example:

```
{
  "bundle": {
    "js/main": {
        "scripts": [
            "vendor/bootstrap/dist/js/bootstrap.js",
            "vendor/handlebars/handlebars.js",
            "vendor/jquery-validation/dist/jquery.validate.js",
            "vendor/jquery-validation/dist/additional-methods.js",
            "vendor/jquery-slimscroll/jquery.slimscroll.js",
            "vendor/icheck/icheck.min.js",
            "vendor/fastclick/lib/fastclick.js",
            "vendor/select2/dist/js/select2.full.js",
            "vendor/clipboard/dist/clipboard.js",
            "userfrosting/js/attrchange.js",
            "userfrosting/js/AdminLTE.js",
            "userfrosting/js/AdminLTE-custom.js",
            "userfrosting/js/fortress-jqueryvalidation-methods.js",
            "userfrosting/js/uf-jqueryvalidation-config.js",
            "userfrosting/js/uf-alerts.js",
            "userfrosting/js/uf-form.js",
            "userfrosting/js/uf-modal.js",
            "userfrosting/js/uf-copy.js",
            "userfrosting/js/uf-init.js"
        ],
        "options": {
            "result": {
                "type": {
                  "scripts": "plain"
                }
            }
        }
    },
    ...
```

Under `bundle` you will notice the name of the bundle (`js/main`), and then a list of paths to bundle assets.  Newbe warning: Naming your bundle "js/main" will replace the bundle from core with yours and in the process will probably break core user management functionality.  In general, be careful to provide your bundle with a unique "js/name" name otherwise your bundle overwrites an existing sprinkle bundle with potentially difficult to diagnose runtime errors.


>>> Each path in a bundle is treated as if it were prefixed with the `assets://` stream wrapper.  Thus, the same rules apply for overriding a Sprinkle's assets when referenced in bundles, as when referencing unbundled assets.  When the appropriate reference tags for the assets are rendered, UserFrosting will look in the most recently loaded Sprinkle's `/assets` directory and search back through the stack until it finds a match.

### Javascript bundles

By convention, Javascript bundles should be named with the `js/` prefix.  The assets for a Javascript bundle must be defined under the `scripts` key in your bundle.

### CSS bundles
 
By convention, CSS bundles should be named with the `css/` prefix.  The assets for a CSS bundle must be defined under the `styles` key in your bundle.

>>>> Generally speaking, it is a good idea to define your Javascript and CSS resources in separate bundles.  The `options` key in both types of bundles is required, and it tells [gulp-bundle-assets](https://github.com/dowjones/gulp-bundle-assets) how to construct the schema file for linking to [compiled assets](/asset-management/compiled-assets).

## Rendering bundles

To render a bundle on a page, simply use the `assets.js()` and `assets.css()` Twig helpers:

```
{{ assets.js('js/main') | raw }}
```

UserFrosting will automatically generate the `<script>` tags for Javascript bundles, or `<link>` tags for CSS bundles, when it renders the template:

```
<script src="http://localhost/myUserFrostingProject/public/assets-raw/core/assets/vendor/bootstrap-3.3.6/js/bootstrap.js" ></script>
<script src="http://localhost/myUserFrostingProject/public/assets-raw/core/assets/vendor/handlebars-1.2.0/handlebars.js" ></script>
<script src="http://localhost/myUserFrostingProject/public/assets-raw/core/assets/vendor/jqueryValidation-1.14.0/jquery.validate.js" ></script>
<script src="http://localhost/myUserFrostingProject/public/assets-raw/core/assets/vendor/jqueryValidation-1.14.0/additional-methods.js" ></script>
<script src="http://localhost/myUserFrostingProject/public/assets-raw/core/assets/userfrosting/js/fortress-jqueryvalidation-methods.js" ></script>
<script src="http://localhost/myUserFrostingProject/public/assets-raw/core/assets/userfrosting/js/uf-jqueryvalidation-config.js" ></script>
<script src="http://localhost/myUserFrostingProject/public/assets-raw/core/assets/userfrosting/js/uf-alerts.js" ></script>
<script src="http://localhost/myUserFrostingProject/public/assets-raw/core/assets/userfrosting/js/uf-form.js" ></script>
<script src="http://localhost/myUserFrostingProject/public/assets-raw/core/assets/userfrosting/js/uf-modal.js" ></script>
```

## Extending and overriding bundles

To complement the overriding behaviour of the Sprinkle system, you can redefine bundles that were defined in previously loaded Sprinkles, in subsequent Sprinkles.

As an example, suppose we have this bundle defined in the core:

```json
{
    "bundle": {
        "css/main": {
            "styles" : [
                "vendor/font-awesome/css/font-awesome.css",
                "vendor/bootstrap/dist/css/bootstrap.css",
                "userfrosting/css/uf-jqueryvalidation.css",
                "userfrosting/css/uf-alerts.css"
            ],
            "options": {
                "result": {
                    "type": {
                        "styles": "plain"
                    }
                }
            }
        }
    }
}
```

And then in a Sprinkle later in the load order have:

```json
{
    "bundle": {
        "css/main": {
            "styles" : [
                "vendor/new-cool-styles/new-cool-styles.css"
            ],
            "options": {
                "result": {
                    "type": {
                        "styles": "plain"
                    }
                }
            }
        }
    }
}
```

The second definition would completely replace the `css/main` bundle.

But suppose you only wanted to add `new-cool-styles.css` to the bundle? You could redefine the bundle including earlier assets, or alternatively specify a collision rule.

Continuing on from the previous example, suppose the second definition was instead the following:

```json
{
    "bundle": {
        "css/main": {
            "styles" : [
                "vendor/new-cool-styles/new-cool-styles.css"
            ],
            "options": {
                "result": {
                    "type": {
                        "styles": "plain"
                    }
                },
                "sprinkle": {
                    "onCollision": "merge"
                }
            }
        }
    }
}
```

The second definition would merge with the first, adding `vendor/new-cool-styles/new-cool-styles.css` to the list of styles.

The complete list collision rules that exist is:
- `replace` - Replaces any previous definition.
- `merge` - Merges with the previous definition.
- `ignore` - If there is a previous definition, leave it as is.
- `error` - If there is a previous definition, show an error.

>>>>> These collision rules will only affect bundles earlier in the Sprinkle load order. So for instance, if `error` where used as the collision rule for a bundle, it can still be affected by any bundle definitions loaded after it.

## Template blocks for bundles

You can use the `assets.css()` and `assets.js()` helpers anywhere in a Twig template, of course, but best practice dictates that CSS links should go in the `<head>` element of your page, and Javascript tags should go just at the end of your `<body>` element.

To facilitate placement of CSS and Javascript tags, the base abstract template `pages/abstract/base.html.twig` defines a number of template blocks.  For CSS, these blocks are:

```
{% block stylesheets %}
    {# Override this block in a child abstract template or page template to override site-level stylesheets. #}
    {% block stylesheets_site %}
        <!-- Include main CSS asset bundle -->
        {{ assets.css() | raw }}
    {% endblock %}

    {# Override this block in a child abstract template or page template to specify or override stylesheets for groups of similar pages. #}
    {% block stylesheets_page_group %}
    {% endblock %}

    {# Override this block in a child abstract template or page template to specify or override page-level stylesheets. #}
    {% block stylesheets_page %}
    {% endblock %}
{% endblock %}
```

Similarly, for Javascript assets, we have:

```
{% block scripts %}
    {# Override this block in a child abstract template or page template to override site-level scripts. #}
    {% block scripts_site %}
        <!-- Load jQuery -->
        <script src="//code.jquery.com/jquery-2.2.4.min.js" ></script>
        <!-- Fallback if CDN is unavailable -->
        <script>window.jQuery || document.write('<script src="{{ assets.url('assets://jquery-2.2.4/jquery.min.js', true) }}"><\/script>')</script>

        {{ assets.js() | raw }}
    {% endblock %}

    {# Override this block in a child abstract template or page template to specify or override scripts for groups of similar pages. #}
    {% block scripts_page_group %}
    {% endblock %}

    {# Override this block in a child abstract template or page template to specify or override page-level scripts. #}
    {% block scripts_page %}
    {% endblock %}
{% endblock %}
```

The main idea is for each page to include no more than three different bundles of each type - a **sitewide bundle**, containing assets that every page on your site uses; a **page group bundle**, to share assets among sets of similar pages; and a **page-specific bundle**, for assets that are specific enough to only be needed on one page.

>>>>>> You may want to create a child abstract that extends `pages/abstract/base.html.twig` for pages that share a common asset bundle.  In your child template, you can inject page group asset bundles by defining the `stylesheets_page_group` and `scripts_page_group` bundles.
