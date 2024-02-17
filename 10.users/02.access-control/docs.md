---
title: Authorization
metadata:
    description: Authorization is sometimes referred to as "access control" or "protecting pages". UserFrosting implements an extended version of role-based access control that supports procedural conditions on user permissions.
taxonomy:
    category: docs
---

## Roles

UserFrosting implements an extended version of [role-based access control](https://en.wikipedia.org/wiki/Role-based_access_control), which allows for very fine-grained control over user permissions. Every user can have zero or more **roles**, and every role can have zero or more **permissions**. Users' effective permissions are determined through their roles.

For example, consider the following roles, and their associated permissions:

- **Member**
  - Can update their own account info
  - Can post a new message
  - Can delete their own messages
- **Site Administrator**
  - Can update any account's info
  - Can delete any account's messages

If Alice has the **Member** role, she will be able to update her account info, post new messages, and delete her own messages. If she had the **Site Administrator** role as well, she would gain the ability to update other users' accounts and delete their messages, in addition to the permissions that she has from her **Member** role.

## Permissions

A permission is a rule that associates an **action** with a set of **conditions** under which that action can be performed. These are defined in the `permissions` database table. For example:

| id  | slug       | name      | conditions | description                     |
| --- | ---------- | --------- | ---------- | ------------------------------- |
| 1   | `uri_user` | View user | `always()` | View the user page of any user. |

- **slug** is a string that you select to represent this permission in your code. If your slug is `uri_user`, then in your code you can call `$authorizer->checkAccess($currentUser, 'uri_user')` to determine if the current user has this permission. You can define multiple permissions on the same slug. As long as a user passes at least one permission on that slug, they will be granted access.
- **conditions** allows you to set constraints on this permission. For example, you might want to create a permission that allows access on `uri_user`, but only for users in a particular group. A boolean expression consisting of [**access condition callbacks**](#callbacks) can be used to construct your condition.
- **name** is a human-readable label for the permission, which can be used to easily identify it in the role management interface.
- **description** is a text description for the permission, allowing you to describe the purpose of the permission in human-readable terms.

## Performing access checks

In your code, access is controlled through the use of access checks on permission slugs. Often times, you will want to perform these checks in your controller methods, and throw a `ForbiddenException` if the current user fails the check.

This can be done by calling the `checkAccess` method of the `AuthorizationManager` service. For example:

```php
#[\DI\Attribute\Inject]
protected Authenticator $authenticator

#[\DI\Attribute\Inject]
protected AuthorizationManager $authorizer

// ...

$currentUser = $this->authorizer->user();

if (!$this->authorizer->checkAccess($currentUser, 'uri_users')) {
    throw new ForbiddenException();
}
```

Or simply use the `checkAccess` method of the `Authenticator` service, which is a shortcut for the above code (the current user will be automatically passed to `AuthorizationManager` behind the scene). For example:

```php
#[\DI\Attribute\Inject]
protected Authenticator $authenticator

// ...
if (!$this->authenticator->checkAccess('uri_users')) {
    throw new ForbiddenException();
}
```

If the current user does not have any permissions for the slug `uri_users`, then the controller method will be aborted and a `ForbiddenException` will be thrown. By default, the `ForbiddenExceptionHandler` will catch this exception and generate a "404 Not Found" response.

You can, of course, use `checkAccess` to control the behavior of your controller methods in other ways. For example, you might build a data API that is available to the public, but that returns more specialized information to authorized users:

```php
if ($authenticator->checkAccess('uri_owls')) {
    return $response->withJson($secretOwls);
} else {
    return $response->withJson($publicOwls);
}
```

## Access conditions

Access conditions are PHP expressions composed of callbacks and boolean operators. These expressions must return a boolean value when evaluated. When UserFrosting checks a permission for a given user, it will evaluate the condition expression, passing in any additional data from the final argument of `checkAccess`, and grant the permission only if the expression evaluates to `true`. For example, suppose the current user has the permission:

| id  | slug           | name          | conditions                             | description                      |
| --- | -------------- | ------------- | -------------------------------------- | -------------------------------- |
| 1   | `uri_activity` | View activity | `equals_num(self.id,activity.user_id)` | View one of your own activities. |

In your code, if you call:

```php
$requestedActivity = Activity::find($requestedActivityId);

if (!$authenticator->checkAccess('uri_activity', [
    'activity' => $requestedActivity
])) {
    throw new ForbiddenException();
}
```

Then, the `equals_num` condition will be used to compare the current user's `id` with the `user_id` associated with the requested activity (passed in as the `activity` key). If they match, then the condition evaluates to `true` and the user is granted access. You can use boolean operators to built arbitrarily complex conditions:

```php
!has_role(user.id,2) && !is_master(user.id)
```

[notice]In access conditions, the special keyword `self` is used to refer to the current user. This avoids the need to explicitly pass in the current user's object.[/notice]

### Callbacks

UserFrosting ships with a number of predefined access condition callbacks, which are defined in `UserFrosting\Sprinkle\Account\Authorize\AccessConditions`:

| Callback                          | Description                                                                                  |
| --------------------------------- | -------------------------------------------------------------------------------------------- |
| `always()`                        | Unconditionally grant permission - use carefully!                                            |
| `equals($val1, $val2)`            | Check if the specified values are identical to one another (strict comparison).              |
| `equals_num($val1, $val2)`        | Check if the specified values are numeric, and if so, if they are equal to each other.       |
| `has_role($user_id, $role_id)`    | Check if the specified user (by `$user_id`) has a particular role.                           |
| `in($needle, $haystack)`          | Check if the specified value `$needle` is in the values of `$haystack`.                      |
| `in_group($user_id, $group_id)`   | Check if the specified user (by `$user_id`) is in a particular group.                        |
| `is_master($user_id)`             | Check if the specified user (by `$user_id`) is the master user.                              |
| `subset($needle, $haystack)`      | Check if all **values** in the array `$needle` are present in the **values** of `$haystack`. |
| `subset_keys($needle, $haystack)` | Check if all **keys** of the array `$needle` are present in the **values** of `$haystack`.   |

### Custom callbacks

To add your own access condition callbacks, simply extend `UserFrosting\Sprinkle\Account\Authorize\AccessConditions` and replace it in a custom Service Provider. For example : 

```php
use UserFrosting\ServicesProvider\ServicesProviderInterface;
use UserFrosting\Sprinkle\Account\Authorize\AccessConditionsInterface;
use MyApp\Authorize\MyCustomAccessConditions;

// ...

final class CustomAccessConditionsService implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            AccessConditionsInterface::class => \DI\autowire(MyCustomAccessConditions::class),
        ];
    }
}
```
<!-- TODO : Requires update in Account Sprinkle (UserFrosting\Sprinkle\Account\Authorize\AccessConditions) -->
<!-- 
Alternatively, to add your own access condition callbacks, simply [decorate](/dependency-injection/extending-services) the `AccessConditions` service:

```php
public function register(): array
{
    return [
        AccessConditionsInterface::class => \DI\decorate(function (AccessConditionsInterface $authorizer, UserInterface $userModel) {
            $authorizer['in_organization'] =>
                /**
                 * Check if the specified user (by id) is in a particular organization.
                 *
                 * @param int $user_id the id of the user.
                 * @param int $organization_id the id of the organization.
                 * @return bool true if the user is in the organization, false otherwise.
                 */
                function ($user_id, $organization_id) use ($userModel) {
                    $user = $userModel->find($user_id);
                    return ($user->organization_id == $organization_id);
                }
            );

            return $authorizer;
        }),
    ];
}
```
-->

## Creating new permissions

You may notice that while roles can be created and modified through the administrative interface, permissions cannot. This is because permissions are intimately tied to your code and should **not** be modified during the course of daily site operation.

Think about it this way - for a permission to have any effect on your application at all, you must reference its slug somewhere in one of your controllers. This means that even if a user were to create a new permission through the web interface, it **wouldn't make any difference** until a developer were to implement some code that makes use of it.

Instead, you should think of permissions as hardcoded parts of your application that just happen to be stored in the database. Permissions can be **added, removed, or modified** using a [database migration](/database/migrations) or a [database seed](/database/seeding).

Both methods can be used to create or manipulate permissions. **Migrations** are better suited to edit or remove existing permissions since they assure your permissions stays constant in time, but won't help you restore a permission if one gets deleted by accident, since a migration can only be run once. **Seeds** on the other hand can be run more than once, so they can be used to restore a deleted permission, but can't be relied on to edit a permission the same way you can with a migration, since a seed can be run in any order, you can't keep track which one have been run, don't have dependencies and can't be automatically rolled down.

With this in mind, it is recommended to use a **migration** to create permissions. However, since both methods are valid and can be used depending on the developer choice, both are shown below.

### Using a Seed

```php
<?php
namespace UserFrosting\Sprinkle\Site\Database\Seeds;

use UserFrosting\Sprinkle\Account\Database\Models\Permission;
use UserFrosting\Sprinkle\Account\Database\Models\Role;
use UserFrosting\Sprinkle\Core\Seeder\SeedInterface;

class CustomPermissions implements SeedInterface
{
    public function run(): void
    {
        // Add default permissions
        $permissions = [
            'uri_members' => new Permission([
                'slug' => 'uri_members',
                'name' => 'Member management page',
                'conditions' => 'always()',
                'description' => 'View a page containing a list of members.'
            ]),
            'uri_owls' => new Permission([
                'slug' => 'uri_owls',
                'name' => 'View owls',
                'conditions' => 'always()',
                'description' => 'View a full list of owls in the system.'
            ])
        ];

        foreach ($permissions as $id => $permission) {
            $slug = $permission->slug;
            $conditions = $permission->conditions;
            // Skip if a permission with the same slug and conditions has already been added
            if (!Permission::where('slug', $slug)->where('conditions', $conditions)->first()) {
                $permission->save();
            }
        }

        // Automatically add permissions to particular roles
        $roleAdmin = Role::where('slug', 'site-admin')->first();
        if ($roleAdmin) {
            $roleAdmin->permissions()->syncWithoutDetaching([
                $permissions['uri_members']->id,
                $permissions['uri_owls']->id
            ]);
        }
    }
}
```

Don't forget to add the seed to your Sprinkle Recipe. Then, it can be run using the following Bakery command, and select your seed:

```bash
$ php bakery seed
```

### Using a Migration

```php
<?php
namespace UserFrosting\Sprinkle\Site\Database\Migrations;

use UserFrosting\Sprinkle\Account\Database\Migrations\v400\PermissionsTable;
use UserFrosting\Sprinkle\Account\Database\Migrations\v400\RolesTable;
use UserFrosting\Sprinkle\Account\Database\Models\Permission;
use UserFrosting\Sprinkle\Account\Database\Models\Role;
use UserFrosting\Sprinkle\Core\Database\Migration;

class CustomPermissions extends Migration
{
    public static $dependencies = [
        PermissionsTable::class,
        RolesTable::class,
    ];

    public function up(): void
    {
        // Add default permissions
        $permissions = $this->getPermissions();

        foreach ($permissions as $id => $permission) {
            $slug = $permission->slug;
            $conditions = $permission->conditions;
            // Skip if a permission with the same slug and conditions has already been added
            if (!Permission::where('slug', $slug)->where('conditions', $conditions)->first()) {
                $permission->save();
            }
        }

        // Automatically add permissions to particular roles
        $roleAdmin = Role::where('slug', 'site-admin')->first();
        if ($roleAdmin) {
            $roleAdmin->permissions()->syncWithoutDetaching([
                $permissions['uri_members']->id,
                $permissions['uri_owls']->id
            ]);
        }
    }

    public function down(): void
    {
        foreach ($this->getPermissions() as $id => $permissionData) {
            $permission = Permission::where($permissionData)->first();
            $permission->delete();
        }
    }

    protected function getPermissions(): array
    {
        return [
            'uri_members' => new Permission([
                'slug' => 'uri_members',
                'name' => 'Member management page',
                'conditions' => 'always()',
                'description' => 'View a page containing a list of members.'
            ]),
            'uri_owls' => new Permission([
                'slug' => 'uri_owls',
                'name' => 'View owls',
                'conditions' => 'always()',
                'description' => 'View a full list of owls in the system.'
            ])
        ];
    }
}
```

Don't forget to add the migration to your Sprinkle Recipe. Then, it can be run using the following Bakery command:

```bash
$ php bakery migrate
```
