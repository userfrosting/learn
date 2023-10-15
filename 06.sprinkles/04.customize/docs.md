---
title: Customizing Your Sprinkle
metadata:
    description: This guide walks you though the process of setting up your application by implementing a new Sprinkle.
taxonomy:
    category: docs
---

This guide assumes that you've already completed the [installation guide](/installation) and successfully managed to get UserFrosting working in your [local development environment](/background/develop-locally-serve-globally) using the [Skeleton](structure/introduction#the-app-skeleton-your-project-s-template). If not, please do that now - feel free to [ask for help](/troubleshooting/getting-help) if you're running into trouble!

## Custom Namespace and Name

The Skeleton application used as a base installation for every new installation since UserFrosting 5 is a great way to start your UserFrosting project. It comes with some default values which you can change to make your App yours, starting with the PHP Namespace, info in the `composer.json` and the Sprinkle name in the Sprinkle Recipe.

### composer.json

The most important part to edit here is the Namespace. The namespace tells Composer where to look for source code in a PHP application. By default, the base namespace in the Skeleton is `UserFrosting\App`. You can change this to whatever you want. The usual scheme is `{Vendor}\{Project}`, where *Vendor* is your brand and *Project* your project name, but there's no *law* you have to follow here.

[notice]Compared to previous version of UserFrosting, **starting from UserFrosting 5**, the namespace isn't strict anymore. There's no more convention to follow.[/notice]

The namespace is defined in the `composer.json` file in the base directory of our Sprinkle. The default file looks like this : 

Original **composer.json**
```json
{
    "name": "userfrosting/userfrosting",
    "type": "project",
    "description": "A secure, modern user management system for PHP.",
    "keywords": ["php user management", "usercake", "bootstrap"],
    "homepage": "https://github.com/userfrosting/UserFrosting",
    "license" : "MIT",
    "authors" : [
        {
            "name": "Alexander Weissman",
            "homepage": "https://alexanderweissman.com"
        },
        {
            "name": "Louis Charette",
            "homepage": "https://bbqsoftwares.com"
        },
        {
            "name": "Jordan Mele",
            "email": "SiliconSoldier@outlook.com.au",
            "homepage": "https://djmm.me"
        }
    ],
    "require": {
        "php": "^8.0",
        "ext-gd": "*",
        "userfrosting/framework": "^5.0",
        "userfrosting/sprinkle-core": "^5.0",
        "userfrosting/sprinkle-account": "^5.0",
        "userfrosting/sprinkle-admin": "^5.0",
        "userfrosting/theme-adminlte": "^5.0"
    },
    "require-dev": {
        "friendsofphp/php-cs-fixer": "^3.0",
        "phpstan/phpstan": "^1.1",
        "phpstan/phpstan-deprecation-rules": "^1.0",
        "phpstan/phpstan-mockery": "^1.0",
        "phpstan/phpstan-phpunit": "^1.0",
        "phpstan/phpstan-strict-rules": "^1.0",
        "phpunit/phpunit": "^9.5",
        "mockery/mockery": "^1.2",
        "league/factory-muffin": "^3.0",
        "league/factory-muffin-faker": "^2.0"
    },
    "minimum-stability": "dev",
    "prefer-stable": true,
    "autoload": {
        "psr-4": {
            "UserFrosting\\App\\": "app/src"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "UserFrosting\\Tests\\App\\": "app/tests/"
        }
    },
    "scripts": {
        "post-create-project-cmd" : "php bakery bake"
    }
}
```

Let's changes thing up. All the metadata can be customized to reflect your project, and we'll define the new namespace to `Owlfancy\Site` : 

Modified **composer.json**
```json
{
    "name": "owlfancy/site",
    "type": "userfrosting-sprinkle",
    "description": "Site sprinkle for owlfancy.com.",
    "license" : "MIT",
    "authors" : [
        {
            "name": "David Attenborough",
            "homepage": "https://attenboroughsreef.com"
        }
    ],
    "require": {
        "php": "^8.0",
        "ext-gd": "*",
        "userfrosting/framework": "^5.0",
        "userfrosting/sprinkle-core": "^5.0",
        "userfrosting/sprinkle-account": "^5.0",
        "userfrosting/sprinkle-admin": "^5.0",
        "userfrosting/theme-adminlte": "^5.0"
    },
    "require-dev": {
        "friendsofphp/php-cs-fixer": "^3.0",
        "phpstan/phpstan": "^1.1",
        "phpstan/phpstan-deprecation-rules": "^1.0",
        "phpstan/phpstan-mockery": "^1.0",
        "phpstan/phpstan-phpunit": "^1.0",
        "phpstan/phpstan-strict-rules": "^1.0",
        "phpunit/phpunit": "^9.5",
        "mockery/mockery": "^1.2",
        "league/factory-muffin": "^3.0",
        "league/factory-muffin-faker": "^2.0"
    },
    "minimum-stability": "dev",
    "prefer-stable": true,
    "autoload": {
        "psr-4": {
            "Owlfancy\\Site\\": "app/src"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "Owlfancy\\Site\\Tests\\": "app/tests/"
        }
    }
}
```

