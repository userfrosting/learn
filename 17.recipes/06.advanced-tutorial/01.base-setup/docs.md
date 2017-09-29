---
title: Setting up the basics
metadata:
    description: Setting up the basic sprinkle
taxonomy:
    category: docs
---

## The sprinkle

First thing to do is to create an empty sprinkle for our code to live in. We'll call this sprinkle `Pastries`. As describes in the [Sprinkles](http://learn.local/sprinkles/first-site) chapter, starts by creating an empty `pastries/` directory under `app/sprinkles`. We now have to create the `composer.json` file for our sprinkle:

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

Next we need to add our `Pastries` sprinkle to the `sprinkles.json` list and update **Composer** so our new PSR4 definition is picked up. From the command line, run `composer update` at the root of your Userfrosting project.

## The route

We now create the [route](/routes-and-controllers) for the pastries page. Create the empty `routes/` directory inside your sprinkle directory structure and create the `pastries.php` file:

`app/sprinkles/pastries/routes/pastries.php`
```php
<?php

/**
 * Routes for pastries related pages.
 */
$app->group('/pastries', function () {
    $this->get('', 'UserFrosting\Sprinkle\Pastries\Controller\PastriesController:displayPage')
         ->setName('pastries');
})->add('authGuard');
```

We now have a `/pastries` route setup. We also define a group for later use. As you can see, this route have the `pastries` name and will invoke the `authGuard` middleware requiring a user to be logged in to see this page. 

## The controller

Now that we have a route, we need to create the `PastriesController` controller with the `displayPage` method:

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
    public function displayPage(Request $request, Response $response, $args)
    {
        return $this->ci->view->render($response, 'pages/pastries.html.twig', [

        ]);
    }
}
``` 

## The template file

Finally, we need to create the template file. We use the same file as the one defined in your controller:

`app/sprinkles/pastries/templates/pages/pastries.html.twig`
```html
{% extends "pages/abstract/dashboard.html.twig" %}

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

## Testing your page

You should now be able to manually go to the `/pastries` page in your browser and see the result:

![Pastries page](/images/pastries/01.png)