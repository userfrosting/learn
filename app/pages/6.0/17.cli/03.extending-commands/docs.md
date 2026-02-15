---
title: Extending Aggregator Commands
description: You may add custom sub-commands to the bake, setup, and debug commands through events.
---

*Aggregator commands* is a fancy term to identify core bakery commands that just run multiple sub-commands in one operation. UserFrosting uses 4 of those special commands:

1. [assets:build](/cli/commands#assetsbuild)
2. [bake](/cli/commands#bake)
3. [setup](/cli/commands#setup)
4. [debug](/cli/commands#debug)

Those commands are typically used as "installation" steps. It this situation, it's much more simpler to run one command than multiple ones. You can easily add your own command(s) to any of these aggregators using [event listeners](/advanced/events#listener).

## Adding Custom Commands to the `bake` command

The [`bake` command](/cli/commands#bake) combines many CLI commands into one and is meant to be used as an "installation" process. Sprinkles can add new commands to `bake` by listening to the `\UserFrosting\Sprinkle\Core\Bakery\Event\BakeCommandEvent` in two easy steps. For example, let's add the `hello` command to `bake` :

1. Create a custom listener

    **app/src/Bakery/BakeCommandListener.php**
    ```php
    <?php

    namespace UserFrosting\App\Bakery;

    use UserFrosting\Sprinkle\Core\Bakery\Event\BakeCommandEvent;

    class BakeCommandListener
    {
        public function __invoke(BakeCommandEvent $event): void
        {
            $event->addCommand('hello');
        }
    }
    ```

2. Register your listener in your Sprinkle Recipe

    ```php
    <?php

    namespace UserFrosting\App;

    // ...
    use UserFrosting\Event\EventListenerRecipe; // <-- Add this
    use UserFrosting\App\Bakery\BakeCommandListener; // <-- Add this
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
                BakeCommandEvent::class => [
                    BakeCommandListener::class,
                ],
            ];
        }
        //<--

        // ...
    }
    ```

In the recipe, you're telling UserFrosting to execute `BakeCommandListener` when the `BakeCommandEvent` is fired by the UserFrosting event dispatcher. The listener itself isn't required to implement any interface. It simply needs to be callable, which the [`__invoke()`](https://www.php.net/manual/en/language.oop5.magic.php#object.invoke) magic method enables. This method accepts a single argument, which will be the event itself.

> [!TIP]
> A single listener can handle multiple events. The or (`|`) syntax can be used to type-hint against multiple event classes, or type-hinting can be omitted, or in this case the parent `AbstractAggregateCommandEvent` can be used to type-hint.

The event listener uses `$event->addCommand('hello');` to add the "hello" command to the event. The command is passed by its name as a string, not the class itself. This method will add the hello command to the end of the list of commands which "bake" will run. Other public methods exist on `$event` to place the command exactly where you need it:

| Command                        | Description                                          |
| ------------------------------ | ---------------------------------------------------- |
| `getCommands(): array`         | Return an all commands currently listed              |
| `setCommands(array):void`      | Set an array of commands, replacing the current list |
| `addCommand(string): void`     | Add a command at the end of the list                 |
| `prependCommand(string): void` | Add a command at the beginning of the list           |

The same command event can be listened by many sprinkles. In this case, dependent sprinkles will receive the event first, then pass it to the next one. Your sprinkle should then be the last one to receive the event, with all modifications from dependent sprinkles applied.

If you need to place your command at a specific place in the stack, you can use the `getCommands` method to retrieve the current list, modify it, and place it back using `setCommands` method. This method can also be used to **remove** commands.

> [!NOTE]
> You can learn more about Event Listening in [Chapter 19](/advanced/events).

## Adding Custom Commands to the `assets:build` command

To add custom sub-commands to the `assets:build` aggregator, the same process as for the "bake" command can be used, but instead of listening to the `BakeCommandEvent` in the recipe, the listener is matched to the `AssetsBuildCommandEvent`.

This is particularly useful if you want to add custom asset compilation steps. For example, you might want to run additional build tools or post-processing steps after the default asset build commands.

## Adding Custom Commands to the `setup` command

To add custom sub-commands to the `setup` aggregator, the same process as for the "bake" command can be used, but instead of listening to the `BakeCommandEvent` in the recipe, the listener is matched to the `SetupCommandEvent`.

## Adding Custom Commands to the `debug` command

To add custom sub-commands to the `debug` aggregator, two events are available to listen for:
1. `DebugCommandEvent`: Will always be executed
2. `DebugVerboseCommandEvent`: Will only execute when the verbose option (`-v`) is used
