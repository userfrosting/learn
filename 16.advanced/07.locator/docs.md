---
title: Locator Service
taxonomy:
    category: docs
---

The locator service goal is to provides an abstraction layer for the different files and folders available across different sprinkles inside UserFrosting. In other words, it is a way of aggregating many search paths together. As you've seen in the previous chapter, each sprinkle can provides multiple resources and sprinkles can have the ability to overwrite a previous sprinkle resources. All of those resources, combined with the overwriting properties of a sprinkle is handled by the locator service. _Templates_ and _config_ files are a good example of those resources.

>>>> While the locator can be used to find files inside the sprinkles `src/` directory, it can't be directly used to handle _PHP Class Inheritance_. The [Dynamic Class Mapper](/advanced/class-mapper) needs to be used in such cases.<br />The locator *can* however be used where it's necessary to list PHP classes used to define objects or usable elements, such as _migrations_, _seeds_ and _Bakery commands_ related classes.

## Streams and Locations

The locator uses the concept of _Streams_ and _Locations_ to represent directories and Sprinkles respectively. Each active sprinkle is registered as a Locator Location on boot. Several streams are registered on boot by the _base system_ and the _core_ sprinkle.

Two types of streams are available within the locator : shared and non-shared streams. The non-shared streams, or normal streams, represent a directory within the Sprinkles folder. Shared streams exists only in . Note that both type can coexist. For example, the `bakery` stream is defined twice, once shared and once non-shared. This allows to find resources (in this cases Bakery commands), inside each sprinkles as well as inside the system (`app/system/`) directory.

### Default streams

The following streams are defined by default by UserFrosting :

