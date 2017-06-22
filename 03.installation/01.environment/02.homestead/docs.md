---
title: Homestead
metadata:
    description: 
taxonomy:
    category: docs
---

>>> If this is your first time installing UserFrosting or if you're not already familiar with setting up a local environment, this page will guide you in setting up your first local environment using Homestead. If you already have a local environment (e.g., LAMP or LEMP) and you're already familiar with **composer**, the [Native Installation](/installation/environment/native) guide is for you.

## Why you need a development environment

We get it - you just want to get UserFrosting up and running as quickly as possible.  Don't worry!  We'll get you there.

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

In the long run, the amount of time you'll spend uploading your code after each change you make will **easily dwarf** the amount of time it takes to set up a working test server on your own computer.  Examples of test server software are Apache and Nginx. If you don't like the idea of setting up a local test server, there's an alternative option that is a little easier and very reliable, called a virtual environment. Vagrant and Homestead (using them together) is an example of setting up a virtual environment.  

When you run your code in a place that is accessible only to you and where it's ok when something breaks, this is referred to as a **development environment.**  When you upload your so that it is running live and interacting with real visitors to your site, this is called a **production environment.**

## VirtualBox, Vagrant and Homestead

The first thing you'll need to do is install VirtualBox and Vagrant.  **VirtualBox** is a virtualization manager that lets you run just any operating system as a "guest" inside another operating system (the "host").  Our goal with VirtualBox is to let you run Ubuntu on a "virtual machine" on your computer, not matter which operating system you natively use.

