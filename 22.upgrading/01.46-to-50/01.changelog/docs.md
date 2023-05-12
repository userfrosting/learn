---
title: What's new ?
metadata:
    description: A detailed look at what's new in UserFrosting 5.
taxonomy:
    category: docs
---

UserFrosting 5 is the culmination of almost two years of work. It is a complete rewrite of the backend PHP code. And by complete, we _really_ mean **complete**. No class was left untouched! The key points are :
- Slim 3 upgraded to Slim 4
- Pimple Dependency Injection Container replaced with [PHP-DI](https://php-di.org)
- Frontend assets management replaced with [Webpack Encore](https://symfony.com/doc/current/frontend.html)
- New Sprinkle system, now with extra modularity
- Skeleton type main repo for easier initial development
- Built-in sprinkle are now managed by Composer
- New Event dispatcher and listener services
- New Bakery command for easier debugging
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

UserFrosting 5 now requires :
- PHP 8.0 or higher
- [Node.js](https://nodejs.org/en/) 14 or higher
- [Composer 2](https://getcomposer.org/)

## New Structure

This is where all the magic happened. The new structure introduced in UserFrosting 5 makes it easier to separate your code from UserFrosting's code while also making it easier for new developer to get things started with UserFrosting. Upgrading will also be easier, as simple as upgrading your `composer.json` file, as well as overwriting default pages.

### Sprinkles
All Sprinkles are now managed by Composer. Gone is the `app/sprinkles` directory. Sprinkles will now be loaded into the `vendor/` directory, like any other dependencies.

Gone is also `app/sprinkles.json`. To register sprinkles, we now use **Sprinkle Recipe**. Recipes makes it easier for sprinkle to define other sprinkle has dependencies. In fact, recipe also makes it easier for Sprinkles to _register_ any class and resources they need. UserFrosting 4 used to rely on naming convention and auto-discovery, which was prone to errors. Registering resources also makes the structure more adaptable. You can read more about [Sprinkle Recipe here](/sprinkles/recipe).

Finally, as mentioned before, the AdminLTE Theme has been moved to it's own Sprinkle. This will make it easier to switch theme in the future, but also means some reference will be updated.

### Routes

Routes used to be in `sprinkles/routes`. While still technically PHP files, they were not part of any namespace, and were included using the `require()` methods. Routes are now namespaced along with all other class in `src/`.

### Directories

To wraps things up, the old directory structure looked like this:

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
├── vendor/
    ├── account/
    ├── admin/
    └── core/
├── composer.json
├── package.json
└── webpack.config.js
```

As you see, since `/vendor` should be omitted from version control (your Github Repository), the new structure is much more cleaner !

## Migrating

Now that we've cover the basics changes, follow on to the next pages to the steps required to bring your app up to date with UserFrosting 5.
