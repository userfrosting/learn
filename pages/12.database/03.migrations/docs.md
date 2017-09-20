---
title: Migrations
metadata:
    description: Extend the database and add your own tables in a consistent and easily replicable way with a migration.
taxonomy:
    category: docs
---

When you start building your application with UserFrosting, you'll no doubt be adding your own tables to the data model. After all, what's the point of having users if there's nothing in your application for them to use?

Though you could add new tables to your database through the command line, phpMyAdmin, or another tool, you will probably want something that is portable, allowing you to set up your database on other developers' machines or on your test and production servers. To do this, you should use a **migration**. Migrations bring version control to your database. If you have ever had to share sql files or manually edit a database schema, you've faced the problem that database migrations solve.

>>>>>> Even if you only have a simple table to create, creating a migration is a good practice. You never know what to that table later on and when or who will need to create it again later on a different system or even database provider!

## Migration Structure

A migration is nothing more than a PHP class that uses Eloquent's [Schema](https://laravel.com/docs/5.4/migrations#tables) interface to create, remove, and modify tables in your database. Migrations can also be used to perform additional setup tasks like seeding your tables with some default values, or prompting the developer for additional information in the command line.


When you run the main UserFrosting install script (`php bakery migrate`), it will first check your `migration` table to see which migrations have been run before. If the migration class has a record in this table, the migrate script will simply skip it.

### Class namespace and semantic versioning

To be picked up by the `migrate` bakery command, migration class files must be located in the `src/Database/Migrations/v{version}` directory of your Sprinkle, where `{sprinkleName}` is the name of your sprinkle and `{version}` the _semantic version_ of your migration.

*Semantic versioning* is a basic way to make sure that migrations are run in the correct order between your Sprinkle versions. It also helps organize migrations so it's easier to find them. This is achieved by grouping migrations by the sprinkle version number. For example:

```bash
src/Database/Migrations/v400/
    ├── MembersTable.php
    └── OwlsTable.php
src/Database/Migrations/v410/
    ├── OwlsTable.php
    ├── SneksTable.php
    └── VolesTable.php
src/Database/Migrations/v412/
    └── MembersTable.php
```

The class names must correspond to these file names; e.g. `MembersTable.php` must contain a single `MembersTable` migration class.

