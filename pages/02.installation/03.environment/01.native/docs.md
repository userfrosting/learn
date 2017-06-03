---
title: Native Installation
metadata:
    description: Getting UserFrosting up and running in your development environment.
taxonomy:
    category: docs
---

If you already have a local environment and you're already familiar with tools like **composer**, this page will guide you in installing UserFrosting on your existing local environment. If this is you're not already familiar with setting up a local environment, the [Homestead Environment](/installation/environment/homestead) guide is for you.

## Environment

### Web stack

If you don't already have a local development environment set up, please [do so now](/basics/requirements/develop-locally-serve-globally#setting-up-a-local-development-environment).

Make certain that your development environment meets the [minimum requirements](/basics/requirements/basic-stack). In particular, make sure that you have PHP **5.6** or higher installed, as well as a webserver that supports URL rewriting (for example, Apache with `mod_rewrite` enabled).

### Other required software

Please make sure that you have the following installed before attempting to install UserFrosting:

- [Git](/basics/requirements/essential-tools-for-php#git)
- [Composer](/basics/requirements/essential-tools-for-php#composer)
- [Node.js](/basics/requirements/essential-tools-for-php#nodejs)

## Get UserFrosting

### Clone the UserFrosting repository

The best way to initially set up UserFrosting in your local environment is by using git to **clone** the main UserFrosting repository. 

```bash
$ git clone https://github.com/userfrosting/UserFrosting.git userfrosting
```

>>>>>> Note the `userfrosting` at the end of the command. This means `git` will create new `userfrosting` subdirectory inside the current lcoation. You can change `userfrosting` to whatever you like. 

### Set directory permissions

Make sure that `/app/cache`, `/app/logs`, and `/app/sessions` are writable by your webserver. See [File System Permissions](/basics/requirements/basic-stack#file-system-permissions) for help with this.

## Install PHP dependencies

Next, we will run Composer to fetch and install the PHP packages used by UserFrosting. Before you do this though, you should check which version of PHP will be run **in the command line**.

### Preflight check

It is very common for a single environment to have multiple different versions of PHP installed. If you've never run PHP from the command line before, you may not realize that the version of PHP run by the *webserver* (Apache, nginx, etc) can be different from the one that would get run in the *command line*.

To check the "command line" version, use the command:

```bash
$ php -v
```

You should then see a message like:

```bash
PHP 5.6.15 (cli) (built: Dec  4 2015 12:52:38)
Copyright (c) 1997-2015 The PHP Group
Zend Engine v2.6.0, Copyright (c) 1998-2015 Zend Technologies
```

This is the version of PHP which will be used by Composer. Make sure it meets the minimum required version for UserFrosting!

If it's a lower version than the version that you **know** your webserver uses, then chances are that your terminal is incorrectly resolving the `php` command. This happens because there is an older version of PHP (often preinstalled with your operating system) in one of the paths specified in your path variable (`$PATH` in *nix systems, `PATH` environment variable in Windows).

If you're using a distribution like XAMPP or WAMP, you'll want to update your `PATH` variable so that your _terminal_ finds the same version of PHP that your webserver uses. This process depends heavily on the distribution you're using and your operating system (Google it!)  However, the general steps are:

1. Determine the path to the version of PHP that your webserver uses. For example, the XAMPP distribution has PHP installed in its own directory, e.g. `/Applications/XAMPP/xamppfiles/bin/`.
2. Append that path to your `PATH` variable. In `*nix` systems, this can be set in your shell config file, for example `~/.bash_profile`. The command should look something like `export PATH="/path/to/newer/version/of/php:$PATH"`. See [this answer](http://superuser.com/a/284351/378833) on Superuser for information on modifying `PATH` in your operating system.
3. Restart your terminal.
4. Run the command `which php` to ensure that the `php` command is now resolving to the correct directory. If not, double-check steps 1-3.

>>>>>> To check the value of your `PATH` variable in *nix environments, simply run `echo $PATH`.

### Running Composer

Once you've got the right version of PHP running from your command line, it's time to run Composer:

```bash
$ composer install
```

This may take some time to complete. If Composer has completed successfully, you should see that a `vendor/` directory has been created under `app/`. This `vendor/` directory contains all of UserFrosting's PHP dependencies - there should be nearly 30 subdirectories in here!

If you only see `composer` and `wikimedia` subdirectories after running `composer install`, then you may need to delete the `composer.lock` file and run `composer install` again.

## Run the installer 

You're almost done! We now have the base code and the php dependencies. Last thing left to do is setup the database, create the first user and repare the assets. Luckily, at this point, UserFrosting _bakery_ is here to help ! 

### Set up the database

Before installing, you'll need to create a database and database user account. Consult your database documentation for more details. If you use _phpmyadmin_ or a similar tool, you can create your database and database user through their interface. Otherwise, you can do it via the command line.

>>>>> "Database user account" and "UserFrosting user account" are not the same thing. The "database user account" is independent of UserFrosting. See your database technology's documentation for information on creating a database user. Make sure that your database user has all read and write permissions for your database.

### Bake installer

To finish the installation and create your first UserFrosting account, we will run the command-line installer:

```bash
$ php bakery bake
```

You will first be prompted for your database credentials. This is the information PHP needs to connect to your database. If PHP can't connect to your database using theses credential, make sure you have entered the right informations and run the `bake` command to try again. 

If the database connexion is successful, the installer will then check that basic dependencies are met. If so, the installer will run the _migrations_ to populate your database with new tables. During this process, you will be prompted for some information to set up the master account. Finally, thge installer will run the `build-assets` command to fetch javascript dependencies and build the [assets bundles](/building-pages/assets).

## Visit your website

At this point, you should be able to access the basic pages for your application and login with the newly created amster account. Visit:

`http://localhost/userfrosting/public/`

You should see a basic page:

![Basic front page of a UserFrosting installation](/images/front-page.png)

## Changing git remote

At this point, you should also change your **remotes**. Since you are starting your own project at this point, rather than working on changes that would eventually be merged into the main UserFrosting repository on GitHub, we'll give the GitHub remote a different, more meaningful name. First, use `git remote -v` to see the current remotes:

```bash
$ git remote -v
origin	https://github.com/userfrosting/UserFrosting.git (fetch)
origin	https://github.com/userfrosting/UserFrosting.git (push)
```

This basically means that `origin` is a shortcut for pushing and pulling to the official UserFrosting repository on GitHub.  Let's change that:

```bash
$ git remote rm origin
$ git remote add upstream https://github.com/userfrosting/UserFrosting.git
$ git remote -v
upstream	https://github.com/userfrosting/UserFrosting.git (fetch)
upstream	https://github.com/userfrosting/UserFrosting.git (push)
```

This renames the `origin` remote to `upstream`.  Let's also disable the `push` part of this remote (don't worry, you won't have push rights for the official repo anyway, but this will help us stay organized):

```bash
$ git remote set-url --push upstream no-pushing
$ git remote -v
upstream	https://github.com/userfrosting/UserFrosting.git (fetch)
upstream	no-pushing (push)
```

Now, if we were to try and push to `upstream` for some reason, we'll get a useful error instead of being prompted for credentials.

For future reference (you don't have to do this right now) with the `upstream` remote set up, you will be able to pull any updates from the official UserFrosting repository into your project:

```bash
$ git fetch upstream
$ git checkout master
$ git merge upstream/master
```

See GitHub's article on [syncing a fork](https://help.github.com/articles/syncing-a-fork/) for more information.

If you are developing as part of a team, you may wish to set up a _new_ `origin` remote, for example one that points to a private repo on Bitbucket. When you are ready to deploy, you may also set up yet another `deploy` remote, which will allow you to push your code directly to the production server. See [deployment](/going-live/deployment) for more information.

## Star the project and follow us on Twitter

It will help us a lot if you could star [the UserFrosting project on GitHub](https://github.com/userfrosting/UserFrosting). Just look for the button in the upper right-hand corner!

[![How to star](/images/how-to-star.png)](https://github.com/userfrosting/UserFrosting)

You should also follow us on Twitter for real-time news and updates:

<a class="twitter-follow-button" href="https://twitter.com/userfrosting" data-size="large">Follow @userfrosting</a>

Congratulations!  Now that this is complete, you're ready to start developing your application by [creating your first Sprinkle](/sprinkles).
