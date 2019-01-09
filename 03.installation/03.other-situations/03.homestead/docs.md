---
title: Advanced Homestead Setup
metadata:
    description: Homestead is a virtual environment, managed by Vagrant, that comes with everything you need to run UserFrosting preinstalled.  It's a great way to develop in a Ubuntu environment, if you'll be running your production site in a similar environment.
taxonomy:
    category: docs
---

>>> This guide is intended for advanced users who are familiar with Vagrant and Virtual Machines. For basic Homestead installation, see the [Homestead Dev Environment](/installation/environment/homestead) guide.

## Install Homestead Manually

In addition to the [Vagrant support built-in UserFrosting](/installation/environment/homestead), the Homestead dev environment can also be setup manually. Manual setup can be used to customize the installation or load multiple sites inside the same virtual machine.

Compared to the Vagrant integration built-in UserFrosting, which basically load the Homestead configuration into the UserFrosting directory, this page will guide you into loading the Homestead configuration into it's own directory, allowing you to define multiple sites in the same Vagrant Virtual Machine.

### Setting up Homestead

**Once you've installed VirtualBox and Vagrant**, we can use Vagrant to spin up a virtual machine with the Homestead configuration.

#### Set up the virtual machine

The first thing we need to do is **create a virtual machine**.  To do this, open up your command line program (Terminal, Git Bash, whatever).  In Windows, you may need use choose "Run as administrator".  At the command line, run:

```bash
vagrant box add laravel/homestead
```

This will hit Vagrant's public catalog of preconfigured boxes and install the `laravel/homestead` box.  You will be prompted to choose which virtual machine manager to use.  Choose the `virtualbox` option.

Homestead will automatically give us the following components that we need to run UserFrosting:

- Ubuntu 18.04
- Git
- PHP 7.2.x
- Nginx (webserver)
- MySQL/MariaDB (database)
- Composer
- Node and npm
- Bower and Gulp

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
---
ip: "192.168.10.10"
memory: 2048
cpus: 1
provider: virtualbox

authorize: ~/.ssh/id_rsa.pub

keys:
    - ~/.ssh/id_rsa

folders:
    - map: ~/code
      to: /home/vagrant/code

sites:
    - map: homestead.test
      to: /home/vagrant/code/public

databases:
    - homestead

# blackfire:
#     - id: foo
#       token: bar
#       client-id: foo
#       client-token: bar

# ports:
#     - send: 50000
#       to: 5000
#     - send: 7777
#       to: 777
#       protocol: udp
```

The first section we'll focus on is the `authorize` and `keys` section.  This is the configuration for SSH which, for our purposes, is the means by which we will be able to "log in" to our virtual machine.

#### Create an SSH keypair

You can generate a new SSH keypair using the `ssh-keygen` tool.  Before doing this, make sure you have a `.ssh` directory in your user's home directory (e.g. `C:/Users/<username>` in Windows, or `/Users/<username>` in Mac/Linux).  If not, you can do `mkdir $HOME/.ssh`.

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
 - ~/.ssh/homestead_rsa
```

#### Customize `folders`, `sites`, and `database`

Homestead lets us share directories between our native operating system and the virtual machine.  For this to work, we need to map each directory in our native operating system, to a corresponding directory on the virtual machine.  To do this, we use the `folders` setting in `Homestead.yaml`.  Replace the default `map` with the directory where you cloned UserFrosting on your host machine:

```yaml
folders:
    - map: ~/dev/userfrosting            # This is the directory on your "real" computer; should point to the userfrosting repo directory we made earlier
      to: /home/vagrant/userfrosting   # This is the corresponding directory in the virtual machine
```

>>> For Windows users, you should use the use the full, absolute path including the drive letter in your `map` value.  For example, `C:/Users/alexweissman/dev/userfrosting`.

If `folders` maps directories to directories, then `sites` maps URLs to our **document root** (similar to what VirtualHosts do in Apache).  In the case of UserFrosting, we want our document root on the virtual machine to be `/home/vagrant/userfrosting/public`.  We'll map this to a `userfrosting.test` URL, which we'll use to access our website in the browser.  Change the defaults to look like:

```yaml
sites:
    - map: userfrosting.test
      to: /home/vagrant/userfrosting/public
```

Now any time we visit `http://userfrosting.test` in our browser, it will run our website starting in `/home/vagrant/userfrosting/public`.

Finally, we need to tell Homestead to create a database for us.  Change the `database` section to:

```yaml
databases:
    - userfrosting
```

Homestead will automatically create a `userfrosting` database, along with a `homestead` database user account.  The password will be `secret`.

#### Add `userfrosting.test` to your `hosts` file

We need to tell our host operating system how to find the "server" (running in our virtual machine) that corresponds to `userfrosting.test`.  To do this, we need to edit the `hosts` file.  In Windows, this file is located at `C:\Windows\System32\drivers\etc\hosts`.  In MacOS, you can find it at `/private/etc/hosts`.  In either case, you will need to edit it **as an administrator**, or temporarily give yourself permissions to write to this file.

Add the following lines at the bottom, save and exit:

