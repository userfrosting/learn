---
title: Database Seeding
metadata:
    description: Seeding is a simple way to populate the database with default data.
taxonomy:
    category: docs
---

Seeding can be used to populate the database. A seed is perfect to add test or default data to the database at any moment.

## Migration vs. Seed

While migrations acts on the structure of the database, seeds acts on the data itself. Furthermore, a migration can only be run once, can be undone (run down) and can have dependencies, where a seed can be run as many time as you want, don't have automatic dependencies check and can't be undone.

A seed is actually the most versatile yet simple way to interact with the database as well as many other aspect of UserFrosting. And while a seed don't have build in dependencies and has no direct way to be reverted, this can still be done manually !

>>>>> Seeds can't interact with the user through the command line and can't accept any arguments. In fact, seeds can also be run outside of the Bakery. If you want to interact with the user through the console, you should write a [custom Bakery command](/cli/custom-commands).

## Seed structure

First of all, to be picked up by the `seed` bakery command, a seed class files must be located in the `src/Database/Seeds/` directory of your Sprinkle and have the appropriate PSR-4 namespace, i.e. `UserFrosting\Sprinkle\{sprinkleName}\Database\Seeds` (where `{sprinkleName}` is the name of your sprinkle). Don't forget namespaces are case-sensitive and **must** match the case of the corresponding directories !

Each seed class needs to implement the `\UserFrosting\Sprinkle\Core\Database\Seeder\SeedInterface` interface and must at least contains the `run` method. This method will be the one ran by the `seed` Bakery command. Of course your class may contains other helper methods, but they need to be called by the `run` one. A seed can also extends the base `UserFrosting\Sprinkle\Core\Database\Seeder\BaseSeed` class, which will provide access to the service providers and [the DI Container](/services/the-di-container) (`$this->ci`) in your seed class.

>>>>> Extending the `BaseSeed` class is not required if you don't need the DI Container, but you'll still need to implement the `SeedInterface` interface.

The basic seed class looks like this :

```php
<?php

namespace UserFrosting\Sprinkle\MySprinkle\Database\Seeds;

use UserFrosting\Sprinkle\Core\Database\Seeder\BaseSeed;

class MySeed extends BaseSeed
{
    public function run()
    {
        // Do something...
    }
}       
```

## Running seeds

To run your seed, simply run the Bakery `seed` command, in UserFrosting's root directory, and pass the seed `className` you want to run as argument.

```bash
$ php bakery seed <className>
```

Multiple seeds can be run at once by listing them all, separated by a space. See [Chapter 8](/cli/commands) for more details.

Note that seeds respects the Sprinkle priority. Let's say those two seeds are defined in this order :

- `UserFrosting\Sprinkle\MySprinkle\Database\Seeds\Foo`
- `UserFrosting\Sprinkle\MyOtherSprinkle\Database\Seeds\Foo`

Running the `php bakery seed Foo` command will run the class from the `MyOtherSprinkle` sprinkle. This means two sprinkles can't have the same class name for two different seeds. It does means however the sprinkle with higher priority can actually **overwrite** a seed defined by another sprinkle, just like assets or templates. This can also be used to extends a seed class defined in another sprinkle, and add more code to an existing seed.

>>>>>> Default seeds from the `core` and `account` sprinkles can be replaced with your own data by overwriting them in your own sprinkle. By doing so, initial setup of a UserFrosting instance using your sprinkle and the **bake** command won't create the default group or permissions for instance, but the one defined in your sprinkle.

## Writing a seed

Inside the seed's `run` method, you can do whatever you want. You are not limited to pure database insertion. This means you can easily determine if you need to execute the seed before doing so. For example, the seed below will create a new group with the `bar` slug using the `Group` model, but will will only create it if the `bar` slug doesn't already exist.

```php
<?php

namespace UserFrosting\Sprinkle\MySprinkle\Database\Seeds;

use UserFrosting\Sprinkle\Core\Database\Seeder\BaseSeed;
use UserFrosting\Sprinkle\Account\Database\Models\Group;

class MyGroupSeed extends BaseSeed
{
    public function run()
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
}      
```

To run the `MyGroupSeed` :

```bash
$ php bakery seed MyGroupSeed
```

Of course, you can also choose to delete all existing groups before creating a new one :

```php
public function run()
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
public function run()
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

```bash
 [ERROR] Group with Bar slug already exist
```

### Migration dependency check

You can test if the `Group` migration has been run before trying to manipulate the data of the group table using the `migrator` service :

```php
public function run()
{
    /** @var \UserFrosting\Sprinkle\Core\Database\Migrator\Migrator; */
    $migrator = $this->ci->migrator;

    // Get ran migrations list
    $ranMigrations = $migrator->getRepository()->getMigrationsList();

    // The migration we require
    $groupMigrations = [
        '\UserFrosting\Sprinkle\Account\Database\Migrations\v400\ActivitiesTable',
        '\UserFrosting\Sprinkle\Account\Database\Migrations\v400\GroupsTable'
    ];

    foreach ($groupMigrations as $groupMigration) {
        // Make sure required migration is in the ran list. Throw exception if it isn't.
        if (!in_array($groupMigration, $ranMigrations)) {
            throw new \Exception("Migration `$groupMigration` doesn't appear to have been run!");
        }
    }

    // Execute group seed...
}
```

This can also be used using the `validateMigrationDependencies` helper method. This method accept a single string or an array of strings :

```php
public function run()
{
    // The migration we require
    $this->validateMigrationDependencies('\UserFrosting\Sprinkle\Account\Database\Migrations\v400\GroupsTable');

    // Execute group seed...
}
```

### Master seeds

A master seed can be used instead of running multiple seed at once. Any seed can execute other seeds :

```php
<?php

namespace UserFrosting\Sprinkle\Gaston\Database\Seeds;

use UserFrosting\Sprinkle\Core\Database\Seeder\BaseSeed;
use UserFrosting\Sprinkle\Gaston\MySprinkle\Seeder\MyGroups;
use UserFrosting\Sprinkle\Gaston\MySprinkle\Seeder\MyPermissions;

class MyMasterSeed extends BaseSeed
{
    public function run()
    {
        $groupsSeed = new MyGroups($this->ci);
        $groupsSeed->run();

        $permissionsSeed = new MyPermissions($this->ci);
        $permissionsSeed->run();
    }
}
```

Using the above seed, those two commands would be equivalent :

```bash
$ php bakery seed MyMasterSeed
```

```bash
$ php bakery seed MyGroups MyPermissions
```
