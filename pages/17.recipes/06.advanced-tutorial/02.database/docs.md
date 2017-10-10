---
title: Integrating the database
metadata:
    description: Customizing our UserFrosting controller to retrieve dynamic data from the database and display it in our page.
taxonomy:
    category: docs
---

Now that we have a sprinkle with an empty page to work with, it's time to get started with our database integration. Our data structures for the database table will be straightforward. We'll store pastries in a `pastries` table using the following columns:

- `id`
- `name`
- `description`
- `origin`

## Creating a data model

First, we create the [data model](database/overview#data-models). In this model, we define the table name, list the columns we want to be [mass assignable](https://laravel.com/docs/5.4/eloquent#mass-assignment) and enable automatic timestamp creation.

`app/sprinkles/pastries/src/Database/Models/Pastry.php`
```php
<?php

namespace UserFrosting\Sprinkle\Pastries\Database\Models;

use Illuminate\Database\Capsule\Manager as Capsule;
use UserFrosting\Sprinkle\Core\Database\Models\Model;

class Pastry extends Model
{
    /**
     * @var string The name of the table for the current model.
     */
    protected $table = 'pastries';

    protected $fillable = [
        'name',
        'description',
        'origin'
    ];

    /**
     * @var bool Enable timestamps for this class.
     */
    public $timestamps = true;
}
```

## Creating the database table using a migration

Next we create a migration class. This migration will create the database table for us. Migrations are located in `src/Database/Migrations`. Since this is the first version of our Sprinkle, we'll add them to the `v100` sub directory. Finally, since the migration's purpose is to create the `pastries` table, we'll name the migration class `PastriesTable`.

`app/sprinkles/pastries/src/Database/Migrations/v100/PastriesTable.php`
```php
<?php

namespace UserFrosting\Sprinkle\Pastries\Database\Migrations\v100;

use UserFrosting\System\Bakery\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

class PastriesTable extends Migration
{
    /**
     * {@inheritDoc}
     */
    public function up()
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
     * {@inheritDoc}
     */
    public function down()
    {
        $this->schema->drop('pastries');
    }
}
```

As described in the [Migration](/database/migrations) chapter, the `up` method contains the instructions to create the new table while the `down` method contains the instructions to undo the changes made by the `up` method - in this case, removing the `pastries` table.

Next we'll populate our newly created table with some default data. To do this, we'll create a second migration. While this could be done in the same migration as the table creation, it is recommended to separate your migrations (this also gives us an excuse to demonstrate the concept of [migration dependencies](/database/migrations#dependencies)). We call this second migration `DefaultPastries`:

`app/sprinkles/pastries/src/Database/Migrations/v00/DefaultPastries.php`
```php
<?php

namespace UserFrosting\Sprinkle\Pastries\Database\Migrations\v100;

use UserFrosting\System\Bakery\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use UserFrosting\Sprinkle\Pastries\Database\Models\Pastry;

class DefaultPastries extends Migration
{
    /**
     * {@inheritDoc}
     */
    public $dependencies = [
        '\UserFrosting\Sprinkle\Pastries\Database\Migrations\v100\PastriesTable'
    ];

    /**
     * {@inheritDoc}
     */
    public function up()
    {
        foreach ($this->pastries() as $pastry) {
            $pastry = new Pastry($pastry);
            $pastry->save();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function down()
    {
        foreach ($this->pastries() as $pastry) {
            $pastry = Pastry::where($pastry)->first();
            $pastry->delete();
        }
    }

    protected function pastries()
    {
        return [
            [
                'name' => 'Apple strudel',
                'description' => 'Sliced apples and other fruit are wrapped and cooked in layers of filo pastry. The earliest known recipe is in Vienna, but several countries in central and eastern Europe claim this dish.',
                'origin' => 'Central Europe'
            ],
            [
                'name' => 'Pain au chocolat',
                'description' => '"Chocolate bread", also called a chocolatine in southern France and in French Canada, is a French pastry consisting of a cuboid-shaped piece of yeast-leavened laminated dough, similar to puff pastry, with one or two pieces of chocolate in the centre.',
                'origin' => 'France'
            ],
            [
                'name' => 'Baklava',
                'description' => 'A Turkish pastry that is rich and sweet, made of layers of filo pastry filled with chopped nuts and sweetened with syrup or honey.',
                'origin' => 'Turkish/Greek'
            ]
        ];
    }
}
```

The `$dependencies` array here is important. By referencing our `PastriesTable` here, this ensures that the migrator doesn't try to insert rows in the `pastries` table before it has been created.

Also notice how we are using a new method called `pastries` which returns an array of pastries. This helps us remove redundancy in our code since the `up` and `down` methods can both use the same list. If you were to make a typo in one of the default entries, this means we'd only have to correct it in one place.

We are now ready to run our migrations. From the command line, use the [Bakery migrate command](/cli/commands#migrate) to run the migration up: `php bakery migrate`. You should now see the newly created table with the default rows in your database (using _phpMyAdmin_ or the database CLI, for instance).

## Fetching data from the database

Now it's time to go back to our controller and fetch the data from our new database table. The first thing we need to do is tell the controller to use the model we created. To do so, we add the fully qualified namespace of the `Pastry` model to the controller's list of [namespace aliases](http://php.net/manual/en/language.namespaces.importing.php). Right under `use UserFrosting\Support\Exception\ForbiddenException;`, add:

```php
use UserFrosting\Sprinkle\Pastries\Database\Models\Pastry;
```

Now that we've defined this convenient alias for our model, it's time to interact with it and select all the available rows:

```php
$pastries = Pastry::all();
```

>>>>> Fetching all the available rows is not an ideal solution since in production, it can involve an arbitrarily large number of rows. This can clutter the UI, providing poor user experience, and can also result in poor performance (slow page generation, high server resource usage). It is recommended to use AJAX and [**Sprunging**](/database/data-sprunjing) to display paginated data in this situation.

The `$pastries` variable should now contains an [Eloquent Collection](https://laravel.com/docs/5.4/eloquent-collections) of `Pastry` objects. At this point, it's a good idea to use **Debugging** to make sure everything works as it should. We'll use the `Debug` facade to do so. Start by adding the facade class to the usage declaration of your class:

```php
use UserFrosting\Sprinkle\Core\Facades\Debug;
```

...and pass the `$pastries` variable to the debugger (right under the `$pastries = ...` line) :

```php 
Debug::debug($pastries);
```


This file should contain something similar to this:
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

As you can see, it successfully listed our three default pastries along with their description and origin. You can now comment out the `Debug` line as we don't require it anymore, but might need it later.

The only thing left to do is to send the collection to Twig. To do so, we simply add the `$pastries` variable to render arguments:

```php
return $this->ci->view->render($response, 'pages/pastries.html.twig', [
    'pastries' => $pastries
]);
```

Our controller should now look like this:

`app/sprinkles/pastries/src/Controller/PastriesController.php`
```php
<?php

namespace UserFrosting\Sprinkle\Pastries\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\NotFoundException;
use UserFrosting\Sprinkle\Core\Controller\SimpleController;
use UserFrosting\Support\Exception\ForbiddenException;
use UserFrosting\Sprinkle\Pastries\Database\Models\Pastry;
use UserFrosting\Sprinkle\Core\Facades\Debug;

class PastriesController extends SimpleController
{
    public function displayPage(Request $request, Response $response, $args)
    {
        $pastries = Pastry::all();
        
        //Debug::debug($pastries);

        return $this->ci->view->render($response, 'pages/pastries.html.twig', [
            'pastries' => $pastries
        ]);
    }
}
```

## Displaying the data in Twig

Back in our Twig templating file, we'll use Twig's [`for`](https://twig.symfony.com/doc/2.x/tags/for.html) construct to loop through the `pastries` variable and render a new HTML table row for each pastry:

```html
{% extends "pages/abstract/dashboard.html.twig" %}

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
                    <table class="table table-bordered">
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
                    </table>
                </div>
            </div>
        </div>
    </div>
{% endblock %}
```

What we are interested in here is what's inside the `box-body` div, especially the `{% for pastry in pastries %}` loop. In the controller, we passed the rows from the database, contained in an Eloquent Collection, to the `pastries` key in the render arguments array. Those rows from the database, the same ones displayed in our debug output, are now available in our Twig template as an array. This means we can use [Twig's tags, filters and functions](https://twig.symfony.com/doc/2.x/) to manipulate that array, or any other data passed to the Twig template.

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
