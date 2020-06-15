---
---
title: Lando
metadata:
  description: Lando is a wrapper around Docker that simplifies the process for PHP applications to run on Docker.
taxonomy:
  category: docs
---

>>> To contribute to this documentation, please submit a pull request to our [learn repository](https://github.com/userfrosting/learn/tree/master/pages).

Lando provides a stable and easy-to-use local development environment. It allows you to integrate PHPMyAdmin and most PHP IDE debuggers hook simply into this as well.

>> Note: Lando is not meant for production!

>> Note: Lando works best with Mac & Linux, but does support Windows as well. Note that if you are using Windows, you must enable Hyper-V. Doing so prevents you from running Vagrant/VMBox. If you are able to run Docker on Windows, you _should_ be able to run Lando on Windows as well.

## Installation Steps

### Install & Configure Lando

Start by installing [Lando](https://docs.lando.dev/basics/installation.html#system-requirements).

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
  cache: redis

services:
  appserver:
    composer:
      phpunit/phpunit: '*'
    build_as_root:
      - apt-get update -y
      - apt-get install -my wget gnupg
      - curl -sL https://deb.nodesource.com/setup_12.x | bash - # install node + (UF4.2.0 requires 10.12+) https://github
      - apt-get install -y nodejs
      - a2enmod headers
    #config:
    #  conf: ./lando-config/php.ini # uncomment this section to enable your custom php.ini file
    overrides:
      environment:
        PHP_IDE_CONFIG: "serverName=userfrosting.lndo.site"
    ssl: true


  # Add a phpmyadmin db frontend
  pma:

    # Use the latest version of phpmyadmin
    type: phpmyadmin

    # The databases you want to look at, this will default to a service called
    # "database"
    #
    # You might want to run `lando info` on your app to see what databases you
    # have available
    hosts:
      - database

# Add in a proxy route to phpmyadmin
proxy:
  pma:
    - pma.userfrosting.lndo.site

tooling:
  phpunit:
    service: appserver
    description: "Run PHP Unit tests: lando phpunit"
  composer:

    # Run the `lando composer` command against the appserver from the services
    # section above
    service: appserver

    # Give a nice description to describe what this does.
    # If ommited this will default to "Run COMMAND commands" where COMMAND is
    # composer in this case
    description: Run composer commands

    # Define a specific command to start with inside the container. If omitted
    # this will default to the name you give the service eg composer.
    #
    # This can be a string or an array if you want to run multiple commands
    # See below for a multi-command example
#    cmd: composer --ansi
  npm:
    service: appserver
  node:
    service: appserver
  gulp:
    service: appserver
  redis-cli:
    service: cache
```

This configuration file creates a LAMP stack. If you prefer a LEMP (nginx) stack, change "lamp" to "lemp" in the `recipe` setting near the top of the file and be sure to comment out the `a2enmod headers` line in the build section for the Lando config file. This will enable `xdebug` for integration with your IDE. If you do not use xdebug, you can set `xdebug` to false in the configuration file to improve performance. However, since this is a local development environment, we strongly encourage you to keep it enabled!

### Configure UserFrosting

1. Copy `app/sprinkles.example.json` to `app/sprinkles.json`
1. Run `chmod 777 app/{logs,cache,sessions}` to fix file permissions for web server. (NOTE: File
   permissions should be properly secured in a production environment!)

### 1st Time Lando Start-up

Using the terminal, run `lando start` from within your UserFrosting app. The first start up will take a brief period to install everything and set up the docker components.

When the application boots successfully, you'll see something like: 

```
BOOMSHAKALAKA!!!

Your app has started up correctly.
Here are some vitals:

 NAME            userfrosting
 LOCATION        /Your/application/location
 SERVICES        appserver, database, pma
 APPSERVER URLS  https://localhost:32841
                 http://localhost:32842
                 http://userfrosting.lndo.site
                 https://userfrosting.lndo.site
 PMA URLS        http://localhost:32844
                 http://userfrosting.phub.lndo.site
                 https://userfrosting.phub.lndo.site
```

### Install UserFrosting

Next, we need to install UserFrosting. 

1. Run `lando composer install --ignore-platform-reqs --no-scripts` to install Lando's PHP dependencies.
1. Run `lando npm install` to install NPM modules
1. **Optional:** If you have an existing database file, you can import that right away using `lando db-import <filename>`. It is important to note that your file must be within the UF app as Lando, like Docker, cannot access files outside of its environment.
1. Run `lando php bakery bake` to run UserFrosting's `bake` command and follow the UserFrosting install steps as described in the previous chapter.

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

Now that you have your application installed, you can access it <http://userfrosting.lndo.site> and access PHPMyAdmin (PMA) at <http://pma.userfrosting.lndo.site>. Lando supports SSL as well, but if you get SSL certificate errors follow the guidance listed at <https://docs.lando.dev/config/security.html>.

### Integrating with PHPStorm IDE 

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
