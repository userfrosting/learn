---
title: Extending the Database
metadata:
    description: Extend the database and add your own tables in a consistent and easily replicable way with a migration.
taxonomy:
    category: docs
---

When you start building your application with UserFrosting, you'll no doubt be adding your own tables to the data model.  After all, what's the point of having users if there's nothing in your application for them to use?

Though you could add new tables to your database through the command line, phpMyAdmin, or another tool, you will probably want something that is portable, allowing you to set up your database on other developers' machines and your test and production servers.  To do this, you should use a **migration**.

## Migrations

A migration is nothing more than a PHP script that uses Eloquent's [Schema](https://laravel.com/docs/5.3/migrations) interface to create, remove, and modify tables in your database.  Migrations can also be used to perform additional setup tasks like seeding your tables with some default values, or prompting the developer for additional information in the command line.

### Semantic versioning

Every Sprinkle can optionally have a `migrations/` directory, which contains your migration scripts.  Each migration script should follow a **semantic versioning** naming scheme - for example, `/sprinkles/site/migrations/1.0.0-dev.php`.  The file name of your migration script represents the **database version** that it implements.  For example:

```bash
sprinkles/
└── site/
    └── migrations/
        ├── 1.0.0-dev.php
        ├── 1.0.0.php
        ├── 1.1.0.php
        └── 2.0.0.php
```

When you run the main UserFrosting install script (`migrations/install.php`), it will first check your `versions` table to see if any migrations have been run for each Sprinkle before:

| sprinkle | version     |
|----------|-------------|
| account  | 4.0.0-alpha |
| admin    | 4.0.0-alpha |
| site     | 1.0.0       |

If the Sprinkle has a record in this table, the install script will only run those migrations that have a version **greater than** the version in this record.  For example, in this case, the installer would only run the `1.1.0` and `2.0.0` migrations.

### Creating a migration

Your migrations themselves will contain PHP commands to create, modify, and drop tables.  For a simple example, suppose that you want to create an `owlers` table, which will be used to add application-specific fields for our users:

```php
<?php
    use Illuminate\Database\Capsule\Manager as Capsule;
    use Illuminate\Database\Schema\Blueprint;
    /**
     * Owler table
     */
    if (!$schema->hasTable('owlers')) {
        $schema->create('owlers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->unique();
            $table->string('city', 255)->nullable();
            $table->timestamps();

            $table->engine = 'InnoDB';
            $table->collation = 'utf8_unicode_ci';
            $table->charset = 'utf8';
            $table->foreign('user_id')->references('id')->on('users');
            $table->index('user_id');
        });
        echo "Created table 'owlers'..." . PHP_EOL;
    } else {
        echo "Table 'owlers' already exists.  Skipping..." . PHP_EOL;
    }
```

`$schema` is a global variable created by the installer script, which models your database structure.  In this example, we call `hasTable` to check if the `owlers` table already exists, and then create it if it doesn't.  We then call a series of methods on the `$table` variable in `create`'s closure, to set up the columns and other table properties.

You'll also notice that we've created a `user_id` column, which associates each record in `owlers` with a corresponding record in `users`.  By adding a `unique` constraint to this column as well, we effectively set up a one-to-one relationship between `owlers` and `users`.

For a complete explanation of the available methods for working with tables, see Laravel's [Migrations](https://laravel.com/docs/5.3/migrations) chapter.

>>>> For your table to work correctly with Eloquent, it should always have an autoincrementing `id` column which serves as the primary key.  This is done automatically for you with the `increments` method.

### Running your migration

To run your migrations simply re-run `php install.php` from your command line, in UserFrosting's main `migrations/` directory.  If you want to do a "fresh install" of your migration, you will have to reverse any changes it made manually, and then change the version for the Sprinkle's migration in the `versions` table to a lower value.

## Creating your own models

Of course, to interact with the new tables in your code, you'll probably want to create your own model classes.  To do this, simply extend the `UFModel` class:

