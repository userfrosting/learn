---
title: Front Controller
description: The front controller consists of the route definitions that UserFrosting uses to process incoming requests from the client.
obsolete: true
---

The front controller is a collective term for the **routes** that your web application defines for its various **endpoints**. This is how UserFrosting links URLs and methods to your application's code.

Sprinkles define their routes in classes and register them in their Recipe. There are two ways to define a route - as a closure, or as a reference to a [controller class](/routes-and-controllers/controller-classes) method. We will use a simple closure example here to illustrate the concept, but for your application, **you should create controller classes**.

The following is an example of a `GET` route:

```php
$app->get('/api/users/u/{username}', function (string $username, Request $request, Response $response, array $args) 
{
    $getParams = $request->getQueryParams();

    $result = User::where('user_name', $username)->get();

    if ($getParams['format'] == 'json') {
        $payload = json_encode($result, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
        $response->getBody()->write($payload);
    } else {
        return $response->getBody()->write("No format specified");
    }
});
```

This is a very simplified example, but it illustrates the main features of a route definition. First, there is the call to `$app->get()`. The `get` refers to the HTTP method for which this route is defined. You may also define `post()`, `put()`, `delete()`, `options()`, and `patch()` routes.

The first parameter is the URL for the route. Routes can contain placeholders such as `{username}` to match arbitrary values in a portion of the URL. These placeholders can even be matched according to regular expressions: see the [Slim documentation](https://www.slimframework.com/docs/v4/objects/routing.html#route-placeholders) and [PHP-DI Slim's Bridge Documentation](https://php-di.org/doc/frameworks/slim.html#route-placeholder-injection) for a complete guide to URL placeholders.

After the URL comes the **closure**, where we place our actual route logic. In this example, the closure uses three parameters - a **placeholder** variable, the **request** object (which contains all the information from the client request), and the **response** object (which is used to build the response that the server sends back to the client). These parameters can vary from route to route. Behind the scenes, PHP-DI will intelligently inject the proper services and variables into the closure--more on that in a bit.

In the example above, we use the `username` placeholder to look up information for that user from the database. We then use the value of the `format` query parameter from the request to decide what to put in the response. You'll notice that the closure writes to the body of the `$response` object before returning. Slim will return the response to the client, perhaps first modifying it further through the use of [middleware](/advanced/middlewares).

For a more detailed guide to routes, we highly recommend that you read the [Slim documentation](https://www.slimframework.com/docs/v4/objects/routing.html) and [PHP-DI Slim's Bridge Documentation](https://php-di.org/doc/frameworks/slim.html#why-use-php-dis-bridge).
