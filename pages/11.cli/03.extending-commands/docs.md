---
title: Extending Aggregator Commands
metadata:
    description: You may add custom sub-commands to the bake, setup and debug commands through events.
taxonomy:
    category: docs
---

*Aggregator commands* is a fancy term to identify core bakery commands that, in reality, doesn't do more than run multiple sub-commands in one operation. UserFrosting uses 3 of those special commands:

1. [bake](/cli/commands#bake)
2. [setup](/cli/commands#setup)
3. [debug](/cli/commands#debug)

Those commands are typically used as "installation" steps. It this situation, it's much more simpler to run one command than run multiple ones. You can easily add your own command to any of those using [event listeners](/advanced/events#listener). 

## Adding Custom Commands to the `bake` command

The [`bake` command](/cli/commands#bake) combines many CLI commands into a one and is meant to be used as an "installation" process. Sprinkles can add new commands to `bake` by listening to the `\UserFrosting\Sprinkle\Core\Bakery\Event\BakeCommandEvent` in two easy steps. For example, let's add the `hello` command to `bake` :

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

In the recipe, you're telling UserFrosting to execute `BakeCommandListener` when the `BakeCommandEvent` is fired by UserFrosting event dispatcher. The listener itself doesn't require to implement any interface. It simply need to be callable, which the [`__invoke()`](https://www.php.net/manual/en/language.oop5.magic.php#object.invoke) magic method enables. This method accept a single argument, which will be the event itself. 

[notice=tip]A single listener can handle multiple events. The or (`|`) syntax can be used to type-hint against multiple event classes, or type-hinting can be omitted, or in this case the parent `AbstractAggregateCommandEvent` can be used to type-hint.[/notice]

The event listener uses `$event->addCommand('hello');` to add the "hello" command to the event. The command is passed by it's name as a string, not the class itself. This method will add the hello command to the end of the list of command "bake" will run. Other public methods exist on `$event` to place the command exactly where you need to it : 

| Command                        | Description                                          |
| ------------------------------ | ---------------------------------------------------- |
| `getCommands(): array`         | Return an all commands currently listed              |
| `setCommands(array):void`      | Set an array of commands, replacing the current list |
| `addCommand(string): void`     | Add a command at the end of the list                 |
| `prependCommand(string): void` | Add a command at the beginning of the list           |

The same command event can be listened by many sprinkle. In this case, dependent sprinkles will receive the event first, and pass it to the next one. Your sprinkle should then be the last one to receive the event, with all modifications from dependent sprinkles applied.

If you need to place your command at a specific place in the stack, you can use the `getCommands` method to retrieve the current list of array, modify it, and place it back using `setCommands` method. This method can also be used to **remove** commands.

[notice]You can learn more about Event Listening in [Chapter 18](/advanced/events).[/notice]

## Adding Custom Commands to the `setup` command

To add custom sub-commands to the `setup` aggregator, the same process as for the "bake" command can be used, but instead of listening to the `BakeCommandEvent` in the recipe, the listener is matched to the `SetupCommandEvent`.

## Adding Custom Commands to the `debug` command

To add custom sub-commands to the `debug` aggregator, two events are available to listen for :
1. `DebugCommandEvent`: Will always be executed when debug is run;
2. `DebugVerboseCommandEvent`: Will only execute the command when the verbose option (`-v`) is used;
