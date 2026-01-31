---
title: Sessions
description: UserFrosting offers an easy to use wrapper for PHP sessions, and supports session drivers for file and database storage mechanisms.
wip: true
---

HTTP itself is stateless - you may recall that we compare a web application to a [conversation between two agents with very poor memory](background/the-client-server-conversation). This presents a problem if we want to implement a "login" functionality - the server needs to be able to remember that someone has already authenticated in an earlier request!

Like other web frameworks, UserFrosting uses [PHP sessions](http://php.net/manual/en/intro.session.php) to solve this problem. When a user visits your site, PHP will place a random, hard-to-guess string called the **session id** in the `Set-Cookie` header of the response. This is automatically stored by the user's browser in a cookie. It uniquely identifies the user and cannot easily be guessed by anyone without access to the user's browser.

The next time a user makes a request _on your site_ (for example, visiting a page), your browser automatically sends the session id back to the server in the `Cookie` request header. By sending the session id to the server with each subsequent request, the server can resume the user's session that was saved after the last request. You can think of the session id as a temporary "password" that your browser automatically sends to the server with each request.

The session itself is nothing more than an associative array, which PHP stores in a file or other persistence mechanism, like the database or a high-performance cache.

## Using sessions for authentication

To actually associate a user's PHP session with their user account in the database, UserFrosting stores the user's `user_id` in their session after they [authenticate](users/user-accounts#login-form). This is stored in the `account.current_user_id` key of the session.

UserFrosting uses this stored `user_id` to retrieve the current user's complete account information when it is needed - for example, to determine the user's permissions or display personalized information to the user. Since the session is only stored on the _server side_, and users **do not** have direct access to read or modify their sessions, this prevents one user from impersonating another user (for example, the root user or someone with admin role).

## The session service

_Users_ may not have direct access to their session, but _your server-side code_ can access the current user's session via the [`session` service](services/default-services#session).

> [!WARNING]
> It is preferred to use the `session` service over PHP's `$_SESSION` superglobal.

You can inject the service in your class through Autowiring or Annotation injection on the `UserFrosting\Session\Session` class:

```php
use DI\Attribute\Inject;
use UserFrosting\Session\Session;

class MyClass
{
    #[Inject]
    protected Session $session;
}
```

### Accessing session values

We can access session data using [array dot notation](https://medium.com/@assertchris/dot-notation-3fd3e42edc61):

```php
echo $this->session['account.current_user_id'];
```

### Storing session values

You can store additional data in the session simply by using the assignment operator:

```php
$this->session['secret.api.key'] = $customerApiKey;
```

### Checking for existence

You can determine if a particular session key exists using the `has` method:

```php
if ($this->session->has('secret.api.key')) {
    // ...
}
```

### Regenerating the session

Sometimes you will want to regenerate the session id, for example to prevent [session fixation attacks](https://en.wikipedia.org/wiki/Session_fixation). To do this, use the `regenerateId` method:

```php
$this->session->regenerateId();
```

If you pass a value of `true` to this method, it will delete the old session record before creating the new session. Internally, this is just a wrapper for [`session_regenerate_id`](http://php.net/manual/en/function.session-regenerate-id.php). UserFrosting uses `regenerateId(true)` when logging a user in.

### Destroying a session

To completely destroy the current session and its data, use the `destroy` method:

```php
$this->session->destroy();
```

UserFrosting uses this method to destroy the session when a user logs out.

If you pass a value of `true` to this method, UserFrosting will set the expiration time for the current session cookie to a negative value, causing the browser to immediately delete the session cookie on the next request.

> [!IMPORTANT]
> You should avoid storing additional information in the user's session whenever possible. Sessions are difficult to scale and can cause concurrency issues when they are used excessively. You should instead store additional persistent data in the [database](database), [cache](advanced/cache) or another more robust storage mechanism, and associate it with the user's `user_id`.

## Session drivers

### File driver

By default, UserFrosting stores sessions in individual files in `app/sessions`. The associative arrays are encoded using PHP's [serialization format](http://php.net/manual/en/function.serialize.php#66147).

### Database driver

If you'd prefer, UserFrosting can store your sessions in the database instead. To use this option, the `sessions` table must exist in your database. This should have been created automatically for you in the [built-in migrations](database/default-tables#sessions).

To start using database sessions instead, set the value of `session.handler` to `database` in your [configuration file](configuration/config-files). With this option, the serialized session data will be encoded in a base-64 string.
