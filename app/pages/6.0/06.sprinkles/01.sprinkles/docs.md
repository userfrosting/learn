---
title: Basic concept
description: Learn how sprinkles provide a modular system for extending UserFrosting without modifying core code.
---

In earlier versions of UserFrosting, you had no choice—customizing meant editing core files. Want to add a field to the registration page? Modify `register.twig` directly. Need to extend the `User` class? Edit `User.php` itself. Every framework update risked breaking your work.

**UserFrosting's sprinkle system** eliminates this problem entirely. Sprinkles let you extend, override, and customize functionality while keeping your code completely separate from the core framework. Updates become safe again—your customizations stay intact, isolated in your own sprinkle. Think of it like adding layers to a cake: each layer (sprinkle) builds on the previous one without destroying what's underneath.

## What is a "Sprinkle"?

A sprinkle is a functionally cohesive unit of code and content that fits neatly into its own package. A sprinkle could implement a core component, a third-party plugin, a theme, or even an entire website! As a reminder, any project created from the [App Skeleton](structure/introduction#the-app-skeleton-your-project-s-template) is still a sprinkle!

Each sprinkle can contain any or all of the following entities:

- assets (JS, CSS, images, etc)
- configuration files
- translations (i18n)
- route endpoints (URL definitions)
- request schema
- PHP code (typically classes)
- templates (Twig files)
- "extra" content that does not fit into any of these categories

As seen in the [App Structure Chapter](structure), sprinkles can be located anywhere. The only requirement is its **recipe** needs to be accessible through a PSR-4 compatible namespace.

### The Main Sprinkle

Sprinkles are loaded in a specific order, defined by their dependencies, and entities of a given type in one sprinkle can extend entities of the same type in other sprinkles. The topmost sprinkle, usually your own project, is called the **main sprinkle**. All other sprinkles are called **depends sprinkles**.

### Default Sprinkles

A basic UserFrosting installation comes with four sprinkles. A description of them can be found [in a previous chapter](structure/sprinkles#bundled-sprinkles).

Now that we're familiar with the basic concept, let's dig into the [contents of a sprinkle](sprinkles/content)!
