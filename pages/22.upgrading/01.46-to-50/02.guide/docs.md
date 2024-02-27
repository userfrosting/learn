---
title: Upgrade Guide
metadata:
    description: Upgrade guide from version 4.6.x to 5.0.x
taxonomy:
    category: docs
---

Upgrading an existing sprinkle from UserFrosting 4 to UserFrosting 5 can unfortunately be a difficult task. It all depends of how must customization of the core code you have done. Very basic sites will be easy to upgrade, very complex one could be a nightmare. However, it's for the greater good. UserFrosting 5 is more modern and uses new techniques 

[notice=warning]This guide contains the most important changes and action you need to take to upgrade your UserFrosting 4 Sprinkle. However, **it's far from complete**, as there are too many changes to document. Make sure you have a backup of your existing application and your **development database** before starting. 

If you spot anything missing, don't hesitate to contribute to this page via the [*edit this page*](https://github.com/userfrosting/learn/blob/5.0-beta/pages/22.upgrading/01.46-to-50/02.guide/docs.md) button at the top.[/notice]

## Before you start

The upgrade path for your UserFrosting 4 sprinkle will depend on how many features you're using. The good new is, the database structure is the same, and the frontend is 90% the same. 

To begin the upgrade journey, **the first you should do is a [fresh install](/installation) of UserFrosting 5**. This is important, as it will allow you to familiarize yourself with the new structure, as well as validate that your [local development environment](/background/develop-locally-serve-globally) is up to date and meet the [minimum requirements](/installation/requirements).

Once you have a functioning vanilla version of UserFrosting 5, you can begin upgrading your sprinkle.

[notice]While the database structure is mostly the same between V4 and V5, it is highly recommended you keep a backup of your existing application and your **development database**.[/notice]

## Upgrading your sprinkle structure

As seen on the [previous page](/upgrading/46-to-50/changelog), one of the biggest change is the app structure. It's recommended before you start to head over to [Chapter 3 - App Structure](/structure), to learn more about this new structure. 

Once you're familiar with the new structure, it's time to move thing around so your sprinkle meet the new structure. There's two options here : You can either start from scratch from the [Skeleton repo](/structure/introduction#the-app-skeleton-your-project-s-template), *or* you can manually upgrade in place.

The first option is easier as the base has been setup for you, and you can start right away moving your code into the skeleton created from the sprinkle template, in a new git repository. However, it's drawback is you'll probably lose your Git History. The second option is harder, but since you're starting from your existing code, you'll keep your git history if your code is saved on Github for example.

### Option 1 - Start over from the Skeleton

To start with this option, the first step is to create a [fresh install](/installation) from the app skeleton. You'll then have an empty sprinkle with the new structure, ready to copy your code into it. However there's some step you need to follow before going further.

#### Update `composer.json`

