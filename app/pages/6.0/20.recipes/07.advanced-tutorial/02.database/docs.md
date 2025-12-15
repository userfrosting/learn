---
title: Integrating the Database
metadata:
    description: Customizing our UserFrosting controller to retrieve dynamic data from the database and display it in our page.
    obsolete: true
---

Now that we have a sprinkle with an empty page to work with, it's time to get started with our database integration. Our data structures for the database table will be straightforward. We'll store pastries in a `pastries` table using the following columns:

- `id`
- `name`
- `description`
- `origin`

## Creating a data model

First, we create the [data model](/database/overview#data-models). In this model, we define the table name, list the columns we want to be [mass assignable](https://laravel.com/docs/8.x/eloquent#mass-assignment) and enable automatic timestamp creation.

**app/src/Database/Models/Pastries.php**:
```php
<?php

namespace UserFrosting\App\Database\Models;

use UserFrosting\Sprinkle\Core\Database\Models\Model;

class Pastries extends Model
{
    protected $fillable = [
        'name',
        'description',
        'origin',
    ];
}
```

## Creating the database table using a migration

Next we create a migration class. This migration will create the database table for us. Migrations are located in `src/Database/Migrations`. Since this is the first version of our Sprinkle, we'll add them to the `v100` sub directory. Finally, since the migration's purpose is to create the `pastries` table, we'll name the migration class `PastriesTable`.

**app/src/Database/Migrations/V100/PastriesTable.php**:
```php
<?php

namespace UserFrosting\App\Database\Migrations\V100;

use Illuminate\Database\Schema\Blueprint;
use UserFrosting\Sprinkle\Core\Database\Migration;

class PastriesTable extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(): void
    {
        $this->schema->create('pastries', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->string('origin', 255);
            $table->timestamps();

            $table->engine = 'InnoDB';
            $table->collation = 'utf8_unicode_ci';
            $table->charset = 'utf8';
        });
    }

    /**
     * {@inheritdoc}
     */
    public function down(): void
    {
        $this->schema->drop('pastries');
    }
}
```

As described in the [Migration](/database/migrations) chapter, the `up` method contains the instructions to create the new table while the `down` method contains the instructions to undo the changes made by the `up` method - in this case, removing the `pastries` table.

## Populating the database with default data using a seed

Next we'll populate our newly created table with some default data. To do this, we'll create a [**seed**](/database/seeding). While this could be done in a migration, it is recommended to create default database values using a seed as it enabled the data to be recreated if it get deleted. We call this seed `DefaultPastries`:

**app/src/Database/Seeds/DefaultPastries.php**:
```php
<?php

namespace UserFrosting\App\Database\Seeds;

use UserFrosting\Sprinkle\Core\Seeder\SeedInterface;
use UserFrosting\Sprinkle\Pastries\Database\Models\Pastries;

class DefaultPastries implements SeedInterface
{
    /**
     * {@inheritdoc}
     */
    public function run(): void
    {
        foreach ($this->pastries() as $pastry) {
            $pastry = new Pastries($pastry);
            $pastry->save();
        }
    }

    protected function pastries(): array
    {
        return [
            [
                'name'        => 'Apple strudel',
                'description' => 'Sliced apples and other fruit are wrapped and cooked in layers of filo pastry. The earliest known recipe is in Vienna, but several countries in central and eastern Europe claim this dish.',
                'origin'      => 'Central Europe',
            ],
            [
                'name'        => 'Pain au chocolat',
                'description' => '"Chocolate bread", also called a chocolatine in southern France and in French Canada, is a French pastry consisting of a cuboid-shaped piece of yeast-leavened laminated dough, similar to puff pastry, with one or two pieces of chocolate in the centre.',
                'origin'      => 'France',
            ],
            [
                'name'        => 'Baklava',
                'description' => 'A Turkish pastry that is rich and sweet, made of layers of filo pastry filled with chopped nuts and sweetened with syrup or honey.',
                'origin'      => 'Turkish/Greek',
            ],
        ];
    }
}
```

## Integrating the seed into the migration

Notice at this point how we haven't run our migration and seed yet. This means the table and the default data doesn't exist. Before doing so, we'll make one small change to the *PastriesTable* migration. We'll tell the migration to execute the *DefaultPastries* seed after the table is created. Let's edit the *PastriesTable* `up()` method (down method isn't affected) and add the following at the bottom of the method :

