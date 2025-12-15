---
title: Overview
description: UserFrosting uses Eloquent, Laravel's powerful ORM, to provide a fluent query builder and active record layer on top of your database.
obsolete: true
---

Even in relatively simple applications, writing out the same types of database queries over and over can get tedious. To make things easier and your code [DRY](https://en.wikipedia.org/wiki/Don't_repeat_yourself)er, UserFrosting takes advantage of Laravel's excellent object-relation mapper, **[Eloquent](https://laravel.com/docs/8.x/eloquent#introduction)**.

## Configuration

By default, UserFrosting creates a `default` database connection, and handles basic configuration through [environment variables](configuration/environment-vars).

These values are, in turn, retrieved through the `db` key in your [configuration files](configuration/config-files). For advanced database configuration, or to add additional database connections, you can directly override this key or subkeys in your Sprinkle's configuration file:

```php
<?php

// In your custom config file
return [
    // ...

    'db' => [
        'default' => 'nestdb',
        'connections' => [
            'nestdb' => [
                'driver'    => 'sqlite',
                'host'      => env('NESTDB_HOST'),
                'database'  => env('NESTDB_NAME'),
                'username'  => env('NESTDB_USER'),
                'password'  => env('NESTDB_PASSWORD'),
                'charset'   => 'utf8',
                'collation' => 'utf8_unicode_ci',
                'prefix'    => ''
            ]
        ],
    ],

    // ...
];
```

## Data models

Eloquent works by having you define [model classes](https://laravel.com/docs/8.x/eloquent#eloquent-model-conventions). Generally speaking, each model maps to a table in your database. Interactions with the table are then handled through the corresponding model.

UserFrosting has already implemented all of the models needed for interacting with its [default tables](database/default-tables). These can be found in the `src/Database/Models/` subdirectories of your sprinkles. Among the three default Sprinkles (`core`, `account`, and `admin`), UserFrosting implements:

- Migration
- Throttle
- Activity
- Group
- PasswordReset
- Permission
- Role
- User
- Verification

### Using models

Eloquent implements an [active record pattern](https://en.wikipedia.org/wiki/Active_record_pattern), which means that it represents each row in a table as an instance of the corresponding model class.

#### Create

To insert a new row into a table, you create an instance of the corresponding object class and then call its `save` method:

```php
<?php

use UserFrosting\Sprinkle\Account\Database\Models\User;

// ...

$user = new User([
    'user_name' => 'david',
    'first_name' => 'David',
    'last_name' => 'Attenborough',
    'email' => 'david@example.com'
]);
$user->save();
```

> [!WARNING]
> Notice that the `User` class is in a [namespace](http://php.net/manual/en/language.namespaces.rationale.php). To reference it correctly, we need to either specify the fully qualified path in a `use` statement at the top of our file, or explicitly reference it in our code as `\UserFrosting\Sprinkle\Account\Database\Models\User`.

#### Select

Records can be fetched from the database using Eloquent's sophisticated [query builder](https://laravel.com/docs/8.x/eloquent#retrieving-models). This is typically done by calling a static method on the corresponding model class:

```php
<?php

use UserFrosting\Sprinkle\Account\Database\Models\User;

// ...

// Returns a Collection of User objects

$users = User::where('num_owls', '>', 2)->get();

// Iterate over the collection
foreach ($users as $user) {
    echo $user->first_name . "<br>";
}
```

The query builder allows us to "chain" various criteria for a query, generating and executing a (usually) single query at the end. For example, the method `where()` allows us to filter the user table by the value of a column. If we then chain this with the `get()` method, we'll get a collection of Users filtered by that criteria.

If our model implements a [relationship](https://laravel.com/docs/8.x/eloquent-relationships), we can also fetch related models through the query builder:

```php
<?php

use UserFrosting\Sprinkle\Account\Database\Models\User;

// ...

// Returns a Collection of User objects, each of which contains its own Collection of Owls

$users = User::with('owls')->get();

```

Now, each User's Owls can be accessed via `$user->owls`.

#### Update

To update a row, simply fetch it from the database, modify the desired properties of the object, and then call `save()` to update with the new values:

```php
<?php

use UserFrosting\Sprinkle\Account\Database\Models\User;

// ...

$david = User::where('user_name', 'david')->first();
$david->email = 'owlman@example.com';
$david->save();

```

#### Delete

Call `delete` on the active record object:

```php
<?php

use UserFrosting\Sprinkle\Account\Database\Models\User;

// ...

$user = User::where('user_name', 'chuck703')->first();
$user->delete();

```

### Advanced usage

We've only touched on the very basics of how Eloquent and the query builder works. You will likely want to learn how to [define relationships between models](https://laravel.com/docs/8.x/eloquent-relationships), [encapsulate longer queries](https://laravel.com/docs/8.x/eloquent#local-scopes), and perform more advanced queries, for example. For this, we urge you to spend some time reading through [Laravel Eloquent excellent documentation](https://laravel.com/docs/8.x/eloquent).
