---
title: Adding custom authorization
metadata:
    description: Adding custom authorization rules to control access to our Pastries page.
taxonomy:
    category: docs
---

Now it's time to add custom authorization rules to our page. We will use these rules to control two things: visibility of the page itself, and visibility of the `origin` column in the table. Each one will require a new [permission](/users/access-control#permissions).

## Creating the permission in the database

### The base seed class

UserFrosting doesn't have a UI to create new [Permissions](/users/access-control#permissions). This is done on purpose because, as you'll see, permission slugs are tightly coupled with the code and it doesn't make sense for an admin user to create new permissions through the UI when they'll need to modify the code to actually use them.

To [create a new permission](/users/access-control#creating-new-permissions), we instead have to create a new row in the `permissions` database table. To do this, we'll use a **seed**. We've already explained how to create seeds earlier, but we will now create a new seed whose role will be solely to create the new permissions. We could also use that seed to assign our new permission to existing roles, even though this _can_ be done in the admin interface.

Let's start by creating our base seed class. That class will be located in the same place as our other seed class and will be named `PastriesPermissions`.

`app/sprinkles/pastries/src/Database/Seeds/PastriesPermissions.php`
```php
<?php

namespace UserFrosting\Sprinkle\Pastries\Database\Seeds;

use UserFrosting\Sprinkle\Core\Database\Seeder\BaseSeed;

class PastriesPermissions extends BaseSeed
{
    /**
     * {@inheritDoc}
     */
    public function run()
    {

    }
}
```

### The Dependencies

Unlike the `DefaultPastries` seed, this migration doesn't depend on the `Pastry` model. We do, however, have a dependency on the `Permission` and `Role` models from the `Account` Sprinkle.

One could reasonably assume at this point that the migrations for the `Account` sprinkle might have already been run. While this is true in our case (since we already have a working UserFrosting installation), this might not be the case if someone is running **all** migrations at once, if running a seed before running the depending migrations (a new deployment for instance) or if someone is not using the `account` sprinkle for some reason. So, it's always safer to declare the corresponding migrations as dependencies if you are going to interact with the table they correspond to.

With that cleared up, let's add the dependencies for the `Permission` and `Role` models inside the seed `run` method. We will use the `validateMigrationDependencies` helper method to test our dependencies are met :

```php
    $this->validateMigrationDependencies([
        '\UserFrosting\Sprinkle\Account\Database\Migrations\v400\RolesTable',
        '\UserFrosting\Sprinkle\Account\Database\Migrations\v400\PermissionsTable'
    ]);
```

The clever reader may ask about the [`PermissionRolesTable` migration](https://github.com/userfrosting/UserFrosting/blob/v4.1.11-alpha/app/sprinkles/account/src/Database/Migrations/v400/PermissionRolesTable.php). This migration is tied to the `permission_roles` table which we will indirectly use to associate our new permission with roles. Since the `PermissionsTable` migration already defines the `PermissionRolesTable` as a dependency, we are not required to define it again on our side. By this logic, we could also skip the `RolesTable` from our dependencies list, but let's keep it anyway for sanity reasons.

Next, let's define our new permissions:

```php
protected function pastryPermissions()
{
    return [
        [
            'slug' => 'see_pastries',
            'name' => 'See the pastries page',
            'conditions' => 'always()',
            'description' => 'Enables the user to see the pastries page'
        ],
        [
            'slug' => 'see_pastry_origin',
            'name' => 'See pastry origin',
            'conditions' => 'always()',
            'description' => 'Allows the user to see the origin of a pastry'
        ]
    ];
}
```

Before going further, we need to `use` the `Permission` model. Add a namespace alias in the header of the migration class:

```php
use UserFrosting\Sprinkle\Account\Database\Models\Permission;
```

We can now add the code to save the permissions in the `run` method :

```php
    foreach ($this->pastryPermissions() as $permissionInfo) {
        $permission = new Permission($permissionInfo);
        $permission->save();
    }
```

The `foreach` will simply loop through the permissions defined in the `pastryPermissions` method's returned array and create a new record in the database using the `Permission` model.

### Final seed

Our finalized seed should now look like this:

```php
<?php

namespace UserFrosting\Sprinkle\Pastries\Database\Seeds;

use UserFrosting\Sprinkle\Core\Database\Seeder\BaseSeed;
use UserFrosting\Sprinkle\Account\Database\Models\Permission;

class PastriesPermissions extends BaseSeed
{
    /**
     * {@inheritDoc}
     */
    public function run()
    {
        $this->validateMigrationDependencies([
            '\UserFrosting\Sprinkle\Account\Database\Migrations\v400\RolesTable',
            '\UserFrosting\Sprinkle\Account\Database\Migrations\v400\PermissionsTable'
        ]);

        foreach ($this->pastryPermissions() as $permissionInfo) {
            $permission = new Permission($permissionInfo);
            $permission->save();
        }
    }

    protected function pastryPermissions()
    {
        return [
            [
                'slug' => 'see_pastries',
                'name' => 'See the pastries page',
                'conditions' => 'always()',
                'description' => 'Enables the user to see the pastries page'
            ],
            [
                'slug' => 'see_pastry_origin',
                'name' => 'See pastry origin',
                'conditions' => 'always()',
                'description' => 'Allows the user to see the origin of a pastry'
            ]
        ];
    }
}

```

You can now run the migration using :

```
$ php bakery seed PastriesPermissions
```

You can make sure the migration was successful by logging in as the root user and going to the permissions page:

![Pastries permission list](/images/pastries/permission-list.png)

## Adding permission to the page

Before we continue, you'll have to login as a non-root user to test permissions. If the top navigation bar is red and says _you are signed in as the root user_, the authorization system will be completely bypassed. It's important that you use a different user to make sure that the new permissions are actually working properly.

### Implementing the `see_pastries` permission

#### Controller

First we'll add a permission check in the `pageList` of the `PastriesController`. Users without our `see_pastries` permission will not be able to see the page. In that case, a `ForbiddenException` will be thrown. The namespace alias for this exception should already be added: `use UserFrosting\Support\Exception\ForbiddenException;`.

Next, we need a reference to the `authorizer` service and the current user to perform the check. Add this code before loading the pastries from the database:

```php
/** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
$authorizer = $this->ci->authorizer;

/** @var UserFrosting\Sprinkle\Account\Database\Models\User $currentUser */
$currentUser = $this->ci->currentUser;
```

Finally we add the check:

```php
// Access-controlled page
if (!$authorizer->checkAccess($currentUser, 'see_pastries')) {
    throw new ForbiddenException();
}
```

The full method should now be:

`app/sprinkles/pastries/src/Controller/PastriesController.php`
```php
public function pageList(Request $request, Response $response, $args)
{
    /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
    $authorizer = $this->ci->authorizer;

    /** @var UserFrosting\Sprinkle\Account\Database\Models\User $currentUser */
    $currentUser = $this->ci->currentUser;

    // Access-controlled page
    if (!$authorizer->checkAccess($currentUser, 'see_pastries')) {
        throw new ForbiddenException();
    }

    $pastries = Pastry::all();

    //Debug::debug($pastries);

    return $this->ci->view->render($response, 'pages/pastries.html.twig', [
        'pastries' => $pastries
    ]);
}
```

At this point, we haven't yet added the `see_pastries` permission to any role. This means if you navigate to the page (with a non-root user), you'll get an error.

>>>>> If you see a detailed debugging page, don't worry. In a **production** environment, this will automatically be replaced by a generic "access denied" page.

#### Removing the link From the menu

Now that non-root users don't have access to the page, it would be nice to hide the link to the page in the sidebar menu. Let's dive back into our implementation of that link and add the permission verification using the `checkAccess`  Twig function provided by UserFrosting:

`app/sprinkles/pastries/templates/navigation/sidebar-menu.html.twig`
```twig
{% extends '@admin/navigation/sidebar-menu.html.twig' %}

{% block navigation %}
    {{ parent() }}
    {% if checkAccess('see_pastries') %}
        <li>
            <a href="/pastries"><i class="fa fa-cutlery fa-fw"></i> <span>{{translate('PASTRIES.LIST')}}</span></a>
        </li>
    {% endif %}
{% endblock %}
```

>>>>> We don't need to tell `checkAccess` to use the current user here as it is done by default.

The link should now be hidden from the menu when you refresh the page.

#### Adding the permission to a role

Now to make sure everything works correctly, let's add that `see_pastries` permission to the **User** role. Once this is done, a normal user will regain access to the page. Using a root account, navigate to the **Roles** page and select **Manage permissions** from the Actions dropdown menu of the **User** role.

![Role page](/images/pastries/role-page.png)

Select the `see_pastries` permission from the bottom dropdown (use the search field to easily find it) and then click `update permissions`.

![Adding permission](/images/pastries/adding-permission.png)

Your non-root user should now have access to the pastry page again (assuming they have the User role).

### Implementing the `see_pastry_origin` permission

For this permission, we won't need to add anything to the controller. We will simply hide the `origin` column in the Twig template if the user doesn't have the permission. The `checkAccess` function needs to be used twice so it can control the table header as well as the rows in the loop:

`app/sprinkles/pastries/templates/pages/pastries.html.twig`
```twig
{% extends 'pages/abstract/dashboard.html.twig' %}

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
                            {% if checkAccess('see_pastry_origin') %}<th>{{translate('PASTRIES.ORIGIN')}}</th>{% endif %}
                            <th>{{translate('PASTRIES.DESCRIPTION')}}</th>
                        </tr>
                        {% for pastry in pastries %}
                            <tr>
                                <td>{{pastry.name}}</td>
                                {% if checkAccess('see_pastry_origin') %}<td>{{pastry.origin}}</td>{% endif %}
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

The non-root user should no longer be able to see the origin column. You can now add that permission to the **Site Administrator** role if you want users with the "Site Administrator" role to be able to see the origin column.

## Going further

In this example we used `always()` as the [callback](/users/access-control#callbacks) for our permission. If you want to learn more about the **Authorization System**, you could try adding [conditions](/users/access-control#performing-access-checks) to a new `see_pastry_details` permission to control which columns can be viewed using the same slug, and passing the column name to the callback.
