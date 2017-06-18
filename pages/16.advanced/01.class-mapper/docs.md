---
title: Dynamic Class Mapper
metadata:
    description: 
taxonomy:
    category: docs
---

Extending PHP classes is a little different from extending other types of entities.  You cannot simply replace a class by redefining it in a custom Sprinkle.  In fact, classes with the same name in two different Sprinkles would be treated as two different fully-qualified classes.  For example, if I loaded the Sprinkles `Account` and `Site`, and I had the following structure:

```
sprinkles
├── account
│   └── src
│       └── Database
|           └── Models
│               └── User.php
└── site
|   └── src
│       └── Database
|           └── Models
│               └── User.php
```

then `User.php` in `site` would *not* override `User.php` in `account`.  Rather, I'd have two different classes: `\UserFrosting\Sprinkle\Account\Database\Models\User` and `\UserFrosting\Sprinkle\Site\Database\Models\User`.

To actually override and replace the functionality of a class, we have two tools available:

##### Class Inheritance

We could, for example, define our `User` class in the `site` Sprinkle to inherit from the `User` class in `account` using the `extends` keyword:

```
<?php

/* /app/sprinkles/site/src/Database\Models/User.php */

namespace \UserFrosting\Sprinkle\Site\Database\Models;

class User extends \UserFrosting\Sprinkle\Account\Database\Models\User
{

    ...

}

```

Now, we can start using `\UserFrosting\Sprinkle\Site\Database\Models\User` to extend the functionality provided by the `User` class in the `Account` sprinkle.

##### Dynamic Class Mapper

Of course, the limitations of object-oriented inheritance becomes clear when you want to change the behavior of the original class in other places where it has been used.  For example, if I extended `Account\Database\Models\User` and redefined the `onLogin` method in my `Site\Database\Models\User` class, this would let me use `Site\Database\Models\User` going forward in any code I write in the `site` Sprinkle.  However, it wouldn't affect references to `User` in the `account` Sprinkle - they would still be referring to the base class.

To allow this sort of "retroactive extendability", UserFrosting introduces another layer of abstraction - the **class mapper**.  The class mapper resolves generic class identifiers to specific class names at runtime.  Rather than hardcoding references to `Account\Database\Models\User`, Sprinkles can generically reference `user` through the class mapper, and it will find the most recently mapped version of that class.

For example, a controller in the account Sprinkle could do something like:

```
$user = $classMapper->staticMethod('user', 'where', 'email', 'admin@example.com')->first();
```

The account Sprinkle itself maps the `user` identifier to `UserFrosting\Sprinkle\Account\Database\Models\User`.  Thus, this call would be equivalent to:

```
$user = \UserFrosting\Sprinkle\Account\Database\Models\User::where('email', 'admin@example.com')->first();
```

**However**, if I later re-map the `user` identifier to `\UserFrosting\Sprinkle\Site\Database\Models\User`, then all calls to `$classMapper->staticMethod('user', ...)` **in any Sprinkle** will dynamically resolve to `\UserFrosting\Sprinkle\Site\Database\Models\User` instead.

Dynamic class mappings are typically defined by extending the `classMapper` service in your Sprinkle's **service provider**:

```
/* /app/sprinkles/account/src/ServicesProvider/ServicesProvider.php */

$container->extend('classMapper', function ($classMapper, $c) {
    $classMapper->setClassMapping('user', 'UserFrosting\Sprinkle\Account\Database\Models\User');
    $classMapper->setClassMapping('group', 'UserFrosting\Sprinkle\Account\Database\Models\Group');
    $classMapper->setClassMapping('role', 'UserFrosting\Sprinkle\Account\Database\Models\Role');
    $classMapper->setClassMapping('permission', 'UserFrosting\Sprinkle\Account\Database\Models\Permission');
    $classMapper->setClassMapping('activity', 'UserFrosting\Sprinkle\Account\Database\Models\Activity');
    return $classMapper;
});
```

You can learn more about services in [Chapter 15](/services).

Note that it's not just database models that you can dynamically remap (though they are the most common use case!)  Any class references that haven't been hardcoded can be dynamically remapped in another Sprinkle's `classMapper` service.
