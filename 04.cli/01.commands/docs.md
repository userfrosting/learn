---
title: Overview
taxonomy:
    category: docs
---

UserFrosting's CLI, or [*Command-line Interface*](https://en.wikipedia.org/wiki/Command-line_interface), is called **The Bakery**. It provides a number of helpful commands that can assist you while you build, manage and install your application. To view a list of all available Bakery commands, you may use the `list` command from your UserFrosting root directory :

```bash
$ php bakery list
``` 

Every command also includes a "help" screen which displays and describes the command's available arguments and options. To view a help screen, simply precede the name of the command with help:

```bash
$ php bakery help [command]
``` 

General help can also be displayed by running:

```bash
$ php bakery help
``` 

## Available commands

### debug

The `debug` command will run a series of tests to make sure everything is ready to run UserFrosting on your system. If you have trouble accessing your UserFrosting installation, you should run this command first to make sure basics requirements are met. 

The information displayed by this command can also be useful to other people when [asking for help](/installation/getting-help) and submitting new issues on Github. 

```bash
$ php bakery debug
``` 

### setup

The `setup` command can be used to setup the database and email configuration. This can also be done manually by editing the `app/.env` file or using global server environment variables. See [The .env file](/installation/env-file) chapter for more information abput that file.

```bash
$ php bakery setup 
``` 

| Option      | Description                                    |  
|-------------|------------------------------------------------|
| -f, --force | If `.env` file exist, force setup to run again |

### build-assets

The `build-assets` command is an alias for the node.js and npm scripts used for assets management. The `/build` directory contains thoses scripts and configuration files required to download Javascript, CSS, and other assets used by UserFrosting. This command will install Gulp, Bower, and several other required npm packages locally. With npm set up with all of its required packages, it can be use it to automatically download and install the assets in the correct directories.

See the [Pages and Assets](/building-pages) chapter for more info about assets bundles.

```bash
$ php bakery build-assets
``` 
  
| Option        | Description                                                     |
|---------------|-----------------------------------------------------------------|
| -c, --compile | Compile the assets and asset bundles for production environment |

### migrate

The `mgirate` command runs all the pending [database migrations](/database/migrations). Migrations consist of special PHP clases used to manipulate the database structure and data, creating new table or modifting exsiting one. UserFrosting comes with an handfull of migrations to create all of the bases tables and even creating the master user. Thoses build in migrations also handle the changes in the database between versions. See the [Migrations](/database/migrations) section for more information about migrations.

```bash
$ php bakery migrate
``` 

### migrate:rollback

The `migrate:rollback` command allows you to cancel, or rollback, the last migration operation. For example, if something went wrong with the last migration operation or if you made a mistake in your migration definition, you can use that command to undo it. 

Note that migrations are run in batch. For example, when running the `migrate` command, if 4 classes, or migration definition are executed, when rolling back the last migration operation, all thoses 4 definition will be reverted back. 

Options can also be used to rollback more than one migration at a time or to rollback migrations from a specific sprinkle. 

```bash
$ php bakery migrate:rollback
``` 

| Option              | Description                              |
|---------------------|------------------------------------------|
| -s, --steps=STEPS   | Number of steps to rollback [default: 1] |
| --sprinkle=SPRINKLE | The sprinkle to rollback [default: ""]   |

### migrate:reset

The `migrate:reset` command is the same as the _rollback_ command, but it will revert **every** migrations. Without options, this is the same as wipping the database to a clean state. **_Use this command with caution!_**.

The `--sprinkle=` option can also be used to reset only migrations from a specific sprinkle. 


```bash
$ php bakery migrate:reset
``` 

| Option              | Description                              |
|---------------------|------------------------------------------|
| --sprinkle=SPRINKLE | The sprinkle to rollback [default: ""]   |

### migrate:refresh

The `migrate:refresh` command rollback the last mgiration operation and execure it again. This is the same as executing `migrate:rollback` and then `migrate`.

```bash
$ php bakery migrate:refresh
``` 

| Option              | Description                              |
|---------------------|------------------------------------------|
| -s, --steps=STEPS   | Number of steps to rollback [default: 1] |
| --sprinkle=SPRINKLE | The sprinkle to rollback [default: ""]   |

### test

The `test` command is used to execute PhpUnit tests. See the [Unit Testing](/other-services/unit-tests) chapter for more informations.

```bash
$ php bakery test
``` 

### bake
Bake is the general installation command. It combines `setup`, `debug`, `migrate` and `build-assets` into a single command : 

```bash
$ php bakery bake
``` 