The Skeleton comes with a basic `composer.json`. This defines the PSR-4 autoload namespace to `UserFrosting\App\`. You should edit this file to fit your sprinkle, as well as use the **same namespace as your old sprinkle**.

[notice]**Important :** To make sure your migrations and database data are preserved, the app namespace must be the same![/notice]

You'll also need to update the namespace in every existing class, `index.php` and `bakery`. See [Customizing Your Sprinkle](/sprinkles/customize#custom-namespace-and-name) for more information.

Once this is done, you can run `composer update` to fetch dependencies.

#### Edit the Recipe

Next up you should familiarize yourself with the [Sprinkle Recipe](/sprinkles/recipe#name). Right now, the only thing you must update is the sprinkle name, but we'll come back later to the recipe.

#### Moving your code

Last part is to move your code from `app/sprinkles/{YourSprinkle}/` from your old install (usually from `app/sprinkles/{YourSprinkle}/`) to `app/` in the new directory.

At this point you can skip to [Upgrading components](/upgrading/46-to-50/guide#upgrading-components)

### Option 2 - Upgrade in place

To start with this option, you should already be familiar with UserFrosting 5 directory structure, as it involves moving some directory around and editing some files. It also assume your code (your sprinkle) was located (and not as a community Sprinkle for example) You should have UserFrosting 5 Skeleton repo on hand to copy some files.

#### Moving files and directory

- Delete : 
  - `composer.json` 
  - `bakery`
  - `build/`
  - `vagrant/`
  - `public/index.php`
  - `app/system/`
  - `app/tests/`
  - `app/defines.php`
  - `app/.htaccess`
  - `app/sprinkles/account/`
  - `app/sprinkles/admin/`
  - `app/sprinkles/core/`
  - `app/sprinkles.example.json`
  - `app/sprinkles.json`
- Move everything inside `app/sprinkles/{YourSprinkle}/` to `app/`, and delete the empty `app/sprinkles/` directory. 
- Copy from the skeleton, replacing existing files : 
  - `/public/index.php`
  - `/bakery`
  - `/package.json`
  - `/webpack.config.js`
  - `/webpack.entries.js`
  - `docker-compose.yml`
  - `docker/`
  - `.gitignore`

#### Updating `composer.json`

In UserFrosting 4, your `composer.json` was located in `app/sprinkles/{YourSprinkle}/composer.json`. It was merged with the main composer file and the dependent sprinkles on build. With UserFrosting 5, there's only one `composer.json`: Yours. And it lives in `/composer.json`.

This step requires you to merge your `composer.json` with UF5's. Start by moving your file, which should now be located at `/app/sprinkles.json` to `/composer.json`. 

Next, you'll need to update some dependencies. Open `composer.json` **from the skeleton**, and copy every dependencies from `required` and `require-dev` from it to your `composer.json`.

You should also update the path for the `autoload` and `autoload-dev` section to point to `app/src/` and `app/tests/` respectively.

[notice]**Important :** Don't change the namespace if you want to keep the existing migration![/notice]

Once this is done, you can run `composer update` to fetch dependencies.

#### The Recipe

The next step is to create your [sprinkle recipe](/sprinkles/recipe#name).

Start by : 
- Copy `app/src/MyApp.php` from the skeleton into your app, inside `app/src/`
- You can rename `MyApp` to whatever you want. Simply don't forget to adapt the next steps with the name you chose.
- Change the namespace inside your `app/src/MyApp.php` to the namespace you use in your `composer.json`

We'll update the rest of your recipe later.

#### The entry files

Finally, you'll need to update both *entry files*, aka `/bakery` and `/public/index.php`, with the correct reference to your recipe. Open both files, and replace `UserFrosting\App\MyApp` and `MyApp` with the correct reference to your recipe. See [this page](/sprinkles/customize#the-entry-files) for more information and example.

## Upgrading components

At this point your UserFrosting 5 app is _kind of ready_ to work. You simply need to upgrade every component your use. The following list might contains stuff you're not using, but you should still go through them as they contains links and tip you may need later.

Remember, this guide will give you only the big picture of what changed, pointing you to other part of the guide that goes in details with all the features. 

### Global changes

It's important to note some changes have been applied at large, and affect pretty much every PHP class, based on PHP 8 new features. These changes include:

- Extensive use of type declaration and [return types](https://dev.to/karleb/return-types-in-php-3fip)
- [Named Arguments](https://www.php.net/releases/8.0/en.php#named-arguments)
- [Constructor property promotion](https://www.php.net/releases/8.0/en.php#constructor-property-promotion)
- [Union type](https://www.php.net/releases/8.0/en.php#union-types)
- [Etc.](https://www.php.net/releases/8.0/en.php)

Keep this in mind, especially if you've extended built-in classes. Not only may these classes been renamed or moved, the method declaration might have changed even if the code of these method haven't.

### Services -> Dependency Injection

Services have been updated for UserFrosting 5. While the principle is the same, the way to register service is different. Services are now served by the new dependency injection container, PHP-DI. You should head over to the [Dependency Injection Chapter](/dependency-injection) to learn more about PHP-DI integration in UserFrosting 5 before going further. 

Your services definition must first be updated to implement `UserFrosting\ServicesProvider\ServicesProviderInterface`. For example:

**OLD:**
```php
class ServicesProvider
{
    public function register(ContainerInterface $container)
    {
        // ...
    }
}
```

**NEW:**
```php
use UserFrosting\ServicesProvider\ServicesProviderInterface;

