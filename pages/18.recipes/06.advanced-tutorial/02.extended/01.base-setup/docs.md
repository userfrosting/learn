---
title: Setup
metadata:
    description: Setting up the basic sprinkle
taxonomy:
    category: docs
---

>>>>> You can find the complete source code from this tutorial on [GitHub](https://github.com/userfrosting/pastries).

## The Sprinkle

Let's begin by setting up some basics that we will expand upon throughout the tutorial.

### The route

You should already have a [route file](/routes-and-controllers) at `app/sprinkles/pastries/routes/pastries.php`. We will add two additional groups to the file. First, add the group `/api/pastries` to use later on for routes that will retrieve/modify data from the database.

```php
// These routes will be for any methods that retrieve/modify data from the database.
$app->group('/api/pastries', function () {

})->add('authGuard')->add(new NoCache());
```

Second, add the group `/modals/pastries` which will be used to retrieve our modal forms.

```php
// These routes will be used to store any modals
$app->group('/modals/pastries', function () {

})->add('authGuard');
```

We will add routes to these groups later on in the tutorial. For now, your completed file should look like:

`app/sprinkles/pastries/routes/pastries.php`

```php
<?php

use UserFrosting\Sprinkle\Core\Util\NoCache;

$app->group('/pastries', function () {
    $this->get('', 'UserFrosting\Sprinkle\Pastries\Controller\PastriesController:pageList')
         ->setName('pastries');
})->add('authGuard');

// These routes will be for any methods that retrieve/modify data from the database.
$app->group('/api/pastries', function () {

})->add('authGuard')->add(new NoCache());

// These routes will be used to store any modals
$app->group('/modals/pastries', function () {

})->add('authGuard');

```

### Template directories

We need to create a few more directories for which we will add additional Twig template files to later on in the tutorial. In `pastries/templates/` add the `forms`, `modals`, and `tables` sub directories. Your directory structure should look like:

```
pastries
├──templates
   ├── forms
   ├── modals
   ├── navigation
   ├── pages
   └── tables
```

Each of these directories will contain a file named `pastries.html.twig`. Go ahead and create those now as well.

```
pastries
├──templates
   ├──forms
       └── pastries.html.twig
   ├── modals
       └── pastries.html.twig
   ├── navigation
   ├── pages
   └── tables
       └── pastries.html.twig
```

### The Controller

We need to add a number of `use` statements to the top of our controller file. Go ahead and completely replace the existing with:

```php
use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use UserFrosting\Sprinkle\Core\Controller\SimpleController;
use UserFrosting\Support\Exception\ForbiddenException;
use UserFrosting\Sprinkle\Pastries\Database\Models\Pastries;
use UserFrosting\Sprinkle\Pastries\Sprunje\PastrySprunje;
use UserFrosting\Fortress\RequestDataTransformer;
use UserFrosting\Fortress\RequestSchema;
use UserFrosting\Fortress\ServerSideValidator;
use UserFrosting\Support\Exception\NotFoundException;
use UserFrosting\Fortress\Adapter\JqueryValidationAdapter;
```
