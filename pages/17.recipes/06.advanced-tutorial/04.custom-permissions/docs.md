---
title: Adding custom authorization rules
metadata:
    description: Adding custom authorization rules to control access to our page
taxonomy:
    category: docs
---

Now it's time to add custom authorization rules to our page. At this point, we will control two thing: Authorisation to see the page and authorisation to see the `origin` column. Each one will require a new [permission](/users/access-control#permissions). We will also assign those permissions to the existing [roles](/users/access-control#roles). 

Before we continue, you'll have to login as a non root user. If the top navigation bar is red and tells you _you are signed in as the root user_, well, guess what... It's important you use a different user at this point since the root user has all the permissions and you won't be able to see the permissions in action otherwise. 

## Creating the permission in the database

UserFrosting doesn't have an UI to create new [Permissions](/users/access-control#permissions). This is done on purpose because, as you'll see, permissions slugs are thighly coupled with the code and it doesn't make sense for any admin user to create new permission in the UI if he's not going to use them in the code.

To [create a new permission](/users/access-control#creating-new-permissions), we instead have to create a new row in the database table named `permissions`. To do this, we'll use a **migration**. Since we don't want to (and we already run that one), we will create a new migration who's role will be solely to create that new permission. We will also use that migration to assign  our new permissions to existing roles, even if this could be done in the UI.

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

Note that we won't define our other pastries related migrations here. While logically our permission migration should be run after our main one, there's no actual requirement to do so. Unlike the default pastries migration, we don't need to use the `Pastries` model here. We will however use the `Permissions` and `Role` model from the `Account` Sprinkle. 

One could assume at this point the migrations for the `Account` sprinkle might have already been run. While this true in our case here (since we already have a working UserFrosting install), this might not be the case if someone is running **all** migrations at one (new deployment for instance) or if someone is not using the `account` sprinkle for some reason. So it's always safer to declare the core sprinkles migrations as dependencies if you are going to interact with the table they define.

With that cleared up, let's add the dependencies for the `Permissions` and `Role` tables. You can find the appropriates migrations by looking at the list of avaialble migrations for that sprinkle:

```php
    public $dependencies = [
        '\UserFrosting\Sprinkle\Account\Database\Migrations\v400\RolesTable',
        '\UserFrosting\Sprinkle\Account\Database\Migrations\v400\PermissionsTable'
    ];
```

The clever reader may ask about the [`PermissionRolesTable` migration](https://github.com/userfrosting/UserFrosting/blob/v4.1.11-alpha/app/sprinkles/account/src/Database/Migrations/v400/PermissionRolesTable.php). This migration is tied to the `permission_roles` table which we will indirectly use to associate our new permissions with roles. Since the `PermissionsTable` migration already define the `PermissionRolesTable` as a dependency, we are not required to defined it again on our side. By this logic, we could also skip the `RolesTable` from our dependencies list, but let's keep it anyway for sanity reasons.

Next is the `up` method. 


Now for the `down` method.


Our finalyzed migration should now look like this:

```php

```

## Adding the permissions to the role in the UI

## Adding permission check in the controller

## Adding permission check in the template