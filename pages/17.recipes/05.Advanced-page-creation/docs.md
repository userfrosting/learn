---
title: Advanced page creation
metadata:
    description: Complete step by step guide to create a complex page
taxonomy:
    category: docs
---

>NOTE: This recipe assumes that the reader already setup his own sprinkle and is familiar with all the base components, including Twig, Routing, Eloquent and Controllers.

This tutorial will guide you to create a fully featured page including custom data model, permission, template and localisation. This is the most complete guide (yet) about creating a custom page for Userfrosting and summarize everything covered by this documentation so far. This guide is indented for advanced users. If there's a word you didn't understood at this point, go back read some more pages!

For this exercice, we'll create a simple page which will displays a list of pastries from a new database table. This page will be accessible at the `/pastries` route, have it's own database table, basic permissions and entry in the sidebar menu. And all of this will be store in it's own sprinkle. At this point, we assume you already have a clean instance of Userfrosting installed and running. Shall we begin?

>>> This recipe was spronsored by @neurone. Get in touch with the UserFrosting team if you want to sponsor own receipe !

## Setting up the basics 
### The sprinkle

First thing to do is to create an empty sprinkle for our code to live in. We'll call this sprinkle `Pastries`. As describes in the [Sprinkles](http://learn.local/sprinkles/first-site) chapter, starts by creating an empty `pastries/` directory under `app/sprinkles`. We now have to create the `composer.json` file for our sprinkle:

`app/sprinkles/pastries/composer.json`
```json
{
    "name": "owlfancy/pastries",
    "type": "userfrosting-sprinkle",
    "description": "Pastries list for UserFrosting.",
    "autoload": {
        "psr-4": {
            "UserFrosting\\Sprinkle\\Pastries\\": "src/"
        }
    }
}
```

Next we need to add our `Pastries` sprinkle to the `sprinkles.json` list and update **Composer** so our new PSR4 definition is picked up. From the command line, run `composer update` at the root of your Userfrosting project.

### The route

We now create the [route](/routes-and-controllers) for the pastries page. Create the empty `routes/` directory inside your sprinkle directory structure and create the `pastries.php` file:

`app/sprinkles/pastries/routes/pastries.php`
```php
<?php

/**
 * Routes for pastries related pages.
 */
$app->group('/pastries', function () {
    $this->get('', 'UserFrosting\Sprinkle\Pastries\Controller\PastriesController:displayPage')
         ->setName('pastries');
})->add('authGuard');
```

We now have a `/pastries` route setup. We also define a group for later use. As you can see, this route have the `pastries` name and will invoke the `authGuard` middleware requiring a user to be logged in to see this page. 

### The controller

Now that we have a route, we need to create the `PastriesController` controller with the `displayPage` method:

`app/sprinkles/pastries/src/Controller/PastriesController.php`
```php
<?php

namespace UserFrosting\Sprinkle\Pastries\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\NotFoundException;
use UserFrosting\Sprinkle\Core\Controller\SimpleController;
use UserFrosting\Support\Exception\ForbiddenException;

class PastriesController extends SimpleController
{
    public function displayPage(Request $request, Response $response, $args)
    {
        return $this->ci->view->render($response, 'pages/pastries.html.twig', [

        ]);
    }
}
``` 

### The template file

Finally, we need to create the template file. We use the same file as the one defined in your controller:

`app/sprinkles/pastries/templates/pages/pastries.html.twig`
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

                </div>
            </div>
        </div>
    </div>
{% endblock %}
```

### Testing your page

You should now be able to manually go to the `/pastries` page in your browser and see the result:

![Pastries page](/images/pastries/01.png)

## Integrating the database

Now that we have a sprinkle with an empty page to work with, it's time to get started with our database integration. Our data structure for the database table will be strait forward: We'll store pastries in a `pastries` table using the following columns:
- id
- name
- description
- origin

### Creating a data model

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

### Creating the db table using a migration

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


### Fetching data from the database

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

### Displaying the data in Twig

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

## Adding the page to the menu

Now that we have our page, it's time to add it to the sidebar menu. Do to so, we will [extend the default menu template](/recipes/extending-template#adding-custom-menu-entries). 

Create a new template file located in `templates/navigation/` and call it `sidebar-menu.html.twig` so it can replace the original sidebar menu template file:

`app/sprinkles/pastries/templates/navigation/sidebar-menu.html.twig`
```html
{% extends "@admin/navigation/sidebar-menu.html.twig" %}

