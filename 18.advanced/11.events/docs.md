---
title: Events
taxonomy:
    category: docs
---
[plugin:content-inject](/modular/_update5.0)

<!-- ### redirect.onAlreadyLoggedIn

Returns a callback that redirects the client when they attempt to perform certain guest actions, but they are already logged in. For example, if they attempt to visit the registration or login pages when they are already signed in, this service will be used to redirect them to an appropriate landing page. -->

<!-- ### redirect.onLogin

Returns a callback that sets the `UF-Redirect` header in the response. This callback is automatically invoked in the `AccountController::login` method. The `UF-Redirect` header is used by client-side code to determine where to redirect a given user after they log in.

See [_Changing the post-login destination_](/recipes/custom-login-page#changing-the-post-login-destination) for an example on how to customixzed this in your own sprinkle. -->

At the base level of each Sprinkle, you may optionally define a bootstrapper class. This is a class where you can hook into any of the five events mentioned above: `onSprinklesInitialized`, `onSprinklesAddResources`, `onSprinklesRegisterServices`, `onAppInitialize`, and `onAddGlobalMiddleware`. The name of the class must be the same as the name of the Sprinkle directory, but in [StudlyCaps](https://laravel.com/api/8.x/Illuminate/Support/Str.html#method_studly). The class itself must extend the base `UserFrosting\System\Sprinkle\Sprinkle` class.

Bootstrapper classes are basically implementations of [Symfony's `EventSubscriberInterface`](http://symfony.com/doc/current/components/event_dispatcher.html#using-event-subscribers), and they subscribe to the event dispatcher that is created in step 2 of the application lifecycle.

To add a listener for an event, simply create a method of the same name as the event in your bootstrapper class. The method should take one parameter - an `Event` object that contains any additional information the dispatcher chooses to include with the event. In the case of the `onAppInitialize` and `onAddGlobalMiddleware` events, this object will contain a reference to the Slim application that can be accessed from the Event's `getApp` method.

You should also add your listener to a static `getSubscribedEvents` method, which returns a list of events mapped to a list containing the listener method and the associated **priority integer**. You can control the order in which listeners in each bootstrapper class are executed, by setting this integer to a higher number. For each event, Sprinkles that assign a higher number to the corresponding listener method will cause that method to be executed earlier than Sprinkles that assigned a lower number to the event.

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

Notice that the base `Sprinkle` class has access to the application dependency injection container via `$this->ci`. This allows you to invoke other services, or even use the entire container, in your listener methods.

[notice=tip]For more information on event dispatching, subscribing, and listening, check out the [Symfony documentation](http://symfony.com/doc/current/components/event_dispatcher.html).[/notice]