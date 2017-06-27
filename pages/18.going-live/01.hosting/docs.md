---
title: Hosting Your Site
description:
    metadata: UserFrosting can easily be deployed to any server with PHP 5.6 or higher, a compatible database, and a webserver application (nginx, Apache, or IIS).
taxonomy:
    category: docs
---

>>> To contribute to this documentation, please submit a pull request to our [learn repository](https://github.com/userfrosting/learn/tree/master/pages).

Nowadays, there is little reason not to use a VPS (virtual private server) for hosting your application.  Prices have fallen considerably, to the point where they are as affordable as shared hosting.

It is true that a VPS requires a little more effort to set up than shared hosting.  However, companies like DigitalOcean have gone to great effort to produce a [huge collection of tutorials](https://www.digitalocean.com/community/tutorials) so that even someone with zero devops experience can set up a webserver on a VPS and deploy their application.

## Why you should not use shared hosting

A few reasons that we suggest that you _not_ use shared hosting:

- **Resource allocation.**  Shared hosting is _literally_ **shared**, which means that your application will take a hit if another hosting customer's application locks up a lot of memory and/or processing power.
- **Security.**  Similiarly, if another application on the same server is improperly secured, and the hosting company has not taken steps to properly limit what their customers' applications can do on the host operating system, a malicious agent can **gain unauthorized access** to the server.  This means that they could [gain read-only access](http://resources.infosecinstitute.com/risks-on-a-shared-hosting-server/) to your user directory!
- **Support tooling.**  Shared hosting tends to be well behind the curve in terms of the tools and strategies that modern developers use to make their workflow more efficient.  Most shared hosting services **don't provide command-line access**, and even those that do won't let you install new software on the server.  You're stuck with whatever they provide, and you can bet that tools like Composer and Node.js won't be on their radar.
- **Deployment options.**  Our recommended strategy for deploying small- and medium-sized projects is via `git`.  In contrast, shared hosting providers often only support FTP for transferring files from development to production.  This means that whenever you want to update your live site, you need to either try to figure out which files have changed, or **delete your entire live codebase and reupload everything**!  By deploying with git, you can use a simple `push` command to **automatically update** your live codebase.

## VPS hosting options

### DigitalOcean

DigitalOcean is a popular VPS hosting service that offers a flat monthly rate.  They call their virtual machines "Droplets", which are priced based on memory, processing power, disk storage, and bandwidth.  Each Droplet is essentially a dedicated IP address and computer that you have root access to, and on which you can install whatever operating system and software you like.

Their cheapest option is a USD $5/mo server, which provides 512MB memory, 20GB storage, and 1TB bandwidth per month.  This is more than enough to run a typical UserFrosting application - as a matter of fact, we host all of the documentation and the demo site for UserFrosting on a $5/month Droplet.

They also provide a convenient web-based control panel, which lets you perform some basic administrative tasks and monitor your Droplet's resource usage:

![Droplet control panel](/images/droplet.png)

### Promotions

DigitalOcean offers a number of discounts and promotions.  If you are a student, you can get $50 in free credit when you register for the [GitHub student pack](https://education.github.com/pack) (requires a `.edu` email address).

If you don't have a `.edu` address, you can get $10 free credit by using our [referral link](https://m.do.co/c/833058cf3824).  This is also a great way to support UserFrosting - if you use this link, _we'll_ also get $25 in credit for our own hosting once you've spent $25.

### Getting started

We recommend that you start with a $5/month Droplet and install a LEMP stack (Ubuntu 16.04, nginx, MariaDB, and PHP 7).  If you prefer you may install Apache instead, but nginx offers superior performance and requires less configuration.

When you go to create your Droplet, DigitalOcean will ask you some initial configuration questions.  Choose Ubuntu 16.04 as your distribution, and select a datacenter that is nearest to you and your customers.  **Do NOT set up SSH keys at this time** - if you do, DigitalOcean won't email you a root user password.  We will set up SSH later, after we've logged in with a password first.

From here, you can follow DigitalOcean's tutorials to set up your server:

#### [Initial Server Setup with Ubuntu 16.04](https://www.digitalocean.com/community/tutorials/initial-server-setup-with-ubuntu-16-04)

Some notes:

1. On Windows, you may find it easier to generate an SSH key in Putty and manually copy it to the `authorized_keys` file on your Droplet.
2. When you create your non-root user account in Ubuntu, we recommend adding them to the `www-data` group, which is the group to which your webserver belongs.  That way, you can set the group owner of your UserFrosting application files to `www-data`, and both your account _and_ the webserver account will have ownership.  To do this, do `sudo usermod -a -G www-data alex`, assuming `alex` is your user account name.
3. Their instructions for the `ufw` firewall only have you open up the `ssh` port by default.  Obviously for a web server, you will also need to open up ports 80 and 443.  See [this guide](https://www.digitalocean.com/community/tutorials/how-to-set-up-a-firewall-with-ufw-on-ubuntu-14-04#allow-other-connections) for help opening up additional ports.

#### [Installing the LEMP Stack](https://www.digitalocean.com/community/tutorials/how-to-install-linux-nginx-mysql-php-lemp-stack-in-ubuntu-16-04)

Some notes:

1. This guide has you install MySQL instead of MariaDB.  In general they are completely interchangeable, but MariaDB is more reliable as an  open-source option going forward.  See [Switching to MariaDB](https://www.digitalocean.com/community/tutorials/switching-to-mariadb-from-mysql) for help with this.

#### [Add Swap Space](https://www.digitalocean.com/community/tutorials/how-to-add-swap-space-on-ubuntu-16-04)

Some notes:

1. This is just a failsafe in the event that your server experiences occasional spikes in memory usage, for example when installing new software or running a backup.  If your server seems to be routinely using more than 50% of its allocated memory, you should consider upgrading to a Droplet with more memory.

#### Other Tools

- [Installing Composer](https://www.digitalocean.com/community/tutorials/how-to-install-and-use-composer-on-ubuntu-16-04)
- [Installing Node.js](https://www.digitalocean.com/community/tutorials/how-to-install-node-js-on-ubuntu-16-04)
- [Installing Git](https://www.digitalocean.com/community/tutorials/how-to-install-git-on-ubuntu-16-04)

#### [Install Certbot (Let's Encrypt)](https://www.digitalocean.com/community/tutorials/how-to-secure-nginx-with-let-s-encrypt-on-ubuntu-16-04)

Some notes:

1. You won't actually be able to set up a new SSL certificate until you have deployed your application for the first time.  Just follow the first few steps for now to install the `certbot` client.

#### [Install phpMyAdmin](https://www.digitalocean.com/community/tutorials/how-to-install-and-secure-phpmyadmin-with-nginx-on-an-ubuntu-14-04-server)

Notes:

1. This guide is for Ubuntu 14.04, but the process should be basically the same in Ubuntu 16.04.
