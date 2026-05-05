---
title: CSRF Protection
metadata:
    description: Cross-site request forgeries (CSRF) are a type of social engineering attack in which a malicious agent tricks a user into submitting a valid, but unintended request to your server.  UserFrosting mitigates this risk with a secret token embedded into all forms on your website.
taxonomy:
    category: docs
---

Cross-site request forgeries (CSRF) are a type of social engineering attack in which a malicious agent tricks a user into submitting a valid, but unintended request to your server.  This can happen, for example, when a user opens a malicious email or website while they are still signed in to your website.

By exploiting the default behavior of certain types of HTML tags, the malicious source can cause your client's browser to automatically submit a particular request to your server.  This request could be anything from "post a message" on an online forum, to "transfer money to account XXXXXX" in a banking system!

## Protecting Against CSRF Attacks

The simplest way to reliably defend against CSRF attacks is by using a cryptographically secure [secret token](https://www.owasp.org/index.php/CSRF_Prevention_Cheat_Sheet#Synchronizer_.28CSRF.29_Tokens) in any part of your client-side application that is capable of performing state-changing operations on the user's account (typically forms and Javascript widgets that can submit POST, PUT, or DELETE requests).

The idea is that when your server receives one of these requests, it must contain the secret token issued earlier.  Requests made through a legitimate portion of your application will have the secret token embedded in them, while forgeries will not - and cannot - because they do not have the ability to *retrieve* data from the user's session.

## CSRF Guard

UserFrosting uses the [Slim CSRF Guard](https://github.com/slimphp/Slim-Csrf) middleware to automatically generate new CSRF tokens and check for them at the beginning of POST, PUT, DELETE and PATCH requests.  The tokens consist of a name-value pair.  All that is necessary from the developer point of view is to inject the CSRF tokens into any point in the application where a user could make one of these types of requests.

### Injecting the Tokens Into Forms

The easiest way to add the CSRF tokens to a form is by including the partial Twig template `pages/partials/csrf.html.twig`.  Simply add this after the opening `<form>` tag:

```twig
<form id="sign-in" role="form" action="{{site.uri.public}}/account/login" method="post">
    {% include "forms/csrf.html.twig" %}
    
    ...
</form>
```

This will automatically add the hidden input fields `csrf_name` and `csrf_value` to your form, along with their values.

### Injecting the Tokens Into AJAX Requests

Not all requests that require CSRF protection originate from HTML forms.  To inject the tokens into AJAX requests, you may access them via the global `site` Javascript variable.

```js
var url = site.uri.public + '/api/users/u/bob';

var data = {
    // Put whatever data you are submitting here
};

data[site.csrf.keys.name] = site.csrf.name;
data[site.csrf.keys.value] = site.csrf.value;

$.ajax({
    type: 'PUT',
    url: url,
    data: data
});
```

>>>>> The Javascript `site` variable is declared in the `pages/partials/config.js.twig` template.

### Blacklisting Routes

>>>> Please note that this has changed slightly as of v4.1.8.  We now require that you explicitly add the leading `/` to your blacklisted routes, to be consistent with the way the route definitions themselves are declared.

Sometimes, you need to bypass CSRF protection for certain requests.  For example, if you want to avoid opening the session to retrieve the CSRF token, or if you are creating an API that is not meant for consumption by a user via a browser, but rather by some other application.

To bypass CSRF protection, you can map regular expressions to arrays of HTTP methods in the `csrf.blacklist` configuration setting:

```php
'csrf' => [
    'blacklist' => [
        '^/api/hoots' => [
            'POST'
        ],
        '^/api/screeches' => [
            'POST',
            'PUT'
        ]
    ]
],
```

Any requests whose URL matches one of these regular expressions, and whose method matches one of the mapped methods, will be automatically exempted from loading the CSRF middleware.  This means that the CSRF token will not be retrieved (for `GET` requests) or checked (for `POST`, `PUT`, `DELETE`, and `PATCH` requests).

Requests for [raw assets](/asset-management/basic-usage#PublicassetURLs) are automatically exempted from CSRF protection in the `config` [service](/services/default-services#config).
