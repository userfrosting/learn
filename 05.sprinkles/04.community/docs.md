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

If you want to distribute your sprinkle, there's no real requirements that need to be met. You should at least make sure your sprinkle contains a valid `composer.json` file. This file is required to add any sort of class and PSR-4 definition to your sprinkle, so the chances are good you already have one. Make sure it contains up-to-date information, like your name and licence details, it's always welcome. Oh, and make sure the `type` key is defined as `userfrosting-sprinkle` in your sprinkles `composer.json` file !

You should also make sure your Sprinkle is up to date with the latest version of UserFrosting. Providing documentation and examples in a `README` file will encourage dev to use your sprinkle. If your sprinkle is interacting with the database, make sure you bundle a working [migration](/database/migrations) with your sprinkle. That's pretty much it! 

## Sprinkles and Composer

When sharing a community sprinkle, we highly recommend publishing it on GitHub and adding it to [Packagist](https://packagist.org). Since the `app/sprinkles.json` file is actually interpreted by Composer the same way a `composer.json` file is when running `composer update`, you can add any sprinkles available in the [`require` key](https://getcomposer.org/doc/01-basic-usage.md#the-require-key) of your `sprinkles.json` file. For example:

```json
{
    "require": {
        "foo/bar" : "^1.0.2"
    },
    "base": [
        "core",
        "account",
        "admin",
        "bar"
    ]
}
```

When executing `composer update` with the above example, composer will automatically load `foo/bar` (version 1.0.2 or above) into your `/app/sprinkles` directory, as long as `foo/bar` is defined as `userfrosting-sprinkle` type in its own `composer.json` file. That's where the magic happens! This works just like any normal Composer dependency. You can also add non-Packagist sprinkles the same way you would do with any Composer dependencies, as long as the type is set to `userfrosting-sprinkle` in the repo `composer.json`. Otherwise, composer will load the package to `app/vendor` as usual. And don't forget to add `bar` to the list of sprinkle after!

>>>>>> Other sprinkles can also be defined as your sprinkle's dependencies in your own `composer.json`.

By default, Composer will load into `app/sprinkles/` every dependency marked as a type `userfrosting-sprinkle` in a folder with the same name as the Packagist or GitHub repository inside `app/sprinkles`. In the above example, the `foo/bar` package would be downloaded to the `app/sprinkles/bar` directory. If you want to install your sprinkle in a different directory, you can use the `installer-name` key in your `composer.json` file.

For example, to install `foo/bar` as `app/sprinkles/AwesomeBar/`:

```json
{
    "name": "foo/bar",
    "type": "userfrosting-sprinkle",
    ...
    "extra": {
        "installer-name": "AwesomeBar"
    }
}
```

See [Composer Installers documentation](https://github.com/composer/installers#custom-install-names) for more information on this.
