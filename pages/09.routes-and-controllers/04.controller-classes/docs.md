---
title: Controller Classes
metadata:
    description: Controller classes allow you to easily separate the logic for your routes from your endpoint definitions.
taxonomy:
    category: docs
---

To keep your code organized, it is highly recommended to use controller classes.  By separating your code in this way, you can easily see a list of the endpoints that a Sprinkle defines by looking at its route files.  The implementation can then be tucked away in separate files.

## Defining Controller Classes

UserFrosting provides a base class, `UserFrosting\Sprinkle\Core\Controller\SimpleController`, that can be extended with methods that implement your controller logic.  An easy way to define a new controller class is to extend this class in your Sprinkle, under `src/Controller/`:

```
<?php

namespace UserFrosting\Sprinkle\Site\Controller;

use UserFrosting\Sprinkle\Core\Controller\SimpleController;
use UserFrosting\Sprinkle\Site\Model\Owl;

class OwlController extends SimpleController
{
    public function getOwls($request, $response, $args)
    {
        $genus = $args['genus'];

        // GET parameters
        $params = $request->getQueryParams();

        $this->ci->db;
        $result = Owl::where('genus', $genus)->get();
        
        if ($params['format'] == 'json') {
            return $response->withJson($result, 200, JSON_PRETTY_PRINT);
        } else {
            return $response->write("No format specified");
        }
    }
    
    ...
    
}
```

The basic idea is to have one method per route.  The naming convention is for any route that generates a page to be prefixed with `page`, while methods that retrieve JSON or other structured data begin with `get`.  Methods that perform other options, like creating, updating, and deleting resources, begin with the appropriate verb.

You'll notice that `$request`, `$response`, and `$args`, the same parameters that were required when using a closure, are now the parameters for our route method.  In our front controller, we can tell our routes to use a controller class method as follows:

```
$app->get('/api/owls', 'UserFrosting\Sprinkle\Site\Controller\OwlController:getOwls');
```

Slim will automatically invoke the method and pass in the values of `$request`, `$response`, and `$args`.

`SimpleController` really just defines a constructor that takes the [DI container](/services/the-di-container) as an argument, and stores it in its `$ci` member variable.  UserFrosting services can then be accessed in the controller via `$this->ci`.

## Decoupling Services

The entire dependency injection container is passed to a SimpleController child class as a convenience, and is not necessarily the best design choice.  You may wish to implement controller classes that explicitly define their dependencies.  To do this, you would register these controllers themselves in your Sprinkle's [service provider](/services/the-di-container#service-providers):

```
$container['UserFrosting\Sprinkle\Site\Controller\OwlController'] = function ($c) {
    return new UserFrosting\Sprinkle\Site\Controller\OwlController($c['view'], $c['voleFinder']);
};
```

Then your class definition would look like:

```
<?php

namespace UserFrosting\Sprinkle\Site\Controller;

use Slim\Views\Twig;
use UserFrosting\Sprinkle\Site\Finder\VoleFinder;

final class OwlController
{
    private $view;
    private $voleFinder;

    public function __construct(Twig $view, VoleFinder $voleFinder)
    {
        $this->view = $view;
        $this->voleFinder = $voleFinder;
    }
    
    ...
    
}
```
