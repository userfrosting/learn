---
title: 5.0.x to 5.1.x
metadata:
    description: Upgrade guide from version 5.0.x to 5.1.x
taxonomy:
    category: docs
---

## Overview

UserFrosting 5.1 focuses on adding PHP 8.3 support, removing PHP 8.1 support, upgrading Laravel & PHPUnit dependencies and continue improving code quality.

### Changed PHP Requirements
UserFrosting 5.1 add official support for PHP 8.3 and remove support for PHP version 8.1. PHP 8.1 has reached [End Of Life](http://php.net/supported-versions.php) as of [insert date here]. **PHP version 8.3 is now recommended**.

### Upgraded Dependencies
 - Update from Laravel 8 to Laravel 10
 - Update from PHPUnit 9 to PHPUnit 10
 - Update from Monolog 2 to Monolog 3
  
### General
 - Test against MariaDB [#1238](https://github.com/userfrosting/UserFrosting/issues/1238)

### Framework
- [General] Removed `src/Assets` (it wasn't used as part of UserFrosting 5) 
- [General] SprinkleManager is a bit more strict on argument types. Recipe classed must be a `class-string`. The actual instance of the class will now be rejected (it wasn't a documented feature anyway).
- [Fortress] Complete refactoring of Fortress. Mostly enforcing strict types, updating PHPDocs, simplifying code logic and making uses of new PHP features and method. Most classes have been deprecated and replaced by new classes with updated implementation. In general, instead of passing the *schema* in the constructor of Adapters, Transformers and Validators class, you pass it directly to theses class methods. This makes it easier to inject the classes as services and reuse the same instance with different schemas. Checkout the documentation for more information on new class usage. 
- [Config] Methods `getBool`, `getString`, `getInt` & `getArray` now return `null` if key doesn't exist, to make it on par with parent `get` method.
- [Alert] Messages are now translated at read time ([#1156](https://github.com/userfrosting/UserFrosting/pull/1156), [#811](https://github.com/userfrosting/UserFrosting/issues/811)). Messages will be translated when using `messages` and `getAndClearMessages`. `addMessage` now accept the optional placeholders, which will be stored with the alert message. `addMessageTranslated` is **deprecated**. 
- [Alert] Translator is not optional anymore. `setTranslator` method has been removed.
- [Alert] `addValidationErrors` is deprecated (N.B.: It can't accept the new `\UserFrosting\Fortress\Validator\ServerSideValidatorInterface`)

### Core sprinkle
- Rework assets building command. This change allows new bakery command to update Npm assets, and eventually allows sprinkles to replace webpack with something else (eg. Vite). The new commands are :
  - `assets:install` : Alias for `npm install`.
  - `assets:update` : Alias for `npm update`.
  - `assets:webpack` : Alias for `npm run dev`, `npm run build` and `npm run watch`, each used to run Webpack Encore.
  - `assets:build` : Aggregator command for building assets. Include by default `assets:install` and `assets:webpack`. The `webpack` and `build-assets` command are now alias of this command. `bake` also uses this command now. Sub commands can be added to `assets:build` by listening to `AssetsBuildCommandEvent`.

***tl;dr*** Use `php bakery assets:build` instead of `php bakery webpack` or `php bakery build-assets`.

See [Assets Chapter](/asset-management) and [Bakery commands](/cli/commands) for more details.

## Upgrading to 5.1.x

Upgrading UserFrosting to `5.1.x` is as simple as updating your `composer.json` file and fetching updated dependencies! First, you'll need to edit your `composer.json`.

Update from : 
```json
// ...
"require": {
    "php": "^8.0",
    "ext-gd": "*",
    "userfrosting/framework": "^5.0",
    "userfrosting/sprinkle-core": "^5.0",
    "userfrosting/sprinkle-account": "^5.0",
    "userfrosting/sprinkle-admin": "^5.0",
    "userfrosting/theme-adminlte": "^5.0"
},
// ...
```

To:
```json
// ...
"require": {
    "php": "^8.1",
    "ext-gd": "*",
    "userfrosting/framework": "^5.1",
    "userfrosting/sprinkle-core": "^5.1",
    "userfrosting/sprinkle-account": "^5.1",
    "userfrosting/sprinkle-admin": "^5.1",
    "userfrosting/theme-adminlte": "^5.1"
},
// ...
```

Now, simply use composer and [Bakery](/cli) to get up to date with everything else:

```bash
$ composer update
$ php bakery bake
```

### Migrating your Sprinkles

#### Missing default permissions

Some build-in permissions [were missing from the database](https://github.com/userfrosting/UserFrosting/issues/1225). Run `php bakery seed` and select `UserFrosting\Sprinkle\Account\Database\Seeds\DefaultPermissions` to install them. You can now add them to existing roles if desired. 

Added permissions :
 - `uri_role`
 - `uri_roles`
 - `uri_permissions`
 - `view_role_field`

#### `urlFor` service change

When calling [`urlFor`](/templating-with-twig/filters-and-functions#urlfor) **in PHP** (not Twig) to generate a route from it's name, the service as been replace. Find and replace the following import to upgrade: 
- Find : `use Slim\Interfaces\RouteParserInterface;`
- Replace : `use UserFrosting\Sprinkle\Core\Util\RouteParserInterface;`

#### Fortress

- `UserFrosting\Fortress\RequestSchema` constructor first argument now accept the schema data as an array, as well as a string representing a path to the schema json or yaml file. The argument can still be omitted to create an empty schema. This change makes `UserFrosting\Fortress\RequestSchema\RequestSchemaRepository` obsolete and and such been ***deprecated***. For example:
  ```php
  // Before
  $schemaFromFile = new \UserFrosting\Fortress\RequestSchema('path/to/schema.json');
  $schemaFromArray = new \UserFrosting\Fortress\RequestSchema\RequestSchemaRepository([
    // ...
  ]);

  // After
  $schemaFromFile = new \UserFrosting\Fortress\RequestSchema('path/to/schema.json');
  $schemaFromArray = new \UserFrosting\Fortress\RequestSchema([
    // ...
  ]);
  ```

- `UserFrosting\Fortress\RequestSchema\RequestSchemaInterface` now extends `\Illuminate\Contracts\Config\Repository`. The interface itself is otherwise unchanged.

- `UserFrosting\Fortress\RequestDataTransformer` is ***deprecated*** and replaced by `\UserFrosting\Fortress\Transformer\RequestDataTransformer` (*notice the difference in the namespace!*). `\UserFrosting\Fortress\RequestDataTransformerInterface` is also ***deprecated*** and replaced by `\UserFrosting\Fortress\Transformer\RequestDataTransformerInterface`. When using the new class, instead of passing the schema in the constructor, you pass it directly to `transform()` or `transformField()`. For example : 
  ```php
  // Before
  $transformer = new \UserFrosting\Fortress\RequestDataTransformer($schema);
  $result = $transformer->transform($data, 'skip');

  // After
  $transformer = new \UserFrosting\Fortress\Transformer\RequestDataTransformer();
  $result = $transformer->transform($schema, $data, 'skip');
  ```

- `\UserFrosting\Fortress\ServerSideValidator` is ***deprecated*** and replaced by `\UserFrosting\Fortress\Validator\ServerSideValidator` (*notice the difference in the namespace!*). `\UserFrosting\Fortress\ServerSideValidatorInterface` is also ***deprecated*** and replaced by `\UserFrosting\Fortress\Validator\ServerSideValidatorInterface`. When using the new class, instead of passing the schema in the constructor, you pass it directly to `validate()`. For example : 
  ```php
  // Before
  $validator = new \UserFrosting\Fortress\ServerSideValidator($schema, $this->translator);
  $result = $validator->validate($data);

  // After
  $adapter = new \UserFrosting\Fortress\Validator\ServerSideValidator($this->translator);
  $result = $validator->validate($schema, $data);
  ```
  
- `UserFrosting\Fortress\Adapter\FormValidationAdapter` is ***deprecated***. 
  Instead of defining the format in the `rules` method, you simply use of the appropriate class for the associated format.
  | `rules(...)`                               | Replacement class                                          |
  |--------------------------------------------|------------------------------------------------------------|
  | `$format = json` & `$stringEncode = true`  | `UserFrosting\Fortress\Adapter\FormValidationJsonAdapter`  |
  | `$format = json` & `$stringEncode = false` | `UserFrosting\Fortress\Adapter\FormValidationArrayAdapter` |
  | `$format = html5`                          | `UserFrosting\Fortress\Adapter\FormValidationHtml5Adapter` |

  `UserFrosting\Fortress\Adapter\JqueryValidationAdapter` is ***deprecated***. 
  Instead of defining the format in the `rules` method, you simply use of the appropriate class for the associated format.
  | `rules(...)`                               | Replacement class                                            |
  |--------------------------------------------|--------------------------------------------------------------|
  | `$format = json` & `$stringEncode = true`  | `UserFrosting\Fortress\Adapter\JqueryValidationJsonAdapter`  |
  | `$format = json` & `$stringEncode = false` | `UserFrosting\Fortress\Adapter\JqueryValidationArrayAdapter` |

  All adapters above now implements `UserFrosting\Fortress\Adapter\ValidationAdapterInterface` for easier type-hinting. 
  
  Finally, instead of passing the schema in the constructor, you now pass it directly to `rules()`. 
  
  For example : 
  ```php
  // Before
  $adapter = new FormValidationAdapter($schema, $this->translator);
  $result = $adapter->rules('json', false);

  // After
  $adapter = new FormValidationArrayAdapter($this->translator);
  $result = $adapter->rules($schema);
  ```

- `ClientSideValidationAdapter` abstract class replaced with `FromSchemaTrait` trait + `ValidationAdapterInterface` interface.

- `FormValidationHtml5Adapter` Will now throw an exception on missing field param, instead of returning null.

- In `FormValidationHtml5Adapter`, when using `identical` rule, the validation used to be applied to the "confirmation" field. It will now be applied to the source field, making it consistent with array|json format. For example, if `password` requires to be identical to `passwordc`, the validation was added to the `passwordc` field. Now it's applied to `password`.

## Complete change Log

See the changelog of each component for the complete list of changes included in this release.
- [Skeleton](https://github.com/userfrosting/UserFrosting/blob/5.1/CHANGELOG.md#510)
- [Framework](https://github.com/userfrosting/framework/blob/5.1/CHANGELOG.md#510)
- [Core sprinkle](https://github.com/userfrosting/sprinkle-core/blob/5.1/CHANGELOG.md#510)
- [Account sprinkle](https://github.com/userfrosting/sprinkle-account/blob/5.1/CHANGELOG.md#510)
- [Admin sprinkle](https://github.com/userfrosting/sprinkle-admin/blob/5.1/CHANGELOG.md#510)
- [AdminLTE sprinkle](https://github.com/userfrosting/theme-adminlte/blob/5.1/CHANGELOG.md#510)
