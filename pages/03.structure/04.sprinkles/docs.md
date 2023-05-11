---
title: Sprinkles, what are they?
metadata:
    description: Detailed breakdown of a Sprinkle's contents.
taxonomy:
    category: docs
---
<!-- TODO : Complete this page -->

Sprinkles are an integral part of UserFrosting. We'll see in details how they works in a later chapter, but for now it's important to understand what they are and how they work.

Unlike dependencies, which are usually meant to be used across many different framework, sprinkles are meant to integrate and interact directly into UserFrosting. Sprinkles could be compared to "plugins", but we don't call them that because they're so much more.

Actually, your own application can be a Sprinkle if you want to! That's because sprinkles share the same structure as the **App Skeleton** we saw in the previous pages. 

You app can have as many sprinkles as you want. But a basic UserFrosting installation comes with **four** sprinkles, each of which will be downloaded by [Composer](/installation/requirements/essential-tools-for-php#composer) in the `/vendor` directory during installation. 

Because UserFrosting is modular, you can decide to use those bundled sprinkles or not. You may or may not need the functionality they provide in your app. We'll go over how to enabled and disable each one later. For now, let's focus on which features they provided.

## Bundled Sprinkles
### Core Sprinkle
<!-- TODO -->
`core` contains most of the "heavy lifting" PHP code, and provides a basic, public website. It loads most of UserFrosting's PHP and asset dependencies, and contains classes for features like [mail](/mail), [request throttling](/routes-and-controllers/client-input/throttle), [error handling](/advanced/error-handling), and much more!

### Account Sprinkle
<!-- TODO -->
The `account` sprinkle handles user modeling and authentication, user groups, roles, and access control, and contains the routes, templates, and controllers needed to implement pages for registration, password reset, login, and more.

### Admin Sprinkle
<!-- TODO -->
The `admin` sprinkle contains the routes, templates, and controllers to implement the administrative user management interface, as well as the group, role, and permission management interfaces.

Now that we're familiar with the basic concept, let's dig into the [contents of a Sprinkle](/sprinkles/contents)!

### AdminLTE Theme
<!-- TODO -->

## Dependencies
<!-- TODO -->