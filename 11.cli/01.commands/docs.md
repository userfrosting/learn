---
title: Built-in Commands
metadata:
    description: An overview of the commands available in Bakery.
taxonomy:
    category: docs
---

UserFrosting's CLI, or [*Command-line Interface*](https://en.wikipedia.org/wiki/Command-line_interface), is called the **Bakery**. It provides a number of helpful commands that can assist you while you build, manage, and install your application. To view a list of all available Bakery commands, you may use the `list` command from your UserFrosting root directory:

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

[notice=tip]Always run any bakery command from the project root directory (`/`). Otherwise, you'll receive a `Could not open input file: bakery` error.[/notice]

## Available commands

### assets:build

The `assets:build` (alias : `webpack` & `build-assets`) command is the general assets building command. It combines `assets:install` and `assets:webpack` into a single command:

```bash
$ php bakery assets:build [options]
```

| Option           | Description                                                     |
|------------------|-----------------------------------------------------------------|
| -p, --production | Compile the assets and asset bundles for production environment |
| -w, --watch      | Watch for changes and recompile automatically                   |

[notice=note]The `production` option is automatically applied when the [environment mode](/configuration/config-files#environment-modes) is set to `production`.[/notice]

See the [Asset Management](/asset-management) chapter for more information about asset bundles and these options.

### assets:install

The `assets:install` command is an alias for the **NPM** scripts used to install all required frontend dependencies locally, based on `packages.lock`. The versions defined in the lock file will be downloaded. Behind the scene, it's an alias for the `npm install` npm command.

### assets:update

The `assets:update` command is an alias for the **NPM** scripts used to update all frontend dependencies, ignoring the versions defined in `packages.lock`. Behind the scenes, it's an alias for the `npm update` npm command.

### assets:webpack

The `assets:webpack` command is an alias for the **Webpack Encore** scripts used to compile frontend dependencies to `/public/assets`. Behind the scenes, it's an alias for the npm commands `npm run dev`, `npm run build` and `npm run watch`. See the table below for more information.

| Option           | Description                                                                               | Alias of        |
|------------------|-------------------------------------------------------------------------------------------|-----------------|
| _no options_     | Compile the assets for development environment                                            | `npm run dev`   |
| -p, --production | Compile the assets for production environment                                             | `npm run build` |
| -w, --watch      | Watch for changes and recompile automatically. Only available in development environment. | `npm run watch` |

[notice=note]The `production` option is automatically applied when the [environment mode](/configuration/config-files#environment-modes) is set to `production`.[/notice]

[notice=note]If both `watch` and `production` options are used, _watch_ will be ignored and assets will be build for production.[/notice]

See the [Asset Management](/asset-management) chapter for more information about asset bundles and these options.

### bake

Bake is the general installation command. It combines `setup:db`, `setup:mail`, `debug`, `migrate`, `create:admin-user`, `assets:build` and `clear-cache` into a single command:

```bash
$ php bakery bake
```

[notice=tip]This command should be executed every time you run `composer update`, change assets, create a new migration, create a new sprinkle or install a [community sprinkle](/sprinkles/community).[/notice]

[notice=tip]It is possible and easy to add your own command to the `bake` command. See [Extending Aggregator Commands](/cli/extending-commands) for more information.[/notice]


### clear-cache

The `clear-cache` command takes care of deleting all cached data. See [Chapter 17](/advanced/caching) for more information.

```bash
$ php bakery clear-cache
```

[notice=note]When using the `file` cache store, you might need to run this command as administrator or using `sudo` to avoid file permission issues.[/notice]


### create:admin-user

The `create:admin-user` command is used to create a new root user. This command will self-abort if the root user already exists.

```bash
$ php bakery create:admin-user [options]
```

Options can also be used to create the admin user without interaction (See the table below for the list of available options). For example :

```bash
$ php bakery create:admin-user --username="admin" --email="admin@userfrosting.test" --password="adminadmin12" --firstName="Admin" --lastName="istrator"
```

| Option                  | Description               |
| ----------------------- | ------------------------- |
| --username[=USERNAME]   | The admin user username   |
| --email[=EMAIL]         | The admin user email      |
| --password[=PASSWORD]   | The admin user password   |
| --firstName[=FIRSTNAME] | The admin user first name |
| --lastName[=LASTNAME]   | The admin user last name  |

### create:user

This command is the same as `create:admin-user`, but will create a non-root user.

```bash
$ php bakery create:user [options]
```

### debug

The `debug` command will run a series of tests to make sure everything is ready to run UserFrosting on your system. If you have trouble accessing your UserFrosting installation, you should run this command first to make sure basic requirements are met.

The information displayed by this command can also be useful to other people when [asking for help](/troubleshooting/getting-help) or submitting new issues on GitHub.

```bash
$ php bakery debug
```

The verbose option can be used with this command to display even more information.

```bash
$ php bakery debug -v
```

The debug command is in fact an aggregator of sub-commands, similar to `bake`. It include the following commands by default:

| Command       | Description                                                                    | Require verbose |
|---------------|--------------------------------------------------------------------------------|:---------------:|
| debug:config  | Test the UserFrosting database config                                          |                 |
| debug:db      | Test the UserFrosting database connection                                      |                 |
| debug:events  | List all currently registered events listener for each events.                 | ✓               |
| debug:locator | List all locations and streams, with their respective path, to help debugging. | ✓               |
| debug:mail    | Display Mail Configuration                                                     | ✓               |
| debug:twig    | List all twig namespaces to help debugging                                     | ✓               |
| debug:version | Test the UserFrosting version dependencies                                     |                 |
| sprinkle:list | List all available sprinkles and their parameters                              |                 |

Some results will be only displayed when the verbose mode is active.

[notice=tip]It is also possible (and easy) to add your own command to the `debug` command. See [Extending Aggregator Commands](/cli/extending-commands) for more information.[/notice]

### locale:compare

This command compare two locale dictionaries. A list of all locale keys found in the left locale but not found in the right locale will be generated, as well as a list of all keys with empty values and/or duplicate values. This can be helpful to list all values in a specific languages that are present, but might need translation.

```bash
$ php bakery locale:compare [options]
```

This command is interactive, which means it will ask for which locales to compare. Options can also be used to automatically compare the two locales without user interaction (See the table below for the list of available options).
à
This command will display :
 - Comparison between _Right_ and _Left_ locales : Returns a list of all differences in both locales using [`array_diff_assoc`](https://www.php.net/manual/en/function.array-diff-assoc.php). This can be used to compare the two locales.
 - Missing keys from _Right_ found in _Left_ : This can be used to see which keys are missing in the _Right_ locale, but that can be found in the _Left_ locale, so they can be added.
 - Same values found in both _Left_ and _Right_ locale : This can be used to find strings in the _right_ locale that is the same in the _left_ locale. When two locales have the same string value, it may means the string is not translated in the _right_ locale.
 - Empty values for _Right_ locale : Lists keys with empty string for the _right_ locale. These strings might need to be filled in.

| Option            | Description                                           |
| ----------------- | ----------------------------------------------------- |
| -l, --left=LEFT   | The base locale to compare against.                   |
| -r, --right=RIGHT | The second locale to compare.                         |
| --length=LENGTH   | Set the length for preview column text. [default: 50] |

For example :

```bash
$ php bakery locale:compare -l en_US -r fr_FR
```

### locale:dictionary

This command shows the compiled dictionary for the selected locale.

```bash
$ php bakery locale:dictionary [options]
```
This command is interactive, which mean it will ask to select the locale to show the dictionary from. Options can also be used to automatically select the locale without user interaction (See the table below for the list of available options).

| Option              | Description                                           |
| ------------------- | ----------------------------------------------------- |
| -l, --locale=LOCALE | The selected locale.                                  |
| --length=LENGTH     | Set the length for preview column text. [default: 50] |

For example :

```bash
$ php bakery locale:dictionary -l fr_FR
```

### locale:info

This command list all available locales as well as the default locale.

```bash
$ php bakery locale:info
```

Example output :

```txt
+------------+----------------------+----------------------+---------+---------+
| Identifier | Name                 | Regional             | Parents | Default |
+------------+----------------------+----------------------+---------+---------+
| en_US      | English              | English              |         | Yes     |
| es_ES      | Spanish              | Español              | en_US   |         |
| de_DE      | German               | Deutsch              | en_US   |         |
| fr_FR      | French               | Français             | en_US   |         |
+------------+----------------------+----------------------+---------+---------+
```

### migrate

[notice=warning]Database migrations have the potential to destroy data. **Always** back up production databases, and databases with important data, before running migrations on them.[/notice]

The `migrate` command runs all the pending [database migrations](/database/migrations). Migrations consist of special PHP classes used to manipulate the database structure and data, creating new tables or modifying existing ones. UserFrosting comes with a handful of migrations to create the [default tables](/database/default-tables). The built-in migrations also handle the changes in the database between versions. See the [Migrations](/database/migrations) section for more information about migrations.

```bash
$ php bakery migrate [options]
```

| Option                  | Description                                                    |
| ----------------------- | -------------------------------------------------------------- |
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
| ------------------------- | --------------------------------------------- |
| -p, --pretend             | Run migrations in "dry run" mode              |
| -f, --force               | Force the operation to run when in production |
| -d, --database=DATABASE   | The database connection to use                |
| -m, --migration=MIGRATION | The specific migration class to rollback      |
| -s, --steps=STEPS         | Number of steps to rollback [default: 1]      |


### migrate:reset

The `migrate:reset` command is the same as the _rollback_ command, but it will revert **every** migration. Without options, this is the same as wiping the database to a clean state. 

[notice=warning]**Use this command with caution!**[/notice]

```bash
$ php bakery migrate:reset [options]
```

| Option                  | Description                                   |
| ----------------------- | --------------------------------------------- |
| -p, --pretend           | Run migrations in "dry run" mode              |
| -f, --force             | Force the operation to run when in production |
| -d, --database=DATABASE | The database connection to use                |


### migrate:reset:hard

The `migrate:reset:hard` command is the same as the `migrate:reset` command, but it will bypass all migrations and drop all tables from the database. This can be used as a last resort when a specific migration won't allow you to reset the whole stack.

[notice=warning]**Use this command with _extreme_ caution!**[/notice]

```bash
$ php bakery migrate:reset:hard [options]
```


### migrate:refresh

The `migrate:refresh` command will rollback the last migration operation and execute it again. This is the same as executing `migrate:rollback` and then `migrate`.

```bash
$ php bakery migrate:refresh [options]
```

| Option                  | Description                                   |
| ----------------------- | --------------------------------------------- |
| -f, --force             | Force the operation to run when in production |
| -d, --database=DATABASE | The database connection to use                |
| -s, --steps=STEPS       | Number of steps to rollback [default: 1]      |


### migrate:status

The `migrate:status` command will show what migration have been run and which one can be run. It will also display if a previously run migration is available to be rolled back.

```bash
$ php bakery migrate:status [options]
```

| Option                  | Description                    |
| ----------------------- | ------------------------------ |
| -d, --database=DATABASE | The database connection to use |


### route:list

Display the list of all registered [routes](/routes-and-controllers/front-controller).

```bash
$ php bakery route:list [options]
```

| Option          | Description                                                        |
| --------------- | ------------------------------------------------------------------ |
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

The `seed` command can be used to run any registered seed classes. See [Chapter 12](/database/seeding) for more info on database seeds.

```bash
$ php bakery seed [options] [--] <class> (<class>)...
```

If no `<class>` is specified, an interactive list will be displayed, prompting you to select which registered seed(s) to run.

Multiple seed classes can be run at once by separating them with a space. For example, to run `Class1` and `Class2` :

```bash
$ php bakery seed Class1 Class2
```

| Option      | Description                                   |
| ----------- | --------------------------------------------- |
| -f, --force | Force the operation to run when in production |


### seed:list

The `seed:list` command will list all database seeds available. See [Chapter 12](/database/seeding) for more info on database seeds.

```bash
$ php bakery seed:list
```

Example result:

```txt
Seeds List
==========
 * UserFrosting\Sprinkle\Account\Database\Seeds\DefaultGroups
 * UserFrosting\Sprinkle\Account\Database\Seeds\DefaultPermissions
 * UserFrosting\Sprinkle\Account\Database\Seeds\DefaultRoles
```


### setup:db

The `setup:db` command can be used to setup the database configuration interactively. This configuration will be saved in the `app/.env` file. This can also be done manually by editing the `app/.env` file or using global server environment variables. See [Environment Variables](/configuration/environment-vars) for more information about these variables.

```bash
$ php bakery setup:db [options]
```

Options can be used to define each variable individually in a non-interactive way. (See the table below for the list of available options.) For example:

```bash
php bakery setup:db --db_driver=mysql --db_name=userfrosting --db_port=3306 --db_host=localhost --db_user=userfrosting --db_password=secret
```


| Option                      | Description                                             |
| --------------------------- | ------------------------------------------------------- |
| --force                     | Force setup if db is already configured                 |
| --db_driver[=DB_DRIVER]     | The database driver ["mysql","pgsql","sqlsrv","sqlite"] |
| --db_name[=DB_NAME]         | The database name                                       |
| --db_host[=DB_HOST]         | The database hostname                                   |
| --db_port[=DB_PORT]         | The database port                                       |
| --db_user[=DB_USER]         | The database user                                       |
| --db_password[=DB_PASSWORD] | The database password                                   |


### setup:mail

The `setup:mail` command can be used to setup the outgoing email configuration. Different setup methods can be selected to guide you into configuring outgoing email support. This configuration will be saved in the `app/.env` file.

As with the database setup, this can also be done manually by editing the `app/.env` file or using global server environment variables. See [Environment Variables](/configuration/environment-vars) for more information about these variables.

```bash
$ php bakery setup:mail [options]
```

Options can also be used to defined each variable individually in a non-interactive way. When using one or more options, the "SMTP Server" method will automatically be selected.

| Option                          | Description                                          |
| ------------------------------- | ---------------------------------------------------- |
| --force                         | Force setup if SMTP appears to be already configured |
| --smtp_host[=SMTP_HOST]         | The SMTP server hostname                             |
| --smtp_user[=SMTP_USER]         | The SMTP server user                                 |
| --smtp_password[=SMTP_PASSWORD] | The SMTP server password                             |
| --smtp_port[=SMTP_PORT]         | The SMTP server port                                 |
| --smtp_auth[=SMTP_PASSWORD]     | The SMTP server authentication                       |
| --smtp_secure[=SMTP_SECURE]     | The SMTP server security type                        |


### setup:env

The `setup:env` command can be used to select the desired [Environment Mode](/configuration/config-files#environment-modes). The default choices are `default`, `production` and `debug`. A custom value can also be defined.

As with the database and outgoing email setup, this can also be done manually by editing the `app/.env` file or using global server environment variables. See [Environment Variables](/configuration/environment-vars) for more information about these variables.

```bash
$ php bakery setup:env [options]
```

| Option        | Description            |
| ------------- | ---------------------- |
| --mode[=MODE] | The environment to use |

Example usage :
```bash
php bakery setup:env --mode=production
```


### setup

The `setup` command combines the `setup:db`, `setup:mail`, and `setup:env` commands using interactive mode only. This command doesn't accept any options.

```bash
$ php bakery setup
```

[notice=tip]It is also possible (and easy) to add your own command to the `setup` command. See [Extending Aggregator Commands](/cli/extending-commands) for more information.[/notice]

### sprinkle:list

Display the list of all loaded sprinkles. It will also display the base namespace that classes from the sprinkle are expected to have, as well as the sprinkle's base path.

```bash
$ php bakery sprinkle:list
```

Example result:

```txt 
 Loaded Sprinkles
================

 ------------------ --------------------------------------- ---------------------------------------------------------------------- 
  Sprinkle           Namespace                               Path                                                                
 ------------------ --------------------------------------- ---------------------------------------------------------------------- 
  Core Sprinkle      UserFrosting\Sprinkle\Core\Core         /home/UserFrosting/vendor/userfrosting/sprinkle-core/app/src/../     
  Account Sprinkle   UserFrosting\Sprinkle\Account\Account   /home/UserFrosting/vendor/userfrosting/sprinkle-account/app/src/../  
  AdminLTE Theme     UserFrosting\Theme\AdminLTE\AdminLTE    /home/UserFrosting/vendor/userfrosting/theme-adminlte/app/src/../    
  Admin Sprinkle     UserFrosting\Sprinkle\Admin\Admin       /home/UserFrosting/vendor/userfrosting/sprinkle-admin/app/src/../    
  My Application     UserFrosting\App\MyApp                  /home/UserFrosting/app/src/../                                       
 ------------------ --------------------------------------- ---------------------------------------------------------------------- 
```


### serve

The `serve` command is used to execute [PHP's Built-in web server](https://www.php.net/manual/en/features.commandline.webserver.php). This is a simple way to test your application without having to configure a full web server.

```bash
$ php bakery serve [options]
```

Hit `ctrl+c` to quit.

| Option                            | Description                                             |
| --------------------------------- | ------------------------------------------------------- |
| -p, --port=PORT                   | The port to serve the application on [default: "8080"]. |


### test

The `test` command is used to execute [PHPUnit](https://phpunit.de/) tests.

Tests from a specific sprinkle can optionally be run using the 'testscope' argument (eg. `php bakery test SprinkleName`). This argument can also run a specific test class (eg. `php bakery test 'UserFrosting\Sprinkle\SprinkleName\Tests\TestClass'`) or a specific test method (eg. `php bakery test 'UserFrosting\Sprinkle\SprinkleName\Tests\TestClass::method'`).

See the [Automated Testing](/testing) section for more information.

```bash
$ php bakery test [options] [--] [<testscope>]
```

| Option                            | Description                                                                                          |
| --------------------------------- | ---------------------------------------------------------------------------------------------------- |
| -c, --coverage                    | Enable code coverage report.                                                                         |
| --coverage-format=COVERAGE-FORMAT | Select test coverage format. Choose from html, clover, crap4j, php, text, xml, etc. Defaults to HTML. |
| --coverage-path=COVERAGE-PATH     | Code coverage report saving location. Default to `_meta/coverage`.                                   |

[notice=warning]UserFrosting's built-in integration tests use a temporary in-memory SQLite database.  For testing to run successfully, you must have the `php-sqlite3` package installed and enabled.

Alternatively, you can create a separate testing database and override the `test_integration` database settings in `testing.php` [environment mode](/configuration/config-files).[/notice]


### test:mail

The `test:mail` command lets you test the email sending capability of your UserFrosting setup by sending a test email. By default, it will send the test email to the admin contact defined in the configuration file, but this can be changed using the provided `--to` options.

```bash
$ php bakery test:mail [options]
```

| Option  | Description                                                        |
| ------- | ------------------------------------------------------------------ |
| --to=TO | Email address to send test email to. Use admin contact if omitted. |
