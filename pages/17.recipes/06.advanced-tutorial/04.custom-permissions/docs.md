---
title: Adding custom authorization
metadata:
    description: Adding custom authorization rules to control access to our page
taxonomy:
    category: docs
---

Now it's time to add custom authorization rules to our page. At this point, we will control two things: Authorization to see the page and authorization to see the `origin` column. Each one will require a new [permission](/users/access-control#permissions). We will also assign those permission to the existing [roles](/users/access-control#roles). 

## Creating the permission in the database

### The migration class

UserFrosting doesn't have an UI to create new [Permissions](/users/access-control#permissions). This is done on purpose because, as you'll see, permission slugs are tightly coupled with the code and it doesn't make sense for any admin user to create new permission in the UI if he's not going to use them in the code.

To [create a new permission](/users/access-control#creating-new-permissions), we instead have to create a new row in the database table named `permissions`. To do this, we'll use a **migration**. Since we don't want to (and we already run that one), we will create a new migration whose role will be solely to create that new permission. We will also use that migration to assign  our new permission to existing roles, even if this could be done in the UI.

Let's start by creating our base migration class. That class will be located at the same place as our other class and will be named `PastriesPermissions`.

`app/sprinkles/pastries/src/Database/Migrations/V100/PastriesPermissions.php`
```php
<?php

namespace UserFrosting\Sprinkle\Pastries\Database\Migrations\v100;

use UserFrosting\System\Bakery\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

class PastriesPermissions extends Migration
{
    /**
     * {@inheritDoc}
     */
    public $dependencies = [

    ];
    
    /**
     * {@inheritDoc}
     */
    public function up()
    {

    }

    /**
     * {@inheritDoc}
     */
    public function down()
    {

    }
}
```

### The Dependencies 

Note that we won't define our other pastries related migrations here. While logically our permission migration should be run after our main one, there's no actual requirement to do so. Unlike the default pastry migration, we don't need to use the `Pastries` model here. We will, however, use the `Permissions` and `Role` model from the `Account` Sprinkle. 

Also, one could assume at this point the migrations for the `Account` sprinkle might have already been run. While this true in our case here (since we already have a working UserFrosting install), this might not be the case if someone is running **all** migrations at one (new deployment for instance) or if someone is not using the `account` sprinkle for some reason. So it's always safer to declare the core sprinkles migrations as dependencies if you are going to interact with the table they define.

With that cleared up, let's add the dependencies for the `Permissions` and `Role` tables. You can find the appropriates migrations by looking at the list of available migrations for that sprinkle:

```php
    public $dependencies = [
        '\UserFrosting\Sprinkle\Account\Database\Migrations\v400\RolesTable',
        '\UserFrosting\Sprinkle\Account\Database\Migrations\v400\PermissionsTable'
    ];
```

The clever reader may ask about the [`PermissionRolesTable` migration](https://github.com/userfrosting/UserFrosting/blob/v4.1.11-alpha/app/sprinkles/account/src/Database/Migrations/v400/PermissionRolesTable.php). This migration is tied to the `permission_roles` table which we will indirectly use to associate our new permission with roles. Since the `PermissionsTable` migration already defines the `PermissionRolesTable` as a dependency, we are not required to define it again on our side. By this logic, we could also skip the `RolesTable` from our dependencies list, but let's keep it anyway for sanity reasons.

### The `up` method

Next is the `up` method. We'll use the same trick as for the pastries migration and use an independent method which will return an array of permission. This means we will be able to use the same generic list for the `down` method.

First, since we'll manipulate the permission, we need to `use` the `Permission` model. Add its namespace in the header of the migration class:

```php
use UserFrosting\Sprinkle\Account\Database\Models\Permission;
```

Next, let's write the method that returns the list of permission :

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

And finally the `up` method:

```php
public function up()
{
    foreach ($this->pastryPermissions() as $permissionInfo) {
        $permission = new Permission($permissionInfo);
        $permission->save();
    }
}
```

As you can see, the `up` method will simply loop all the permission defined in the `pastryPermissions` method's returned array and create a new one in the database using the `Permission` model.

### The `down` method

Now for the `down` method. As any migration, this will contain the instructions on how to revert the changes made by the `up` method. We simply loop the same list of permission and do `delete` instead of `save`.

```php
public function down()
{
    foreach ($this->pastryPermissions() as $permissionInfo) {
        $permission = Permission::where($permissionInfo)->first();
        $permission->delete();
    }
}
```

### Final migration
Our finalized migration should now look like this:

```php
<?php

namespace UserFrosting\Sprinkle\Pastries\Database\Migrations\v100;

use UserFrosting\System\Bakery\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use UserFrosting\Sprinkle\Account\Database\Models\Permission;

class PastriesPermissions extends Migration
{
    /**
     * {@inheritDoc}
     */
    public $dependencies = [
        '\UserFrosting\Sprinkle\Account\Database\Migrations\v400\RolesTable',
        '\UserFrosting\Sprinkle\Account\Database\Migrations\v400\PermissionsTable'
    ];

    /**
     * {@inheritDoc}
     */
    public function up()
    {
        foreach ($this->pastryPermissions() as $permissionInfo) {
            $permission = new Permission($permissionInfo);
            $permission->save();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function down()
    {
        foreach ($this->pastryPermissions() as $permissionInfo) {
            $permission = Permission::where($permissionInfo)->first();
            $permission->delete();
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

You can now run the migration using `php bakery migrate`. You can then make sure the migration is successful by logging in as the root user and going to the permission page:

![Pastries permission list](/images/pastries/permission-list.png)


Let's also test the `down` method. From the CLI, use the rollback command from Bakery: `php bakery migrate:rollback`. This will revert the last run migrations. The permission page should back to normal :

>>>>> Don't forget to run `php bakery migrate` again at this point to redo the migration !

## Adding permission to the page

Before we continue, you'll have to login as a non-root user to test the permission. If the top navigation bar is red and tells you _you are signed in as the root user_, well, guess what... It's important you use a different user at this point since the root user has all the permission and you won't be able to see the permission in action otherwise. 

### Implementing `see_pastries` permission

#### Controller part

First we'll add a permission check in the `displayPage` of the `PastriesController`. Users without our `see_pastries` permission will not be able to see the page. In that case, a `ForbiddenException` will be thrown. That exception namespace should already be added to the class : `use UserFrosting\Support\Exception\ForbiddenException;` 

Next, we need a reference to `authorizer` service and the current user to perform the check. Add this code before loading the pastry from the database:

```php
/** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
$authorizer = $this->ci->authorizer;

/** @var UserFrosting\Sprinkle\Account\Database\Models\User $currentUser */
$currentUser = $this->ci->currentUser;
```

And now we add directly under the above the check itself:

```php
// Access-controlled page
if (!$authorizer->checkAccess($currentUser, 'see_pastries')) {
    throw new ForbiddenException();
}
```

The full method should now be:

`app/sprinkles/pastries/src/Controller/PastriesController.php`
```php
public function displayPage(Request $request, Response $response, $args)
{
    /** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
    $authorizer = $this->ci->authorizer;

    /** @var UserFrosting\Sprinkle\Account\Database\Models\User $currentUser */
    $currentUser = $this->ci->currentUser;

    // Access-controlled page
    if (!$authorizer->checkAccess($currentUser, 'see_pastries')) {
        throw new ForbiddenException();
    }

    $pastries = Pastries::all();

    //Debug::debug($pastries);

    return $this->ci->view->render($response, 'pages/pastries.html.twig', [
        'pastries' => $pastries
    ]);
}
```

At this point, we didn't add the `see_pastries` permission to any role. This means if you navigate to the page (with a non-root user), you should see the exception. 

>>>>> If you see the detailled error page, don't worry. In a **production** envrionement, this will be automatically replaced by the appropriate page.

#### Removing Link From the Menu

Now that our non-root users don't have access to the page, it would be nice to remove the link to the page from the sidebar menu. Let's dive back into our implementation of that link and add the permission verification using the `checkAccess`  Twig function provided by UserFrosting.

`app/sprinkles/pastries/templates/navigation/sidebar-menu.html.twig`
```html
{% extends "@admin/navigation/sidebar-menu.html.twig" %}

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

Now to make sure everything works correctly, let's add that `see_pastries` permission to the user role. Once this is done, a normal user should regain access to the page. Using a root account, navigate to the **Roles** page and select **Manage permissions** from the Actions dropdown menu of the **User** role. 

![Role page](/images/pastries/role-page.png)

Select the `see_pastries` permission from the bottom dropdown (use the search field to easily find it) and then click `update permissions`. 

![Adding permission](/images/pastries/adding-permission.png)

Your non-root user should now have access to the pastry page again (assuming they have the user role).

### Implementing `see_pastry_origin` permission

For this permission, we won't need to add anything to the controller. We will simply remove the `origin` column in the Twig template. The `checkAccess` needs to be added twice so it can control the table header and the rows in the loop. 

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

The non-root user shouldn't be able to see the origin column now. You can now add that permission to the **Site administrator** role if you want experiments adding that permission to various roles and adding said role to various users.

## Going further

In this example we used `always()` as the [callback](/users/access-control#callbacks) for our permission. If you want to further learn about the **Authorization System**, you could try adding [conditions](/users/access-control#performing-access-checks) to a new `see_pastry_details` permission to control which columns can be viewed using the same slug, and passing the column name to the callback.