---
title: Data Sprunjing
metadata:
    description: Sprunjing is our term for the common task of filtering, sorting, and paginating data from the database.  Data can be sprunjed for JSON or CSV output.
taxonomy:
    category: docs
---

Once your users log in, you'll probably want to have them interact with your data models in some way. One very common UI pattern is to present a list or table of data to a user, and allow them to **sort**, **paginate**, and **search/filter** the data. For example, the [tablesorter](https://mottie.github.io/tablesorter/docs/) and [select2](http://select2.github.io/) plugins follow this pattern.

In cases where you could potentially have thousands of retrievable rows, it would be unwise to try to send them _all_ to the client and then use Javascript to handle searching and sorting. This would make response times much slower, and place an excessive burden on the client's browser - not a good user experience!

Instead it's best to perform sorting, filtering, and pagination (which we will collectively refer to as **constraints**) on the server, directly in your SQL queries. The client passes in a set of parameters through your API endpoints to indicate how the data should be constrained. For example:

```bash
GET http://example.com/api/users?size=5&page=0&sorts%5Bname%5D=asc
```

This tells the server that we want a list of users sorted by their `name`, in ascending order (`sorts[name]=asc`). We then want them chunked into blocks of 5 results at a time (`size=5`), and for this request we want the first chunk (`page=0`).

Building queries that can handle all of these request parameters correctly, for every one of your endpoints, can be surprisingly tricky and very tedious. This is why UserFrosting introduces the **Sprunjer**, allowing you to easily define rules for how clients can retrieve constrained results from your database.

## Sprunje parameters

Every Sprunje can accept the following parameters. Typically, they are passed in as a query string in a `GET` request, and then passed as the second argument in your Sprunje's constructor.

- `sorts`: an associative array of field names mapped to sort directions. Sort direction can be either `asc` or `desc`.
- `filters`: an associative array of field names mapped to queries. For example, `name: Attenb` will search for names that contain "Attenb."
- `size`: either an integer specifying the maximum number of results to return, or `all` to retrieve all results.
- `page`: an integer specifying the page (chunk) number to retrieve, when `size` is specified. For example, to retrieve the first chunk, set `page` to 0.
- `format` => the format in which data should be returned. Can be either `json` or `csv`.

## Defining a Sprunje

By implementing a custom `Sprunje` class, you can pass parameters directly from the client request and they will be used to safely construct the appropriate query.

A custom Sprunje is simply a class that extends the base `UserFrosting\Sprinkle\Core\Sprunje\Sprunje` class. At the minimum you must implement the `baseQuery` class, which specifies the Eloquent query to be performed before any additional constraints are applied. By convention, you should place your Sprunje classes in `src/Sprunje/` in your Sprinkle.

**OwlSprunje.php**

```php
<?php
namespace UserFrosting\Sprinkle\MySprinkle\Sprunje;

use UserFrosting\Sprinkle\Core\Sprunje\Sprunje;
use UserFrosting\Sprinkle\Site\Database\Models\Owl;

class OwlSprunje extends Sprunje
{
    /**
     * Set the initial query used by your Sprunje.
     */
    protected function baseQuery()
    {
        // TIP: The Owl class can also be injected in the constructor
        $instance = new Owl();

        return $instance->newQuery();
    }
}
```

`baseQuery` should return an instance of a "queriable" class. Queriable classes include :
- `Illuminate\Database\Eloquent\Builder`
- `Illuminate\Database\Query\Builder`
- `Illuminate\Database\Eloquent\Relations\Relation`
- `Illuminate\Database\Eloquent\Model`
- and child classes of these.

If you want your Sprunje to automatically join other tables or load related objects, `baseQuery` is a good place to do this.

### Sorts and filters

By default, `Sprunje` will try to match the field names in the `sorts` and `filters` query parameters to column names in your Eloquent query. Sorts are automatically transformed into `orderBy('fieldName', 'sortDirection')` clauses, and filters are transformed into `where('fieldName', 'like', '%$value%')` clauses.

However, before you can sort or filter on a particular column name, you must add it to the `$sortable` and `$filterable` members of your Sprunje class first:

```php
<?php
namespace UserFrosting\Sprinkle\MySprinkle\Sprunje;

use UserFrosting\Sprinkle\Core\Sprunje\Sprunje;

class OwlSprunje extends Sprunje
{
    protected array $sortable = [
        'name',
        'species'
    ];

    protected array $filterable = [
        'name',
        'species'
    ];
    
    // ...
}
```

This whitelisting is done to prevent consumers of your API from sorting/filtering on arbitrary columns, which could reveal potentially sensitive information. For example, even if you don't return the actual **value** of a column in your result set (e.g., `is_admin`), one could still determine which users are admins, for example, by filtering based on `is_admin=1`. Whitelisting ensures that this cannot happen.

[notice]Both `sorts` and `filters` can accept multiple values separated by `||`, which will cause your Sprunje to return rows that match *any* of the values.[/notice]

## Using your Sprunje

With your Sprunje defined, you can use the `toResponse` method in your controller to automatically append the query results to the response. `toResponse` will handle all the necessary code to return JSON encoded data.

