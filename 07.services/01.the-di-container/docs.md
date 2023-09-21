---
title: The DI Container
metadata:
    description: The Dependency Injection (DI) container provides an elegant and loosely coupled way to make various services available globally in your application.
taxonomy:
    category: docs
---
[plugin:content-inject](/modular/_update5.0)

### Dependency Injection

[Dependency Injection](http://www.phptherightway.com/#dependency_injection) is one of the fundamental pillars of modern object-oriented software design - it is a prime example of the **D** in [**SOLID**](https://en.wikipedia.org/wiki/SOLID_(object-oriented_design)). The idea is that instead of creating objects _inside_ other objects, you create your "inner objects" (dependencies) separately and then _inject_ (by passing as an argument to the constructor or a setter method) them into the "outer object" (dependent).

For example, if you have class `Owl`:

```php
class Owl
{
    protected $nest;

    public function __construct()
    {
        $this->nest = new Nest();
    }
}
```

...an `Owl` would create its own `Nest` in its constructor:

```php
// Nest is automatically created in Owl's constructor

$owl = new Owl();
```

This might seem like a nice, convenient way of packaging things - after all, `Nest` seems like an implementation detail that we shouldn't have to worry about. However, what happens if we come along later with an `ImprovedNest`, and we want our `Owl` to use that instead?

Unfortunately we can't. Our classes `Owl` and `Nest` are what we would call **tightly coupled** - Owls can use Nests and _only_ Nests. Dependency injection solves this problem:

```php
class Owl
{
    protected $nest;

    public function __construct($nest)
    {
        $this->nest = $nest;
    }
}
```

now, we create our `Nest` externally to our `Owl`, and then pass it in:

```php
$nest = new Nest();

$owl = new Owl($nest);
```

If later we define `ImprovedNest` (either as an inherited class of `Nest`, or they both implement a common `NestInterface`), we can create Owls with different types of Nests:

```php
$nest = new Nest();
$improvedNest = new ImprovedNest();

$owl1 = new Owl($nest);
$owl2 = new Owl($improvedNest);
```

This is of course a contrived example, but the general strategy of keeping your classes loosely coupled is a good way to make your code more reusable and easily tested.

### The Dependency Injection (DI) Container

The obvious issue with dependency injection, of course, is that it becomes harder to encapsulate functionality. Injecting a `Nest` into an `Owl`'s constructor requires that we build the Nest ourselves, instead of delegating it to the Owl. For classes with many, many dependencies, we can end up with a lot of code just to create an instance. For example, let's look at the code required to create a new Monolog logging object:

```php
$logger = new Logger('debug');

$logFile = $c->locator->findResource('log://userfrosting.log', true, true);

$handler = new StreamHandler($logFile);

$formatter = new MixedFormatter(null, null, true);

$handler->setFormatter($formatter);
$logger->pushHandler($handler);
```

- Our `$logger` object requires the `$handler` object, which we inject using `pushHandler()`;
- The `$handler` object requires a `$formatter` object, which we inject using `setFormatter()`;
- The `$handler` object also requires the path to the log file, which we need to look up using the `locator`.

This is a lot of code to write just to create one measly object! It would be great if we could somehow encapsulate the creation of the object, but without creating tight couplings by doing that within the object itself.

This is where the **dependency injection container (DIC)** comes into play. The DIC handles basic management of dependencies, encapsulating their creation into simple callbacks. We will call these callbacks **services**. The DIC implementation that we use, _Slim's Container_, which itself [Pimple](http://pimple.sensiolabs.org/), has two powerful features that we rely on:

1. It creates dependencies lazily ("on demand"). My `$logger` (and its dependencies) won't be created until the first time I actually try to access them through the container (`$container->logger`).
2. Once an object has been created in the container, Pimple can return the same object in each subsequent call to the container. For example:

```php
$logger = $container->logger; // Pimple creates the Logger object

... // Do some stuff

$logger = $container->logger; // Pimple returns the same Logger it created earlier
```

Taken together, this means we can define our services without needing to worry about when and where their dependencies are created in our application's lifecycle.

[notice=note]When we talk about services, this might bring to mind an anti-pattern called the **Service Locator Pattern**. It is true that the DIC _can_ be used as a service locator, especially if you inject the entire container into your objects. With the exception of controllers and a few other types of classes that have a very large number of dependencies, we try to avoid implementing the Service Locator Pattern whenever possible.[/notice]

### Service Providers

UserFrosting sets up its services through **service provider** classes. Each Sprinkle can define a service provider class in `src/ServicesProvider/ServicesProvider.php`. A service provider class typically contains a single method, `register`, which takes a single argument, the Pimple DIC. For example, the `ServicesProvider` class in the `core` Sprinkle starts like this:

```php
class ServicesProvider
{
    /**
     * Register UserFrosting's core services.
     *
     * @param ContainerInterface $container A DI container implementing ArrayAccess and psr-container.
     */
    public function register(ContainerInterface $container)
    {
        /**
         * Flash messaging service.
         *
         * Persists error/success messages between requests in the session.
         */
        $container['alerts'] = function ($c) {
            return new MessageStream($c->session, $c->config['session.keys.alerts'], $c->translator);
        };

        ...

    }
```

The `alerts` service is defined by simply assigning a callback to `$container['alerts']` which returns our service object (in this case, an instance of `MessageStream`).

You'll notice that the callback itself takes a parameter `$c`, which is also a reference to the DIC. This allows us to reference services inside other services. For example, you'll notice that `MessageStream` depends on `$c->session`, `$c->config`, and `$c->translator`. These are other services that are defined further down in `ServicesProvider`. Thus, the first time we reference `$c->alerts`, it will not only create our `MessageStream` object, but any dependencies that have not yet been created as well.

The service provider class itself is registered during the [application lifecycle](/advanced/application-lifecycle), when each Sprinkle is set up. Alternatively, you can also register your own [custom services](/services/adding-services) manually.

Next, we list the **default services** that ship with UserFrosting. After that, we talk about how you can **add** your own services, **extend** existing services, or completely **replace** certain services in your own Sprinkle.
