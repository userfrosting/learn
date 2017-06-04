---
title: Data Sprunjing
metadata:
    description: Sprunjing is our term for the common task of filtering, sorting, and paginating data from the database.  Data can be sprunjed for JSON or CSV output.
taxonomy:
    category: docs
---

Once your users log in, you'll probably want to have them interact with your data models in some way.  One very common UI pattern is to present a list or table of data to a user, and allow them to **sort**, **paginate**, and **search/filter** the data.  For example, the [tablesorter](https://mottie.github.io/tablesorter/docs/) and [select2](http://select2.github.io/) plugins follow this pattern.

In cases where you could potentially have thousands of retrievable rows, it would be unwise to try to send them _all_ to the client and then use Javascript to handle searching and sorting.  This would make response times much slower, and place an excessive burden on the client's browser - not a good user experience!

Instead it's best to perform sorting, filtering, and pagination (which we will collectively refer to as **constraints**) on the server, directly in your SQL queries.  The client passes in a set of parameters through your API endpoints to indicate how the data should be constrained.  For example:

`GET http://localhost/userfrosting/public/api/users?size=5&page=0&sorts%5Bname%5D=asc`

This tells the server that we want a list of users sorted by their `name`, in ascending order (`sorts[name]=asc`).  We then want them chunked into blocks of 5 results at a time (`size=5`), and for this request we want the first chunk (`page=0`).

Building queries that can handle all of these request parameters correctly, for every one of your endpoints, can be surprisingly tricky and very tedious.  This is why UserFrosting introduces the **Sprunjer**, allowing you to easily define rules for how clients can retrieve constrained results from your database.

## Sprunje parameters

Every Sprunje can accept the following parameters.  Typically, they are passed in as a query string in a `GET` request, and then passed as the second argument in your Sprunje's constructor.

- `sorts`: an associative array of field names mapped to sort directions.  Sort direction can be either `asc` or `desc`.
- `filters`: an associative array of field names mapped to queries.  For example, `name: Attenb` will search for names that contain "Attenb."
- `size`: either an integer specifying the maximum number of results to return, or `all` to retrieve all results.
- `page`: an integer specifying the page (chunk) number to retrieve, when `size` is specified.  For example, to retrieve the first chunk, set `page` to 0.
- `format` => the format in which data should be returned.  Can be either `json` or `csv`.

## Defining a Sprunje

By implementing a custom `Sprunje` class, you can pass parameters directly from the client request and they will be used to safely construct the appropriate query.

A custom Sprunje is simply a class that extends the base `UserFrosting\Sprinkles\Core\Sprunje\Sprunje` class.  At the minimum you must define the `name` property and implement the `baseQuery` class, which specifies the Eloquent query to be performed before any additional constraints are applied.  By convention, you should place your Sprunje classes in `src/Sprunje/` in your Sprinkle.

**OwlSprunje.php**

```php
<?php
namespace UserFrosting\Sprinkle\Site\Sprunje;

use UserFrosting\Sprinkle\Core\Facades\Debug;
use UserFrosting\Sprinkle\Core\Sprunje\Sprunje;

class OwlSprunje extends Sprunje
{
    protected $name = 'owls';

    /**
     * Set the initial query used by your Sprunje.
     */
    protected function baseQuery()
    {
        $query = new Owl();

        // Alternatively, if you have defined a class mapping, you can use the classMapper:
        // $query = $this->classMapper->createInstance('owl');

        return $query;
    }
}
```   

`baseQuery` should return an instance of the Eloquent `Builder` class.  If you want your Sprunje to automatically join other tables or load related objects, `baseQuery` is a good place to do this.

### Sorts and filters

By default, `Sprunje` will try to match the field names in the `sorts` and `filters` query parameters to column names in your Eloquent query.  Sorts are automatically transformed into `orderBy('fieldName', 'sortDirection')` clauses, and filters are transformed into `where('fieldName', 'like', '%$value%')` clauses.

However, before you can sort or filter on a particular column name, you must add it to the `$sortable` and `$filterable` members of your Sprunje class first:

