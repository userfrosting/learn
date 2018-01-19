---
title: Application Setup
metadata:
    description: This section covers installing and using Composer, running Bakery, and configuring the webserver in the production environment.
taxonomy:
    category: docs
---

To actually get our application up and running, we need to do a few more things on the remote server:

1. Run Composer to install PHP dependencies;
2. Run Bakery to set up our environment variables, create `sprinkles.json`, run our migrations, and install frontend vendor assets;
3. Configure the webserver to use `/var/www/<repo name>/public/` as the document root;
4. Compile assets for production.
5. Use `certbot` to install an SSL certificate for your site.

## Run Composer on the remote server

The tricky thing about Composer is that it will try to pull the latest version of a dependency (subject to the version constraints in `composer.json`) every time you run `composer update`.  This means that you could end up with latest version of dependencies in production every time you update your code - even if you don't want them!

To get around this, commit the `composer.lock` file to your repository.  Then, when you run `composer install` on the remote machine, it will pull the _exact_ same versions of your dependencies as those that were last pulled when you ran `composer update` locally.

A few more caveats:

- On a 512MB Droplet, there may not be enough physical memory to run Composer.  What a pig!  [Create a swapfile](https://www.digitalocean.com/community/tutorials/how-to-add-swap-space-on-ubuntu-16-04) first to avoid Composer failing due to insufficient memory.
- Composer needs to be able to write to the `~/.composer` directory, but it doesn't like when you try to run Composer as `sudo`.  To address this set proper ownership and permissions on the `~/.composer` directory:

```bash
sudo chown <your username>:<your username> ~/.composer
sudo chmod u+rwx,g+rwx ~/.composer
```

Now, to run Composer:

```bash
cd /var/www/<repo name>
composer install
```

If you get errors related to the `php-zip` package, you may need to install it:

```bash
sudo apt-get install zip unzip php7.0-zip
sudo service nginx restart
```

We can add the following line to our `post-receive` hook to automatically rerun `composer install` each time we push changes:

```bash
# Install or update packages specified in the lock file
composer install --working-dir=/var/www/<repo name> >> /var/log/deploy.log 2>&1
```

## Run Bakery on the remote server

Just like we did in development, we'll run Bakery in the production environment to configure our DB and mail credentials, setup `sprinkles.json`, and install assets.  Again, do this in the `/var/www/<repo name>` directory:

```bash
php bakery bake
```

When Bakery finishes, modify the `.env` file and set `UF_MODE` to `production`.

## Configure the webserver (nginx)

UserFrosting ships with a default nginx configuration file, in `webserver-configs/nginx.conf`.  Copy this file to a new filename.  You can name the copy anything you like, but the convention is to use the same name as that of your repository, followed by `.conf`.

We'll start by setting the `root` and `server_name` directives.  `root` is the application's document root directory, and `server_name` is the hostname (domain or subdomain) that should be served from this directory.  Find the `Server Info` block.  Change it to look like this:

```
## Begin - Server Info
## Document root directory for your project.  Should be set to the directory that contains your index.php.
root /var/www/<repo name>/public;
server_name owlfancy.com;
## End - Server Info
```

Again, `<repo name>` should be replaced with your project repo name, and `owlfancy.com` should be changed to your site's planned domain or subdomain.

Next, we'll tell nginx to run our application with PHP 7.  Why?  Because it's super fast!  Find the lines that say "For FPM".  Comment out the line for PHP 5, and _uncomment_ the line for PHP 7:

```
# For FPM (PHP 5.x)
#fastcgi_pass unix:/var/run/php5-fpm.sock;
# For FPM (PHP 7)
fastcgi_pass unix:/run/php/php7.0-fpm.sock;
```

Save your changes.  We can now use `scp` to copy this file from your local machine to nginx's configuration directory on the remote repository.  Since we disabled remote root login, and only the root user can write to `/etc/nginx/sites-available` by default, we'll first copy to our home directory on the remote server, and then use `sudo` on the remote server to move it into nginx's directory.

In your local development environment:

```bash
scp /<path to local project directory>/webserver-configs/<repo name>.conf <your username>@<hostname>:~
```

If this succeeded, then you can go back to your remote environment, move the config file to `/etc/nginx/sites-available`, and "enable" it by creating a symlink and reloading the webserver:

```bash
sudo mv ~/<repo name>.conf /etc/nginx/sites-available/<repo name>.conf
sudo ln -s /etc/nginx/sites-available/<repo name>.conf /etc/nginx/sites-enabled/<repo name>.conf
sudo service nginx reload
```

We also need to make sure that **nginx can read the `public/` directory, and write to the cache, logs, and sessions directories**:

```bash
cd /var/www/<repo name>/
sudo chown <your username>:www-data public
sudo chown <your username>:www-data app/cache app/logs app/sessions
```

After your first `git push`, you'll want to set up the `cache/` directory so that it is owned by the `www-data` **group**.  Since both your user account and webserver are part of this group, they'll both be able to write to it.  This is important, so that in your `post-receive` script, you'll have the necessary permissions to clear the cache when you push.  To do this, we'll use the `setfacl` command:

```bash
sudo setfacl -d -m g::rwx /var/www/<repo name>/app/cache
```

>>>>> **A note about debugging server configuration:** Browsers and operating systems tend to aggressively cache DNS resolutions and redirects.  This means that if you misconfigured your server initially and it returned an error due to DNS or webserver configuration issues, your browser might still have the error response cached even after you've fixed the problem.  You may need to clear your browser's cache or even [your operating system's DNS cache](https://help.dreamhost.com/hc/en-us/articles/214981288-Flushing-your-DNS-cache-in-Mac-OS-X-and-Linux).  When configuring your server, you might have better luck using `curl` to check whether a particular URL is working, rather than going through your browser.

## Compile assets for production

We can use Bakery again to compile our asset bundles for production, and copy all assets from our Sprinkles to the `public/` directory so they may be served more efficiently by the webserver:

```bash
php bakery build-assets -c
```

If everything worked out successfully, you should now be able to access the `http` version of your live site in your browser!  The next step is to [install an SSL certificate](/going-live/vps-production-environment/ssl).
