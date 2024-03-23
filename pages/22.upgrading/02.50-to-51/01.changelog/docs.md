---
title: What's new in 5.1
metadata:
    description: A detailed look at what's new in UserFrosting 5.1
taxonomy:
    category: docs
---

UserFrosting 5.1 focuses on adding PHP 8.3 support, removing PHP 8.1 support, upgrading Laravel, PHPUnit & FontAwesome dependencies and continue improving code quality.

## Changed Requirements
UserFrosting 5.1 removes support for PHP version 8.1, which has reached [End Of Life](http://php.net/supported-versions.php) as of November 2023 and won't receive security update as of November 2024. **PHP version 8.3 is now recommended**.

## Upgraded Dependencies
 - Update from Laravel 8 to Laravel 10
 - Update from PHPUnit 9 to PHPUnit 10
 - Update from Monolog 2 to Monolog 3
 - Update FontAwesome 5 to FontAwesome 6

## Detailed changes
Changes generally applied across UserFrosting includes : 
 - Add automated testing against MariaDB [#1238](https://github.com/userfrosting/UserFrosting/issues/1238) on each sprinkles
 - Complete 100% test coverage & PHPStan Level 8 validation across all default packages

### UserFrosting Framework
#### General
- Removed `src/Assets` (it isn't used as part of UserFrosting 5.0) 
- SprinkleManager is a bit more strict on argument types. Recipe classed must be a `class-string`. The actual instance of the class will now be rejected (it wasn't a documented feature anyway).

#### Fortress
Complete refactoring of Fortress. Mostly enforcing strict types, updating PHPDocs, simplifying code logic and making uses of new PHP features and method. Most classes have been deprecated and replaced by new classes with updated implementation. 

In general, instead of passing the *schema* in the constructor of Adapters, Transformers and Validators class, you pass it directly to theses class methods. This makes it easier to inject the classes as services and reuse the same instance with different schemas. 

[Check out the updated documentation](/routes-and-controllers/client-input/validation) for more information on new class usage, as well as the upgrade guide. 

#### Config
- Methods `getBool`, `getString`, `getInt` & `getArray` now return `null` if key doesn't exist, to make it on par with parent `get` method.

#### Alert
- Messages are now translated at read time ([#1156](https://github.com/userfrosting/UserFrosting/pull/1156), [#811](https://github.com/userfrosting/UserFrosting/issues/811)). Messages will be translated when using `messages` and `getAndClearMessages`. `addMessage` now accept the optional placeholders, which will be stored with the alert message. `addMessageTranslated` is **deprecated**. 
- Translator is not optional anymore. `setTranslator` method has been removed.
- `addValidationErrors` is deprecated (N.B.: It can't accept the new `\UserFrosting\Fortress\Validator\ServerSideValidatorInterface`)

#### UniformResourceLocator
- Two locations cannot have the same name anymore. An `InvalidArgumentException` will be thrown otherwise. (Ref [userfrosting/UserFrosting#1243](https://github.com/userfrosting/UserFrosting/issues/1243)).
- [*DEPRECATION*] Location's `getSlug` is deprecated (redundant with the name and not really used).

### Core sprinkle
#### Rework of the assets building commands
This change allows new bakery command to update Npm assets, and eventually allows sprinkles to replace webpack with something else (eg. Vite). The new commands are :
  - `assets:install` : Alias for `npm install`.
  - `assets:update` : Alias for `npm update`.
  - `assets:webpack` : Alias for `npm run dev`, `npm run build` and `npm run watch`, each used to run Webpack Encore.
  - `assets:build` : Aggregator command for building assets. Include by default `assets:install` and `assets:webpack`. The `webpack` and `build-assets` command are now alias of this command. `bake` also uses this command now. Sub commands can be added to `assets:build` by listening to `AssetsBuildCommandEvent`.

*tl;dr* : Use `php bakery assets:build` instead of `php bakery webpack` or `php bakery build-assets`. 

See [Assets Chapter](/asset-management) and [Bakery commands](/cli/commands) for more details.

#### Loggers changes
The different loggers now implement their own interface, for more flexibility with dependency injection. Debuggers should now be injected using their interface, instead of their class name.

| Class name         | Interface                   |
|--------------------|-----------------------------|
| DebugLogger        | DebugLoggerInterface        |
| ErrorLogger        | ErrorLoggerInterface        |
| MailLogger         | MailLoggerInterface         |
| QueryLogger        | QueryLoggerInterface        |
| UserActivityLogger | UserActivityLoggerInterface |

Behind the scene, each interface extends `Psr\Log\LoggerInterface`. Plus, instead of *extending* `Monolog\Logger`, each loggers now *wraps* `Monolog\Logger`. This makes the loggers decoupled from Monolog implementation, in favor of the PSR implementation, making it easier to replace Monolog if required.

Finally, `UserActivityLogger` used to define some constants. These have been moved to `UserActivityTypes` enum.

#### New bakery commands
- [`serve`](/cli/commands#serve) : Run the php built-in web server to test your application 
- [`debug:twig`](cli/commands#debug) : List all twig namespaces to help debugging

#### New Twig Function
Any configuration values can now be accessed in Twig using the [config](templating-with-twig/filters-and-functions#config) helper function. Before, only `site` subarray were available in Twig. Use this carefully, as sensitive information (ie. passwords) could be stored in config !

#### Misc changes
- Session database model now implements 
- `UserFrosting\Sprinkle\Core\Database\Models\Interfaces\SessionModelInterface`;
- Use our own RouterParser, wrapped around Slim's RouteParser. Allows to add 'fallback' routes when names routes are not found.

### Account sprinkle
Missing permissions slugs were missing from the original seed, and thus from to the database ([#1225](https://github.com/userfrosting/UserFrosting/issues/1225)). See the next page for more information on this.

### Admin Sprinkle
No significant changes.

### AdminLTE theme
- Add fallback routes when 'index' is not defined (Fix [#1244](https://github.com/userfrosting/UserFrosting/issues/1244))

## Complete change Log

See the changelog of each component for the complete list of changes included in this release.
- [Skeleton](https://github.com/userfrosting/UserFrosting/blob/5.1/CHANGELOG.md#510)
- [Framework](https://github.com/userfrosting/framework/blob/5.1/CHANGELOG.md#510)
- [Core sprinkle](https://github.com/userfrosting/sprinkle-core/blob/5.1/CHANGELOG.md#510)
- [Account sprinkle](https://github.com/userfrosting/sprinkle-account/blob/5.1/CHANGELOG.md#510)
- [Admin sprinkle](https://github.com/userfrosting/sprinkle-admin/blob/5.1/CHANGELOG.md#510)
- [AdminLTE sprinkle](https://github.com/userfrosting/theme-adminlte/blob/5.1/CHANGELOG.md#510)

## Migrating

Now that we've cover the basics changes, follow on to the next pages to the steps required to bring your app up to date with UserFrosting 5.1.
