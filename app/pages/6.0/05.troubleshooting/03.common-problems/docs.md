---
title: Common Problems
description: Commonly encountered issues when setting up, developing, or deploying a UserFrosting project.
obsolete: true
---

## Installation

<!-- ### Route error -->
<!-- TODO -->

### I get a Node/npm error when running `php bakery bake`.

If installation of npm dependencies fails, see [npm](basics/requirements/essential-tools-for-php#npm) to ensure npm is correctly installed and updated. You may need to [change npm permissions](https://docs.npmjs.com/getting-started/fixing-npm-permissions).

### An exception has been thrown during the rendering of a template

When trying to view your site you get an error similar to this :

> An exception has been thrown during the rendering of a template ("The asset 'vendor/font-awesome/css/font-awesome.css' could not be found. Referenced in '/home/vagrant/userfrosting/app/sprinkles/core/asset-bundles.json [css/main]'.").

<!-- TODO : The above error needs to be updated ! -->

This is an indication that asset build failed or you missed a step in the installation process. Try [running the installer](installation/environment/native/install#clone-the-userfrosting-repository) again with `php bakery bake` and check for any error messages.

### Installation went fine, except I don't see any styling on my home page. I am using Apache.

UserFrosting uses a [dynamic routing system](asset-management/basic-usage) for serving assets in a development environment. For this to work on an Apache webserver, `mod_rewrite` needs to be enabled, **and** you need to give Apache permission to use the `.htaccess` file in `public/`.

#### Enabling `mod_rewrite`

If you have shell access to your server, please take the following steps (from [Stack Overflow](http://stackoverflow.com/questions/869092/how-to-enable-mod-rewrite-for-apache-2-2/21658877#21658877)):

- Open up your console and type into it: `sudo a2enmod rewrite`
- Restart your apache server: `sudo service apache2 restart`

If this still does not work, you may need to change the override rule in your Apache configuration to allow Apache to use UserFrosting's `.htaccess` file.

#### Enabling `.htaccess`

1. Locate either your site-specific virtualhost file (preferred) or your master Apache configuration file (typically called `apache2.conf`, `http.conf`, or `000-default.conf`). In XAMPP, for example, this file is located at `XAMPP/etc/httpd.conf`.
2. Locate the line `Directory /var/www/`
3. Change `Override None` to `Override All`
4. Restart Apache.

You may need a server admin to do this if you are using shared hosting.

If you get an error stating rewrite module is not found, then your `userdir` module is probably not enabled. To enable it:

1. Type this into the console: `sudo a2enmod userdir`
2. Enable the rewrite module (per the instructions above).

For more information, see [this blog article](http://seventhsoulmountain.blogspot.com/2014/02/wordpress-permalink-ubuntu-problem-solutions.html).

<!-- ## Sprinkles -->

<!-- ### I get an error like "There is no class mapped" or "class not found" when using the class mapper or running my migrations.

The "There is no class mapped" error occurs when you attempt to use the [dynamic class mapper](advanced/class-mapper) with an identifier that has not been successfully mapped to a class name. If you are sure that you defined the mapping in your Sprinkle's `ServicesProvider` class, it is likely that UserFrosting is simply not even finding your `ServicesProvider` class itself. This is usually due to using an incorrect namespace for your Sprinkle.

It's important to understand that UserFrosting uses a very strict, **case-sensitive** naming convention for Sprinkle namespaces. UserFrosting will convert your Sprinkle's directory name to [studly caps](https://laravel.com/docs/8.x/helpers#method-studly-case) when it builds the fully qualified namespace where it expects your `ServicesProvider` and `Migration` classes to be found. If your Sprinkle's namespace does not match what UserFrosting is expecting, it will not find and load these classes. This will not cause an error directly, but will manifest in other parts of your code that depend on these classes to be located successfully.

Studly caps uses the following code to convert your Sprinkle directory name to an expected namespace:

```php
$value = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $value)));
```

As you can see, all `-` and `_` characters are first converted to spaces. Then, PHP's [`ucwords`](http://php.net/manual/en/function.ucwords.php) function is used to capitalize each word. Finally, the spaces are removed.

Some examples:

| Sprinkle directory | Sprinkle namespace |
| ------------------ | ------------------ |
| `site`             | `Site`             |
| `pokemon-master`   | `PokemonMaster`    |
| `pokemonmaster`    | `Pokemonmaster`    |
| `pokemonMaster`    | `PokemonMaster`    |
| `Pokemonmaster`    | `Pokemonmaster`    |
| `PokemonMaster`    | `PokemonMaster`    |
| `Pokemon-Master`   | `PokemonMaster`    |

We **strongly recommend** using only lowercase words separated with `-` for Sprinkle directory names.

> [!NOTE]
> You may need to re-run Composer if you change your Sprinkle directory path or namespace. On certain operating systems with case-insensitive filesystems, Composer may not update the directory -> namespace mappings correctly. You may need to completely erase your `app/vendor` directory and re-run `composer install` in these cases. -->

## Deployment/Production

### My routes don't seem to work when I switch to `UF_MODE='production'`.

The `production` mode, by default, enables [route caching](https://www.slimframework.com/docs/v4/objects/routing.html#route-expressions-caching). This can result in route definitions not being updated in the cache during production. To resolve this, you should clear the route cache in `app/cache/routes.cache`.
