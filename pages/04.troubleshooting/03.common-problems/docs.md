---
title: Common Problems
metadata:
    description: Commonly encountered issues when setting up, developing, or deploying a UserFrosting project.
taxonomy:
    category: docs
---

>>> To contribute to this documentation, please submit a pull request to our [learn repository](https://github.com/userfrosting/learn/tree/master/pages).

## Installation

### I get a Node/npm error when running `php bakery bake`.

If installation of npm dependencies fails, see [npm](/basics/requirements/essential-tools-for-php#npm) to ensure npm is correctly installed and updated. You may need to [change npm permissions](https://docs.npmjs.com/getting-started/fixing-npm-permissions).

### Installation went fine, except I don't see any styling on my home page.  I am using Apache.

UserFrosting uses a [dynamic routing system](/asset-management/basic-usage#PublicassetURLs) for serving assets in a development environment.  For this to work on an Apache webserver, `mod_rewrite` needs to be enabled, **and** you need to give Apache permission to use the `.htaccess` file in `public/`.

#### Enabling `mod_rewrite`

If you have shell access to your server, please take the following steps (from [Stack Overflow](http://stackoverflow.com/questions/869092/how-to-enable-mod-rewrite-for-apache-2-2/21658877#21658877)):

- Open up your console and type into it: `sudo a2enmod rewrite`
- Restart your apache server: `sudo service apache2 restart`

If this still does not work, you may need to change the override rule in your Apache configuration to allow Apache to use UserFrosting's `.htaccess` file.

#### Enabling `.htaccess`

1. Locate either your site-specific virtualhost file (preferred) or your master Apache configuration file (typically called `apache2.conf`, `http.conf`, or `000-default.conf`).  In XAMPP, for example, this file is located at `XAMPP/etc/httpd.conf`.
2. Locate the line `Directory /var/www/`
3. Change `Override None` to `Override All`
4. Restart Apache.

You may need a server admin to do this if you are using shared hosting.

If you get an error stating rewrite module is not found, then your `userdir` module is probably not enabled. To enable it:

1. Type this into the console: `sudo a2enmod userdir`
2. Enable the rewrite module (per the instructions above).

For more information, see [this blog article](http://seventhsoulmountain.blogspot.com/2014/02/wordpress-permalink-ubuntu-problem-solutions.html).

## Deployment/Production

### My routes don't seem to work when I switch to `UF_MODE='production'`.

The `production` mode, by default, enables [FastRoute's route caching](https://www.slimframework.com/docs/objects/application.html#slim-default-settings).  This can result in route definitions not being updated in the cache during production.  To resolve this, you should clear the route cache in `app/cache/routes.cache`.
