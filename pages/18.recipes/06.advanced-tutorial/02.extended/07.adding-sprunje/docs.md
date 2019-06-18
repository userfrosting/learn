---
title: Adding a Sprunje
metadata:
    description: Sort, paginate, and search/filter data using a Sprunje
taxonomy:
    category: docs
---

### The Sprunje file

We will use a Sprunje to sort, paginate and filter data after adding ufTable later on in this tutorial.

Sprinkle Sprunje classes are stored inside the `src/Sprunje` directory. Create a new file in the directory called `PastriesSprunje.php`.

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

We will need to add another route to the route file that will be called from our JavaScript code. This allows the table to be refreshed and changed dynamically without requiring the page to be refreshed. The route will be inside the `/api/pastries` group:

```php
// These routes will be for any methods that retrieve/modify data from the database.
$app->group('/api/pastries', function () {

    $this->get('', 'UserFrosting\Sprinkle\Pastries\Controller\PastriesController:getList');

})->add('authGuard')->add(new NoCache());
```

### Adding to the controller

Now that we have a route for `getList` we need to actually add that function to our controller. 

```
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
}```
