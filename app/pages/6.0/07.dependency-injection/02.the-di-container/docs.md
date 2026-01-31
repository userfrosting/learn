---
title: The DI Container
description: The Dependency Injection (DI) container provides an elegant and loosely coupled way to make various services available globally in your application.
wip: true
---

### The Dependency Injection (DI) Container

The obvious issue with dependency injection, of course, is that it becomes harder to encapsulate functionality. Injecting a `Nest` into an `Owl`'s constructor requires that we build the Nest ourselves, instead of delegating it to the Owl. For classes with many, many dependencies, we can end up with a lot of code just to create an instance. Imagine an *Owl* that requires a *Nest*, that requires a *Tree*, that requires a *Forest*, etc.

As a more concrete example, let's look at the code required to create a new Monolog logging object:

```php
// 1° Create formatter
$formatter = new LineFormatter(null, null, true);

// 2° Create Handler, give him the formatter
$handler = new StreamHandler('userfrosting.log');
$handler->setFormatter($formatter);

//3° Create the Logger, give him the handler
$logger = new Logger('debug');
$logger->pushHandler($handler);
```

Three main steps are required to create the object:
1. Our `$logger` object requires the `$handler` object, which we inject using `pushHandler()`;
2. The `$handler` object requires a `$formatter` object, which we inject using `setFormatter()`;
3. The `$handler` object also requires the path to the log file.

This is a lot of code to write just to create one measly object! It would be great if we could somehow encapsulate the creation of the object without creating tight couplings within the object itself.

This is where the **dependency injection container (DIC)** comes into play. The DIC handles basic management of dependencies, encapsulating their creation into simple callbacks. We will call these callbacks **services**.

> [!NOTE]
> Dependency Injection (DI) and the Dependency Injection Container (DIC) are two separate concepts.
> 1. dependency injection is a method for writing better code
> 2. a container is a tool to help injecting dependencies
> You don't need a container to do dependency injection. However, a container can make injections easier.

