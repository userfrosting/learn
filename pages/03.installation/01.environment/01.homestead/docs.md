---
title: Homestead
metadata:
    description: Homestead is a virtual environment, managed by Vagrant, that comes with everything you need to run UserFrosting preinstalled.  It's a great way to develop in a Ubuntu environment, if you'll be running your production site in a similar environment.
taxonomy:
    category: docs
---

>>> If this is your first time installing UserFrosting or if you're not already familiar with setting up a local environment, this page will guide you in setting up your first local environment using Homestead. If you already have a local environment (e.g., LAMP or LEMP) and you're already familiar with **composer**, the [Native Installation](/installation/environment/native) guide is for you.

## Why you need a development environment

We get it - you just want to get UserFrosting up and running as quickly as possible.  Don't worry!  We'll get you there.  Actually, using Vagrant and Homestead is the easiest and fastest way to get UserFrosting up and running !

If you're uploading your code to a live site while you're still writing it, this section is for you. If you are already developing in a local environment, please skip this section.

### Web hosting vs. web server

In actuality, "web server" and "web hosting" are not the same thing. A web server is nothing more than a piece of software that runs on a computer and listens for **HTTP requests**.  The most common web servers - Apache and Nginx - are completely free and open-source.  You can download and run them on your personal computer.

When you sign up with a web hosting company, you're not paying for the web server software; you're paying to run a web server on _their computers_ instead of your own.  The reason that you need a web hosting service is **not** because they possess some magical ability to run PHP.  What you're paying for is:

- Hardware maintenance and reliable uptime
- Better network connections, superior bandwidth
- Static IP addresses
- Support services (backup, monitoring, etc)

These are important concerns - and part of the reason that most people don't run their web applications off of a home/office server - but they have nothing to do with the _development_ of your application.

### Using a local server to view and test your code

In the long run, the amount of time you'll spend uploading your code after each change you make will **easily dwarf** the amount of time it takes to set up a working test server on your own computer.  Examples of test server software are Apache and Nginx. If you don't like the idea of setting up a local test server, there's an alternative option that is a little easier and very reliable, called a virtual environment. **Vagrant and Homestead (using them together) is an example of setting up a virtual environment**.

When you run your code in a place that is accessible only to you and where it's ok when something breaks, this is referred to as a **development environment**.  When you upload your code so that it is running live and interacting with real visitors to your site, this is called a **production environment**.

### VirtualBox, Vagrant and Homestead

The easiest way to have a full **development environment** is to use _VirtualBox, Vagrant and Homestead_. Combined, they create a virtual machine (VM) on your computer containing all the necessary softwares to run a web server. All three components have their own purpose. **VirtualBox** is a virtualization manager that lets you run just any operating system as a "guest" inside another operating system (the "host").  **Vagrant** works in tandem with VirtualBox to automatically manage the configuration and installed software inside your virtual machine. Finally, **Homestead** is a VM configuration specifically designed for PHP development.

If you think of VirtualBox as your kitchen, Vagrant is sort of like the cookbook that contains recipes for how to set up a useful development environment on your virtual machine.  The particular recipe that we'll be using is called _Homestead_, and it is has everything we need to easily set up the UserFrosting development environment.

Homestead will automatically give us the following components that we need to run UserFrosting:

- Ubuntu 18.04
- Git
- PHP 7.2
- Nginx (webserver)
- MySQL/MariaDB (database)
- Composer
- Node and npm
- Bower and Gulp

Nice!  This means that we are saved the hassle of [setting these up natively in our operating system](/installation/requirements/essential-tools-for-php).

## Setting up your local development environment

The first thing you'll need to do is install VirtualBox.  Our goal with VirtualBox is to let you run Ubuntu on a "virtual machine" on your computer, not matter which operating system you natively use. To install VirtualBox, simply download and run one of the installers available [on their Downloads page](https://www.virtualbox.org/wiki/Downloads).

