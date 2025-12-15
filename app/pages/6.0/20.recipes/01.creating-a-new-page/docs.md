---
title: Creating a new page
description: A very basic walkthrough for setting up a new page in your application. The purpose of this recipe is to get you familiar with adding routes, controller classes, and Twig templates to your Sprinkle.
obsolete: true
---

Before we begin this tutorial, it is important that you understand what a web page really is. Unfortunately, many "classic" approaches to building a website give developers the mistaken impression that a web page "is" either a static `.html` page or a scripted `.php` page. Neither of these approaches are conceptually correct.

In reality, a web page is simply an HTTP `GET` request to which the server responds with an HTML body. For a more detailed explanation of this, please see [The Client-Server Conversation](background/the-client-server-conversation).

It is true that with more primitive approaches to building a website or web application, there might be a one-to-one correspondence between pages and `.html` or `.php` files. Indeed, most web servers are configured by default to look for a web page or PHP script with the same name and relative path as the request URL (for example, `http://example.com/admin/user.php` and `/var/www/admin/user.php`). But, there is no law that says that we _must_ use this approach.

In fact, UserFrosting adds several layers of abstraction between the request URL and the content and functionality of a page. Most notably, UserFrosting uses the [front controller pattern](routes-and-controllers) and [templating](templating-with-twig) to isolate the URL of a page from its content and functionality.

In this tutorial, you will learn how to set up the components required to implement a basic web page:

- Defining a route
- Creating a new controller class and method
- Creating a new page template file

## Set up your site Sprinkle

If you haven't already, set up your site Sprinkle, as per the [installation instructions, using the Skeleton](installation/environment). For the purposes of this tutorial, the Sprinkle Namespace will be `UserFrosting\Sprinkle\Site` and the main Sprinkle Recipe will be found in `UserFrosting\Sprinkle\Site\MyApp`.

## Defining a route

The first thing we'll need to do is set up a **route** for our page. This basically tells UserFrosting how it should respond to a request for a particular URL and HTTP method.

Let's say we want to define a page with the URL `http://example.com/members/`. We'll need to define a `GET` route for the `/members` URL.

To define routes in our Sprinkle, we'll edit the pre-existing route definition class from the base Skeleton. In `app/src/MyRoutes.php`, we define our route as follows:

```php
use UserFrosting\Sprinkle\Site\Controller\PageMembers; // <-- Add this
use UserFrosting\Sprinkle\Account\Authenticate\AuthGuard; // <-- Add this

//...


public function register(App $app): void
{
    $app->get('/', [AppController::class, 'pageIndex'])->setName('index');
    $app->get('/about', [AppController::class, 'pageAbout'])->setName('about');
    $app->get('/legal', [AppController::class, 'pageLegal'])->setName('legal');
    $app->get('/privacy', [AppController::class, 'pagePrivacy'])->setName('privacy');

    // Add this:
    $app->get('/members', PageMembers::class)->add(AuthGuard::class);
}
```

Notice the `->add(AuthGuard::class)` part. This is a piece of middleware which checks to make sure that only authenticated users can access this page. If you wanted to create a "public" page, you would simply remove this part of the route definition.

That's it! We've told UserFrosting that whenever someone requests the `/members` URL, that it should invoke the `PageMembers` class. What's that you say? I don't have a `PageMembers` class? Well then, we'll just have to create it now.

## Creating a new controller class

The [controller class](routes-and-controllers/controller-classes) is where the meat of our application logic will reside. In your Sprinkle's `src/Controller` directory, create `PageMembers.php`, which should look like this:

```php
<?php

namespace UserFrosting\Sprinkle\Site\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\Twig;

class PageMembers
{
    public function __invoke(Response $response, Twig $view): Response
    {
        return $view->render($response, 'pages/members.html.twig');
    }
}
```

Notice that `PageMembers` only does one thing - it renders the contents of a template file (`pages/members.html.twig`), and appends it to the response. We then return this (modified) response to the main application, which sends it off to the client. Both arguments of the `__invoke` method, the Response as well as the Twig Services, will be [injected by the Dependency Injector Container](dependency-injection) to perform these operations. 

Next we'll create the page template itself, which contains the actual HTML content we want to render.

## Creating a new page template file

All templates live in the `app/templates/` directory of your Sprinkle. These files end with `.html.twig`, which tells UserFrosting that a file is a Twig template for rendering an HTML document. Of course, you could use Twig to dynamically render Javascript (`.js.twig`) or other types of content as well.

In `app/templates/pages` create your template file, `members.html.twig`, which should look something like this:

```html
{% extends 'pages/abstract/default.html.twig' %}

{# Overrides blocks in head of base template #}
{% block page_title %}Members{% endblock %}

{% block page_description %}The members-only section of OwlFancy.com.{% endblock %}

{% block body_matter %}
    Welcome, {{ current_user.first_name }}!
{% endblock %}
```

Notice that we extend the `default.html.twig` abstract template, which is the same [abstract template](templating-with-twig/sprinkle-templates#abstract-templates) used by the "home" and "about" pages. If we wanted to create a "dashboard" style page, we would extend the `pages/abstract/dashboard.html.twig` template instead.

Then, we simply have to fill in some of the [blocks](https://twig.symfony.com/doc/3.x/tags/extends.html) defined in the abstract template with our page content. As a simple example of using Twig to produce dynamic content, we reference the `current_user` global Twig variable to get and display the user's first name.

> [!TIP]
> The default UserFrosting theme is based on [AdminLTE](https://adminlte.io). Check it out while building your pages. It comes with pretty cool features and widgets you can use in your own pages

## Next steps

This recipe only covers the basics of setting up a new page. From here, you might want to try:

- [Extending the user model](recipes/extending-the-user-model)
- Setting up an AJAX data source with a [custom Sprunje](database/data-sprunjing)
- Creating a [form, table, or other client-side component](client-side-code) in a dashboard page
