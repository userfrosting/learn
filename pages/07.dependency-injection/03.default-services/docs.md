---
title: Default Services
metadata:
    description: UserFrosting's default services provide most of the tools needed to build a basic web application.
taxonomy:
    category: docs
---

As mentioned in the last section, each sprinkle can set up its own services through **service providers**. The [bundled sprinkles](/structure/sprinkles#bundled-sprinkles) set up many services that are essential to UserFrosting's functionality. These services can be found in the `src/ServicesProvider/` subdirectories in each Sprinkle's directory. 

But this is just the tip of the iceberg, since _Autowiring_ is also used throughout the source code to inject other types of classes pretty much everywhere.

This is a short list of the most important services defined in each sprinkle. The fully qualified class names are used, so you can easily **inject** them in your controller or other classes.

Third party services are also used directly throughout the code. They can be injected using their original fully qualified class name, while still being configured in a sprinkle.

## Core Sprinkle Services & Framework Services

### `UserFrosting\Alert\AlertStream`

This service handles the [alert message stream](/advanced/alert-stream)), sometimes known as "flash messages". (See Chapter 18 for more information.)

### `Illuminate\Cache\Repository as Cache`

Creates an instance of a Laravel [Cache](https://laravel.com/docs/8.x/cache). See [Chapter 17](/advanced/caching) for more information.

### `UserFrosting\Config\Config`

Constructs a `Config` object, which [processes and provides a merged repository for configuration files](/configuration/config-files) across all loaded sprinkles. Additionally, it imports [Dotenv](https://github.com/vlucas/phpdotenv) to allow automatically loading environment variables from `.env` file.

The `config` service also builds the `site.uri.public` config variable from the component values specified in the configuration.

### `UserFrosting\Sprinkle\Core\Csrf\CsrfGuard`

Constructs the [CSRF Guard](https://github.com/slimphp/Slim-Csrf) middleware, which mitigates cross-site request forgery attacks on your users. See [Chapter 2](/background/security) for more information on security features.

### `UserFrosting\Sprinkle\Core\Database\Migrator\Migrator`

Creates an instance of `Migrator`, which runs your database [migrations](/database/migrations).

### `UserFrosting\Sprinkle\Core\Log\DebugLogger`

Monolog `Logger` object for sending debug print statements and data to `logs/debug.log`. Can also be accessed via the [`Debug` facade](/troubleshooting/debugging#debug-statements).

### `UserFrosting\Sprinkle\Core\Log\ErrorLogger`

Monolog `Logger` object for sending non-fatal error information from custom error handlers to `logs/userfrosting.log`.

### `UserFrosting\Sprinkle\Core\Log\MailLogger`

Monolog `Logger` object for sending detailed SMTP mail server information from the `mailer` service to `logs/userfrosting.log`. Mail logging will only occur if `debug.smtp` is set to `true`.

### `UserFrosting\Sprinkle\Core\Log\QueryLogger`

Monolog `Logger` object for logging successfully completed database queries to `logs/userfrosting.log`.

### `UserFrosting\Sprinkle\Core\Mail\Mailer`

Creates an instance of `Mailer`, which serves as a UF-compatible wrapper for a [PHPMailer](https://github.com/PHPMailer/PHPMailer) object. See [Chapter 14](/mail) for more information.

### `UserFrosting\Sprinkle\Core\Throttle\Throttler`

Creates a `Throttler` object, which handles [request throttling](/routes-and-controllers/client-input/throttle) for different routes. This service will automatically register any throttling rules defined in the `throttles` key of your configuration.

### `Slim\Interfaces\RouteParserInterface`

See [Chapter 8](/routes-and-controllers) for more information about defining routes, and the [Slim Documentation](https://www.slimframework.com/docs/v4/objects/routing.html#route-names) on how to use the Route Parser.

### `UserFrosting\Session\Session`

Sets up UserFrosting's `Session` object, which serves as a wrapper for the `$_SESSION` superglobal. `Session` will use file- or database-based storage for sessions, depending on your configuration setting for `session.handler`. Session handlers are provided by [Laravel's session handlers](https://laravel.com/docs/8.x/session#configuration), which implement PHP's [`SessionHandlerInterface`](http://php.net/SessionHandlerInterface).

Please note that when using file-based sessions, UserFrosting places sessions in its own `/app/sessions` directory instead of PHP's default session directory.

[notice=warning]Use UserFrosting's `Session` service instead of PHP's `$_SESSION` superglobal in your code for proper functionality.[/notice]

### `Slim\Views\Twig`

Sets up the Twig View object, which is implemented by the [Slim Twig-View](https://github.com/slimphp/Twig-View) project. Turns on caching and/or debugging depending on the settings for `cache.twig` and `debug.twig`, respectively. Also registers the UserFrosting's `CoreExtension`
extension (`UserFrosting\Sprinkle\Core\Twig\CoreExtension`), which provides some additional functions, filters, and global variables for UserFrosting.

See [Templating with Twig](/templating-with-twig) for more information about Twig and the custom functions, filters, and variables that UserFrosting defines.

### `UserFrosting\I18n\Translator`

Sets up the `Translator` object (`UserFrosting\I18n\Translator`) for translation, localization, and internationalization of your site's contents. See [Chapter 17](/i18n) for more information.

### `UserFrosting\UniformResourceLocator\ResourceLocatorInterface`

An instance of our own [Uniform Resource Locator class](https://github.com/userfrosting/framework/tree/5.1/src/UniformResourceLocator#readme), which provides a unified method of accessing Sprinkle entities via streams.

See [Chapter 18](/advanced/locator) for more information.

### `UserFrosting\Sprinkle\SprinkleManager`

The `SprinkleManager` can be used to get a list of all sprinkles currently loaded, get the main sprinkle, test if a sprinkle is available, etc.

## Account Sprinkle Services

### `UserFrosting\Sprinkle\Account\Authenticate\Authenticator`

Creates an instance of `Authenticator`, which handles user authentication and logins. See [Chapter 10](/users/user-accounts#authentication-and-authorization) for more information.

### `UserFrosting\Sprinkle\Account\Authenticate\AuthGuard`

The `AuthGuard` middleware, which is bound to routes which require authentication to access ("protected routes"). See [Chapter 10](/users/user-accounts#authentication-and-authorization) for more information.

### `UserFrosting\Sprinkle\Account\Authenticate\GuestGuard`

The `GuestGuard` middleware, which is bound to routes that require a guest (non logged-in user). See [Chapter 10](/users/user-accounts#authentication-and-authorization) for more information.

### `UserFrosting\Sprinkle\Account\Log\AuthLogger`

Monolog `Logger` object for logging detailed information about access control checks. See [Chapter 10](/users/access-control) for more information about access control. Note that access control checks will only be logged if `debug.auth` is set to `true` in the configuration.

### `UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager` 

*Associated Interface : `UserFrosting\Sprinkle\Account\Authorize\AuthorizationManagerInterface`*

Creates an instance of `AuthorizationManager`, which handles access control checks via the `checkAccess` method. This service also defines several default access condition callbacks. More information, and a complete list of default access condition callbacks, can be found in [Chapter 10](/users/access-control).

### `UserFrosting\Sprinkle\Account\Authenticate\Hasher`

Creates an instance of `Hasher`, which handles password hashing and validation.

### `UserFrosting\Sprinkle\Account\Log\UserActivityLogger`

Sets up a Monolog `Logger` object, which uses `UserFrosting\Sprinkle\Account\Log\UserActivityDatabaseHandler` and `UserFrosting\Sprinkle\Account\Log\UserActivityProcessor` to allow logging of user activities to the `activities` database table. Monolog makes it easy to swap to other storage solutions such as Redis or Elastic Search.
