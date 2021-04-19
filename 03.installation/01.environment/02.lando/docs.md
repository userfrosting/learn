---
title: Lando
metadata:
  description: Lando is a wrapper around Docker that simplifies the process for PHP applications to run on Docker.
taxonomy:
  category: docs
---

[Lando](https://lando.dev/) provides a stable, easy-to-use, and portable local development environment. It allows you to integrate [phpMyAdmin](https://www.phpmyadmin.net/) (among [other services](https://docs.lando.dev/config/services.html)) and simplifies setting up [XDebug](https://xdebug.org/).

[notice=warning]**This is a development tool!** Note that while you can run Lando in production, it is highly discouraged, not recommended and 100% not supported! DON'T DO IT![/notice]

UserFrosting ships with a default landofile in `.lando.dist.yml`, using the dist format. This enables conflict free overrides, which means you can overwrite this in your own project by creating a `.lando.yml` landofile to customized it to your needs. 

But for most people, the default configuration should be fine. With the included landofile, the following is supported;

1. Support for sending and viewing of mail with MailHog (only 1 simple config change needed), with custom URL.
1. Pre-configured PHPMyAdmin, with custom URL.
1. Composer managed by Lando, and exposed via Lando CLI.
1. `bakery` command exposed via Lando CLI.
1. `redis-cli` command exposed via Lando CLI (haven't verified config here, I probably should).
1. `phpunit` command exposed via Lando CLI.
1. User keys and tokens pulled in, avoiding a host of auth frustrations normally associated with container based dev tools

## Installation Steps

### Install Lando

Start by **installing [Lando](https://docs.lando.dev/basics/installation.html)**.

### Clone UserFrosting 

Once you've installed Lando, we can use UserFrosting built-in support for Lando to spin up a container with the appropriate configuration. **In a directory of your choice**, use git to clone the UserFrosting repository into a new directory :

```bash
git clone https://github.com/userfrosting/UserFrosting.git
```

Next, `cd` into your new UserFrosting dir :

```sh
cd UserFrosting
```

### 1st Time Lando Start-up

Using the terminal, run `lando start` from within your UserFrosting app. The first start up will take a brief period to install everything and set up the docker components.

When the application boots successfully, you'll see something like: 

```txt
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

1. Run `lando composer install` to install UserFrosting's PHP dependencies using Composer.
2. Run `lando bakery bake` to run UserFrosting's `bake` command and follow the UserFrosting install steps, including the creation of your first admin user.

### Accessing Your Site

Now that you have your application running, you can access it at <http://userfrosting.lndo.site> or any other url displayed in `APPSERVER URLS` from the previous steps. 

[notice=tip]Lando supports SSL as well, but if you get SSL certificate errors follow the guidance listed at [Lando Security](https://docs.lando.dev/config/security.html).[/notice]

Additional tooling and services can be accessed via;

* phpMyAdmin : <http://pma.userfrosting.lndo.site>
* Bakery CLI : `lando bakery`
* PHPUnit : `lando phpunit` and `lando bakery test`
* Redis CLI : `lando redis-cli`
* ...and those documented at the [Lando LAMP recipe docs](https://docs.lando.dev/config/lamp.html#tooling).

## Additional Lando Commands

* You can stop your Lando server by running `lando stop`
* You can start your Lando server again next time by running `lando start`
* Get database connection details and more at `lando info`
* If something went wrong and you want to try again, you can destroy your Lando instance (without erasing your application on your local machine) by running `lando destroy`
* Learn more commands and advanced usage tricks at <https://docs.devwithlando.io>

<!-- 
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
-->

#### Further Reading

* [Lando XDebug toggle docs](https://docs.lando.dev/config/php.html#toggling-xdebug)
