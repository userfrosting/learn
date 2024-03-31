---
title: Setting up the basics
metadata:
    description: Setting up the basic sprinkle
taxonomy:
    category: docs
---

## The Base Sprinkle

First thing to do is to create an empty sprinkle for our code to live in from the UserFrosting 5 Skeleton. We'll call this sprinkle `Pastries`. As described in the [Installation Chapter](/installation) chapter, start by creating an empty base using the Skeleton template:

```bash
$ composer create-project userfrosting/userfrosting UserFrosting "^5.1"
```

Make sure the default skeleton app is working. Once the base website is created and working, we can start creating our new page.

## The Route Class

We can create the [route definition](/routes-and-controllers) for the `/pastries` page. We'll *edit* the default `app/src/MyRoutes.php` file. 

**app/src/MyRoutes.php**:
```php
<?php

namespace UserFrosting\App;

use Slim\App;
use Slim\Routing\RouteCollectorProxy; // <-- Add this
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

We now have a `/pastries` route set up, which point to a (future) `PastriesPageAction` controller class. We defined that route inside a route group for later use, if we wish to add additional routes whose URLs also begin with `/pastries`. As you can see this route has the `pastries` name and will invoke the `AuthGuard` middleware, which requires a user to be logged in to see this page.

## The Controller Class

Now that we have a route, we need to create the `PastriesPageAction` controller:

**src/Controller/PastriesPageAction.php**:
```php
<?php

namespace UserFrosting\App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;

class PastriesPageAction
{
    public function __invoke(Response $response, Twig $view): Response
    {
        $pastries = [];

        return $view->render($response, 'pages/pastries.html.twig', [
            'pastries' => $pastries,
        ]);
    }
}
```

For now, the pastries array is empty. In the next page, we'll replace this empty array with a database model. 

## The Template File

Finally, we need to *create* the template file. We use the same file name as the one defined in your controller:

****app/templates/pages/pastries.html.twig**:
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
                    <tr>
                        <th>Name</th>
                        <th>Origin</th>
                        <th>Description</th>
                    </tr>
                </div>
            </div>
        </div>
    </div>
{% endblock %}
```

## Testing the page skeleton

You should now be able to manually go to the `/pastries` page in your browser and see the result:

![Pastries page](/images/pastries/01.png)

You'll notice that at this point, we're not actually displaying any useful content on the page. In the next section, we'll discuss how to display content dynamically retrieved from the database.
