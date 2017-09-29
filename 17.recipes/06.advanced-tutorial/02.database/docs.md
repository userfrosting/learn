---
title: Integrating the database
metadata:
    description: Adding database integration to our page
taxonomy:
    category: docs
---

Now that we have a sprinkle with an empty page to work with, it's time to get started with our database integration. Our data structure for the database table will be strait forward: We'll store pastries in a `pastries` table using the following columns:
- id
- name
- description
- origin

## Creating a data model

First, we create the [data model](database/overview#data-models). In this model, we define the table name, list the column we want to be fillable and enable the automatic timestamp creation.

```php
<?php

namespace UserFrosting\Sprinkle\Pastries\Database\Models;

use Illuminate\Database\Capsule\Manager as Capsule;
use UserFrosting\Sprinkle\Core\Database\Models\Model;

class Pastries extends Model
{
    /**
     * @var string The name of the table for the current model.
     */
    protected $table = "pastries";

    protected $fillable = [
        "name",
        "description",
        "origin"
    ];

    /**
     * @var bool Enable timestamps for this class.
     */
    public $timestamps = true;
}
```

## Creating the db table using a migration

Next we create a migration class. This migration will create the database table for us. Migrations are located in `src/Database/Migrations`. Since this is our first migrations, we'll add them to the `V100` sub directory. Finally, since that migration purpose is to create the `Pastries` table, we'll name it `PastriesTable`.

`app/sprinkles/pastries/src/Database/Migrations/V100/PastriesTable.php`
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

As described in the [Migration](/database/migrations) chapter, the `up` method contains the instructions to create the new table while the `down` method contains the instruction to undo the changes made by the `up` method, in this case removing the `pastries` table.

Next we'll populate our newly created table with default rows. To do this, we'll create a second migration. While this could be done in the same migration as the table creation, it is recommended to separate you migrations (and gives us an excuse to show migration dependencies). We call this second migration `DefaultPastries`:

`app/sprinkles/pastries/src/Database/Migrations/V100/DefaultPastries.php`
```php
<?php

namespace UserFrosting\Sprinkle\Pastries\Database\Migrations\v100;

use UserFrosting\System\Bakery\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use UserFrosting\Sprinkle\Pastries\Database\Models\Pastries;

class DefaultPastries extends Migration
{
    /**
     * {@inheritDoc}
     */
    public $dependencies = [
        '\UserFrosting\Sprinkle\Pastries\Database\Migrations\V100\PastriesTable'
    ];

    /**
     * {@inheritDoc}
     */
    public function up()
    {
        foreach ($this->pastries() as $pastry) {
            $pastry = new Pastries($pastry);
            $pastry->save();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function down()
    {
        foreach ($this->pastries() as $pastry) {
            $pastry = Pastries::where($pastry)->first();
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

The `$dependencies` array here is important. By referencing our `PastriesTable` here, this will make sure the migrator doesn't try to insert rows in the `pastries` table before said table is created.

We are now ready to run our migrations. From the command line, use the [Bakery migrate command](/cli/commands#migrate) to run the migration up : `php bakery migrate`. You should now see the newly created table with the default rows if you look at the database using _phpMyAdmin_ for instance.


## Fetching data from the database

Now it's time to go back to our controller and fetch the data from our new database table. First thing we need to do is tell the controller to use the model we created. To do so, we add the `Pastries` model fully qualified namespace to the controller list of usable class. Right under `use UserFrosting\Support\Exception\ForbiddenException;`, add:

```php
use UserFrosting\Sprinkle\Pastries\Database\Models\Pastries;
```

Now that our controller can see our model, it's time to interact with it and select all the available rows:

```php
$pastries = Pastries::all();
```

>>>>> Fetching all the available rows is not an ideal solution since it can involves an infinite numbers of rows. This can cluter the UI, providing poor user experience, and can also result in poor performance (slow page generation, high server ressource usage). It is recommended to use **Sprunging** in this situation.

The `$pastries` variable should now contains an [eloquent collection]() of pastries. At this point, it's a good idea to use **Debugging** to make sure everything works as it should. We'll use the `Debug` facade to do so. Start by adding the facade class to the usage delacration of your class:

```php
use UserFrosting\Sprinkle\Core\Facades\Debug;
```

...and pass the `$pastries` variable to the debugger (right under the `$pastries = ...` line) :

```php 
Debug::debug($pastries);
```


This file should contains something similar to this:
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

As you can see, it successfully listed our three default pastries along with their description and origin. You can now comment out the Debug line as we don't required it anymore, but might need it later.

The only thing left to to is to send the collection to Twig. To do so, we simply add the `$pastries` variable to render arguments:

```php
return $this->ci->view->render($response, 'pages/pastries.html.twig', [
    'pastries' => $pastries
]);
```

Our controller should now look like this:

```php
<?php

namespace UserFrosting\Sprinkle\Pastries\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\NotFoundException;
use UserFrosting\Sprinkle\Core\Controller\SimpleController;
use UserFrosting\Support\Exception\ForbiddenException;
use UserFrosting\Sprinkle\Pastries\Database\Models\Pastries;
use UserFrosting\Sprinkle\Core\Facades\Debug;

class PastriesController extends SimpleController
{
    public function displayPage(Request $request, Response $response, $args)
    {
        $pastries = Pastries::all();
        
        //Debug::debug($pastries);

        return $this->ci->view->render($response, 'pages/pastries.html.twig', [
            'pastries' => $pastries
        ]);
    }
}
```

## Displaying the data in Twig

Back in our Twig templating file, we can now. Let's look at the complete code and dive in afterwards:

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

What we are intered in here is what's inside the `box-body` div, especially the `{% for pastry in pastries %}` loop. In the controller, we passed the rows from the database, contained in an eloquent collection, to the `pastries` key in the render arguments array. Those rows from the database, the same one displayed in our debug output, are now available in our Twig template as an array. This means we can use [Twig's tags, filters and functions](https://twig.symfony.com/doc/2.x/) to manipulate that array, or any data passed to the Twig template. Let's get a closer look this [for](https://twig.symfony.com/doc/2.x/tags/for.html) block:

```html
{% for pastry in pastries %}
    <tr>
        <td>{{pastry.name}}</td>
        <td>{{pastry.origin}}</td>
        <td>{{pastry.description}}</td>
    </tr>
{% endfor %}
```
This is the same as using `foreach` in PHP to loop all the items available in an array. The `{% for pastry in pastries %}` will loop each `pastries` and create a HTML table row for each one. If you refresh the page, you should now see this in your browser :

![Pastries page](/images/pastries/02.png)