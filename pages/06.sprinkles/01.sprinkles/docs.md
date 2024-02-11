---
title: Basic concept
metadata:
    description: 
taxonomy:
    category: docs
---

In previous versions of UserFrosting, you had to directly modify the files that come with the default installation in order to add your own functionality. For example, if you wanted to add a field to the user registration page, you had to actually modify `register.twig`. Or, if you wanted to add a new relation on the `User` class, you had to modify the actual `User.php` class that comes with UserFrosting.

Starting in version 4, this is no longer the case! **UserFrosting 4** introduced the **[Sprinkle system](/structure/sprinkles)** as a way to completely isolate the code and content that you and your team produce from the core UserFrosting installation, as well as third-party code. **UserFrosting 5** took this a step further, by allowing Composer to manage sprinkles and decoupling even more functionality from the base install. 

## What is a "Sprinkle"?

A sprinkle is a functionally cohesive unit of code and content that fits neatly into its own package. A sprinkle could implement a core component, a third-party plugin, a theme, or even an entire website! As a reminder, any project created from the [App Skeleton](/structure/introduction#the-app-skeleton-your-project-s-template) is still a sprinkle!

Each sprinkle can contain any or all of the following entities:

- assets (JS, CSS, images, etc)
- configuration files
- translations (i18n)
- route endpoints (URL definitions)
- request schema
- PHP code (typically classes)
- templates (Twig files)
- "extra" content that does not fit into any of these categories

As seen in the [App Structure Chapter](/structure), sprinkles can be located anywhere. The only requirement is its **recipe** needs to be accessible through a PSR-4 compatible namespace.

### The Main Sprinkle

Sprinkles are loaded in a specific order, defined by their dependencies, and entities of a given type in one sprinkle can extend entities of the same type in other sprinkles. The topmost sprinkle, usually your own project, is called the **main sprinkle**. All other sprinkles are called **depends sprinkles**. 

### Default Sprinkles

A basic UserFrosting installation comes with four sprinkles. A description of them can be found [in a previous chapter](/structure/sprinkles#bundled-sprinkles).

Now that we're familiar with the basic concept, let's dig into the [contents of a sprinkle](/sprinkles/content)!
