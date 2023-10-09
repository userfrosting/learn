---
title: Application Lifecycle
metadata:
    description: Each Sprinkle may define a bootstrapper class that allows it to hook into various stages of the UserFrosting application lifecycle.
taxonomy:
    category: docs
---

Every time UserFrosting is booted up to handle a request, it goes through its **application lifecycle**. This process includes loading the resources and [services](/services) in your Sprinkles, setting up the [Slim application](https://www.slimframework.com/docs/v3/objects/application.html), registering middleware, and setting up your [routes](/routes-and-controllers/front-controller).

At each stage in this process, some events are triggered that you can hook into via an **[Listener class](/advanced/events)** in your Sprinkle. The overall lifecycle is managed in the [UserFrosting Framework](/structure/framework) and proceeds as follows:

1. Initiate the *SprinkleManager* with the Main Sprinkle identifier.
2. Create the [dependency injection container](/services/the-di-container).
3. Register basic system services definitions, such as the *event dispatcher* inside the DI Container.
4. Register each Sprinkle services definitions inside the DI Container.
5. Build the container.
6. Register the *SprinkleManager* service inside the DI Container.
7. Create the Slim application instance ***or*** the Symfony Console Application.
8. The routes and global Middlewares are registered on the Slim App ***or*** commands are registered on the Symfony Console App.
9. Fires `AppInitiatedEvent` ***or*** `BakeryInitiatedEvent`.
10. Invoke the `run` method on the Slim ***or*** Console application.

[notice]When running the normal application (webpage), only the Slim Application and `AppInitiatedEvent` is fired. When using the Bakery CLI, the Slim App and associated event **is not** used. Instead, the Symfony Console application is created, and `BakeryInitiatedEvent` is fired. Both the App and Console can still be accessed trough Dependency Injection, which will handle injecting the routes or command as needed.[/notice]