```php
// Run Seed for default pastries
$seed = new DefaultPastries();
$seed->run();
```

We'll also need to import the `DefaultPastries` class by adding `use UserFrosting\App\Database\Seeds\DefaultPastries;` to the top of our class.

Our `PastriesTable` class should now look like this :

**app/src/Database/Migrations/V100/PastriesTable.php**:
```php
<?php

namespace UserFrosting\App\Database\Migrations\V100;

use Illuminate\Database\Schema\Blueprint;
use UserFrosting\Sprinkle\Core\Database\Migration;
use UserFrosting\Sprinkle\Pastries\Database\Seeds\DefaultPastries;

class PastriesTable extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(): void
    {
        $this->schema->create('pastries', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->string('origin', 255);
            $table->timestamps();

            $table->engine = 'InnoDB';
            $table->collation = 'utf8_unicode_ci';
            $table->charset = 'utf8';
        });

        // Run Seed for default pastries
        $seed = new DefaultPastries();
        $seed->run();
    }

    /**
     * {@inheritdoc}
     */
    public function down(): void
    {
        $this->schema->drop('pastries');
    }
}
```

## Registering the migration and seed in the sprinkle recipe

We also need to tell our Recipe that it will be providing migrations and seeds. To do so, your recipe class must implement, on top of `SprinkleRecipe`, `UserFrosting\Sprinkle\Core\Sprinkle\Recipe\MigrationRecipe` and `use UserFrosting\Sprinkle\Core\Sprinkle\Recipe\SeedRecipe`. 

We'll then list our migration in the `getMigrations()` method, and our seed in the `getSeeds()` method. 

> [!NOTE]
> Note the seed can still work if it's not registered because we're calling it directly in the migration. However, if the seed is not registered, it won't show up when running `php bakery seed`.

The **MyApp** recipe should now look like this : 

**app/src/MyApp.php**
```php
namespace UserFrosting\App;

use UserFrosting\Sprinkle\Account\Account;
use UserFrosting\Sprinkle\Admin\Admin;
use UserFrosting\Sprinkle\Core\Core;
use UserFrosting\Sprinkle\Core\Sprinkle\Recipe\MigrationRecipe; // <-- Add here !
use UserFrosting\Sprinkle\Core\Sprinkle\Recipe\SeedRecipe; // <-- Add here !
use UserFrosting\Sprinkle\Pastries\Database\Migrations\V100\PastriesPermissions; // <-- Add here !
use UserFrosting\Sprinkle\Pastries\Database\Migrations\V100\PastriesTable; // <-- Add here !
use UserFrosting\Sprinkle\Pastries\Database\Seeds\DefaultPastries; // <-- Add here !
use UserFrosting\Sprinkle\SprinkleRecipe;
use UserFrosting\Theme\AdminLTE\AdminLTE;

class MyApp implements 
    SprinkleRecipe, 
    MigrationRecipe, // <-- Add here !
    SeedRecipe // <-- Add here !
{
    // ...

    /**
     * Return an array of all registered Migrations.
     *
     * @return string[]
     */
    public function getMigrations(): array
    {
        return [
            PastriesTable::class, // <-- Add this block
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getSeeds(): array
    {
        return [
            DefaultPastries::class, // <-- Add this block
        ];
    }
    
    // ...
}
```

