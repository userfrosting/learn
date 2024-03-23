---
title: Upgrade Guide
metadata:
    description: Upgrade guide from version 5.0.x to 5.1.x
taxonomy:
    category: docs
---

Upgrading UserFrosting to `5.1.x` from `5.0.x` is as simple as updating your `composer.json` file and fetching updated dependencies! First, you'll need to edit your `composer.json`.

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
    "userfrosting/framework": "~5.1.0",
    "userfrosting/sprinkle-core": "~5.1.0",
    "userfrosting/sprinkle-account": "~5.1.0",
    "userfrosting/sprinkle-admin": "~5.1.0",
    "userfrosting/theme-adminlte": "~5.1.0"
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

Some build-in permissions [were missing from the database](https://github.com/userfrosting/UserFrosting/issues/1225). Run `php bakery seed` and select `UserFrosting\Sprinkle\Account\Database\Seeds\DefaultPermissions` to install them. Theses will also be added to the `site-admin` role when running the seed.

Added permissions :
 - `uri_role`
 - `uri_roles`
 - `uri_permissions`
 - `view_role_field`
 - `create_role`
 - `delete_role`
 - `update_user_field_role`
 - `update_role_field`
 - `view_user_field_permissions`
 - `view_system_info`
 - `clear_cache`

The `site-admin` role is now on par with the root user permission by default, except for the last two permissions added, `view_system_info` & `clear_cache`. Theses can be added to the role if desired using the UI.

[notice=warning]If your application defines custom permissions to the `site-admin` role or you customized this role, **do not run the seed** unless you want to lose any custom changes. Running the seed will revert back that role to it's default state.[/notice]

#### `urlFor` service change

When calling [`urlFor`](/templating-with-twig/filters-and-functions#urlfor) **in PHP** (not Twig) to generate a route from it's name, the service as been replace. Find and replace the following import to upgrade: 
- Find : `use Slim\Interfaces\RouteParserInterface;`
- Replace : `use UserFrosting\Sprinkle\Core\Util\RouteParserInterface;`

#### Fortress

addValidationErrors

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
