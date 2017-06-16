---
title: Introduction
metadata:
    description: A Sprinkle can contain assets, configuration files, translations, routes, PHP classes, and Twig templates.
taxonomy:
    category: docs
---

In previous versions of UserFrosting, you had to directly modify the files that come with the default installation in order to add your own functionality.  For example, if you wanted to add a field to the user registration page, you had to actually modify `register.twig`.  Or, if you wanted to add a new relation on the `User` class, you had to modify the actual `User.php` class that comes with UserFrosting.

Starting in version 4, this is no longer the case!  UserFrosting 4 now introduces the **Sprinkle system** as a way to completely isolate the code and content that you and your team produce from the core UserFrosting installation, as well as third-party code.

## What is a "Sprinkle"?

A Sprinkle is a functionally cohesive unit of code and content that fits neatly into its own separate directory.  A Sprinkle could implement a core component, a third-party plugin or theme, or even an entire website!  In fact, the first step in building your application with UserFrosting is to **create a new sprinkle.**

Each Sprinkle can contain any or all of the following entities:

- assets (JS, CSS, images, etc)
- configuration files
- translations (i18n)
- route endpoints (URL definitions)
- validation schema
- PHP code (typically classes)
- templates (Twig files)
- "extra" content that does not fit into any of these categories

Sprinkles are loaded in a specific order, and entities of each type in one sprinkle can extend entities of the same type in other sprinkles.  We'll explain exactly what we mean by "extend" in a minute.

Each Sprinkle is located in its own subdirectory under the main `app/sprinkles/` directory.  To create a new Sprinkle, simply create a new subdirectory in `app/sprinkles/`.

## Loading Sprinkles

Sprinkles are automatically loaded via the `sprinkles.json` file in your `app/` directory.  If you used Bakery to install UserFrosting, this file will have been automatically created for you:

```json
{
    "base": [
        "core",
        "account",
        "admin"
    ]
}
```

UserFrosting will load the Sprinkles specified under the `base` key during the application lifecycle.  To have UserFrosting load another Sprinkle, simply add it to this array.  For example, if you have a `site` Sprinkle:

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

By default, the UserFrosting repository is set to ignore the `sprinkles.json` file.  You may wish to commit this to your own personal repository, though.  In this case, simply remove it from your `.gitignore` file.

>>>> The order in which we load our Sprinkles is important.  Files in one Sprinkle may override files with the same name and path in previously loaded Sprinkles.  For example, if we created `site/templates/pages/about.html.twig`, this would override `core/templates/pages/about.html.twig` because we load the `site` Sprinkle *after* the `core` Sprinkle.

### Default Sprinkles

A basic UserFrosting installation comes with three sprinkles, each of which can be found in its own subdirectory in `/app/sprinkles`:

```
app
├──sprinkles
   ├── account
   ├── admin
   └── core
```

When you begin to implement your own project, you will do so in a new Sprinkle, separate from these three.

#### Core

`core` contains most of the "heavy lifting" PHP code, and provides a basic, public website.  It loads most of UserFrosting's PHP and asset dependencies, and contains classes for features like [mail](/mail), [request throttling](/routes-and-controllers/client-input/throttle), [error handling](/advanced/error-handling), and much more!

#### Account

The `account` sprinkle handles user modeling and authentication, user groups, roles, and access control, and contains the routes, templates, and controllers needed to implement pages for registration, password reset, login, and more.

#### Admin

The `admin` sprinkle contains the routes, templates, and controllers to implement the administrative user management interface, as well as the group, role, and permission management interfaces.

Now that we're familiar with the basic concept, let's dig into the [contents of a Sprinkle](/sprinkles/contents)!
