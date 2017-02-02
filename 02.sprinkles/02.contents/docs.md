---
title: Contents
metadata:
    description: Detailed breakdown of a Sprinkle's contents.
taxonomy:
    category: docs
---

Within each sprinkle, you will find any or all of the following directories and files:

```
├── assets
├── config
├── locale
├── migrations
├── routes
├── schema
├── src
├── templates
├── composer.json
├── bundle.config.json
└── bower.json
```

Each of these directories corresponds to specific types of entities that make up your application.  UserFrosting has different rules for how each type of entity can extend the entities of the same type loaded in previous Sprinkles.

### /composer.json

The `composer.json` file is primarily used in UserFrosting to map a Sprinkles classes, but as this is Composer, it can also be used to reference additional PHP libraries. 

The master `composer.json` file in `/app` will automatically merge the `composer.json` file for every Sprinkle when performing:

```bash
$ composer update
```

>>>> The master `composer.json` file will load **all** child `composer.json` files, even in Sprinkles that haven't been loaded in your site's `index.php`.  To change this behavior, you will need to modify the master `composer.json` file.

### /bundle.config.json

The `bundle.config.json` file is used for defining asset bundles, that can be referenced by templates. The advantage of using asset bundles (as compared to referencing the specific files) is that multiple files can be quickly referenced, and when it comes to deployment, the bundles have their individual files merged, reducing the number of individual asset requests, and thus reducing server load.

To compliment the overriding behaviour of the Sprinkle system, you can redefine existing bundles.

As an example, suppose we have this bundle defined in the core:

```json
{
    "bundle": {
        "css/main": {
            "styles" : [
                "vendor/font-awesome/css/font-awesome.css",
                "vendor/bootstrap/dist/css/bootstrap.css",
                "local/core/css/uf-jqueryvalidation.css",
                "local/core/css/uf-alerts.css"
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

The second definition would replace bundle.

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
- replace - Replaces any previous definition.
- merge - Merges with the previous definition.
- ignore - If there is a previous definition, leave it as is.
- error - If there is a previous definition, show an error.

>>>>> These collision rules will only affect bundles earlier in the Sprinkle load order. So for instance, if `error` where used as the collision rule for a bundle, it can still be affected by any bundle definitions loaded after it.

### /bower.json

The `bower.json` file is used for easily retrieving vendor assets from the package management system [Bower.io](https://bower.io/search/), like [Bootstrap](http://getbootstrap.com/). Vendor assets specified in `bower.json` will be downloaded to `/assets/vendor`.

To download vendor assets, from the `/build` directory:

```bash
$ npm run uf-assets-install
```

### /assets

The `assets` directory contains Javascript, CSS, images, and other content that enhances the client-side experience.

Asset files in this directory will override asset files of the same name in previously loaded Sprinkles.  To accomplish this, UserFrosting sets up a [custom stream wrapper](http://php.net/manual/en/intro.stream.php) that automatically resolves to the most recently loaded Sprinkle in which a particular file was defined.

For example, suppose we have:

```
account
└── assets
    └── local
        └── account
            └── images
                    └── barn-owl.jpg
```

as well as:

```
site
└── assets
    └── local
        └── account
            └── images
                └── barn-owl.jpg
```

Assuming we've loaded the `account` and `site` Sprinkles (in that order), we can now use the uri `assets://local/account/images/barn-owl.jpg` in our code, and UserFrosting will correctly resolve it to `/site/assets/local/account/images/barn-owl.jpg`.

>>>>> Notice the directory pattern used to organise the assets. This pattern is used provide more control over asset overriding, such that assets aren't accidentally overridden, where `local` refers to non-vendor assets, and the use of the Sprinkle name to specify where the assets originally came from. While following this pattern is optional, it is recommended.

>>>>> Custom stream uris like `assets://local/account/images/barn-owl.jpg` will be correctly interpreted in your server-side code, but cannot be understood by clients' browsers.  To serve an asset like this to the client, UserFrosting has a special route that maps these uris to public urls.  The Asset Manager can automatically generate the appropriate public urls for use in HTML (e.g. for `<img>`, `<link>`, `<script>`, and other tags).  See [section 5.2](/building-pages/assets) for more information about asset management.

