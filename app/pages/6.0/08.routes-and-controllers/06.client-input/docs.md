---
title: Client Input
metadata:
    description: Retrieving client input (GET, POST, PUT, DELETE, and URL arguments) in your controllers.
taxonomy:
    category: docs
---

There is no such thing as a `$_GET` array or a `$_POST` array - at least, not according to the [HTTP specifications](https://en.wikipedia.org/wiki/HTTP#Message_Format). These superglobals are merely constructs offered by PHP to make your life more "convenient".

The problem with `$_GET` and `$_POST` is that despite their names, they don't actually have anything to do with the request methods `GET` and `POST` at all. Rather, `$_GET` retrieves variables from the [URL query string](http://php.net/manual/en/reserved.variables.get.php), while `$_POST` retrieves variables submitted as part of a form. For some reason, PHP has arbitrarily tangled up the *data representation* with the request method. Not to mention that [global variables are a terrible idea](http://softwareengineering.stackexchange.com/questions/148108/why-is-global-state-so-evil) in general!

Fortunately, Slim saves the day by providing a more HTTP-friendly way of accessing data supplied by the request. Rather than thinking in terms of "GET parameters" and "POST parameters", we should think in terms of the different components of the HTTP request. [Recall](/routes-and-controllers/rest) that a request consists of a **url**, **method**, **headers**, and optionally, a **body** - any of these could potentially contain data that we'd want to use in one of our controller methods.

## Retrieving URL Parameters

There are really two places in the URL that could contain information we'd want to retrieve - the path itself, and the query string.

Variables that we'd want to retrieve from the path itself are typically declared in the [route definition](/routes-and-controllers/front-controller). These can be retrieved using [Route Placeholder Injection](https://php-di.org/doc/frameworks/slim.html#route-placeholder-injection) by simply adding a parameter with the same name to the controller. PHP-DI will directly inject it by indexing it with the name of the placeholder. For example, suppose we have a route defined as:

```php
$app->get('/api/users/u/{username}', function (string $username, Request $request, Response $response) {
    ...
});
```

If someone submits a request to `/api/users/u/david`, then the value `'david'` will be available at `$username`.

The other place where we'd typically find client-supplied data is in the query string (the part of the URL after the `?`). In this case, we can access these variables through the `$request` parameter, using the `getQueryParams` method:

```php
// request was GET /api/users/u/david?format=json

$params = $request->getQueryParams();

// Displays 'json'
echo $params['format'];
```

> [!NOTE]
> By default, browsers typically send data (from AJAX requests, etc) for `GET` requests through the query string. Again, this does **not** mean that query strings == GET.

## Retrieving Body Parameters

Slim [provides a number of methods](https://www.slimframework.com/docs/v4/objects/request.html#the-request-body) for retrieving data from the body. The two most common scenarios involve retrieving data that was submitted from a form, and uploaded files.

### Form Data

To get client-submitted form data, use the `getParsedBody()` method on `$request`:

```php
// request was POST /api/users, with form values username => 'kevin' and password => 'hunter2'

$params = $request->getParsedBody();

// Displays 'kevin'
echo $params['username'];
```

> [!NOTE]
> Again, browsers typically send data from `POST` requests through the message body, but this does not mean that message body and POST are equivalent concepts.

### Uploaded Files

To get files that have been [encoded in the HTTP request body](http://stackoverflow.com/a/26791188/2970321) (for example using an HTML `<input type='file' ...` element), use the `getUploadedFiles()` method:

```
$files = $request->getUploadedFiles();
```

## Retrieving Headers

To retrieve request headers, use `$request->getHeaders()`, `$request->getHeader()`, or `$request->getHeaderLine()`. See [Slim's documentation](https://www.slimframework.com/docs/v4/objects/request.html#the-request-headers) for more information.

## Retrieving the Method

Usually, you'll already know the HTTP method by the time you've started executing code in your controller, because it will be explicitly mentioned in your route definition. Nonetheless, there are circumstances when you need to determine the HTTP method dynamically:

```php
$method = $request->getMethod();
```
