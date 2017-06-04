---
title: Application Lifecycle
metadata:
    description: Each Sprinkle may define a bootstrapper class that allows it to hook into various stages of the UserFrosting application lifecycle.
taxonomy:
    category: docs
---

## Bootstrapper class

At the base level of each Sprinkle, you may optionally define a bootstrapper class.  The name of the class must be the same as the name of the Sprinkle directory, but in [StudlyCaps](https://laravel.com/api/5.2/Illuminate/Support/Str.html#method_studly).

The initialization class must implement the `\UserFrosting\Sprinkle\Core\Initialize\Sprinkle` abstract class, in particular, the `init` method.  UserFrosting's Sprinkle Manager will automatically run the code in `init` when it loads the Sprinkle.   For example, the `Account` Sprinkle's initialization class looks like this:

```
namespace UserFrosting\Sprinkle\Account;

use UserFrosting\Sprinkle\Account\ServicesProvider\AccountServicesProvider;
use UserFrosting\Sprinkle\Core\Initialize\Sprinkle;

class Account extends Sprinkle
{
    /**
     * Register Account services.
     */
    public function init()
    {
        $serviceProvider = new AccountServicesProvider();
        $serviceProvider->register($this->ci);
    }
}
```

In general, a Sprinkle's initialization class should not need to do much more beyond registering your Sprinkle's [service provider class](/services).