```
# Vagrant projects
192.168.10.10  userfrosting.test
```

Notice that we're mapping the IP address from our `Homestead.yaml` file to our desired domain.

### Running the virtual machine

Congratulations!  We're ready to start up our virtual machine and get to work.  First, from inside your `homestead/` directory, run:

```bash
vagrant up
```

This will take a little bit of time to provision the virtual machine.

If you get an error like "did not find expected key while parsing a block mapping", this means that Vagrant could not properly parse your `Homestead.yaml` file.  To find syntax errors in YAML files, try pasting them into [YAML Lint](http://www.yamllint.com/).

>>>>>> Make sure that the directories you map in `Homestead.yaml` exist _before_ you run `vagrant up`.  Otherwise, you will need to reload your virtual machine using `vagrant reload --provision` so that Homestead has a chance to find your directories.

Once it's done, you'll be able to log into your virtual machine:

```bash
vagrant ssh
```

>>>> It would appear that Git-Bash functions poorly as an SSH client in Windows.  For Windows users, you may want to use the native "command prompt" application instead.

If it connects successfully, you will see a welcome message for Ubuntu:

```bash
Welcome to Ubuntu 18.04 LTS (GNU/Linux 4.15.0-20-generic x86_64)

 * Documentation:  https://help.ubuntu.com
 * Management:     https://landscape.canonical.com
 * Support:        https://ubuntu.com/advantage

  System information as of Sun May 13 19:06:39 UTC 2018

  System load:  0.0               Processes:           121
  Usage of /:   8.2% of 61.80GB   Users logged in:     0
  Memory usage: 19%               IP address for eth0: 10.0.2.15
  Swap usage:   0%                IP address for eth1: 192.168.10.10

 * Meltdown, Spectre and Ubuntu: What are the attack vectors,
   how the fixes work, and everything else you need to know
   - https://ubu.one/u2Know

0 packages can be updated.
0 updates are security updates.
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

- Type: `MySQL`
- Host: `localhost`
- Port: `3306`
- Database name: `userfrosting`
- Database user: `homestead`
- Database password: `secret`

If the database connection is successful, the installer will then ask for STMP server config. This config is used to connect to the outgoing mail server. You can use the default values here, but UserFrosting won't be able to send outgoing emails.

Once the STMP config is defined, the installer will check that the basic dependencies are met. If so, the installer will run the _migrations_ to populate your database with new tables. After this process, you will be prompted for some information to set up the master account (first user). Finally, the installer will run the `build-assets` command to fetch javascript dependencies and build the [assets bundles](/asset-management/asset-bundles).

#### Check our your first UserFrosting installation!

Ok, that should be it!  If you head over to `http://userfrosting.test` in your browser, you should see the front page of the default UserFrosting installation.

### Next steps

#### Change your git remote

We highly recommend that you [change your git remote](/installation/environment/native#changing-git-remote) to make it easier to pull future updates to UserFrosting.

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
    - map: userfrosting.test
      to: /home/vagrant/userfrosting/public

    - map: phpmyadmin.test
      to: /home/vagrant/phpmyadmin
```

Don't forget to add `phpmyadmin.test` to your `hosts` file as well:

```
# Vagrant projects
192.168.10.10  userfrosting.test
192.168.10.10  phpmyadmin.test
```

Finally, reload your virtual machine and log back in:

```bash
vagrant reload --provision
vagrant ssh
```

You should be able to access phpmyadmin in your browser at `http://phpmyadmin.test`.  Remember, your database credentials are `homestead`/`secret`.  You may see some errors the first time you sign in - these can be ignored.

#### Configure NFS if pages load slowly

By default, the way that VirtualBox shares directories between your native operating system and the virtual machine can be very slow.  If you are experiencing slow page loads because of this, you can configure Homestead to use the `nfs` filesystem.

First, log in to the virtual machine:

```bash
vagrant ssh
```

Then install the `nfs-common` package in your virtual machine:

```bash
sudo apt-get install nfs-common portmap
```

When this is done, `exit` from your virtual machine.

In your `Homestead.yaml`, modify the `folders` mappings to use `nfs`:

```yaml
folders:
    - map: ~/userfrosting
      to: /home/vagrant/userfrosting
      type: "nfs"
```

Reload the virtual machine:

```bash
vagrant reload --provision
```

If you get errors about a missing `vboxsf` filesystem, then it is possible that your host operating system does not have NFS natively available.  In this case, you may need to install special NFS server software for your operating system.

#### Start developing!

Head over to the chapter on [Sprinkles](/sprinkles) to get oriented and find your way around the UserFrosting codebase.  Come see us in [chat](https://chat.userfrosting.com) if you're having trouble.

It will help us a lot if you could star [the UserFrosting project on GitHub](https://github.com/userfrosting/UserFrosting). Just look for the button in the upper right-hand corner!

[![How to star](/images/how-to-star.png)](https://github.com/userfrosting/UserFrosting)

You should also follow us on Twitter for real-time news and updates:

<a class="twitter-follow-button" href="https://twitter.com/userfrosting" data-size="large">Follow @userfrosting</a>
