---
title: Understanding Dependency Injection
metadata:
    description: Dependency Injection (DI) is the backbone of modern programming
taxonomy:
    category: docs
---

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

Unfortunately, we can't. Our classes `Owl` and `Nest` are what is called **tightly coupled** - Owls can use Nests and _only_ Nests. Dependency injection solves this problem:

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

If later we define `ImprovedNest`, we can create Owls with different types of Nests:

```php
$nest = new Nest();
$improvedNest = new ImprovedNest();

$owl1 = new Owl($nest);
$owl2 = new Owl($improvedNest);
```

But how can an owl be sure it's receiving a *nest*, and not a *dog house*? That's where **Interfaces** comes into play. If both `Nest` and `ImprovedNest` implements a `NestInterface` Interface, then the *Owl* can be sure it will receive the proper object : 

```php
interface NestInterface
{
    public function getSize(): string;
}

class Nest implements NestInterface
{
    public function getSize(): string
    {
        return 'Small';
    }
}

class ImprovedNest extends Nest
{
    public function getSize(): string
    {
        return 'Enormous';
    }
}

class Owl
{
    protected NestInterface $nest;

    public function __construct(NestInterface $nest)
    {
        $this->nest = $nest;
    }

    public function getNestSize(): string
    {
        $this->nest->getSize();
    }
}
```

In the above example, it doesn't matter if `Owl` received a `Nest` or an `ImprovedNest`, or even a `SuperDuperNest`, as long as they all obey the same definition defined by the `NestInterface`. Moreover, the Owl class can confidently call the `getSize` method of the injected `$nest` property, because the interface makes sure that method is available, no matter which implementation of the `NestInterface` it receives. 

Using interfaces to declare what kind of object a class is expected to receive, even if you don't plan to have multiple "nest" types, is a key element in *Autowiring* that we'll see shortly.

This is of course a contrived example, but the general strategy of keeping your classes loosely coupled is a good way to make your code more reusable and easily tested.

[notice=tip]You can learn more, and see other examples, on the [PHP-DI Website : Understanding Dependency Injection](https://php-di.org/doc/understanding-di.html).[/notice]
