---
title: Custom Commands
description: You may extend the UserFrosting\System\Bakery\BaseCommand class to implement your own CLI commands that can be run through Bakery.
obsolete: true
---

While the Bakery CLI tool comes with great built-in commands, your sprinkles can also take advantage of the Bakery by adding their own cli commands.

## Writing custom commands

Your Sprinkle Recipe can register any class extending the `\Symfony\Component\Console\Command\Command` class and implementing the `configure` and `execute` methods. Each registered command will then be automatically added to the list of available commands.

The `configure` method takes care of defining your command name and description. See [Configuring the Command](http://symfony.com/doc/current/console.html#configuring-the-command) from the Symfony documentation for more details. Arguments and options can also be defined in the `configure` method. Again, the [Symfony documentation](http://symfony.com/doc/current/components/console/console_arguments.html) is the place to look for more information on this.

Executing the command calls the `execute` method. From there you can interact with the user, interact with UserFrosting, or do whatever else you want to do. You can also add your own methods inside this class and access them from the _execute_ method.

### Interacting with the user

Interacting with the user can be done with the `SymfonyStyle` instance defined in `$this->io` when the `\UserFrosting\Bakery\WithSymfonyStyle` trait is used. For a complete list of available IO methods, check out the [Symfony documentation](http://symfony.com/doc/current/console/style.html#helper-methods).

### Interacting with UserFrosting

The UserFrosting service providers can be injected using the PHP-DI `#[Inject]` [PHP Attribute](https://php-di.org/doc/attributes.html#inject) or via the constructor method with Autowiring.

> [!WARNING]
> Because all Bakery commands extend a base class originating from Symfony, don't forget to call the parent constructor when injecting dependency through autowiring in the constructor. For example, to inject `EventDispatcherInterface`:
> ```php
> public function __construct(
> protected EventDispatcherInterface $eventDispatcher
> ) {
> parent::__construct(); // <-- Don't forget to add this!
> }
> ```

In all cases, Bakery command classes will be instantiated by the DI Container, and all dependencies will be properly injected at runtime.


## Command Class Template

This template can be used to create new commands. Don't forget to substitute the namespace with your own.

```php
<?php

namespace UserFrosting\App\Bakery;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UserFrosting\Bakery\WithSymfonyStyle;

/**
 * Sample Bakery command.
 */
class HelloCommand extends Command
{
    use WithSymfonyStyle;

    protected function configure(): void
    {
        $this->setName('hello')
             ->setDescription('Show hello world message');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title('Hello !');
        $this->io->success('Hello world');

        return self::SUCCESS;
    }
}
```

Now, you simply need to register your command in your [Sprinkle Recipe](/sprinkles/recipe). First, add the `BakeryRecipe` implementation. Then, register your command in the `getBakeryCommands()` method. Don't forget to import your class:

```php
<?php

namespace UserFrosting\App;

// ... 
use UserFrosting\App\Bakery\HelloCommand; // <-- Add this
// ...
use UserFrosting\Sprinkle\BakeryRecipe; // <-- Add this
// ...

class MyApp implements
    SprinkleRecipe,
    BakeryRecipe // <-- Add this
{
    // ...

    // Add this -->
    public function getBakeryCommands(): array
    {
        return [
            HelloCommand::class,
        ];
    }
    //<--
    
    // ...
}

```

The command can now be used:

**Command:**
```bash
$ php bakery hello
```

**Result:**
```txt
Hello !
=======

 [OK] Hello world                                                                                                       
                                                                                                                        
```
