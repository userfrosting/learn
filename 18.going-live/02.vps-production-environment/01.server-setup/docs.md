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

## [Installing the LEMP Stack](https://www.digitalocean.com/community/tutorials/how-to-install-linux-nginx-mysql-php-lemp-stack-in-ubuntu-16-04)

Some notes:

1. This guide has you install MySQL instead of MariaDB.  In general they are completely interchangeable, but MariaDB is more reliable as an  open-source option going forward.  See [Switching to MariaDB](https://www.digitalocean.com/community/tutorials/switching-to-mariadb-from-mysql) for help with this.
2. Be sure to [log into MySQL from the command line](https://www.digitalocean.com/community/tutorials/a-basic-mysql-tutorial) and [create a non-root database user account](https://www.digitalocean.com/community/tutorials/how-to-create-a-new-user-and-grant-permissions-in-mysql).  You should give this user limited permissions on your production database.
3. The `gzip` module (which is important for site speed and SEO!), may require some additional configuration.  See [this guide](https://www.digitalocean.com/community/tutorials/how-to-add-the-gzip-module-to-nginx-on-ubuntu-14-04).
4. If the `php-gd` package is not installed, you may need to install it manually:

```bash
sudo apt-get install php7.0-gd
sudo service nginx restart
```

## [Add Swap Space](https://www.digitalocean.com/community/tutorials/how-to-add-swap-space-on-ubuntu-16-04)

Some notes:

1. This is just a failsafe in the event that your server experiences occasional spikes in memory usage, for example when installing new software or running a backup.  If your server seems to be routinely using more than 50% of its allocated memory, you should consider upgrading to a Droplet with more memory.

## Other Tools

- [Installing Composer](https://www.digitalocean.com/community/tutorials/how-to-install-and-use-composer-on-ubuntu-16-04)
- [Installing Node.js](https://www.digitalocean.com/community/tutorials/how-to-install-node-js-on-ubuntu-16-04)
- [Installing Git](https://www.digitalocean.com/community/tutorials/how-to-install-git-on-ubuntu-16-04)

## [Install Certbot (Let's Encrypt)](https://www.digitalocean.com/community/tutorials/how-to-secure-nginx-with-let-s-encrypt-on-ubuntu-16-04)

Some notes:

1. You won't actually be able to set up a new SSL certificate until you have deployed your application for the first time.  Just follow the first few steps for now to install the `certbot` client.

## [Install phpMyAdmin](https://www.digitalocean.com/community/tutorials/how-to-install-and-secure-phpmyadmin-with-nginx-on-an-ubuntu-14-04-server)

Notes:

1. This guide is for Ubuntu 14.04, but the process should be basically the same in Ubuntu 16.04.