```php
<?php
namespace UserFrosting\Sprinkle\Site\Model;

use UserFrosting\Sprinkle\Core\Model\UFModel;

class Owler extends UFModel {

    /**
     * @var string The name of the table for the current model.
     */
    protected $table = "owlers";

    protected $fillable = [
        "user_id",
        "city"
    ];

    /**
     * Directly joins the related user, so we can do things like sort, search, paginate, etc.
     */
    public function scopeJoinUser($query)
    {
        $query = $query->select('owlers.*');

        $query = $query->leftJoin('users', 'owlers.user_id', '=', 'users.id');

        return $query;
    }

    /**
     * Get the user associated with this owler.
     */
    public function user()
    {
        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = static::$ci->classMapper;

        return $this->belongsTo($classMapper->getClassMapping('user'), 'user_id');
    }
}
```

This should be placed in the `src/Model/` directory in your own Sprinkle.  Notice that we set two properties: `$table`, which should contain the name of your table, and `$fillable`, which should be an array of column names that you want to allow to be [mass assignable](https://laravel.com/docs/5.3/eloquent#mass-assignment) when creating new instances of the model.

We also define two methods.  `scopeJoinUser` allows us to automatically join the columns in the `users` table when we use Laravel's query builder to retrieve `owlers`.  For example:

```php
$owlers = Owler::where('city', 'London')->joinUser()->get();
```

The second method, `user()`, defines a [one-to-one relationship](https://laravel.com/docs/5.3/eloquent-relationships#one-to-one) between `Owler` and `User`.  This is similar to what `scopeJoinUser()` does, except that it actually creates a completely separate `User` object that you can access as a property of an `Owler`:

```php
$owler = Owler::where('city', 'London')->first();

// Get the associated user object
$user = $owler->user;
```

## Modifying existing models

In addition to creating new models, you might also want to modify the behavior of existing models.  Suppose for example, that we want to be able to access the related user object for a given `Owler`.  Since we don't want to directly modify the `User` model in the `account` Sprinkle, we'll need to create a new class that extends `User`:  

```php
<?php
namespace UserFrosting\Sprinkle\Site\Model;

use UserFrosting\Sprinkle\Account\Model\User;

class OwlerUser extends User {

    /**
     * Get the owler associated with this user.
     */
    public function owler()
    {
        return $this->hasOne('\UserFrosting\Sprinkle\Site\Model\Owler', 'user_id');
    }
}
```

The problem, of course, is that all of the controllers in the Sprinkle that _defined_ the `User` model, are still _using_ the `User` model (this is simply how inheritance works).

Fortunately, the default Sprinkles never directly reference the `User` class.  Instead, they use the [class mapper](/sprinkles/contents#dynamic-class-mapper).  All we need to do, then, is remap the class mapper's `user` identifier to our new class, `OwlerUser`.  This can be done by extending the `classMapper` service in a custom [service provider](/services/extending-services):

```php
<?php

// In /app/sprinkles/site/src/ServicesProvider/SiteServicesProvider.php

$container->extend('classMapper', function ($classMapper, $c) {
    $classMapper->setClassMapping('user', 'UserFrosting\Sprinkle\Site\Model\OwlerUser');
    return $classMapper;
});

```

Now, **anywhere** that the `user` identifier is used with the class mapper, for example:

```
$user = $classMapper->staticMethod('user', 'where', 'email', 'admin@example.com')->first();
$owler = $user->owler;
```

The class mapper will call the method on the `OwlerUser` class instead.  Note that we now get an instance of `OwlerUser`, which means we can use our new relationship to get at the related `Owler` object.

>>>>> You might want your _own_ references to be overrideable by other Sprinkles that might be loaded later on.  In this case, you should use the class mapper in your own controllers as well.

### Advanced usage

We've only touched on the very basics of how Eloquent and the query builder work.  You will likely want to learn how to [define relationships between models](https://laravel.com/docs/5.3/eloquent-relationships), [encapsulate longer queries](https://laravel.com/docs/5.3/eloquent#local-scopes), and perform more [advanced queries](https://laravel.com/docs/5.3/queries), for example.  For this, we urge you to spend some time reading through Laravel's excellent documentation.
