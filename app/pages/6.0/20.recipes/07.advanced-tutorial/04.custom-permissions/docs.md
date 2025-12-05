---
title: Adding custom authorization
metadata:
    description: Adding custom authorization rules to control access to our Pastries page.
taxonomy:
    category: docs
---

Now it's time to add custom authorization rules to our page. We will use these rules to control two things: visibility of the page itself, and visibility of the `origin` column in the table. Each one will require a new [permission](/users/access-control#permissions).

## Creating the permission in the database

### The migration

UserFrosting doesn't have a UI to create new [Permissions](/users/access-control#permissions). This is done on purpose because, as you'll see, permission *slugs* are tightly coupled with the code and it doesn't make sense for an admin user to create new permissions through the UI when they'll need to modify the code to actually use them.

To [create a new permission](/users/access-control#creating-new-permissions), we instead have to create a new row in the `permissions` database table. To do this, we'll use a **migration**. We've already explained how to create migration earlier, but we will now create a new migration whose role will be solely to create the new entry into the database table, and drop them on rollback. We could also use that migration to assign our new permission to existing roles, even though this _can_ be done in the admin interface.

> [!NOTE]
> A seed can also be used to create permission entries. However, since a seed is only a one way operation (up) and can be run multiple times, it's best to use a migration for permissions entries. This will allow to remove the entries if the sprinkle/permission is not required anymore. Plus, since the permissions cannot be manually removed from the UI (unlike default pastries eventually, for example), there's no benefits of being able to run the seed twice.

Let's start by creating our migration class. That class will be located in the same place as our other migration and will be named `PastriesPermissions`.

**app/src/Database/Migrations/V100/PastriesPermissions.php**:
```php
<?php

namespace UserFrosting\App\Database\Migrations\V100;

use UserFrosting\Sprinkle\Core\Database\Migration;

class PastriesPermissions extends Migration
{
    /**
     * {@inheritdoc}
     */
    public static $dependencies = [];

    /**
     * {@inheritdoc}
     */
    public function up(): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function down(): void
    {
    }
}
```

### The Dependencies

Unlike the `PastriesTable` migration, this migration will have a dependency on the `PermissionsTable` and `RolesTable` migrations from the `Account` Sprinkle.

One could reasonably assume at this point that the migrations for the `Account` sprinkle might have already been run. While this is true in our case (since we already have a working UserFrosting installation), this might not be the case if someone is running ***all*** migrations at once, or if other dependencies are at play. So, it's always safer to declare the corresponding migrations as dependencies if you are going to interact with the table they correspond to.

With that cleared up, let's add the dependencies for the `PermissionsTable` and `RolesTable` migrations inside the migration `$dependencies` property. Don't forget to import the classes.

```php
use UserFrosting\Sprinkle\Account\Database\Migrations\v400\PermissionsTable;
use UserFrosting\Sprinkle\Account\Database\Migrations\v400\RolesTable;

// ...

public static $dependencies = [
    RolesTable::class,
    PermissionsTable::class,
];
```

The clever reader may ask about the `PermissionRolesTable` migration. This migration is tied to the `permission_roles` table which we will indirectly use to associate our new permission with roles. Since the `PermissionsTable` migration already defines the `PermissionRolesTable` as a dependency, we are not required to define it again on our side. By this logic, we could also skip the `RolesTable` from our dependencies list, but let's keep it anyway for sanity reasons.

Next, let's define our new permissions, in a new method at the bottom of the class:

```php
protected function pastryPermissions(): array
{
    return [
        [
            'slug'        => 'see_pastries',
            'name'        => 'See the pastries page',
            'conditions'  => 'always()',
            'description' => 'Enables the user to see the pastries page',
        ],
        [
            'slug'        => 'see_pastry_origin',
            'name'        => 'See pastry origin',
            'conditions'  => 'always()',
            'description' => 'Allows the user to see the origin of a pastry',
        ],
    ];
}
```

Now, we need to `use` the `Permission` model. This will be used to create the new permission object for saving to the database. Add the namespace alias in the header of the migration class:

```php
use UserFrosting\Sprinkle\Account\Database\Models\Permission;
```

We can now add the code to save the permissions in the `up()` method :

```php
foreach ($this->pastryPermissions() as $permissionInfo) {
    $permission = new Permission($permissionInfo);
    $permission->save();
}
```

The `foreach` will simply loop through the permissions defined in the `pastryPermissions` method's returned array and create a new record in the database using the `Permission` model.

Finally we do the same thing for the `down()` method, but deleting each entries instead of saving them:

```php
foreach ($this->pastryPermissions() as $permissionInfo) {
    /** @var Permission */
    $permission = Permission::where($permissionInfo)->first();
    $permission->delete();
}
```

### Final migration

Our finalized seed should now look like this:

```php
<?php

namespace UserFrosting\App\Database\Migrations\V100;

use UserFrosting\Sprinkle\Account\Database\Migrations\v400\PermissionsTable;
use UserFrosting\Sprinkle\Account\Database\Migrations\v400\RolesTable;
use UserFrosting\Sprinkle\Account\Database\Models\Permission;
use UserFrosting\Sprinkle\Core\Database\Migration;

class PastriesPermissions extends Migration
{
    /**
     * {@inheritdoc}
     */
    public static $dependencies = [
        RolesTable::class,
        PermissionsTable::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function up(): void
    {
        foreach ($this->pastryPermissions() as $permissionInfo) {
            $permission = new Permission($permissionInfo);
            $permission->save();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function down(): void
    {
        foreach ($this->pastryPermissions() as $permissionInfo) {
            /** @var Permission */
            $permission = Permission::where($permissionInfo)->first();
            $permission->delete();
        }
    }

    /**
     * @return string[][]
     */
    protected function pastryPermissions(): array
    {
        return [
            [
                'slug'        => 'see_pastries',
                'name'        => 'See the pastries page',
                'conditions'  => 'always()',
                'description' => 'Enables the user to see the pastries page',
            ],
            [
                'slug'        => 'see_pastry_origin',
                'name'        => 'See pastry origin',
                'conditions'  => 'always()',
                'description' => 'Allows the user to see the origin of a pastry',
            ],
        ];
    }
}
```

You can now run the migration using :

```bash
$ php bakery migrate
```

You can make sure the migration was successful by logging in as the root user and going to the permissions page:

![Pastries permission list](/images/pastries/permission-list.png)

## Adding permission to the page

Before we continue, you'll have to login as a non-root user to test permissions. If the top navigation bar is red and says _you are signed in as the root user_, the authorization system will be completely bypassed. It's important that you use a different user to make sure that the new permissions are actually working properly.

### Implementing the `see_pastries` permission

#### Controller

First we'll add a permission to the main `PastriesPageAction` method. Users without our `see_pastries` permission will not be able to see the page. In that case, a `ForbiddenException` will be thrown. The namespace alias for this exception should already be added: `use UserFrosting\Sprinkle\Account\Exceptions\ForbiddenException;`.

Next, we need to inject the **Authenticator** in the method, as a property. Then, we add the check:

```php
// Access-controlled page
if (!$authenticator->checkAccess('see_pastries')) {
    throw new ForbiddenException();
}
```

The full method should now be:

**app/src/Controller/PastriesPageAction.php**:
```php
public function __invoke(
    Response $response,
    Authenticator $authenticator,
    Twig $view,
): Response {
    // Access-controlled page
    if (!$authenticator->checkAccess('see_pastries')) {
        throw new ForbiddenException();
    }

    // Get pastries from the database
    $pastries = Pastries::all();

    return $view->render($response, 'pages/pastries.html.twig', [
        'pastries' => $pastries,
    ]);
}
```

At this point, we haven't yet added the `see_pastries` permission to any role. This means if you navigate to the page (with a non-root user), you'll get an error.

> [!NOTE]
> If you see a detailed debugging page, don't worry. In a **production** environment, this will automatically be replaced by a generic "access denied" page.

#### Hide the link from the menu

Now that non-root users don't have access to the page, it would be nice to hide the link to the page in the sidebar menu. Let's dive back into our implementation of that link and add the permission verification using the custom `checkAccess` Twig function provided by UserFrosting:

**app/templates/navigation/sidebar-menu.html.twig**:
```html
{% extends "@admin-sprinkle/navigation/sidebar-menu.html.twig" %}

{% block navigation %}
    {{ parent() }}
    {% if checkAccess('see_pastries') %}
        <li>
            <a href="{{ urlFor('pastries') }}"><i class="fas fa-utensils fa-fw"></i> <span>{{translate('PASTRIES.LIST')}}</span></a>
        </li>
    {% endif %}
{% endblock %}
```

The link should now be hidden from the menu when you refresh the page.

#### Adding the permission to a role

Now to make sure everything works correctly, let's add that `see_pastries` permission to the **User** role. Once this is done, a normal user will regain access to the page. Using a root account, navigate to the **Roles** page and select **Manage permissions** from the Actions dropdown menu of the **User** role.

![Role page](/images/pastries/role-page.png)

Select the `see_pastries` permission from the bottom dropdown (use the search field to easily find it) and then click `update permissions`.

![Adding permission](/images/pastries/adding-permission.png)

Your non-root user should now have access to the pastry page again (assuming they have the User role).

### Implementing the `see_pastry_origin` permission

For this permission, we won't need to add anything to the controller. We will simply hide the `origin` column in the Twig template if the user doesn't have the permission (Note: the resulting data won't be visible in any api request). The `checkAccess` function needs to be used twice so it can control the table header as well as the rows in the loop:

**app/templates/pages/pastries.html.twig**:
```html
{% extends 'pages/abstract/dashboard.html.twig' %}

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
                    <tr>
                        <th>Name</th>
                        {% if checkAccess('see_pastry_origin') %}<th>Origin</th>{% endif %}
                        <th>Description</th>
                    </tr>
                    {% for pastry in pastries %}
                        <tr>
                            <td>{{pastry.name}}</td>
                            {% if checkAccess('see_pastry_origin') %}<td>{{pastry.origin}}</td>{% endif %}
                            <td>{{pastry.description}}</td>
                        </tr>
                    {% endfor %}
                </div>
            </div>
        </div>
    </div>
{% endblock %}
```

The non-root user should no longer be able to see the origin column. You can now add that permission to the **Site Administrator** role if you want users with the "Site Administrator" role to be able to see the origin column.

## Going further

In this example we used `always()` as the [callback](/users/access-control#callbacks) for our permission. If you want to learn more about the **Authorization System**, you could try adding [conditions](/users/access-control#performing-access-checks) to a new `see_pastry_details` permission to control which columns can be viewed using the same slug, and passing the column name to the callback.
