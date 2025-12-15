---
title: Locator Service
obsolete: true
---

The locator service goal is to provides an abstraction layer for the different files and folders available across different sprinkles inside UserFrosting. In other words, it is a way of aggregating many search paths together. As you've seen in the previous chapters, each sprinkle can provides multiple resources and sprinkles can have the ability to overwrite a previous sprinkle resources. All of those resources, combined with the overwriting properties of a sprinkle is handled by the locator service. _Templates_ and _config_ files are a good example of those resources.

While the locator can be used to find files inside the sprinkles main directory (i.e. `app/`), it can't be directly used to handle _PHP Class Inheritance_. The [Dependency Injection](dependency-injection) needs to be used in such cases. The locator *can* however be used where it's necessary to list PHP files.

## Streams and Locations

The locator uses the concept of *Streams* and *Locations* to represent *directories* and *Sprinkles* respectively. Each active sprinkle is registered as a *Locator Location* on boot. Several default streams are registered by the *Core Sprinkle*.

Two types of streams are available within the locator : **shared** and **non-shared** streams. The shared streams (or normal streams) represent a directory within the Sprinkles folder, which can usually be found in every sprinkles. Shared streams exists only in one place. Note that both type can coexist. For example, a stream can be defined twice, once shared and once non-shared. This allows to find resources inside each sprinkles as well as inside the public directory.

### Default streams

The following streams are defined by default by UserFrosting :

