---
title: Your First UserFrosting Site
metadata:
    description: This guide walks you though the process of setting up your application by implementing a new Sprinkle.
taxonomy:
    category: docs
---

This guide assumes that you've already completed the [installation guide](/basics/installation) and successfully managed to get UserFrosting working in your [local development environment](/basics/requirements/develop-locally-serve-globally).  If not, please do that now - feel free to [ask for help](/basics/getting-help) if you're running into trouble!

## Create a Sprinkle

By building your site as a separate Sprinkle, you avoid directly modifying the core components that ship with UserFrosting.  This makes it easier for you and your team to test and debug your project, and makes upgrading as simple as replacing the core sprinkles with `git` or a manual merge.

To create a new Sprinkle, simply create a new subdirectory in `/app/sprinkles/`.  Let's call our sample Sprinkle `site`:

```
app
└── sprinkles
    ├── account
    ├── admin
    ├── core
    ├── root
    └── site
```

To make sure that our new Sprinkle will actually get loaded, we need to modify our `app/sprinkles/sprinkles.json` file:

```json
{
    "base": [
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
            'auth' => true
        ],
        'site' => [
            'author'    =>      'David Attenborough',
            'title'     =>      'Owl Fancy',
            // URLs
            'uri' => [
                'author' => 'https://attenboroughsreef.com'
            ]
        ],   
        'timezone' => 'Europe/London'        
    ];
```

This customizes some basic properties for our application - set your site title, author, author's URL, and timezone.  Reload the page, and you should see the new site title appear in the upper left corner:

![Overriding site settings in config file](/images/site-title.png)

This is because the template for this page is dynamically pulling that particular piece of text from your new configuration file.  Note that your value for `site.title` overrides the value of `site.title` in the core Sprinkle's configuration file.

>>>>>> You can [override configuration values](/sprinkles/contents#config) from any previously loaded Sprinkles, including the default Sprinkles that ship with UserFrosting.  Check `/app/sprinkles/core/config/default.php` and `/app/sprinkles/account/config/default.php` for a complete list.