The important part here is the `autoload.psr-4` key. This tells Composer to map the root `src/` directory of your Sprinkle to the root **namespace** `Owlfancy\Site\`.

[notice]If you're not familiar with PSR-4, it is a standard that says we should map a base directory to a base namespace, and then the relative namespaces of classes in that directory should correspond to the relative paths of their class files. For example, the directory `/app/src/Controller/OwlController.php` would be mapped to the fully qualified class name `\Owlfancy\Site\Controller\OwlController`.[/notice]

The next step is to run Composer from your project directory, so that it can detect the changes in `composer.json` and update the appropriate mappings. You only need to run this once, when you make any changes to the `composer.json` file  - any new classes that you add to `src/` will be automatically picked up in the future.

```bash
$ composer update
```

[notice=note]If after running these steps, UserFrosting fails to find new classes that you add to `src/`, make sure that that the user running Composer had read permissions for your Sprinkle. You can check that the path to your Sprinkle's `src/` directory was actually added in `app/vendor/composer/autoload_psr4.php` You can also try running Composer with the `-vvv` flag for more detailed reporting.[/notice]

### The recipe

The next step is to update every reference of `UserFrosting\App` to `Owlfancy\Site`, in each PHP files inside `app/src/`. But there's one file that might require a bit more attention. The recipe, located in `app/src/MyApp.php` can be renamed to reflect your project. For example, we can rename it to `app/src/Owlfancy.php`. The class name should also be renamed to `Owlfancy`. We'll also edit the `getName()` method, to reflect our project name : 

```php
<?php

namespace Owlfancy\Site;

use Owlfancy\Site\Bakery\HelloCommand;
use UserFrosting\Sprinkle\Account\Account;
use UserFrosting\Sprinkle\Admin\Admin;
use UserFrosting\Sprinkle\BakeryRecipe;
use UserFrosting\Sprinkle\Core\Core;
use UserFrosting\Sprinkle\SprinkleRecipe;
use UserFrosting\Theme\AdminLTE\AdminLTE;

class Owlfancy implements
    SprinkleRecipe,
    BakeryRecipe
{
    public function getName(): string
    {
        return 'Owlfancy';
    }

    // ...
}
```

### The entry files

There's still one last thing to edit. Since we change the recipe from `UserFrosting\App\MyApp` to `Owlfancy\Site\Owlfancy` in the entry files, which are `public/index.php` and `bakery`:

**/public/index.php**
```php
<?php

// First off, we'll grab the Composer dependencies
require_once __DIR__ . '/../vendor/autoload.php';

// Workaround to get php built-in server to access legacy assets
// @see : https://github.com/slimphp/Slim/issues/359#issuecomment-363076423
if (PHP_SAPI == 'cli-server') {
    $_SERVER['SCRIPT_NAME'] = '/index.php';
}

use Owlfancy\Site\Owlfancy; // <-- Here
use UserFrosting\UserFrosting;

$uf = new UserFrosting(Owlfancy::class); // <-- Here
$uf->run();
```

**/bakery**
```php
#!/usr/bin/env php
<?php

/**
 * Require composer autoload file. Not having this file means Composer might not be installed / run
 */
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    die("ERROR :: File `".__DIR__."/vendor/autoload.php` not found. This indicate that composer has not yet been run on this install. Install composer and run `composer install` from the project root directory. Check the documentation for more details.\n");
} else {
    require_once __DIR__ . '/vendor/autoload.php';
}

use Owlfancy\Site\Owlfancy; // <-- Here
use UserFrosting\Bakery\Bakery;

/**
 * Get and run CLI App
 */
$bakery = new Bakery(Owlfancy::class); // <-- Here
$bakery->run();
```

## Configuration settings

Next, let's try setting some basic configuration settings. To do this, simply create a new subdirectory in your sprinkle, `config`, and inside that create a file `default.php`:

```txt
app
└── config
    └── default.php
```

In `default.php`, add the following:

```php
<?php

    /**
     * Sample site configuration file for UserFrosting. You should definitely set these values!
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

This customizes some basic properties for our application - set your site title, author, author's URL, and timezone. Reload the page, and you should see the new site title appear in the upper left corner:

![Overriding site settings in config file](/images/site-title.png)

This is because the template for this page is dynamically pulling that particular piece of text from your new configuration file. Note that your value for `site.title` overrides the value of `site.title` in the core Sprinkle's configuration file.

[notice=tip]You can [override configuration values](/configuration/config-files) from any previously loaded Sprinkles, including the default Sprinkles that ship with UserFrosting. Check the Core Sprinkle and the Account Sprinkle `/app/config/default.php` for a complete list.[/notice]

## Removing default Sprinkles

A default install, from the Skeleton, enables every [default Sprinkles](/structure/sprinkles#bundled-sprinkles). But it's not said your app requires every features provided by theses default sprinkles. For example, you might not need the Admin and Account Sprinkle if you don't need any of the user management feature. 

In this case, two files need to be edited : `composer.json` and the Sprinkle Recipe.

1. In **/composer.json**, remove those two lines :
    ```json
    "userfrosting/sprinkle-account": "^5.0",
    "userfrosting/sprinkle-admin": "^5.0",
    ```

2. Since changes were made to *composer.json*, composer need to be updated (`composer update`).

3. In the Sprinkle Recipe, both `Account::class` and `Admin:class` can be removed from the `getSprinkles()` method:
    ```php 
    public function getSprinkles(): array
    {
        return [
            Core::class,
            //Account::class,
            //Admin::class,
            AdminLTE::class,
        ];
    }
    ```

[notice]Technically, the **Core** sprinkle IS optional. However, remember it provides pretty much every base feature of UserFrosting, including database support. Without any sprinkles, i.e. only the UserFrosting Framework, your app would be a very basic Slim Application with routes support.[/notice]
