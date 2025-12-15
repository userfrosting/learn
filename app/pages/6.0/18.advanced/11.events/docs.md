---
title: Events
obsolete: true
---

UserFrosting makes uses of *Event Dispatching* to enable customization of some built-in features. For example, when someone uses the login form, the following process is done : 

1. User info is found on the database based on it's username or password
2. User account is validated (is it enabled? Is it verified? etc.)
3. User is authenticated, i.e. it's password is verified
4. User is written to the session, ready to be used on subsequent requests

It's understandable that your would want to step in during this process that is the core feature of UserFrosting to implement an additional step specific to your project. That is why after step #2 is done, the `UserValidatedEvent` event will be dispatched, after step #3, `UserAuthenticatedEvent` event will be dispatched, and after step #4, the `UserLoggedInEvent` event will be dispatched. Each sprinkle can intercept these events and act upon then to change the default behavior. For example, the `UserLoggedInEvent` could be intercept to log the user activity.

The process of intercepting events and acting upon them is called **listening** to events through an **Event Dispatcher**. The [PSR-14 Standard](https://www.php-fig.org/psr/psr-14/) defines each part of an event dispatching system like this : 

> - **Event** - An Event is a message produced by an Emitter. It may be any arbitrary PHP object.
> - **Listener** - A Listener is any PHP callable that expects to be passed an Event. Zero or more Listeners may be passed the same Event. A Listener MAY enqueue some other asynchronous behavior if it so chooses.
> - **Emitter** - An Emitter is any arbitrary code that wishes to dispatch an Event. This is also known as the "calling code". It is not represented by any particular data structure but refers to the use case.
> - **Dispatcher** - A Dispatcher is a service object that is given an Event object by an Emitter. The Dispatcher is responsible for ensuring that the Event is passed to all relevant Listeners, but MUST defer determining the responsible listeners to a Listener Provider.
> - **Listener Provider** - A Listener Provider is responsible for determining what Listeners are relevant for a given Event, but MUST NOT call the Listeners itself. A Listener Provider may specify zero or more relevant Listeners.

A simple workflow used to visualize of the process of event dispatching would be : 

1. The **Emitter** decide to dispatch an **Event** : It create the *Event Object* and gives it to the **Dispatcher**
2. The **Dispatcher** ask the **Listener Provider** for a list of **Listener** relevant to the **Event**
3. The **Dispatcher** invoke each **Listener** sequentially, givin them the *Event Object* in the process
4. Each **Listener** act on the **Event** and returns the *Event Object* to the **Dispatcher**
5. The **Dispatcher** returns the *Event Object* to the **Emitter**

Let's go deeper in each part.

### Events

Events are objects that act as the unit of communication between an Emitter and appropriate Listeners. Events are essentially basic classes: they don't require to implement a specific interface. Event classes doesn't even need to contain any code. However, it's possible for events to contain other objects, which the listener can use. A vary basic example of this is the `UserLoggedInEvent`:

```php 
class UserLoggedInEvent
{
    /**
     * @param UserInterface $user
     */
    public function __construct(public UserInterface $user)
    {
    }
}
```

When it's created, the *Emitter* will define a user object as it's contractor argument. Because it's used a *public* property, the *Listeners* can have **read and write access** to it. The *Emitter* can retrieve the mutated version of the object when the dispatcher return the event to it.

> [!NOTE]
> Remember the goal of events are to be a container. It **can** contains other variables and object, but **shouldn't** act on them. As defined in the PSR-14 standard :
> > Event objects MAY be mutable should the use case call for Listeners providing information back to the Emitter. However, if no such bidirectional communication is needed then it is RECOMMENDED that the Event be defined as immutable; i.e., defined such that it lacks mutator methods.

#### Stoppable Events

A Stoppable Event is a special case of Event that contains additional ways to prevent further Listeners from being called. It is indicated by implementing the `Psr\EventDispatcher\StoppableEventInterface`.

An Event that implements `StoppableEventInterface` MUST return true from `isPropagationStopped()` when whatever Event it represents has been completed. Behind the scenes, the *Dispatcher* will test if `isPropagationStopped() === true` after each Listener has handled the event. If it is, the other listeners won't be called. 

For example, if the event purpose is to log an activity, and it should only be logged once based on the user permissions, propagation should be stopped once it's been successfully logged once to avoid duplicates.

### Listener

A Listener may be any PHP callable. In it's basic form, it's also a very basic class that doesn't requires to implement any interface, it must only have the `__invoke` method. The Listener's `__invoke` method MUST have one and only one parameter, which is the Event to which it responds, and should always return `void`.

> [!TIP]
> A listeners can listen to many events. It should type hint it's parameter as specifically as possible; that is, a Listener may type hint against an interface to indicate it is compatible with any Event type that implements that interface, or to a specific event class.

For example : 
```php
class BakeCommandListener
{
    public function __invoke(BakeCommandEvent $event): void
    {
        $event->addCommand('create:admin-user');
    }
}
```

This listener accept a `BakeCommandEvent`, which exposes some methods, like `addCommand`, that modify a list of commands stored in the `BakeCommandEvent` object. Since this listener doesn't stop the propagation of a stoppable event, other listeners can also add their own command to the event, and they'll even see that `create:admin-user` exist if they list all currently registered commands (and it it's executed *after* `BakeCommandListener` of course).

A listener can also delegate task to other code or service. It is definitively possible to inject a service in the service constructor method - Listeners will in fact be instantiated by the [dependency injection container](dependency-injection/the-di-container). For example : 

```php
class AssignDefaultRoles
{
    // Inject the Config service and RoleInterface Model
    public function __construct(
        protected Config $config,
        protected RoleInterface $roleModel,
    ) {
    }

    public function __invoke(UserCreatedEvent $event): void
    {
        // Do stuff...
    }
}
```

> [!TIP]
> An Exception or Error thrown by a Listener WILL block the execution of any further Listeners. An Exception or Error thrown by a Listener will propagate back up to the Emitter. Compared to stoppable event, the exception can (should) be catch by the emitter, making it very useful stop execution of of any further Listeners, but also the emitter code.

### Dispatcher

UserFrosting implements a PSR-14 compatible `EventDispatcherInterface`. This means you can inject the `Psr\EventDispatcher\EventDispatcherInterface` directly in any class to receive and instance of the UserFrosting event dispatcher.

```php 
use Psr\EventDispatcher\EventDispatcherInterface;

// ...

public function __construct(
    protected EventDispatcherInterface $eventDispatcher,
) {
}

// ...

$event = $this->eventDispatcher->dispatch($event);
```

The dispatcher only has one public method : `public function dispatch(object $event): object`. Any emitter must give the **Event** to the dispatcher, and in return should expect an object of the same type in return.

### Listener Provider

UserFrosting implements a PSR-14 compatible `Psr\EventDispatcher\ListenerProviderInterface`, used by the dispatcher. Sprinkles are not expected to access it directly: Invoking listeners should only be done thought the provided dispatcher.

It's only worth to know that UserFrosting listener provider will return the relevant listeners for a given event based on the [Sprinkle dependency order](sprinkles/recipe#getsprinkles), then the order they are registered (which we'll see next). Your sprinkle will always be the top sprinkle, so your listeners will always be invoked first. 

## Registering a listener

Registering a listener is done in the Sprinkle Recipe, thought the `getEventListeners` method and `UserFrosting\Event\EventListenerRecipe`. However, this recipe is different from other class you register in your recipe. You have to assign each listener to it's event. And because an event can have multiple listeners, we'll actually assign listeners to events. For example : 

```php 
use UserFrosting\Event\EventListenerRecipe; // Don't forget to import !

// ...

class MyApp implements
    SprinkleRecipe,
    EventListenerRecipe, // <-- Add this !
{

// ... 

public function getEventListeners(): array
{
    // event => [listeners]
    // First one is executed first
    return [
        AppInitiatedEvent::class => [
            RegisterShutdownHandler::class,
            ModelInitiated::class,
            SetRouteCaching::class,
        ],
        BakeryInitiatedEvent::class => [
            ModelInitiated::class,
            SetRouteCaching::class,
        ],
        ResourceLocatorInitiatedEvent::class => [
            ResourceLocatorInitiated::class,
        ],
    ];
}
```

> [!TIP]
> to get a compiled map of all registered events and their associated listeners, in the order returned by UserFrosting Listener Provider, you can use the debug bakery command :
> ```bash
> php bakery debug:events
> ```

## Built-in events

These are the events the Framework and default sprinkles uses. You can easily listen to them in your Sprinkle to customize the behavior of the built-in sprinkle.

| Event                                                            | Description                                                                                                       |
|------------------------------------------------------------------|-------------------------------------------------------------------------------------------------------------------|
| `UserFrosting\Event\AppInitiatedEvent`                           | Dispatched when the Slim App is ready to be run.                                                                  |
| `UserFrosting\Event\BakeryInitiatedEvent`                        | Dispatched when the Symfony Console App is ready to be run.                                                       |
| `UserFrosting\Sprinkle\Core\Bakery\Event\BakeCommandEvent`       | Dispatched when the `bake` command is about to be run. The list of subcommands that will be run can be manipulated using this event to insert custom subcommands into the callstack. |
| `UserFrosting\Sprinkle\Core\Bakery\Event\DebugCommandEvent`      | Dispatched when the `debug` command is about to be run.                                                           |
| `UserFrosting\Sprinkle\Core\Bakery\Event\DebugVerboseCommandEvent` | Dispatched when the `debug` command is about to be run ins verbose mode                                         | 
| `UserFrosting\Sprinkle\Core\Bakery\Event\SetupCommandEvent`      | Dispatched when the `setup` command is about to be run.                                                           |
| `UserFrosting\Sprinkle\Core\Event\ResourceLocatorInitiatedEvent` | Dispatched when the ResourceLocatorInterface is ready to be used. The locator itself is available in the handler. |
| `UserFrosting\Sprinkle\Account\Event\UserCreatedEvent`           | Dispatched when a user is created. User can be mutated by the listener (N.B.: any modification to the user need to be saved to the db by the listener) |
| `UserFrosting\Sprinkle\Account\Event\UserValidatedEvent` | This event is dispatched when the user is validated, before login or session is restored. A listener can throw an exception to interrupt the login, session or rememberme restoration process.
| `UserFrosting\Sprinkle\Account\Event\UserAuthenticatedEvent`     | This event is dispatched after the user is authenticated, but **before** it's logged in. A listener can throw an exception to abort the login process. User object is available in the event. |
| `UserFrosting\Sprinkle\Account\Event\UserLoggedInEvent`          | This event is dispatched when the user is logged in. If a listener throws an exception, an error page will be displayed, but on refresh the user will already be restore from the session. User object is available in the event. |
| `UserFrosting\Sprinkle\Account\Event\UserLoggedOutEvent`         | This event is dispatched when the user is logged out. A listener can throw an exception, and while the exception will interrupt the process, but since this is dispatched after session is closed, a refresh will keep the user logged out. | 
| `UserFrosting\Sprinkle\Account\Event\UserRedirectedAfterDenyResetPasswordEvent` | Define the destination route when a user use the deny the reset password link |
| `UserFrosting\Sprinkle\Account\Event\UserRedirectedAfterLoginEvent` | Define the destination route when a user login |
| `UserFrosting\Sprinkle\Account\Event\UserRedirectedAfterLogoutEvent` | Define the destination route when a user logout |
| `UserFrosting\Sprinkle\Account\Event\UserRedirectedAfterVerificationEvent` | Define the destination route when a user use the verification link |


## Helpers

Some Traits and Interfaces are available and can be used in your events.

| Event                                                                   | Description                                                                               |
|-------------------------------------------------------------------------|-------------------------------------------------------------------------------------------|
| `UserFrosting\Sprinkle\Core\Event\Contract\RedirectingEventInterface`   | Class using this interface can use `getRedirect` method to get where to redirect a user   |
| `UserFrosting\Sprinkle\Core\Event\Helper\RedirectTrait`                 | Implementation of `RedirectingEventInterface`                                             |
| `UserFrosting\Sprinkle\Core\Event\Helper\StoppableTrait`                | Implementation for `Psr\EventDispatcher\StoppableEventInterface`                          |
| `UserFrosting\Sprinkle\Core\Bakery\Event\AbstractAggregateCommandEvent` | Base event used to aggregate bakery sub-command in an umbrella command, similar to 'bake' |