### /config

`config` contains the configuration parameters for your Sprinkle.  A UserFrosting configuration file is nothing more than a PHP script that returns an associative array.  For example:

```php
<?php

    // default.php

    return [
        'timezone' => 'America/New_York',
        'site' => [
            'title'     =>      'Owl Fancy',
            'author'    =>      'David Attenborough'
        ]
    ];
```

In each Sprinkle, you can specify multiple config files for different environment modes.  UserFrosting will start by loading the array of configuration values from the `default.php` config file in the `core` Sprinkle.

Next, it will check the `UF_MODE` environment variable to see if an environment mode has been set (this can be set either directly in your operating system's environment variables, or in the `/app/.env` file.)  If `UF_MODE` has been set, it will look for a configuration file of the same name in the `core` sprinkle and recursively merge that array into the default configuration array.

For example, if `UF_MODE="development"`, then it will look for a `development.php` configuration file.  The array it returns will be recusively merged into the array from `default.php`, replacing any keys that are the same.  So for example, if I have:

```php
<?php

    // development.php

    return [
        'site' => [
            'title'     =>      'Save the Kakapo',
            'twitter'   =>      '@savethekakapo'    
        ]
    ];
```

The resulting configuration array created by UserFrosting will look like:

```php
    [
        'timezone' => 'America/New_York',
        'site' => [
            'title'     =>      'Save the Kakapo',
            'author'    =>      'David Attenborough',
            'twitter'   =>      '@savethekakapo'            
        ]
    ]
```

UserFrosting will repeat these steps for any subsequently loaded Sprinkles, recursively merging in each file.

To summarize, Sprinkle configuration files are loaded using the following algorithm:

1. Load the core `default.php` configuration file;
2. Recursively merge in the core configuration file for the environment mode, if set;
3. Move on to the next Sprinkle;
4. Recursively merge in the `default.php` configuration file from the current Sprinkle;
5. Recursively merge in the configuration file for the environment mode, if set, from the current Sprinkle;
6. Repeat steps 3-5 for all remaining Sprinkles to be loaded.

>>>>>> Use environment variables to easily set the appropriate configuration parameters for different environments.  In addition to setting the `UF_MODE` environment variable to select different configuration files, you can assign sensitive information like database passwords and API keys directly to environment variables, and then reference them in your configuration files using `getenv()`.<br><br>See [the Twelve-Factor App](https://12factor.net/config) for more information on why this is a good idea.

### /locale

The `locale` directory contains [translation files](/building-pages/i18n) for your Sprinkle.  Like configuration files, translation files simply return an associative array.

Just as with configuration files, UserFrosting will recursively merge translation files for the currently selected language(s) from each loaded Sprinkle.  This means that each subsequently loaded Sprinkle can override translations from previous Sprinkles, or define new ones entirely.

See [Section 5.3](/building-pages/i18n) for more information on UserFrosting's internationalization and localization system.

### /migrations

The `migrations` directory contains database migration scripts for your Sprinkle.  See [Chapter 6](/database/extending-the-database) for more information on using migrations.

### /routes

Files in the `routes` directory should contain the Slim [front controller routes](/routes-and-controllers/front-controller) for your Sprinkle.  For example, if your website was `http://owlfancy.com`, then the URL at `http://owlfancy.com/supplies/preening` would be defined in a route file as:

```
$app->get('/supplies/preening', 'UserFrosting\Sprinkle\MySprinkle\Controller\MySprinkleController:pagePreening');
```

As with configuration and translation files, route files can override routes from previous Sprinkles in addition to defining new ones.

Learn more about routes and controllers in [Chapter 4](/routes-and-controllers).

>>> You may have as many route files as you'd like in a Sprinkle.  Within each Sprinkle, route files are loaded in alphabetical order, so in general it is not a good idea to override a route in the same Sprinkle in which it was originally defined.

### /schema

`schema` contains the [validation schema](/routes-and-controllers/validation) for your Sprinkle.  Schema files must be overridden in their entirety; to extend a schema file in a previously loaded Sprinkle, you must redefine the entire schema.

### /src

`src` contains the (preferably) [PSR-4](http://www.php-fig.org/psr/psr-4/) compatible PHP code for your Sprinkle.

As mentioned in the introduction, UserFrosting uses a master `composer.json` file to automatically merge the `composer.json` files in each Sprinkle.  Each Sprinkle, in turn, is responsible for defining its third-party package dependencies, as well as the PSR-4 base namespace for the Sprinkle.

In general, the convention is to map `\UserFrosting\Sprinkle\<Sprinkle name>` as the base namespace for the `\src` directory.  For example, the directory `/app/sprinkles/mysprinkle/src/Controller/OwlController.php` would be mapped to the fully qualified class name `\UserFrosting\Sprinkle\MySprinkle\Controller\OwlController`.

#### Initialization class

At the base level of each Sprinkle, you may optionally define an initialization class.  The name of the class should be the same as the name of the Sprinkle directory, but in [StudlyCaps](https://laravel.com/api/5.2/Illuminate/Support/Str.html#method_studly).

The initialization class must implement the `\UserFrosting\Sprinkle\Core\Initialize\Sprinkle` abstract class, in particular, the `init` method.  UserFrosting's Sprinkle Manager will automatically run the code in `init` when it loads the Sprinkle.   For example, the `Account` Sprinkle's initialization class looks like this:

```
namespace UserFrosting\Sprinkle\Account;

use UserFrosting\Sprinkle\Account\ServicesProvider\AccountServicesProvider;
use UserFrosting\Sprinkle\Core\Initialize\Sprinkle;

class Account extends Sprinkle
{
    /**
     * Register Account services.
     */
    public function init()
    {
        $serviceProvider = new AccountServicesProvider();
        $serviceProvider->register($this->ci);
    }
}
```

In general, a Sprinkle's initialization class should not need to do much more beyond registering your Sprinkle's [service provider class](/services).

#### Extending classes

Extending PHP classes is a little different from extending other types of entities.  You cannot simply replace a class by redefining it in a custom Sprinkle.  In fact, classes with the same name in two different Sprinkles would be treated as two different fully-qualified classes.  For example, if I loaded the Sprinkles `Account` and `Site`, and I had the following structure:

```
sprinkles
├── account
│   └── src
│      └── Model
│           └── User.php
└── site
    └── src
        └── Model
            └── User.php
```

then `User.php` in `site` would *not* override `User.php` in `account`.  Rather, I'd have two different classes: `\UserFrosting\Sprinkle\Account\Model\User` and `\UserFrosting\Sprinkle\Site\Model\User`.

To actually override and replace the functionality of a class, we have two tools available:

##### Class Inheritance

We could, for example, define our `User` class in the `site` Sprinkle to inherit from the `User` class in `account` using the `extends` keyword:

```
<?php

/* /app/sprinkles/site/src/Model/User.php */

namespace \UserFrosting\Sprinkle\Site\Model;

class User extends \UserFrosting\Sprinkle\Account\Model\User
{

    ...

}

```

Now, we can start using `\UserFrosting\Sprinkle\Site\Model\User` to extend the functionality provided by the `User` class in the `Account` sprinkle.

##### Dynamic Class Mapper

Of course, the limitations of object-oriented inheritance becomes clear when you want to change the behavior of the original class in other places where it has been used.  For example, if I extended `Account\Model\User` and redefined the `onLogin` method in my `Site\Model\User` class, this would let me use `Site\Model\User` going forward in any code I write in the `site` Sprinkle.  However, it wouldn't affect references to `User` in the `account` Sprinkle - they would still be referring to the base class.

To allow this sort of "retroactive extendability", UserFrosting introduces another layer of abstraction - the **class mapper**.  The class mapper resolves generic class identifiers to specific class names at runtime.  Rather than hardcoding references to `Account\Model\User`, Sprinkles can generically reference `user` through the class mapper, and it will find the most recently mapped version of that class.

For example, a controller in the account Sprinkle could do something like:

```
$user = $classMapper->staticMethod('user', 'where', 'email', 'admin@example.com')->first();
```

The account Sprinkle itself maps the `user` identifier to `UserFrosting\Sprinkle\Account\Model\User`.  Thus, this call would be equivalent to:

```
$user = \UserFrosting\Sprinkle\Account\Model\User::where('email', 'admin@example.com')->first();
```

**However**, if I later re-map the `user` identifier to `\UserFrosting\Sprinkle\Site\Model\User`, then all calls to `$classMapper->staticMethod('user', ...)` **in any Sprinkle** will dynamically resolve to `\UserFrosting\Sprinkle\Site\Model\User` instead.

Dynamic class mappings are typically defined by extending the `classMapper` service in your Sprinkle's **service provider**:

```
    /* /app/sprinkles/account/src/ServicesProvider/AccountServicesProvider.php */

    $container->extend('classMapper', function ($classMapper, $c) {
        $classMapper->setClassMapping('user', 'UserFrosting\Sprinkle\Account\Model\User');
        $classMapper->setClassMapping('group', 'UserFrosting\Sprinkle\Account\Model\Group');
        $classMapper->setClassMapping('role', 'UserFrosting\Sprinkle\Account\Model\Role');
        $classMapper->setClassMapping('permission', 'UserFrosting\Sprinkle\Account\Model\Permission');
        $classMapper->setClassMapping('activity', 'UserFrosting\Sprinkle\Account\Model\Activity');
        return $classMapper;
    });
```

You can learn more about services in [Chapter 7](/services).

### /templates

To separate content and logic, UserFrosting uses the popular [Twig](http://twig.sensiolabs.org/) templating engine.  Since Twig has its own system for [loading templates](http://twig.sensiolabs.org/doc/api.html#built-in-loaders), UserFrosting builds upon this to allow overriding templates in Sprinkles.

UserFrosting's Sprinkle Manager will automatically add the path to each Sprinkle's `/template` directory in Twig's file loader.  Twig will search for template files starting with the most recently loaded Sprinkle, falling back to previously loaded Sprinkles until it finds a match.

To completely override a template in a Sprinkle, simply redefine it with the same name and relative path in your Sprinkle:

`/app/sprinkles/core/templates/pages/about.html.twig`:

```twig

{% extends "layouts/guest.html.twig" %}

{% set page_active = "about" %}

{# Overrides blocks in head of base template #}
{% block page_title %}About{% endblock %}

{% block page_description %}All about my UserFrosting website.{% endblock %}

{% block body_matter %}
    <!-- Page Heading/Breadcrumbs -->
    <div class="row">
    
    ...
    
{% endblock %}
```

`/app/sprinkles/site/templates/pages/about.html.twig`:

```twig

{% extends "layouts/guest.html.twig" %}

{% set page_active = "about" %}

{# Overrides blocks in head of base template #}
{% block page_title %}About OwlFancy.com{% endblock %}

{% block page_description %}OwlFancy.com - history, facts, and fiction.{% endblock %}

{% block body_matter %}
    Owl Fancy was founded in 1943 in response to an owl shortage both domestically and abroad.  Civilians across the globe were asked to contribute their owls towards the war effort, resulting in large-scale deowlment throughout the countryside.  Exploding vole populations were...
    
{% endblock %}
```

Then, if we had the following code in a controller:

```
    return $this->ci->view->render($response, 'pages/about.html.twig');
```

Twig would resolve to the `pages/about.html.twig` file in the `site` Sprinkle's `template` directory, since `site` is loaded after `core`.

In general, the `templates` directory for a Sprinkle is structured as follows:

```
├── components
├── layouts
├── mail
└── pages
```

`components` contains partial HTML and Javascript templates, such as forms, tables, navigation bars, and other commonly reused components.  `layouts` contains parent templates meant to be extended (using Twig's [extend](http://twig.sensiolabs.org/doc/tags/extends.html) feature).  These layouts can be used within the same Sprinkle in which they were defined, or in another loaded Sprinkle.  `mail` contains email templates - see [Chapter 11.1](/other-services/mail) for more information.

`pages` should contain templates that correspond to specific pages in your application.  For example, the main content template for `http://owlfancy.com/supplies/preening` might be located at `pages/supplies/preening.html.twig`.
