---
title: Basic Stack Requirements
metadata:
    description: UserFrosting requires a web server, PHP, and some sort of database.
taxonomy:
    category: docs
process:
    twig: true
---

The basic stack requirements for running UserFrosting are pretty typical of any web framework or CMS. Those requirements are the software required to _run_ UserFrosting, usually on a "server". These are different from the [developer tools used to build your website](/installation/requirements/essential-tools-for-php) which we'll see on the next page.

To run UserFrosting, you'll need four things :

1. [Web Server Software Requirements](#web-server-software-requirements)
2. [PHP Requirements](#php-requirements)
3. [Database](#database)
4. [SMTP (Mail) Server](#smtp-mail-server)

[notice=note]If you're already using or plan on using Docker, all of the necessary stack will already be configured for you. However, it's important to understand what is required to run UserFrosting, so you can understand the services Docker provides for you![/notice]

## Web Server Software Requirements

To run any website, you need *web server software*. Its tasks are to receive client requests, execute them, and send a reply. For a PHP website, the web server software won't execute the PHP code itself. Instead, it passes it to PHP which interprets the code and returns a response for the web server to display.

The most popular web servers today are : 

- [Nginx](https://www.nginx.com)
- [Apache](https://httpd.apache.org)
- [IIS](https://www.iis.net)

Any of these can be used to run UserFrosting. However, when developing locally (on your computer), it's also possible to use [PHP's built-in web server](https://www.php.net/manual/en/features.commandline.webserver.php). This option isn't suited for production websites (for people on the internet to access), but it's a perfectly viable option when testing your application. The biggest benefit is you don't have to install anything else if you already have PHP installed!

## PHP Requirements

You're probably here because you already know what PHP is. Great! The only thing left to say is UserFrosting requires **PHP 8.1** or higher. However, it's highly recommended you use the latest supported version, which is *PHP 8.3*.

### But my host only supports PHP 7.x! Why do I need PHP 8.1?

Look, programming languages evolve, and PHP is no exception. Actually, PHP and other web languages have it particularly tough because they have so many responsibilities. PHP is the bouncer at the door: it has to be prepared to defend against constantly evolving security threats to your server. At the same time, it has to keep up with demand for faster performance, and satisfy the demand for new features from the [enormous](https://w3techs.com/technologies/overview/programming_language/all) PHP community.

Honestly, PHP 8.1 isn't exactly cutting edge - in fact, **PHP 8.1 was no longer in active support as of [November 25th, 2023](http://php.net/supported-versions.php) and will be declared "End of Life" as of [November 25th, 2024](http://php.net/supported-versions.php)**.

In fact, we didn't make this decision directly. UserFrosting depends on a lot of third-party components, and *those* components require a minimum PHP version of _8.1_. As the whole community moves forward, UserFrosting does too. And fast! PHP 8.3 will only be supported until [November 23th, 2025](http://php.net/supported-versions.php) !

If your hosting service doesn't have PHP 8.1 or above installed, call them and ask them to upgrade. If they refuse, point out that PHP 7.4 has been out of life for {{ date("now").diff(date("2022-11-28")).m }} months! To be honest, there is little reason to use a shared hosting (e.g. cPanel) service these days, especially when VPS providers like DigitalOcean and Amazon EC2 are so inexpensive. Unless you're stuck with shared hosting for some reason or another (fussy boss), [there's no real reason not to switch to a VPS](https://www.hostt.com/still-use-shared-hosting-theres-vps/).

As for your local development environment ([You _do_ have a local development environment, right ?](/background/develop-locally-serve-globally)), if it's that much of a burden then... I don't know what to tell you. So what are you waiting for? Upgrade!

[notice=note]As a reminder, as of UserFrosting 5.1, **PHP 8.3** is officially recommended. While you can still use UserFrosting 5 with PHP 8.1 and 8.2, upgrading to PHP 8.3 is highly recommended. Both PHP 8.1 and 8.2 support will eventually be removed the future.[/notice]

### PHP Extensions

UserFrosting and its dependencies requires some PHP Libraries and Extensions to be installed and enabled : 

- [GD](https://www.php.net/manual/en/book.image.php)
- [DOM](https://www.php.net/manual/en/book.dom.php)
- [ZIP](https://www.php.net/manual/en/book.zip.php)

Occasionally, people use web hosting services that do not provide the GD library, or provide it but do not have it enabled. The GD library is an image processing module for PHP. UserFrosting uses it to generate the captcha code for new account registration. The DOM and ZIP extensions are used by Composer.

## Database

To store data, UserFrosting requires a [relational database](https://www.techtarget.com/searchdatamanagement/definition/database) provider. UserFrosting support the following database providers:
- [MySQL 8](https://www.mysql.com/)
- [MariaDB](https://mariadb.org)
- [SQLite 3](https://www.sqlite.org/index.html)
- [PostgreSQL 14](https://www.postgresql.org)
- [Microsoft SQL Server 2019](https://en.wikipedia.org/wiki/Microsoft_SQL_Server)

[notice]MariaDB is an open-source fork of MySQL. The reason it exists is because of [numerous concerns](https://www2.computerworld.com.au/article/457551/dead_database_walking_mysql_creator_why_future_belongs_mariadb/) that Oracle would not do a good job honoring the open-source nature of the MySQL community. For all technical purposes, MariaDB and MySQL are more or less perfectly interoperable.[/notice]

[notice=warning]Support for SQL Server on Windows is **experimental**. Contact the UserFrosting team if you want to help improve support for SQL Server ![/notice]

MySQL and MariaDB are the most popular choice of database provider. However, when developing locally, you can skip installing additional software by using **SQLite** as your database provider. SQLite support is built-in to PHP and the data is stored as a file within the UserFrosting directory structure. This option isn't suited for production websites as it's slower than other solutions, but it's perfectly viable when testing your application locally.

[notice=tip]It is not required to develop with the same database provider as the one you'll be using in production. It's totally fine to develop locally using SQLite and use MySQL on your production server. A [testing environment](/testing) can be used to make sure your code runs smoothly on both.[/notice]

[notice=tip]One additional reason not to use SQLite in production: it does not fully support `ALTER TABLE` operations, and [the official workaround is twelve steps long](https://www.sqlite.org/lang_altertable.html#otheralter).  This is most likely to cause issues when [rolling back a migration](/cli/commands#migrate-rollback), or if you decide to [remove foreign keys or columns (or tables!)](https://www.sqlite.org/lang_altertable.html#altertabdropcol) in a later migration. There is an easier [workaround for dropping entire tables](/database/migrations#running-your-migration), but this is usually not acceptable in production. In development, it's much easier to drop a table if needed, re-run any needed migrations, and restore any missing (test) data.[/notice]

## SMTP (Mail) Server

The final piece of software required on your server is an *SMTP Server*. The *Simple Mail Transfer Protocol* (SMTP) is an application used by mail servers to send, receive, and relay outgoing email between senders and receivers. UserFrosting requires you provide a SMTP server for sending email to your users (especially registration emails).

Again, when developing locally it's possible to use third-party services and applications to handle emails, like *Mailpit*, *Mailtrap*, or even *Gmail*. However, keep in mind a complete SMTP server is required in production.
