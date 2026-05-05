---
title: Your First UserFrosting Site
metadata:
    description: This guide walks you though the process of setting up your application by implementing a new Sprinkle.
taxonomy:
    category: docs
---

This guide assumes that you've already completed the [installation guide](/installation) and successfully managed to get UserFrosting working in your [local development environment](/background/develop-locally-serve-globally).  If not, please do that now - feel free to [ask for help](/troubleshooting/getting-help) if you're running into trouble!

## Create a Sprinkle

By building your site as a separate Sprinkle, you avoid directly modifying the core components that ship with UserFrosting.  This makes it easier for you and your team to test and debug your project, and makes upgrading as simple as replacing the core sprinkles with `git` or a manual merge.

To create a new Sprinkle, simply create a new subdirectory in `/app/sprinkles/`.  Let's call our sample Sprinkle `site`:

```
app
└── sprinkles
    ├── account
    ├── admin
    ├── core
    └── site
```

To make sure that our new Sprinkle will actually get loaded, we need to modify our `app/sprinkles.json` file:

```json
{
    "base": [
        "core",
        "account",
        "admin",
        "site"
    ]
}
```

### Configuration settings

Next, let's try setting some basic configuration settings.  To do this, simply create a new subdirectory in your sprinkle, `config`, and inside that create a file `default.php`:

```
app
└── sprinkles
    └── site
        └── config
            └── default.php
```

In `default.php`, add the following:

```
<?php

    /**
     * Sample site configuration file for UserFrosting.  You should definitely set these values!
     *
     */
    return [
        'address_book' => [
            'admin' => [
                'name'  => 'Squawkbot'
            ]
        ],
        'debug' => [
            'smtp' => true
        ],
        'site' => [
            'author'    =>      'David Attenborough',
            'title'     =>      'Owl Fancy',
            // URLs
            'uri' => [
                'author' => 'https://attenboroughsreef.com'
            ]
        ],
        'php' => [
            'timezone' => 'Europe/London'
        ]
    ];
```

This customizes some basic properties for our application - set your site title, author, author's URL, and timezone.  Reload the page, and you should see the new site title appear in the upper left corner:

![Overriding site settings in config file](/images/site-title.png)

This is because the template for this page is dynamically pulling that particular piece of text from your new configuration file.  Note that your value for `site.title` overrides the value of `site.title` in the core Sprinkle's configuration file.

>>>>>> You can [override configuration values](/configuration/config-files) from any previously loaded Sprinkles, including the default Sprinkles that ship with UserFrosting.  Check `/app/sprinkles/core/config/default.php` and `/app/sprinkles/account/config/default.php` for a complete list.

### composer.json

Chances are, you'll be adding some classes to your Sprinkle - at the very least, you will probably have some new models and controllers. You will want these to be autoloaded by Composer.  For this to happen, we need to create a `composer.json` file in the base directory of our Sprinkle.  This file will tell Composer where to look for source code in your Sprinkle:

**composer.json**

```json
{
    "name": "owlfancy/site",
    "type": "userfrosting-sprinkle",
    "description": "Site sprinkle for owlfancy.com.",
    "license" : "MIT",
    "authors" : [
        {
            "name": "Alexander Weissman",
            "homepage": "https://alexanderweissman.com"
        }
    ],
    "autoload": {
        "psr-4": {
            "UserFrosting\\Sprinkle\\Site\\": "src/"
        }
    }
}
```

The important part here is the `autoload.psr-4` key.  This tells Composer to map the root `src/` directory of your Sprinkle to the root **namespace** `UserFrosting\Sprinkle\Site\`.

If you're not familiar with PSR-4, it is a standard that says we should map a base directory to a base namespace, and then the relative namespaces of classes in that directory should correspond to the relative paths of their class files.  For example, the directory `/app/sprinkles/mysprinkle/src/Controller/OwlController.php` would be mapped to the fully qualified class name `\UserFrosting\Sprinkle\MySprinkle\Controller\OwlController`.

>>> As mentioned in [Sprinkle Contents](/sprinkles/contents), UserFrosting uses a master `composer.json` file to automatically merge the `composer.json` files in each Sprinkle.  If you define additional third-party package dependencies in your Sprinkle's `composer.json`, these will be installed to the shared `app/vendor` directory when you run `composer update`.

Go ahead and actually create a `src/` directory in your Sprinkle now.  At this point, your Sprinkle should look like:

```bash
site/
├── config/
    └── default.php
├── src/
└── composer.json
```

The last step is to run Composer from your **root project directory**, so that it can detect the new `composer.json` file and create the appropriate mappings.  You only need to run this once when you create a new Sprinkle - any new classes that you add to `src/` will be automatically picked up in the future.

```bash
$ composer update
```

>>>>> If after running these steps, UserFrosting fails to find new classes that you add to `src/`, make sure that that the user running Composer had read permissions for your Sprinkle.  You can check that the path to your Sprinkle's `src/` directory was actually added in `app/vendor/composer/autoload_psr4.php`  You can also try running Composer with the `-vvv` flag for more detailed reporting.
