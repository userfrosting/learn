---
title: Sprunjes
description: Build powerful server-side data tables with filtering, sorting, and pagination using Sprunjes.
wip: true
---

**Sprunjes** are UserFrosting's solution for building powerful, server-side data tables. They handle the complex logic of filtering, sorting, and paginating data on the backend, while providing a clean API for your frontend components.

## What is a Sprunje?

A Sprunje is a PHP class that:

- **Queries your database** using Eloquent models
- **Filters data** based on user input
- **Sorts results** by any column
- **Paginates** large datasets
- **Returns JSON** formatted for frontend consumption

Think of it as a "smart endpoint" specifically designed for data tables. Instead of writing custom logic for every table in your application, you create a Sprunje that handles all the complexity.

## Why Use Sprunjes?

### Without Sprunjes

You'd need to manually:
1. Parse query parameters for filters, sorts, and pagination
2. Build complex Eloquent queries with WHERE clauses
3. Handle sorting logic
4. Calculate pagination metadata
5. Format the response as JSON

### With Sprunjes

You define what data to load and how to filter it, and Sprunjes handle the rest automatically.

## Basic Sprunje

Here's a simple Sprunje for displaying users:

**`app/src/Sprunje/UserSprunje.php`:**
```php
<?php

namespace App\Sprunje;

use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Core\Sprunje\Sprunje;

class UserSprunje extends Sprunje
{
    protected string $name = 'users';

    /**
     * Set the initial Eloquent query.
     */
    protected function baseQuery()
    {
        return User::query();
    }
}
```

That's it! This Sprunje can now:
- List all users
- Filter by any column
- Sort by any column
- Paginate results

## Using a Sprunje in a Controller

**`app/src/Controller/UserController.php`:**
```php
<?php

namespace App\Controller;

use App\Sprunje\UserSprunje;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UserController
{
    public function list(Request $request, Response $response, UserSprunje $sprunje): Response
    {
        // Get query parameters and process with Sprunje
        $result = $sprunje->getResults($request->getQueryParams());

        // Return JSON response
        $payload = json_encode($result);
        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }
}
```

**Route definition:**
```php
$app->get('/api/users', [UserController::class, 'list']);
```

## Frontend Integration

Use the Sprunje endpoint in your Vue component:

```vue
<template>
    <table class="uk-table uk-table-striped">
        <thead>
            <tr>
                <th @click="sort('user_name')">Username</th>
                <th @click="sort('email')">Email</th>
                <th @click="sort('created_at')">Created</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="user in users" :key="user.id">
                <td>{{ user.user_name }}</td>
                <td>{{ user.email }}</td>
                <td>{{ formatDate(user.created_at) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="uk-margin">
        <button @click="prevPage" :disabled="!hasPrevPage">Previous</button>
        <button @click="nextPage" :disabled="!hasNextPage">Next</button>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'

interface User {
    id: number
    user_name: string
    email: string
    created_at: string
}

const users = ref<User[]>([])
const currentPage = ref(1)
const totalPages = ref(1)
const sortColumn = ref('user_name')
const sortDirection = ref<'asc' | 'desc'>('asc')

async function loadUsers() {
    const response = await axios.get('/api/users', {
        params: {
            page: currentPage.value,
            sort: `${sortDirection.value === 'desc' ? '-' : ''}${sortColumn.value}`
        }
    })

    users.value = response.data.rows
    totalPages.value = response.data.count_page
}

function sort(column: string) {
    if (sortColumn.value === column) {
        sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc'
    } else {
        sortColumn.value = column
        sortDirection.value = 'asc'
    }
    loadUsers()
}

function prevPage() {
    if (currentPage.value > 1) {
        currentPage.value--
        loadUsers()
    }
}

function nextPage() {
    if (currentPage.value < totalPages.value) {
        currentPage.value++
        loadUsers()
    }
}

const hasPrevPage = computed(() => currentPage.value > 1)
const hasNextPage = computed(() => currentPage.value < totalPages.value)

onMounted(() => {
    loadUsers()
})
</script>
```

## Custom Filters

Add custom filtering logic to your Sprunje:

