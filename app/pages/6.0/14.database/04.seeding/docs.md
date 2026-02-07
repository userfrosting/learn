---
title: Database Seeding
description: Seeding is a simple way to populate the database with default data.
wip: true
---

Seeding can be used to populate the database. A seed is perfect to add test or default data to the database at any moment.

## Migration vs. Seed

While migrations acts on the structure of the database, seeds acts on the data itself. Furthermore, a migration can only be run once, can be undone (run down) and can have dependencies, where a seed can be run as many time as you want, don't have automatic dependencies check and can't be undone.

A seed is actually the most versatile yet simple way to interact with the database as well as many other aspect of UserFrosting. And while a seed don't have build in dependencies and has no direct way to be reverted, this can still be done manually! They can even be run by a migration sometimes.

> [!NOTE]
> Seeds can't interact with the user through the command line and can't accept any arguments. This is because seeds can also be run outside of the Bakery. If you want to interact with the user through the console, you should write a [custom Bakery command](cli/custom-commands).

## Seed structure

Each seed class needs to implement the `\UserFrosting\Sprinkle\Core\Seeder\SeedInterface` interface and must at least implement the `run(): void` method. This method will be the one ran by the `seed` Bakery command. Of course your class may contains other helper methods, but they need to be called by the `run` one.

> [!TIP]
> Any services you require inside your seed class can be [injected in the constructor via Autowire](dependency-injection/the-di-container#autowiring).

The basic seed class looks like this :

```php
<?php

namespace UserFrosting\Sprinkle\MySprinkle\Database\Seeds;

use UserFrosting\Sprinkle\Core\Seeder\SeedInterface;

class MySeed implements SeedInterface
{
    public function run(): void
    {
        // Do something...
    }
}
```

## Registering Seeds

To be picked up by the `seed` bakery command, a seed class files must first be registered in the *Sprinkle Recipe*, using the `SeedRecipe` sub-recipe and the `getSeeds():array` method:

```php
<?php

namespace UserFrosting\Sprinkle\MySprinkle;

// ...
use UserFrosting\Sprinkle\MySprinkle\Database\Seeds\MySeed; // <-- Add this
use UserFrosting\Sprinkle\Core\Sprinkle\Recipe\SeedRecipe; // <-- Add this
// ...

class MyApp implements
    SprinkleRecipe,
    SeedRecipe, // <-- Add this
{
    // ...

    public function getSeeds(): array
    {
        return [
            MySeed::class,
        ];
    }

    // ...
}
```

## Running seeds

To run your seed, simply run the Bakery `seed` command, in UserFrosting's root directory. The interactive command will ask you to select which seed you wish to run and your new seed will be one of the available choice if correctly registered.

```bash
$ php bakery seed
```

Result:
```txt
Seeder
======

 Select seed(s) to run. Multiple seeds can be selected using comma separated values:
  [0] UserFrosting\Sprinkle\Account\Database\Seeds\DefaultGroups
  [1] UserFrosting\Sprinkle\Account\Database\Seeds\DefaultPermissions
  [2] UserFrosting\Sprinkle\Account\Database\Seeds\DefaultRoles
  [3] UserFrosting\Sprinkle\MySprinkle\Database\Seeds\MySeed
 >
```

The `seed:list` command can also be used to display a list of all available seeds:
```bash
$ php bakery seed:list
```

Result:
```txt
Seeds List
==========

 * UserFrosting\Sprinkle\Account\Database\Seeds\DefaultGroups
 * UserFrosting\Sprinkle\Account\Database\Seeds\DefaultPermissions
 * UserFrosting\Sprinkle\Account\Database\Seeds\DefaultRoles
 * UserFrosting\Sprinkle\MySprinkle\Database\Seeds\MySeed
```

You can also pass the seed fully qualified class name as argument to bypass interactivity and run the seed directly. Be careful to properly escape the `\` character. For example:

```bash
$ php bakery seed "UserFrosting\\Sprinkle\\MySprinkle\\Database\\Seeds\\MySeed"
```

Multiple seeds can be run at once by listing them all, separated by a space. See [Chapter 8](cli/commands) for more details.

## Writing a seed

Inside the seed's `run` method, you can do whatever you want. You are not limited to pure database insertion. This means you can easily determine if you need to execute the seed before doing so. For example, the seed below will create a new group with the `bar` slug using the `Group` model, but will will only create it if the `bar` slug doesn't already exist.

```php
public function run(): void
{
    $barGroup = Group::where('slug', 'bar')->first();

    if (!$barGroup) {
        $newGroup = new Group([
            'slug' => 'bar',
            'name' => 'Foo bar'
        ]);
        $newGroup->save();
    }
}
```

Of course, you can also choose to delete all existing groups before creating a new one :

```php
public function run(): void
{
    // Delete existing groups
    $groups = Group::all();
    foreach ($groups as $group) {
        $group->delete();
    }

    // Create my default group
    $newGroup = new Group([
        'slug' => 'bar',
        'name' => 'Foo bar'
    ]);
    $newGroup->save();
}
```

### Error handling

When an error is encountered and the code execution needs to be stopped, an exception should be thrown. Exceptions will be catch by the seed command and displayed as an error in the CLI. For example, to display an error when the `bar` group already exist :

```php
public function run(): void
{
    $barGroup = Group::where('slug', 'bar')->first();

    if (!$barGroup) {
        $newGroup = new Group([
            'slug' => 'bar',
            'name' => 'Foo bar'
        ]);
        $newGroup->save();
    } else {
        throw new \Exception('Group with Bar slug already exist');
    }
}
```

This will display the following error when running the seed :

```txt
 [ERROR] Group with Bar slug already exist
```

### Migration dependency check

You can test if the `Group` migration has been run before trying to manipulate the data of the group table using the `MigrationRepositoryInterface` service :

```php
use UserFrosting\Sprinkle\Core\Database\Migrator\MigrationRepositoryInterface;
use UserFrosting\Sprinkle\Account\Database\Migrations\v400\ActivitiesTable;
use UserFrosting\Sprinkle\Account\Database\Migrations\v400\GroupsTable;

// ...

public function __construct(
    protected MigrationRepositoryInterface $repository,
) {
}

public function run(): void
{
    // The migration we require
    $groupMigrations = [
        ActivitiesTable::class,
        GroupsTable::class,
    ];

    foreach ($groupMigrations as $groupMigration) {
        // Make sure required migration is in the ran list. Throw exception if it isn't.
        if (!$this->repository->has($groupMigration)) {
            throw new \Exception("Migration `$groupMigration` doesn't appear to have been run!");
        }
    }

    // Execute group seed...
}
```

### Master seed

A master seed can be used instead of running multiple seed at once. Any seed can execute other seeds :

```php
<?php

namespace UserFrosting\Sprinkle\MySprinkle\Database\Seeds;

use UserFrosting\Sprinkle\MySprinkle\Database\Seeds\GroupSeed;
use UserFrosting\Sprinkle\MySprinkle\Database\Seeds\MySeed;
use UserFrosting\Sprinkle\Core\Seeder\SeedInterface;

class MyMasterSeed implements SeedInterface
{
    /**
     * Inject seeds using DI
     */
    public function __construct(
        protected MySeed $mySeed,
        protected GroupSeed $groupSeed,
    ) {
    }

    public function run(): void
    {
        $this->mySeed->run();
        $this->groupSeed->run();
    }
}
```

> [!TIP]
> The same method can be used to run a seed inside a migration.
