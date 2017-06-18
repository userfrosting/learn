---
title: Quick Start Guide
metadata:
    description: UserFrosting is a free, open-source jumping-off point for building user-centered web applications with PHP and Javascript.
taxonomy:
    category: docs
---

>>>>> This quick start guide is aimed at experienced PHP developers who already have a development environment set up. Head over to the [Installation Chapter](/installation) for the complete guide.

UserFrosting is a free, open-source jumping-off point for building user-centered web applications with PHP and Javascript.  It comes with a sleek, modern interface, basic user account features, and an administrative user management system - all fully functioning out of the box.

>>>> This is the documentation for UserFrosting 4.  If you are looking for documentation for previous versions of UserFrosting, please see [here](http://v3.userfrosting.com).

## Server Requirements

UserFrosting has a few system requirements. Of course, all of these requirements are satisfied by the [Homestead](/installation/environment/homestead) virtual machine, so it's highly recommended that you use Homestead as your local UserFrosting development environment.

However, if you are not using Homestead, you will need to make sure your local UserFrosting development environment meets the following requirements:

- Web server software (Apache, Nginx, IIS, etc)
- PHP **5.6** or higher
- PDO PHP Extension
- GD PHP Extension
- Database (MariaDB, MySQL, Postgres, SQLite, or SQL Server)
- [Composer](https://getcomposer.org/)
- [Node.js](https://nodejs.org/en/) **4.0** or higher
- npm **3.0** or higher 

## Installing UserFrosting

Installing UserFrosting is done by issuing the Composer create-project command in your terminal. For more installation options, see the [Installation](/installation) chapter.

```bash
$ git clone https://github.com/userfrosting/UserFrosting.git userfrosting
```

This will clone the latest version of UserFrosting in a new `userfrosting` folder.

Once the project is created using composer, `cd` into the newly created `userfrosting` folder and run the following command to fetch the composer dependencies:

```bash
$ composer install
```

Now run the final setup by calling the [Bakery CLI](/cli):

```bash
$ php bakery bake
``` 

This will run installation process, which consists of setting up your database credential in `app/.env`, checkching for missing dependencies, run the database migration and build the raw assets. If any dependencies are not met, an error will occur. Simply run the command again after fixing said error. For more information about the `bake` command, heads to the [Bakery CLI](/cli) chapter.

## Public Directory

After installing UserFrosting, you should configure your web server's document / web root to be the `/public` directory. The `index.php` in this directory serves as the front controller for all HTTP requests.

## Directory Permissions

UserFrosting needs to be able to write to the file system for a few directories:

- `/app/cache`
- `/app/logs`
- `/app/sessions`

## Web Server Configuration

### Apache

UserFrosting includes a `public/.htaccess` file that is used to provide URLs without the index.php front controller in the path. Before serving UserFrosting with Apache, be sure to enable the `mod_rewrite` module so the `.htaccess` file will be honored by the server.

### Nginx

Use the configuration file provided in `webserver-configs/nginx.conf`.

### IIS

Please see the recipe [Hosting with IIS](/recipes/hosting-with-iis).

## Visit your website

At this point, you should be able to access your application. You should see a basic page:

![Basic front page of a UserFrosting installation](/images/front-page.png)

## What's next...

For more detailed information about installing UserFrosting, or if you need help with the basic setup requirements, check out the [Installation Chapter](/installation). Otherwise, head over to the [Sprinkles Chapter](/sprinkles).