```php
<?php

namespace UserFrosting\Sprinkle\MySprinkle\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Sprinkle\Account\Exceptions\ForbiddenException;
use UserFrosting\Sprinkle\MySprinkle\Sprunje\OwlSprunje;

class OwlsSprunjeAction
{
    /** 
     * Inject Sprunje and other services using DI
     */
    public function __construct(
        protected Authenticator $authenticator,
        protected OwlSprunje $sprunje,
    ) {
    }

    /**
     * Returns a list of Owls
     *
     * Generates a list of owls, optionally paginated, sorted and/or filtered.
     * This page requires authentication.
     * Request type: GET
     */
    public function __invoke(Request $request, Response $response): Response
    {
        // Access-controlled page
        if (!$this->authenticator->checkAccess('uri_owls')) {
            throw new ForbiddenException();
        }

        // GET parameters and pass to Sprunje
        $params = $request->getQueryParams();
        $this->sprunje->setOptions($params);

        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        return $this->sprunje->toResponse($response);
    }
```

Supposing that we make a request like `GET http://example.com/api/owls?size=5&page=0&sorts%5Bgenus%5D=asc`, we might get a response like:

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

Notice that when retrieving data in JSON format (default), the response is an array containing three keys: `count`, `count_filtered`, and `rows`. `count` contains the total number of results before any filters or pagination were applied. `count_filtered` contains the number of results found after filtering, but _before_ pagination was applied. Finally, `rows` contains the array of results itself, with all constraints applied.

## Custom sorts and filters

The default sorting/filtering capabilities are great, but in some cases you may want to customize the behavior for certain columns, or even define constraints that don't map directly to a column name.

To do this, you can define custom methods in your Sprunje:

```php
    /**
     * Filter LIKE the genus OR species
     *
     * @param EloquentBuilder|QueryBuilder|Relation $query
     * @param string                                $value
     * 
     * @return static
     */
    protected function filterScientificName($query, string $value): static
    {
        $query->like('genus', $value)
                ->orLike('species', $value);

        return $this;
    }
```

The method name should consist of the field name (converted to StudlyCase), prefixed with `filter` or `sort`.

Thus, in this example a request parameter of `filters[scientific_name]=mega` will invoke the `filterScientificName` method in your Sprunje.

## Custom data transformations

Sometimes, you will want to transform the dataset after executing the query, but before returning the results to the client. For example, you might want to remove the `password` field from a list of users. To have your Sprunje do this every time it is invoked, simply implement the `applyTransformations` method in your Sprunje:

```php
/**
 * Set any transformations you wish to apply to the collection, after the query is executed.
 * This method is meant to be customized in child class.
 *
 * @param \Illuminate\Support\Collection $collection
 *
 * @return \Illuminate\Support\Collection
 */
protected function applyTransformations(Collection $collection): Collection
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

You might want to further modify your Sprunje's base query in certain endpoints. For example, you might want to retrieve the owner of each Owl in your result set, but only in your `/api/owls` endpoint. In this case, you can modify your Sprunje by passing a callback to the `extendQuery` method:

```php
// In OwlsSprunjeAction
$this->sprunje->extendQuery(function ($query) {
    return $query->with('owner');
});
```

## Sprunje lists

Sprunjes can also be used to enumerate a unique list of values for fields in the target model. This is useful, for example, when you want to present a dropdown list of values for the user to choose among. To make a field listable, simply add it to a `listable` member variable in your Sprunje class:

```php
class OwlSprunje extends Sprunje
{
    protected $listable = [
        'species'
    ];

    // ...
}
```

An array mapping each listable field to a list of possible values can be obtained by calling the `getListable` method on your Sprunje. For each listable field, by default `getListable` will look for a corresponding table column of the same name, and generate sub-arrays containing `value` and `text` fields, each of which will contain the given value:

```json
"listable": {
    "species": [
        {
            "value": "Athene",
            "text": "Athene"
        },
        {
            "value": "Bubo",
            "text": "Bubo"
        },
        {
            "value": "Glaucidium",
            "text": "Glaucidium"
        },
        {
            "value": "Megascops",
            "text": "Megascops"
        },
        {
            "value": "Tyto",
            "text": "Tyto"
        }
    ]
}
```

[notice=warning]It is recommended to use strings for both the `value` and `text` for compatibility purposes with the TableSorter plugin. You can cast your value to a string by wrapping it in double quotation marks or with `(string)` prefix of the value. See [this thread](https://github.com/userfrosting/UserFrosting/issues/966#issuecomment-483245033) for an example.[/notice]

Of course you can override the default listing behavior for a field by defining a custom method. This method must consist of the field name (converted to StudlyCase) prefixed with `list`:

```php
/**
 * Return a list of possible user statuses.
 * Uses the Translator service, injected in the constructor.
 *
 * @return array{value: string, text: string}[]
 */
protected function listStatus(): array
{
    return [
        [
            'value' => 'active',
            'text' => $this->translator->translate('ACTIVE')
        ],
        [
            'value' => 'unactivated',
            'text' => $this->translator->translate('UNACTIVATED')
        ],
        [
            'value' => 'disabled',
            'text' => $this->translator->translate('DISABLED')
        ]
    ];
}
```

## Etymology

If you're wondering where the term "Sprunje" comes from:

![Futurama - Sprunjer](/images/sprunje.mp4)
