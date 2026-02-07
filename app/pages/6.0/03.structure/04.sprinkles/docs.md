---
title: Sprinkles, what are they?
description: Detailed breakdown of a sprinkle's contents.
---

Sprinkles are an integral part of UserFrosting. We'll see in detail how they work in [a later chapter](/sprinkles), but for now it's important to have an overview.

Unlike dependencies, which are usually meant to be used across many different frameworks, sprinkles are meant to integrate directly into UserFrosting and interact together. Sprinkles could be compared to "plugins", but they're so much more.

Actually, your own project, created from the _App Skeleton_, **is** a sprinkle, even if it's not located in the `/vendor` directory. The directory structure is one part, but what really makes a sprinkle a sprinkle is its **recipe**, which we'll see later. Your project will actually be the **main sprinkle**, while any sprinkles it uses will be **dependent sprinkles**.

Your app can have as many sprinkles as you want. A sprinkle could even depend on another sprinkle, creating a nested doll of sprinkles. Maybe your first app could become a sprinkle in someone else's app!

## Bundled Sprinkles

A default UserFrosting installation comes with **four** sprinkles, each of which will be downloaded by [Composer](/installation/requirements/essential-tools-for-php#composer) in the `/vendor` directory during installation.

Because UserFrosting is modular, you can decide to use these bundled sprinkles or not. You may or may not need the functionality each provides in your app. We'll go over how to enable and disable them [later](/sprinkles/recipe#removing-default-sprinkles). For now, let's focus on their features.

> [!NOTE]
> Remove all sprinkles and the [Framework](/structure/framework) can be used by itself to create a very basic Slim/Symfony Console application with no database, template, etc. !

### Core Sprinkle
The **Core** contains most of the "heavy lifting" PHP code. It provides all the necessary services for [databases](/database), [templating](/templating-with-twig), [error handling](/advanced/error-handling), [mail](/mail) support, [request throttling](/routes-and-controllers/client-input/throttle), and more.

### Account Sprinkle
The **Account** sprinkle handles [user modeling and authentication](/users), [user groups](/users/groups), and [roles & access control](/users/access-control). It contains the routes, templates, and controllers needed to implement pages for registration, password resetting, login, and more.

The Account sprinkle depends on the Core sprinkle.

### Admin Sprinkle
The **Admin** sprinkle contains the routes and controllers to implement the administrative user management interface, as well as the group, role, and permission management interfaces.

The Admin sprinkle depends on the Core, Account and Pink Cupcake sprinkles.

### Pink Cupcake Theme
The **Pink Cupcake** theme sprinkle contains all the Twig templates and frontend assets built with [UiKit](https://getuikit.com). It provides a modern, responsive interface with Vue 3 components for interactive features.

The Pink Cupcake sprinkle depends on the Core and Account sprinkles.
