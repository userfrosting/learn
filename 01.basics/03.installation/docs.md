---
title: Installation
metadata:
    description: Getting UserFrosting up and running in your development environment.
taxonomy:
    category: docs
---

The process of setting up UserFrosting so that you can begin work in your [local development environment](/basics/develop-locally-serve-globally) is known as **installation**.  This is a separate process from [deployment](/going-live/deployment), which is when you actually push your fully developed application to a live server.  Please be sure that you understand this distinction before proceeding further!  UserFrosting is not like, for example, Wordpress, where you can "install" directly to your production server.

## Environment

### Web stack

If you don't already have a local development environment set up, please [do so now](/basics/requirements/develop-locally-serve-globally#setting-up-a-local-development-environment).

Make certain that your development environment meets the [minimum requirements](/basics/requirements/basic-stack).  In particular, make sure that you have PHP **5.6** or higher installed, as well as a webserver that supports URL rewriting (for example, Apache with `mod_rewrite` enabled).

### Other required software

Please make sure that you have the following installed before attempting to install UserFrosting:

- [Git](/basics/requirements/essential-tools-for-php#git)
- [Composer](/basics/requirements/essential-tools-for-php#composer)
- [Node.js](/basics/requirements/essential-tools-for-php#nodejs)

## Clone the UserFrosting repository

The best way to initially set up UserFrosting in your local environment is by using git to **clone** the main UserFrosting repository.  Create a new subdirectory in your webserver's document root.  For example, in Apache:

```bash
$ cd /path/to/htdocs
$ mkdir myUserFrostingProject
```

```
htdocs
└── myUserFrostingProject
```

To clone the repository, simply run:

```bash
$ cd myUserFrostingProject
$ git clone https://github.com/userfrosting/UserFrosting.git .
```

>>>> Note the `.` at the end of the second command - if you omit it, `git` will try to create another subdirectory inside `myUserFrostingProject`!

At this point, you should also change your **remotes**.  Since you are starting your own project at this point, rather than working on changes that would eventually be merged into the main UserFrosting repository on GitHub, we'll give the GitHub remote a different, more meaningful name.  First, use `git remote -v` to see the current remotes:

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

If you are developing as part of a team, you may wish to set up a _new_ `origin` remote, for example one that points to a private repo on Bitbucket.  When you are ready to deploy, you may also set up yet another `deploy` remote, which will allow you to push your code directly to the production server.  See [deployment](/going-live/deployment) for more information.

### Set up `sprinkles.json`

The `sprinkles.json` file tells UserFrosting which [Sprinkles](/sprinkles) to load, and in what order.  It is also used by Node to determine which client-side dependencies to download from the Bower repository.

An example `sprinkles.json.example` file is provided in the `app/sprinkles/` directory.  To start, simply copy this to a new file, `sprinkles.json`.  The file should look like this:

```json
{
    "base": [
        "account",
        "admin"
    ]
}
```

To learn more about Sprinkles, see [Chapter 2](/sprinkles/introduction).

### Set directory permissions

Make sure that `/app/cache`, `/app/logs`, and `/app/sessions` are writable by your webserver.  See [File System Permissions](/basics/requirements/basic-stack#file-system-permissions) for help with this.

## Install dependencies

### PHP dependencies

Next, we will run Composer in the `app` subdirectory to fetch and install the PHP packages used by UserFrosting:

```bash
$ cd app
$ composer install
```

This may take some time to complete.

>>>> If you're using a distribution like XAMPP or WAMP, the version of PHP that your webserver runs might not be the same version that Composer will run.  This can lead to problems if Composer ends up running a version of PHP below 5.6.  You may need to set your path variable to point to the newer version of PHP.

If Composer has completed successfully, you should see that a `vendor/` directory has been created under `app/`.  This `vendor/` directory contains all of UserFrosting's PHP dependencies - there should be nearly 30 subdirectories in here!

### npm dependencies

The `build` directory contains the scripts and configuration files required to download Javascript, CSS, and other assets used by UserFrosting.

Before we can run these scripts, we need to install some required npm packages:

```bash
$ cd /path/to/myUserFrostingProject/build
$ npm install
```

This command will install Gulp, Bower, and several other required npm packages.

### UserFrosting assets

Now that we have npm set up with all of its required packages, we can use it to automatically download and install the assets in the correct directories:

```bash
$ cd /path/to/myUserFrostingProject/build
$ npm run uf-assets-install
```

That's it!  Your project should now have all the required PHP and client-side dependencies that it needs to function.

>>>> If you have issues, ensure that **git** is installed.

## Set up the database

You're almost done!  To actually make the **user** part of UserFrosting work, we'll need to set up the database tables and a root user account.

First thing's first, you'll need to create a database and database user account.  Consult your database documentation for more details.  If you use phpmyadmin or a similar tool, you can create your database and database user through their interface.  Otherwise, you can do it via the command line.

>>>>> "Database user account" and "UserFrosting user account" are not the same thing.  The "database user account" is independent of UserFrosting.  See your database technology's documentation for information on creating a database user.  Make sure that your database user has all read and write permissions for your database.

The basic database settings for UserFrosting can be set through environment variables.  By default, UserFrosting looks for the following environment variables:

- `DB_NAME`: The name of the database you just created
- `DB_USER`: The database user account
- `DB_PASSWORD`: The database user password

If you don't want to (or can't) configure environment variables directly in your development environment, UserFrosting uses the fantastic [phpdotenv](https://github.com/vlucas/phpdotenv) library to let you set these variables in a `.env` file.  Simply copy the sample file in your `app/` directory:

```bash
$ cp app/.env.example app/.env
```

Now, you can set values in the `.env` file and UserFrosting will pick them up _as if_ they were actual environment variables.

You may also want to configure your SMTP server settings as well at this point so that you can use features that require mail, such as password reset and email verification.  See [Chapter 11](/other-services/mail) for more information on the mail service.

## Visit your website

At this point, you should be able to access the basic pages for your application (without a signed-in user).  Visit:

`http://localhost/myUserFrostingProject/public/`

You should see a basic page:

![Basic front page of a UserFrosting installation](/images/front-page.png)

## UserFrosting installer

To set up the database tables and create your first UserFrosting account, we will run the command-line installer:

```bash
$ cd /path/to/myUserFrostingProject/migrations
$ php install.php
```

You will be prompted to confirm your operating system, after which the tables and some default rows will be created.  Next, you will be prompted for some information to set up the master account.

Once this has completed successfully, you can sign in with your root account at `http://localhost/myUserFrostingProject/public/account/sign-in-or-register`.

Congratulations!  Now that this is complete, you're ready to start developing your application by [creating your first Sprinkle](https://learn.userfrosting.com/sprinkles).
