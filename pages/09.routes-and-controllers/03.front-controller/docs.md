---
title: Front Controller
metadata:
    description: The front controller consists of the route definitions that UserFrosting uses to process incoming requests from the client.
taxonomy:
    category: docs
---
[plugin:content-inject](/modular/_update5.0)

The front controller is a collective term for the **routes** that your web application defines for its various **endpoints**. This is how UserFrosting links urls and methods to your application's code.

Sprinkles define their routes in files in the `routes/` subdirectory. The name of the file is unimportant - for a simple application you can create a file `routes/routes.php` and define all of your routes there. For larger applications, you may wish to divide them among multiple files. The Sprinkle manager will read routes from all files in the `routes/` subdirectory.

There are two ways to define a route - as a closure, or as a reference to a [controller class](/routes-and-controllers/controller-classes) method. We will use a simple closure example here to understand the concepts, but for your application **you should create controller classes**.

The following is an example of a `GET` route:

```php
$app->get('/api/users/u/{user_name}', function (Request $request, Response $response, array $args) {
    $userName = $args['user_name'];

    $getParams = $request->getQueryParams();

    $this->getContainer()->db;
    $result = User::where('user_name', $userName)->get();

    if ($getParams['format'] == 'json') {
        return $response->withJson($result, 200, JSON_PRETTY_PRINT);
    } else {
        return $response->write("No format specified");
    }
});
```

This is a very simplified example, but it illustrates the main features of a route definition. First, there is the call to `$app->get()`. The `get` refers to the HTTP method for which this route is defined. You may also define `post()`, `put()`, `delete()`, `options`, and `patch`, routes.

The first parameter is the url for the route. Routes can contain placeholders, such as `{user_name}` to match arbitrary values in a portion of the url. These placeholders can even be matched according to regular expressions. See the [Slim documentation ](https://www.slimframework.com/docs/v3/objects/router.html#route-placeholders) for a complete guide to url placeholders.

After the url comes the **closure**, where we place our actual route logic. The closure requires three parameters - the **request** object, which contains all the information from the client request, the **response** object, which is used to build the response that the server sends back to the client, and the `$args` parameter, which is an array of the values of any matched placeholders in the url.

In the example above, we grab the `user_name` placeholder from `$args`, and use it to look up information for that user from the database. We then use the value of the `format` query parameter from the request, to decide what to put in the response. You'll notice that the closure modifies and then returns the `$response` object, which is then passed back to the main Slim application. Slim will return the response to the client, perhaps modifying it further through the use of [middleware](https://www.slimframework.com/docs/concepts/middleware.html) first.

For a more detailed guide to routes, we highly recommend that you read the [Slim documentation](https://www.slimframework.com/docs/v3/objects/router.html).

## Overriding Routes

Routes themselves cannot be extended by other Sprinkles, but they can be overridden. To modify the behavior of one of the routes that ships with UserFrosting, you may simply redefine it in one of your route files. This definition will replace any routes for the endpoint defined in previously loaded Sprinkles.