UserFrosting uses [_PHP-DI 7_](https://php-di.org) as it's DIC implementation since it provides many powerful features that we rely on:

1. It creates dependencies lazily ("on demand"). Any service (and its dependencies) won't be created until the first time we access them.
2. Once an object has been created in the container, the same object is returned in each subsequent call to the container.
3. It has the ability to automatically create and inject dependencies.
4. It has powerful Slim 4 integration.

Taken together, we can define our services without needing to worry about when and where their dependencies are created in our application's lifecycle.

> [!NOTE]
> When we talk about services, this might bring to mind an anti-pattern called the **Service Locator Pattern**. It is true that the DIC _can_ be used as a service locator, especially if you inject the entire container into your objects. With the exception of Models and a few other classes with very large numbers of dependencies, we try to avoid implementing the Service Locator Pattern whenever possible.

### Autowiring

Let's go back to our basic Owl example:

```php
class Nest
{
    // ...
}

class Owl
{
    public function __construct(Nest $nest)
    {
        // ...
    }
}
```

When using _PHP-DI_ to create an Owl, the container detects that the constructor takes a `Nest` object (using [type declarations](http://www.php.net/manual/en/functions.arguments.php#functions.arguments.type-declaration)). Without any configuration, **and as long as the constructor argument is properly typed**, PHP-DI will create an `Nest` instance (if it wasn't already created) and pass it as a constructor parameter. The equivalent code would now be :

```php
$owl = $container->get(Owl::class);
```

It's very simple, doesn't require any configuration, and it just works !

> Autowiring is an exotic word that represents something very simple: the ability of the container to automatically create and inject dependencies.

> [!NOTE]
> You can learn more about autowiring in the [PHP-DI Documentation](https://php-di.org/doc/autowiring.html)

### Service Providers & Definitions

Sometimes classes might be a bit more complex to instantiate, especially third party ones (eg. the logger object from before). Or you might want to use a different class based on some configuration value. You might also want a class to be replaced by another one (eg. our `ImprovedNest`). In these cases, autowiring cannot be used. This is where PHP-DI **definition** comes handy. PHP-DI loads the definitions you have written and uses them like instructions on how to create objects.

UserFrosting sets up its services through **service provider** classes. Each sprinkle can define as many service providers as it needs and register them in the [Recipe](dependency-injection/adding-services). For example, the Services Provider class for the previous `Logger` example would look like this:

```php
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use UserFrosting\ServicesProvider\ServicesProviderInterface;

class LoggerServicesProvider implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            Logger::class => function (StreamHandler $handler, LineFormatter $formatter) {
                $handler->setFormatter($formatter);
                $logger = new Logger('debug');
                $logger->pushHandler($handler);

                return $logger;
            },

            StreamHandler::class => function () {
                // 'userfrosting.log' could be fetched from a Config service here, for example.
                return new StreamHandler('userfrosting.log');
            },

            LineFormatter::class => \DI\create()->constructor(null, null, true),
        ];
    }
}
```

This definition uses the [PHP-DI factories](https://php-di.org/doc/php-definitions.html#factories) syntax. From the PHP-DI documentation:

> Factories are PHP callables that return the instance. They allow to easily define objects lazily, i.e. each object will be created only when actually needed (because the callable will be called when actually needed).
>
> Just like any other definition, factories are called once and the same result is returned every time the factory needs to be resolved.
>
> Other services can be injected via type-hinting (as long as they are registered in the container or autowiring is enabled).

You'll notice that the callable used to create a `Logger` object takes two parameters: `StreamHandler` and `LineFormatter`. This allows us to inject these services inside the definition. When `Logger` is created (or injected), both `StreamHandler` and `LineFormatter` will be injected using their own definition.

> [!NOTE]
> The `LineFormatter` definition is different. It uses the [object syntax](https://php-di.org/doc/php-definitions.html#objects) instead of the _factories_ syntax.

> [!NOTE]
> You can learn more about PHP Definitions in the [PHP-DI Documentation](https://php-di.org/doc/php-definitions.html#definition-types)

### Binding Interfaces

Earlier we discussed the benefits of using interfaces, as the constructor can accept any class that implements the correct interface:

```php
public function __construct(NestInterface $nest) // Accept both `Nest` and `ImprovedNest`
```

In this case, _Autowiring_ can't help us since the `NestInterface` cannot be instantiated: it's not a class, it's an interface! In this case, PHP Definitions can be used to match the interface with the correct class we want, using either a factory, or the [Autowired object](https://php-di.org/doc/php-definitions.html#autowired-objects) syntax:

```php
return [
    // mapping an interface to an implementation
    NestInterface::class => \DI\autowire(ImprovedNest::class),
];
```

The "nest of choice" can now be selected in the service provider. It could also be selected using another kind of logic, for example using a `Config` service and the new for PHP 8.0 [match expression](https://www.php.net/manual/en/control-structures.match.php):
```php
return [
    // Inject Config to decide which nest to use, and the Container to get the actual class
    NestInterface::class => function (ContainerInterface $ci, Config $config) {
        return match ($config->get('nest.type')) {
            'normal'    => $ci->get(Nest::class),
            'fancy'     => $ci->get(ImprovedNest::class),
            default     => throw new \Exception("Bad nest configuration '{$config->get('nest.type')}' specified in configuration file."),
        };
    },
];
```

But why are interfaces really needed? If `ImprovedNest` extends `Nest`, wouldn't the constructor accept an `ImprovedNest` anyway if you type-hinted against `Nest`? Well, yes... But it won't work the other way around. For example :


```php
// This will work

class AcceptNest {
    public function __construct(protected Nest $nest)
    {
        // ...
    }
}

$improvedNest = $this->ci->get(Nest::class); // Return `ImprovedNest`, because service is configured this way
$test = new AcceptNest($improvedNest); // Works, ImprovedNest is a subtype of Nest

// This wont

class AcceptImprovedNest {
    public function __construct(protected ImprovedNest $nest)
    {
        // ...
    }
}

$nest = $this->ci->get(Nest::class); // Return `Nest`
$test = new AcceptImprovedNest($nest); // Throws TypeError Exception, Nest is not a subtype of ImprovedNest
```

> [!IMPORTANT]
> In most cases it's considered "best practice" to type-hint against interfaces, unless you explicitly required a specific class to fit a very specific need, said class is very basic and it's not worth it, or you don't plan on ever extending or distributing your code.

The next page shows a small list of the **default services** that ship with UserFrosting, as well as tips for using them. After that, we talk about how you can **add** your own services, **extend** existing services, or completely **replace** certain services in your own sprinkle.
