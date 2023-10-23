---
title: Basic Stack Requirements
metadata:
    description: UserFrosting requires a web server, PHP, and some sort of database.
taxonomy:
    category: docs
process:
    twig: true
---

The basic stack requirements for running UserFrosting are pretty typical of any web framework or CMS. Those requirements are the software required to _run_ UserFrosting, usually on a "server". Theses are different from the developer tools, used to build your website, which we'll see on the next page.

To run UserFrosting, you'll need four things :

1. [Web Server Software Requirements](#web-server-software-requirements)
2. [PHP Requirements](#php-requirements)
3. [Database](#database)
4. [SMTP (Mail) Server](#smtp-mail-server)

[notice=note]If you're using or plan on using Docker, all of the necessary stack will already be configured for your. However, it's important to understand what is required to run UserFrosting, so you can understand which service Docker does provides for you![/notice]

## Web Server Software Requirements

To run any website, you need a *web server software*. It's task is to receive clients requests, executing them and sending a reply. In the case of a PHP website, the web server softwares won't be executing the PHP code itself, but instead pass it to PHP which will interpret it and return a response for the web server to display.

The most popular web server todays are : 

- [Nginx](https://www.nginx.com)
- [Apache](https://httpd.apache.org)
- [IIS](https://www.iis.net)

Anyone of theses can be used to run UserFrosting. However, when developing locally (on your computer), it's also possible to use [PHP Built-in web server](https://www.php.net/manual/en/features.commandline.webserver.php). This option isn't suited for production websites (the ones people on the internet can access), but it's a perfectly viable option when testing your application. The biggest benefit is you don't have to install anything else if you already have PHP installed !

## PHP Requirements

You're probably here because you already know what PHP is. Great! The only thing left to say is UserFrosting requires **PHP 8.0** or higher. However, it's highly recommended you use the latest supported version, which is *PHP 8.2*.

### But my host only supports PHP 7.x! Why do I need PHP 8.0?

Look, programming languages evolve, and PHP is no exception. Actually, PHP (and other web languages) have it particularly tough because they have so many responsibilities. PHP is the bouncer at the door and it has to be prepared to defend against the constantly evolving security threats to your server. At the same time it has to keep up with the demand for faster performance, and satisfy the demand for new features from the [enormous](https://w3techs.com/technologies/overview/programming_language/all) PHP community.

Honestly, PHP 8.0 isn't exactly cutting edge - in fact, **PHP 8.0 is no longer in active support as of [November 26th, 2022](http://php.net/supported-versions.php) and will be declared "End of Life" as of [November 26th, 2023](http://php.net/supported-versions.php)**.

And the truth is, we didn't make this decision directly. UserFrosting depends on a lot of third-party components, and *those* components require a minimum PHP version of _8.0_. Thus, UserFrosting does too, as the whole community moves forward. And fast too! PHP 8.2 will only be supported until [December 8th, 2024](http://php.net/supported-versions.php) !

If your hosting service doesn't have PHP 8 installed, call them and ask them to upgrade. If they refuse, point out that PHP 7.4 has been out of life for {{ date("now").diff(date("2022-11-28")).m }} months! To be honest, there is little reason to use a shared hosting (e.g. cPanel) service these days, especially when VPS providers like DigitalOcean and Amazon EC2 are so inexpensive. Unless you're stuck with shared hosting for some reason another (fussy boss), [there's no real reason not to switch to a VPS](https://www.hostt.com/still-use-shared-hosting-theres-vps/).

As for your local development environment ([You _do_ have a local development environment, right ?](/background/develop-locally-serve-globally)), if it's that much of a burden then... I don't know what to tell you. So what are you waiting for? Upgrade!

[notice=note]As a reminder, as of UserFrosting 5.0, **PHP 8.2** is officially recommended. While you can still use UserFrosting 5 with PHP 8.0 and 8.1, upgrading to PHP 8.2 is highly recommended as both PHP 8.0 and 8.1 support will eventually be removed the future.[/notice]

### PHP Extensions

UserFrosting and it's dependencies requires some PHP Libraries and Extensions to be installed and enabled : 

- [GD](https://www.php.net/manual/en/book.image.php)
- [DOM](https://www.php.net/manual/en/book.dom.php)
- [ZIP](https://www.php.net/manual/en/book.zip.php)

Occasionally, people use web hosting services that do not provide the GD library, or provide it but do not have it enabled. The GD library is an image processing module for PHP. UserFrosting uses it to generate the captcha code for new account registration. The DOM and ZIP extensions are used by Composer itself.

## Database

To store data, UserFrosting requires a [relational database](https://www.techtarget.com/searchdatamanagement/definition/database) provider. UserFrosting support the following database providers:
- [MySQL 8](https://www.mysql.com/)
- [MariaDB](https://mariadb.org)
- [SQLite 3](https://www.sqlite.org/index.html)
- [PostgreSQL 14](https://www.postgresql.org)
- [Microsoft SQL Server 2019](https://en.wikipedia.org/wiki/Microsoft_SQL_Server)

[notice]MariaDB is an open-source fork of MySQL. The reason it exists is because of [numerous concerns](https://www2.computerworld.com.au/article/457551/dead_database_walking_mysql_creator_why_future_belongs_mariadb/) that Oracle would not do a good job honoring the open-source nature of the MySQL community. For all technical purposes, MariaDB and MySQL are more or less perfectly interoperable.[/notice]

[notice=warning]Support for SQL Server on Windows is **experimental**. Contact the UserFrosting team if you want to help improve support for SQL Server ![/notice]

MySQL and MariaDB are the most popular choice of database provider. However, when developing locally, you can skip installing any additional software by using **SQLite** as your database provider. SQLite support is built-in PHP and the data is store in a file within the UserFrosting directory structure. This option isn't suited for production websites as it's slower than other solution, but it's a perfectly viable option when testing your application locally.

[notice=tip]It is not required to develop with the same database provider as the one you'll be using in production. It's totally fine to develop locally using SQLite and use MySQL on your production server. [Testing environment](/testing) can be used to make sure your code run smoothly on both.[/notice]

## SMTP (Mail) Server

The final piece of software you'll require on your server is a *SMTP Server*. The *Simple Mail Transfer Protocol* (SMTP) is an application used by mail servers to send, receive, and relay outgoing email between senders and receivers. UserFrosting requires you provide a SMTP server for sending email to your user, especially registration emails.

Again, when developing locally it's possible to use third-party services and applications to handle emails, like *Mailpit*, *Mailtrap* or even *Gmail*. However, keep in mind a complete SMTP server will be required when going into production.