```php
<?php
namespace UserFrosting\Sprinkle\Site\Sprunje;

use UserFrosting\Sprinkle\Core\Facades\Debug;
use UserFrosting\Sprinkle\Core\Sprunje\Sprunje;

class OwlSprunje extends Sprunje
{
    protected $sortable = [
        'name',
        'species'
    ];

    protected $filterable = [
        'name',
        'species'
    ];
    ...
} 
```

This whitelisting is done to prevent consumers of your API from sorting/filtering on arbitrary columns, which could reveal potentially sensitive information.  For example, even if you don't return the actual **value** of a column in your result set (e.g., `is_admin`), one could still determine which users are admins, for example, by filtering based on `is_admin=1`.  Whitelisting ensures that this cannot happen.

>>> Both `sorts` and `filters` can accept multiple values separated by `||`, which will cause your Sprunje to return rows that match *any* of the values.

## Using your Sprunje

With your Sprunje defined, you can use the `toResponse` method in your controller to automatically append the query results to the response:

```php
    /**
     * Returns a list of Owls
     *
     * Generates a list of owls, optionally paginated, sorted and/or filtered.
     * This page requires authentication.
     * Request type: GET
     */
    public function getList($request, $response, $args)
    {
        // GET parameters
        $params = $request->getQueryParams();

        /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
        $authorizer = $this->ci->authorizer;

        /** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
        $currentUser = $this->ci->currentUser;

        // Access-controlled page
        if (!$authorizer->checkAccess($currentUser, 'uri_owls')) {
            throw new ForbiddenException();
        }

        /** @var UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        $sprunje = new OwlSprunje($classMapper, $params);

        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        return $sprunje->toResponse($response);
    }
```

Supposing that we make a request like `GET http://localhost/userfrosting/public/api/owls?size=5&page=0&sorts%5Bgenus%5D=asc`, we might get a response like:

```json
{
    "count": 5,
    "count_filtered": 5,
    "rows": [
        {
            "id": 11,
            "name": "slasher",
            "genus": "Bubo"
        },
        {
            "id": 8,
            "name": "shredder",
            "genus": "Megascops"
        },
        {
            "id": 3,
            "name": "harold",
            "genus": "Phodilus"
        },
        {
            "id": 1,
            "name": "fluffers",
            "genus": "Tyto"
        },
        {
            "id": 2,
            "name": "kevin",
            "genus": "Tyto"
        }
    ]
}
```

Notice that when retrieving data in JSON format (default), the response is an array containing three keys: `count`, `count_filtered`, and `rows`.  `count` contains the total number of results before any filters or pagination were applied.  `count_filtered` contains the number of results found after filtering, but _before_ pagination was applied.  Finally, `rows` contains the array of results itself, with all constraints applied.

## Custom sorts and filters

The default sorting/filtering capabilities are great, but in some cases you may want to customize the behavior for certain columns, or even define constraints that don't map directly to a column name.

To do this, you can define custom methods in your Sprunje:

```php
    /**
     * Filter LIKE the genus OR species
     *
     * @param Builder $query
     * @param mixed $value
     * @return Builder
     */
    protected function filterScientificName($query, $value)
    {
        return $query->like('genus', $value)
                     ->orLike('species', $value);
    }
```

The method name should consist of the field name (converted to StudlyCase), prefixed with `filter` or `sort`.

Thus, in this example a request parameter of `filters[scientific_name]=mega` will invoke the `filterScientificName` method in your Sprunje.

## Custom data transformations

Sometimes, you will want to transform the dataset after executing the query, but before returning the results to the client.  For example, you might want to remove the `password` field from a list of users.  To have your Sprunje do this every time it is invoked, simply implement the `applyTransformations` method in your Sprunje:

```php
// In UserSprunje.php

protected function applyTransformations($collection)
{
    // Exclude password field from results
    $collection->transform(function ($item, $key) {
        unset($item['password']);
        return $item;
    });

    return $collection;
}
```

## Extending a Sprunje query

You might want to further modify your Sprunje's base query in certain endpoints.  For example, you might want to retrieve the owner of each Owl in your result set, but only in your `/api/owls` endpoint.  In this case, you can modify your Sprunje by passing a callback to the `extendQuery` method:

```php
// In OwlController::getList method

$sprunje = new OwlSprunje($classMapper, $params);
$sprunje->extendQuery(function ($query) {
    return $query->with('owner');
});
```
