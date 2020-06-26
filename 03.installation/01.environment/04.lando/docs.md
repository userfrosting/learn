---
---
title: Lando
metadata:
  description: Lando is a wrapper around Docker that simplifies the process for PHP applications to run on Docker.
taxonomy:
  category: docs
---

>>> To contribute to this documentation, please submit a pull request to our [learn repository](https://github.com/userfrosting/learn/tree/master/pages).

[Lando](https://lando.dev/) provides a stable, easy-to-use, and portable local development environment. It allows you to integrate [phpMyAdmin](https://www.phpmyadmin.net/) (among [other services](https://docs.lando.dev/config/services.html)) and simplifies setting up [XDebug](https://xdebug.org/).

>>> Note: Lando is not meant for production!

## Installation Steps

### Install & Configure Lando

Start by installing [Lando](https://docs.lando.dev/basics/installation.html).

Next, create your Lando configuraton file in your application root. Call the file `.lando.yml`. A sample configuration is provided below:

#### Sample Lando Configuration File

```yml
name: userfrosting
recipe: lamp
config:
  webroot: ./public
  php: '7.3'
  database: mariadb
  xdebug: true

services:
  appserver:
    build_as_root:
      - apt-get update -y
      - apt-get install -my wget gnupg
      - a2enmod headers
      # Patch to bring NodeJS into app server container
      # https://docs.lando.dev/guides/installing-node-in-your-lando-php-service.html
      - curl -sL https://deb.nodesource.com/setup_12.x | bash -
      - apt-get install -y nodejs
    overrides:
      environment:
        PHP_IDE_CONFIG: "serverName=userfrosting.lndo.site"
    ssl: true

  # Redis cache
  cache:
    type: redis

  # Add phpMyAdmin database web client
  pma:
    type: phpmyadmin
    hosts:
      - database

proxy:
  pma:
    - pma.userfrosting.lndo.site

tooling:
  phpunit:
    service: appserver
    description: "Run PHP Unit tests"
    cmd: app/vendor/bin/phpunit
  redis-cli:
    service: cache
    description: "Redis cache CLI"
  bakery:
    service: appserver
    description: "UserFrosting CLI"
    cmd: php bakery
```

This configuration file creates a LAMP stack.

If you prefer a LEMP (nginx) stack, change "lamp" to "lemp" in the `recipe` setting near the top of the file and be sure to comment out the `a2enmod headers` line in the build section for the Lando config file.

### Configure UserFrosting

1. Copy `app/sprinkles.example.json` to `app/sprinkles.json`
1. Run `chmod 777 app/{logs,cache,sessions}` to fix file permissions for web server. (NOTE: File
   permissions should be properly secured in a production environment!)

### 1st Time Lando Start-up

Using the terminal, run `lando start` from within your UserFrosting app. The first start up will take a brief period to install everything and set up the docker components.

When the application boots successfully, you'll see something like: 

```

   ___                      __        __        __     __        ______
  / _ )___  ___  __ _  ___ / /  ___ _/ /_____ _/ /__ _/ /_____ _/ / / /
 / _  / _ \/ _ \/  ' \(_-</ _ \/ _ `/  '_/ _ `/ / _ `/  '_/ _ `/_/_/_/ 
/____/\___/\___/_/_/_/___/_//_/\_,_/_/\_\\_,_/_/\_,_/_/\_\\_,_(_|_|_)  
                                                                       

Your app has started up correctly.
Here are some vitals:

 NAME            userfrosting                       
 LOCATION        /home/silicon/source/uf/framework  
 SERVICES        appserver, database, cache, pma    
 APPSERVER URLS  https://localhost:32895            
                 http://localhost:32896             
                 http://userfrosting.lndo.site/     
                 https://userfrosting.lndo.site/    
 PMA URLS        http://localhost:32898             
                 http://pma.userfrosting.lndo.site/
```

### Install UserFrosting

Next, we need to install UserFrosting. 

1. Run `lando composer install` to install UserFrosting's PHP dependencies.
1. Run `lando bakery bake` to run UserFrosting's `bake` command and follow the UserFrosting install steps as described in the previous chapter.

### Database Settings

In your `app/.env` file, use the following constants to get you started. If you changed to a LEMP stack, replace "lamp" with "lemp" below.

```
DB_DRIVER="mysql"
DB_HOST="database"
DB_PORT="3306"
DB_NAME="lamp"
DB_USER="lamp"
DB_PASSWORD="lamp"
```

>>> **Having database connection troubles?**  Get database connection details and more by running `lando info`

### Additional Lando Commands

* You can stop your Lando server by running `lando stop`
* You can start your Lando server again next time by running `lando start`
* Get database connection details and more at `lando info`
* If something went wrong and you want to try again, you can destroy your Lando instance (without erasing your application on your local machine) by running `lando destroy`
* Learn more commands and advanced usage tricks at <https://docs.devwithlando.io>

### Accessing Your Site

Now that you have your application running, you can access it at <http://userfrosting.lndo.site>. Lando supports SSL as well, but if you get SSL certificate errors follow the guidance listed at [Lando Security](https://docs.lando.dev/config/security.html).

Additional tooling and services can be accessed via;

* phpMyAdmin - http://pma.userfrosting.lndo.site
* Bakery CLI - `lando bakery`
* PHPUnit - `lando phpunit` and `lando bakery test`
* Redis CLI - `lando redis-cli`
* ...and those documented at the [Lando LAMP recipe docs](https://docs.lando.dev/config/lamp.html#tooling).

### IDE Integration

>>> If using Lando in WSL 2 via the Docker for Windows WSL 2 backend you may experience difficulties connecting to XDebug.
>>> The cause may be the XDebug port (`9000` by default) not being forwarded to Windows, assuming an XDebug misconfiguration has not already been ruled out.
>>> [microsoft/WSL#4636](https://github.com/microsoft/WSL/issues/4636) is a good place to start looking for potential workarounds and fixes if impacted.

#### PHPStorm

To use PHPStorm's built in xdebug support to enable breakpoints and other useful debug tools, you'll want to add your Lando server to PHPStorm.

1. In PHPStorm, open your preferences (`cmd+x` on Mac)
1. Under "Languages & Frameworks" > "PHP" select "Servers"
1. Add a new server using the plus icon
1. Name the server `userfrosting.lndo.site` 
1. Set the host to `userfrosting.lndo.site`. 
1. Keep/set `80` as the port.
1. Keep/set `Xdebug` as the debugger
1. Check the box to "Use path mappings"
1. Under project files, be sure the application matches your UF install directory. If not, you'll need to open your application in PHPStorm first and repeat the above steps.
1. To the right of your project files, under "absolute path on the server", click into this field and enter `/app` and save your changes
1. You should be done at this point and your server will accept XDebug incoming connections. For additional help, see <https://www.jetbrains.com/help/phpstorm/configuring-xdebug.html>

#### Further Reading

* [Lando XDebug toggle docs](https://docs.lando.dev/config/php.html#toggling-xdebug)
