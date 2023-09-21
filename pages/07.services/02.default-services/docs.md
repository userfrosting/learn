---
title: Default Services
metadata:
    description: UserFrosting's default services provide most of the tools needed to build a basic web application.
taxonomy:
    category: docs
---
[plugin:content-inject](/modular/_update5.0)

As mentioned in the last section, each Sprinkle can set up its own services through a **service provider** class. The default `core` and `account` Sprinkles set up many services that are essential to UserFrosting's functionality. These classes can be found in the `src/ServicesProvider/` subdirectories in each Sprinkle's directory.

## Core Services

### alerts

This service handles the [alert message stream](/routes-and-controllers/alert-stream), sometimes known as "flash messages". See [Section 9](/routes-and-controllers/alert-stream) for more information.

### assetLoader

This service handles requests for [raw assets](/asset-management/asset-bundles#raw-assets) made to the application. It locates the appropriate file for a given url, and builds the response containing the contents of the asset, along with setting headers for MIME type and length.

### assets

Constructs the `Assets` object (namespace `UserFrosting\Assets\Assets`), which is responsible for loading information about assets (Javascript, CSS, images, etc) required by each page and constructing the appropriate HTML tags. See [Asset Management](/asset-management/asset-bundles) for more information.

### cache

Creates an instance of a Laravel [Cache](https://laravel.com/docs/8.x/cache). See [Chapter 17](/advanced/caching) for more information.

### checkEnvironment

Constructs the `CheckEnvironment` object (namespace `UserFrosting\Sprinkle\Core\Util\CheckEnvironment`), which does some basic checks in a new UF installation to make sure that the minimum requirements are met, directory permissions are set, etc.

### classMapper

Constructs the `ClassMapper` object (namespace `UserFrosting\Sprinkle\Core\Util\ClassMapper`), which provides dynamic class mapping in your controllers and classes. See [Advanced Dev Features](/advanced/class-mapper) for more information on dynamic class mapping.

### cli

The CLI service return a boolean value. True means the APP is in CLI mode, either in a test of Bakery command.

### config

Constructs a `Repository` object (namespace `UserFrosting\Support\Repository\Repository`), which [processes and provides a merged repository for the configuration files](/configuration/config-files) across all loaded Sprinkles. Additionally, it imports the [Dotenv](https://github.com/vlucas/phpdotenv) to allow automagically loading environment variables from `.env` file.

The `config` service also builds the `site.uri.public` config variable from the component values specified in the configuration.

### csrf

Constructs the [CSRF Guard](https://github.com/slimphp/Slim-Csrf) middleware, which mitigates cross-site request forgery attacks on your users. See [Chapter 2](/background/security) for more information on security features.

### db

Sets up the [database](/database).

### debugLogger

Monolog `Logger` object for sending debug print statements and data to `logs/debug.log`. Can also be accessed via the [`Debug` facade](/troubleshooting/debugging#debug-statements).

### errorHandler

Sets up a `ExceptionHandlerManager` object, which is used as a [custom error handler](http://www.slimframework.com/docs/v3/handlers/error.html#custom-error-handler) for UF's Slim application. It then registers the custom handlers for `HttpException`, `PDOException`, and `phpmailerException`. See [Chapter 17](/advanced/error-handling) for more information on custom exceptions and exception error handlers.

### errorLogger

Monolog `Logger` object for sending non-fatal error information from custom error handlers to `logs/userfrosting.log`.

### factory

Provide access to factories for the rapid creation of [test](/testing/writting-tests/factories) objects.

### filesystem

Provide access to the filesystem for file handling. Include support for multiple cloud based storage solution. See [File Storage](/advanced/storage) for more information.

### locale

Creates an instance of `SiteLocale` (namespace `UserFrosting\Sprinkle\Core\I18n\SiteLocale`), which provides helper methods for the locale system. See [Chapter 16](/i18n) for more information about locale and the [API Documentation](http://api.userfrosting.com) for available methods.

### mailer

Creates an instance of `Mailer` (namespace `UserFrosting\Sprinkle\Core\Mail\Mailer`), which serves as a UF-compatible wrapper for a [PHPMailer](https://github.com/PHPMailer/PHPMailer) object.

See [Chapter 14](/mail) for more information.

### mailLogger

Monolog `Logger` object for sending detailed SMTP mail server information from the `mailer` service to `logs/userfrosting.log`. Mail logging will only occur if `debug.smtp` is set to `true`.

### migrator

Creates an instance of `Migrator` (namespace `UserFrosting\Sprinkle\Core\Database\Migrator\Migrator`), which runs your database [migrations](/database/migrations).

### notFoundHandler

Implements Slim's [Custom Not Found handler](http://www.slimframework.com/v3/docs/handlers/not-found.html), causing the application to return a 404 not found page.

### phpErrorHandler

Alias for the [`errorHandler`](#errorhandler) service.

### queryLogger

Monolog `Logger` object for logging successfully completed database queries to `logs/userfrosting.log`.

### router

Overrides Slim's default `router`, replacing their `Router` object with a `UserFrosting\Sprinkle\Core\Router` object. Our custom `Router` class allows for routes to be overridden and redefined in Sprinkles.

See [Chapter 9](/routes-and-controllers) for more information about defining routes.

### seeder

Creates an instance of `Seeder` (namespace `UserFrosting\Sprinkle\Core\Database\Seeder\Seeder`), which runs your database [seeds](/database/seeding).

### session

Sets up UserFrosting's `Session` object (`UserFrosting\Session\Session`), which serves as a wrapper for the `$_SESSION` superglobal. `Session` will use file- or database-based storage for sessions, depending on your configuration setting for `session.handler`. Session handlers are provided by [Laravel's session handlers](https://laravel.com/docs/8.x/session#configuration), which implement PHP's [`SessionHandlerInterface`](http://php.net/SessionHandlerInterface).

Please note that when using file-based sessions, UserFrosting places sessions in its own `/app/sessions` directory instead of PHP's default session directory.

[notice=warning]Use UserFrosting's `session` service (`$container->session`) instead of PHP's `$_SESSION` superglobal in your code for proper functionality.[/notice]

### throttler

Creates a `Throttler` object, which handles [request throttling](/routes-and-controllers/client-input/throttle) for different routes. This service will automatically register any throttling rules defined in the `throttles` key of your configuration.

### translator

Sets up the `Translator` object (`UserFrosting\I18n\Translator`) for translation, localization, and internationalization of your site's contents. See [Chapter 16](/i18n) for more information.

### view

Sets up the Twig View object, which is implemented by the [Slim Twig-View](https://github.com/slimphp/Twig-View) project. Turns on caching and/or debugging depending on the settings for `cache.twig` and `debug.twig`, respectively. Also registers the UserFrosting's `CoreExtension`
extension (`UserFrosting\Sprinkle\Core\Twig\CoreExtension`), which provides some additional functions, filters, and global variables for UserFrosting.

See [Templating with Twig](/templating-with-twig) for more information about Twig and the custom functions, filters, and variables that UserFrosting defines.

## Account Services

### assets

The Account Sprinkle extends the core `assets` service, to add search paths for any assets loaded in a user's custom theme.

### classMapper

The Account Sprinkle extends the core `classMapper` service, and registers the following model identifiers:

| Identifier       | Model                                                         |
| ---------------- | ------------------------------------------------------------- |
| `user`           | `UserFrosting\Sprinkle\Account\Database\Models\User`          |
| `group`          | `UserFrosting\Sprinkle\Account\Database\Models\Group`         |
| `role`           | `UserFrosting\Sprinkle\Account\Database\Models\Role`          |
| `permission`     | `UserFrosting\Sprinkle\Account\Database\Models\Permission`    |
| `activity`       | `UserFrosting\Sprinkle\Account\Database\Models\Activity`      |
| `password_reset` | `UserFrosting\Sprinkle\Account\Database\Models\PasswordReset` |
| `verification`   | `UserFrosting\Sprinkle\Account\Database\Models\Verification`  |
| `persistence`    | `UserFrosting\Sprinkle\Account\Database\Models\Persistence`   |

### errorHandler

The Account Sprinkle extends the core `errorHandler` service, to add the following custom exception handlers:

| Exception                                                                       | Handler                                                                       |
| ------------------------------------------------------------------------------- | ----------------------------------------------------------------------------- |
| `UserFrosting\Support\Exception\ForbiddenException`                             | `UserFrosting\Sprinkle\Account\Handler\ForbiddenExceptionHandler`             |
| `UserFrosting\Sprinkle\Account\Authenticate\Exception\AuthExpiredException`     | `UserFrosting\Sprinkle\Account\Error\Handler\AuthExpiredExceptionHandler`     |
| `UserFrosting\Sprinkle\Account\Authenticate\Exception\AuthCompromisedException` | `UserFrosting\Sprinkle\Account\Error\Handler\AuthCompromisedExceptionHandler` |

### view

The Account Sprinkle extends the core `view` service, adding the `AccountExtension` Twig extension (`UserFrosting\Sprinkle\Account\Twig\AccountExtension`). This extension adds the following:

#### Functions

- `checkAccess`: Twig wrapper for the `authorizer` service's `checkAccess` method.

#### Variables

- `current_user`: Twig wrapper for the `currentUser` service.

The extended `view` also adds search paths for any template files loaded in a user's custom theme.

### authenticator

Creates an instance of `Authenticator` (`UserFrosting\Sprinkle\Account\Authenticate\Authenticator`), which handles authenticating and logging in users. See [Chapter 7](/users/user-accounts#authentication-and-authorization) for more information.

### authGuard

Sets up the `AuthGuard` middleware, which is bound to routes that require authentication to access ("protected routes"). See [Chapter 7](/users/user-accounts#authentication-and-authorization) for more information.

### authLogger

Monolog `Logger` object for logging detailed information about access control checks. See [Chapter 7](/users/access-control) for more information about access control. Note that access control checks will only be logged if `debug.auth` is set to `true` in the configuration.

### authorizer

Creates an instance of `AuthorizationManager` (`UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager`), which handles access control checks via the `checkAccess` method. This service also defines several default access condition callbacks. More information, and a complete list of default access condition callbacks, can be found in [Chapter 7](/users/access-control).

### currentUser

Sets up the `User` object (`UserFrosting\Sprinkle\Account\Database\Models\User`) for the currently logged-in user. If there is no logged-in user, it returns `null`. It also loads the locale and theme for the current user, if set.

### locale

Extends the _Core_ `SiteLocale` instance (with namespace `UserFrosting\Sprinkle\Account\I18n\SiteLocale`) to add account related info to the service.

### passwordHasher

Creates an instance of `Hasher` (`UserFrosting\Sprinkle\Account\Authenticate\Hasher`), which handles password hashing and validation.

### redirect.onAlreadyLoggedIn

Returns a callback that redirects the client when they attempt to perform certain guest actions, but they are already logged in. For example, if they attempt to visit the registration or login pages when they are already signed in, this service will be used to redirect them to an appropriate landing page.

### redirect.onLogin

Returns a callback that sets the `UF-Redirect` header in the response. This callback is automatically invoked in the `AccountController::login` method. The `UF-Redirect` header is used by client-side code to determine where to redirect a given user after they log in.

See [_Changing the post-login destination_](/recipes/custom-login-page#changing-the-post-login-destination) for an example on how to customixzed this in your own sprinkle.

### repoPasswordReset

Sets up a `PasswordResetRepository` object (`UserFrosting\Sprinkle\Account\Repository\PasswordResetRepository`), which handles token creation, verification, and expiration for password reset requests.

### repoVerification

Sets up a `VerificationRepository` object (`UserFrosting\Sprinkle\Account\Repository\VerificationRepository`), which handles token creation, verification, and expiration for new account verification requests.

### userActivityLogger

Sets up a Monolog logger, which uses `UserFrosting\Sprinkle\Account\Log\UserActivityDatabaseHandler` and `UserFrosting\Sprinkle\Account\Log\UserActivityProcessor` to allow logging of user activities to the `activities` database table. By using Monolog, it makes it easy to swap other storage solutions such as Redis or Elastic Search.

## System services

There are a few services which are loaded outside of the Sprinkle system, in UserFrosting's `UserFrosting\System\ServiceProvider` service provider. These include:

### eventDispatcher

An instance of `RocketTheme\Toolbox\Event\EventDispatcher`, which itself extends the Symfony `EventDispatcher` class. This is used by Sprinkles to hook into the [application lifecycle](/advanced/application-lifecycle).

### locator

An instance of our own [Uniform Resource Locator class](https://github.com/userfrosting/uniformresourcelocator), which provides a unified method of accessing Sprinkle entities via [streams](https://webmozart.io/blog/2013/06/19/the-power-of-uniform-resource-location-in-php/).

See [Chapter 17](/advanced/locator) for more information.

### sprinkleManager

An instance of `UserFrosting\System\Sprinkle\SprinkleManager`. Handles registration of Sprinkle bootstrapper classes and the loading of Sprinkle resources when UserFrosting is initialized.
