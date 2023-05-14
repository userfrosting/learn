---
title: The Framework
metadata:
    description: A simple description of the UserFrosting Framework.
taxonomy:
    category: docs
---

The [**UserFrosting Framework**](https://github.com/userfrosting/framework/) contains the critical services required for UserFrosting to work. This is the only part of USerFrosting that is not considered a Sprinkle. The reason for it not being considered a Sprinkle is simple : The Framework contains the code required for the sprinkle system to work. If it was itself a sprinkle, we'll be in a loop!

Aside from managing the sprinkles (through the cleverly named _SprinkleManager_), the Framework is responsible for setting up the Slim/Symfony Console application and initiate the PHP-DI container.

## Shared Usage
The UserFrosting Framework also contains some parts that are not tied directly to UserFrosting. Theses parts could be used outside of UserFrosting, in a completely separate application.

The documentation for each parts are embedded in the next chapters, but you can still see each part documentation on it's own : 
 - [Cache](https://github.com/userfrosting/framework/tree/develop-5.0/src/Cache) : Wrapper function for Laravel cache system for easier integration of the cache system in standalone projects.
 - [Config](https://github.com/userfrosting/framework/tree/develop-5.0/src/Config) : Configuration files aggregator
 - [Fortress](https://github.com/userfrosting/framework/tree/develop-5.0/src/Fortress) : A schema-driven system for elegant whitelisting, transformation and validation of user input on both the client and server sides from a unified set of rules.
 - [i81n](https://github.com/userfrosting/framework/tree/develop-5.0/src/I18n) : The I18n module handles translation tasks.
 - [Session](https://github.com/userfrosting/framework/tree/develop-5.0/src/Session) : PHP Session wrapper
 - [UniformResourceLocator](https://github.com/userfrosting/framework/tree/develop-5.0/src/UniformResourceLocator) : The Uniform Resource Locator module handles resource aggregation and stream wrapper
