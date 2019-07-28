---
title: Custom Commands
metadata:
    description: You may extend the UserFrosting\System\Bakery\BaseCommand class to implement your own CLI commands that can be run through Bakery.
taxonomy:
    category: docs
---

While the Bakery CLI tool comes with great built-in commands, your Sprinkles can also take advantage of the Bakery by adding their own cli commands.

## Writing custom commands

Any class defined in your sprinkle `src/Bakery` directory will be picked up by Bakery and automatically added to the list of available commands. Your custom command should extend the `UserFrosting\System\Bakery\BaseCommand` class and implement the `configure` and `execute` methods.

The `configure` method takes care of defining your command name and description. See [Configuring the Command](http://symfony.com/doc/current/console.html#configuring-the-command) from the Symfony documentation for more details. Arguments and options can also be defined in the `configure` method. Again, the [Symfony documentation](http://symfony.com/doc/current/components/console/console_arguments.html) is the place to look for more information on this.

The `execute` method is the one called when the command is executed. From there you can interact with the user, interact with UserFrosting or do whatever else you want to do. You can also add your own methods inside this class and access them from the execute method.

### Interacting with the user

Interacting with the user can be done with the `SymfonyStyle` instance defined in `$this->io`. For a complete list of available IO methods, check out the [Symfony documentation](http://symfony.com/doc/current/console/style.html#helper-methods).

### Interacting with UserFrosting

The UserFrosting service providers can be accessed by using `$this->ci` which holds an instance of the [The DI Container](/services/the-di-container). The project root directory path is also available in the `$this->projectRoot` property.

## Command Class Template

This template can be used to create new commands. Substitute `SprinkleName` in the namespace with your sprinkle name and `CommandName` with your command name in the class name.

```php
<?php

namespace UserFrosting\Sprinkle\SprinkleName\Bakery;

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
        $this->setName("my-command");

        // the short description shown while running "php bakery list"
        $this->setDescription("My command description");
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

## Adding Custom Commands to the `bake` command

The [`bake` command](/cli/commands#bake) combines many CLI commands into a one and is meant to be used as a "setup" process. Sprinkles can add new commands to `bake` by extending the main command class. For example, to add a new `foo:bar` command :

```php
<?php

namespace UserFrosting\Sprinkle\MySite\Bakery;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UserFrosting\Sprinkle\Account\Bakery\BakeCommand as AccountBakeCommand;

class BakeCommand extends AccountBakeCommand
{
    /**
     * {@inheritdoc}
     */
    protected function executeConfiguration(InputInterface $input, OutputInterface $output)
    {
        parent::executeConfiguration($input, $output);

        $command = $this->getApplication()->find('foo:bar');
        $command->run($input, $output);
    }
}
```

>>>>> Because of PHP class inheritance, you must extend the last class in the chain. Since the `Account` sprinkle extend the class from the `Core` sprinkle, your sprinkle should typically extend the class from the `account` sprinkle, aka `UserFrosting\Sprinkle\Account\Bakery\BakeCommand`.

The main `BakeCommand` class contains many methods you can use to insert your command in the right place in the _baking_ process.

Available methods / inclusion points for your custom commands are, in order of execution :
- `executeSetup` : Execute database setup commands
- `executeDebug` : Execute commands displaying debugging information
- `executeConfiguration` : Execute commands that ask for configuration values
- `executeAsset` : Execute assets related commands
- `executeCleanup` : Execute cache clearing operations

For example, the `foo:bar` command above is added in the `executeConfiguration` method, which means it will be executed after the `executeSetup` and `executeDebug` method / commands. This is useful if your command requires to be run after the database setup for example, but before the assets installation.