Stream       | Sprinkle      | Path                                                   | Shared | Description / Use
-------------|---------------|--------------------------------------------------------|--------|------------------------------------------------------------------------------------------
`bakery`     | _base system_ | `app/system/Bakery/Command`                            | yes    | System Bakery commands
`bakery`     | _base system_ | `app/sprinkles/{sprinkleName}/src/Bakery`              | no     | Sprinkles [Bakery commands](/cli/custom-commands)
`sprinkles`  | _base system_ | `app/sprinkles/{sprinkleName}/`                        | no     | General path to each sprinkles
`cache`      | `core`        | `app/cache`                                            | yes    | Shared cache directory
`log`        | `core`        | `app/log`                                              | yes    | Shared log directory
`session`    | `core`        | `app/session`                                          | no     | Shared [sessions](/advanced/sessions#file-driver) directory
`config`     | `core`        | `app/sprinkles/{sprinkleName}/config`                  | no     | [Config files](/configuration/config-files) location
`extra`      | `core`        | `app/sprinkles/{sprinkleName}/extra`                   | no     | Misc directory, used to store files unrelated to any other stream
`factories`  | `core`        | `app/sprinkles/{sprinkleName}/factories`               | no     | Factory Muffin [factories definition](/testing/writting-tests/factories) used for testing
`locale`     | `core`        | `app/sprinkles/{sprinkleName}/locale`                  | no     | [Translation files](/advanced/i18n)
`routes`     | `core`        | `app/sprinkles/{sprinkleName}/routes`                  | no     | [Routes files](/routes-and-controllers/front-controller)
`schema`     | `core`        | `app/sprinkles/{sprinkleName}/schema`                  | no     | [Request Schema](/routes-and-controllers/client-input/validation#fortress) files
`templates`  | `core`        | `app/sprinkles/{sprinkleName}/templates`               | no     | [Templates files](/templating-with-twig/sprinkle-templates)
`seeds`      | `core`        | `app/sprinkles/{sprinkleName}/src/Database/Seeds`      | no     | [Seed Classes](/database/seeding)
`migrations` | `core`        | `app/sprinkles/{sprinkleName}/src/Database/Migrations` | no     | [Migration Classes](/database/migrations)
`assets`     | `core`        | See below                                              | both   | [Assets](/asset-management)

Note that the `assets` streams will register different paths depending of the current [Environment Mode](/configuration/config-files#environment-modes). When in production mode, the [compiled assets](/asset-management/compiled-assets) will be returned. Otherwise, the shared assets as well as the one from each sprinkles will be returned.

>>>>>> The `sprinkles` stream can be used as wildcard to access pretty much anything inside a sprinkle without defining a new [custom stream](#registering-a-custom-stream).


## Using the locator

The locator provides many public methods for managing streams, locations and resources. But the most important tools provided by the locator is **getting resources** and **listing resources**. As stated earlier, this will allows you to find files and directories across multiple sprinkles, while respecting the sprinkle order or priority, without having to manually loop all sprinkles manually.

### Getting resources

Whether you want to find a specific file or directory, the `getResource` and `getResources` methods will

Those two methods can be used to find paths for the specified URI. While `getResource` will return the top most file, `getResources` will return all the resources available for that URI, sorted by priority. For example:

```
$this->ci->locator->getResources('schema://default.json');

/*
[
    'app/sprinkles/Core/schema/default.json',
    'app/sprinkles/Account/schema/default.json',
    'app/sprinkles/MySite/schema/default.json',
]
*/

$this->ci->locator->getResource('schema://default.json');

// 'app/sprinkles/MySite/schema/default.json',
```

The locator will retuned an instance of the `Resource` object (or an array of objects). Theses objects can cast as string and will return the absolute path to the resource (file or directory). Further [public methods](https://github.com/userfrosting/UniformResourceLocator/tree/master/docs#resource-instance) can be used on the Resource object to get more informations about the returned resource. For example, to return the sprinkle name where it was found :

```
$schema = $this->ci->locator->getResource('schema://default.json');
echo $schema->getLocation()->getName();

// 'MySite'
```

Note theses methods can also work on directories URI :

```
$this->ci->locator->getResources('schema://foo');

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

```
$resources = $this->ci->locator->listResources('schema://');

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

```
$resources = $this->ci->locator->listResources('schema://', true);

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

### Going further

For more information about the locator, you can refer to the detailed documentation, API reference and examples on [the locator package repository](https://github.com/userfrosting/UniformResourceLocator/tree/master/docs).

## Registering a custom stream

While the `sprinkles` stream can be used to access pretty much anything inside a sprinkle, sometimes it can be useful to create your own stream. Not only will it create a shorter URI, but you can also accommodate different use case, such as the usage of a shared stream or multiple search path.

To register a new stream, the locator service can't be extended. Fortunately, a new stream can be registered pretty much anywhere. You can register a new location from your sprinkle service provider, or even the sprinkle master class (eg. `app/sprinkles/MySite/src/MySite.php`).

This later approach is recommended as you won't have to touch a particular service to use the stream or create a service only for this purpose. However, if the stream is tied to a particular service, it could be easier to register the stream at the same time as this service. For example, if a `foo` service uses the `bar` stream and all access to the `bar` stream is done inside the `foo` service, then it make sense to register the `bar` stream inside the service.


To register a new service, the locator `registerStream` method can be used. The first argument is the stream name, the second the [prefix](https://github.com/userfrosting/UniformResourceLocator/tree/master/docs#using-scheme-prefix) and the third one the relative search path (from the root dir for shared stream, from each sprinkle dir for non-shared streams). The fourth parameter can be set to `true` to indicate a shared stream.


For example :

```php
<?php

namespace UserFrosting\Sprinkle\MySite;

use UserFrosting\System\Sprinkle\Sprinkle;
use Psr\Container\ContainerInterface;

/**
 * Bootstrapper class for the `MySite` sprinkle.
 */
class MySite extends Sprinkle
{
    /**
     * Create a new Sprinkle object.
     *
     * @param ContainerInterface $ci The global container object, which holds all your services.
     */
    public function __construct(ContainerInterface $ci)
    {
        $this->ci = $ci;

        $this->registerStreams();
    }

    /**
     * Register sprinkle locator streams
     */
    protected function registerStreams()
    {
        /** @var \UserFrosting\UniformResourceLocator\ResourceLocator $locator */
        $locator = $this->ci->locator;

        // Register shared streams
        $locator->registerStream('storage', '', 'app/storage/', true);

        // Register sprinkle streams
        $locator->registerStream('storage', '', 'storage/');
        $locator->registerStream('orders', '', 'restaurant/orders/');

    }
}

```
