---
title: Exceptions and Error Handling
metadata:
    description: UserFrosting provides a rich set of features for catching errors and exceptions, logging or displaying detailed error information, and providing appropriate responses to the client.
taxonomy:
    category: docs
---

Things don't always go the way they were intended in your application. Sometimes this is the client's fault, and sometimes it is the server's (i.e. _your_) fault. When this happens, it is important that your application:

- Make every effort to recover from the situation;
- Communicate clearly to the client that there was an error, and who is at fault (them or the server);
- Provide detailed information about the error to the developer.

UserFrosting provides a framework for this process using custom PHP **[exceptions](http://php.net/manual/en/class.exception.php)** and **exception handlers**.

## The exception lifecycle

Any time an uncaught **exception** is thrown in your code, a global **Exception Handler Middleware** catches it and invokes a custom **error handler** specific to the exception that has been thrown. In return, this exception handler might invoke a specific **error renderer** to format the response for display, storage or logging. 

### Exceptions

Every time something goes wrong during your code execution, an exception should be thrown. Whether it's a logic error (a config value is defined as a String, but an array is excepted), user input error or validation error (User didn't provide his username), something not found (either a page, or an *Owl*), the Exception is an easy way to stop code execution and act on this exception.

UserFrosting provides many default exceptions you can use in your code. While some are very specific, others are generic enough to be used in many situation. It is also encouraged to create [your own exceptions](#custom-exception).

It's worth noting at this point that some Exception are *user facing*, while others are not. User facing exception contains a specific and detailed message which is intended to be seen by the end user (eg.: *Username not found*), as well as an (optional) separate message intended to be seen only by you, the developer. Non-user facing exception only display a generic message to the end user (eg.: *An error has occurred*) while still providing the detailed information to your, usually via the log system.

#### Default Exceptions

| Exception                          | Description                                      |
|------------------------------------|--------------------------------------------------|
| BadConfigException                 | Bad value if defined in config.                  |
| MigrationDependencyNotMetException | A migration dependency is not met.               |
| MigrationNotFoundException         | A required migration class cannot be found       |
| MigrationRollbackException         | A migration cannot be rollback                   |
| ValidationException                | Used when [client input validation](/routes-and-controllers/client-input/validation) fails. Implements *UserMessageException*.                                                                 |
| **UserFacingException**            | Shows a specific message and optional description meant for to the user. This exception is meant to be extended to create specific exceptions. Implements *TwigRenderedException* & *UserMessageException* |
| VersionCompareException            | Used when a version constraint is not matched.   |

**Child of `UserFacingException`:**

| Exception                     | Description                                                                                                     |
|-------------------------------|-----------------------------------------------------------------------------------------------------------------|
| **AccountException**          | This exception is used as umbrella exception for all Account related exception to make it easier to catch them. |
| CsrfMissingException          | Used when the CSRF token is missing from the request                                                            |
| **NotFoundException**         | Base exception for when _something_ is not found. Default to a typical 404. Not Found message, but should be extended to provide more specific messages.                                                                                                                |
| GroupException                | Group related exceptions. The description is expected to be set by the controller.                              |
| MissingRequiredParamException | Used when an the a model property is not found.                                                                 |
| RoleException                 | Role related exceptions. The description is expected to be set by the controller.                               |


**Child of `AccountException`:**

| Exception                     | Description                                                                              |
|-------------------------------|------------------------------------------------------------------------------------------|
| AccountDisabledException      | Used when an account has been disabled.                                                  |
| AccountInvalidException       | Used when an account has been removed during an active session.                          |
| AccountNotFoundException      | Used when an account has been removed during an active session.                          |
| AccountNotVerifiedException   | Used when an account is required to complete email verification, but hasn't done so yet. |
| AuthCompromisedException      | Used when we suspect theft of the rememberMe cookie.                                     |
| AuthExpiredException          | Used when the user needs to authenticate/reauthenticate.                                 |
| AuthGuardException            | Used when a page requires the user to be logged-in.                                      |
| DefaultGroupException         | Used when the default group is not found.                                                |
| EmailNotUniqueException       | Used when an the username is not unique.                                                 |
| ForbiddenException            | Used when an account doesn't have access to a resource.                                  |
| InvalidCredentialsException   | Used when an account fails authentication for some reason.                               |
| LocaleNotFoundException       | Used when the user needs to authenticate/reauthenticate.                                 |
| LoggedInException             | Used when the user needs to authenticate/reauthenticate.                                 |
| MissingRequiredParamException | Used when an the a model property is not found.                                          |
| PasswordInvalidException      | Used when current password doesn't match the one we have on record.                      |
| PasswordResetInvalidException | Used when an account doesn't have access to a resource.                                  |
| RegistrationException         | Used when an exception is encountered by the registration mechanism.                     |
| UsernameNotUniqueException    | Used when an the username is not unique.                                                 |

**Child of `NotFoundException`:**

| Exception                   | Description                     |
|-----------------------------|---------------------------------|
| AccountNotFoundException    | Account not found exception.    |
| GroupNotFoundException      | Group not found exception.      |
| PermissionNotFoundException | Permission not found exception. |
| RoleNotFoundException       | Role not found exception.       |

> [!NOTE]
> Notice how `ForbiddenException` extends `AccountException`, which extends `UserFacingException`, which *implements* `UserMessageException`. In the same way, `AccountNotFoundException` extends `NotFoundException`, which *also* extends `UserFacingException`. Both `ForbiddenException` and `AccountNotFoundException` ultimately implements `UserMessageException`.

### Exception handlers
In most cases, when an exception is thrown, the `UserFrosting\Sprinkle\Core\Error\ExceptionHandlerMiddleware` will catch the exception. Rather than handling the exception directly, `ExceptionHandlerMiddleware` checks to see if the exception type has been registered with a custom **exception handler**. If so, it invokes the corresponding exception handler; otherwise, it invokes a generic one.

Every exception handler implements one public method (as defined by `UserFrosting\Sprinkle\Core\Error\Handler\ExceptionHandlerInterface`): `handle()`. This is the entry point for handling the exception. This is where you decide if/how to show an error message, debugging page, log the error, and/or do something different entirely. This methods receive the *Server Request* and the *Exception* itself, and must return a Server Response.

The base implementation of this interface is `UserFrosting\Sprinkle\Core\Error\Handler\ExceptionHandler`. This is the default Exception Handler, and most custom exception handlers will extends this class. It add some features over the interface : 
1. Generate the appropriate user facing message, translate it if required, and determine the exception status code, if applicable;
2. Decide if the exception should be saved to log. If so, uses `writeToErrorLog()` to write the detailed error information and stack trace to the UserFrosting error log service (which defaults to writing to the `logs/userfrosting.log` file), using a specific Error Renderer;
3. Render the Response using the appropriate *Error Renderer*;

A single handler isn't necessarily bind to a single Exception. Each handler can handle [Subtypes](https://www.php.net/manual/en/function.is-subclass-of.php) of an Exception natively. For example, `UserMessageExceptionHandler` handles `UserMessageException`, but also any exception that is a child of `UserMessageException`, since the handler also accept subtype handling. Since `ForbiddenException` extend `AccountException`, which extends `UserFacingException`, which *implements* `UserMessageException`, which is *handled* by `UserMessageExceptionHandler`, `ForbiddenException` will be handled by `UserMessageExceptionHandler`. 

However, `AuthExpiredException` will be handled by `RedirectToLoginDangerHandler`, even it extends `AccountException` because a handler is directly defined for this exception. 

#### Provided Exception Handler

| Handler                      | Description                                                                               |
|------------------------------|-------------------------------------------------------------------------------------------|
| ExceptionHandler             | Default handler.                                                                          |
| HttpExceptionHandler         | Custom handler for all HttpException. Force log and error detail to be off. Use the HTTP status code instead of Exception status code.                                                                                                     |
| PhpMailerExceptionHandler    | Handler for phpMailer exceptions. Force error message.                                    |
| UserMessageExceptionHandler  | Force log and error detail to be off. Add the user facing message to the [Alert Stream]() |
| LoggedInExceptionHandler     | Handler for LoggedInException. Redirect to index.                                         |
| RedirectToLoginDangerHandler | Redirect the user to the login page with danger alert.                                    |
| RedirectToLoginInfoHandler   | Redirect the user to the login page with info alert.                                      |

By default, these exceptions are handled by these handlers :

| Exception to handle           | Assigned handler             | Handle Subtypes? |
|-------------------------------|------------------------------|------------------|
| HttpException                 | HttpExceptionHandler         | Yes              |
| UserMessageException          | UserMessageExceptionHandler  | Yes              |
| PHPMailerException            | PhpMailerExceptionHandler    | No               |
| LoggedInException             | LoggedInExceptionHandler     | No               |
| AuthGuardException            | RedirectToLoginInfoHandler   | No               |
| AuthExpiredException          | RedirectToLoginDangerHandler | No               |
| PasswordResetInvalidException | RedirectToLoginDangerHandler | No               |


> [!NOTE]
> A special `ShutdownHandler` is also used. This is handler will only be called when the aboves handlers themselves throws an exception, or the exception is thrown before the handlers can be registered. Some PHP fatal errors will always be handled by the *Shutdown Handler*.

### Error Renderers

While the handlers *handles* the exception, the renderer format the exceptions to be displayed to the user, according to the content type the user is currently using. Each renderer must implement the `UserFrosting\Sprinkle\Core\Error\Renderer\ErrorRendererInterface` interface, and thus the following method:

```php 
public function render(
    ServerRequestInterface $request,
    Throwable $exception,
    Message $userMessage,
    int $statusCode,
    bool $displayErrorDetails = false
): string;
```

As you can see, the renderer will receive the server request, the exception itself, the user facing message to display (generated by the handler), the status code (determined by the handler) and if the renderer should display error details (as determined by the handler). In return the renderer will return a *string*, which the handler will receive back, and write to whatever (log, the response, etc.).

> [!TIP]
> Remember, any services can be injected into the Renderer classes, as with any Handlers classes 

#### Provided Renderers

| Formatter          | Description                                                                             |
|--------------------|-----------------------------------------------------------------------------------------|
| EmptyRenderer      | Renders an empty string. Used by handlers that provides a redirection link.             |
| HtmlRenderer       | Renders a basic, hardcoded HTML page.                                                   |
| JsonRenderer       | Renders the error message and stack trace as a JSON object.                             |
| PlainTextRenderer  | Renders the error as plain text. Used for logs, or Bakery.                              |
| PrettyPageRenderer | Renders an HTML page using [Whoops](https://github.com/filp/whoops) or a Twig Template. |
| XmlRenderer        | Renders the error message and stack trace in XML format.                                |

By default, each request type is tied to :

| Request Type     | Formatter          |
|------------------|--------------------|
| application/json | JsonRenderer       |
| application/xml  | XmlRenderer        |
| text/xml         | XmlRenderer        |
| text/html        | PrettyPageRenderer |
| text/plain       | PlainTextRenderer  |

> [!NOTE]
> When an exception is written to the text log, the `PlainTextRenderer` is used by default.

## Default implementation & Config

Some configuration variables are available to configure the behavior of some handlers and renderers : `debug.exception` and `logs.exceptions`. The first one dictate if a detailed debug message will be shown to the end user. This is usually the case in a debug environment, but not in a production environment. The second one dictate if the exception should be saved to the logs.

For example, by default in a dev environment, the `PrettyPageHandler` will render a debug response when `debug.exception` config is set to **true**. This page is renderer using the [Whoops](https://github.com/filp/whoops) dependency. When `debug.exception` is set to **false** (by default in a production environment), it will instead render a simple message to the client using a Twig template.

Similarly, when `logs.exception` config is set to **true**, the exception will be saved to the log, along with a stack trace. When this is set to **false**, no error will be logged. By default, this is set to false in a dev environnement, and true in production.

However, keep in mind some handlers/renderers might force configuration value. For example, an exception is thrown when a user is not logged in. There's no point to display a detailed stack trace or log for this kind of exception, in any environment. In fact, this exception is mostly used to display an alert to the end user and redirect the user to the login page. In this case, the handler/renderer won't obey the general configuration value.

<!-- TODO : Need to update and Test this Ajax stuff -->
### site.debug.ajax

Normally, when `displayErrorDetails` is enabled and an error is generated during an AJAX request that requests a JSON response, the `JsonRenderer` will be invoked to return a JSON object containing the debugging information.

However for some types of exceptions, you may wish to display a debugging page in the browser instead - even for AJAX requests! When this setting is enabled, `ExceptionHandler` will ignore the requested content type and generate an HTML response instead. In [client side code](/client-side-code), when `site.debug.ajax` is enabled and an error code is received by an AJAX call, your Javascript can use this information to decide whether or not to completely replace the current page with the HTML debug page that was returned in the response.

> [!WARNING]
> Any detailed error message can, in fact, leak sensitive information to the client! That's why, by default, both `debug.exception` and `site.debug.ajax` are disabled in the **production** configuration environment. Do not change this! Displaying detailed exception traces in production is an extreme security risk and could leak sensitive passwords to your users and/or the public.

## Creating a custom Exception Handler

### Custom Exception

You can define your own exceptions, of course, optionally inheriting from any of the [existing exception types](advanced/error-handling#exceptions). Every exception you define must eventually inherit back to PHP's base `Exception` class.

You'll notice that a large portion of UserFrosting's exception types inherit from the `UserFacingException` class. This is an interesting exception (no pun intended) - it acknowledges that the _exception_ message (which you would want your developers and sysadmins to see), and the _client_ messages (which are displayed to the user to let them know that something went wrong), are generally different things.

The `UserFacingException` class acts like a typical exception, but it maintains two additional parameters internally: a list of messages that the exception handler may display to the client, and a status code that should be returned with the response. As a simple example, consider the `AccountInvalidException`:

```php
<?php

declare(strict_types=1);

namespace UserFrosting\Sprinkle\Account\Exceptions;

use UserFrosting\Support\Message\UserMessage;

/**
 * Invalid account exception. Used when an account has been removed during an active session.
 */
final class AccountInvalidException extends AccountException
{
    protected string $title = 'ACCOUNT.EXCEPTION.INVALID.TITLE';
    protected string|UserMessage $description = 'ACCOUNT.EXCEPTION.INVALID.DESCRIPTION';
}

```

It defines a default message title and description, `ACCOUNT.EXCEPTION.INVALID.TITLE` and `ACCOUNT.EXCEPTION.INVALID.DESCRIPTION` respectively, that the registered exception handler can display on an error page or push to the alert stream. It also sets a default HTTP status code (400) to return with the error response, through the inheritance to `UserFacingException`.

The default message can be overridden when the exception is thrown in your code:

```php
$e = new AccountInvalidException("This is the exception message that will be logged for the dev/sysadmin.");
$e->setTitle("Hello, client!");
$e->setDescription("This is a custom error message that will be sent back to the client.");

throw $e;
```

### Custom Handler

In some situation, it can useful to create your own Exception Handler. As a basic example, lets take a look at the `HttpExceptionHandler` class, which modifies the base behavior of `ExceptionHandler` by reading an error message and status code from the corresponding `HttpException` (or child subtype) and using these to construct the response. 

```php
<?php

declare(strict_types=1);

namespace UserFrosting\Sprinkle\Core\Error\Handler;

use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpException;
use Throwable;

/**
 * Custom handler for all HttpException.
 */
final class HttpExceptionHandler extends ExceptionHandler
{
    /**
     * Never log exceptions for HttpException.
     */
    protected function shouldLogExceptions(): bool
    {
        return false;
    }

    /**
     * Never display error details for HttpException.
     *
     * @return bool
     */
    protected function displayErrorDetails(): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    protected function determineStatusCode(ServerRequestInterface $request, Throwable $exception): int
    {
        if ($exception instanceof HttpException) {
            return intval($exception->getCode());
        }

        return parent::determineStatusCode($request, $exception);
    }
}
```

> [!NOTE]
> In addition to the public `handle()` method, you can also override protected methods in `ExceptionHandler` to customize other behaviors. Take a look at the source code for `ExceptionHandler` for more details.

As you can see, this handler extends the `ExceptionHandler`, declare that any exception handled by it shouldn't be logged and error details should never be displayed (bypassing the general config), and finally use the http status code from the exception, instead of the normal exception status code. Notice that, no matter what, we never display a debugging page when handling an `HttpException`. This is because `HttpException` is not an error at all, but rather an exception that can be thrown during production when the application itself is functioning perfectly normally.

As another example, you can take a look at the `UserMessageExceptionHandler` class, which handles most of the user facing exception, defined by the `UserMessageException` interface. This handler will send the user facing message to the alert stream. The `UserFacingException` exception also implement `TwigRenderedException`, which is used by the PrettyPageRenderer to determine which Twig template file to use to render this specific exception.

Handlers can also perform redirection. For example `RedirectToLoginDangerHandler` is used when the `AuthExpiredException` is thrown (when a user has been logged out) to redirect the user to the login page.

### Registering custom exception handlers

Once you have defined your custom exception handler, you'll need to map the corresponding exception to it by extending the [`ExceptionHandlerMiddleware` service provider](/dependency-injection/extending-services).

To do this, simply decorate the `ExceptionHandlerMiddleware` class and call the `registerHandler` method on the `$middleware` object:

```php
<?php

namespace UserFrosting\Sprinkle\Site\ServicesProvider;

use UserFrosting\ServicesProvider\ServicesProviderInterface;
use UserFrosting\Sprinkle\Core\Error\ExceptionHandlerMiddleware;
use UserFrosting\Sprinkle\Site\Database\Exceptions\MissingOwlException;
use UserFrosting\Sprinkle\Site\Error\Handler\MissingOwlExceptionHandler;

class ErrorHandlerService implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            ExceptionHandlerMiddleware::class => \DI\decorate(function (ExceptionHandlerMiddleware $middleware) {
                $middleware->registerHandler(MissingOwlException::class, MissingOwlExceptionHandler::class);

                return $middleware;
            }),
        ];
    }
}
```

The first argument is the the exception class reference, and the second argument is the handler class reference.

> [!WARNING]
> It's important to **decorate** the `ExceptionHandlerMiddleware`. Do not follow the example set in the Core sprinkle, and use `\DI\Autowire` instead, as you'll **replace** the middleware already defined, and every handler already registered.

### Handling child exception types

Internally, `ExceptionHandlerMiddleware` can use the [`is_subclass_of`](https://www.php.net/manual/en/function.is-subclass-of.php) PHP method to map a given exception to the handler of any of it's parent. This means that if your exception implement a specific interface or extend another Exception, then `ExceptionHandlerMiddleware` *can* use the handler mapped to the _last_ matching exception type. For example, if you have an exception:

```php
<?php

class MissingOwlException extends MissingBirdException
{

}
```

And you have registered a handler for `MissingBirdException` with the `handleSubclasses` argument as "true":

```php
ExceptionHandlerMiddleware::class => \DI\decorate(function (ExceptionHandlerMiddleware $middleware) {
    $middleware->registerHandler(MissingBirdException::class, MissingBirdExceptionHandler::class, true);

    return $middleware;
}),
```

Since `MissingOwlException` inherits from `MissingBirdException`, UserFrosting will use the `MissingBirdExceptionHandler` to handle your `MissingOwlException`, unless later on you register another handler specifically on the `MissingOwlException` type.

## Creating a custom Exception Renderer

It is possible for a Sprinkle to create and register it's own Exception Renderer. The only requirement is for the renderer to implement `UserFrosting\Sprinkle\Core\Error\Renderer\ErrorRendererInterface` and thus the `render` method. For example:

```php
<?php

namespace UserFrosting\Sprinkle\Site\Error\Renderer;

use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use UserFrosting\Sprinkle\Core\Util\Message\Message;
use UserFrosting\Sprinkle\Core\Error\Renderer\ErrorRendererInterface;

class SimplePlainTextRenderer implements ErrorRendererInterface
{
    /**
     * {@inheritDoc}
     */
    public function render(
        ServerRequestInterface $request,
        Throwable $exception,
        Message $userMessage,
        int $statusCode,
        bool $displayErrorDetails = false
    ): string {
        return $exception->getMessage();
    }
}
```

Your custom renderer now need to be registered using one of three methods: 
1. In a custom handler;
2. By decorating an existing handler;
3. By replacing a global renderer through dependency injection;

To use your renderer in your handler, replace the `$errorRenderers` and/or `$logFormatter` property of the base `ExceptionHandler`: 
```php
class MyExceptionHandler implements ExceptionHandler
{
    protected array $errorRenderers = [
        'application/json' => JsonRenderer::class,
        'application/xml'  => XmlRenderer::class,
        'text/xml'         => XmlRenderer::class,
        'text/html'        => PrettyPageRenderer::class,
        'text/plain'       => SimplePlainTextRenderer::class, // <-- Here
    ];

    /**
     * @var string Renderer for log messages
     */
    protected string $logFormatter = SimplePlainTextRenderer::class; // <-- And here

    // ...

}
```

A renderer can be registered using the `registerErrorRenderer` method of the `ExceptionHandler`. For example, by decorating an existing class/service : 
```php
public function register(): array
{
    return [
        ExceptionHandler::class => \DI\decorate(function (ExceptionHandler $handler) {
            $handler->registerErrorRenderer('text/plain', SimplePlainTextRenderer::class);

            return $handler;
        }),
    ];
}
```

It's also possible to replace a default renderer globally through Dependency Injection. Simply replace the definition of one renderer with your renderer in your service. For example, to replace `PlainTextRenderer` with `SimplePlainTextRenderer`.

```php
public function register(): array
{
    return [
        PlainTextRenderer::class => \DI\autowire(SimplePlainTextRenderer::class),
    ];
}
```
