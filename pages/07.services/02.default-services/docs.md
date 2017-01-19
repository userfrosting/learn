---
title: Default Services
metadata:
    description: UserFrosting's default services provide most of the tools needed to build a basic web application.
taxonomy:
    category: docs
---

As mentioned in the last section, each Sprinkle can set up its own services through a **service provider** class.  The default `core` and `account` Sprinkles set up their services through `CoreServicesProvider` and `AccountServicesProvider`, respectively.  These classes can be found in the `src/ServicesProvider/` subdirectories in each Sprinkle's directory.

### Core Services

#### alerts

This service handles the [alert message stream](/routes-and-controllers/alert-stream), sometimes known as "flash messages".  See Section 4.6 for more information.

#### assets

Constructs the `AssetManager` object (namespace `UserFrosting\Assets\AssetManager`), which is responsible for loading information about assets (Javascript, CSS, images, etc) required by each page and constructing the appropriate HTML tags.  See [Section 5.2](/building-pages/assets) for more information.

#### assetLoader

This service handles requests for [raw assets](/building-pages/assets#raw-assets) made to the application.  It locates the appropriate file for a given url, and builds the response containing the contents of the asset, along with setting headers for MIME type and length.

#### cache

Creates an instance of a Laravel [Cache](https://laravel.com/docs/5.3/cache).

#### checkEnvironment

Constructs the `CheckEnvironment` object (namespace `UserFrosting\Sprinkle\Core\Util\CheckEnvironment`), which does some basic checks in a new UF installation to make sure that the minimum requirements are met, directory permissions are set, etc.

#### classMapper

Constructs the `ClassMapper` object (namespace `UserFrosting\Sprinkle\Core\Util\ClassMapper`), which provides dynamic class mapping in your controllers and classes.  See [Section 2.2](http://localhost/userfrosting-learn/sprinkles/contents#dynamic-class-mapper) for more information on dynamic class mapping.

#### config

Constructs the `Config` object (namespace `UserFrosting\Config\Config`), which [processes and provides a merged repository for the configuration files](http://localhost/userfrosting-learn/sprinkles/contents#config) across all loaded Sprinkles.  Additionally, it imports the [Dotenv](https://github.com/vlucas/phpdotenv) to allow automagically loading environment variables from `.env` file.

The `config` service also builds the `site.uri.public` config variable from the component values specified in the configuration, and sets values for the PHP settings `display_errors`, `error_reporting`, and `timezone`.

#### csrf

Constructs the [CSRF Guard](https://github.com/slimphp/Slim-Csrf) middleware, which mitigates cross-site request forgery attacks on your users.  See Chapter 12 for more information on security features.

#### db

Sets up the [database](/database).  

#### debugLogger

Monolog `Logger` object for sending debug print statements and data to `logs/debug.log`.  Can also be accessed via the [`Debug` facade](http://localhost/userfrosting-learn/background/server-side#debug-facade).

#### errorHandler

Sets up a `CoreErrorHandler` object, which is used as a [custom error handler](http://www.slimframework.com/docs/handlers/error.html#custom-error-handler) for UF's Slim application.  It then registers the custom handlers for `HttpException`, `PDOException`, and `phpmailerException`.  See [Chapter 10](/error-handling/overview) for more information on custom exceptions and exception error handlers.

#### errorLogger

Monolog `Logger` object for sending non-fatal error information from custom error handlers to `logs/errors.log`.

#### locator

An instance of [RocketTheme's Uniform Resource Locator class](https://github.com/rockettheme/toolbox/blob/develop/ResourceLocator/src/UniformResourceLocator.php), which provides a unified method of accessing Sprinkle entities via [streams](https://webmozart.io/blog/2013/06/19/the-power-of-uniform-resource-location-in-php/).  

See [Section 11.3](/other-services/locator) for more information.

#### mailer

Creates an instance of `Mailer` (namespace `UserFrosting\Sprinkle\Core\Mail\Mailer`), which serves as a UF-compatible wrapper for a [PHPMailer](https://github.com/PHPMailer/PHPMailer) object.

See [Section 11.1](/other-services/mail) for more information.

#### mailLogger

Monolog `Logger` object for sending detailed SMTP mail server information from the `mailer` service to `logs/mail.log`.  Mail logging will only occur if `debug.smtp` is set to `true`.

#### notFoundHandler

Implements Slim's [Custom Not Found handler](http://www.slimframework.com/docs/handlers/not-found.html), causing the application to return a 404 not found page.

#### router

Overrides Slim's default `router`, replacing their `Router` object with a `UserFrosting\Sprinkle\Core\Router` object.  Our custom `Router` class allows for routes to be overridden and redefined in Sprinkles.

See [Chapter 4](/routes-and-controllers) for more information about defining routes.

#### session

Sets up UserFrosting's `Session` object (`UserFrosting\Session\Session`), which serves as a wrapper for the `$_SESSION` superglobal.  `Session` will use file- or database-based storage for sessions, depending on your configuration setting for `session.handler`.  Session handlers are provided by [Laravel's session handlers](https://laravel.com/docs/5.3/session#configuration), which implement PHP's [`SessionHandlerInterface`](http://php.net/SessionHandlerInterface).

Please note that when using file-based sessions, UserFrosting places sessions in its own `/app/sessions` directory instead of PHP's default session directory.

>>>> Use UserFrosting's `session` service (`$container->session`) instead of PHP's `$_SESSION` superglobal in your code for proper functionality.

#### shutdownHandler

Sets up an instance of `ShutdownHandler` (`UserFrosting\Sprinkle\Core\Handler\ShutdownHandler`), which attempts to capture and log any fatal errors raised.  It registers itself with PHP's `register_shutdown_function`.

See [Chapter 10](/error-handling) for more information on error handlers.

#### throttler

Creates a `Throttler` object, which handles [request throttling](/routes-and-controllers/user-input/throttle) for different routes.  This service will automatically register any throttling rules defined in the `throttles` key of your configuration.

#### translator

Sets up the `MessageTranslator` object (`UserFrosting\I18n\MessageTranslator`) for translation, localization, and internationalization of your site's contents.  See [Chapter 5](/building-pages/i18n) for more information.

#### view

Sets up the Twig View object, which is implemented by the [Slim Twig-View](https://github.com/slimphp/Twig-View) project.  Turns on caching and/or debugging depending on the settings for `cache.twig` and `debug.twig`, respectively.  Also registers the UserFrosting's `CoreExtension`
extension (`UserFrosting\Sprinkle\Core\Twig\CoreExtension`), which provides some additional functions, filters, and global variables for UserFrosting.

See [Chapter 5](/building-pages/templating-with-twig) for more information about Twig and the custom functions, filters, and variables that UserFrosting defines.

### Account Services

#### assets

The Account Sprinkle extends the core `assets` service, to add search paths for any assets loaded in a user's custom theme.

#### classMapper

The Account Sprinkle extends the core `classMapper` service, and registers the following model identifiers:

| Identifier       | Model                                               |
| ---------------- | --------------------------------------------------- |
| `user`           | `UserFrosting\Sprinkle\Account\Model\User`          |
| `group`          | `UserFrosting\Sprinkle\Account\Model\Group`         |
| `role`           | `UserFrosting\Sprinkle\Account\Model\Role`          |
| `permission`     | `UserFrosting\Sprinkle\Account\Model\Permission`    |
| `activity`       | `UserFrosting\Sprinkle\Account\Model\Activity`      |
| `password_reset` | `UserFrosting\Sprinkle\Account\Model\PasswordReset` |
| `verification`   | `UserFrosting\Sprinkle\Account\Model\Verification`  |

#### errorHandler

The Account Sprinkle extends the core `errorHandler` service, to add the following custom exception handlers:

| Exception                                           | Handler                                                           |
| --------------------------------------------------- | ----------------------------------------------------------------- |
| `UserFrosting\Support\Exception\ForbiddenException` | `UserFrosting\Sprinkle\Account\Handler\ForbiddenExceptionHandler` |

#### translator

The Account Sprinkle extends the core `translator` service, to add search paths for any locale files loaded in a user's custom theme.

#### view

The Account Sprinkle extends the core `view` service, adding the `AccountExtension` Twig extension (`UserFrosting\Sprinkle\Account\Twig\AccountExtension`).  This extension adds the following:

##### Functions

- `checkAccess`: Twig wrapper for the `authorizer` service's `checkAccess` method.

##### Variables

- `current_user`: Twig wrapper for the `currentUser` service.

The extended `view` also adds search paths for any template files loaded in a user's custom theme.

#### authenticator

Creates an instance of `Authenticator` (`UserFrosting\Sprinkle\Account\Authenticate\Authenticator`), which handles authenticating and logging in users.  See [Chapter 8](/users) for more information.

#### authLogger

Monolog `Logger` object for logging detailed information about access control checks.  See [Chapter 8](/users/access-control) for more information about access control.  Note that access control checks will only be logged if `debug.auth` is set to `true` in the configuration.

#### authorizer

Creates an instance of `AuthorizationManager` (`UserFrosting\Sprinkle\Account\Authorize\AuthorizationManager`), which handles access control checks via the `checkAccess` method.  This service also defines several default access condition callbacks.  More information, and a complete list of default access condition callbacks, can be found in [Chapter 8](/users/access-control).

#### currentUser

Sets up the `User` object (`UserFrosting\Sprinkle\Account\Model\User`) for the currently logged-in user.  If there is no logged-in user, it returns `null`.  It also loads the locale and theme for the current user, if set.

#### repoPasswordReset

Sets up a `PasswordResetRepository` object (`UserFrosting\Sprinkle\Account\Repository\PasswordResetRepository`), which handles token creation, verification, and expiration for password reset requests.

#### repoVerification

Sets up a `VerificationRepository` object (`UserFrosting\Sprinkle\Account\Repository\VerificationRepository`), which handles token creation, verification, and expiration for new account verification requests.

#### userActivityLogger

Sets up a Monolog logger, which uses `UserFrosting\Sprinkle\Account\Log\UserActivityDatabaseHandler` and `UserFrosting\Sprinkle\Account\Log\UserActivityProcessor` to allow logging of user activities to the `activities` database table.  By using Monolog, it makes it easy to swap other storage solutions such as redis or Elastic Search.
