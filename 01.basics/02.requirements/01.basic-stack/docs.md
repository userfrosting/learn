---
title: Basic Stack Requirements
description: UserFrosting requires a web server, PHP 5.5.9 or higher, and some sort of database.
taxonomy:
    category: docs
process:
    twig: true
---

The basic requirements for running UserFrosting are pretty typical of any web framework or CMS.  You'll need:

- Web server software (Apache, Nginx, IIS, etc)
- **PHP 5.5.9** or higher
- Database (MariaDB, MySQL, Postgres, SQLite, or SQL Server)

(By the way, MariaDB is just an open-source fork of MySQL.  The reason it exists is because of [numerous concerns](http://www.computerworld.com.au/article/457551/dead_database_walking_mysql_creator_why_future_belongs_mariadb/) that Oracle would not do a good job honoring the open-source nature of the MySQL community.  For all technical purposes, MariaDB and MySQL are more or less perfectly interoperable).

### Web Server Requirements

#### Apache

If you are using Apache (the default web server that comes installed with XAMPP, WampServer, and most shared web hosting services), check that you have the Rewrite Engine module (`mod_rewrite.c`) installed and enabled.

Some distributions, like WampServer, may not have this module automatically enabled, and you will need to do so manually.

In a shared hosting environment, you may need to have your hosting service do this for you.

##### Enabling `mod_rewrite`

If you have shell access to your server, please take the following steps (from [Stack Overflow](http://stackoverflow.com/questions/869092/how-to-enable-mod-rewrite-for-apache-2-2/21658877#21658877)):

- Open up your console and type into it: `sudo a2enmod rewrite`
- Restart your apache server: `sudo service apache2 restart`

If this still does not work, you may need to change the override rule in your Apache configuration to allow Apache to use UserFrosting's `.htaccess` file.

1. Locate either your site-specific virtualhost file (preferred) or your master Apache configuration file (typically called `apache2.conf`, `http.conf`, or `000-default.conf`).  In XAMPP, for example, this file is located at `XAMPP/etc/httpd.conf`.
2. Locate the line `Directory /var/www/`
3. Change `Override None` to `Override All`

Again, you may need a server admin to do this if you are using shared hosting.

If you get an error stating rewrite module is not found, then probably your `userdir` module is not enabled. For this reason you need to enable it.

1. Type this into the console: `sudo a2enmod userdir`
2. Enable the rewrite module (per the instructions above).

For more information, see http://seventhsoulmountain.blogspot.com/2014/02/wordpress-permalink-ubuntu-problem-solutions.html.

### PHP Requirements

UserFrosting requires the following PHP modules to be installed and enabled:

#### GD

Occasionally, people use web hosting services that do not provide the GD library, or provide it but do not have it enabled.  The GD library is an image processing module for PHP.  UserFrosting uses it to generate the captcha code for new account registration.

##### Windows

If you are having trouble with `gd` in Windows, you should first check your `php.ini` file to make sure it is enabled.  You'll include the GD2 DLL `php_gd2.dll` as an extension in `php.ini`.  See http://php.net/manual/en/image.installation.php.

##### Ubuntu/Debian

In Ubuntu/Debian, you can install GD as a separate module:

```
sudo apt-get install php5-gd
sudo service apache2 restart
```

##### OSX

For OSX users (Yosemite and Capitan), you might have GD installed but `imagepng` isn't available.  In this case, you need to upgrade the default version of GD that ships with these versions of OSX.  See [this answer on Stack Overflow](http://stackoverflow.com/a/26505558/2970321) for a complete guide.

### File System Permissions

UserFrosting needs to be able to write to the file system for a few directories:

- `/app/cache` - This is where UF will cache rendered Twig templates for faster processing, as well as other objects;
- `/app/logs` - UF writes error, debugging, and mail logs to this directory;
- `/app/sessions` - If you're using file-based sessions, UF writes to this directory instead of PHP's default session directory.

You should make sure that the group under which your webserver runs (for example, `www-data`, `apache`, `_www`, `nobody`) has read and write permissions for these directories.  You may need to use `chgrp` to ensure that these directories are owned by the webserver's group.

To determine the user under which, for example, Apache runs, try this command:

`ps aux | egrep '(apache|httpd)'`

Once you know the user, you can determine the group(s) to which the web server user belongs by using the `groups` command:

`groups <username>`

For all other directories, you should make sure that they are *not* writable by the webserver.  We also recommend keeping the `/app` directory out of your web server's document root entirely, to prevent it from inadvertently serving any files in that directory.  Only the contents of `/public` need to be in the document root.

### Other software (local development environment only)

During development, and before you're ready to deploy, you'll also want to have the following tools installed:

- [Composer](https://getcomposer.org) - PHP package manager
- [Node.js](https://nodejs.org/en/) - Javascript runtime environment and package manager

See the [next section](/basics/requirements/essential-tools-for-php) for more information on these tools.

## But my host only supports PHP 5.x!  Why do I need PHP 5.5.9+?

Look, programming languages evolve, and PHP is no exception.  Actually, PHP (and other web languages) have it particularly tough because they have so many responsibilities.  PHP is the bouncer at the door and it has to be prepared to defend against the constantly evolving security threats to your server.  At the same time it has to keep up with the demand for faster performance, and satisfy the demand for new features from the [enormous](https://w3techs.com/technologies/overview/programming_language/all) PHP community.

Honestly, PHP 5.5 isn't exactly cutting edge - in fact, it's been considered "end of life" since [July 2016](http://php.net/eol.php).  And the truth is, we didn't make this decision directly.  UserFrosting depends on a lot of third-party components, and *those* components require a minimum version of 5.5.9.  Thus, UF does too, and the whole community moves forward.

If your hosting service doesn't have PHP 5.5.9 or higher installed, call them and ask them to upgrade.  If they refuse, point out that even PHP 5.5 has been end-of-life for {{ date("now").diff(date("2016-07-21")).m }} months!  To be honest, there is little reason to use a shared hosting (e.g. cPanel)
service these days, especially when VPS providers like DigitalOcean and Amazon EC2 are so inexpensive.  Unless you're stuck with shared hosting for some reason another (fussy boss), [there's no real reason not to switch to a VPS](https://www.hostt.com/still-use-shared-hosting-theres-vps/).

As for your local development environment ([You _do_ have a local development environment, right](/basics/requirements/develop-locally-serve-globally)), if it's that much of a burden then...I don't know what to tell you.  So what are you waiting for?  Upgrade!

## Third-party components?  Why don't you write all your own code?

I think that for a lot of developers - novices and professionals alike - building on top of others' work can seem like a betrayal of our trade.  We're not "real" developers unless we built everything with our bare hands from scratch, and know firsthand the nitty-gritty details of how our code works.  With third-party components, we have to take time to actually *learn* how to use them, and follow *their* rules.  I get it.  It all feels so antithetical to the DIY spirit that got so many of us into coding in the first place.  Trust me, as someone who built a cold frame out of some doors and framing I found in the dumpster, I know:

![DIY cold frame](/images/cold-frame.jpg?resize=500)

However unlike me with with my cold frame, software developers aren't limited by the contents of their local dumpster.  With the advent of Composer, the PHP community abounds with free, high-quality third-party packages for pretty much every task imaginable.  The trick is to know *which* packages to use, and to avoid getting overwhelmed.

Using third-party components solves a couple of problems.  First and most obviously, we save time it would have taken write the code ourselves.  However for a lot of people, this benefit alone is canceled out by the extra effort involved in learning how to use a particular software package.  Fair enough.  But consider the additional benefits:

### Software maintenance.

It's [well-established](http://www.eng.auburn.edu/~kchang/comp6710/readings/Forgotten_Fundamentals_IEEE_Software_May_2001.pdf) that on average, 60% of time and money spent on a software project goes into maintenance.   Chances are, you won't be the only one using a given package, and this means more opportunities for the community to spot and fix bugs.  Even if you don't care a whit about contributing to open source projects, other people do and you stand to gain tremendously from their efforts.  You're effectively offloading a huge amount of work in debugging and improvement to the communities surrounding those packages.

### Documentation.

In a few months or years, you (and perhaps other people) will have to read the code that you wrote today.  And as we all know, writing code is easy, but reading code is [very, very hard](https://blog.codinghorror.com/when-understanding-means-rewriting/).  Heck, sometimes I struggle to understand code that I wrote just a few months prior!  High-quality packages are already documented for us.  We can build an application using package X, put it down for a few months, and get back to work without having to dig through our code to figure out "how does feature A work?"  A decent software package is already thoroughly documented!

### Community.

Components that are sufficiently popular will likely have an active community in chat rooms, discussion forums, IRC, and Q&A sites.  As much fun as it is to solve everything ourselves, sometimes you really just need to ask for help.  Hopefully as you learn more about a particular package, you will also start to help others.  And as we all know, the best way to master a subject is to teach it to someone else.

Ok, so maybe now you're thinking "but what if I end up using a package that's missing a feature that I realize that I need later?"  That's where the beauty of the open-source community and the social coding movement come in.  You can always make your own copy of a package and modify it to suit your needs (this is Github's "fork" feature).

Of course at this point, the package is no longer a black box.  You'll have to read through someone else's code in order to be able to modify it.  But keep in mind that reading your *own* code from a few months prior can be just as difficult as reading someone else's - perhaps even moreso if their code is carefully documented and yours isn't.  And of course, if you're the type that likes to give back, you can offer to merge your improvements into the main project repository (this is Github's "pull request" feature).

Hopefully I've convinced you by now that there's no real reason not to stand on the shoulders of others whenever possible.

It is, of course, important to pick the *right* packages.  You want to choose packages that are well-maintained by an active community.  This doesn't necessarily mean a large community - often a small but highly active community will be more productive than a large community that is swamped by feature requests, poor management, and more takers than givers.

In building UserFrosting, we have tried to collect what we believe are the best packages that the PHP community has to offer that are needed to build a basic web application.  For functionality beyond the groundwork we've laid, your best bet is to carefully research your options before committing to a specific package.
