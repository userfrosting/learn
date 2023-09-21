---
title: Native Installation
metadata:
    description: Getting UserFrosting up and running in your development environment.
taxonomy:
    category: docs
---

If you already have a local environment and you're familiar with tools like **composer**, this page will guide you in installing UserFrosting on your existing local environment. If you're don't already have a local environment set up, or you don't want to install the required software natively, you may instead want to consider setting up [Homestead](/installation/environment/homestead) or [Docker](/installation/environment/docker) as a pre-configured virtual environment.

## Environment

### Configure your web stack

If your local development environment doesn't already have the [required stack and tools](/installation/requirements), please set these up. You'll need the following:

- Web server software (Apache, Nginx, IIS, etc)
- PHP **8.0* or higher (**8.2** recommended)
- PDO & GD PHP Extension
- Database (MariaDB, MySQL, Postgres, SQLite, or SQL Server)

Make certain that you have [properly configured](/installation/requirements/basic-stack) your web server (for example, Apache needs `mod_rewrite` enabled), PHP, and the file system permissions.

### Other required software

Please **make sure** that you have the following installed **before** attempting to install UserFrosting:

- [Git](/installation/requirements/essential-tools-for-php#Git)
- [Composer 2](/installation/requirements/essential-tools-for-php#Composer)
- [Node.js](/installation/requirements/essential-tools-for-php#Nodejs) version **18.0.0** or higher

## Install PHP dependencies

Next, we will run Composer to fetch and install UserFrosting and install the required dependencies. Before you do this though, you should check which version of PHP will be run **in the command line**.

### Preflight check

It is very common for a single environment to have multiple different versions of PHP installed. If you've never run PHP from the command line before, you may not realize that the version of PHP run by the *webserver* (Apache, nginx, etc) can be different from the one that would get run in the *command line*.

To check the "command line" version, use the command:

```bash
$ php -v
```

You should then see a message like:

```bash
PHP 8.2.9 (cli) (built: Aug 16 2023 21:20:30) (NTS)
Copyright (c) The PHP Group
Zend Engine v4.2.9, Copyright (c) Zend Technologies
    with Xdebug v3.2.0, Copyright (c) 2002-2022, by Derick Rethans
    with Zend OPcache v8.2.9, Copyright (c), by Zend Technologies
```

This is the version of PHP which will be used by Composer. Make sure it meets the minimum required version for UserFrosting!

If it's a lower version than the version that you **know** your webserver uses, then chances are that your terminal is incorrectly resolving the `php` command. This happens because there is an older version of PHP (often preinstalled with your operating system) in one of the paths specified in your path variable (`$PATH` in *nix systems, `PATH` environment variable in Windows).

[notice=tip]To check the value of your `PATH` variable in *nix environments, simply run `echo $PATH`.[/notice]

## Set up the database

Before installing, you'll need to create a database and database user account. Consult your database documentation for more details. If you use _phpmyadmin_ or a similar tool, you can create your database and database user through their interface. Otherwise, you can do it via the command line.

[notice=note]"Database user account" and "UserFrosting user account" are not the same thing. The "database user account" is independent of UserFrosting. See your database technology's documentation for information on creating a database user. Make sure that your database user has all read and write permissions for your database.[/notice]

## Get UserFrosting

### Clone the UserFrosting repository

Use Composer to create an empty project with the latest version of UserFrosting skeleton into a new `UserFrosting` folder:

```bash
$ composer create-project userfrosting/userfrosting UserFrosting "^5.0@dev"
```
<!-- TODO : Change this for release -->

[notice=tip]Note the `UserFrosting` at the end of the command. This means `composer` will create new `UserFrosting` subdirectory inside the current location. You can change `UserFrosting` to whatever you like.[/notice]

This may take some time to complete. If Composer has completed successfully, you should see that a `vendor/` directory has been created. This `vendor/` directory contains all of UserFrosting's PHP dependencies - there should be nearly 30 subdirectories in here! 

Next the **Bakery** will execute it's magic. You'll have to answer some questions, which will guide you into the configuration process. These will help you set up the **database** credentials, create the first **admin user** and install the third-party **assets**. If any error is encountered at this point, in the main project directory, run:

```bash
$ php bakery bake
```

You will first be prompted for your database credentials. This is the information PHP needs to connect to your database. If PHP can't connect to your database using these credentials, make sure you have entered the right information and re-run the `bake` command.

Bakery will also prompt you for SMTP credentials, so that UserFrosting can send emails for activating new accounts and setting and resetting passwords. If you are not ready to set up email at this time, you can choose _No email support_ to skip through SMTP configuration. Please note that in production, you _will_ need to have a working SMTP service. If you do not already have a mail provider, please see our section on [mail providers](/mail/mail-providers) for our recommendations including both free and paid third-party mail services.

If the database connection is successful, the installer will then check that the basic dependencies are met. If so, the installer will run the _migrations_ to populate your database with new tables. During this process, you will be prompted for some information to set up the master account (first user). Finally, the installer will run the `build-assets` command to fetch javascript dependencies and build the [assets bundles](/asset-management/asset-bundles).

[notice=tip]Composer `create-project` command is an umbrella command which run the following commands. You can still run them following manually if you want, or to debug any issue :

1. Run `git clone` (from the UserFrosting repo)
2. Run `composer install`
3. Run `php bakery bake`
[/notice]

### Set directory permissions

UserFrosting needs to be able to write to the following directories:

- `/app/cache`
- `/app/logs`
- `/app/sessions`
- `/app/storage`

Set your system permissions so that the group under which your webserver runs has read and write permissions for these directories. See [File System Permissions](/installation/requirements/basic-stack#FileSystemPermissions) for help with this.

## Visit your website

At this point, you should be able to access the basic pages for your application and login with the newly created master account. You should see a basic page:

![Basic front page of a UserFrosting installation](/images/front-page.png)

## Star the project and follow us on Twitter

It will help us a lot if you could star [the UserFrosting project on GitHub](https://github.com/userfrosting/UserFrosting). Just look for the button in the upper right-hand corner!

[![How to star](/images/how-to-star.png)](https://github.com/userfrosting/UserFrosting)

You should also follow us on Twitter for real-time news and updates:

<a class="twitter-follow-button" href="https://twitter.com/userfrosting" data-size="large">Follow @userfrosting</a>

Congratulations! Now that this is complete, you're ready to start developing your application by [creating your first Sprinkle](/sprinkles).
