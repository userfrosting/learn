---
title: Community Sprinkles
metadata:
    description: Sprinkles shared between projects are called Community Sprinkles.
taxonomy:
    category: docs
---

One great thing about the **Sprinkle system** is its ability to wrap complete functionality inside a single package. This makes it a great tool to write your website isolated from the core UserFrosting code. It also means it's super easy to share sprinkles between projects... and with other members of the UserFrosting community! That's what we call a **community sprinkle**.

## Finding Sprinkles

The best place to look for community sprinkles is on [GitHub](https://github.com). We recommend tagging your community sprinkles with the `userfrosting-sprinkle` topic. This will make it easier for people to find your **community sprinkle** [using GitHub search bar](https://github.com/search?q=topic%3Auserfrosting-sprinkle&type=Repositories).

## Distributing your Sprinkle

If you want to distribute your sprinkle, there's no real requirements that need to be met. You should at least make sure your sprinkle contains a valid `composer.json` file. This file is required to add any sort of class and PSR-4 definition to your sprinkle, so you already have one. Make sure it contains up-to-date information, like your name and license details, it's always welcome. Oh, and make sure the `type` key is defined as `userfrosting-sprinkle` in your sprinkles `composer.json` file, but it's not required since UserFrosting 5.

You should also make sure your Sprinkle is up to date with the latest version of UserFrosting. Providing documentation and examples in a `README` file will encourage dev to use your sprinkle. If your sprinkle is interacting with the database, make sure you bundle a working [migration](/database/migrations) with your sprinkle. That's pretty much it!

When sharing a community sprinkle, we highly recommend publishing it on GitHub and adding it to [Packagist](https://packagist.org). This means your Sprinkle will now be able to be included in others `composer.json`, similar to how the default sprinkles are already defined. 

When someone install your Sprinkle as a dependency, the last step is to add *your* recipe to *their* Recipe, in the `getSprinkles()` method.
