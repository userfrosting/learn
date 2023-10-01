---
title: Custom Namespace
metadata:
    description: Setting up the basic sprinkle
taxonomy:
    category: docs
---

## The Base Sprinkle

First thing to do is to create an empty sprinkle for our code to live in from the UserFrosting 5 Skeleton. We'll call this sprinkle `Pastries`. As described in the [Installation Chapter](/installation) chapter, start by creating an empty base using the Skeleton template:

```bash
$ composer create-project userfrosting/userfrosting UserFrosting "^5.0.0"
```

Once the base website is created and working, the first step is to edit the base **composer.json** schema. The main part to edit here are the `autoload` and `autoload-dev` properties. This will change the PSR-4 namespace from the default namespace to a custom once. We'll map the `UserFrosting\Sprinkle\Pastries` namespace to the `app/src/` directory for this Sprinkle.

Meta properties (name, description, homepage, authors, etc.) can be edited to fit your project. The default `require` and `required-dev` can be kept as-is for now.

The complete file should look similar to this : 

<!-- TODO : We might have a better way to use the default one, and move this to another page at the end? -->
**composer.json**:
```json
{
    "name": "userfrosting/pastries",
    "type": "userfrosting-sprinkle",
    "description": "Pastries list for UserFrosting.",
    "keywords": ["UserFrosting", "Sprinkle", "Pastries"],
    "homepage": "https://github.com/userfrosting/pastries",
    "license" : "MIT",
    "authors" : [
        {
            "name": "Your Name Here",
            "homepage": "https://yourUrlHere.com"
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
        "phpstan/phpstan-mockery": "^1.0",
        "phpstan/phpstan-phpunit": "^1.0",
        "phpunit/phpunit": "^9.5",
        "mockery/mockery": "^1.2",
        "league/factory-muffin": "^3.0",
        "league/factory-muffin-faker": "^2.0"
    },
    "minimum-stability": "dev",
    "prefer-stable": true,
    "autoload": {
        "psr-4": {
            "UserFrosting\\Sprinkle\\Pastries\\": "app/src"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "UserFrosting\\Tests\\Sprinkle\\Pastries\\": "app/tests/"
        }
    }
}
```

Now we need to update **Composer** so our new [PSR4 mapping](http://www.php-fig.org/psr/psr-4/#3-examples) is picked up. From the command line, run `composer update` in the **root directory** of your UserFrosting project.

[notice=tip]Don't forget to always run any composer command from the project root directory (`/`).[/notice]

<!-- ## Cleanup

The Skeleton comes with some default pages and code we won't need or use. Theses can be savefly deleted : -->

## The Sprinkle Recipe


<!-- ### Update index -->


## The Route Class

We now create the [route class](/routes-and-controllers) for the "pastries" page. We'll edit the default `app/src/MyRoutes.php` file. First, we'll need 

**app/src/MyRoutes.php**:
```php
<?php

namespace UserFrosting\App;

use Slim\App;
use UserFrosting\App\Controller\AppController;
use UserFrosting\Routes\RouteDefinitionInterface;
use UserFrosting\Sprinkle\Account\Authenticate\AuthGuard; // <-- Add this
use UserFrosting\App\Controller\PastriesPageAction; // <-- Add this

class MyRoutes implements RouteDefinitionInterface
{
    public function register(App $app): void
    {
        $app->get('/', [AppController::class, 'pageIndex'])->setName('index');
        $app->get('/about', [AppController::class, 'pageAbout'])->setName('about');
        $app->get('/legal', [AppController::class, 'pageLegal'])->setName('legal');
        $app->get('/privacy', [AppController::class, 'pagePrivacy'])->setName('privacy');

        // Add this -->
        $app->group('/pastries', function (RouteCollectorProxy $group) {
            $group->get('', PastriesPageAction::class)->setName('pastries');
        })->add(AuthGuard::class);
        // <-- End Add
    }
}
```

We now have a `/pastries` route set up. We also define a route group for later use, if we wish to add additional routes whose URLs also begin with `/pastries`. As you can see this route has the `pastries` name and will invoke the `AuthGuard` middleware, which requires a user to be logged in to see this page.

## The Controller Class

Now that we have a route, we need to create the `PastriesPageAction` controller:

**src/Controller/PastriesPageAction.php**:
```php
<?php

namespace UserFrosting\App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\NotFoundException;
use UserFrosting\Sprinkle\Core\Controller\SimpleController;
use UserFrosting\Support\Exception\ForbiddenException;

class PastriesPageAction
{
    public function pageList(Request $request, Response $response, $args)
    {
        return $this->ci->view->render($response, 'pages/pastries.html.twig', [

        ]);
    }
}
```

[notice=tip]Later on, we can add methods for other pastry-related pages to this same class as a way to logically organize our code.[/notice]

## The template file

Finally, we need to create the template file. We use the same file as the one defined in your controller:

****app/sprinkles/pastries/templates/pages/pastries.html.twig**:
```html
{% extends 'pages/abstract/dashboard.html.twig' %}

{# Overrides blocks in head of base template #}
{% block page_title %}Pastries{% endblock %}
{% block page_description %}This page provides a yummy list of pastries{% endblock %}

{% block body_matter %}
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title"><i class="fa fa-cutlery fa-fw"></i> List of Pastries</h3>
                </div>
                <div class="box-body">

                </div>
            </div>
        </div>
    </div>
{% endblock %}
```

## Testing the page skeleton

You should now be able to manually go to the `/pastries` page in your browser and see the result:

![Pastries page](/images/pastries/01.png)

You'll notice that at this point, we're not actually displaying any useful content on the page.  In the next section, we'll discuss how to display content dynamically retrieved from the database.
