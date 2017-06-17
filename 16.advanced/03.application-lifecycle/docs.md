---
title: Application Lifecycle
metadata:
    description: Each Sprinkle may define a bootstrapper class that allows it to hook into various stages of the UserFrosting application lifecycle.
taxonomy:
    category: docs
---

Every time UserFrosting is booted up to handle a request, it goes through its **application lifecycle**.  This process includes loading the resources and [services](/services) in your Sprinkles, setting up the [Slim application](https://www.slimframework.com/docs/objects/application.html), registering middleware, and setting up your [routes](/routes-and-controllers/front-controller).

At each stage in this process, events are triggered that you can hook into via a **Bootstrapper class** in your Sprinkle.

## Application lifecycle

The overall lifecycle is managed in the `UserFrosting/System/UserFrosting` class and proceeds as follows:

1. Create the [dependency injection container](/services/the-di-container).
2. Register basic system services, such as the [locator](/advanced/locator) service, Sprinkle manager, and the lifecycle event dispatcher.
3. Load the list of Sprinkles from `sprinkles.json`, and register each Sprinkle's bootstrapper class (if it exists) with the event dispatcher.
4. Fire the `onSprinklesInitialized` event.
5. Add each Sprinkle's resources (assets, config, templates, routes, etc) to the locator service.
6. Fire the `onSprinklesAddResources` event.
7. Register each Sprinkle's service provider (if it exists).
8. Fire the `onSprinklesRegisterServices` event.
9. Load the Slim application settings from the `config` service, and create the Slim application instance.
10. Fire the `onAppInitialize` event.
11. Load the route definitions in each Sprinkle's `routes/` directory.
12. Fire the `onAddGlobalMiddleware` event.
13. Invoke the `run` method on the Slim application.

## Bootstrapper classes

At the base level of each Sprinkle, you may optionally define a bootstrapper class.  This is a class where you can hook into any of the five events mentioned above: `onSprinklesInitialized`, `onSprinklesAddResources`, `onSprinklesRegisterServices`, `onAppInitialize`, and `onAddGlobalMiddleware`.  The name of the class must be the same as the name of the Sprinkle directory, but in [StudlyCaps](https://laravel.com/api/5.4/Illuminate/Support/Str.html#method_studly).  The class itself must extend the base `UserFrosting\System\Sprinkle\Sprinkle` class.

Bootstrapper classes are basically implementations of [Symfony's `EventSubscriberInterface`](http://symfony.com/doc/current/components/event_dispatcher.html#using-event-subscribers), and they subscribe to the event dispatcher that is created in step 2 of the application lifecycle.

To add a listener for an event, simply create a method of the same name as the event in your bootstrapper class.  The method should take one parameter - an `Event` object that contains any additional information the dispatcher chooses to include with the event.  In the case of the `onAppInitialize` and `onAddGlobalMiddleware` events, this object will contain a reference to the Slim application that can be accessed from the Event's `getApp` method.

You should also add your listener to a static `getSubscribedEvents` method, which returns a list of events mapped to a list containing the listener method and the associated **priority integer**.  You can control the order in which listeners in each bootstrapper class are executed, by setting this integer to a higher number.  For each event, Sprinkles that assign a higher number to the corresponding listener method will cause that method to be executed earlier than Sprinkles that assigned a lower number to the event.

### Sample bootstrapper class

As an example, consider the following bootstrapper class, which hooks into the `onAddGlobalMiddleware` and `onSprinklesInitialized` events:

```php
namespace UserFrosting\Sprinkle\Site;

use RocketTheme\Toolbox\Event\Event;
use UserFrosting\Sprinkle\Site\SomeRandomStaticClass;
use UserFrosting\System\Sprinkle\Sprinkle;

class Site extends Sprinkle
{
    /**
     * Defines which events in the UF lifecycle our Sprinkle should hook into.
     */
    public static function getSubscribedEvents()
    {
        return [
            'onAddGlobalMiddleware' => ['onAddGlobalMiddleware', 0],
            'onSprinklesInitialized' => ['onSprinklesInitialized', 0]
        ];
    }

    /**
     * Add custom global middleware.
     */
    public function onAddGlobalMiddleware(Event $event)
    {
        // Assume `myMiddleware` is a service that returns an instance of your middleware class,
        // and that you have defined this in your Sprinkle's service provider.
        $app = $event->getApp();
        $app->add($this->ci->myMiddleware);
    }

    /**
     * Set static references to DI container in necessary classes.
     */
    public function onSprinklesInitialized()
    {
        // Set container for SomeRandomStaticClass
        SomeRandomStaticClass::$ci = $this->ci;
    }
}

```

Notice that the base `Sprinkle` class has access to the application dependency injection container via `$this->ci`.  This allows you to invoke other services, or even use the entire container, in your listener methods.

>>>>>> For more information on event dispatching, subscribing, and listening, check out the [Symfony documentation](http://symfony.com/doc/current/components/event_dispatcher.html).
