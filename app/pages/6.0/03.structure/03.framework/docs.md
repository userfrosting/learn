---
title: The Framework
description: A simple description of the UserFrosting Framework.
wip: true
---

The [**UserFrosting Framework**](https://github.com/userfrosting/framework/) contains the critical services required for UserFrosting to work. This is the only part of UserFrosting that is not considered a sprinkle. The reason for it not being considered a sprinkle is simple : the Framework contains the code required for the Sprinkle system to work. If it was a sprinkle itself, we'd be in a loop!

Aside from managing sprinkles (through the cleverly named _SprinkleManager_), the Framework is responsible for setting up the Slim/Symfony Console application and initiating the PHP-DI container.

## Shared Usage
The UserFrosting Framework also contains some parts that are not tied directly to UserFrosting. These parts could be used outside of UserFrosting, in a completely separate application.

The documentation for each part is embedded in the next chapters, but you can still see each part's documentation on it's own :
 - [Cache](https://github.com/userfrosting/framework/tree/5.1/src/Cache) : Wrapper function for Laravel cache system for easier integration of the cache system in standalone projects.
 - [Config](https://github.com/userfrosting/framework/tree/5.1/src/Config) : Configuration files aggregator
 - [Fortress](https://github.com/userfrosting/framework/tree/5.1/src/Fortress) : A schema-driven system for elegant whitelisting, transformation and validation of user input, on both the client and server sides, from a unified set of rules.
 - [i81n](https://github.com/userfrosting/framework/tree/5.1/src/I18n) : The I18n module handles translation tasks.
 - [Session](https://github.com/userfrosting/framework/tree/5.1/src/Session) : PHP Session wrapper
 - [UniformResourceLocator](https://github.com/userfrosting/framework/tree/5.1/src/UniformResourceLocator) : The Uniform Resource Locator module handles resource aggregation and stream wrapper
