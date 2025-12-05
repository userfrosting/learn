---
title: Configuration Files
metadata:
    description: Configuration files allow you to customize the default behavior of UserFrosting - for example, to toggle debugging, caching, and logging behaviors and to set other sitewide settings.
taxonomy:
    category: docs
---

Configuration files allow you to customize the default behavior of UserFrosting - for example, to toggle debugging, caching, and logging behaviors and to set other sitewide settings. Configuration files are found in the `config/` directory of each Sprinkle.

## File Structure

At runtime, UserFrosting will merge the `default.php` configuration files in each Sprinkle based on the Sprinkle order specified in your Sprinkle Recipe and the dependency tree.

A UserFrosting configuration file is nothing more than a PHP script that returns an associative array. For example:

```php
<?php

    // mysite/config/default.php

    return [
        'site' => [
            'title'     =>      'Owl Fancy',
            'author'    =>      'David Attenborough'
        ],
        'php' => [
            'timezone' => 'America/New_York'
        ]
    ];
```

Sprinkle config files are merged according to the rules of [`array_replace_recursive`](http://php.net/manual/en/function.array-replace-recursive.php), inserting any new array keys and replacing any keys that are the same as a previously loaded Sprinkle. So for example, if I have a `mysite2` Sprinkle with its own `config/default.php` file:

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

You can define separate configuration files for different **environment modes** - for example development, testing, production, etc. At runtime, UserFrosting will decide which mode to use based on the `UF_MODE` environment variable (this can be set either directly in your operating system's environment variables, or in the `/app/.env` file.)

If `UF_MODE` has been set, it will look for a configuration file of the same name in each Sprinkle and recursively merge that in on top of the Sprinkle's `default.php` before moving on to the next Sprinkle. For example, if `UF_MODE="development"`, then it will look for a `development.php` configuration file as it merges each Sprinkle.

To summarize, Sprinkle configuration files are loaded using the following algorithm:

1. Start with the `default.php` configuration file in the first loaded Sprinkle (typically `core`);
2. Recursively merge in the first Sprinkle's environment config file for the currently selected environment mode (e.g. `production.php`), if it exists;
3. Move on to the next Sprinkle;
4. Recursively merge in the `default.php` configuration file from the current Sprinkle;
5. Recursively merge in the configuration file for the environment mode, if set, from the current Sprinkle;
6. Repeat steps 3-5 for all remaining Sprinkles to be loaded.

If `UF_MODE` is empty or not set, UserFrosting will only load the `default.php` configuration files in each Sprinkle.

> [!TIP]
> Use environment variables to easily set the appropriate configuration parameters for different environments. In addition to setting the `UF_MODE` environment variable to select different configuration files, you can assign sensitive information like database passwords and API keys directly to environment variables, and then reference them in your configuration files using `env()`.
> See [the Twelve-Factor App](https://12factor.net/config) for more information on why this is a good idea.

The default environment mode includes:

|    Mode    | Description                                                                   |
| :--------: | ----------------------------------------------------------------------------- |
|  default   | The default mode. Should be used only for development.                        |
| production | Serve optimized assets and error management for the front user facing application |
|   debug    | Enables all debugging options                                                 |
|  testing   | Mode used for [automated testing](/testing)                                   |

> [!TIP]
> The Bakery command `php bakery setup:env` can be used to switch from one environment to the other.

## Accessing Config Values

To access values from the final, merged configuration array during runtime, use the `config` service. Subkeys can be accessed using [array dot notation](https://medium.com/@assertchris/dot-notation-3fd3e42edc61). For example, if your configuration array ends up looking like:

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

then you can access the `twitter` value in a controller using `$config->get('site.twitter')`. Of course, you may place additional custom configuration values/subarrays in your Sprinkle's configuration files and they will get merged into the final configuration array as well.

### In Twig Templates

Any configuration values under the `site` subarray are automatically passed to Twig as the `site` global variable. This means you can easily access them in Twig:

```twig
<a href="https://twitter.com/{{site.twitter}}">Follow me on Twitter!</a>
```

Alternatively, the [config function](/templating-with-twig/filters-and-functions#config) can be used to access any configuration values:

```twig
<a href="https://twitter.com/{{ config('site.twitter') }}">Follow me on Twitter!</a>
```
