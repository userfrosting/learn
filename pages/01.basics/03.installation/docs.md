---
title: Installation
metadata:
    description: Getting UserFrosting up and running in your development environment.
taxonomy:
    category: docs
---

The process of setting up UserFrosting so that you can begin work in your [local development environment](/basics/develop-locally-serve-globally) is known as **installation**.  This is a separate process from [deployment](/going-live/deployment), which is when you actually push your fully developed application to a live server.  Please be sure that you understand this distinction before proceeding further!  UserFrosting is not like, for example, Wordpress, where you can "install" directly to your production server.

## Environment

### Web Stack

If you don't already have a local development environment set up, please [do so now](/basics/requirements/develop-locally-serve-globally#setting-up-a-local-development-environment).

Make certain that your development environment meets the [minimum requirements](/basics/requirements/basic-stack).  In particular, make sure that you have PHP **5.5.9** or higher installed, as well as a webserver that supports URL rewriting (for example, Apache with `mod_rewrite` enabled).

### Git

You will also need to make sure that you have **git** [installed](/basics/requirements/essential-tools-for-php#git).  git is one of the most popular [version control systems](https://en.wikipedia.org/wiki/Version_control) in the world, and is important to use with UserFrosting for three reasons:

1. It makes it easier to merge updates in UserFrosting into your project;
2. It makes it easier for you and your team to keep track of changes in your code, and allows your team to work simultaneously on different features;
3. It makes it easy to deploy and update your code on your production server (if you're using a VPS or dedicated hosting).

**git is not the same as GitHub!**  GitHub is a "social coding" company, while git is the open-source software around which GitHub was built. Many open source projects choose to use GitHub to host their git repositories, because GitHub offers free hosting for public repositories.  However, you should be aware that there are other companies that offer free git hosting such as Atlassian (Bitbucket).  Unlike GitHub, Atlassian also offers free _private_ repositories.  You can also [set up your own server to host repositories](http://stackoverflow.com/a/5507556/2970321), or use a third-party package such as Gitlab, which has GitHub/Bitbucket-like features such as issue tracking, code review, etc.

### Node.js

Finally, you will need to install **Node.js**, an extremely popular Javascript runtime.  Although UserFrosting does not _run_ on server-side Javascript, it does use several Javascript-based tools to fetch client-side Javascript and CSS dependencies, as well as perform critical build tasks.

To check if Node.js is installed:

```bash
$ node -v
v7.4.0
```

The [Node.js website](https://nodejs.org/en/) provides easy-to-use installers for most operating systems.  We recommend using the latest version of Node.js (7.x at the time of this writing).

## Package Managers

As [mentioned earlier](/basics/requirements/basic-stack#third-party-components-why-dont-you-write-all-your-own-code), UserFrosting depends on a lot of different third-party libraries.  Rather than try to manage all these dependencies manually (which would be a nightmare), UserFrosting uses **package managers**, which are tools that automatically download and install the dependencies for your application.

UserFrosting is built around two extremely popular package managers: **Composer**, for managing PHP packages, and **npm**, which manages Node.js packages.

**Bower**, a frontend package manager is also used.

### Install Composer

**Composer** is the _de facto_ package manager for PHP, and it is needed to automatically fetch UserFrosting's dependencies and build the autoloader (so that you don't need to write a bunch of `require` statements in your code).

The preferred way to work with Composer is to install it globally.  Instructions can be found on Composer's [website](https://getcomposer.org/doc/00-intro.md#globally). 

To chcek if Composer is installed:

```bash
$ composer --version
Composer version 1.3.1 2017-01-07 18:08:51
```

### Update npm

npm stands for **N**ode **P**ackage **M**anager.  It is used to grab the various Node packages that are required by UserFrosting's installation and build tools.  When you installed Node, it should have automatically installed npm as well.  However, we still recommend updating npm:

```bash
$ npm install npm@latest -g
```

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

### npm dependencies

The `build` directory contains the scripts and configuration files required to download Javascript, CSS, and other assets used by UserFrosting.

Before we can run these scripts, we need to install some required npm packages:

```bash
$ npm install
```

This command will install Gulp, Bower, and several other required npm packages.

### UserFrosting assets

Now that we have npm set up with all of its required packages, we can use it to automatically download and install the assets in the correct directories:

```bash
$ npm run uf-assets-install
```

That's it!  Your project should now have all the required PHP and client-side dependencies that it needs to function.

>>>> If you have issues, ensure that **git** is installed.

## Visit your website

At this point, you should be able to access the basic pages for your application (without a signed-in user).  Visit:

`http://localhost/myUserFrostingProject/public/`

You should see a basic page:

![Basic front page of a UserFrosting installation](/images/front-page.png)

## Set up the database and root user account

You're almost done!  To actually make the **user** part of UserFrosting work, we'll need to set up the database tables and a root user account.

### Database configuration

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

### UserFrosting installer

To set up the database tables and create your first UserFrosting account, we will run the command-line installer:

```bash
$ cd /path/to/myUserFrostingProject/migrations
$ php install.php
```

You will be prompted to confirm your operating system, after which the tables and some default rows will be created.  Next, you will be prompted for some information to set up the master account.

Once this has completed successfully, you can sign in with your root account at `http://localhost/myUserFrostingProject/public/account/sign-in-or-register`.

Congratulations!  Now that this is complete, you're ready to start developing your application by [creating your first Sprinkle](https://learn.userfrosting.com/sprinkles).
