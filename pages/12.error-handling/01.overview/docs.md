---
title: Overview
metadata:
    description: UserFrosting's powerful error handling system allows you to generate custom exceptions for various error conditions, and then handle those exceptions using custom error handlers.
taxonomy:
    category: docs
---

Things don't always go the way they were intended in your application.  Sometimes this is the client's fault, and sometimes it is the server's (i.e. _your_) fault.  When this happens, it is important that your application:

- Make every effort to recover from the situation;
- Communicate clearly to the client that there was an error, and who is at fault (them or the server);
- Provide detailed information about the error to the developer.

UserFrosting provides a framework for this process using custom PHP **[exceptions](http://php.net/manual/en/class.exception.php)** and **exception handlers**.

## The exception lifecycle

Any time an uncaught exception is thrown in your code, the underlying Slim application [catches it](https://www.slimframework.com/docs/handlers/error.html) and invokes a custom error handler.  This may be familiar to you if you've used Slim before.

The difference with UserFrosting is that it replaces Slim's default error handler with a custom error handler, `UserFrosting\Sprinkle\Core\Handler\CoreErrorHandler`.  `CoreErrorHandler` receives the exception, along with the current `Request` and `Response` objects.  Rather than handling the exception directly, though, `CoreErrorHandler` checks to see if the exception type has been registered with a custom **exception handler**.  If so, it invokes the corresponding exception handler; otherwise, it invokes the default `UserFrosting\Sprinkle\Core\Handler\ExceptionHandler`.

## Exception handlers

Every custom exception handler needs to implement two methods (as defined by the `ExceptionHandlerInterface`):

- `ajaxHandler` - Invoked when the exception was generated during an xhr (AJAX) request;
- `standardHandler` - Invoked when the exception was generated during a non-AJAX request

As a basic example, lets take a look at the `PhpMailerExceptionHandler` class, which handles exceptions generated when trying to send mail:

```php
<?php
namespace UserFrosting\Sprinkle\Core\Handler;

use UserFrosting\Support\Message\UserMessage;

/**
 * Handler for phpMailer exceptions.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class PhpMailerExceptionHandler extends ExceptionHandler
{
    public function ajaxHandler($request, $response, $exception)
    {
        $message = new UserMessage("MAIL_ERROR");

        $this->logFlag = true;

        $this->ci->alerts->addMessageTranslated("danger", $message->message, $message->parameters);

        return $response->withStatus(500);
    }

    public function standardHandler($request, $response, $exception)
    {
        $messages = [
            new UserMessage("MAIL_ERROR")
        ];
        $httpCode = 500;

        $this->logFlag = true;

        $view = $this->ci->view;

        $response = $view->render($response, 'pages/error/default.html.twig', [
                            "messages" => $messages
                        ])
                        ->withStatus($httpCode)
                        ->withHeader('Content-Type', 'text/html');

        return $response;
    }

}
```

As you can see, the `ajaxHandler` generates an error message and appends it to the [alert stream](/routes-and-controllers/alert-stream), allowing the client to fetch and render the message as necessary.  The response is simply an HTTP 500 status code, with no response body.

The `standardHandler`, on the other hand, renders a complete error page and appends it to the response.  You'll notice that the `error/default.html.twig` template simply lists any `UserMessage`s that were passed to it in the handler; of course, you can create a custom error page as you see fit and use that instead.

You'll also notice that in both cases we set the `logFlag` to true, which will tell the `CoreErrorHandler` that it should log the exception so that the developer or system administrator can review the problem later.  This makes sense for an exception that was raised while trying to send an email - perhaps there is a problem with the mail server, and we want the sysadmin to be able to go back and figure out what went wrong.

>>>>> You should always have your custom exception handlers extend the base `ExceptionHandler` class so that you do not need to re-implement the `getLogFlag` method.

### Registering custom exception handlers

Once you have defined your custom exception handler, you'll need to map the corresponding exception to it in your [service provider](/services/extending-services).

To do this, simply extend the `errorHandler` service and call the `register` method on the `$handler` object:

```php
$container->extend('errorHandler', function ($handler, $c) {
    // Register the MissingOwlExceptionHandler
    $handler->registerHandler('\UserFrosting\Sprinkle\Site\Model\Exception\MissingOwlException', '\UserFrosting\Sprinkle\Site\Handler\MissingOwlExceptionHandler');

    return $handler;
});
```

The first argument is the fully qualified name of the exception class, and the second argument is the fully qualified name of the handler class.  Notice that we need to use the **fully qualified** names, including the entire namespace!

### Debugging modes

Generally speaking, when an exception is thrown and a matching handler is found UserFrosting simply invokes the `ajaxHandler` or `standardHandler`, depending on the request type.  During development however, you may find it more convenient to simply output exception information directly to the browser.  This behavior can be controlled by two configuration parameters: `settings.displayErrorDetails`, and `site.debug.ajax`.

#### settings.displayErrorDetails

This setting, when enabled, controls two behaviors:

1. For AJAX requests, the `ajaxHandler` will be invoked as normal.  However, a detailed error report will be appended to the response as well, including a trace of the exception and a list of the request headers and parameters.
2. For standard requests, the `standardHandler` will **not** be invoked.  Instead, the detailed error report will be generated and appended to the response.  The exception will _not_ be logged, even if the handler has set its `logFlag` to true.

#### site.debug.ajax

If this setting is enabled AND `settings.displayErrorDetails` is enabled, then logging will be disabled and the message stream will be cleared when handling an exception thrown during an AJAX request.

This setting is also used by client-side code.  When `site.debug.ajax` is enabled and an error code is received by an AJAX call, your Javascript can use this information to decide whether or not to completely replace the current page with the error report page that was returned in the response.

For example, in the `ufAlerts` widget:

```js
base._newMessagesPromise = $.getJSON( base.options.url )
.done(function ( data ) {
    if (data) {
        base.messages = $.merge(base.messages, data);
    }

    base.$T.trigger("fetch.ufAlerts");
}).fail(function ( data ) {
    base.$T.trigger('error.ufAlerts');
    if ((typeof site !== "undefined") && site.debug.ajax && data.responseText) {
        document.write(data.responseText);
        document.close();
    } else {
        if (base.options.DEBUG) {
            console.log("Error (" + data.status + "): " + data.responseText );
        }
    }
});
```

You'll notice the block:

```js
if ((typeof site !== "undefined") && site.debug.ajax && data.responseText) {
    document.write(data.responseText);
    document.close();
}
```

This lets you display an error report when an exception is thrown during the AJAX request to the `/alerts` route.

>>>> By default, both `settings.displayErrorDetails` and `site.debug.ajax` are disabled in the production configuration environment.  Do not change this!  Displaying detailed exception traces in production is an extreme security risk and could leak sensitive passwords to your users and/or the public.

## Custom exceptions

UserFrosting comes with the following exceptions already built-in:

```bash
RuntimeException (built-in to PHP)
├── UserFrosting\Support\FileNotFoundException
├── UserFrosting\Support\JsonException
└── UserFrosting\Sprinkle\Core\Throttle\ThrottlerException

UserFrosting\Support\HttpException
├── UserFrosting\Support\BadRequestException
├── UserFrosting\Support\ForbiddenException
    ├── UserFrosting\Sprinkle\Core\Model\DatabaseInvalidException
    └── UserFrosting\Sprinkle\Account\Authenticate\Exception\AuthCompromisedException
├── UserFrosting\Sprinkle\Account\Authenticate\Exception\AccountDisabledException
├── UserFrosting\Sprinkle\Account\Authenticate\Exception\AccountInvalidException
├── UserFrosting\Sprinkle\Account\Authenticate\Exception\AccountNotVerifiedException
├── UserFrosting\Sprinkle\Account\Authenticate\Exception\AuthExpiredException
├── UserFrosting\Sprinkle\Account\Authenticate\Exception\InvalidCredentialsException
├── UserFrosting\Sprinkle\Account\Authorize\AuthorizationException
├── UserFrosting\Sprinkle\Account\Controller\Exception\SpammyRequestException
└── UserFrosting\Sprinkle\Account\Util\HashFailedException
```

You can define your own exceptions, of course, optionally inheriting from any of these existing exception types.  Every exception you define must eventually inherit back to PHP's base `Exception` class.

### HttpException

You'll notice that a large portion of UserFrosting's exception types inherit from the `HttpException` class.  This is an interesting exception (no pun intended) - it acknowledges that the _exception_ message (which you would want your developers and sysadmins to see), and the _client_ messages (which are displayed to the user to let them know that something went wrong), are generally different things.

The `HttpException` class acts like a typical exception, but it maintains two additional parameters internally: a list of messages that the exception handler may display to the client, and a status code that should be returned with the response.  As a simple example, consider the `AccountInvalidException`:

```php
<?php
namespace UserFrosting\Sprinkle\Account\Authenticate\Exception;

use UserFrosting\Support\Exception\HttpException;

class AccountInvalidException extends HttpException
{
    protected $default_message = 'ACCOUNT.INVALID';
    protected $http_error_code = 403;
}
```

It defines a default message, `ACCOUNT.INVALID`, that the registered exception handler can display on an error page or push to the alert stream.  It also sets a default HTTP status code to return with the error response.

The default message can be overridden when the exception is thrown in your code:

```php
$e = new AccountInvalidException("This is the exception message that will be logged for the dev/sysadmin.");
$e->addUserMessage("This is a custom error message that will be sent back to the client.  Hello, client!");
throw $e;
```

### Handling child exception types

Internally, `CoreErrorHandler` uses the `instanceof` method to map a given exception to a given handler.  This means that if your exception is an `instanceof` multiple different classes, for example if you inherited from another exception class, then `CoreErrorHandler` will use the handler mapped to the _last_ matching exception type.  For example, if you have an exception:

```php
<?php

class MissingOwlException extends MissingBirdException
{

}
```

And you have registered a handler for `MissingBirdException`:

```php
$container->extend('errorHandler', function ($handler, $c) {
    $handler->registerHandler('\UserFrosting\MissingBirdException', '\UserFrosting\MissingBirdExceptionHandler');

    return $handler;
});
```

Since `MissingOwlException` inherits from `MissingBirdException`, UserFrosting will use the `MissingBirdExceptionHandler` to handle your `MissingOwlException`, unless later on you register another handler specifically on the `MissingOwlException` type.
