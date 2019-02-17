---
title: Built-in Commands
metadata:
    description: An overview of the commands available in Bakery.
taxonomy:
    category: docs
---

UserFrosting's CLI, or [*Command-line Interface*](https://en.wikipedia.org/wiki/Command-line_interface), is called the **Bakery**. It provides a number of helpful commands that can assist you while you build, manage and install your application. To view a list of all available Bakery commands, you may use the `list` command from your UserFrosting root directory:

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

### bake

Bake is the general installation command. It combines `setup:db`, `setup:smtp`, `debug`, `migrate`, `create-admin` and `build-assets` into a single command:

```bash
$ php bakery bake
```

>>>>>> This command should be executed every time you run `composer update`, change assets, create a new sprinkle or install a [community sprinkle](/sprinkles/community).


### build-assets

The `build-assets` command is an alias for the node.js scripts used for asset management. The `/build` directory contains the scripts and configuration files required to download Javascript, CSS, and other assets used by UserFrosting. This command will install all required build dependencies locally (e.g. Gulp and Yarn), and then automatically download frontend dependencies to `/app/assets`.

See the [Asset Management](/asset-management) chapter for more information about asset bundles and the `compile` option.

```bash
$ php bakery build-assets [options]
```

| Option        | Description                                                      |
|---------------|------------------------------------------------------------------|
| -c, --compile | Compile the assets and asset bundles for production environment  |
| -f, --force   | Force fresh install by deleting cached data and installed assets |