class ServicesProvider implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            // ...
        ];
    }
}
```

You'll also need to **register your service** in your recipe. Checkout [Adding Services](/dependency-injection/adding-services) for more information.

Finally, instead or injecting the whole container and retrieving your service from it, your should inject the service directly into the class using [autowiring](/dependency-injection/the-di-container#autowiring) in the class constructor or thought [route service injection](/routes-and-controllers/controller-classes#service-injection) for example.

For example:

**OLD:**
```php
public function __construct($ci)
{
    $this->camHelper = $ci->camHelper;
}
```

**NEW:**
```php
public function __construct(protected CamHelper $camHelper): void
{

}
```

### Classmapper 

The classmapper has been removed in UF5. PHP-DI should be used instead, via the "autowire" and [interface binding](/dependency-injection/the-di-container#binding-interfaces). Existing classmapper definition should be moved inside a service, and call to the classmapper should be updated to use dependendy injection via the controller or other method. 

### Migrations

Migration are mostly the same, only the class structure as changed, as well as the need to register migration in your Sprinkle Recipe. The key points regarding migration are as follow : 

1. Up/Down return type : The `up()` and `down()` method [must now return `void`](/database/migrations#base-class)
   
2. Migration must [now implement](/database/migrations#base-class) `UserFrosting\Sprinkle\Core\Database\Migration`. Change `use UserFrosting\System\Bakery\Migration;` to `use UserFrosting\Sprinkle\Core\Database\Migration;` in every one of your migrations.
   
3. [Dependencies](/database/migrations#dependencies) must now be declared in a static property. Change `public $dependencies = [];` to `public static $dependencies = [];` in every one of your migrations.
   
4. Migration are not auto-discovered anymore. You need to add them to your sprinkle recipe, using `MigrationRecipe`. See [the migration chapter](/database/migrations#sprinkle-recipe) for more information and a detailed guide.

### Models

The only change in database model is the `$timestamps` property is not `true` by default. It used to be `false`. ` public $timestamps = true;` can be removed from your models, unless you're **not** using timestamps where you should add `public $timestamps = false;`.

### Routes

The way to register route as changed. The definition is mostly the same, however the routes are now a real PHP classes, instead of static php resources. Check out the [Registering routes](/routes-and-controllers/registering-routes) guide for more information.

To updated your routes, you should start by :

1. Move your routes from `app/routes/*` to `app/src/Routes/*`;
2. Update your routes definitions so it's a class [implementing RouteDefinitionInterface](/routes-and-controllers/registering-routes)
3. Register your routes in your Sprinkle Recipe.

A few other key point to know:
1. Route groups definition has changed. See [Slim Documentation](https://www.slimframework.com/docs/v4/objects/routing.html#route-groups) for more information
2. Controller resolution should be updated to make use of [PHP’s `::class` operator](https://www.slimframework.com/docs/v4/objects/routing.html#container-resolution)
3. Middleware must now be called by their class name. For example, `authGuard` must be updated to `UserFrosting\Sprinkle\Account\Authenticate\AuthGuard::class`

### Sprunje

The sprunje class used to accept options in the constructor. To make it easier to [inject dependencies](/dependency-injection/the-di-container), options should now be defined using the `setOptions` method. Sprunje should now be injected into controllers, 

**OLD:**
```php
public function camsList(Request $request, Response $response, array $args)
{
    // GET parameters
    $params = $request->getQueryParams();

    /** @var /UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
    $classMapper = $this->ci->classMapper;

    /** @var \UserFrosting\Sprinkle\Core\Router $router */
    $router = $this->ci->router;

    $sprunje = new CamsSprunje($classMapper, $params, $router);

    return $sprunje->toResponse($response);
}
```

**NEW:**
```php
public function camsList(Request $request, Response $response, CamsSprunje $sprunje): Response
{
    // GET parameters
    $params = $request->getQueryParams();

    return $sprunje->setOptions($params)->toResponse($response);
}
```

This also mean the **Classmapper** is not available anymore in sprunjes. You should inject the necessary classes and services into your sprunjes.

### Controllers

Simple changes have been made to controller classes : 

1. Remove `extends SimpleController`, no extension is required anymore.
2. `use Slim\Http\Request;` must be changed to `use Psr\Http\Message\ServerRequestInterface as Request;`
3. `use Slim\Http\Response;` must be changed to `use Psr\Http\Message\ResponseInterface as Response;`
4. Since the DI container is not available globally in controllers, the services you requires must be [injected in constructor](/routes-and-controllers/controller-classes#service-injection)
   1. For example, to use the `view` service, inject `Slim\Views\Twig;` 

See [Controller classes](/routes-and-controllers/controller-classes) guide for more information.

[note]If your sprinkle extends a controller class from a default sprinkle instead of `SimpleController`, note that **every** controller classes from default sprinkles have been moved, renamed and rewritten as *Action classes*. Make sure to check out the sprinkle source code to find out how to update your sprinkle.[/note]

### Bakery

Simple changes have been made to bakery commands : 

1. Command must extend `Symfony\Component\Console\Command\Command` instead of `UserFrosting\System\Bakery\BaseCommand`
2. Service should be injected through the class constructor (don't forget to call the parent constructor) or via [attribute injection](https://php-di.org/doc/attributes.html#inject).
3. Add `use UserFrosting\Bakery\WithSymfonyStyle;` Trait to access `$this->io`
4. Command should return `return self::SUCCESS;` or `return self::FAILLURE;`

The biggest changes is you're now required to **register your command in your sprinkle recipe**. Check out [Bakery CLI](/cli/custom-commands#command-class-template) chapter for more information.

Also note that the `create-admin` command has been renamed `create:admin-user`. If your were adding custom command to "bake" command, you can now use the [Extending Aggregator Commands](/cli/extending-commands) guide to achieve this more easily.

### Resources Stream / Locator

Not must as changed regarding the Resource Locator. Refer to the [Locator Service](/advanced/locator) page for more information. Note however that the two stream below have been renamed:

1. log -> logs
2. session -> sessions

### Template

Three points are to be considered when working with templates:

First, sprinkles have been renamed. If you're extending a default sprinkle, you'll need to update the sprinkle reference in the `extends` tag. For example: `{% extends "@admin/pages/activities.html.twig" %}` => `{% extends "@admin-sprinkle/pages/activities.html.twig" %}`. Check the table below for a list of sprinkle identifier.
   
 | Name             | Slug             |
 |------------------|------------------|
 | Admin Sprinkle   | admin-sprinkle   |
 | AdminLTE Theme   | adminlte-theme   |
 | Account Sprinkle | account-sprinkle |
 | Core Sprinkle    | core-sprinkle    |

In a similar way, some Twig templates have been moved to the "AdminLTE" sprinkle. Be sure to check the new structure if you're extending templates with the `extends` tag. Some templates file previously in the Admin Sprinkle might be in the AdminLTE sprinkle now.

FontAwesome has also been updated, and references to icon must also be updated : `fa` -> `fas`

### Misc

1. Facades (Translator, etc.) are no longer available. The corresponding service should be injected properly now.
2. Router `PathFor` has been changed. To generate a route based on it's name, inject `Slim\Interfaces\RouteParserInterface` and use `urlFor` method.  
3. Exceptions have changed, especially HTTP ones. For example : `use UserFrosting\Support\Exception\ForbiddenException;` => `use UserFrosting\Sprinkle\Account\Exceptions\ForbiddenException;` Check out [Exception and Error Handling](/advanced/error-handling#default-exceptions) guide for more information.

### Login redirect

If your sprinkle was implementing a custom post-login destination, be sure to [check out the updated recipe](/recipes/custom-login-page#changing-the-post-login-destination).

### Testing

If your sprinkle had automated testing : 
1. [Create a new Sprinkle Test case](/testing/writting-tests/testcase)
2. `setupTestDatabase` has been removed, it's no longer necessary
3. `UserFrosting\Sprinkle\Core\Tests\RefreshDatabase` is now `UserFrosting\Sprinkle\Core\Testing\RefreshDatabase`
5. `TestDatabase` is removed
6. `withTestUser` usage [has been updated](/testing/writting-tests/traits#withtestuser)
7. [Factories have been overhauled](/testing/writting-tests/factories)

## Assets

Asset management have been completely changed. Instead of a custom solution, UserFrosting 5 now uses [Symfony's Webpack Encore](https://github.com/symfony/webpack-encore) to handle and build frontend assets. Your first stop should the [Asset Chapter](/asset-management) or the documentation for in-dept guide for this new system.

The key points here are :
1. Your `asset-bundles.json` must be replaced with a new [entrypoint and entries](/asset-management/asset-bundles).
2. Rendering of assets (css and js entries) [must be updated](/asset-management/asset-bundles#rendering-entrypoints). Checkout the `content/scripts_site.html.twig` and `content/stylesheets_site.html.twig` files from the skeleton repo for an example.
3. Static assets must be [copied to the built directory](/asset-management/basic-usage)