| Stream    |  Defined By   | Path              | Shared | Description / Use                                                                |
|-----------|:-------------:|-------------------|:------:|----------------------------------------------------------------------------------|
| sprinkles | Core Sprinkle | `./`              |   No   | Generic path to each sprinkles                                                   |
| config    | Core Sprinkle | `./config`        |   No   | [Config files](configuration/config-files) location                             |
| extra     | Core Sprinkle | `./extra`         |   No   | Misc directory, used to store files unrelated to any other stream                |
| locale    | Core Sprinkle | `./locale`        |   No   | [Translation files](i18n)                                                       |
| schema    | Core Sprinkle | `./schema`        |   No   | [Request Schema](routes-and-controllers/client-input/validation#fortress) files |
| templates | Core Sprinkle | `./templates`     |   No   | [Templates files](templating-with-twig/sprinkle-templates)                      |
| cache     | Core Sprinkle | `./cache`         |  Yes   | Shared [cache](advanced/caching) directory                                      |
| database  | Core Sprinkle | `./database`      |  Yes   | Location of any file based database, for example SQLite database                 |
| logs      | Core Sprinkle | `./logs`          |  Yes   | Shared log directory                                                             |
| sessions  | Core Sprinkle | `./sessions`      |  Yes   | Shared [sessions](advanced/sessions#file-driver) directory                      |
| storage   | Core Sprinkle | `./storage`       |  Yes   | The [local disk](advanced/storage#the-local-disk) file storage                  |
| public    | Core Sprinkle | `/public`         |  Yes   | Absolute path to the [public directory](sprinkles/content#-public)              |
| assets    | Core Sprinkle | `public://assets` |  Yes   | Path to the public [assets](asset-management) directory. Sub-stream of *public* |

The paths for non-shared streams are calculated relatively from each [sprinkle path](sprinkles/recipe#getpath), usually `./app`. The paths for shared streams are relative from the Main Sprinkle path only, unless otherwise noted.

> [!TIP]
> The `sprinkles` stream can be used as wildcard to access pretty much anything inside a sprinkle without defining a new [custom stream](#registering-a-custom-stream).

### Debugging locations

The bakery command provide a debugging command to help you understand and validate all the paths returned by the locator service: 

```bash
$ php bakery debug:locator
```

This command displays :
1. The root path (from the main Sprinkle);
2. Registered locations (Sprinkles), with the path of each sprinkle;
3. A list of all registered streams;
4. All schemes paths (Each schemes with all calculated paths for each scheme);

## Using the locator

The locator provides many public methods for managing streams, locations and resources. But the most important tools provided by the locator is **getting resources** and **listing resources**. As stated earlier, this will allows you to find files and directories across multiple sprinkles, while respecting the sprinkle order or priority, without having to manually loop all sprinkles manually.

The first step is to inject the locator service in your class through Autowiring or Annotation injection on the `UserFrosting\UniformResourceLocator\ResourceLocatorInterface` class:

```php
use DI\Attribute\Inject;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

class MyClass
{
    #[Inject]
    protected ResourceLocatorInterface $locator;

    public function myMethod(): void
    {
        // $this->locator...
    }
}
```

or

```php
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

class MyClass
{
    public function __construct(protected ResourceLocatorInterface $locator)
    {
    }

    public function myMethod(): void
    {
        // $this->locator...
    }
}
```

### Getting resources

Whether you want to find a specific file or directory, the `getResource` and `getResources` methods will

Those two methods can be used to find paths for the specified URI. While `getResource` will return the top most file, `getResources` will return all the resources available for that URI, sorted by priority. For example:

```php
$this->locator->getResources('schema://default.json');

// RESULT :
/*
[
    'app/sprinkles/Core/schema/default.json',
    'app/sprinkles/Account/schema/default.json',
    'app/sprinkles/MySite/schema/default.json',
]
*/


$this->locator->getResource('schema://default.json');

// RESULT :
// 'app/sprinkles/MySite/schema/default.json',
```

The locator will return an instance of the `Resource` object (or an array of `Resource` objects). These objects can be cast as string and will return the absolute path to the resource (file or directory). Further [public methods](https://github.com/userfrosting/framework/tree/5.1/src/UniformResourceLocator/docs#resource-instance) can be used on the Resource object to get more information about the returned resource. For example, to return the sprinkle name where it was found :

```php
$schema = $this->locator->getResource('schema://default.json');
echo $schema->getLocation()->getName();

// RESULT :
// 'MySite'
```

Note these methods can also work on directories URI :

```php
$this->locator->getResources('schema://foo');

// RESULT :
/*
[
    'app/sprinkles/Core/schema/foo',
    'app/sprinkles/Account/schema/foo',
    'app/sprinkles/MySite/schema/foo',
]
*/
```

### Listing resources

All available resources in a given directory can be listed using the `listResources` method. This method will also returns the resources recursively, unlike `getResources`.

```php
$resources = $this->locator->listResources('schema://');

// RESULT :
/*
[
    'app/sprinkles/Core/schema/develop.json',
    'app/sprinkles/Core/schema/testing.json',
    'app/sprinkles/Account/schema/test/foo.json',
    'app/sprinkles/MySite/schema/default.json',
]
*/
```

If every sprinkle have a `default.json` file, only the top most version will be returned. To return all instances of every resources, the `all` flag can be set to `true` :

```php
$resources = $this->locator->listResources('schema://', true);

// RESULT :
/*
[
    'app/sprinkles/Core/schema/develop.json',
    'app/sprinkles/Core/schema/testing.json',
    'app/sprinkles/Core/schema/default.json',
    'app/sprinkles/Account/schema/default.json',
    'app/sprinkles/Account/schema/test/foo.json',
    'app/sprinkles/MySite/schema/default.json',
]
*/
```

## Registering a custom stream

While the `sprinkles` stream can be used to access pretty much anything inside a sprinkle, sometimes it can be useful to create your own stream. Not only will it create a shorter URI, but you can also accommodate different use case, such as the usage of a shared stream or multiple search path.

A new stream can be registered pretty much anywhere. If the stream is tied to a particular service, it could be easier to register the stream at the same time as this service. For example, if a `foo` service uses the `bar` stream and all access to the `bar` stream is done inside the `foo` service, then it make sense to register the `bar` stream inside the service. However, the recommended method to register a new stream is through *event listening*.

When the locator service is initialized, the `ResourceLocatorInitiatedEvent` will be dispatched by the global event dispatcher. Each sprinkle can listen for this even and use it to register their own stream on the locator service. In fact, even the default stream are registered this way!

To register a new service, a new instance of `UserFrosting\UniformResourceLocator\ResourceStream` can be created and passed to the locator through the `addStream` method. 

For example, let's create a listener class that will register a `foo` stream, and a `bar` shared stream:

```php
<?php

namespace UserFrosting\Sprinkle\Site\Listeners;

use UserFrosting\Sprinkle\Core\Event\ResourceLocatorInitiatedEvent;
use UserFrosting\UniformResourceLocator\ResourceStream;

class ResourceLocatorInitiated
{
    /**
     * Add a new `foo` stream.
     *
     * @param ResourceLocatorInitiatedEvent $event
     */
    public function __invoke(ResourceLocatorInitiatedEvent $event): void
    {
        // Normal Stream
        $stream = new ResourceStream('foo');
        $event->locator->addStream($stream);
        
        // Shared Stream with custom path
        $stream = new ResourceStream('bar', path: 'foobar', shared: true),
        $event->locator->addStream($stream);
    }
}
```

> [!TIP]
> Other service can be injected inside this class

The listener then need to be registered in the Sprinkle Recipe:

```php
namespace UserFrosting\Sprinkle\Site;

// ... 
use UserFrosting\Event\EventListenerRecipe; // <-- Add this
use UserFrosting\Sprinkle\Site\Listeners\ResourceLocatorInitiated; // <-- Add this
// ...

class MyApp implements
    SprinkleRecipe,
    EventListenerRecipe // <-- Add this
{
    // ...

    // Add this -->
    public function getEventListeners(): array
    {
        return [
            ResourceLocatorInitiatedEvent::class => [
                ResourceLocatorInitiated::class,
            ],
        ];
    }
    //<--
    
    // ...
}
```

## Going further

For more information about the locator, you can refer to the detailed documentation, API reference and examples on [the Framework repository](https://github.com/userfrosting/framework/tree/5.1/src/UniformResourceLocator/docs).