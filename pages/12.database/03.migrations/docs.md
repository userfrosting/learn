---
title: Migrations
metadata:
    description: Extend the database and add your own tables in a consistent and easily replicable way with a migration.
taxonomy:
    category: docs
---

When you start building your application with UserFrosting, you'll no doubt be adding your own tables to the data model. After all, what's the point of having users if there's nothing in your application for them to use?

Though you could add new tables to your database through the command line, phpMyAdmin, MySQL Workbench or another tool, you will probably want something that is portable, allowing you to set up your database on other developers' machines or on your test and production servers. To do this, you should use a **migration**. Migrations bring version control to your database. If you have ever had to share sql files or manually edit a database schema, you've faced the problem that database migrations solve.

[notice=tip]Even if you only have a simple table to create, creating a migration is a good practice. You never know what changes you'll need to do to that table later on. You also don't know when or who will need to create that table again later on a different system or even database provider![/notice]

Migrations are also very useful when dealing with [Automated Test](/testing). Tests can use your migrations to setup a temporary or a test database so your tests are executed in a safe environment, keeping your production database secure.

## Migration Structure

A migration is nothing more than a PHP class that uses Eloquent's [Schema Builder](https://laravel.com/docs/5.4/migrations#tables) to create, remove, and modify tables in your database. Migrations can also be used to perform additional setup tasks like seeding your tables with some default values.


When you run the main UserFrosting install script (`php bakery migrate`), it will first check a special `migration` table to see which migrations have been run before. If the migration class has a record in this table, the migrate script will simply skip it.

### Class namespace

To be picked up by the `migrate` bakery command, migration class files must be located in the `src/Database/Migrations/` directory of your Sprinkle and have the appropriate PSR-4 namespace.

Recall that [PSR-4](http://www.php-fig.org/psr/psr-4/#examples) requires that classes have a namespace that corresponds to their file path, i.e. `UserFrosting\Sprinkle\{sprinkleName}\Database\Migrations`(where `{sprinkleName}` is the name of your sprinkle).  **Crucially**, namespaces are case-sensitive and **must** match the case of the corresponding directories. Also note that dots (`.`) and dashes (`-`) are not included in the directories (and namespace) as per PSR-4 rules. The class names must also correspond to these file names; e.g. `MembersTable.php` must contain a single `MembersTable` class.


You can also optionally organize your migrations in subdirectories so it's easier to find and manage them. For example:

```bash
src/Database/Migrations/v400/
    ├── MembersTable.php
    └── OwlsTable.php
src/Database/Migrations/SneksNStuff/
    ├── SneksTable.php
    └── VolesTable.php
src/Database/Migrations/v500/
    ├── OwlsTable.php
    └── MembersTable.php
```

While multiple operations _can_ be done in the same migration class, it is recommended to use **one class per table (or operation)**. This way, if something goes wrong while creating one of the tables for example, the table previously created won't be created again when running the migrate command again. Plus, every change made before the error occurred can even be reverted using the `migrate:rollback` command.


### Up and down we go

Each migration class needs to extend the base `UserFrosting\Sprinkle\Core\Database\Migration` class. A migration class must contains two methods: `up` and `down`. The `up` method is used to add new tables, columns, or indexes to your database, while the `down` method should simply reverse the operations performed by the `up` method.

Within both of these methods you may use the [Laravel schema builder](https://laravel.com/docs/5.4/migrations) (available in the `$this->schema` property) to expressively create and modify tables. To learn about all of the methods available on the Schema builder, [check out Laravel documentation](https://laravel.com/docs/5.4/migrations#creating-tables).

For a simple example, suppose that you want to create a `members` table, which will be used to add application-specific fields for our users:

```php
<?php

namespace UserFrosting\Sprinkle\MySprinkle\Database\Migrations;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use UserFrosting\Sprinkle\Core\Database\Migration;

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

[notice=note]Using `hasTable` to make sure the table doesn't already exist is not strictly required since [Dependencies](#dependencies) could also be used to prevent any duplicate, but it can still be useful in case another sprinkle already created a table with the same name.[/notice]

We then call a series of methods on the `$table` variable in `create`'s closure, to set up the columns and other table properties.

You'll also notice that we've created a `user_id` column, which associates each record in `members` with a corresponding record in `users`. By adding a `unique` constraint to this column as well, we effectively set up a one-to-one relationship between `members` and `users`.  Since we've also added a foreign key from `user_id` to the `id` column in `users`, it's **very important** that the two columns have the exact same type.  Since `id` is an unsigned integer, `user_id` must also be defined as an unsigned integer.

For a complete explanation of the available methods for working with tables, see Laravel's [Migrations](https://laravel.com/docs/5.4/migrations) chapter. They have a nice table with all the available options.

As for the `down` method, it simply tells the database structure to delete the table created by the `up` method when rolling back that migration. In the `members` example, the table created by the `up` method would be **deleted** by the `down` method.

[notice=warning]For your table to work correctly with Eloquent, it should always have an autoincrementing `id` column which serves as the primary key. This is done automatically for you with the `increments` method.[/notice]

## Dependencies

An important aspect of migrations is **data consistency**. Since migrations are like recipes used to create a database, the order in which theses migrations are executed is very important. You don't want to drop those cupcakes in the oven before mixing the flour and eggs, the same way you don't want to add a column to a table before said table is created! UserFrosting uses **dependencies** to make sure migrations are run in the correct order.

Some situations require a more complex way to make sure migrations are run in the correct order. This is the case when a Sprinkle requires that a migration from another Sprinkle is executed before its own migration. It can also be the case when two tables inside the same Sprinkle are dependent on one another.

To define which migrations are required to be executed before your own migration, you can specify the fully qualified class name of the dependent migration as an array in the `$dependencies` attribute. For example:

```php
<?php

namespace UserFrosting\Sprinkle\MySprinkle\Database\Migrations\v400;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use UserFrosting\Sprinkle\Core\Database\Migration;

class MembersTable extends Migration
{
    public static $dependencies = [
        '\UserFrosting\Sprinkle\Account\Database\Migrations\v400\UsersTable',
        '\UserFrosting\Sprinkle\Account\Database\Migrations\v400\RolesTable',
        '\UserFrosting\Sprinkle\Account\Database\Migrations\v400\RoleUsersTable'
    ];

    public function up()
    { ... }
}
```

[notice=note]Don't forget to start your fully qualified class names with `\`. If you're using `class` to get the fully qualified class name you can do the following :

```php
'\\' . MyClass::class
```
[/notice]

The above example tells the bakery `migrate` command that the `UsersTable`, `RolesTable` and `RoleUsersTable` migrations from the `Account` Sprinkle need to be already executed before executing the `MembersTable` migration from the `MySprinkle` sprinkle. If those migrations are not yet executed and are pending execution, the `migrate` command will take care of the order automatically. If a migration's dependencies cannot be met, the `migrate` command will abort.

[notice=note]Dependencies can also target previous versions of your own migrations. For example, you should check that your `member` table is created before adding a new column in a new migration.[/notice]

## Running your migration

To run your migrations simply re-run the Bakery `migrate` from your command line, in UserFrosting's root directory:

```bash
$ php bakery migrate
```

If you want to do a "fresh install" of your migration or cancel the changes made, you can **rollback** the previous migration. You can also do a dry run of your migrations using the `pretend` option. See [Chapter 8](/cli/commands) for more details.