```php
<?php

namespace App\Sprunje;

use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Core\Sprunje\Sprunje;
use Illuminate\Database\Eloquent\Builder;

class UserSprunje extends Sprunje
{
    protected string $name = 'users';

    protected function baseQuery()
    {
        return User::query();
    }

    /**
     * Filter by active status.
     */
    protected function filterActive(Builder $query, mixed $value): Builder
    {
        return $query->where('flag_enabled', $value);
    }

    /**
     * Filter by role.
     */
    protected function filterRole(Builder $query, mixed $value): Builder
    {
        return $query->whereHas('roles', function ($query) use ($value) {
            $query->where('slug', $value);
        });
    }

    /**
     * Filter by email domain.
     */
    protected function filterEmailDomain(Builder $query, mixed $value): Builder
    {
        return $query->where('email', 'LIKE', "%@{$value}");
    }
}
```

**Using custom filters:**
```javascript
// Get only active users
axios.get('/api/users?filters[active]=1')

// Get users with admin role
axios.get('/api/users?filters[role]=admin')

// Get users from specific domain
axios.get('/api/users?filters[email_domain]=example.com')

// Combine filters
axios.get('/api/users?filters[active]=1&filters[role]=admin')
```

## Sorting

Sorting is automatic for all columns, but you can customize it:

```php
/**
 * Custom sort for full name (concatenated first_name + last_name).
 */
protected function sortFullName(Builder $query, string $direction): Builder
{
    return $query->orderByRaw("CONCAT(first_name, ' ', last_name) {$direction}");
}
```

**Frontend usage:**
```javascript
// Sort ascending
axios.get('/api/users?sort=user_name')

// Sort descending (prefix with -)
axios.get('/api/users?sort=-created_at')

// Custom sort
axios.get('/api/users?sort=full_name')
```

## Eager Loading Relationships

Improve performance by eager loading relationships:

```php
protected function baseQuery()
{
    return User::with(['group', 'roles']);
}
```

## Limiting Accessible Columns

For security, limit which columns can be filtered and sorted:

```php
protected array $sortable = [
    'user_name',
    'email',
    'created_at'
];

protected array $filterable = [
    'user_name',
    'email',
    'active'
];
```

## Response Format

Sprunjes return JSON in this format:

```json
{
    "count": 50,
    "count_filtered": 10,
    "count_page": 2,
    "rows": [
        {
            "id": 1,
            "user_name": "admin",
            "email": "admin@example.com",
            "created_at": "2024-01-15T10:30:00"
        }
    ]
}
```

- **count**: Total records in database
- **count_filtered**: Records after applying filters
- **count_page**: Total pages available
- **rows**: Current page data

## Advanced: Transforming Results

Modify data before sending to frontend:

```php
protected function transformRow($row): array
{
    return [
        'id' => $row->id,
        'username' => $row->user_name,
        'email' => $row->email,
        'fullName' => $row->first_name . ' ' . $row->last_name,
        'isActive' => (bool) $row->flag_enabled,
        'roleNames' => $row->roles->pluck('name')->join(', ')
    ];
}
```

## Authorization

Check permissions before returning data:

```php
protected function baseQuery()
{
    // Check if user can view users
    if (!$this->currentUser->can('view_users')) {
        throw new ForbiddenException();
    }

    return User::query();
}
```

## Best Practices

1. **Create one Sprunje per data table**: Don't try to make one Sprunje do everything
2. **Use eager loading**: Load relationships upfront with `with()` to avoid N+1 queries
3. **Limit exposed columns**: Use `$sortable` and `$filterable` to control what can be queried
4. **Transform sensitive data**: Use `transformRow()` to remove or modify sensitive fields
5. **Check permissions**: Always verify the user can access the data
6. **Index database columns**: Ensure columns used for sorting and filtering are indexed

## Next Steps

- **[Tables](/ui-theming/tables)**: See how to build table UIs that use Sprunjes
- **[Advanced Tables](/ui-theming/tables#advanced-features)**: Implement search, filters, and exports

## Resources

- [Sprunje Source Code](https://github.com/userfrosting/framework/tree/6.x/src/Sprinkle/Core/Sprunje)
- [Eloquent Documentation](https://laravel.com/docs/eloquent)
