---
title: RESTful Responses
metadata:
    description: Your responses should use headers and status codes consistent with the HTTP specifications.  This section lists the HTTP codes commonly used by UserFrosting.
taxonomy:
    category: docs
---

## RESTful Responses

HTTP status codes have specific meanings, and your application should use the appropriate code in its responses.  The following is a list of the most commonly encountered HTTP status codes in UserFrosting:

## HTTP status codes

### 200 (OK)

The default status code used by the `Response` object.  You should use this code when, indeed, the client request was "successful" - however you define that.

### 301 (Moved Permanently)

You should use this whenever you permanently rename a route - especially for pages!  You want the old route to automatically resolve to your new URL, otherwise this could hurt your search engine rankings.

Recommended practice is to create a new route file in your Sprinkle, `routes/redirects.php`, and keep your redirect routes there:

```php
<?php

    $app->get('/old/dumb/url', function ($request, $response) {
        $target = $this->router->pathFor('newUrl');
        return $response->withRedirect($target, 301);
    });

    ...
```

Note that most major browsers perform the redirect automatically.

### 302 (Found)

This is typically used for temporary redirects; for example, to redirect your users after they log out of the site.  The logout route returns a 302 status code along with a `Location` header, to tell the client's browser where to redirect:

```php
    public function logout(Request $request, Response $response, $args)
    {
        // Destroy the session
        $this->ci->authenticator->logout();

        // Return to home page
        $config = $this->ci->config;
        return $response->withStatus(302)->withHeader('Location', $config['site.uri.public']);
    }
```

Note that most major browsers perform the redirect automatically when they receive a 302 response.

### 400 (Bad Request)

Respond with this code when the client has submitted an "invalid" request.  In most cases where the user's request has failed validation, `400` is the appropriate code to return.

>>>>> Don't return a 400 code if the error isn't the client's fault, or if the request was valid but refused for some other reason (like failing authorization, or a CSRF token check).

### 401 (Unauthorized)

Technically, this code is meant to be used with [HTTP Basic](https://en.wikipedia.org/wiki/Basic_access_authentication) and [HTTP Digest Authentication](https://en.wikipedia.org/wiki/Digest_access_authentication), which UserFrosting doesn't use.

However in lieu of a better alternative, UserFrosting has co-opted this code for its own authentication checks.  If an AJAX request fails because the user is **not logged in**, UserFrosting's `AuthGuard` middleware will return a 401 status code.

For non-AJAX requests (i.e., when visiting a page), if a request fails because the user is not logged in, a 302 status code will be returned instead, and the user will be redirected to the login page.

>>>>> Don't return a 401 code if the user is authenticated, but simply lacks the proper permissions.  A 403 should be used in this situation.

### 403 (Forbidden)

This code is almost always returned because a user has failed a `checkAccess` call.  Controller methods will commonly have a check like:

```php
if (!$authorizer->checkAccess($currentUser, 'uri_users')) {
    throw new ForbiddenException();
}
```

The default `ForbiddenExceptionHandler` that handles `ForbiddenException`s will automatically generate an error message/page response with a 403 response code.

In some cases, you may not want to disclose to unauthorized users that the resource even _exists_.  In this case, you can [override](/advanced/error-handling) the `ForbiddenExceptionHandler` with your own handler and have it return a 404 error instead.

### 404 (Not Found)

This code is your classic "could not find the thing you're looking for" error.  To trigger this code manually in UserFrosting, you'll need to throw a `NotFoundException`:

```php

use Slim\Exception\NotFoundException;

...

    public function updateField($request, $response, $args)
    {
        $user = $this->getUserFromParams($args);

        // Will cause a 404 response
        if (!$user) {
            throw new NotFoundException($request, $response);
        }
        
        ...
    }
```

### 405 (Method Not Allowed)

This code is [automatically returned by Slim](https://www.slimframework.com/docs/handlers/not-allowed.html), when a route exists for a given URL, but not for the requested method.  For example, if someone tries to `POST` to a URL, but there is only a `GET` route defined.

### 429 (Too Many Requests)

This code is returned by the [throttler](/routes-and-controllers/client-input/throttle), when a request's rate limit has been exceeded.

### 500 (Internal Server Error)

This code is a generic "something didn't work right, but it's not your fault" error.  UserFrosting uses this code whenever we want to let the client know that something went wrong, but don't want to provide any further details.

For example, the client probably doesn't care whether your database is down, your mail server stopped working, or there is a missing semicolon in your last commit.

By default when an exception is thrown and no registered exception handler is found, UserFrosting invokes the base `ExceptionHandler`.   This handler returns a 500 status code.

### 503 (Service Unavailable)

You should return this code, for example, if you absolutely need to have your application down for a period of time (for example, for maintenance). 