The next thing we'll do is set up **Vagrant**. Head over to Vagrant's [downloads page](https://www.vagrantup.com/downloads.html) to grab one of their installers.

### Command-line Life

Before we begin, it's important to understand that we will rely heavily on **command-line operations** and **git**.  If you are natively running on a Linux distribution or MacOS, this is already handled for you with Terminal and a preinstalled copy of git.

However, **if you are a Windows user**, you'll need to install `git` and get set up with a decent command-line program.  Fortunately, [Git for Windows](https://git-scm.com/downloads) takes care of both of these things for you.  Just install it, and you'll have `git` and the `Git Bash` command-line terminal available in your start menu.

### Get Started

Once you've installed VirtualBox and Vagrant, we can use UserFrosting built-in support for Vagrant to spin up a virtual machine with the Homestead configuration. **In a directory of your choice** (I have a generic `dev/` directory on my computer where I keep all of my projects), use git to clone the UserFrosting repository into a new directory :

```bash
git clone https://github.com/userfrosting/UserFrosting.git userfrosting
```

Next, `cd` into your new UserFrosting dir and clone Homestead Git repository :

```sh
cd userfrosting
git clone https://github.com/laravel/homestead.git vagrant/Homestead
```

You will need a SSH key-pair to connect to the Virtual Machine created in the next step. If you already have a keypair in your user's home directory you can skip this step. Otherwise you can generate a new SSH keypair using the `ssh-keygen` tool.  Before doing this, make sure you have a `.ssh` directory in your user's home directory (e.g. `C:/Users/<username>` in Windows, or `/Users/<username>` in Mac/Linux).  If not, you can do `mkdir $HOME/.ssh`.

Then, run the following command:

```sh
ssh-keygen -t rsa -f $HOME/.ssh/id_rsa
```

It will prompt you to create a passphrase. Since this is all for a development environment, we don't need a passphrase - just hit Enter.

Now simply run `vagrant up` from the root of your cloned fork of the UserFrosting Git repository :

```sh
vagrant up
```

When you _vagrant up_, the Laravel/Homestead box is transparently loaded as a Virtual Machine on your computer (this may take several minutes the very first time while it downloads the VM image to your computer). Your local UserFrosting repository clone is mirrored/shared with the VM, so you can work on the UserFrosting code on your computer, and see the changes immediately when you browse to UserFrosting at the URL provided by the VM.

### Check out your first UserFrosting installation!

Ok, that should be it!  If you head over to `http://192.168.10.10/` in your browser, you should see the front page of the default UserFrosting installation.

A default administrator account will also be preconfigured with the following credentials :

* Username: **admin**
* Password: **adminadmin12**


If you encounter any error at this point, look at the previous output for signs of failure. You might need to [connect to the virtual machine](#connecting-to-the-virtual-machine) to run the installer script again. In doubts, the [troubleshooting section](/troubleshooting) might also be of help.

## Next steps

### Editing your hosts file

If you prefer to access UserFrosting from the more friendly URL `http://userfrosting.test` then you must update your computer's hosts file. To do this, we need to edit the `hosts` file.  In Windows, this file is located at `C:\Windows\System32\drivers\etc\hosts`.  In MacOS and Linux, you can find it at `/etc/hosts`.  In either case, you will need to edit it **as an administrator**, or temporarily give yourself permissions to write to this file.

Add the following lines at the bottom, save and exit:

```
# Vagrant projects
192.168.10.10  userfrosting.test
```

### Change your git remote

We highly recommend that you [change your git remote](/installation/environment/native#changing-git-remote) to make it easier to pull future updates to UserFrosting.


### Connecting to the virtual machine

Once your virtual machine is up and running, you'll be able to log into it:

```bash
vagrant ssh
```

>>>> It would appear that Git-Bash functions poorly as an SSH client in Windows.  For Windows users, you may want to use the native "command prompt" application instead.

If it connects successfully, you will see a welcome message for Ubuntu:

```bash
Welcome to Ubuntu 18.04.3 LTS (GNU/Linux 4.15.0-74-generic x86_64)

Thanks for using
 _                               _                 _
| |                             | |               | |
| |__   ___  _ __ ___   ___  ___| |_ ___  __ _  __| |
| '_ \ / _ \| '_ ` _ \ / _ \/ __| __/ _ \/ _` |/ _` |
| | | | (_) | | | | | |  __/\__ \ ||  __/ (_| | (_| |
|_| |_|\___/|_| |_| |_|\___||___/\__\___|\__,_|\__,_|

* Homestead v10.3.0 released
* Settler v9.2.0 released

0 packages can be updated.
0 updates are security updates.
```

If you try the `ls` command, you should see the `userfrosting` directory. You can switch to this directory and run all [bakery commands](/cli/commands) directly in the VM. For example :

```
$ cd userfrosting
$ php bakery bake
```

To log out of the virtual machine, use the `exit` command.

#### Additional Vagrant Commands

To pause your server:

```sh
vagrant suspend
```

To shut down your server:

```sh
vagrant halt
```

To delete and remove your server:

```sh
vagrant destroy
```

>>>> Destroying the vagrant server will remove all traces of the VM from your computer, reclaiming any disk space used by it. However, it also means the next time you vagrant up, you will be creating a brand new VM with a fresh install of UserFrosting and a new database.

### Access phpmyadmin

Your virtual machine provides phpmyadmin to make it easier to interact with the `UserFrosting` database.

First, while logged in into your virtual machine, install phpmyadmin. Accept all default settings:

```
sudo apt-get install phpmyadmin
```

Next, simply add `phpmyadmin.test` to your `hosts` file as well:

```
# Vagrant projects
192.168.10.10  userfrosting.test
192.168.10.10  phpmyadmin.test
```

You should be able to access phpmyadmin in your browser at `http://phpmyadmin.test`. You may see some errors the first time you sign in - these can be ignored.

The default database information should be as follows:

- Type: `MySQL`
- Host: `localhost`
- Port: `3306`
- Database name: `UserFrosting`
- Database user: `homestead`
- Database password: `secret`

### Using PostegreSQL

By default, UserFrosting is pre-configured to install with a MySQL database. You can, however, switch to PostegreSQL or SQLite3 by editing the `app/.env` file in the UserFrosting directory or running the `php bakery setup:db --force` command. The database user and password for PostegreSQL are the same, only the port is different (`54320`).

## Start developing!

Head over to the chapter on [Sprinkles](/sprinkles) to get oriented and find your way around the UserFrosting codebase.  Come see us in [chat](https://chat.userfrosting.com) if you're having trouble.

It will help us a lot if you could star [the UserFrosting project on GitHub](https://github.com/userfrosting/UserFrosting). Just look for the button in the upper right-hand corner!

[![How to star](/images/how-to-star.png)](https://github.com/userfrosting/UserFrosting)

You should also follow us on Twitter for real-time news and updates:

<a class="twitter-follow-button" href="https://twitter.com/userfrosting" data-size="large">Follow @userfrosting</a>
