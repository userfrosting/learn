---
title: Installing Requirements
metadata:
    description: Getting UserFrosting up and running in your development environment.
taxonomy:
    category: docs
---

If your local development environment doesn't already have the [required stack and tools](/installation/requirements), we'll now set them up. We'll go through the following:

- [Command Line Interface](#cli)
- [PHP 8.0 or higher](#php)
- [Composer 2](#composer)
- [Node.js 18.0 or higher](#node)
- [Npm 9 or higher](#npm)
- [Mail Server](#mail)

If you followed the previous pages, you probably noticed two pieces of software absent from that list: The *web server software* and the *database*. This is on purpose. This guide uses the built-in PHP Server and SQLite, which don't require additional installation. Optionally, you may want to install a web server (e.g.: Apache, Nginx, etc) and a database (MariaDB, MySQL, Postgres or SQL Server). The "optional" part of this guide will cover setup for those.

[notice]Please **make sure** that you have these installed **before** attempting to install UserFrosting![/notice]

## Installing required tools

### CLI

[plugin:content-inject](/04.installation/_modular/cli)

### PHP

Installing PHP 8.2 locally will make it easier to develop locally, as it will allow you to run Composer locally, too. 

#### MacOS
The easiest way to install PHP on MacOS is through Homebrew:
1. Install XCode Command Line Tools : `xcode-select --install`
2. Install [Homebrew](https://brew.sh) using their guide
3. Install PHP 8.2, from the terminal : `brew install shivammathur/php/php@8.2` 

[notice=tip]It's possible to use multiple versions of PHP on MacOS. See [shivammathur/php documentation](https://github.com/shivammathur/homebrew-php#switch-between-php-versions).[/notice]

#### Linux & Windows WSL2
Install PHP through the package manager. For example, on Ubuntu : 

1. Add [Ondřej Surý PPA](https://launchpad.net/~ondrej/+archive/ubuntu/php/) to get the latest version : 
    ```bash
    sudo add-apt-repository ppa:ondrej/php
    sudo apt update
    ```

2. Install PHP and the necessary extensions : 
   ```bash
   sudo apt install php8.2 php8.2-gd php8.2-dom php8.2-zip php8.2-sqlite3 php8.2-pdo_mysql php8.2-curl php8.2-mbstring unzip
   ```

#### Preflight checks
Before going further, you should check which version of PHP will be run **in the command line**. It is very common for a single environment to have multiple different versions of PHP installed. If you've never run PHP from the command line before, you may not realize that the version of PHP run by the *webserver* (Apache, nginx, etc.) can be different from the one that will run in the *command line*.

To check the "command line" version, use the command:

```bash
php -v
```

You should then see a message like:

```txt
PHP 8.2.9 (cli) (built: Aug 16 2023 21:20:30) (NTS)
Copyright (c) The PHP Group
Zend Engine v4.2.9, Copyright (c) Zend Technologies
    with Xdebug v3.2.0, Copyright (c) 2002-2022, by Derick Rethans
    with Zend OPcache v8.2.9, Copyright (c), by Zend Technologies
```

This is the version of PHP which will be used by Composer. Make sure it meets the minimum required version for UserFrosting!

[notice]If it's a lower version than the version that you **know** your webserver uses, then chances are that your terminal is incorrectly resolving the `php` command. This happens because there is an older version of PHP (often preinstalled with your operating system) in one of the paths specified in your path variable (`$PATH` in *nix systems, `PATH` environment variable in Windows).

To check the value of your `PATH` variable in *nix environments, simply run `echo $PATH`.[/notice]

To check if the required extensions are enabled, you can use :

```bash
php -m
```

### Composer

Next step is to install [Composer 2](/installation/requirements/essential-tools-for-php#composer-2). At this point, the installation is the same for MacOS, Linux and Windows WSL2. The full instructions for installing Composer can be found at their [website](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx). We **strongly recommend that you install [Composer globally](https://getcomposer.org/doc/00-intro.md#globally)** on your system. This will let you run Composer using the `composer` command.

```bash
mv composer.phar /usr/local/bin/composer
```

[notice=warning]Composer has a special installer that you can use for **Windows** - [Composer-Setup.exe](https://getcomposer.org/Composer-Setup.exe). However, since we're using WSL2 (a Linux backend), this installer **won't work** and won't be available in the command line.[/notice]

#### Preflight checks

To check if Composer is correctly installed:

```bash
$ composer --version
Composer version 2.5.4 2023-02-15 13:10:06
```

### Node

It's now time to install [Node.js](https://nodejs.org/en/). Any version above **18.0** will work with UserFrosting 5.0, however we recommend using the latest LTS version of Node.js (18.18.2 LTS as of October 2023).

#### MacOS

Node.js has an installer that you can use for MacOS - [Node.js Download](https://nodejs.org/en/download). Node will then be available in Terminal.

#### Linux & Windows WSL2

Node.js binary distributions are available from [NodeSource](https://github.com/nodesource/distributions#nodejs).

#### Preflight checks

To check if Node.js is installed:

```bash
$ node -v
v18.17.1
```

### npm

When you installed Node, it should have automatically installed npm as well. However, we still recommend updating npm (if unable to update, any version later than **9.0** should work):

```bash
npm install npm@latest -g
```

To check which version of Npm is installed:

```bash
$ npm -v
10.1.0
```

UserFrosting build scripts will automatically install all other Node and NPM dependencies for you !

### Mail

As we've seen [in previous pages](/installation/requirements/basic-stack#smtp-mail-server), UserFrosting requires an SMTP server for sending email to your users, especially registration emails. However, in a local environment you don't want "test" emails to be sent to your users. You also don't want to fill your inbox with test emails.

This is where email testing tools come in. When an email is sent by UserFrosting, instead of sending the email to a real mailbox, the email testing tool will capture the email and makes it available to you to read, regardless of the recipient of the email. In other words, if UserFrosting send five emails to five different email address, all five email will be captured and displayed in the same "test" inbox.

While multiple solutions are available, two are recommended by UserFrosting : **Mailpit** and **Mailtrap**. It's up to you to choose the one you prefer.

[notice]Please note that in production, you _will_ need to have a real, working SMTP service. If you do not already have a mail provider, please see our section on [mail providers](/going-live/mail-providers#choosing-a-mail-service-provider) for our recommendations including both free and paid third-party mail services. While it's not recommended, a real SMTP server *can* also be used in a development environment.[/notice]

#### Mailpit

[Mailpit](https://github.com/axllent/mailpit) is a small, fast, low memory, zero-dependency, multi-platform email testing tool & API for developers. Mailpit runs locally, acts as a *fake* SMTP server and provides a modern web interface to view & test captured emails. Oh, and **it's free and Open Source**!

Mailpit can be installed on [MacOS through Homebrew](https://github.com/axllent/mailpit#install-via-package-managers), on Linux/WSL2 through their [Bash Script](https://github.com/axllent/mailpit#install-via-bash-script-linux--mac), or through [Docker](https://github.com/axllent/mailpit/wiki/Docker-images). By default, Mailpit UI can be access at [http://0.0.0.0:8025](http://0.0.0.0:8025).

When using Mailpit with UserFrosting, the following parameters will need to be provided during UserFrosting installation, which we'll see on the next page : 

| Param       | Value     |
|-------------|-----------|
| SMTP_HOST   | localhost |
| SMTP_PORT   | 1025      |
| SMTP_SECURE | false     |
| SMTP_AUTH   | false     |

#### Mailtrap

[Mailtrap](https://mailtrap.io/) is similar to Mailpit, but it runs in the cloud, so there's nothing to install. However, Mailtrap is not open source. Mailtrap features a forever free plan that offers basic functionality for personal use. The *Free Sandbox* provides one inbox and up to 100 emails per month. It's a great way to get started, as it's super easy and fast to setup. For a more permanent solution however, Mailpit should be preferred. 

To get started, simply create your account on [Mailtrap's website](https://mailtrap.io/register/signup). 

When using Mailtrap with UserFrosting, the following parameters will need to be provided during UserFrosting installation, which we'll see on the next page : 

| Param         | Value                    |
|---------------|--------------------------|
| SMTP_HOST     | sandbox.smtp.mailtrap.io |
| SMTP_PORT     | 25 or 465 or 587 or 2525 |
| SMTP_USER     | *See below*              |
| SMTP_PASSWORD | *See below*              |

The *user* and *password* are unique to your Mailtrap inbox, and can be found in your Mailtrap account.

## Optional Installation

The next tools are not required in your local development environment to run UserFrosting. However, you may be interested in installing them anyway; or the instructions may be helpful for those tools which apply to you. 

### Git

By default, MacOS and other Linux operating systems should come with git preinstalled. On MacOS, Apple also ships a binary package of Git with Xcode. You may not need to install it manually. If you would like to update your version of git, you can do so with their [installer](https://git-scm.com/downloads).

[notice=tip]If you're looking for a Git GUI and are working with Github, you might be interested in [Github Desktop](https://desktop.github.com).[/notice]

### Web Server Config

As mentioned at the beginning of this page, it's not required to install a web server on your local stack, as we'll use the PHP Built-in Server. However, if you prefer to install Apache or Nginx, it's certainly possible to do so.

To serve UserFrosting with a web server, you should configure your web server's document / web root to be the `/public` directory. The `index.php` in this directory serves as the front controller for all HTTP requests.

#### Apache

Apache can be installed natively on Linux using most package managers. Some [very useful guides](https://www.digitalocean.com/community/tutorials/how-to-install-the-apache-web-server-on-ubuntu-22-04) can be found online with instructions for Ubuntu. It can also be installed through [Homebrew on MacOS](https://formulae.brew.sh/formula/httpd).

When using Apache to serve UserFrosting, check that you have the Rewrite Engine module (`mod_rewrite.c`) installed and enabled. Some distributions may not have this module automatically enabled, and you will need to do so manually. In a shared hosting environment, you may need to have your hosting service do this for you.

**In addition**, make sure that the `Directory` block in your `VirtualHost` configuration is set up to allow `.htaccess` files. For example:

```txt
# Allow .htaccess override
<Directory /var/www/userfrosting/public/>
    Options Indexes FollowSymLinks MultiViews
    AllowOverride All
    Order allow,deny
</Directory>
```

UserFrosting includes a `public/.htaccess` file that is used to provide URLs without the index.php front controller in the path. Before serving UserFrosting with Apache, be sure to enable the `mod_rewrite` module so the `.htaccess` file will be honored by the server.

#### Nginx

Nginx can be installed natively on Linux using most package managers. Some [very useful guides](https://www.digitalocean.com/community/tutorials/how-to-install-nginx-on-ubuntu-22-04) can be found online with instructions for Ubuntu. It can also be installed through [Homebrew on MacOS](https://formulae.brew.sh/formula/nginx).

When using Apache to serve UserFrosting, make sure to include this directive in your site config.

```txt
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### Database

As mentioned at the beginning of this page, it's not required to install a database provider in your local stack, as we'll use the file-based SQLite database. However, if you prefer to install MySQL or Progress, you may do so.

For example, MySQL can be installed natively on Linux. If you're interested, check out *Digital Ocean's [How To Install MySQL on Ubuntu 22.04]*(https://www.digitalocean.com/community/tutorials/how-to-install-mysql-on-ubuntu-20-04) guide. On MacOS, it can obviously be installed through [Homebrew](https://formulae.brew.sh/formula/mysql).

It's also possible to install each database provider with Docker:
- [MySQL](https://hub.docker.com/_/mysql)
- [MariaDB](https://hub.docker.com/_/mariadb)
- [PostgresSQL](https://hub.docker.com/_/postgres/)
- [Microsoft SQL Server](https://hub.docker.com/_/microsoft-mssql-server)

Before installing UserFrosting, you'll need to create a database and database user account. Consult your database documentation for more details. If you use [_phpmyadmin_](https://hub.docker.com/_/phpmyadmin) or a similar tool, you can create your database and database user through their interface. Otherwise, you can do it via the command line.

[notice=note]"Database user account" and "UserFrosting user account" are not the same thing. The "database user account" is independent of UserFrosting. See your database's documentation for information on creating a database user. Make sure that your database user has all read and write permissions for your database.[/notice]
