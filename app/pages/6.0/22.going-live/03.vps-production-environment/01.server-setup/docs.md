---
title: Server Setup
description: No matter which VPS option you choose, you'll need to make sure that you have the required software installed and properly configured for UserFrosting.
outdated: true
---

We recommend starting with a 1GB+ memory VPS and installing a LEMP stack (Ubuntu 24.04 LTS, nginx, MariaDB, and PHP 8.1+). You can use a pre-configured [one-click LEMP stack](https://marketplace.digitalocean.com/apps/lemp) or manually install the components. While Apache is also supported, nginx offers superior performance and requires less configuration.

> [!NOTE]
> UserFrosting requires PHP 8.1 or higher. Make sure your server stack includes a compatible PHP version.

When creating your VPS, select **Ubuntu 24.04 LTS** (or 22.04 LTS) as your distribution, and choose a datacenter that is geographically close to you and your users for optimal latency. **Do NOT set up SSH keys at this time** - if you do, DigitalOcean won't email you a root user password. We will set up SSH later, after we've logged in with a password first.

From here, you can follow DigitalOcean's tutorials to set up your server:

## Initial Server Setup with Ubuntu

Follow [**this tutorial for Ubuntu 24.04**](hhttps://www.digitalocean.com/community/tutorials/how-to-install-linux-nginx-mysql-php-lemp-stack-on-ubuntu) or [Ubuntu 22.04](https://www.digitalocean.com/community/tutorials/initial-server-setup-with-ubuntu-22-04).

Key configuration steps:

1. **SSH Keys**: On Windows, use [Windows Terminal](https://aka.ms/terminal) or [PuTTY](https://www.putty.org/) to generate SSH keys. Modern Windows includes OpenSSH by default.

2. **User Groups**: Add your non-root user to the `www-data` group so both your account and the webserver can access application files:
   ```bash
   sudo usermod -a -G www-data <your-username>
   ```

3. **Firewall Configuration**: Configure `ufw` to allow web traffic:
   ```bash
   sudo ufw allow 'Nginx Full'  # Allows both HTTP (80) and HTTPS (443)
   sudo ufw allow OpenSSH
   sudo ufw enable
   ```
   
   Alternatively, use your hosting provider's cloud firewall dashboard.

4. **Disable Root Login**: For security, set `PermitRootLogin no` in `/etc/ssh/sshd_config` and reload SSH:
   ```bash
   sudo systemctl reload sshd
   ```

## Additional server configuration

### Set your server's timezone

Configure your server to use the correct timezone:

```bash
sudo timedatectl set-timezone America/New_York  # Replace with your timezone
timedatectl  # Verify the change
```

For more details, see [**this DigitalOcean guide**](https://www.digitalocean.com/community/tutorials/how-to-set-up-time-synchronization-on-ubuntu-22-04).

### Configure the `nano` command-line editor to convert tabs to spaces

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

## Add Swap Space

Follow [**this tutorial**](https://www.digitalocean.com/community/tutorials/how-to-add-swap-space-on-ubuntu-22-04). Swap space is a part of virtual memory, which allows your server to temporarily move data to the hard drive when there is not enough physical memory available for whatever it is doing. This is essentially the same thing as the `pagefile.sys` in a Windows environment.

Some notes:

1. This is just a failsafe in the event that your server experiences occasional spikes in memory usage, for example when installing new software or running a backup. If your server seems to be routinely using more than 70% of its allocated memory, you should consider upgrading to a Droplet with more memory.
2. DigitalOcean recommends against enabling a swap file on any server (including theirs) which uses SSD.

## Install the LEMP Stack

See [**this guide**](https://www.digitalocean.com/community/tutorials/how-to-install-linux-nginx-mysql-php-lemp-stack-on-ubuntu-22-04).

Some notes:

1. This guide has you install MySQL instead of MariaDB. In general they are completely interchangeable, but MariaDB is more reliable as an open-source option going forward. See [Switching to MariaDB](https://www.digitalocean.com/community/tutorials/switching-to-mariadb-from-mysql) for help with this.
2. Be sure to log into MySQL from the command line and [create a non-root database user account](https://www.digitalocean.com/community/tutorials/how-to-create-a-new-user-and-grant-permissions-in-mysql). You should give this user limited permissions on your production database.
3. The `gzip` module (which is important for site speed and SEO!), may require some additional configuration. See [this guide](https://www.digitalocean.com/community/tutorials/how-to-improve-website-performance-using-gzip-and-nginx-on-ubuntu-22-04).

### Additional php modules to install:

Install gd and curl:

```bash
sudo apt-get install php8.5-gd
sudo apt-get install php-curl
sudo service nginx restart
```

### `browscap.ini`

PHP's [`get_browser()`](http://php.net/manual/en/function.get-browser.php) function uses the `User-Agent` header to guess information about your visitors such as browser, OS, etc. For it to work properly, you need to download a copy of `browscap.ini` from the [Browscap Project](https://browscap.org) and configure your `php.ini` to find the file.

Assuming that your PHP installation is in `/etc/php/8.5`, do the following:

```bash
cd /etc/php/8.5/fpm
sudo mkdir extra
sudo curl -o /etc/php/8.5/fpm/extra/browscap.ini https://browscap.org/stream?q=Lite_PHP_BrowsCapINI
```

This will download the "lite" browscap database, which is supposed to be adequate for most websites. Visit [Browscap Project](https://browscap.org) for other options.

Now, we need to edit our `php.ini` to tell PHP where this file is located:

```bash
sudo nano /etc/php/8.5/fpm/php.ini
```

Use Ctrl+W to search for the `browscap` section. Uncomment the `browscap = ` line. When you're done, it should look like this:

```
[browscap]
; http://php.net/browscap
browscap = extra/browscap.ini
```

Save and exit.

## Other Tools

- [Installing Composer](https://www.digitalocean.com/community/tutorials/how-to-install-and-use-composer-on-ubuntu-22-04) (Steps 1 and 2 only)
- [Installing Node.js and npm](https://www.digitalocean.com/community/tutorials/how-to-install-node-js-on-ubuntu-22-04) (Distro-stable version)
- Git comes preinstalled on Ubuntu, but you may want to [update and configure](https://www.digitalocean.com/community/tutorials/how-to-install-git-on-ubuntu-22-04) it as well.

### Node.js compatibility package

On Ubuntu, the `node` package has been changed to `nodejs` to avoid a naming collision with another package called `node`. Unfortunately, this breaks `npm`, which is expecting the `node` command to refer to Node.js. To fix this, install the compatibility package:

```bash
sudo apt-get install nodejs-legacy
```

## Install Certbot (Let's Encrypt)

See the [**certbot tutorial**](https://certbot.eff.org/#ubuntuxenial-nginx).

Some notes:

1. You won't actually be able to set up a new SSL certificate until you have deployed your application for the first time. Just install the `certbot` client for now.

## Install phpMyAdmin

See this [**DigitalOcean tutorial**](https://www.digitalocean.com/community/tutorials/how-to-install-and-secure-phpmyadmin-with-nginx-on-an-ubuntu-22-04-server).

Notes:

1. Make sure to pick a particularly strong password for the phpmyadmin user account. For development, you can use [Random.org](https://www.random.org/passwords/) - we recommend generating something with at least 20 characters. > [!NOTE]
> Random.org recommends against using any online password creation service, including theirs, for anything sensitive.
2. To enable `mcrypt` in PHP 8:

```bash
sudo phpenmod mcrypt
sudo service php8.5-fpm restart
```

To disable root login and restrict access to specific users:

```bash
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
