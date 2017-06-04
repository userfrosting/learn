---
title: Configuration files
metadata:
    description: 
taxonomy:
    category: docs
---

A UserFrosting configuration file is nothing more than a PHP script that returns an associative array.  For example:

```php
<?php

    // default.php

    return [
        'timezone' => 'America/New_York',
        'site' => [
            'title'     =>      'Owl Fancy',
            'author'    =>      'David Attenborough'
        ]
    ];
```

In each Sprinkle, you can specify multiple config files for different environment modes.  UserFrosting will start by loading the array of configuration values from the `default.php` config file in the `core` Sprinkle.

Next, it will check the `UF_MODE` environment variable to see if an environment mode has been set (this can be set either directly in your operating system's environment variables, or in the `/app/.env` file.)  If `UF_MODE` has been set, it will look for a configuration file of the same name in the `core` sprinkle and recursively merge that array into the default configuration array.

For example, if `UF_MODE="development"`, then it will look for a `development.php` configuration file.  The array it returns will be recusively merged into the array from `default.php`, replacing any keys that are the same.  So for example, if I have:

```php
<?php

    // development.php

    return [
        'site' => [
            'title'     =>      'Save the Kakapo',
            'twitter'   =>      '@savethekakapo'    
        ]
    ];
```

The resulting configuration array created by UserFrosting will look like:

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

UserFrosting will repeat these steps for any subsequently loaded Sprinkles, recursively merging in each file.

To summarize, Sprinkle configuration files are loaded using the following algorithm:

1. Load the core `default.php` configuration file;
2. Recursively merge in the core configuration file for the environment mode, if set;
3. Move on to the next Sprinkle;
4. Recursively merge in the `default.php` configuration file from the current Sprinkle;
5. Recursively merge in the configuration file for the environment mode, if set, from the current Sprinkle;
6. Repeat steps 3-5 for all remaining Sprinkles to be loaded.

>>>>>> Use environment variables to easily set the appropriate configuration parameters for different environments.  In addition to setting the `UF_MODE` environment variable to select different configuration files, you can assign sensitive information like database passwords and API keys directly to environment variables, and then reference them in your configuration files using `getenv()`.<br><br>See [the Twelve-Factor App](https://12factor.net/config) for more information on why this is a good idea.
