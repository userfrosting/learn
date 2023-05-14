---
title: Sprinkles, what are they?
metadata:
    description: Detailed breakdown of a Sprinkle's contents.
taxonomy:
    category: docs
---

Sprinkles are an integral part of UserFrosting. We'll see in details how they works in a later chapter, but for now it's important to understand what they are and how they work.

Unlike dependencies, which are usually meant to be used across many different framework, sprinkles are meant to integrate directly into UserFrosting and interact together. Sprinkles could be compared to "plugins", but we don't call them that because they're so much more.

Actually, your own project, created from the _App Skeleton_, **is** a Sprinkle. Even if it's not physically in the `/vendor` directory. The directory structure is one part, but what really makes a Sprinkle a Sprinkle, is it's **recipe**, which we'll see later.

You app can have as many sprinkles as you want. A sprinkle could even depend on another sprinkle, creating a nesting doll of sprinkle. Maybe your first app could become a sprinkle in someone else app!

## Bundled Sprinkles

A default UserFrosting installation comes with **four** sprinkles, each of which will be downloaded by [Composer](/installation/requirements/essential-tools-for-php#composer) in the `/vendor` directory during installation. 

Because UserFrosting is modular, you can decide to use those bundled sprinkles or not. You may or may not need the functionality they provide in your app. We'll go over how to enabled and disable each one later. For now, let's focus on which features they provided.

[notice]Remove all sprinkles and the [Framework](/structure/framework) can be used by itself to create a very basic Slim / Symfony console application (with no database, template, etc.) ![/notice]

### Core Sprinkle
The **Core** contains most of the "heavy lifting" PHP code. It provides all the necessary services for database, templating, [error handling](/advanced/error-handling), [mail](/mail) support, [request throttling](/routes-and-controllers/client-input/throttle), and more.

### Account Sprinkle
The **Account** sprinkle handles user modeling and authentication, user groups, roles, and access control. It contains the routes, templates, and controllers needed to implement pages for registration, password reset, login, and more.

The Account sprinkle depends on the Core Sprinkle.

### Admin Sprinkle
The **Admin** sprinkle contains the routes and controllers to implement the administrative user management interface, as well as the group, role, and permission management interfaces.

The Admin sprinkle depends on the Core, Account & AdminLTE Sprinkles.

### AdminLTE Theme
The **AdminLTE** theme sprinkle contains all the twig files and frontend asset to implement the [AdminLTE](https://adminlte.io) template.

The AdminLTE sprinkle depends on the Core and Account Sprinkles.
