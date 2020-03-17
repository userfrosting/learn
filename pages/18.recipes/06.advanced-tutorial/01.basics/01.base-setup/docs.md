---
title: Setting up the basics
metadata:
    description: Setting up the basic sprinkle
taxonomy:
    category: docs
---

>>>>> You can find the complete source code from this tutorial on [GitHub](https://github.com/userfrosting/pastries).

## The sprinkle

First thing to do is to create an empty sprinkle for our code to live in. We'll call this sprinkle `Pastries`. As described in the [Sprinkles](/sprinkles/first-site) chapter, start by creating an empty `pastries/` directory under `app/sprinkles`. We now have to create the `composer.json` file for our sprinkle:

`app/sprinkles/pastries/composer.json`
```json
{
    "name": "owlfancy/pastries",
    "type": "userfrosting-sprinkle",
    "description": "Pastries list for UserFrosting.",
    "autoload": {
        "psr-4": {
            "UserFrosting\\Sprinkle\\Pastries\\": "src/"
        }
    }
}
```

Next we need to add our `Pastries` sprinkle to the `sprinkles.json` list and update **Composer** so our new [PSR4 mapping](http://www.php-fig.org/psr/psr-4/#3-examples) is picked up. From the command line, run `composer update` in the root directory of your UserFrosting project.

## The route

We now create the [route](/routes-and-controllers) for the "pastries" page. Create the empty `routes/` directory inside your sprinkle directory structure and create the `pastries.php` file:

`app/sprinkles/pastries/routes/pastries.php`
```php
<?php

/**
 * Routes for pastry-related pages.
 */
$app->group('/pastries', function () {
    $this->get('', 'UserFrosting\Sprinkle\Pastries\Controller\PastriesController:pageList')
         ->setName('pastries');
})->add('authGuard');
```

We now have a `/pastries` route set up. We also define a route group for later use, if we wish to add additional routes whose URLs also begin with `/pastries/`. As you can see this route has the `pastries` name and will invoke the `authGuard` middleware, which requires a user to be logged in to see this page.

## The controller class

Now that we have a route, we need to create the `PastriesController` controller with the `pageList` method:

`app/sprinkles/pastries/src/Controller/PastriesController.php`
```php
<?php

namespace UserFrosting\Sprinkle\Pastries\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\NotFoundException;
use UserFrosting\Sprinkle\Core\Controller\SimpleController;
use UserFrosting\Support\Exception\ForbiddenException;

class PastriesController extends SimpleController
{
    public function pageList(Request $request, Response $response, $args)
    {
        return $this->ci->view->render($response, 'pages/pastries.html.twig', [

        ]);
    }
}
```

>>>>>> Later on, we can add methods for other pastry-related pages to this same class as a way to logically organize our code.

## The template file

Finally, we need to create the template file. We use the same file as the one defined in your controller:

`app/sprinkles/pastries/templates/pages/pastries.html.twig`
```twig
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
