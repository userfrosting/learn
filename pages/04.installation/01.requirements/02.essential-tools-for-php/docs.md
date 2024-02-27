---
title: Essential Tools for Modern PHP
metadata:
    description: A minimal set of tools that every PHP developer should have installed in their development environment.
taxonomy:
    category: docs
---

On the previous page, we saw the softwares required to run UserFrosting. Now it's time to look at tools you'll need during development to build your UserFrosting application. These tools are not strictly required to be installed on your production server, which we'll cover in a [later chapter](/going-live).

During development, and before you're ready to deploy, you'll want to have the following tools installed:

1. [The Command Line](#the-command-line-cli)
2. [Git](#git)
3. [Composer 2](#composer-2)
4. [Node.js](#nodejs)
5. [npm](#npm)
6. [A code editor](#code-editor)

[notice=note]If you're using or plan on using Docker, most of these tools will be provided for you. However, it's important to understand what they are, since you'll need to interact with them.[/notice]

## The Command Line (CLI)

The command line interface, or CLI, is a [program that accepts text input to execute operating system functions](https://www.w3schools.com/whatis/whatis_cli.asp). If you're using MacOS or Linux, you might already be familiar with a CLI (or Terminal), as it's been built-in to your operating system for decades. If you're using Windows, you may recognize Command or Powershell, but chances are you never used a CLI.

But no worries! Regardless of the dev environment chosen, the next pages will guide you how to open or install the appropriate CLI.

## Git

Git is one of the most popular [version control systems](https://en.wikipedia.org/wiki/Version_control), originally created for aiding development of the Linux kernel. To start working with UserFrosting, you won't need to use **git** right away. However, Git is an important part of UserFrosting for many reasons:

1. Many of the [asset management tools](#npm) that UserFrosting depends on use git
2. It makes it easier for you and your team to keep track of changes in your code, and allows your team to work simultaneously on different features
3. It makes it easy to deploy and update code on your production server (if you're using a VPS or dedicated hosting)

**Git is not the same as GitHub!** GitHub is a "social coding" company, while git is the open-source software around which GitHub was built. Many open source projects choose to use GitHub to host their git repositories, because GitHub offers free hosting for public repositories. However, you should be aware that there are other companies that also offer free git hosting, such as Atlassian (Bitbucket). Both also offer free _private_ repositories. You can also [set up your own server to host repositories](http://stackoverflow.com/a/5507556/2970321). Third-party packages such as Gitlab also have GitHub/Bitbucket-like features such as issue tracking, code review, etc.

## Composer 2

Up until March of 2012, PHP didn't really have a good project-level package manager. There was PEAR, but it [failed to keep up with the evolution of the PHP community](https://benramsey.com/blog/2013/11/the-fall-of-pear-and-the-rise-of-composer/). In March of 2012, on the heels of the [PHP Standard Recommendations (PSR)](http://www.php-fig.org/psr/) project, [Composer](https://getcomposer.org) was released and a new era of PHP began.

If you've been out of the PHP world for a while, you might have missed this critical shift. Over the past few years, Composer has risen to become the *de facto* package manager for PHP, with [Packagist](https://packagist.org/) as its main public package repository. This means that the best way to incorporate third-party code (which you [definitely should do](/background/dont-reinvent-the-wheel)) is by installing and using Composer - at the very least, in your development environment.

Composer also handles autoloading, which means that the days of needing long blocks of `include` or `require` statements in your code are over. It fully implements the [PSR-4 standard](http://www.php-fig.org/psr/psr-4/) for autoloading, which further helps the PHP community develop a consistent approach to releasing and consuming packages.

[notice=note]Following its release in October 2020, UserFrosting 5 now requires [**Composer 2**](https://getcomposer.org).[/notice]

## Node.js

**[Node.js](https://nodejs.org/en/)** is an an extremely popular JavaScript runtime built on Chrome's V8 JavaScript Engine. In recent years it has become extremely popular for creating multiplatform applications, and for its role in providing a means to run platform independent build tools like `gulp` and `grunt` (to name just a few). Node.js also includes `npm` (Node.js Package Manager).

Although UserFrosting does not _run_ on Node.js, it does use several Node-based tools to fetch client-side Javascript and CSS dependencies, as well as perform critical build tasks.

[notice]Even though we'll be using these tools to get our application ready for deployment, you don't need to install Node.js on your live server. You can install it locally, perform your installation and build tasks, and then push the built application to the live server afterwards.[/notice]

[notice=note]UserFrosting 5 requires **Node 18** or above.[/notice]

## npm

[npm](https://www.npmjs.com) stands for **N**ode **P**ackage **M**anager. npm is to Node.js what Composer is to PHP. It is used to grab the various Node packages that are required by UserFrosting's installation and build tools. 

[notice=note]UserFrosting 5 requires **NPM 9** or above.[/notice]

## Code Editor

The final tool you'll need to develop your own application or website using UserFrosting is a good *Code Editor*, or IDE. While it's still possible to write your code in *Notepad*, a code editor will provide advanced features to make it easier for you to write, understand, and debug your code.

The most popular editor to write PHP in today, and the one recommended by the UserFrosting team, is [Visual Studio Code, or **VSCode**](https://code.visualstudio.com). VSCode was first released by Microsoft in 2015 and is partly Open Source. It's available for Windows, Linux, MacOS, and even the web! It supports pretty much every language you'll need, and its extension system is really awesome, backed by a very large marketplace. Be sure to check out [PHP Intelephense](https://marketplace.visualstudio.com/items?itemName=bmewburn.vscode-intelephense-client)!

Other popular IDE includes :
- [PHPStorm](https://www.jetbrains.com/phpstorm/) (Paid)
- [Eclipse](https://projects.eclipse.org/projects/tools.pdt)
- [Komodo](https://www.activestate.com/products/komodo-ide/)
- Etc.

### (Optional) Database GUI

In the past, PhpMyAdmin was the *de facto* tool used to view and edit databases content. It still "does the job", but can be hard to setup (unless you do through Docker!) and proper GUI desktop apps are much nicer to use for most database stuff. Desktop app can be useful to experiment with queries in a nice editor, can display your tables structure visually, allow you to save your query scripts to files, etc.

Popular database GUI app includes :
- [Table Plus](https://tableplus.com) - A free perpetual trial is available
- [MySQL Workbench](https://www.mysql.com/products/workbench/)
- [DBeaver](https://dbeaver.io)

### Other optional tools

These tools are optional, but can be useful for any serious developer:

- [PHP Intelephense](https://intelephense.com) - plugin for most editors that provides an essential set of code intelligence features for a productive and rich PHP development experience.
- [PHP-CS-Fixer](https://github.com/PHP-CS-Fixer/PHP-CS-Fixer) - fixes your code to follow standards
- [PHPStan](https://phpstan.org) - scans your whole codebase and looks for both obvious & tricky bugs
- [XDebug](https://xdebug.org) - an extension for PHP, and provides a range of features to improve the PHP development experience, like step debugging
- [Postman](https://www.postman.com) - API platform useful to build and debug REST API;
- [GitHub Copilot](https://github.com/features/copilot) - AI powered autocompletion for VSCode