{% block navigation %}
    {{ parent() }}
    <li>
        <a href="/pastries"><i class="fa fa-cutlery fa-fw"></i> <span>List of Pastries</span></a>
    </li>
{% endblock %}
``` 

The key here is the `{% extends "@admin/navigation/sidebar-menu.html.twig" %}` part. While our new file overwrite the same one from the `admin` sprinkle, we tell **our** file to use the one from the admin sprinkle as a base. We ccan then use Twig inheritance to add our new link to the navigation block. See the [Extending Templates and Menus](/recipes/extending-template) receipe for more informations about this.

You should now see the new link in the menu :

![Pastries menu link](/images/pastries/03.png)

## Adding custom permissions

Now it's time to add custom permissions to our page. At this point, we will add two sets of permissions: Permision to see the page and permission to see the origin column.  

Before we continue, you'll have to login as a non root user. If the top navigation bar is red and tells you _you are signed in as the root user_, well, guess what... It's important you use a different user at this point since the root user has all the permissions and you won't be able to see the permissions in action otherwise. 

### Creating the permission in the database

### Adding the permissions to the role in the UI

### Adding permission check in the controller

### Adding permission check in the template

## Adding localisations

While we are using our non root user, it's the perfect opportunity to test localization for our page. We will first need to change the hardcoded English string to localisation keys and afterwards we'll add the French translations. 

Even if you don't need (or want) to translate your page, it's a good idea to use translations key and the translator when you first build your pages. You never know when or why you'll need to add another language in the future and it's harder to add localization later than do it while you're building your page, even if it take a little more time.

First thing is to switch the hardcoded string for the English translation. At this point theres a couple of templates and place to change: The page title, the sidebar menu entry, the box title and the table header.

Let's start with the sidebar menu. Here we change the `List of Pastries` to `{{translate('PASTRIES.LIST')}}` (We'll add the `PASTRIES.LIST` locale key later).


`app/sprinkles/pastries/templates/navigation/sidebar-menu.html.twig`
```html
Find:
<a href="/pastries"><i class="fa fa-cutlery fa-fw"></i> <span>List of Pastries</span></a>

Replace it by: 
<a href="/pastries"><i class="fa fa-cutlery fa-fw"></i> <span>{{translate('PASTRIES.LIST')}}</span></a>
```

Next we change the strings from our page template. We will reuse `PASTRIES.LIST` key to replace `List of Pastries` and change the other like this:
- `Pastries` => `PASTRIES`
- `This page provides a yummy list of pastries` => `PASTRIES.PAGE`
- `Name` => `PASTRIES.NAME`
- `Origin` => `PASTRIES.ORIGIN`
- `Description` => `PASTRIES.DESCRIPTION`

The page template should now look like this
`app/sprinkles/pastries/templates/pages/pastries.html.twig`
```html
{% extends "pages/abstract/dashboard.html.twig" %}

{# Overrides blocks in head of base template #}
{% block page_title %}{{translate('PASTRIES')}}{% endblock %}
{% block page_description %}{{translate('PASTRIES.PAGE')}}{% endblock %}

{% block body_matter %}
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header">
                    <h3 class="box-title"><i class="fa fa-cutlery fa-fw"></i> {{translate('PASTRIES.LIST')}}</h3>
                </div>
                <div class="box-body">
                    <table class="table table-bordered">
                        <tr>
                            <th>{{translate('PASTRIES.NAME')}}</th>
                            <th>{{translate('PASTRIES.ORIGIN')}}</th>
                            <th>{{translate('PASTRIES.DESCRIPTION')}}</th>
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

Next we need to add the English association for those key. We now create a new `locale/en_US` directory and a `pastries.php` file to old our new translations:

`app/sprinkles/pastries/locale/en_US/pastries.php`
```php
<?php

return [
    "PASTRIES" => [
        "@TRANSLATION" => "Pastries",
        "PAGE" => "This page provides a yummy list of pastries",
        "LIST" => "List of Pastries",
        "NAME" => "Name",
        "ORIGIN" => "Origin",
        "DESCRIPTION" => "Description"
    ]
];
```

Now it's time to create the French translation file. To avoid confusion, the French translation should be on the same file (`pastries.php`), but place it in a `fr_FR` directory:

`app/sprinkles/pastries/locale/fr_FR/pastries.php`
```php
<?php

return [
    "PASTRIES" => [
        "@TRANSLATION" => "Pâtisseries",
        "PAGE" => "Cette page propose une appétissante liste de pâtisseries",
        "LIST" => "Liste des pâtisseries",
        "NAME" => "Nom",
        "ORIGIN" => "Origine",
        "DESCRIPTION" => "Description"
    ]
];
```

At this point, you can go in your user prefrences and change the site language to French. Once this is done, go back to the pastries page and _voilà, tout en français_ !

![Pastries in French](/images/pastries/04.png)

## Going further

- [Use the Sprunjing to paginate the results](/database/data-sprunjing)
- [use ufForm to create, edit and delete entries](/client-side-code/components/forms)
- [Create some tests](/advanced/automated-tests)
- [Create a custom Bakery command](/advanced/custom-cli)

## Source

You can find the finalized code from this tutorial on GitHub.