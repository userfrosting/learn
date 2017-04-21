---
title: Creating a New Page
metadata:
    description: A very basic walkthrough for setting up a new page in your application.  The purpose of this recipe is to get you familiar with adding routes, controller classes, and Twig templates to your Sprinkle.
taxonomy:
    category: docs
---

Before we begin this tutorial, it is important that you understand what a web page really is.  Unfortunately, many "classic" approaches to building a website give developers the mistaken impression that a web page "is" either a static `.html` page or a scripted `.php` page.  Neither of these approaches are conceptually correct.

In reality, a web page is simply an HTTP `GET` request to which the server responds with an HTML body.  For a more detailed explanation of this, please see Section 3.1, [The Client-Server Conversation](/background/the-client-server-conversation).

It is true that with more primitive approaches to building a website or web application, there might be a one-to-one correspondence between pages and `.html` or `.php` files.  Indeed, most web servers are configured by default to look for a web page or PHP script with the same name and relative path as the request URL (for example, `http://example.com/admin/user.php` and `/var/www/admin/user.php`).  But, there is no law that says that we _must_ use this approach.

In fact, UserFrosting adds several layers of abstraction between the request URL and the content and functionality of a page.  Most notably, UserFrosting uses the [front controller pattern](/routes-and-controllers) and [templating](/building-pages/templating-with-twig) to isolate the URL of a page from its content and functionality.

In this tutorial, you will learn how to set up the components required to implement a basic web page:

- Defining a route
- Creating a new controller class and method
- Creating a new page template file

## Set up your site Sprinkle

If you haven't already, set up your site Sprinkle, as per the instructions in ["Your First UserFrosting Site"](/sprinkles/first-site).  For the purposes of this tutorial, we will call our Sprinkle `site`.

## Defining a route

The first thing we'll need to do is set up a **route** for our page.  This basically tells UserFrosting how it should respond to a request for a particular URL and HTTP method.

Let's say we want to define a page with the URL `http://example.com/members/`.  We'll need to define a `GET` route for the `/members` URL.

To define routes in our Sprinkle, we'll need to create a `routes/` directory.  Inside this directory, we'll create a PHP file `routes.php` where we can define our route:

```bash
app
└── sprinkles
    └── site
        ├── config/
        ├── routes/
            └── routes.php
        ├── src/
        └── composer.json
```

In `routes.php`, we define our route as follows:

```
<?php

global $app;

$app->get('/members', 'UserFrosting\Sprinkle\Site\Controller\PageController:pageMembers')
    ->add('authGuard');
```

Notice the `->add('authGuard')` part.  This is a piece of middleware which checks to make sure that only authenticated users can access this page.  If you wanted to create a "public" page, you would simply remove this part of the route definition.

That's it!  We've told UserFrosting that whenever someone requests the `/members` URL, that it should invoke the `pageMembers` method of the `PageController` class.  What's that you say?  I don't have a `PageController` class?  Well then, we'll just have to create it now.

## Creating a new controller class and method

The [controller class](/routes-and-controllers/controller-classes) is where the meat of our application logic will reside.  In your Sprinkle's `src/` directory, create a new directory `Controller`, and inside that create `PageController.php`:

```bash
app
└── sprinkles
    └── site
        ├── config/
        ├── routes/
        ├── src/
            └── Controller/
                └── PageController.php
        └── composer.json
```

`PageController.php` should look like this:

```
<?php

namespace UserFrosting\Sprinkle\Site\Controller;

use UserFrosting\Sprinkle\Core\Controller\SimpleController;

class PageController extends SimpleController
{
    public function pageMembers($request, $response, $args)
    {
        return $this->ci->view->render($response, 'pages/members.html.twig');
    }
}
```

Notice how we've defined the [namespace](http://php.net/manual/en/language.namespaces.rationale.php) for this class.  If you correctly [set up your Sprinkle's `composer.json` file](/sprinkles/first-site#composerjson), the namespace of the Sprinkle (`UserFrosting\Sprinkle\Site`) should correspond to the `src/` directory of your Sprinkle.

Any subdirectories in `src/` correspond to sub-namespaces in `UserFrosting\Sprinkle\Site`.  Since our `PageController` class is in the `Controller/` subdirectory, its full namespace would be `UserFrosting\Sprinkle\Site\Controller`.

>>> This convention of mapping directories to namespaces is not our invention!  It comes from the [PSR-4 specifications](http://www.php-fig.org/psr/psr-4/).

Since `SimpleController` is in a different namespace, we need to use the `use` keyword to tell PHP where to find it.  Otherwise, PHP will try to look in the same namespace that we defined with the `namespace` keyword by default.  In this case, it would cause an error, because `SimpleController` is in `UserFrosting\Sprinkle\Core\Controller` while `PageController` is in `UserFrosting\Sprinkle\Site\Controller`.

Notice that `pageMembers` only does one thing - it renders the contents of a template file (`pages/members.html.twig`), and appends it to the response.  We then return this (modified) response to the main application, which sends it off to the client.

Next we'll create the page template itself, which contains the actual HTML content we want to render.

## Creating a new page template file

All templates live in the `templates/` directory of your Sprinkle.  These files end with `.html.twig`, which tells UserFrosting that a file is a Twig template for rendering an HTML document.  Of course, you could use Twig to dynamically render Javascript (`.js.twig`) or other types of content as well.

In `templates/` create a subdirectory `pages/`, and inside that create your template file, `members.html.twig`:

```bash
app
└── sprinkles
    └── site
        ├── config/
        ├── routes/
        ├── src/
        ├── templates/
            └── pages/
                └── members.html.twig
        └── composer.json
```

Your `members.html.twig` template should look something like this:

```twig
{% extends "layouts/default.html.twig" %}

{# Overrides blocks in head of base template #}
{% block page_title %}Members{% endblock %}

{% block page_description %}The members-only section of OwlFancy.com.{% endblock %}

{% block body_matter %}
    Welcome, {{ current_user.first_name }}!
{% endblock %}
```

Notice that we extend the `default.html.twig` layout, which is the same [layout template](/building-pages/templating-with-twig#layouts) used by the "home" and "about" pages.  If we wanted to create a "dashboard" page, we would extend the `layouts/dashboard.html.twig` template instead.

Then, we simply have to fill in some of the [blocks](https://twig.sensiolabs.org/doc/2.x/tags/extends.html) defined in the layout template with our page content.  As a simple example of using Twig to produce dynamic content, we reference the `current_user` global Twig variable to get and display the user's first name.

## Next steps

This recipe only covers the basics of setting up a new page.  From here, you might want to try:

- [Extending the user model](/recipes/extending-the-user-model)
- Setting up an AJAX data source with a [custom Sprunje](/database/data-sprunjing)
- Creating a [form, table, or other client-side component](/client-side-code) in a dashboard page
