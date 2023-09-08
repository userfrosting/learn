---
title: Quick Start Guide
metadata:
    description: The official documentation for UserFrosting, a PHP framework and full-featured user management application.
taxonomy:
    category: docs
---

[notice=note]This quick start guide is aimed at experienced PHP developers who already have a development environment set up. Head over to the [Installation Chapter](/installation) for the complete guide.[/notice]

UserFrosting is a free, open-source jumping-off point for building user-centered web applications with PHP and Javascript. It comes with a sleek, modern interface, basic user account features, and an administrative user management system - all fully functioning out of the box.

[notice]This is the documentation for **UserFrosting 5**. If you are looking for documentation for _UserFrosting 4_, please see [here](https://learn.userfrosting.com/4.6/).[/notice]

## Server Requirements

UserFrosting has a few system requirements. Of course, all of these requirements are satisfied by the [Docker](/installation/environment/docker) virtual machine, so it's highly recommended that you use Docker as your local UserFrosting development environment.

However, if you are not using Docker, you will need to make sure your local UserFrosting development environment meets the following requirements:

- Web server software (Apache, Nginx, IIS, etc)
- PHP **8.0* or higher (**8.2** recommended)
- PDO PHP Extension
- GD PHP Extension
- Database (MariaDB, MySQL, Postgres, SQLite, or SQL Server)
- [Composer 2](https://getcomposer.org/)
- [Node.js](https://nodejs.org/en/) **18.0** or higher
- npm **9** or higher

## Installing UserFrosting

Use Composer to create an empty project with the latest version of UserFrosting skeleton into a new `UserFrosting` folder:

```bash
$ composer create-project userfrosting/userfrosting UserFrosting "^5.0.0@dev"
```
<!-- TODO : Change this for release -->

This will clone the skeleton repository and run the installation process:

- Run `composer install`
- Run `php bakery bake`
- Setting up your database and SMTP credential in `app/.env`
- Checking for missing dependencies
- Create the admin user
- Running the database migration
- Building the raw assets

If any dependencies are not met, an error will occur. Simply try again after fixing said error, or manually run `composer install` and `php bakery bake` from the install directory. For more information about the `bake` command, head to the [Bakery CLI](/cli) chapter.

## Public Directory

After installing UserFrosting, you should configure your web server's document / web root to be the `/public` directory. The `index.php` in this directory serves as the front controller for all HTTP requests.

## Directory Permissions

UserFrosting needs to be able to write to the file system for a few directories:

- `/app/cache`
- `/app/logs`
- `/app/sessions`
- `/app/storage`

## Web Server Configuration

### Apache

UserFrosting includes a `public/.htaccess` file that is used to provide URLs without the index.php front controller in the path. Before serving UserFrosting with Apache, be sure to enable the `mod_rewrite` module so the `.htaccess` file will be honored by the server.

### Nginx

Use the configuration file provided in `webserver-configs/nginx.conf`.

### IIS

Please see the section on [Configuring for IIS](/installation/other-situations/iis).

## Visit your website

At this point, you should be able to access your application. You should see a basic page:

![Basic front page of a UserFrosting installation](/images/front-page.png)

## What's next...

For more detailed information about installing UserFrosting, or if you need help with the basic setup requirements, check out the [Installation Chapter](/installation). Otherwise, head over to the [Sprinkles Chapter](/sprinkles).
