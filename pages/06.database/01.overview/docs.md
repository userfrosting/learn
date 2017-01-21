---
title: Overview
metadata:
    description: UserFrosting uses Eloquent, Laravel's powerful ORM, to provide a fluent query builder and active record layer on top of your database.
taxonomy:
    category: docs
---

Even in relatively simple applications, writing out the same types of database queries over and over can get tedious.  To make things easier and your code [DRY](https://en.wikipedia.org/wiki/Don't_repeat_yourself)er, UserFrosting takes advantage of Laravel's excellent object-relation mapper, [Eloquent](https://laravel.com/docs/5.3/eloquent#introduction).

## Configuration

By default, UserFrosting creates a `default` database connection, and handles basic configuration through [environment variables](/basics/installation#database-configuration).

These values are, in turn, retrieved through the `db` key in your [configuration files](/sprinkles/contents#config).  For advanced database configuration, or to add additional database connections, you can directly override this key or subkeys in your Sprinkle's configuration file:

```php
<?php

// In your custom config file
return [
    ...
    
    'db'      =>  [
        'default' => [
            'driver'    => 'postgres'
        ],
        'nestdb' => [
            'driver'    => 'sqlite',
            'host'      => getenv('NESTDB_HOST'),
            'database'  => getenv('NESTDB_HOST'),
            'username'  => getenv('NESTDB_HOST'),
            'password'  => getenv('NESTDB_HOST'),
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => ''
        ]
    ],
    
    ...
];
```

## Data models

Eloquent works by having you define [model classes](https://laravel.com/docs/5.3/eloquent#eloquent-model-conventions).  Generally speaking, each model maps to a table in your database.  Interactions with the table are then handled through the corresponding model.

UserFrosting has already implemented all of the models needed for interacting with its [default tables](/database/default-tables).  These can be found in the `src/Model/` subdirectories of your sprinkles.  Among the three default Sprinkles (`core`, `account`, and `admin`), UserFrosting implements:

- Throttle
- Activity
- Group
- PasswordReset
- Permission
- Role
- User
- Verification
- Version

### Using models

Eloquent implements an [active record pattern](https://en.wikipedia.org/wiki/Active_record_pattern), which means that it represents each row in a table as an instance of the corresponding model class.

#### Create

To insert a new row into a table, you create an instance of the corresponding object class and then call its `save` method:

```php
<?php

use UserFrosting\Sprinkle\Account\Model\User;

...

$user = new User([
    'user_name' => 'david',
    'first_name' => 'David',
    'last_name' => 'Attenborough',
    'email' => 'david@example.com'
]);
$user->save();
```

>>>> Notice that the `User` class is in a [namespace](http://php.net/manual/en/language.namespaces.rationale.php).  To reference it correctly, we need to either specify the fully qualified path in a `use` statement at the top of our file, or explicitly reference it in our code as `\UserFrosting\Sprinkle\Account\Model\User`.

#### Select

Records can be fetched from the database using Eloquent's sophisticated [query builder](https://laravel.com/docs/5.3/eloquent#retrieving-models).  This is typically done by calling a static method on the corresponding model class:

```php
<?php

use UserFrosting\Sprinkle\Account\Model\User;

...

// Returns a Collection of User objects

$users = User::where('num_owls', '>', 2)->get();

// Iterate over the collection
foreach ($users as $user) {
    echo $user->first_name . "<br>";
}
```

The query builder allows us to "chain" various criteria for a query, generating and executing a (usually) single query at the end. For example, the method `where()` allows us to filter the user table by the value of a column. If we then chain this with the `get()` method, we'll get a collection of Users filtered by that criteria.

If our model implements a [relationship](https://laravel.com/docs/5.3/eloquent-relationships), we can also fetch related models through the query builder:

```php
<?php

use UserFrosting\Sprinkle\Account\Model\User;

...

// Returns a Collection of User objects, each of which contains its own Collection of Owls

$users = User::with('owls')->get();

```

Now, each User's Owls can be accessed via `$user->owls`.

#### Update

To update a row, simply fetch it from the database, modify the desired properties of the object, and then call `save()` to update with the new values:

```php
<?php

use UserFrosting\Sprinkle\Account\Model\User;

...

$david = User::where('user_name', 'david')->first();
$david->email = 'owlman@example.com';
$david->save();

```

#### Delete

Call `delete` on the active record object:

```php
<?php

use UserFrosting\Sprinkle\Account\Model\User;

...

$user = User::where('user_name', 'chuck703')->first();
$user->delete();

```

### Creating your own models

When you start building your application with UserFrosting, you'll no doubt be adding your own tables to the data model.  To interact with them in your code, you'll want to create your own model classes.  Simply extend the `UFModel` class:

```php
<?php
namespace UserFrosting\Sprinkle\Site\Model;

use UserFrosting\Sprinkle\Core\Model\UFModel;

class Owl extends UFModel {

    /**
     * @var string The name of the table for the current model.
     */
    protected $table = "owls";

    protected $fillable = [
        "name",
        "species",
        "fluffiness"
    ];
}
```

This should be placed in the `src/Model/` directory in your own Sprinkle.  Notice that we set two properties: `$table`, which should contain the name of your table, and `$fillable`, which should be an array of column names that you want to allow to be [mass assignable](https://laravel.com/docs/5.3/eloquent#mass-assignment) when creating new instances of the model.

>>>> For your model to work correctly, your table should have an autoincrementing `id` column which serves as the primary key.

### Modifying existing models

In addition to creating new models, you might also want to modify the behavior of existing models.  Since we don't want to directly modify the code in other Sprinkles, we'll need to create a new class that extends the existing class:

```php
<?php
namespace UserFrosting\Sprinkle\Site\Model;

use UserFrosting\Sprinkle\Account\Model\User;

class SuperUser extends User {

    protected $fillable = [
        "name",
        "email",
        "superpowers"
    ];
}
```

The problem, of course, is that all of the controllers in the Sprinkle that _defined_ the `User` model, are still _using_ the `User` model (this is simply how inheritance works).

Fortunately, the default Sprinkles never directly reference the `User` class.  Instead, they use the [class mapper](/sprinkles/contents#dynamic-class-mapper).  All we need to do, then, is remap the class mapper's `user` identifier to our new class, `SuperUser`.  This can be done by extending the `classMapper` service in a custom [service provider](/services/extending-services):

```php
<?php

// In /app/sprinkles/site/src/ServicesProvider/SiteServicesProvider.php

$container->extend('classMapper', function ($classMapper, $c) {
    $classMapper->setClassMapping('user', 'UserFrosting\Sprinkle\Site\Model\SuperUser');
    return $classMapper;
});

```

Now, **anywhere** that the `user` identifier is used with the class mapper, for example:

```
$user = $classMapper->staticMethod('user', 'where', 'email', 'admin@example.com')->first();
```

The class mapper will call the method on the `SuperUser` class instead.

>>>>> You might want your _own_ references to be overrideable by other Sprinkles that might be loaded later on.  In this case, you should use the class mapper in your own controllers as well.

### Advanced usage

We've only touched on the very basics of how Eloquent and the query builder work.  You will likely want to learn how to [define relationships between models](https://laravel.com/docs/5.3/eloquent-relationships), [encapsulate longer queries](https://laravel.com/docs/5.3/eloquent#local-scopes), and perform more [advanced queries](https://laravel.com/docs/5.3/queries), for example.  For this, we urge you to spend some time reading through Laravel's excellent documentation.
