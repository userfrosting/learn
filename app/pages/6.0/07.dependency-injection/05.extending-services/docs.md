---
title: Extending Existing Services
description: You may extend UserFrosting's default services for additional functionality, or define completely new services in your sprinkles.
---

PHP-DI allows us to extend services that were defined previously, for example in another sprinkle, using [decorators](https://php-di.org/doc/definition-overriding.html#decorators).

Most of the default services that UserFrosting defines can be overridden in your sprinkle. However, some higher level services cannot be extended since they have already been invoked before the SprinkleManager can load the sprinkles. These services are mostly in the [UserFrosting Framework](structure/framework).

## Overriding Existing Services

Extending a service is done using the same callback used to **register** one, except said callback is registered with the `\DI\decorate` method (instead of a factory or other technique).

For example, if you want to extend the `ExceptionHandlerMiddleware` service to register a new handler :

```php
public function register(): array
{
    return [
        ExceptionHandlerMiddleware::class => \DI\decorate(function (ExceptionHandlerMiddleware $middleware, ContainerInterface $c) {
            $middleware->registerHandler(LoggedInException::class, LoggedInExceptionHandler::class);

            return $middleware;
        }),
    ];
}
```

The first parameter of the callable is the instance returned by the previous definition (i.e. the one we wish to decorate), the second parameter is the container.

> [!NOTE]
> When extending a service, UserFrosting will always apply the extension **on top** of the previously defined service. The service is defined following the sprinkle dependency tree. It's important to keep in mind you might not always receive the `core` sprinkle definition, for example, and that your own extension can be overwritten down the road by a subsequent sprinkle.
