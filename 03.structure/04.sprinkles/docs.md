---
title: Sprinkles, what are they?
metadata:
    description: Detailed breakdown of a Sprinkle's contents.
taxonomy:
    category: docs
---

Sprinkles are an integral part of UserFrosting. We'll see in detail how they work in [a later chapter](/sprinkles), but for now it's important to have an overview.

Unlike dependencies, which are usually meant to be used across many different framework, Sprinkles are meant to integrate directly into UserFrosting and interact together. Sprinkles could be compared to "plugins", but they're so much more.

Actually, your own project, created from the _App Skeleton_, **is** a Sprinkle, even if it's not located in the `/vendor` directory. The directory structure is one part, but what really makes a Sprinkle a Sprinkle is its **recipe**, which we'll see later. Your project will actually be the **main Sprinkle**, while any Sprinkles it uses will be **dependent Sprinkles**.

You app can have as many Sprinkles as you want. A Sprinkle could even depend on another Sprinkle, creating a nesting doll of Sprinkles. Maybe your first app could become a Sprinkle in someone else's app!

## Bundled Sprinkles

A default UserFrosting installation comes with **four** Sprinkles, each of which will be downloaded by [Composer](/installation/requirements/essential-tools-for-php#composer) in the `/vendor` directory during installation. 

Because UserFrosting is modular, you can decide to use these bundled Sprinkles or not. You may or may not need the functionality each provides in your app. We'll go over how to enable and disable them [later](/sprinkles/recipe#removing-default-sprinkles). For now, let's focus on their features.

[notice]Remove all Sprinkles and the [Framework](/structure/framework) can be used by itself to create a very basic Slim/Symfony Console application with no database, template, etc. ![/notice]

### Core Sprinkle
The **Core** contains most of the "heavy lifting" PHP code. It provides all the necessary services for [databases](/database), [templating](/templating-with-twig), [error handling](/advanced/error-handling), [mail](/mail) support, [request throttling](/routes-and-controllers/client-input/throttle), and more.

### Account Sprinkle
The **Account** Sprinkle handles [user modeling and authentication](/users), [user groups](/users/groups), and [roles and access control](/users/access-control). It contains the routes, templates, and controllers needed to implement pages for registration, password resetting, login, and more.

The Account Sprinkle depends on the Core Sprinkle.

### Admin Sprinkle
The **Admin** Sprinkle contains the routes and controllers to implement the administrative user management interface, as well as the group, role, and permission management interfaces.

The Admin Sprinkle depends on the Core, Account & AdminLTE Sprinkles.

### AdminLTE Theme
The **AdminLTE** theme Sprinkle contains all the twig files and frontend assets to implement the [AdminLTE](https://adminlte.io) template.

The AdminLTE Sprinkle depends on the Core and Account Sprinkles.
