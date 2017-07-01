---
title: Server Setup
metadata:
    description: No matter which VPS option you choose, you'll need to make sure that you have the required software installed and properly configured for UserFrosting.
taxonomy:
    category: docs
---

We recommend that you start with a $5/month Droplet and install a LEMP stack (Ubuntu 16.04, nginx, MariaDB, and PHP 7).  If you prefer you may install Apache instead, but nginx offers superior performance and requires less configuration.

When you go to create your Droplet, DigitalOcean will ask you some initial configuration questions.  Choose Ubuntu 16.04 as your distribution, and select a datacenter that is nearest to you and your customers.  **Do NOT set up SSH keys at this time** - if you do, DigitalOcean won't email you a root user password.  We will set up SSH later, after we've logged in with a password first.

From here, you can follow DigitalOcean's tutorials to set up your server:

## [Initial Server Setup with Ubuntu 16.04](https://www.digitalocean.com/community/tutorials/initial-server-setup-with-ubuntu-16-04)

Some notes:

1. On Windows, you may find it easier to generate an SSH key in Putty and manually copy it to the `authorized_keys` file on your Droplet.
2. When you create your non-root user account in Ubuntu, we recommend adding them to the `www-data` group, which is the group to which your webserver belongs.  That way, you can set the group owner of your UserFrosting application files to `www-data`, and both your account _and_ the webserver account will have ownership.  To do this, do `sudo usermod -a -G www-data alex`, assuming `alex` is your user account name.
3. Their instructions for the `ufw` firewall only have you open up the `ssh` port by default.  Obviously for a web server, you will also need to open up ports 80 and 443.  See [this guide](https://www.digitalocean.com/community/tutorials/how-to-set-up-a-firewall-with-ufw-on-ubuntu-14-04#allow-other-connections) for help opening up additional ports.
4. For additional security, you may also want to disable root login via SSH by setting `PermitRootLogin` to `no` in your `/etc/ssh/sshd_config` file.

**Configure the `nano` command-line editor to convert tabs to spaces:**

Because spaces rule.

```bash
nano ~/.nanorc
```

Add the following:

```
set tabsize 4
set tabstospaces
```

Save and exit (Ctrl-X).

You'll probably want to do this same thing in the root `.nanorc` file, for when you are editing files as the root user:

```bash
sudo nano /root/.nanorc
```

## [Add Swap Space](https://www.digitalocean.com/community/tutorials/how-to-add-swap-space-on-ubuntu-16-04)

Some notes:

1. This is just a failsafe in the event that your server experiences occasional spikes in memory usage, for example when installing new software or running a backup.  If your server seems to be routinely using more than 70% of its allocated memory, you should consider upgrading to a Droplet with more memory.

## [Installing the LEMP Stack](https://www.digitalocean.com/community/tutorials/how-to-install-linux-nginx-mysql-php-lemp-stack-in-ubuntu-16-04)

Some notes:

1. This guide has you install MySQL instead of MariaDB.  In general they are completely interchangeable, but MariaDB is more reliable as an  open-source option going forward.  See [Switching to MariaDB](https://www.digitalocean.com/community/tutorials/switching-to-mariadb-from-mysql) for help with this.
2. Be sure to [log into MySQL from the command line](https://www.digitalocean.com/community/tutorials/a-basic-mysql-tutorial) and [create a non-root database user account](https://www.digitalocean.com/community/tutorials/how-to-create-a-new-user-and-grant-permissions-in-mysql).  You should give this user limited permissions on your production database.
3. The `gzip` module (which is important for site speed and SEO!), may require some additional configuration.  See [this guide](https://www.digitalocean.com/community/tutorials/how-to-add-the-gzip-module-to-nginx-on-ubuntu-14-04).

### Additional php modules to install:

Install gd and curl:

```bash
sudo apt-get install php7.0-gd
sudo apt-get install php-curl
sudo service nginx restart
```

## Other Tools

- [Installing Composer](https://www.digitalocean.com/community/tutorials/how-to-install-and-use-composer-on-ubuntu-16-04) (Steps 1 and 2 only)
- [Installing Node.js and npm](https://www.digitalocean.com/community/tutorials/how-to-install-node-js-on-ubuntu-16-04) (Distro-stable version)
- Git comes preinstalled on Ubuntu, but you may want to [update and configure](https://www.digitalocean.com/community/tutorials/how-to-install-git-on-ubuntu-16-04) it as well.

### Node.js compatibility package

On Ubuntu, the `node` package has been changed to `nodejs` to avoid a naming collision with another package called `node`.  Unfortunately, this breaks `npm`, which is expecting the `node` command to refer to Node.js.  To fix this, install the compatibility package:

```
sudo apt-get install nodejs-legacy
```

## [Install Certbot (Let's Encrypt)](https://certbot.eff.org/#ubuntuxenial-nginx)

Some notes:

1. You won't actually be able to set up a new SSL certificate until you have deployed your application for the first time.  Just install the `certbot` client for now.

## [Install phpMyAdmin](https://www.digitalocean.com/community/tutorials/how-to-install-and-secure-phpmyadmin-with-nginx-on-an-ubuntu-14-04-server)

Notes:

1. This guide is for Ubuntu 14.04, but the process should be basically the same in Ubuntu 16.04.
2. Make sure to pick a particularly strong password for the phpmyadmin user account.  You can use [Random.org](https://www.random.org/passwords/) - we recommend generating something with at least 20 characters.
3. To enable `mcrypt` in PHP 7:

```
sudo phpenmod mcrypt
sudo service php7.0-fpm restart
```

To disable root login and restrict access to specific users:

```
cd /etc/phpmyadmin
sudo nano config.inc.php
```

Find the lines that say:

```php
/**
 * Server(s) configuration
 */
$i = 0;
// The $cfg['Servers'] array starts with $cfg['Servers'][1].  Do not use $cfg['Servers'][0].
// You can disable a server config entry by setting host to ''.
$i++;
```

Below this add:

```php
$cfg['Servers'][$i]['AllowDeny']['order'] = 'explicit';
$cfg['Servers'][$i]['AllowDeny']['rules'] = [
    'allow alex from all'
];
```

This will allow only `alex` to log in via phpMyAdmin.