>>>>> The compile option is automatically applied when the [environment mode](/configuration/config-files#environment-modes) is set to `production`.


### clear-cache

The `clear-cache` command takes care of deleting all the cached data. See [Chapter 16](/advanced/caching) for more information.

```bash
$ php bakery clear-cache
```

>>>>> You might need to run this command as administrator or using `sudo` to avoid file permission issues when using the `file` cache store.


### create-admin

The `create-admin` command is used to create the root user. This command will self-abort if the root user already exists.

```bash
$ php bakery create-admin [options]
```

Options can also be used to create the admin user without interaction (See the table below for the list of available options). For example :

```bash
$ php bakery create-admin --username="admin" --email="admin@userfrosting.test" --password="adminadmin12" --firstName="Admin" --lastName="istrator"
```

| Option                   | Description                |  
|--------------------------|----------------------------|
| --username[=USERNAME]    | The admin user username    |
| --email[=EMAIL]          | The admin user email       |
| --password[=PASSWORD]    | The admin user password    |
| --firstName[=FIRSTNAME]  | The admin user first name  |
| --lastName[=LASTNAME]    | The admin user last name   |


### debug

The `debug` command will run a series of tests to make sure everything is ready to run UserFrosting on your system. If you have trouble accessing your UserFrosting installation, you should run this command first to make sure basic requirements are met.

The information displayed by this command can also be useful to other people when [asking for help](/troubleshooting/getting-help) and submitting new issues on GitHub.

```bash
$ php bakery debug
```


### migrate

>>>> Database migrations have the potential to destroy data.  **Always** back up production databases, and databases with important data, before running migrations on them.

The `migrate` command runs all the pending [database migrations](/database/migrations). Migrations consist of special PHP classes used to manipulate the database structure and data, creating new tables or modifying existing ones. UserFrosting comes with a handful of migrations to create the [default tables](/database/default-tables). The built-in migrations also handle the changes in the database between versions. See the [Migrations](/database/migrations) section for more information about migrations.

```bash
$ php bakery migrate [options]
```

| Option                  | Description                                                    |
|-------------------------|----------------------------------------------------------------|
| -p, --pretend           | Run migrations in "dry run" mode                               |
| -f, --force             | Force the operation to run when in production                  |
| -d, --database=DATABASE | The database connection to use                                 |
| -s, --step              | Migrations will be run so they can be rolled back individually |

The `pretend` option can be used to test migrations. This will display the underlying SQL queries:

```bash
$ php bakery migrate --pretend
```

Result :

```txt
UserFrosting\Sprinkle\Core\Database\Migrations\v400\SessionsTable
> select * from information_schema.tables where table_schema = ? and table_name = ?
> create table `sessions` (`id` varchar(255) not null, `user_id` int null, `ip_address` varchar(45) null, `user_agent` text null, `payload` text not null, `last_activity` int not null) default character set utf8 collate utf8_unicode_ci
> alter table `sessions` add unique `sessions_id_unique`(`id`)
```

### migrate:rollback

The `migrate:rollback` command allows you to cancel, or rollback, the last migration operation. For example, if something went wrong with the last migration operation or if you made a mistake in your migration definition, you can use that command to undo it.

Note that migrations are run in batches. For example, when running the `migrate` command, if 4 classes (or migration definitions) are executed, all 4 definitions will be reverted when rolling back the last migration operation, unless you used the `step` option with the `migrate` command.

Options can also be used to rollback more than one migration at a time or to rollback a specific migration.

```bash
$ php bakery migrate:rollback [options]
```

| Option                    | Description                                   |
|---------------------------|-----------------------------------------------|
| -p, --pretend             | Run migrations in "dry run" mode              |
| -f, --force               | Force the operation to run when in production |
| -d, --database=DATABASE   | The database connection to use                |
| -m, --migration=MIGRATION | The specific migration class to rollback      |
| -s, --steps=STEPS         | Number of steps to rollback [default: 1]      |


### migrate:reset

The `migrate:reset` command is the same as the _rollback_ command, but it will revert **every** migration. Without options, this is the same as wiping the database to a clean state. **_Use this command with caution!_**.

```bash
$ php bakery migrate:reset [options]
```

| Option                  | Description                                                            |
|-------------------------|------------------------------------------------------------------------|
| -p, --pretend           | Run migrations in "dry run" mode                                       |
| -f, --force             | Force the operation to run when in production                          |
| --hard                  | Hard reset the whole database to an empty state by dropping all tables |
| -d, --database=DATABASE | The database connection to use                                         |

>>>> The `--hard` option will bypass all migrations and drop all tables from the database. This can be used as a last resort when a specific migration won't allow you to reset the whole stack. Use **extreme** caution with this option !

### migrate:refresh

The `migrate:refresh` command will rollback the last migration operation and execute it again. This is the same as executing `migrate:rollback` and then `migrate`.

```bash
$ php bakery migrate:refresh [options]
```

| Option                  | Description                                   |
|-------------------------|-----------------------------------------------|
| -f, --force             | Force the operation to run when in production |
| -d, --database=DATABASE | The database connection to use                |
| -s, --steps=STEPS       | Number of steps to rollback [default: 1]      |


### migrate:status

The `migrate:status` command will show what migration have been run and which one can be run. It will also display if a ran migration is available, in other words if this migration class was found so it can be rolledback.

```bash
$ php bakery migrate:status [options]
```

| Option                  | Description                                   |
|-------------------------|-----------------------------------------------|
| -d, --database=DATABASE | The database connection to use                |


### route:list

Display the list of all registered [routes](/routes-and-controllers/front-controller).

```bash
$ php bakery route:list [options]
```

| Option          | Description                                                        |
|-----------------|--------------------------------------------------------------------|
| --method=METHOD | Filter the routes by method                                        |
| --name=NAME     | Filter the routes by name                                          |
| --uri=URI       | Filter the routes by uri                                           |
| --reverse, -r   | Reverse the ordering of the routes                                 |
| --sort=SORT     | The column (method, uri, name, action) to sort by [default: "uri"] |

Example result:

```bash
$ php bakery route:list --uri=/account/ --method=POST --sort=action
```

```txt
Registered Routes
=================

 -------- ------------------------------ ---------- -------------------------------------------------------------------------------
  Method   URI                            Name       Action                                                                         
 -------- ------------------------------ ---------- -------------------------------------------------------------------------------
  POST     /account/forgot-password                  UserFrosting\Sprinkle\Account\Controller\AccountController:forgotPassword      
  POST     /account/login                            UserFrosting\Sprinkle\Account\Controller\AccountController:login               
  POST     /account/settings/profile                 UserFrosting\Sprinkle\Account\Controller\AccountController:profile             
  POST     /account/register                         UserFrosting\Sprinkle\Account\Controller\AccountController:register            
  POST     /account/resend-verification              UserFrosting\Sprinkle\Account\Controller\AccountController:resendVerification  
  POST     /account/set-password                     UserFrosting\Sprinkle\Account\Controller\AccountController:setPassword         
  POST     /account/settings              settings   UserFrosting\Sprinkle\Account\Controller\AccountController:settings            
 -------- ------------------------------ ---------- -------------------------------------------------------------------------------
```


### seed

The `seed` command will run the `<class>` seed classes. See [Chapter 12](/database/seeding) for more info on database seeds.

```bash
$ php bakery seed [options] [--] <class> (<class>)...
```

Multiple seeds classes can be run at one by separating multiple seed classes with a space. For example, to run `Class1` and `Class2` :

```bash
$ php bakery seed Class1 Class2
```

| Option      | Description                                   |
|-------------|-----------------------------------------------|
| -f, --force | Force the operation to run when in production |


### seed:list

The `seed:list` command will list all database seeds available. See [Chapter 12](/database/seeding) for more info on database seeds.

```bash
$ php bakery seed:list
```

Example result:

```txt
Database Seeds List
===================

 ---------- -------------------------------------------------------- ----------
  Name       Namespace                                                Sprinkle  
 ---------- -------------------------------------------------------- ----------
  TestSeed   \UserFrosting\Sprinkle\Core\Database\Seeds\TestSeed      Core      
  TestSeed   \UserFrosting\Sprinkle\Account\Database\Seeds\TestSeed   Account   
 ---------- -------------------------------------------------------- ----------
```


### setup:db

The `setup:db` command can be used to setup the database configuration. This configuration will be saved in the `app/.env` file. This can also be done manually by editing the `app/.env` file or using global server environment variables. See [Environment Variables](/configuration/environment-vars) for more information about these variables.

```bash
$ php bakery setup:db [options]
```

Options can be used to defined each info individually in a non-interactive way (See the table below for the list of available options). For example :

```bash
php bakery setup:db --db_driver=mysql --db_name=userfrosting --db_port=3306 --db_host=localhost --db_user=userfrosting --db_password=secret
```


| Option                      | Description                                             |
|-----------------------------|---------------------------------------------------------|
| --force                     | Force setup if db is already configured                 |
| --db_driver[=DB_DRIVER]     | The database driver ["mysql","pgsql","sqlsrv","sqlite"] |
| --db_name[=DB_NAME]         | The database name                                       |
| --db_host[=DB_HOST]         | The database hostname                                   |
| --db_port[=DB_PORT]         | The database port                                       |
| --db_user[=DB_USER]         | The database user                                       |
| --db_password[=DB_PASSWORD] | The database password                                   |


### setup:smtp

The `setup:smtp` command can be used to setup the outgoing email configuration. Different setup method can be selected to guide you into configuring outgoing email support. This configuration will be saved in the `app/.env` file.

As with the database setup, this can also be done manually by editing the `app/.env` file or using global server environment variables. See [Environment Variables](/configuration/environment-vars) for more information about these variables.

```bash
$ php bakery setup:smtp [options]
```

Options can also be used to defined each info individually in a non-interactive way. When using one or more option, the "SMTP Server" method will automatically be selected.

| Option                          | Description                                          |
|---------------------------------|------------------------------------------------------|
| --force                         | Force setup if SMTP appears to be already configured |
| --smtp_host[=SMTP_HOST]         | The SMTP server hostname                             |
| --smtp_user[=SMTP_USER]         | The SMTP server user                                 |
| --smtp_password[=SMTP_PASSWORD] | The SMTP server password                             |


### setup:env

The `setup:env` command can be used to select the desired [Environment Mode](/configuration/config-files#environment-modes). The default choices are `default`, `production` and `debug`. A custom value can also be defined.

As with the database and outgoing email setup, this can also be done manually by editing the `app/.env` file or using global server environment variables. See [Environment Variables](/configuration/environment-vars) for more information about these variables.

```bash
$ php bakery setup:env [options]
```

| Option        | Description            |
|---------------|------------------------|
| --mode[=MODE] | The environment to use |

Example usage :
```bash
php bakery setup:env --mode=production
```


### setup

The `setup` command combines the `setup:db`, `setup:smtp` and `setup:env` commands. This command can't accept any of the option of it's child command.

```bash
$ php bakery setup
```

### sprinkle:list

Display the list of all loaded sprinkles. It will also display the base namespace classes from the sprinkle is expected to have, as weel as the expected path.

```bash
$ php bakery sprinkle:list
```

Example result:

```txt
Loaded Sprinkles
================

 ---------- -------------------------------- ------------------------------------------
  Sprinkle   Calculated Namespace             Calculated Path                                
 ---------- -------------------------------- ------------------------------------------
  core       UserFrosting\Sprinkle\Core       /home/UserFrosting/app/sprinkles/core      
  account    UserFrosting\Sprinkle\Account    /home/UserFrosting/app/sprinkles/account   
  admin      UserFrosting\Sprinkle\Admin      /home/UserFrosting/app/sprinkles/admin     
 ---------- -------------------------------- ------------------------------------------
```


### test

The `test` command is used to execute [PHPUnit](https://phpunit.de/) tests.

Tests from a specific sprinkle can optionally be run using the 'testscope' argument (eg. `php bakery test SprinkleName`). A specific test class can also be run using the testscope argument (eg. `php bakery test 'UserFrosting\Sprinkle\SprinkleName\Tests\TestClass'`), as a specific test method (eg. `php bakery test 'UserFrosting\Sprinkle\SprinkleName\Tests\TestClass::method'`).

See the [Automated Testing](/testing) section for more information.

```bash
$ php bakery test [options] [--] [<testscope>]
```

| Option                            | Description                                                                                          |
|-----------------------------------|------------------------------------------------------------------------------------------------------|
| -c, --coverage                    | Enable code coverage report.                                                                         |
| --coverage-format=COVERAGE-FORMAT | Select test coverage format. Choose from html, clover, crap4j, php, text, xml, etc. Default to HTML. |
| --coverage-path=COVERAGE-PATH     | Code coverage report saving location. Default to `_meta/coverage`.                                   |

>>>> UserFrosting's built-in integration tests use a temporary in-memory SQLite database.  For testing to run successfully, you must have the `php-sqlite3` package installed and enabled.  Alternatively, you can create a separate testing database and override the `test_integration` database settings in the `testing.php` [environment mode](/configuration/config-files).


### test:mail

The `test:mail` command lets you test the email sending capability of your UserFrosting setup by sending a test email. By default, it will send the test email to the admin contact defined the configuration file, but this can be changed using the provided `--to` options.

```bash
$ php bakery test:mail [options]
```

| Option  | Description                                                        |
|---------|--------------------------------------------------------------------|
| --to=TO | Email address to send test email to. Use admin contact if omitted. |
