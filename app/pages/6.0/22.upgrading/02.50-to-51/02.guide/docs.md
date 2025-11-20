---
title: Upgrade Guide
metadata:
    description: Upgrade guide from version 5.0.x to 5.1.x
taxonomy:
    category: docs
---

## Dependencies
### Composer

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

Now, simply use composer to get up to date with everything :

```bash
$ composer update
```

### NPM

Open `package.json` and update from : 

```json
// ...
"dependencies": {
    "@userfrosting/sprinkle-admin": "^5.0",
    "@userfrosting/theme-adminlte": "^5.0"
},
// ...
```

To:
```json
// ...
"dependencies": {
    "@userfrosting/sprinkle-admin": "~5.1.0",
    "@userfrosting/theme-adminlte": "~5.1.0"
},
// ...
```

Now, simply use npm and [Bakery](/cli) to get up to date with everything else:

```bash
$ npm update
$ php bakery bake
```

## Migrating your Sprinkles

### Font Awesome

UserFrosting 5.1 now ships with Font Awesome 6 in the AdminLTE theme. While Font Awesome 6 is backward compatible with Font Awesome 5, some icons [have been renamed](https://docs.fontawesome.com/web/setup/upgrade/whats-changed#icons-renamed-in-version-6) and you might need to manually update these in your sprinkle. 

Checkout the Font Awesome guide for more information : [https://docs.fontawesome.com/web/setup/upgrade/whats-changed](https://docs.fontawesome.com/web/setup/upgrade/whats-changed)

### Missing default permissions

Some built-in permissions [were missing from the database](https://github.com/userfrosting/UserFrosting/issues/1225) :
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

To add them to the database, run `php bakery seed` and select `UserFrosting\Sprinkle\Account\Database\Seeds\DefaultPermissions`. These will also be added to the `site-admin` role when running the seed.

[notice=warning]If your application defines custom permissions to the `site-admin` role or you customized this role, **do not run the seed** unless you want to lose any custom changes. Running the seed will revert back the role to it's default state.[/notice]

The `site-admin` role is now on par with the root user permission by default, except for the last two permissions added, `view_system_info` & `clear_cache`. Theses can be added to the role if desired using the UI.

### `urlFor` service change

When calling [`urlFor`](/templating-with-twig/filters-and-functions#urlfor) **in PHP** (not Twig) to generate a route from its name, the service has been replaced. Find and replace the following import to upgrade: 
- Find : `use Slim\Interfaces\RouteParserInterface;`
- Replace : `use UserFrosting\Sprinkle\Core\Util\RouteParserInterface;`

### Alerts

When using the [alerts](/advanced/alert-stream) service, replace `addMessageTranslated(...);` with `addMessage(...);`. The old method is still available, but it is deprecated and will be removed in a future version.

### Fortress

Fortress has been completely rewritten for UserFrosting 5.1. Most classes have been kept and will continue working, but these have been marked deprecated and will be removed in future version. It is recommended to upgrade your code now to avoid issues later.

#### RequestSchema
The `UserFrosting\Fortress\RequestSchema` constructor's first argument now accepts the schema data as an array, as well as a string representing a path to the schema json or yaml file. The argument can still be omitted to create an empty schema. This change makes `UserFrosting\Fortress\RequestSchema\RequestSchemaRepository` obsolete and and such been ***deprecated***. For example:

```php
// Before
$schemaFromArray = new \UserFrosting\Fortress\RequestSchema\RequestSchemaRepository([
    // ...
]);

// After
$schemaFromArray = new \UserFrosting\Fortress\RequestSchema([
    // ...
]);
```

#### RequestDataTransformer
`UserFrosting\Fortress\RequestDataTransformer` is ***deprecated*** and replaced by `\UserFrosting\Fortress\Transformer\RequestDataTransformer` (*notice the difference in the namespace !*). 

When using the new class, instead of passing the schema in the constructor, you pass it directly to `transform()` or `transformField()`. For example : 

```php
// Before
$transformer = new \UserFrosting\Fortress\RequestDataTransformer($schema);
$result = $transformer->transform($data, 'skip');

// After
$transformer = new \UserFrosting\Fortress\Transformer\RequestDataTransformer();
$result = $transformer->transform($schema, $data, 'skip');
```

`\UserFrosting\Fortress\RequestDataTransformerInterface` is also ***deprecated*** and replaced by `\UserFrosting\Fortress\Transformer\RequestDataTransformerInterface`. 

[notice=tip]Before, `RequestDataTransformer` was typically created inside the controllers each time it was needed. It now can be injected using `RequestDataTransformerInterface`.[/notice]

#### ServerSideValidator
`\UserFrosting\Fortress\ServerSideValidator` is ***deprecated*** and replaced by `\UserFrosting\Fortress\Validator\ServerSideValidator` (*notice the difference in the namespace !*). 

When using the new class, instead of passing the schema in the constructor, you pass it directly to `validate()`. For example : 

```php
// Before
$validator = new \UserFrosting\Fortress\ServerSideValidator($schema, $this->translator);
$result = $validator->validate($data);

// After
$adapter = new \UserFrosting\Fortress\Validator\ServerSideValidator($this->translator);
$result = $validator->validate($schema, $data);
```

`\UserFrosting\Fortress\ServerSideValidatorInterface` is also ***deprecated*** and replaced by `\UserFrosting\Fortress\Validator\ServerSideValidatorInterface`. 
  
[notice=tip]Before, `ServerSideValidator` was typically created inside the controllers each time it was needed. It now can be injected using `ServerSideValidatorInterface`. The translator will be "sub-injected" at the same time.[/notice]

#### FormValidationAdapter
`UserFrosting\Fortress\Adapter\FormValidationAdapter` is ***deprecated***. Instead of defining the format in the `rules` method, you simply use of the appropriate class for the associated format.

| Arguments                                  | Code                                                  | Replacement class                                          |
| ------------------------------------------ | ----------------------------------------------------- | ---------------------------------------------------------- |
| `$format = json` & `$stringEncode = true`  | `rules()` or `rules('json')` or `rules('json', true)` | `UserFrosting\Fortress\Adapter\FormValidationJsonAdapter`  |
| `$format = json` & `$stringEncode = false` | `rules('json', false)`                                | `UserFrosting\Fortress\Adapter\FormValidationArrayAdapter` |
| `$format = html5`                          | `rules('html5')`                                      | `UserFrosting\Fortress\Adapter\FormValidationHtml5Adapter` |

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

```php
// Before
$adapter = new FormValidationAdapter($schema, $this->translator);
$result = $adapter->rules(); // Or $result = $adapter->rules('json');

// After
$adapter = new FormValidationJsonAdapter($this->translator);
$result = $adapter->rules($schema);
```

[notice=tip]Again, the required adapter can now be injected into your class.[/notice]

#### JqueryValidationAdapter
`UserFrosting\Fortress\Adapter\JqueryValidationAdapter` is ***deprecated***. Instead of defining the format in the `rules` method, you simply use of the appropriate class for the associated format.

| Arguments                                  | Code                                                   | Replacement class                                            |
| ------------------------------------------ | ------------------------------------------------------ | ------------------------------------------------------------ |
| `$format = json` & `$stringEncode = false` | `rules()` or `rules('json')` or `rules('json', false)` | `UserFrosting\Fortress\Adapter\JqueryValidationArrayAdapter` |
| `$format = json` & `$stringEncode = true`  | `rules('json', true)`                                  | `UserFrosting\Fortress\Adapter\JqueryValidationJsonAdapter`  |

```php
// Before
$validator = new JqueryValidationAdapter($schema, $this->translator);
$result = $validator->rules();

// After
$validator = new JqueryValidationAdapter($this->translator);
$result = $validator->rules($schema);
```

#### Validation errors

`FormValidationAdapter` and `JqueryValidationAdapter` used to have an `errors()` method to fetch validation errors messages and `validate` used to return true if errors where found, an  false otherwise. These messages are now directly returned as an array when calling `validate`. An empty array means no error. Therefor, the way to handle them has changed :  

Old : 
```php
if ($validator->validate($data) === false && is_array($validator->errors())) {
    $e = new ValidationException();
    $e->addErrors($validator->errors());
}
```

New : 
```php
$errors = $this->validator->validate($schema, $data);
if (count($errors) !== 0) {
    $e = new ValidationException();
    $e->addErrors($errors);

    throw $e;
}
```

This change affect alert's `addValidationErrors`, which can't be used anymore. 

Old :
```php
$validator = new \UserFrosting\Fortress\ServerSideValidator($schema, $this->translator);
if ($validator->validate($data) === false && is_array($validator->errors())) {
    $this->alert->addValidationErrors($validator);
        return;
}
```

New :
```php
$validator = new \UserFrosting\Fortress\Validator\ServerSideValidator($this->translator);
$errors = $this->validator->validate($schema, $data);
if (count($errors) !== 0) {
    foreach ($errors as $idx => $field) {
        foreach ($field as $eidx => $error) {
            $this->alert->addMessage('danger', $error);
        }
    }
    return;
}
```

### UserActivityLogger

If using `UserActivityLogger` service, the default constants have been moved to the `UserActivityTypes` enum. 

| Old                                        | New                                  |
| ------------------------------------------ | ------------------------------------ |
| UserActivityLogger::TYPE_REGISTER          | UserActivityTypes::REGISTER          |
| UserActivityLogger::TYPE_VERIFIED          | UserActivityTypes::VERIFIED          |
| UserActivityLogger::TYPE_PASSWORD_RESET    | UserActivityTypes::PASSWORD_RESET    |
| UserActivityLogger::TYPE_LOGGED_IN         | UserActivityTypes::LOGGED_IN         |
| UserActivityLogger::TYPE_LOGGED_OUT        | UserActivityTypes::LOGGED_OUT        |
| UserActivityLogger::TYPE_PASSWORD_UPGRADED | UserActivityTypes::PASSWORD_UPGRADED |

Plus, injections can be replaced from `UserActivityLogger` to `UserActivityLoggerInterface`.