Recall that [PSR-4](http://www.php-fig.org/psr/psr-4/#examples) requires that classes have a namespace that corresponds to their file path, i.e. `UserFrosting\Sprinkle\{sprinkleName}\Database\Migrations\v{version}`.  **Crucially**, namespaces are case-sensitive and **must** match the case of the corresponding directories.  Also note that dots (`.`) and dashes (`-`) are not included in the directories (and namespace) as per PSR-4 rules. 

Any migrations related to the `4.0.0` version of the sprinkle should be located in the `v400` directory and namespace. The same goes for migrations related to version `4.1.0` and `4.1.2` of your sprinkle. 

While multiple operations _can_ be done in the same migration class, it is recommended to use **one class per table or operation**. This way, if something goes wrong while creating one of the tables for example, the table previously created won't be created again when running the migrate command again. Plus, every change made before the error occurred can even be reverted using the `migrate:rollback` command.

>>>>> Not every sprinkle requires a migration. If nothing changed in the database structure between two versions, there's simply nothing to migrate!

### Up and down we go

Each migration class needs to extend the base `UserFrosting\System\Bakery\Migration` class. A migration class contains two methods: `up` and `down`. The `up` method is used to add new tables, columns, or indexes to your database, while the `down` method should simply reverse the operations performed by the `up` method.

Within both of these methods you may use the [Laravel schema builder](https://laravel.com/docs/5.4/migrations) to expressively create and modify tables. To learn about all of the methods available on the Schema builder, [check out its documentation](https://laravel.com/docs/5.4/migrations#creating-tables).

For a simple example, suppose that you want to create a `members` table, which will be used to add application-specific fields for our users:
 
```php
<?php

namespace UserFrosting\Sprinkle\MySprinkle\Database\Migrations\v400;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use UserFrosting\System\Bakery\Migration;

class MembersTable extends Migration
{
    public function up()
    {
        if (!$this->schema->hasTable('members')) {
            $this->schema->create('members', function (Blueprint $table) {
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
        $this->schema->drop('members');
    }
}
```

`$this->schema` is a variable created by the base migration class, which models your database structure. In this example, we call `hasTable` to check if the `members` table already exists, and then create it if it doesn't. 

>>>>> Using `hasTable` to make sure the table doesn't already exist is not strictly required since [Dependencies](#dependencies) could also be used to prevent any duplicate, but it can still be useful in case another sprinkle already created a table with the same name.

We then call a series of methods on the `$table` variable in `create`'s closure, to set up the columns and other table properties.

You'll also notice that we've created a `user_id` column, which associates each record in `members` with a corresponding record in `users`. By adding a `unique` constraint to this column as well, we effectively set up a one-to-one relationship between `members` and `users`.  Since we've also added a foreign key from `user_id` to the `id` column in `users`, it's **very important** that the two columns have the exact same type.  Since `id` is an unsigned integer, `user_id` must also be defined as an unsigned integer.

For a complete explanation of the available methods for working with tables, see Laravel's [Migrations](https://laravel.com/docs/5.4/migrations) chapter. They have a nice table with all the available options.

As for the `down` method, it simply tells the database structure to delete the table created by the `up` method when rolling back that migration. In the `members` example, the table created by the `up` method would be **deleted** by the `down` method.

>>>> For your table to work correctly with Eloquent, it should always have an autoincrementing `id` column which serves as the primary key. This is done automatically for you with the `increments` method.

## Dependencies

An important aspect of migrations is **data consistency**. Since migrations are like recipes used to create and populate a database, the order in which theses migrations are executed is very important. You don't want to drop those cupcakes in the oven before mixing the flour and eggs, the same way you don't want to insert data into a table before that table is created! UserFrosting uses two methods to make sure migrations are run in the correct order. The first one is **semantic versioning** described above. The other one is **dependencies**.

While semantic versioning is great for basic stuff, some situations require a more complex way to make sure migrations are run in the correct order. This is the case when a Sprinkle requires that a migration from another Sprinkle is executed before its own migration. It can also be the case when two tables inside the same version are dependent on one another. 

To define which migrations are required to be executed before your own migration, you can specify the fully qualified class name of the dependent migration as an array in the `$dependencies` attribute. For example:

```php
<?php

namespace UserFrosting\Sprinkle\MySprinkle\Database\Migrations\v400;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use UserFrosting\System\Bakery\Migration;

class MembersTable extends Migration
{
    public $dependencies = [
        '\UserFrosting\Sprinkle\Account\Database\Migrations\v400\UsersTable',
        '\UserFrosting\Sprinkle\Account\Database\Migrations\v400\RolesTable',
        '\UserFrosting\Sprinkle\Account\Database\Migrations\v400\RoleUsersTable'
    ];
    
    public function up()
    { ... }
}
```

The above example tells the bakery `migrate` command that the `UsersTable`, `RolesTable` and `RoleUsersTable` migrations from the `Account` Sprinkle need to be already executed (and at least at version `4.0.0`) before executing the `MembersTable` migration. If those migrations are not yet executed and are pending execution, the `migrate` command will take care of the order automatically. If a migration's dependencies cannot be met, the `migrate` command will abort.

>>>>> Dependencies can also target previous versions of your own migrations, but semantic versioning should already have taken care of this.

## Seeding

Migrations can also seed data into the database. Seeding should be used when creating new rows, editing existing data or anything else not related to the table structure. Seeding is done in the `seed` method. 

You can also interact with the person who is running the migration by displaying information, confirming actions or asking questions to populate the database. One example is the `CreateAdminUser` migration in the `account` Sprinkle, which is used to set up the master user account. Since migrations are run using UserFrosting's **Bakery** cli tool, which itself uses [Symfony Console](http://symfony.com/doc/current/components/console.html) as a core component, you can invoke I/O methods on the `$this->io` variable. For example:

```php
public function seed()
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

The above `seed` method will display the `Foo creation` title before asking the user to enter the new `Foo` name and saving it to the database.

For a complete list of available I/O methods, check out the [Symfony documentation](http://symfony.com/doc/current/console/style.html#helper-methods).

## Running your migration

To run your migrations simply re-run the Bakery `migrate` from your command line, in UserFrosting's root directory:

```bash
$ php bakery migrate
```

If you want to do a "fresh install" of your migration or cancel the changes made, you can **rollback** the previous migration. You can also do a dry run of your migrations using the `pretend` option. See [Chapter 8](/cli/commands) for more details.
