---
title: Controller Classes
description: Controller classes allow you to easily separate the logic for your routes from your endpoint definitions.
obsolete: true
---

To keep your code organized, it is highly recommended to use **controller** or **action** classes. By separating your code in this way, you can easily see a list of the endpoints that a Sprinkle defines by looking at its route definitions. The implementation can then be tucked away in separate files.

> [!TIP]
> **Controller or Action?** Controller and Action classes are basically the same thing. The only difference is an **Action** handles one specific action (route) per class, while a **Controller** can handle many actions or routes. In other words, a controller class is composed of several actions, with one method per route. In this guide, we often use the term **Controller** for both implementations for simplicity, but we highly recommend you use the "one action = one class" method.

## Defining Controller Classes

A controller class doesn't require implementing any Interface or extending any other class. They are usually standalone classes, located in `src/Controller/`:

```php
<?php

namespace UserFrosting\Sprinkle\Site\Controller;

use UserFrosting\Sprinkle\Site\Model\Owl;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class OwlController
{
    public function getOwls(string $genus, Request $request, Response $response): Response
    {
        // GET parameters
        $params = $request->getQueryParams();

        $result = Owl::where('genus', $genus)->get();

        if ($params['format'] == 'json') {
            $payload = json_encode($result, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
            $response->getBody()->write($payload);
        } else {
            return $response->getBody()->write("No format specified");
        }
    }
}
```

The naming convention is that any route that generates a page is prefixed with `page`, while routes that retrieve JSON or other structured data begin with `get`. Methods that perform other options, like creating, updating, and deleting resources, should begin with the appropriate verb.

You'll notice that `$genus`, `$request` and `$response`, the same parameters that were required when using a closure, are now the parameters for our route method. In our front controller, we can tell our routes to use a controller class method as follows:

```php
use UserFrosting\Sprinkle\Site\Controller\OwlController;

// ...

$app->get('/api/owls/{genus}', [OwlController::class, 'getOwls']);
```

Slim will automatically invoke the method and PHP-DI [will inject](dependency-injection) the values of `$genus`, `$request`, `$response`.

## Service Injection

As mentioned before, PHP-DI will automatically inject the services you need in your controller thanks to the [PHP-DI Slim Bridge](https://php-di.org/doc/frameworks/slim.html#why-use-php-dis-bridge). So far we've seen examples where the route placeholder, Request, and Response were injected into the controller closure or method. However, controller parameters can be any of these things:

1. the request or response (parameters must be named $request or $response, or properly type-hinted)
2. route placeholders
3. [request attributes](https://www.slimframework.com/docs/v4/objects/request.html#attributes)
4. services (injected by type-hint)

You can mix all these types of parameters together, too. They will be matched by priority in the order of the list above. If the `VoleFinder` service was required in our previous example, the code would look like this:

```php
<?php

namespace UserFrosting\Sprinkle\Site\Controller;

use UserFrosting\Sprinkle\Site\Model\Owl;
use UserFrosting\Sprinkle\Site\Finder\VoleFinder;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class OwlController
{
    public function getOwls(string $genus, Request $request, Response $response, VoleFinder $voleFinder): Response
    {
        // ...
        $vole = $voleFinder->search($genus);
        // ...
    }
}
```

> [!TIP]
> The `Response` and `Request` services are not required to be injected into the method. While most of the time the `Response` is required to write to the page (except when throwing an exception, for example), the `Request` might not always be useful. In these cases, it's perfectly fine to omit it.

When writing controllers, which often handle multiple routes, sometimes services needs to be shared between methods. Those can be injected into the class constructor instead of each individual method:

```php
<?php

namespace UserFrosting\Sprinkle\Site\Controller;

use UserFrosting\Sprinkle\Site\Model\Owl;
use UserFrosting\Sprinkle\Site\Finder\VoleFinder;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class OwlController
{
    /**
     * Tip: Makes uses of PHP 8 new Constructor Promotion feature
     * https://www.php.net/manual/en/language.oop5.decon.php#language.oop5.decon.constructor.promotion
     */
    public function __construct(
        protected Twig $view,
        protected VoleFinder $voleFinder)
    {        
    }
    
    public function getOwls(string $genus, Request $request, Response $response): Response
    {
        // ...
        $vole = $this->voleFinder->search($genus);
        // ...
    }

    public function pageOwls(string $genus, Response $response): Response
    {
        // ...
        $vole = $this->voleFinder->search($genus);
        // ...
        $page = $this->view->render($response, 'pages/owls.html.twig');
        // ...
    }
}
```

> [!TIP]
> When many routes share the same code, for example permission validation, the controller can make use of a shared private method to handle this task, which makes the above syntax (i.e. many routes per class) interesting. But it's also possible to share the same code using a [Trait](https://www.php.net/manual/en/language.oop5.traits.php), a Service or a Middleware.

## Defining Action Classes

Action classes are technically the same as controller classes. When a controller focuses on one task instead of handling many routes, we call it an action class instead. Multiple simpler classes are preferred over one big class. The benefits are:
1. Easier to understand the code;
2. Easier to extend and overwrite the action;
3. Better code separation (smaller methods);
4. Possibility to reuse code (eg.: `DeleteOwlAction` and `DeleteNestAction` could extend the abstract `DeleteAction`);
5. Etc.

Further more, since the class only handles one task, we can make use of the [magic `__invoke` method](https://www.php.net/manual/en/language.oop5.magic.php#object.invoke):

```php
<?php

namespace UserFrosting\Sprinkle\Site\Controller;

use UserFrosting\Sprinkle\Site\Model\Owl;
use UserFrosting\Sprinkle\Site\Finder\VoleFinder;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class GetOwlsAction
{
    public function __invoke(string $genus, Request $request, Response $response, VoleFinder $voleFinder): Response
    {
        // ...
        $vole = $voleFinder->search($genus);
        // ...
    }
}
```

The route definition can now simply point to the whole class, instead of a specific method. By default, the `__invoke` method will be called:


```php
use UserFrosting\Sprinkle\Site\Controller\GetOwlsAction;

// ...

$app->get('/api/owls/{genus}', GetOwlsAction::class);
```
