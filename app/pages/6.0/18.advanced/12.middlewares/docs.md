---
title: Middlewares
taxonomy:
    category: docs
---

Sometimes it can be useful to run code _before_ or _after_ the the request is handled, to either manipulate the Request and/or the Response objects. This can be done by _middlewares_. Middlewares can be used to add many feature to every page or simple routes, for example to add protection to the website, handle some configuration, inject some variable, etc.

## How does middleware work?

UserFrosting implement Slim's approach to handle middleware. Slim adds middleware as concentric layers surrounding your core application. Each new middleware layer surrounds any existing middleware layers. The concentric structure expands outwardly as additional middleware layers are added. This means, the last middleware layer added is the first to be executed.

When you run your UserFrosting application, the Request object traverses the middleware structure from the outside in. They first enter the outermost middleware, then the next outermost middleware, (and so on), until they ultimately arrive at the Slim application itself. After the Slim application dispatches the appropriate route, the resultant Response object exits the Slim application and traverses the middleware structure from the inside out. Ultimately, a final Response object exits the outermost middleware, is serialized into a raw HTTP response, and is returned to the HTTP client. Here's a diagram that illustrates the middleware process flow:

![Middleware](middleware.png)
[Source](https://www.slimframework.com/docs/v4/concepts/middleware.html)

## How do I write middleware?

Middleware are classes that must implement `Psr\Http\Server\MiddlewareInterface`. Those class must implement a `process` method that accepts two arguments: a `Request` object and a `RequestHandler` object. This class **MUST** return an instance of  `Psr\Http\Message\ResponseInterface`.

As we've seen before, a middleware can act before, or after a request is handled. The base class will look the same, the only difference between the tow is *when* the action is taken. For example:

**Before middleware**
```php
<?php

namespace UserFrosting\Sprinkle\Site\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ExampleBeforeMiddleware implements MiddlewareInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Do something ...

        return $handler->handle($request);
    }
}
```

**After middleware**
```php
<?php

namespace UserFrosting\Sprinkle\Site\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ExampleAfterMiddleware implements MiddlewareInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        
        // Do something ...

        return $response;
    }
}
```

[notice=tip]It's possible to pass variables from middleware via [request's attributes](https://www.slimframework.com/docs/v4/concepts/middleware.html#passing-variables-from-middleware).[/notice]

## How do I add middleware?

You may add middleware to the entire application, to an individual route or to a route group. All scenarios accept the same middleware and implement the same middleware interface.

### Application middleware

Application middleware is invoked for every **incoming** HTTP request. To add an application wide middleware, it must be registered in your Sprinkle Recipe, via the 

```php 
use UserFrosting\Sprinkle\MiddlewareRecipe; // Don't forget to import !

// ...

class MyApp implements
    SprinkleRecipe,
    MiddlewareRecipe, // <-- Add this !
{

// ... 

/**
 * Returns a list of all Middlewares classes.
 *
 * @return \Psr\Http\Server\MiddlewareInterface[]
 */
public function getMiddlewares(): array
{
    return [
        ExampleBeforeMiddleware::class,
        ExampleAfterMiddleware::class,
    ];
}
```

### Route middleware

Route middleware is invoked _only if_ the route is executed. Route middleware are registered directly in the route definitions, with the **add()** method. This example adds the `ExampleBeforeMiddleware` middleware to the `/myRoute` route :

```php
class MyRoutes implements RouteDefinitionInterface
{
    public function register(App $app): void
    {
        $app->get('/myRoute', MyController::class)->add(ExampleBeforeMiddleware::class);
    }
}
```

### Route group middleware

Middleware can also be added to a group of routes, defined in the **group()** multi-route definition functionality. You simply need to use the **add()** method on the group itself. As with route middleware, you can add as many middleware as you want. You can also add a route middleware inside a group middleware.

```php
class MyRoutes implements RouteDefinitionInterface
{
    public function register(App $app): void
    {
        $app->group('/foo', function (RouteCollectorProxy $group) {
            $group->get('/bar', FooBarGetAction::class);
            $group->post('/bar', FooBarPostAction::class);
        })->add(ExampleBeforeMiddleware::class)->add(ExampleAfterMiddleware::class);
    }
}
```

[notice]This page was inspired by [Slim's documentation](https://www.slimframework.com/docs/v4/concepts/middleware.html). You can find more information on their documentation.[/notice]