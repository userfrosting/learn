---
title: Essential Tools for Modern PHP
metadata:
    description: A minimal set of tools that every PHP developer should have installed in their development environment.
taxonomy:
    category: docs
---

## Composer

Up until March of 2012, PHP didn't really have a good project-level package manager.  There was PEAR, but it [failed to keep up with the evolution of the PHP community](https://benramsey.com/blog/2013/11/the-fall-of-pear-and-the-rise-of-composer/).  In March of 2012, on the heels of the [PHP Standard Recommendations (PSR)](http://www.php-fig.org/psr/) project, Composer was released and a new era of PHP began.

If you've been out of the PHP world for a while, you might have missed this critical shift.  Over the past few years, Composer has risen to become the *de facto* package manager for PHP, with [Packagist](https://packagist.org/) as its main public package repository.  This means that the best way to incorporate third-party code (which you [definitely should do](/basics/requirements/basic-stack#third-party-components-why-dont-you-write-all-your-own-code)) is by installing and using Composer - at the very least, in your development environment.

Composer also handles autoloading, which means that the days of needing long blocks of `include` or `require` statements in your code are over.  It fully implements the [PSR-4 standard](http://www.php-fig.org/psr/psr-4/) for autoloading, which further helps the PHP community develop a consistent approach to releasing and consuming packages.

The full instructions for installing Composer can be found at their [website](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx).  We **strongly recommend that you install Composer globally** on your system.  This will let you run Composer using the `composer` command.  For convenience, we recap the global installation instructions here:

### OSX and *nix

1. Download and run the installer as per the instructions on the [downloads page](https://getcomposer.org/download/).
2. Run `mv composer.phar /usr/local/bin/composer` to make `composer` available as a shell command.

>>>>>> You may need to run the above command(s) with `sudo`.<br><br>On some versions of OSX the `/usr` directory does not exist by default. If you receive the error "/usr/local/bin/composer: No such file or directory" then you must create the directory manually before proceeding: `mkdir -p /usr/local/bin`.

### Windows

Composer has a special installer that you can use for Windows - [Composer-Setup.exe](https://getcomposer.org/Composer-Setup.exe).  If this gives you trouble, you can try the [manual installation instructions](https://getcomposer.org/doc/00-intro.md#manual-installation).

## Git

Coming soon!

## PHPUnit

Coming soon!

## Node.js

Coming soon!

## Coding and style standards

Standards are boring.  But guess what?  "Boring" == "predictable" - a good thing when it comes to writing code.  Most of your time spent as a developer is actually spent [reading, not writing](https://blog.codinghorror.com/when-understanding-means-rewriting/) code.  Without a clean set of standards and good comments, it can be difficult to read your own code 3 months after you wrote it, let alone someone else's code.

The increasingly modular and community-driven nature of software development means that it is important not just to have internal coding standards, but community-wide standards as well.  We **strongly recommend** that you familiarize yourself with some basic standards for PHP, Javascript, and CSS:

- **PHP**: [PHP-FIG](http://www.php-fig.org/)
- **HTML and CSS**: [Code Guide by @mdo](http://codeguide.co)
- **Javascript**: The Javascript community (which, let's face it, is HUGE) has so far failed to adopt a single set of coding standards.  Over the past few years, [Airbnb's coding guidelines](https://github.com/airbnb/javascript) have started to gain a lot of traction.  It is extremely thorough and widely used, and for these reasons we recommend it.

## Comments and API documentation generators

Sooner or later, you may want to generate some low-level documentation for every class and function in your application.  **Don't try to do this by hand!**

Instead, by following a specific set of standards for in-code comments ("doc blocks"), you can use automated tools that will scan your codebase and generate the appropriate documentation.

### PHP

For PHP code, use the [phpDoc standard](https://phpdoc.org/docs/latest/guides/docblocks.html) to document your classes, methods, and member variables.  You can then use [ApiGen](http://www.apigen.org/) to automatically generate clean, attractive, fully searchable API documentation for your application.

### Javascript

For Javascript, use the [JSDoc](http://usejsdoc.org/about-getting-started.html) standard and tool to comment your code and generate an attractive set of API documentation.  It is consistent with Airbnb's commenting standards.
