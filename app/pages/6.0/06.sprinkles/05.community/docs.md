---
title: Community Sprinkles
description: Sprinkles shared between projects are called community sprinkles.
obsolete: true
---

One great thing about the **Sprinkle system** is its ability to wrap complete functionality inside a single package. This makes it a great tool to write your website isolated from the core UserFrosting code. It also means it's super easy to share sprinkles between projects... and with other members of the UserFrosting community! That's what we call a **community sprinkle**.

## Finding sprinkles
The best place to look for community sprinkles is on [GitHub](https://github.com). We recommend tagging your community sprinkles with the `userfrosting-sprinkle` topic. This will make it easier for people to find your **community sprinkle** [using GitHub search bar](https://github.com/search?q=topic%3Auserfrosting-sprinkle&type=Repositories).

## Loading a community sprinkle
### Sprinkle recipe
Once you've found a sprinkle you'd like to use in your own project, the first step is to `require` it in [your `composer.json`](/sprinkles/customize#composer-json), along with the core UserFrosting sprinkles.
```json
    "require": {
...
        "userfrosting/theme-adminlte": "~5.1.0",
        "owlfancy/owlery-sprinkle": "^0.1"
    },
```

Next, add *their* recipe to *your* recipe, in the `getSprinkles()` method.
```php
use Owlfancy\Sprinkle\Owlery ; //don't forget to include the sprinkle's namespace!
...
    public function getSprinkles(): array
    {
        return [
            Core::class,
            Account::class,
            Admin::class,
            AdminLTE::class, // include any base sprinkles you might need
            Owlery::class, // add the community sprinkle as well!
        ];
    }
```

> [!NOTE]
> If the sprinkle does not include Javascript, you're done setting up--skip to the "Installing" section.

### Javascript
If the sprinkle includes Javascript, you will need to add it to both the "dependencies" [in your `package.json`](/asset-management/webpack-encore#npm-and-packages-json)... 
```json
 "dependencies": {
        "@userfrosting/sprinkle-admin": "~5.1.0",
        "@userfrosting/theme-adminlte": "~5.1.0",
        "sprinkle-owlery":"github:owlfancy/sprinkle-owlery"
    },
```
...*and* to the "sprinkles" [in `/webpack.config.js`](/asset-management/webpack-encore#webpack-encore-configuration).
```js
// List dependent sprinkles and local entries files
const sprinkles = {
    AdminLTE: require('@userfrosting/theme-adminlte/webpack.entries'),
    Admin: require('@userfrosting/sprinkle-admin/webpack.entries'), // core sprinkles come included
    Owlery: require('sprinkle-owlery/webpack.entries'),// add any community sprinkles as well
}
```
(We'll talk more about these files in the [Assets chapter](/asset-management)).

> [!TIP]
> In the `package.json` example above, we're loading the Userfrosting core sprinkles from npm, and the Owlery sprinkle from Github. Each community sprinkle decides where it is published, and should include this in their README.

## Installing a community sprinkle
Once you've added the sprinkle to the recipe, `composer.json`, and (if needed) `package.json`, you're ready to use Composer and [Bakery](/cli/commands#bake) to download and install the files.
```
composer update
php bakery bake
```

### Database (optional)
If needed, [migrations](/cli/commands#migrate) and [seeds](/cli/commands#seed) can be run manually through Bakery. 
```txt
php bakery migrate
php bakery seed
```
You can also use `php bakery migrate:status` and `php bakery seed:list` to check what migrations and seeds the sprinkle has added, and if any migrations have not yet been run. 

> [!TIP]
> `php bakery bake` should run migrations automatically, but you can use the above commands later if you don't want to run a full `bake`.

> [!IMPORTANT]
> Migrations set up the database, if the sprinkle adds features there. Depending on the individual sprinkle, seeds may be convenient management tools and not used during install.

## Distributing your sprinkle

### Basic prep work
When you're ready to distribute your sprinkle, first use `composer update` to make sure it is up to date with the latest version of UserFrosting.

Providing documentation and examples in a `README` file will encourage other devs to use your sprinkle. You should specify whether they need to add your sprinkle to `package.json`, if there are any seeds to run, and any other steps needed to fully set up. 
> [!TIP]
> As an example, if your sprinkle adds a new permission, anyone installing your sprinkle may need to manually add that permission to the appropriate roles through the UserFrosting UI.

Every sprinkle needs a valid `composer.json` file. This file is required to add any sort of class and PSR-4 definition to your sprinkle, so you already have one. Make sure it contains up-to-date information; your name and license details are always welcome. If you include a `type` key, be sure it's defined as `userfrosting-sprinkle` in your sprinkles `composer.json` file--but this is not required as of UserFrosting 5.

We highly recommend publishing your community sprinkle on GitHub and adding it to [Packagist](https://packagist.org). This lets others include your sprinkle in `composer.json`, similar to how the default sprinkles are already defined. 

You may also have some extra steps depending on what features your sprinkle provides:

### Database?
If your sprinkle changes the database structure, make sure you bundle a working [migration](/database/migrations) with your sprinkle.
You might also consider whether any seeds would help manage those changes.

### Javascript?
If your sprinkle includes any Javascript, you'll need to show npm how to install it to the `/node_modules` folder. This folder is similar to `/vendor` that Composer uses, so you may see projects with folders in both locations.

[You'll need to flesh out your `package.json`](https://docs.npmjs.com/cli/configuring-npm/package-json) to do this.
```json
    "name": "sprinkle-owlery",
    "homepage": "https://github.com/owlfancy/sprinkle-owlery",
    "license": "BSD-3-Clause",
    "repository": "@owlfancy/sprinkle-owlery",
```

> [!TIP]
> Consider including a ["files" section](https://docs.npmjs.com/cli/v10/configuring-npm/package-json#files), so that only Javascript files are included in `/node_modules`--npm doesn't typically need PHP or Twig files! Different sprinkles may need npm to be aware of different files or folders.

#### Npmjs
[Npmjs](https://www.npmjs.com/about) is a registry of JS projects, similar to Packagist for Composer.

When publishing on npm, you also need a ["version" tag](https://docs.npmjs.com/cli/v10/configuring-npm/package-json#version) in your `package.json`. This must follow [Semantic Versioning](https://semver.org/).

#### Github
If the Javascript is tightly bound to your sprinkle and you've already published your sprinkle on Github, you may decide not to register it with npm. It is possible for npm to pull your code from Github instead. You'll specify this in your `package.json`:
```json
    "repository": "github:owlfancy/sprinkle-owlery",
```
> [!WARNING]
> Publishing via Github may [prevent your users from automatically getting updates](https://medium.com/@jonchurch/use-github-branch-as-dependency-in-package-json-5eb609c81f1a) through npm.
