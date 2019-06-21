---
title: Adding a Sprunje
metadata:
    description: Sort, paginate, and search/filter data using a Sprunje
taxonomy:
    category: docs
---

## The Sprunje

We will use a Sprunje to sort, paginate and filter data after adding ufTable later on in this tutorial. Sprinkle Sprunje classes are stored inside the `src/Sprunje` directory. Create a new file in the directory called `PastrySprunje.php`.

`app/sprinkles/pastries/src/Sprunje/PastriesSprunje.php`
```php
<?php

namespace UserFrosting\Sprinkle\Pastries\Sprunje;

use UserFrosting\Sprinkle\Core\Sprunje\Sprunje;
use UserFrosting\Sprinkle\Pastries\Database\Models\Pastries;

class PastrySprunje extends Sprunje
{
    protected $name = 'pastries';

    protected $sortable = [
    'name',
    'description',
    'origin'
    ];

    protected $filterable = [
    'name',
    'description',
    'origin'
    ];

    protected function baseQuery()
    {
        $instance = new Pastries();

        return $instance->newQuery();
    }
}
```

### The route

We will add a route to allow us to retrieve data using our Sprunje. We will use Java Script to send requests to this route, allowing the table to be changed dynamically without requiring the page to be refreshed. The route will be inside the `/api/pastries` group:

```php
// These routes will be for any methods that retrieve/modify data from the database.
$app->group('/api/pastries', function () {

    $this->get('', 'UserFrosting\Sprinkle\Pastries\Controller\PastriesController:getList');

})->add('authGuard')->add(new NoCache());
```

### The controller

There is now a route for `getList` so we should add that function to our controller class. Add the following code block to the `PastriesController.php` file

```php
public function getList(Request $request, Response $response, $args)
{
    // GET parameters
    $params = $request->getQueryParams();

    /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
    $authorizer = $this->ci->authorizer;

    /** @var UserFrosting\Sprinkle\Account\Database\Models\User $currentUser */
    $currentUser = $this->ci->currentUser;

    // Access-controlled page
    if (!$authorizer->checkAccess($currentUser, 'see_pastries')) {
        throw new ForbiddenException();
    }
    /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
    $classMapper = $this->ci->classMapper;

    $sprunje = new PastrySprunje($classMapper, $params);

    // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
    // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
    return $sprunje->toResponse($response);
}
```

At this point you should be able to use a web browser to navigate to the URI  (HOSTNAME/api/pastries) and get a json response that has the default pastries.

![Sprunje Response](/images/pastries/sprunjeresponse.png)
