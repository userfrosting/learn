---
title: What's new ?
description: A detailed look at what's new in UserFrosting 5.
obsolete: true
---

UserFrosting 5 is the culmination of two years of work. It is a complete rewrite of the backend PHP code. And by complete, we _really_ mean **complete**. No class was left untouched! The key points are :
- Slim 3 upgraded to Slim 4
- Pimple Dependency Injection Container replaced with [PHP-DI](https://php-di.org)
- Frontend assets management replaced with [Webpack Encore](https://symfony.com/doc/current/frontend.html)
- New Sprinkle system, now with extra modularity
- Skeleton type main repo for easier initial development
- Built-in sprinkle are now loaded by Composer
- New Event dispatcher and listener services
- New Bakery command available for easier debugging
- Added return type, type-hint and other quality improvement across all code
- (Almost) 100% test coverage and quality check through PHPStan
- ***And so much more***

The Slim migration itself is a big deal. While brining UF to a more modern era, it had the most impact on the core changes applied to the UserFrosting framework. Most changes were made in response to the new Slim 4 requirements and changes. It also required the use of a new Dependency Injector, which itself _really_ brought UserFrosting 5 to a whole new level. You should really check out [PHP-DI](https://php-di.org), it's awesome!

Basically, UserFrosting 5 uses updated dependencies, all of existing code have been updated to new standard and new test coverage should provide better long term stability.

## What UserFrosting 5 isn't ? 

UserFrosting 5 is not a drop-in replacement for UserFrosting 4. The whole backend has been rewritten, with a new container, new sprinkle system, etc. which will require most sprinkle and app to be updated manually.

## What's the same ?

UserFrosting 5 keeps the same frontend as UserFrosting 4. But the bases have been set for the future, with Webpack Encore and the current AdminLTE theme have been separated in it's own Sprinkle, all of which provides the necessary tools for [Vue.js](https://vuejs.org) or other similar frontend in the future.

The database structure is 99% identical to UserFrosting 4. One migration have been added, but it's a simple, optional even, modification. This means your data shouldn't be impacted by the upgrade. 

## New requirements

With the release of PHP 8 in november 2020 some major updates where made to the system requirements. UserFrosting 5 now requires :
- PHP 8.0 or higher
- [Node.js](https://nodejs.org/en/) 18 or higher
- [Composer 2](https://getcomposer.org/)

While any version of PHP 8 is supported, we recommend you use the latest version.

## New Structure

This is where all the magic happened. The [new structure](/structure) introduced in UserFrosting 5 makes it even more easier to separate your code from UserFrosting's code. This also makes it easier for new developer to get started with UserFrosting. Upgrading will also be easier, as everything is now managed by Composer. Requesting a new major version will required to edit your `composer.json` file. Finally, default pages and content are not part of the Core Sprinkle anymore. This makes easier to overwrite default pages.

The old directory structure looked like this:

```txt
├── app/
    ├── cache/
    ├── database/
    ├── logs/
    ├── sessions/
    ├── sprinkles/
        ├── account/
        ├── admin/
        ├── core/
        └── yourSprinkle/
            ├── assets/
            ├── config/
            ├── locale/
            ├── routes/
            ├── schema/
            ├── src/
                ├── Bakery/
                ├── Controllers/
                ├── Database/
                ├── [...]
                └── Sprunje/
            ├── templates/
            ├── tests/
            ├── composer.json
            └── asset-bundles.json
    ├── storage/
    ├── system/
    ├── tests/
    ├── .env
    ├── defines.php
    └── sprinkles.json
├── public/
├── vendor/
└── composer.json
```

The new structure looks like this:
```txt
├── app/
    ├── assets/
    ├── cache/
    ├── config/
    ├── database/
    ├── locale/
    ├── logs/
    ├── schema/
    ├── src/
        ├── Bakery/
        ├── Controllers/
        ├── Database/
        ├── [...]
        ├── Routes/
        ├── Sprunje/
        └── yourSprinkle.php
    ├── storage/
    ├── templates/
    ├── tests/
    └── .env
├── public/
├── vendor/
    ├── account/
    ├── admin/
    └── core/
├── composer.json
├── package.json
└── webpack.config.js
```

As you see, since `/vendor` should be omitted from version control (your Github Repository), the new structure is much more cleaner! This doesn't means your code can't be distributed and added as a sprinkle in another UserFrosting powered app. [Community Sprinkle](/sprinkles/community) are still a thing! This new structure even makes it easier to develop them!

### Sprinkles
All [bundled Sprinkles](/structure/sprinkles#bundled-sprinkles) are now managed by Composer, and directly required in your project's `composer.json`. Gone is the `app/sprinkles` directory. Your app now sits in `/app` directly and other Sprinkles will now be loaded into the `vendor/` directory, like any other dependencies.

Gone is also `app/sprinkles.json`. To register sprinkles, we now use **[Sprinkle Recipe](/sprinkles/recipe)**. Recipes makes it easier for sprinkle to define other sprinkle as dependencies. In fact, recipe also makes it easier for Sprinkles to _register_ any class and resources they need. UserFrosting 4 used to rely on naming convention and auto-discovery, which was prone to errors. Registering resources also makes the structure more adaptable, as there's **no** naming convention anymore for classes : The structure of `/src` can be whatever you want! You can read more about [Sprinkle Recipe here](/sprinkles/recipe).

Finally, as mentioned before, the AdminLTE Theme has been moved to it's own Sprinkle. This will make it easier to switch theme in the future, but also means some reference will need to be updated.

### Routes

Routes used to be in `sprinkles/routes`. While still technically PHP files, they were not part of any namespace, and were included using the [`require()`](https://www.php.net/manual/fr/function.require.php) method. Routes are now namespaced, same as any other class in `src/`.

It also means routes file are not directly overwritten anymore, similar to template files. They're not real classes, and must be mapped, or in case of UserFrosting 5, injected properly.

### Dependency injection

As mentioned in previous paragraphs, UserFrosting 5 now uses [PHP-DI](https://php-di.org) a. This brings a **ton** of new features and capability. The best way to understand how it affect your code in UserFrosting 5, head over to the [Dependency Injection Chapter](/dependency-injection).

## Migrating

Now that we've cover the basics changes, follow on to the next pages to the steps required to bring your app up to date with UserFrosting 5.
