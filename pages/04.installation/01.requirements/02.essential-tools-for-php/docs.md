---
title: Essential Tools for Modern PHP
metadata:
    description: A minimal set of tools that every PHP developer should have installed in their development environment.
taxonomy:
    category: docs
---
<!-- TODO : Might be useful to move to "Background" chapter? Or split in two, make only installation here? -->

## Git

Git is one of the most popular [version control systems](https://en.wikipedia.org/wiki/Version_control), originally created for aiding development of the Linux kernel.

To start working with UserFrosting, you will need to use **git**. Git is important to use with UserFrosting for four reasons:

1. Many of the [asset management tools](#npm) that UserFrosting depends on, use git;
2. It makes it easier to merge updates in UserFrosting into your project;
3. It makes it easier for you and your team to keep track of changes in your code, and allows your team to work simultaneously on different features;
4. It makes it easy to deploy and update your code on your production server (if you're using a VPS or dedicated hosting).

**git is not the same as GitHub!** GitHub is a "social coding" company, while git is the open-source software around which GitHub was built. Many open source projects choose to use GitHub to host their git repositories, because GitHub offers free hosting for public repositories. However, you should be aware that there are other companies that offer free git hosting such as Atlassian (Bitbucket). Both also offers free _private_ repositories. You can also [set up your own server to host repositories](http://stackoverflow.com/a/5507556/2970321), or use a third-party package such as Gitlab, which has GitHub/Bitbucket-like features such as issue tracking, code review, etc.

### Installing git (MacOS)

By default, MacOS and other *nix operating systems should come with git preinstalled. If you would like to update your version of git, you can do so with their [installer](https://git-scm.com/download/mac).

### Installing git (Windows)

Git has an installer that you can use for Windows - [Git Download](https://git-scm.com/download/win).

## Composer

Up until March of 2012, PHP didn't really have a good project-level package manager. There was PEAR, but it [failed to keep up with the evolution of the PHP community](https://benramsey.com/blog/2013/11/the-fall-of-pear-and-the-rise-of-composer/). In March of 2012, on the heels of the [PHP Standard Recommendations (PSR)](http://www.php-fig.org/psr/) project, Composer was released and a new era of PHP began.

If you've been out of the PHP world for a while, you might have missed this critical shift. Over the past few years, Composer has risen to become the *de facto* package manager for PHP, with [Packagist](https://packagist.org/) as its main public package repository. This means that the best way to incorporate third-party code (which you [definitely should do](/background/dont-reinvent-the-wheel)) is by installing and using Composer - at the very least, in your development environment.

Composer also handles autoloading, which means that the days of needing long blocks of `include` or `require` statements in your code are over. It fully implements the [PSR-4 standard](http://www.php-fig.org/psr/psr-4/) for autoloading, which further helps the PHP community develop a consistent approach to releasing and consuming packages.

To check if Composer is already installed:

```bash
$ composer --version
Composer version 2.5.4 2023-02-15 13:10:06
```

The full instructions for installing Composer can be found at their [website](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-osx). We **strongly recommend that you install Composer globally** on your system. This will let you run Composer using the `composer` command. <!--For convenience, we recap the global installation instructions here:-->

<!-- Removing this part. Best to not _reinvent the wheel_ -->
<!-- ### Installing Composer (MacOS and *nix)

1. Download and run the installer as per the instructions on the [downloads page](https://getcomposer.org/download/).
2. Run `mv composer.phar /usr/local/bin/composer` to make `composer` available as a shell command.

[notice=tip]You may need to run the above command(s) with `sudo`.

On some versions of MacOS the `/usr` directory does not exist by default. If you receive the error "/usr/local/bin/composer: No such file or directory" then you must create the directory manually before proceeding: `mkdir -p /usr/local/bin`.[/notice]

### Installing Composer (Windows)

Composer has a special installer that you can use for Windows - [Composer-Setup.exe](https://getcomposer.org/Composer-Setup.exe). If this gives you trouble, you can try the [manual installation instructions](https://getcomposer.org/doc/00-intro.md#manual-installation). -->

## Node.js

**Node.js** is an an extremely popular JavaScript runtime built on Chrome's V8 JavaScript Engine. In recent years it has become extremely popular for creating multiplatform applications, and for its role in providing a means to run the platform independent build tools, like `gulp` and `grunt`, to name just a few. Node.js also includes `npm` (Node.js Package Manager).

Although UserFrosting does not _run_ on Node.js, it does use several Node-based tools to fetch client-side Javascript and CSS dependencies, as well as perform critical build tasks.

The [Node.js website](https://nodejs.org/en/) provides easy-to-use installers for most operating systems. We recommend using the latest version of Node.js (18.16 LTS as of May 2023).

To check if Node.js is installed:

```bash
$ node -v
v18.17.1
```

[notice]Even though we'll be using these tools to get our application ready for deployment, you don't actually need to install Node.js on your live server. You can install it locally, perform your installation and build tasks, and then push the built application to the live server afterwards.[/notice]

### Installing Node.js (MacOS and Windows)

Node.js has an installer that you can use for MacOS and Windows - [Node.js Download](https://nodejs.org/en/download/current/).

## npm

npm stands for **N**ode **P**ackage **M**anager. npm is to Node.js as Composer is to PHP - it is used to grab the various Node packages that are required by UserFrosting's installation and build tools. When you installed Node, it should have automatically installed npm as well. However, we still recommend updating npm (if unable to update, any version later than 7 should work):

```bash
$ npm install npm@latest -g
```

UserFrosting build scripts will automatically install all other Node and NPM dependencies for you !

## Coding and style standards

Standards are boring. But guess what? "Boring" == "predictable" - a good thing when it comes to writing code. Most of your time spent as a developer is actually spent [reading, not writing](https://blog.codinghorror.com/when-understanding-means-rewriting/) code. Without a clean set of standards and good comments, it can be difficult to read your own code 3 months after you wrote it, let alone someone else's code.

The increasingly modular and community-driven nature of software development means that it is important not just to have internal coding standards, but community-wide standards as well. We **strongly recommend** that you familiarize yourself with some basic standards for PHP, Javascript, and CSS:

- **PHP**: [PHP-FIG](http://www.php-fig.org/)
- **HTML and CSS**: [Code Guide by @mdo](http://codeguide.co)
- **Javascript**: The Javascript community (which, let's face it, is HUGE) has so far failed to adopt a single set of coding standards. Over the past few years, [Airbnb's coding guidelines](https://github.com/airbnb/javascript) have started to gain a lot of traction. It is extremely thorough and widely used, and for these reasons we recommend it. <!-- Is this still true? -->

## Comments and API documentation generators

Sooner or later, you may want to generate some low-level documentation for every class and function in your application. **Don't try to do this by hand!**

Instead, by following a specific set of standards for in-code comments ("doc blocks"), you can use automated tools that will scan your codebase and generate the appropriate documentation.

### PHP

For PHP code, use the [phpDoc standard](https://docs.phpdoc.org/3.0/guide/getting-started/what-is-a-docblock.html#what-is-a-docblock) to document your classes, methods, and member variables. You can then use [phpDocumentor](https://docs.phpdoc.org/guide/getting-started/installing.html) to automatically generate clean, attractive, fully searchable API documentation for your application.

### Javascript

For Javascript, use the [JSDoc](https://jsdoc.app/about-getting-started.html) standard and tool to comment your code and generate an attractive set of API documentation. <!-- It is consistent with Airbnb's commenting standards.  --> <!-- Is this still true? -->
