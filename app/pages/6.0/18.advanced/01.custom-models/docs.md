---
title: Using Custom Data Models
metadata:
    description: The dependency injector makes it easy to override entire data models in your Sprinkle.
taxonomy:
    category: docs
---

Extending PHP classes is a little different from extending other types of entities. You cannot simply replace a class by redefining it in a custom Sprinkle. In fact, classes with the same name in two different Sprinkles would be treated as two different fully-qualified classes per the [PSR-4 standard](http://www.php-fig.org/psr/psr-4/). For example, if I loaded the Sprinkles `Account` and `Site`, and I had the following structure:

```
sprinkles
├── account
│   └── src
│       └── Database
│           └── Models
│               └── User.php
├── site
│   └── src
│       └── Database
│           └── Models
│               └── User.php
```

...then `User.php` in `site` would *not* override `User.php` in `account`. Rather, I'd have two different classes because both classes would have two different **namespace** : `\UserFrosting\Sprinkle\Account\Database\Models\User` and `\UserFrosting\Sprinkle\Site\Database\Models\User`.

To actually override and replace the functionality of a class, we have two tools available:

## Class Inheritance

We could, for example, define our `User` class in the `site` Sprinkle to inherit from the `User` class in `account` using the `extends` keyword:

**app/src/Database/Models/User.php** :
```php
<?php

namespace \UserFrosting\Sprinkle\MySprinkle\Database\Models;

class User extends \UserFrosting\Sprinkle\Account\Database\Models\User
{
    // ...
}
```

Now, we can start using `\UserFrosting\Sprinkle\Site\Database\Models\User` to extend the functionality provided by the `User` class in the `Account` sprinkle.

## Dynamic Model Mapper

Of course, the limitations of object-oriented inheritance becomes clear when you want to change the behavior of the original class in other places where it has been used. For example, if I extended `Account\Database\Models\User` and redefined the `onLogin` method in my `Site\Database\Models\User` class, this would let me use `Site\Database\Models\User` going forward in any code I write in the `site` Sprinkle. However, it wouldn't affect references to `User` in the `account` Sprinkle - they would still be referring to the base class.

To allow this sort of "_retroactive extendability_", the Dependency Injector can be used to resolves interface identifiers to specific class names at runtime [through Interface Binding and custom Autowiring](/dependency-injection/the-di-container#binding-interfaces). 

UserFrosting uses this feature to solve this issue when dealing with data Models **by binding each default model to an interface**. Rather than hardcoding references to `UserFrosting\Sprinkle\Account\Database\Models\User`, UserFrosting reference the `UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface` interface and map the interface with the correct model in the Account Sprinkle service provider. 

For example, a controller in the account Sprinkle will do something like:

```php
public function __construct(
    protected UserInterface $userModel,
) {
}

public function __invoke(Request $request, Response $response): Response
{
    // ...
    $user = $userModel->where('email', 'admin@example.com')->first();
    // ...
}
```

...instead of:

```php
public function __invoke(Request $request, Response $response): Response
{
    // ...
    $user = User::where('email', 'admin@example.com')->first();
    // ...
}
```

## Default Model Identifiers

The following interface-model association are defined by default in the *Account* sprinkle :

| Interface                                                                         | Model                                                         |
| --------------------------------------------------------------------------------- | ------------------------------------------------------------- |
| `UserFrosting\Sprinkle\Account\Database\Models\Interfaces\ActivityInterface`      | `UserFrosting\Sprinkle\Account\Database\Models\Activity`      |
| `UserFrosting\Sprinkle\Account\Database\Models\Interfaces\GroupInterface`         | `UserFrosting\Sprinkle\Account\Database\Models\Group`         |
| `UserFrosting\Sprinkle\Account\Database\Models\Interfaces\PasswordResetInterface` | `UserFrosting\Sprinkle\Account\Database\Models\PasswordReset` |
| `UserFrosting\Sprinkle\Account\Database\Models\Interfaces\PermissionInterface`    | `UserFrosting\Sprinkle\Account\Database\Models\Permission`    |
| `UserFrosting\Sprinkle\Account\Database\Models\Interfaces\PersistenceInterface`   | `UserFrosting\Sprinkle\Account\Database\Models\Persistence`   |
| `UserFrosting\Sprinkle\Account\Database\Models\Interfaces\RoleInterface`          | `UserFrosting\Sprinkle\Account\Database\Models\Role`          |
| `UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface`          | `UserFrosting\Sprinkle\Account\Database\Models\User`          |
| `UserFrosting\Sprinkle\Account\Database\Models\Interfaces\VerificationInterface`  | `UserFrosting\Sprinkle\Account\Database\Models\Verification`  |

## Overwriting existing map

It is possible for any sprinkle to overwrite the default mapping in a service provider. Then every time `UserInterface` is referenced for example, your model will actually be injected instead of the default `User` Model.  

**app/src/ServicesProvider/ModelsServices.php**:
```php
namespace UserFrosting\Sprinkle\MySprinkle\ServicesProvider;

use UserFrosting\ServicesProvider\ServicesProviderInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\MySprinkle\Database\Models\Members;

/**
 * Map models interface to the class.
 *
 * Note both class are map using class-string, since Models are not instantiated
 * by the container in the Eloquent world.
 */
class ModelsService implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            UserInterface::class => \DI\autowire(Members::class),
        ];
    }
}
```

> [!NOTE]
> Keep in mind the last Sprinkle to be loaded, via the dependency tree, will be the last one to take effect. For example, if *Site* depends on *Feature* which depend on *Account*, and they each associate a custom model to `UserInterface` interface, the **Site** version will be used.


Note that it's not just database models that you can dynamically remap (though they are the most common use case!) Any class references that haven't been hardcoded can be dynamically remapped in another Sprinkle's service. You can learn more about services in [Chapter 15](/services).
