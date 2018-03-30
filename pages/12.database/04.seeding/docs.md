---
title: Database Seeding
metadata:
    description: Seeding is a simple way to populate the database with default data.
taxonomy:
    category: docs
---

Seeding can be used to populate the database. A seed is perfect to add test or default data to the database at any moment.

## Migration vs. Seed

While migrations acts on the structure of the database, seeds acts on the data itself. While a migration can only be run once, can be undone (run down) and can have dependencies, a seed can be run as many time as you want, don't have automatic dependencies check and can't be undone.

A seed is actually the most versatile yet simple way to interact with the database and many aspect of UserFrosting. And while a seed don't have build in dependencies and has no direct way to be undone, it can still be done manually !

## Seed structure

First of all, to be picked up by the `seed` bakery command, a seed class files must be located in the `src/Database/Seeder/` directory of your Sprinkle and have the appropriate PSR-4 namespace, i.e. `UserFrosting\Sprinkle\{sprinkleName}\Database\Seeder` (where `{sprinkleName}` is the name of your sprinkle). Don't forget namespaces are case-sensitive and **must** match the case of the corresponding directories !

Each seed class needs to extend the base `UserFrosting\Sprinkle\Core\Database\Seeder\Seeder` class. A migration class must at least contains the `run` method. This method will be the one ran by the `seed` Bakery command. Of course your class may contains other helper methods, but they need to be called by the `run` one.

The basic seed class looks like this :

```
<?php

namespace UserFrosting\Sprinkle\MySprinkle\Database\Seeder;

use UserFrosting\Sprinkle\Core\Database\Seeder\Seeder;

class MySeed extends Seeder
{
    public function run()
    {
        // Do something...
    }
}       
```

Inside the `run` method, you can do whatever you want. The UserFrosting service providers can be accessed in the seed class by using `$this->ci` which holds an instance of the [The DI Container](/services/the-di-container).

>>>>> Seeds can't interact with the user through the command line. In fact, seeds can also be run outside of the Bakery. If you want to interact with the user through the console, you should write a [custom Bakery command](/cli/custom-commands).

Seeds respects the Sprinkle priority. Let's say those two seeds are defined in this order :

- `UserFrosting\Sprinkle\MySprinkle\Database\Seeder\Foo`
- `UserFrosting\Sprinkle\MyOtherSprinkle\Database\Seeder\Foo`

Running the `Foo` seed will run in this case the one from the `MyOtherSprinkle` sprinkle. This means two sprinkles can't have the same seeds name for two different seeds. It does means the sprinkle with higher priority can actually replace a seed defined by another sprinkle. Just like assets or templates.

## Running seeds

To run your seed, simply run the Bakery `seed` from your command line, in UserFrosting's root directory, where `className` is the name of your seed class..

```bash
$ php bakery seed <className>
```

Multiple seeds can be run at once by listing them all, separated by a space. See [Chapter 8](/cli/commands) for more details.

## Writing a seed

The seed below will create a new group with the `bar` slug using the `Group` model. The seeds will only create it if the `bar` slug doesn't already exist.

```
<?php

namespace UserFrosting\Sprinkle\Core\Database\Seeder;

use UserFrosting\Sprinkle\Core\Database\Seeder\Seeder;
use UserFrosting\Sprinkle\Account\Database\Models\Group;

class MyGroupSeed extends Seeder
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

Of course, you can delete also choose to delete all existing groups before creating a new one :

```
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

When dealing with errors, exceptions should be thrown. Exception will be catch by the seed command and displayed as an error. For example, to display an error when the `bar` group already exist :

```
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

You can also check if the `Group` migration has been run before trying to manipulate the data of the group table using the `migrator` service :

```
public function run()
{
    /** @var \UserFrosting\Sprinkle\Core\Database\Migrator\Migrator; */
    $migrator = $this->ci->migrator;

    // Get ran migrations list
    $ranMigrations = $migrator->getRepository()->getMigrationsList();

    // The migration we require
    $groupMigration = '\UserFrosting\Sprinkle\Account\Database\Migrations\v400\GroupsTable';

    // Make sure required migration is in the ran list. Throw exception if it isn't.
    if (!in_array($groupMigration, $ranMigrations)) {
        throw new \Exception("Migration `$groupMigration` doesn't appear to have been run!");
    }
}
```

### Master seeds

Finally, while you can run multiple seed at once, you can also write a master seed that call other one :

```
<?php

namespace UserFrosting\Sprinkle\Gaston\Database\Seeder;

use UserFrosting\Sprinkle\Core\Database\Seeder\Seeder;
use UserFrosting\Sprinkle\Gaston\MySprinkle\Seeder\MyGroups;
use UserFrosting\Sprinkle\Gaston\MySprinkle\Seeder\MyPermissions;

class MyMasterSeed extends Seeder
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