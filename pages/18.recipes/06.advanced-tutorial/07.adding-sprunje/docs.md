---
title: Adding a Sprunje
metadata:
    description: Sort, paginate, and search/filter data using a Sprunje
taxonomy:
    category: docs
---

We will use a Sprunje to sort, paginate and filter data after adding ufTable later on in this tutorial. 

Sprunje classes are stored in the `src/Sprunje` directory. Create a new file in the directory called `PastriesSprunje.php`.

`app/sprinkles/pastries/src/Sprunje/PastriesSprunje.php`
```
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
