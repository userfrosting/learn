---
title: Customizing the Skeleton
description: The default skeleton can easily be changed
obsolete: true
---

## Custom Namespace

The default namespace provided in the Skeleton, `UserFrosting\App\{Path}`, can easily be changed in your app.

Previous version of UserFrosting required a strict namespace (`UserFrosting\Sprinkle\{SprinkleName}\{Path}`). Starting with UserFrosting 5, the namespace can be whatever you want. It doesn't event need to include **UserFrosting** or **Sprinkle**. It *can* still be like this, but it could also be `YourName/YourApp/{Path}` or `App/{Path}`.

In this example, we'll replace the default namespace for `UserFrosting\Sprinkle\Pastries`.

### composer.json

First thing to do is to replace the namespace in your `composer.json` in **autoload** and **autoload-dev**:

**composer.json**:
```json
//...
    "autoload": {
        "psr-4": {
            "UserFrosting\\Sprinkle\\Pastries\\": "app/src"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "UserFrosting\\Tests\\Sprinkle\\Pastries\\": "app/tests/"
        }
    }
// ...
```

Now we need to update **Composer** so our new [PSR4 mapping](http://www.php-fig.org/psr/psr-4/#3-examples) is picked up. From the command line, run in the **root directory** of your UserFrosting project:

```bash
$ composer update
```

### Class reference

You can now find and replace every reference of `UserFrosting\App\` for `UserFrosting\Sprinkle\Pastries` in your classes. Don't forget that you'll need to replace it in :

- Namespace at the top of every classes (`namespace UserFrosting\App\{...};` -> `namespace UserFrosting\Sprinkle\Pastries\{...};`);
- Every class importation (`use UserFrosting\App\{...};` -> `use UserFrosting\Sprinkle\Pastries\{...};`);
- Some files:
  - `/public/index.php`;
  - `/bakery`;
- Etc.

## Cleanup unused code

The Skeleton comes with some default pages and code we won't necessary need. These files and directory can (***optionally***) be safely deleted :

- `app/assets/images/`
- `app/src/Bakery/`
- `app/src/Controller/AppController.php`
- `app/src/MyServices.php`
- `app/templates/footer-nav.html.twig`
- `app/templates/legal.html.twig`
- `app/templates/main-nav.html.twig`
- `app/templates/privacy.html.twig`
- `app/pages/about.html.twig`
- `app/pages/index.html.twig`
- `app/pages/legal.html.twig`
- `app/pages/privacy.html.twig`
- `tests/Controller/AppControllerTest.php`

### Recipe

The default Recipe can also be edited to remove the `MyServices` service from `getServices()`, remove `BakeryRecipe` implementation and remove `getBakeryCommands()` method.

The final version of `app/src/MyApp.php` will look similar to this :
```php
<?php

namespace UserFrosting\Sprinkle\Pastries;

use UserFrosting\Sprinkle\Account\Account;
use UserFrosting\Sprinkle\Admin\Admin;
use UserFrosting\Sprinkle\Core\Core;
use UserFrosting\Sprinkle\Core\Sprinkle\Recipe\MigrationRecipe;
use UserFrosting\Sprinkle\Core\Sprinkle\Recipe\SeedRecipe;
use UserFrosting\Sprinkle\Pastries\Database\Migrations\V100\PastriesPermissions;
use UserFrosting\Sprinkle\Pastries\Database\Migrations\V100\PastriesTable;
use UserFrosting\Sprinkle\Pastries\Database\Seeds\DefaultPastries;
use UserFrosting\Sprinkle\SprinkleRecipe;
use UserFrosting\Theme\AdminLTE\AdminLTE;

class MyApp implements SprinkleRecipe, MigrationRecipe, SeedRecipe
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Pastries';
    }

    /**
     * {@inheritdoc}
     */
    public function getPath(): string
    {
        return __DIR__ . '/../';
    }

    /**
     * {@inheritdoc}
     */
    public function getSprinkles(): array
    {
        return [
            Core::class,
            Account::class,
            Admin::class,
            AdminLTE::class,
        ];
    }

    /**
     * Returns a list of routes definition in PHP files.
     *
     * @return string[]
     */
    public function getRoutes(): array
    {
        return [
            MyRoutes::class,
        ];
    }

    /**
     * Returns a list of all PHP-DI services/container definitions files.
     *
     * @return string[]
     */
    public function getServices(): array
    {
        return [];
    }

    /**
     * Return an array of all registered Migrations.
     *
     * @return string[]
     */
    public function getMigrations(): array
    {
        return [
            PastriesTable::class,
            PastriesPermissions::class,
        ];
    }

    /**
     * {@inheritDoc}
     *
     * @codeCoverageIgnore
     */
    public function getSeeds(): array
    {
        return [
            DefaultPastries::class,
        ];
    }
}
```

### Routes
One caveat of removing the default Skeleton pages, the index page won't exist anymore. Your app won't have an entry page (eg. https://yourapp/) and an error will be thrown. One way to get around this problem is to replace the default routes with a redirect, from `/` to the `/dashboard`. For example :

**app/src/MyRoutes.php**:
```php
<?php

namespace UserFrosting\Sprinkle\Pastries;

use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use UserFrosting\Routes\RouteDefinitionInterface;
use UserFrosting\Sprinkle\Account\Authenticate\AuthGuard;
use UserFrosting\Sprinkle\Pastries\Controller\PastriesPageAction;

class MyRoutes implements RouteDefinitionInterface
{
    public function register(App $app): void
    {
        $app->group('/pastries', function (RouteCollectorProxy $group) {
            $group->get('', PastriesPageAction::class)->setName('pastries');
        })->add(AuthGuard::class);

        // Redirect root to dashboard
        $app->redirect('/', '/dashboard', 301)->setName('index'); // <-- Add this
    }
}
```
