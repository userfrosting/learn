---
title: Upgrade Guide
metadata:
    description: Upgrade guide from version 5.1.x to 5.2.x
taxonomy:
    category: docs
---

## Dependencies
### Composer

Upgrading UserFrosting to `5.1.x` from `5.2.x` is as simple as updating your `composer.json` file and fetching updated dependencies! First, you'll need to edit your `composer.json`.

Update from : 
```json
// ...
"require": {
    "php": "^8.1",
    "ext-gd": "*",
    "userfrosting/framework": "~5.1.0",
    "userfrosting/sprinkle-core": "~5.1.0",
    "userfrosting/sprinkle-account": "~5.1.0",
    "userfrosting/sprinkle-admin": "~5.1.0",
    "userfrosting/theme-adminlte": "~5.1.0"
},
// ...
```

To:
```json
// ...
"require": {
    "php": "^8.1",
    "ext-gd": "*",
    "userfrosting/framework": "~5.2.0",
    "userfrosting/sprinkle-core": "~5.2.0",
    "userfrosting/sprinkle-account": "~5.2.0",
    "userfrosting/sprinkle-admin": "~5.2.0",
    "userfrosting/theme-adminlte": "~5.2.0"
},
// ...
```

Now, simply use composer to get up to date with everything :

```bash
$ composer update
```

### NPM

Open `package.json` and update from : 

```json
// ...
"dependencies": {
    "@userfrosting/sprinkle-admin": "~5.1.0",
    "@userfrosting/theme-adminlte": "~5.1.0"
},
// ...
```

To:
```json
// ...
"dependencies": {
    "@userfrosting/sprinkle-admin": "~5.2.0",
    "@userfrosting/theme-adminlte": "~5.2.0"
},
// ...
```

Now, simply use npm and [Bakery](/cli) to get up to date with everything else:

```bash
$ npm update
$ php bakery bake
```

## Migrating your Sprinkles

<!-- TODO -->