To install VirtualBox, simply download and run one of the installers available [on their Downloads page](https://www.virtualbox.org/wiki/Downloads).

The next thing we'll do is set up **Vagrant**.  Vagrant works in tandem with VirtualBox to automatically manage the configuration and installed software inside your virtual machine.  Head over to Vagrant's [downloads page](https://www.vagrantup.com/downloads.html) to grab one of their installers.

If you think of VirtualBox as your kitchen, Vagrant is sort of like the cookbook that contains recipes for how to set up a useful development environment on your virtual machine.  The particular recipe that we'll be using is called **Homestead**, and it is has everything we need to easily set up the UserFrosting development environment.

### Command-line Life

Before we begin, it's important to understand that we will rely heavily on **command-line operations** and **git**.  If you are natively running on a Linux distribution or MacOS, this is already handled for you with Terminal and a preinstalled copy of git.

However, **if you are a Windows user**, you'll need to install `git` and get set up with a decent command-line program.  Fortunately, [Git for Windows](https://git-scm.com/downloads) takes care of both of these things for you.  Just install it, and you'll have `git` and the `Git Bash` command-line terminal available in your start menu.

### Setting up Homestead

**Once you've installed VirtualBox and Vagrant**, we can use Vagrant to spin up a virtual machine with the Homestead configuration.

#### Set up the virtual machine

The first thing we need to do is **create a virtual machine**.  To do this, open up your command line program (Terminal, Git Bash, whatever).  In Windows, you may need use choose "Run as administrator".  At the command line, run:

```bash
vagrant box add laravel/homestead
```

This will hit Vagrant's public catalog of preconfigured boxes and install the `laravel/homestead` box.

Homestead will automatically give us the following components that we need to run UserFrosting:

- Ubuntu 16.04
- Git
- PHP 7.1
- Nginx (webserver)
- MySQL/MariaDB (database)
- Composer
- Node and npm

Nice!  This means that we are saved the hassle of [setting these up natively in our operating system](/installation/requirements/essential-tools-for-php).

#### Download and initialize Homestead

**In a directory of your choice** (I have a generic `dev/` directory on my computer where I keep all of my projects), clone the Homestead _repository_ to a new subdirectory (we need both the box and the repository!):

```bash
git clone https://github.com/laravel/homestead.git homestead
```

While we're at it, we can also clone the UserFrosting repository into another directory:

```bash
git clone https://github.com/userfrosting/UserFrosting.git userfrosting
```

You should now have a directory structure that looks something like this:

```bash
Users/
└── alexweissman/
    └── dev/
        ├── homestead/
        └── userfrosting/
```

Let's now change into the `homestead/` directory and run the initialization script:

```bash
cd homestead
bash init.sh
```

This will create a `Homestead.yaml` file in the `homestead/` directory.  Open up this file in your favorite text editor, because we will need to make some modifications.

The default `Homestead.yaml` configuration file looks like this:

```yaml
 — -
ip: "192.168.10.10"
memory: 2048
cpus: 1
provider: virtualbox

authorize: ~/.ssh/id_rsa.pub

keys:
 — ~/.ssh/id_rsa

folders:
 — map: ~/Code
 to: /home/vagrant/Code

sites:
 — map: homestead.app
 to: /home/vagrant/Code/Laravel/public

databases:
 — homestead

# blackfire:
# — id: foo
# token: bar
# client-id: foo
# client-token: bar

# ports:
# — send: 50000
# to: 5000
# — send: 7777
# to: 777
# protocol: udp
```

The first section we'll focus on is the `authorize` and `keys` section.  This is the configuration for SSH which, for our purposes, is the means by which we will be able to "log in" to our virtual machine.

#### Create an SSH keypair

You can generate a new SSH keypair using the `ssh-keygen` tool.  Before doing this, make sure you have a `.ssh` directory in your home directory.  If not, you can do `mkdir $HOME/.ssh`.

Then, run the following command:

```bash
ssh-keygen -t rsa -f $HOME/.ssh/homestead_rsa
```

It will prompt you to create a passphrase.  Since this is all for a development environment, we don't need a passphrase - just hit Enter.  If it succeeds, you'll see something like:

```bash
Your identification has been saved in /Users/alexweissman/.ssh/homestead_rsa.
Your public key has been saved in /Users/alexweissman/.ssh/homestead_rsa.pub.
The key fingerprint is:
cf:3e:b8:a0:6a:11:91:74:a7:20:09:fb:b2:79:89:41 alexweissman@willis
The key's randomart image is:
+--[ RSA 2048]----+
|+oo.. .          |
|.ooo o           |
|.E ..            |
|...              |
|o ..    S        |
| *..     o       |
|+ o.  .  .o      |
| ..  . ....      |
| ....   ....     |
+-----------------+
```

You should now have files `homestead_rsa` and `homestead_rsa.pub`.  Change the `authorize` and `keys` paths to point to these files:

```yaml
authorize: ~/.ssh/homestead_rsa.pub

keys:
 — ~/.ssh/homestead_rsa
```

#### Customize `folders`, `sites`, and `database`

Homestead lets us share directories between our native operating system and the virtual machine.  For this to work, we need to map each directory in our native operating system, to a corresponding directory on the virtual machine.  To do this, we use the `folders` setting in `Homestead.yaml`:

```yaml
folders:
    — map: ~/userfrosting            # This is the directory on your "real" computer; should point to the userfrosting repo directory we made earlier
      to: /home/vagrant/userfrosting   # This is the corresponding directory in the virtual machine
```

If `folders` maps directories to directories, then `sites` maps URLs to our **document root** (similar to what VirtualHosts do in Apache).  In the case of UserFrosting, we want our document root on the virtual machine to be `/home/vagrant/userfrosting/public`.  We'll map this to a `userfrosting.app` URL, which we'll use to access our website in the browser:

```yaml
sites:
    - map: userfrosting.app
      to: /home/vagrant/userfrosting/public
```

Now any time we visit `http://userfrosting.app` in our browser, it will run our website starting in `/home/vagrant/userfrosting/public`.

Finally, we need to tell Homestead to create a database for us.  Change the `database` section to:

```yaml
databases:
    — userfrosting
```

Homestead will automatically create a `userfrosting` database, along with a `homestead` database user account.  The password will be `secret`.

#### Add `userfrosting.app` to your `hosts` file

We need to tell our host operating system how to find the "server" (running in our virtual machine) that corresponds to `userfrosting.app`.  To do this, we need to edit the `hosts` file.  In Windows, this file is located at `C:\Windows\System32\drivers\etc\hosts`.  In MacOS, you can find it at `/private/etc/hosts`.  In either case, you will need to edit it **as an administrator**, or temporarily give yourself permissions to write to this file.

Add the following lines at the bottom, save and exit:

```
# Vagrant projects
192.168.10.10  userfrosting.app
```

Notice that we're mapping the IP address from our `Homestead.yaml` file to our desired domain.

### Running the virtual machine

Congratulations!  We're ready to start up our virtual machine and get to work.  First, run:

```bash
vagrant up
```

from inside your `homestead/` directory.  This will take a little bit of time to provision the virtual machine.

>>>>>> Make sure that the directories you map in `Homestead.yaml` exist _before_ you run `vagrant up`.  Otherwise, you will need to reload your virtual machine using `vagrant reload --provision` so that Homestead has a chance to find your directories.

Once it's done, you'll be able to log into your virtual machine:

```bash
vagrant ssh
```

This will give you a welcome message for Ubuntu:

```bash
Welcome to Ubuntu 16.04.2 LTS (GNU/Linux 4.4.0-66-generic x86_64)

 * Documentation:  https://help.ubuntu.com
 * Management:     https://landscape.canonical.com
 * Support:        https://ubuntu.com/advantage

157 packages can be updated.
58 updates are security updates.


Last login: Wed Jun 21 06:42:59 2017 from 10.0.2.2
vagrant@homestead:~$ 
```

If you try the `ls` command, you should see the `userfrosting` directory that you had mapped in your `Homestead.yaml` file.  If you don't see this directory, double-check your `Homestead.yaml`, log out of the virtual machine (`exit`) and then reload the virtual machine (`vagrant reload --provision`).

### Installing UserFrosting

Now that you've logged into the virtual machine and have all the mappings properly set up, you can finish installing UserFrosting.

#### Composer dependencies

This will install UserFrosting's dependencies:

```bash
cd userfrosting
composer install
```

This may take some time to complete. If Composer has completed successfully, you should see that a `vendor/` directory has been created under `app/`. This `vendor/` directory contains all of UserFrosting's PHP dependencies - there should be nearly 30 subdirectories in here!

If you only see `composer` and `wikimedia` subdirectories after running `composer install`, then you may need to delete the `composer.lock` file (`rm composer.lock`) and run `composer install` again.

#### Assets and database setup

We can use [Bakery](/cli) to set up our database and download the Node and Bower dependencies:

```bash
$ php bakery bake
```

You will first be prompted for your database credentials.  Remember, our database information should be as follows:

- Host: `localhost`
- Port: `3306`
- Database name: `userfrosting`
- Database user: `homestead`
- Database password: `secret`

If the database connection is successful, the installer will then check that the basic dependencies are met. If so, the installer will run the _migrations_ to populate your database with new tables. During this process, you will be prompted for some information to set up the master account (first user). Finally, the installer will run the `build-assets` command to fetch javascript dependencies and build the [assets bundles](/asset-management/asset-bundles).

#### Check our your first UserFrosting installation!

Ok, that should be it!  If you head over to `http://userfrosting.app` in your browser, you should see the front page of the default UserFrosting installation.

### Next steps

#### Change your git remote

We highly recommend that you [change your git remote](/installation/environment/native#Changinggitremote) to make it easier to pull future updates to UserFrosting.

#### Install phpmyadmin

You can install phpmyadmin on your virtual machine to make it easier to interact with the `userfrosting` database.  If you're SSH'ed into your virtual machine, do the following:

```bash
sudo apt-get install phpmyadmin 
```

Do **not** select apache2 nor lighttpd when prompted. Just hit tab and enter.  Choose the defaults for any prompts that appear.

Next, create a symlink to the phpmyadmin installation:

```bash
sudo ln -s /usr/share/phpmyadmin/ /home/vagrant/phpmyadmin
```

`exit` from your virtual machine, and then add `phpmyadmin` to your `sites` in `Homestead.yaml`:

```bash
sites:
    - map: userfrosting.app
      to: /home/vagrant/userfrosting/public

    - map: phpmyadmin.app
      to: /home/vagrant/phpmyadmin
```

Don't forget to add `phpmyadmin.app` to your `hosts` file as well:

```
# Vagrant projects
192.168.10.10  userfrosting.app
192.168.10.10  phpmyadmin.app
```

Finally, reload your virtual machine and log back in:

```bash
vagrant reload --provision
vagrant ssh
```

You should be able to access phpmyadmin in your browser at `http://phpmyadmin.app`.  Remember, your database credentials are `homestead/secret`.  You may see some errors the first time you sign in - these can be ignored.

#### Start developing!

Head over to the chapter on [Sprinkles](/sprinkles) to get oriented and find your way around the UserFrosting codebase.  Come see us in [chat](https://chat.userfrosting.com) if you're having trouble.

It will help us a lot if you could star [the UserFrosting project on GitHub](https://github.com/userfrosting/UserFrosting). Just look for the button in the upper right-hand corner!

[![How to star](/images/how-to-star.png)](https://github.com/userfrosting/UserFrosting)

You should also follow us on Twitter for real-time news and updates:

<a class="twitter-follow-button" href="https://twitter.com/userfrosting" data-size="large">Follow @userfrosting</a>
