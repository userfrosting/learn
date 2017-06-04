---
title: Migrations
metadata:
    description: Extend the database and add your own tables in a consistent and easily replicable way with a migration.
taxonomy:
    category: docs
---

When you start building your application with UserFrosting, you'll no doubt be adding your own tables to the data model. After all, what's the point of having users if there's nothing in your application for them to use?

Though you could add new tables to your database through the command line, phpMyAdmin, or another tool, you will probably want something that is portable, allowing you to set up your database on other developers' machines and your test and production servers. To do this, you should use a **migration**. Migrations are like version control for your database. If you have ever had to tell someone to manually edit a database schema, you've faced the problem that database migrations solve.

## Migration Structure

A migration is nothing more than a PHP class that uses Eloquent's [Schema](https://laravel.com/docs/5.4/migrations#tables) interface to create, remove, and modify tables in your database. Migrations can also be used to perform additional setup tasks like seeding your tables with some default values, or prompting the developer for additional information in the command line.

When you run the main UserFrosting install script (`php bakery migrate`), it will first check your `migration` table to see which migrations have been run before. If the migration class has a record in this table, the migrate script will simply skip it.

>>>>> While multiple operations _can_ be done in the same migration file, it is recommended to use **one file/class per table** or other operation. This way, if something goes wrong while creating one of the table for example, the table previously created won't be created again when running the migrate command again. Every operation done before the error occur could even be reverted using the `migrate:rollback` command.

### Creating a migration

Each migration class needs to extend the base `Migration` class and should be located in your sprinkle `/Database/Models/Migrations/` namespace. A migration class contains two methods: `up` and `down`. The `up` method is used to add new tables, columns, or indexes to your database, while the `down` method should simply reverse the operations performed by the `up` method.

Within both of these methods you may use the Laravel schema builder to expressively create and modify tables. To learn about all of the methods available on the Schema builder, [check out its documentation](https://laravel.com/docs/5.4/migrations#creating-tables).

For a simple example, suppose that you want to create an `owlers` table, which will be used to add application-specific fields for our users:
 
```php
<?php

namespace UserFrosting\Sprinkle\MySprinkle\Model\Migrations\v400;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use UserFrosting\System\Bakery\Migrations\Migration;

class OwlersTable extends Migration
{
    public function up()
    {
        if (!$this->schema->hasTable('owlers')) {
            $this->schema->create('owlers', function (Blueprint $table) {
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
        }
    }

    public function down()
    {
        $this->schema->drop('owlers');
    }
}
```

`$this->schema` is a variable created by the base migration class, which models your database structure. In this example, we call `hasTable` to check if the `owlers` table already exists, and then create it if it doesn't. This is not strictly required since [Data consistancy](#data-consistancy) should prevent any duplicate, but it can still be useful in case another sprinkle already created a table with the same name.

We then call a series of methods on the `$table` variable in `create`'s closure, to set up the columns and other table properties.

You'll also notice that we've created a `user_id` column, which associates each record in `owlers` with a corresponding record in `users`. By adding a `unique` constraint to this column as well, we effectively set up a one-to-one relationship between `owlers` and `users`.

For a complete explanation of the available methods for working with tables, see Laravel's [Migrations](https://laravel.com/docs/5.4/migrations) chapter.

As for the `down` method, it simply tells the database structure to delete the table created by the `up` method when rolling back that migration. The table created by the `up` method would then be `deleted` by the `down` method.

>>>> For your table to work correctly with Eloquent, it should always have an autoincrementing `id` column which serves as the primary key. This is done automatically for you with the `increments` method.

### Interacting with the user

Migrations can also interact with the user by displaying information, confirming actions or asking questions to populate the database. Such an example is how migration is used to create the master user. 

Since migrations are run using UserFrosting **bakery** cli tool, which is itself using [Symfony Console Component](http://symfony.com/doc/current/components/console.html), you can use the IO methods exposed in the `$this->io` variable. For example:

```php
public function up()
{
    // Show title
    $this->io->section("Foo creation");
    
    // Get the Foo details
    $foo_name = $this->io->ask("Enter Foo name", "Default name");
            
    // Save the new Foo
    $newFoo = new Foo([
        "name" => $foo_name,
    ]);
    $newFoo->save();
}        
```

The above `up` method will display the `Foo creation` title before asking the user to enter the new Foo name and saving it to the database.

For a complete list of available commands, check out the [Symfony documentation](http://symfony.com/doc/current/console/style.html#helper-methods).

## Data Consistancy

An important aspect of migrations is data consistency. Since migrations are like recipes used to create and populate a database, the order in which theses migrations are executed is very important. You don't want to insert data into a table before that table is created ! UserFrosting uses two methods to make sure migrations are run in the correct order: **Semantic Versioning** and [Dependencies](#dependencies)

### Semantic versioning

Semantic versioning is a basic way to make sure migrations are run in the correct order between your script versions. It also helps organize migrations so it's easier to find them. This is achieved by grouping migrations by the sprinkle version number. For example:

```bash
src/Database/Models/Migrations/v400/
src/Database/Models/Migrations/v410/
src/Database/Models/Migrations/v412/
```

Any migrations related to the `4.0.0` version of the sprinkle should be located in the `v400` directory and namespace. Same goes for migrations related to version `4.1.0` and `4.1.2` of your sprinkle. Note here that dots (`.`) and dashes (`-`) are not included in the directories (and namespace) as per PSR-4 rules. Also note that not every sprinkle requires a migration. If nothing changed in the database structure between two versions, there's simply nothing to migrate. 

>>>>> Even if you only have a simple table to create, creating a migration and putting it in a semantic version directory is a good practice. You never know what changes you'll have to do later on ! 

### Dependencies

Some situations require a more complex way to make sure migrations are run the correct order. This is the case when a sprinkle requires that a migration from another sprinkle is executed before its own migration can be executed. It can also be the case when two tables inside the same version are dependent one another. 

To define which migrations are required to be executed before your own migration, you can specify the fully qualified classname of the dependent migration as an array in the `$dependencie` attribute. For example :

```php
<?php

namespace UserFrosting\Sprinkle\MySprinkle\Model\Migrations\v400;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use UserFrosting\System\Bakery\Migrations\Migration;

class OwlersTable extends Migration
{
    public $dependencies = [
        '\UserFrosting\Sprinkle\Account\Model\Migrations\v400\UsersTable',
        '\UserFrosting\Sprinkle\Account\Model\Migrations\v400\RolesTable',
        '\UserFrosting\Sprinkle\Account\Model\Migrations\v400\RoleUsersTable'
    ];
    
    public function up()
    { ... }
}
```

The above example tells the bakery `migrate` command the `UsersTable`, `RolesTable` and `RoleUsersTable` migration from the `Account` sprinkle needs to be already executed (and at least at version `4.0.0`) before executing the `OwlersTable` migration. If those migrations are not yet executed, but are pending execution, `migrate` command will take care of the order automatically. If a migration dependencies cannot be met, the `migrate` command will abort.

>>>>> Dependencies can also target previous version of your own migrations. While semantic versioning will mostly take care of this, it it still a good idea to check that the previous migration have effectivly been run before applying any migration to minimize data lost. 

## Running your migration

To run your migrations simply re-run the bakery `migrate` from your command line, in UserFrosting's root directory:

```bash
$ php bakery migrate
```

If you want to do a "fresh install" of your migration or cancel the changes made, you can rollback previous migration. See [Chapter 7](/cli/commands) for more details.
