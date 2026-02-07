---
title: Default Services
description: UserFrosting's default services provide most of the tools needed to build a basic web application.
---

As mentioned in the last section, each sprinkle can set up its own services through **service providers**. The [bundled sprinkles](structure/sprinkles#bundled-sprinkles) set up many services that are essential to UserFrosting's functionality. These services can be found in the `src/ServicesProvider/` subdirectories in each Sprinkle's directory.

But this is just the tip of the iceberg, since _Autowiring_ is also used throughout the source code to inject other types of classes pretty much everywhere.

This is a short list of the most important services defined in each sprinkle. The fully qualified class names are used, so you can easily **inject** them in your controller or other classes.

Third party services are also used directly throughout the code. They can be injected using their original fully qualified class name, while still being configured in a sprinkle.

## Core Sprinkle Services & Framework Services

### `UserFrosting\Alert\AlertStream`

This service handles the [alert message stream](/advanced/alert-stream), sometimes known as "flash messages". (See Chapter 18 for more information.)

### `Illuminate\Cache\Repository as Cache`

Creates an instance of a Laravel [Cache](https://laravel.com/docs/8.x/cache). See [Chapter 17](/advanced/caching) for more information.

### `UserFrosting\Config\Config`

Constructs a `Config` object, which [processes and provides a merged repository for configuration files](configuration/config-files) across all loaded sprinkles. Additionally, it imports [Dotenv](https://github.com/vlucas/phpdotenv) to allow automatically loading environment variables from `.env` file.

The `config` service also builds the `site.uri.public` config variable from the component values specified in the configuration.

### `UserFrosting\Sprinkle\Core\Csrf\CsrfGuard`

Constructs the [CSRF Guard](https://github.com/slimphp/Slim-Csrf) middleware, which mitigates cross-site request forgery attacks on your users. This is wrapped by `CsrfGuardMiddleware` and can be globally disabled via the `csrf.enabled` config. See [Chapter 2](background/security) for more information on security features.

### `Illuminate\Database\Capsule\Manager` (Capsule)

Creates the Eloquent ORM database connection manager. This service handles all database connections defined in your configuration and provides access to the query builder and schema builder. Most of the time you'll interact with database models rather than the Capsule directly.

Related services:
- `Illuminate\Database\Connection` - The active database connection
- `Illuminate\Database\Schema\Builder` - For schema operations and migrations

### `UserFrosting\Sprinkle\Core\Database\Migrator\MigrationLocatorInterface`

Provides access to the database migration system. Use the `Migrator` class to run migrations via the `migrate` Bakery command or programmatically. See [database migrations](database/migrations) for more information.

### `UserFrosting\Sprinkle\Core\Log\DebugLoggerInterface`

Monolog `Logger` object for sending debug print statements and data to `logs/debug.log`. Inject via the `DebugLoggerInterface` for proper dependency injection.

### `UserFrosting\Sprinkle\Core\Log\ErrorLoggerInterface` 

Monolog `Logger` object for sending non-fatal error information from custom error handlers to `logs/userfrosting.log`. Inject via the `ErrorLoggerInterface`.

### `UserFrosting\Sprinkle\Core\Log\MailLoggerInterface`

Monolog `Logger` object for sending detailed SMTP mail server information from the `Mailer` service to `logs/userfrosting.log`. Inject via the `MailLoggerInterface`.

### `UserFrosting\Sprinkle\Core\Log\QueryLoggerInterface`

Monolog `Logger` object for logging successfully completed database queries to `logs/userfrosting.log`. Query logging only occurs when `debug.queries` is set to `true` in the configuration. Inject via the `QueryLoggerInterface`.

### `UserFrosting\Sprinkle\Core\Mail\Mailer`

Creates an instance of `Mailer`, which serves as a UF-compatible wrapper for a [PHPMailer](https://github.com/PHPMailer/PHPMailer) object. See [Chapter 14](mail) for more information.

### `League\CommonMark\ConverterInterface`

Provides the CommonMark Markdown parser for converting Markdown to HTML. UserFrosting uses this with GitHub Flavored Markdown support. Sprinkles can register custom Markdown extensions by implementing the `MarkdownExtensionRecipe` interface.

### `UserFrosting\Sprinkle\Core\Throttle\Throttler`

Creates a `Throttler` object, which handles [request throttling](routes-and-controllers/client-input/throttle) for different routes. This service will automatically register any throttling rules defined in the `throttles` key of your configuration.

### `UserFrosting\Sprinkle\Core\Util\RouteParserInterface`

See [Chapter 8](routes-and-controllers) for more information about defining routes, and the [Slim Documentation](https://www.slimframework.com/docs/v4/objects/routing.html#route-names) on how to use the Route Parser.

### `UserFrosting\Session\Session`

Sets up UserFrosting's `Session` object, which serves as a wrapper for the `$_SESSION` superglobal. `Session` will use file- or database-based storage for sessions, depending on your configuration setting for `session.handler`. Session handlers are provided by [Laravel's session handlers](https://laravel.com/docs/8.x/session#configuration), which implement PHP's [`SessionHandlerInterface`](http://php.net/SessionHandlerInterface).

Please note that when using file-based sessions, UserFrosting places sessions in its own `/app/sessions` directory instead of PHP's default session directory.

> [!WARNING]
> Use UserFrosting's `Session` service instead of PHP's `$_SESSION` superglobal in your code for proper functionality.

### `Slim\Views\Twig`

Sets up the Twig View object, which is implemented by the [Slim Twig-View](https://github.com/slimphp/Twig-View) project. Turns on caching and/or debugging depending on the settings for `cache.twig` and `debug.twig`, respectively. Also registers the UserFrosting's `CoreExtension`
extension (`UserFrosting\Sprinkle\Core\Twig\CoreExtension`), which provides some additional functions, filters, and global variables for UserFrosting.

See [Templating with Twig](templating-with-twig) for more information about Twig and the custom functions, filters, and variables that UserFrosting defines.

### `UserFrosting\Sprinkle\Core\Twig\CoreExtension`
UserFrosting's core Twig extensions, which provide additional functions, filters, and global variables.

See [Templating with Twig](templating-with-twig) for more information about Twig and the custom functions, filters, and variables that UserFrosting defines.

### `UserFrosting\ViteTwig\ViteManifestInterface`

Provides integration with Vite for frontend asset management. This service reads the Vite manifest file and provides methods to generate asset URLs with proper cache busting. See [Chapter 13](asset-management) for more information.

### `Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface`

Legacy Webpack Encore integration service. If you're using Webpack instead of Vite for asset management, this service provides access to the Webpack manifest


### `UserFrosting\I18n\Translator`

Sets up the `Translator` object (`UserFrosting\I18n\Translator`) for translation, localization, and internationalization of your site's contents. See [Chapter 17](/i18n) for more information.

### `UserFrosting\UniformResourceLocator\ResourceLocatorInterface`

An instance of our own [Uniform Resource Locator class](https://github.com/userfrosting/framework/tree/5.1/src/UniformResourceLocator#readme), which provides a unified method of accessing Sprinkle entities via streams.

See [Chapter 18](/advanced/locator) for more information.

### `UserFrosting\Sprinkle\SprinkleManager`

The `SprinkleManager` can be used to get a list of all sprinkles currently loaded, get the main sprinkle, test if a sprinkle is available, etc.

## Account Sprinkle Services

### `UserFrosting\Sprinkle\Account\Authenticate\Authenticator`

Creates an instance of `Authenticator`, which handles user authentication and logins. See [Chapter 10](users/user-accounts#authentication-and-authorization) for more information.

### `UserFrosting\Sprinkle\Account\Log\AuthLoggerInterface`

Monolog `Logger` object for logging detailed information about access control checks. See [Chapter 10](users/access-control) for more information about access control. Note that access control checks will only be logged if `debug.auth` is set to `true` in the configuration. Inject via the `AuthLoggerInterface`

### `UserFrosting\Sprinkle\Account\Authenticate\AuthGuard`

The `AuthGuard` middleware, which is bound to routes which require authentication to access ("protected routes"). See [Chapter 10](users/user-accounts#authentication-and-authorization) for more information.

### `UserFrosting\Sprinkle\Account\Authenticate\GuestGuard`

The `GuestGuard` middleware, which is bound to routes that require a guest (non logged-in user). See [Chapter 10](users/user-accounts#authentication-and-authorization) for more information.

### `UserFrosting\Sprinkle\Account\Log\AuthLogger`

Monolog `Logger` object for logging detailed information about access control checks. See [Chapter 10](users/access-control) for more information about access control. Note that access control checks will only be logged if `debug.auth` is set to `true` in the configuration.

### `UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager`

*Associated Interface : `UserFrosting\Sprinkle\Account\Authorize\AuthorizationManagerInterface`*

The `AuthorizationManager` handles access control checks for protected routes. It uses the [Role-Based Access Control (RBAC)](https://en.wikipedia.org/wiki/Role-based_access_control) system, which is based on user roles and permissions. See [Chapter 10](users/access-control) for more information about access control.

### `UserFrosting\Sprinkle\Account\Authenticate\Interfaces\MFAProvider`

Provides Multi-Factor Authentication (MFA) functionality. By default, this uses email-based OTP (One-Time Password) codes via the `EmailOtpProvider` implementation. You can replace this service to use other MFA methods like TOTP (Time-Based OTP) for authenticator apps or SMS.

### `UserFrosting\Sprinkle\Account\Authenticate\Interfaces\EmailVerificationProvider`

Handles email verification for new user registrations and email changes. By default, this uses the same email-based OTP system as MFA via the `EmailOtpProvider` implementation.

### `UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface`

While not technically a service, UserFrosting maps model interfaces to their implementations via the `ModelsService`. This allows you to override model classes by decorating the service. Other model interfaces include:
- `ActivityInterface`
- `GroupInterface`  
- `PermissionInterface`
- `RoleInterface`
- `UserVerificationInterface`

### `UserFrosting\Sprinkle\Account\Authenticate\Interfaces\HasherInterface`

Creates an instance of `Hasher`, which handles password hashing and validation. Inject via the `HasherInterface`.

### `UserFrosting\Sprinkle\Core\I18n\SiteLocaleInterface`

Provides the current site locale settings. The Account Sprinkle overrides the Core implementation to provide user-specific locale preferences.

### `UserFrosting\Sprinkle\Account\Log\UserActivityLoggerInterface`

Sets up a Monolog `Logger` object which logs user activities to the `activities` database table using `UserActivityDatabaseHandler`. This makes it easy to track user actions throughout your application. Inject via the `UserActivityLoggerInterface`
### `UserFrosting\Sprinkle\Account\Log\UserActivityLogger`

Sets up a Monolog `Logger` object, which uses `UserFrosting\Sprinkle\Account\Log\UserActivityDatabaseHandler` and `UserFrosting\Sprinkle\Account\Log\UserActivityProcessor` to allow logging of user activities to the `activities` database table. Monolog makes it easy to swap to other storage solutions such as Redis or Elastic Search.
