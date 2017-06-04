---
title: Authorization
metadata:
    description: Authorization is sometimes referred to as "access control" or "protecting pages".  UserFrosting implements an extended version of role-based access control that supports procedural conditions on user permissions.
taxonomy:
    category: docs
---

## Overview

UserFrosting implements an extended version of [role-based access control](https://en.wikipedia.org/wiki/Role-based_access_control), which allows for very fine-grained control over user permissions.  Every user can have zero or more **roles**, and every role can have zero or more **permissions**.  Users' effective permissions are determined through their roles.

For example, consider the following roles, and their associated permissions:

- **Member**
  - Can update their own account info
  - Can post a new message
  - Can delete their own messages
- **Site Administrator**
  - Can update any account's info
  - Can delete any account's messages
  
If Alice has the **Member** role, she will be able to update her account info, post new messages, and delete her own messages.  If she had the **Site Administrator** role as well, she would gain the ability to update other users' accounts and delete their messages, in addition to the permissions that she has from her **Member** role.

## Defining permissions

A permission is a rule that associates an **action** with a set of **conditions** under which that action can be performed.  These are defined in the `permissions` database table.  For example:

| id | slug | name | conditions | description |
| -- | ---- | ---- | ---------- | ----------- |
| 1  | `uri_user` | View user | `always()` | View the user page of any user. |

- **slug** is a string that you select to represent this permission in your code.  If you slug is `uri_user`, then in your code you can call `$authorizer->checkAccess($currentUser, 'uri_user')` to determine if the current user has this permission.  You can define multiple permissions on the same slug.  As long as a user passes at least one permission on that slug, they will be granted access.
- **conditions** allows you to set constraints on this permission.  For example, you might want to create a permission that allows access on `uri_user`, but only for users in a particular group.  A boolean expression consisting of [**access condition callbacks**](#callbacks) can be used to construct your condition.
- **name** is a human-readable label for the permission, which can be used to easily identify it in the role management interface.
- **description** is a text description for the permission, allowing you to describe the purpose of the permission in human-readable terms.

>>> Roles can be created and modified through the administrative interface, but permissions cannot.  This is because permissions are intimately tied to your code and should **not** be modified during the course of daily site operation.  You should think of permissions as hardcoded parts of your application that just happen to be stored in the database.  When you need to **add, remove, or modify** permissions, this should be done by a developer or sysadmin using a [database migration](/database/extending-the-database).

## Performing access checks

In your code, access is controlled through the use of access checks on permission slugs.  Often times, you will want to perform these checks in your controller methods, and throw a `ForbiddenException` if the current user fails the check.

This can be done by calling the `checkAccess` method of the `authorizer` service.  For example:

```php
/** @var UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager */
$authorizer = $this->ci->authorizer;

/** @var UserFrosting\Sprinkle\Account\Model\User $currentUser */
$currentUser = $this->ci->currentUser;

if (!$authorizer->checkAccess($currentUser, 'uri_users')) {
    throw new ForbiddenException();
}
```

If the current user does not have any permissions for the slug `uri_users`, then the controller method will be aborted and a `ForbiddenException` will be thrown.  By default, the `ForbiddenExceptionHandler` will catch this exception and generate a "404 Not Found" response.

You can, of course, use `checkAccess` to control the behavior of your controller methods in other ways.  For example, you might build a data API that is available to the public, but that returns more specialized information to authorized users:

```php
if ($authorizer->checkAccess($currentUser, 'uri_owls')) {
    return $response->withJson($secretOwls);
} else {
    return $response->withJson($publicOwls);
}
```

## Access conditions

Access conditions are PHP expressions composed of callbacks and boolean operators.  These expressions must return a boolean value when evaluated.  When UserFrosting checks a permission for a given user, it will evaluate the condition expression, passing in any additional data from the final argument of `checkAccess`, and grant the permission only if the expression evaluates to `true`.  For example, suppose the current user has the permission:

| id | slug | name | conditions | description |
| -- | ---- | ---- | ---------- | ----------- |
| 1  | `uri_activity` | View activity | `equals_num(self.id,activity.user_id)` | View one of your own activities. |

In your code, if you call:

```php
$requestedActivity = Activity::find($requestedActivityId);

if (!$authorizer->checkAccess($currentUser, 'uri_activity', [
    'activity' => $requestedActivity
])) {
    throw new ForbiddenException();
}
```

Then, the `equals_num` condition will be used to compare the current user's `id` with the `user_id` associated with the requested activity (passed in as the `activity` key).  If they match, then the condition evaluates to `true` and the user is granted access.  You can use boolean operators to built arbitrarily complex conditions:

`!has_role(user.id,2) && !is_master(user.id)`

>>> In access conditions, the special keyword `self` is used to refer to the current user.  This avoids the need to explicitly pass in the current user's object.

### Callbacks

UserFrosting ships with a number of predefined access condition callbacks, which are defined in `sprinkles/account/src/ServicesProvider/AccountServicesProvider.php`:

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

To add your own access condition callbacks, simply [extend](/services/extending-services#extending-existing-services) the `authorizer` service in your Sprinkle. 
