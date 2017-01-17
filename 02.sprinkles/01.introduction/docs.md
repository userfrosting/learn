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
- installation scripts (migrations)

Sprinkles are loaded in a specific order, and entities of each type in one sprinkle can extend entities of the same type in other sprinkles.  We'll explain exactly what we mean by "extend" in a minute.

Each Sprinkle is located in its own subdirectory under the main `/app/sprinkles/` directory.  To create a new Sprinkle, simply create a new subdirectory in `/app/sprinkles/`.

## Loading Sprinkles

Sprinkles (with the exception of `core`) must be explicitly loaded.  UserFrosting will look for a file called `sprinkles.json` within `app/sprinkles` to determine which Sprinkles to load.  The file `sprinkles.example.json` contains a valid Sprinkle order, and can be used as a template for the `sprinkles.json` file. The example file contains:

```json
{
    "base": [
        "account",
        "admin"
    ]
}
```

The `base` key defines the Sprinkle load order.

Notice that we don't have to explicitly list the `core` Sprinkle in this initialization list; since it is required for all projects, `SprinkleManager` will load it automatically.

>>>> The order in which we load our Sprinkles is important.  Files in one Sprinkle may override files with the same name and path in previously loaded Sprinkles.  For example, if we created `site/templates/pages/about.html.twig`, this would override `core/templates/pages/about.html.twig` because we load the `site` Sprinkle *after* the `core` Sprinkle.

### Default Sprinkles

A basic UserFrosting installation comes with five sprinkles, each of which can be found in its own subdirectory in `/app/sprinkles`:

```
app
├──sprinkles
   ├── account
   ├── admin
   ├── core
   └── root
```

Of these, 3 comprise the bulk of UserFrosting's functionality: `core`, `account`, and `admin`.

#### Core

`core` contains most of the "heavy lifting" PHP code, and is required for every project.  For example, it loads most of UserFrosting's Composer requirements, and contains classes for features like [mail](/other-services/mail), [request throttling](/routes-and-controllers/user-input/throttle), and the code that actually manages the Sprinkle system itself!

#### Account

The `account` sprinkle handles user modeling and authentication, user groups, roles, and access control, and contains the routes, templates, and controllers needed to implement pages for registration, password reset, login, and more.

#### Admin

The `admin` sprinkle contains the routes, templates, and controllers to implement the administrative user and group management interface, as well as the site settings and role and permission management interfaces.

The Sprinkle `root` is a special theme Sprinkle, used to provide some cosmetic styling for different types of users.

Now that we're familiar with the basic concept, let's dig into the [contents of a Sprinkle](/sprinkles/contents)!
