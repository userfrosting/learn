---
title: Custom Bakery Commands
taxonomy:
    category: docs
---

While the Bakery CLI tool comes with great build in commands, your Sprinkles can also take advantage of the Bakery by adding their own cli commands. 

## Writing custom commands

Any class defined in your sprinkle `src/Bakery` directory will be picked up by Bakery and automatically added to the list of available commands. Your custom command should extend the `UserFrosting\System\Bakery\BaseCommand` class and implement the `configure` and `execute` methods. 

The `configure` method takes care of defining your command name and description. See [Configuring the Command](http://symfony.com/doc/current/console.html#configuring-the-command) from the Symfony documentation for more details. Arguments and options can also be defined in the `configure` method. Again, the [Symfony documentation](http://symfony.com/doc/current/components/console/console_arguments.html) is the place to look for more information on this.

The `execute` method is the one called when the command is executed. From there you can interact with the user, interact with UserFrosting or do whatever else you want to do. You can also add your own methods inside this class and access them from the execute method. 

### Interacting with the user

Interacting with the user can be done with the `SymfonyStyle` instance defined in `$this->io`. For a complete list of available IO methods, check out the [Symfony documentation](http://symfony.com/doc/current/console/style.html#helper-methods).

### Interacting with UserFrosting

The UserFrosting service providers can be accessed by using `$this->ci` which holds an instance of the [The DI Container](/services/the-di-container). The project root directory path is also available in the `$this->projectRoot` property.

## Command Class Template 

This template can be used to create new command. Substitute `SprinkleName` in the namespace with your sprinkle name and `CommandName` with your command name in the namespace and class name.

```php
<?php

namespace UserFrosting\SprinkleName\Bakery\CommandName;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use UserFrosting\System\Bakery\BaseCommand;

class CommandName extends BaseCommand
{
    protected function configure()
    {
        // the name of the command (the part after "php bakery")
        $this->setName("my-command")
        
        // the short description shown while running "php bakery list"
        ->setDescription("My command description");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // outputs your command name as a title
        $this->io->title("My Command");
        
        // do something !
        $this->io->writeln("Hello World !");
    }
}
```