We are now ready to run our migration. From the command line, use the [Bakery migrate command](/cli/commands#migrate) to run the migration up: 

```bash
$ php bakery migrate
```

You should now see the newly created table with the default rows in your database (using _phpMyAdmin_ or the database CLI, for instance).

## Fetching data from the database

Now it's time to go back to our controller and fetch the data from our new database table. The first thing we need to do is tell the controller to use the model we created. To do so, we add the fully qualified namespace of the `Pastries` model to the controller's list of [namespace aliases](http://php.net/manual/en/language.namespaces.importing.php). Right before `class PastriesPageAction`, add:

```php
use UserFrosting\Sprinkle\Pastries\Database\Models\Pastries;
```

Now that we've defined this convenient alias for our model, it's time to interact with it and select all the available rows. Replace : 

```php
$pastries = [];
```

With : 
```php
$pastries = Pastry::all();
```

> [!NOTE]
> Fetching all the available rows is not an ideal solution since, in production, it can involve an arbitrarily large number of rows. This can clutter the UI, providing poor user experience, and can also result in poor performance (slow page generation, high server resource usage). It is recommended to use AJAX and [**Sprunging**](/database/data-sprunjing) to display paginated data in this situation.

The `$pastries` variable should now contains an [Eloquent Collection](https://laravel.com/docs/8.x/eloquent-collections) of `Pastry` objects. 

### Debugging

At this point, it's a good idea to use [**debugging**](/troubleshooting/debugging#debug-statements) to make sure everything works as it should. We'll use the `DebugLogger` service to do so. 

Start by adding the facade class to the usage declaration of your class:

```php
use UserFrosting\Sprinkle\Core\Log\DebugLogger;
```

...next, inject the `DebugLogger` inside the `__invoke` method:
```php
public function __invoke(
    Response $response,
    Authenticator $authenticator,
    Twig $view,
    DebugLogger $logger, // <-- Add here
): Response {
```

...and finally pass the `$pastries` variable to the debugger (right under the `$pastries = ...` line) :

```php
$logger->debug($pastries);
```

The next time we run this code, the UserFrosting log (`app/logs/userfrosting.log`) should contain something similar to this:
```
debug.DEBUG: [{
	"id": 1,
	"name": "Apple strudel",
	"description": "Sliced apples and other fruit are wrapped and cooked in layers of filo pastry. The earliest known recipe is in Vienna, but several countries in central and eastern Europe claim this dish.",
	"origin": "Central Europe",
	"created_at": "2017-09-23 21:09:26",
	"updated_at": "2017-09-23 21:09:26"
}, {
	"id": 2,
	"name": "Pain au chocolat",
	"description": "\"Chocolate bread\", also called a chocolatine in southern France and in French Canada, is a French pastry consisting of a cuboid-shaped piece of yeast-leavened laminated dough, similar to puff pastry, with one or two pieces of chocolate in the centre.",
	"origin": "France",
	"created_at": "2017-09-23 21:09:26",
	"updated_at": "2017-09-23 21:09:26"
}, {
	"id": 3,
	"name": "Baklava",
	"description": "A Turkish pastry that is rich and sweet, made of layers of filo pastry filled with chopped nuts and sweetened with syrup or honey.",
	"origin": "Turkish\/Greek",
	"created_at": "2017-09-23 21:09:26",
	"updated_at": "2017-09-23 21:09:26"
}]
```

As you can see, it successfully listed our three default pastries along with their description and origin. You can now comment out the logger line as we don't require it anymore, but might need it later.

## Displaying the data in Twig

The only thing left to do is to send the collection to Twig. Back in our template file, we'll use Twig's [`for`](https://twig.symfony.com/doc/3.x/tags/for.html) construct to loop through the `pastries` variable and render a new HTML table row for each pastry:

```html
{% extends 'pages/abstract/dashboard.html.twig' %}

{# Overrides blocks in head of base template #}
{% block page_title %}Pastries{% endblock %}
{% block page_description %}This page provides a yummy list of pastries{% endblock %}

{% block body_matter %}
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title"><i class="fa fa-cutlery fa-fw"></i> List of Pastries</h3>
                </div>
                <div class="box-body">
                    <tr>
                        <th>Name</th>
                        <th>Origin</th>
                        <th>Description</th>
                    </tr>
                    {% for pastry in pastries %}
                        <tr>
                            <td>{{pastry.name}}</td>
                            <td>{{pastry.origin}}</td>
                            <td>{{pastry.description}}</td>
                        </tr>
                    {% endfor %}
                </div>
            </div>
        </div>
    </div>
{% endblock %}
```

What we are interested in here is what's inside the `box-body` div, especially the `{% for pastry in pastries %}` loop. In the controller, we passed the rows from the database, contained in an Eloquent Collection, to the `pastries` key in the render arguments array. Those rows from the database, the same ones displayed in our debug output, are now available in our Twig template as an array. This means we can use [Twig's tags, filters and functions](https://twig.symfony.com/doc/3.x/) to manipulate that array, or any other data passed to the Twig template.

Let's get a closer look at our `for` block:

```html
{% for pastry in pastries %}
    <tr>
        <td>{{pastry.name}}</td>
        <td>{{pastry.origin}}</td>
        <td>{{pastry.description}}</td>
    </tr>
{% endfor %}
```

This is the same as using `foreach` in PHP to loop through all the items available in an array. The `{% for pastry in pastries %}` will loop through `pastries` and create a HTML table row for each item. If you refresh the page, you should now see this in your browser:

![Pastries page](/images/pastries/02.png)
