---
title: Configuration Files
metadata:
    description: Configuration files allow you to customize the default behavior of UserFrosting - for example to toggle debugging, caching, and logging behavior and to set other sitewide settings.
taxonomy:
    category: docs
---

Configuration files allow you to customize the default behavior of UserFrosting - for example to toggle debugging, caching, and logging behavior and to set other sitewide settings.  Configuration files are found in the `config/` directory of Sprinkles.

## File Structure

At runtime, UserFrosting will merge the `default.php` configuration files in each Sprinkle based on the Sprinkle order specified in `sprinkles.json`.

A UserFrosting configuration file is nothing more than a PHP script that returns an associative array.  For example:

```php
<?php

    // mysite/config/default.php

    return [
        'timezone' => 'America/New_York',
        'site' => [
            'title'     =>      'Owl Fancy',
            'author'    =>      'David Attenborough'
        ]
    ];
```

Sprinkle config files are merged according to the rules of [`array_replace_recursive`](http://php.net/manual/en/function.array-replace-recursive.php), inserting any new array keys and replacing any keys that are the same as a previously loaded Sprinkle.  So for example, if I have a `mysite2` Sprinkle with its own `config/default.php` file:

```php
<?php

    // mysite2/config/default.php

    return [
        'site' => [
            'title'     =>      'Save the Kakapo',
            'twitter'   =>      '@savethekakapo'    
        ]
    ];
```

And I load it after the `mysite` Sprinkle, the resulting configuration array created by UserFrosting will look like:

```php
    [
        'timezone' => 'America/New_York',
        'site' => [
            'title'     =>      'Save the Kakapo',
            'author'    =>      'David Attenborough',
            'twitter'   =>      '@savethekakapo'            
        ]
    ]
```

## Environment Modes

You can define separate configuration files for different **environment modes** - for example development, testing, production, etc.  At runtime, UserFrosting will decide which mode to use based on the `UF_MODE` environment variable (this can be set either directly in your operating system's environment variables, or in the `/app/.env` file.)

If `UF_MODE` has been set, it will look for a configuration file of the same name in each Sprinkle and recursively merge that in on top of the Sprinkle's `default.php` before moving on to the next Sprinkle.  For example, if `UF_MODE="development"`, then it will look for a `development.php` configuration file as it merges each Sprinkle.

To summarize, Sprinkle configuration files are loaded using the following algorithm:

1. Start with the `default.php` configuration file in the first loaded Sprinkle (typically `core`);
2. Recursively merge in the first Sprinkle's environment config file for the currently selected environment mode (e.g. `production.php`), if it exists;
3. Move on to the next Sprinkle;
4. Recursively merge in the `default.php` configuration file from the current Sprinkle;
5. Recursively merge in the configuration file for the environment mode, if set, from the current Sprinkle;
6. Repeat steps 3-5 for all remaining Sprinkles to be loaded.

If `UF_MODE` is empty or not set, UserFrosting will only load the `default.php` configuration files in each Sprinkle.

>>>>>> Use environment variables to easily set the appropriate configuration parameters for different environments.  In addition to setting the `UF_MODE` environment variable to select different configuration files, you can assign sensitive information like database passwords and API keys directly to environment variables, and then reference them in your configuration files using `getenv()`.<br><br>See [the Twelve-Factor App](https://12factor.net/config) for more information on why this is a good idea.
