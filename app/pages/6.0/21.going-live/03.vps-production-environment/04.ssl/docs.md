---
title: SSL/HTTPS
metadata:
    description: It is extremely important to use an SSL certificate when you go live.  Using SSL will prevent malicious agents on unsecured networks from intercepting your users' passwords when they log in to your application, as well as other sensitive information.
    obsolete: true
---
<!-- [plugin:content-inject](/modular/_updateRequired) -->

> [!NOTE]
> This page needs updating. To contribute to this documentation, please submit a pull request to our [learn repository](https://github.com/userfrosting/learn/tree/master/pages).

Let's Encrypt has put an enormous amount of effort into making `certbot` very user-friendly.  Most problems that come up when installing an SSL certificate with `certbot` can be traced back to file permissions issues.  Make sure that you understand how Linux file permissions work before attempting this task.

## Confirm that the webserver can serve acme challenges

When you run `certbot` on your server, it needs to verify that you actually control the domain for which you are requesting the SSL certificate.  Typically, this is done using an **acme challenge**.  `certbot` will place a file with a randomly generated name in `.well-known/acme-challenge/` in your project's document root (in our case, `/var/www/<repo name>/public`.  The Let's Encrypt certificate authority will attempt to access this file and verify that its name matches the one submitted by `certbot`.

This means that your webserver needs to be able to serve the challenge file.  For example, it should be possible to reach a URL like `http://owlfancy.com/.well-known/acme-challenge/0xGVf2dWppZhgTbk4_PlQQmJiNlJ5noHEK3oBy9W7Y` on your site.

To allow `nginx` to serve the acme challenge, you must have the following `location` block in the `server` block in your `/etc/nginx/sites-available/<repo name>.conf` file:

```
# Route ACME challenges for LetsEncrypt/Certbot
location ~ /.well-known {
    allow all;
}
```

This block is **already included** in the default nginx config file that ships with UserFrosting (as of 4.1.3).  If you are using a different configuration file, make sure that it is present.

## Run certbot

To run certbot, we'll use the `certbot` command.  Keep in mind that this won't work unless Let's Encrypt is able to connect to _all_ of the requested domains over http.  In other words, you need to have your DNS configured for these domains/subdomains first.

```bash
sudo certbot certonly --webroot --webroot-path=/var/www/<repo name>/public -d owlfancy.com,www.owlfancy.com
```

You'll be prompted for an email address (Let's Encrypt will email you if your certificate is about to expire), and to agree to the terms of service.

If everything goes well, you should see a message like:

```
Congratulations! Your certificate and chain have been saved at /etc/letsencrypt/live/owlfancy.com/fullchain.pem.
```

If not, carefully read the error message.  If you get an error like `Failed to connect to x.x.x.x:443 for TLS-SNI-01 challenge`, this means that the certificate authority couldn't access the `.well-known/acme-challenge/` URL to verify your ownership.  Double-check that nginx is configured properly to serve files in `.well-known/`, and that it has read permissions for this directory and subdirectories.

## Generate Strong Diffie-Hellman Group

To further increase security, you should also generate a strong Diffie-Hellman group. To generate a 2048-bit group, use this command:

```bash
sudo openssl dhparam -out /etc/nginx/dhparam.pem 2048
```

This may take a few minutes.

## Enable HTTPS in your nginx configuration file

In your nginx project configuration file (`/etc/nginx/sites-available/<repo name>.conf`), we need to enable HTTPS and tell nginx where to find our SSL certificates.  Edit this file, either directly on the server using `nano`, or by modifying it locally and then recopying it to the remote server using `scp`.

At the top of the second `server` block, find and comment out the `listen 80;` line:

```
## Non-SSL configuration.  Not recommended for production!
#listen 80;
```

Then, find the block that says "SSL configuration" and _uncomment_ it, starting at the `listen 443` line:

```
## SSL configuration
## It is STRONGLY RECOMMENDED that you use SSL for all traffic to your UF site.
## Otherwise, you are potentially leaking your users' sensitive info, including passwords!
## See https://letsencrypt.org/ to find out how to get a free, trusted SSL cert for your site.
#
listen 443 ssl spdy; # we listen ssl first with spdy second. if browser support spdy it will attempt to upgrade immediately on handshake
listen [::]:443 ssl spdy;
# Certificate paths (example for letsencrypt)
ssl_certificate /etc/letsencrypt/live/<cert name>/fullchain.pem;
ssl_certificate_key /etc/letsencrypt/live/<cert name>/privkey.pem;
# Disable SSLv3(enabled by default since nginx 0.8.19) since it's less secure then TLS http://en.wikipedia.org/wiki/Secure_Sockets_Layer#SSL_3.0
ssl_protocols TLSv1 TLSv1.1 TLSv1.2;
# Enable session resumption to enable low latency for repeat visitors.
ssl_session_cache shared:SSL:50m;
ssl_session_timeout 5m;
# Enables server-side protection from BEAST attacks
ssl_prefer_server_ciphers on;
# Diffie-Hellman parameter for DHE ciphersuites, recommended 2048 bits
ssl_dhparam /etc/nginx/dhparam.pem; # google will tell you how to make this
# Ciphers chosen for forward secrecy and compatibility
ssl_ciphers 'ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-AES256-GCM-SHA384:DHE-RSA-AES128-GCM-SHA256:DHE-DSS-AES128-GCM-SHA256:kEDH+AESGCM:ECDHE-RSA-AES128-SHA256:ECDHE-ECDSA-AES128-SHA256:ECDHE-RSA-AES128-SHA:ECDHE-ECDSA-AES128-SHA:ECDHE-RSA-AES256-SHA384:ECDHE-ECDSA-AES256-SHA384:ECDHE-RSA-AES256-SHA:ECDHE-ECDSA-AES256-SHA:DHE-RSA-AES128-SHA256:DHE-RSA-AES128-SHA:DHE-DSS-AES128-SHA256:DHE-RSA-AES256-SHA256:DHE-DSS-AES256-SHA:DHE-RSA-AES256-SHA:AES128-GCM-SHA256:AES256-GCM-SHA384:AES128-SHA256:AES256-SHA256:AES128-SHA:AES256-SHA:AES:CAMELLIA:DES-CBC3-SHA:!aNULL:!eNULL:!EXPORT:!DES:!RC4:!MD5:!PSK:!aECDH:!EDH-DSS-DES-CBC3-SHA:!EDH-RSA-DES-CBC3-SHA:!KRB5-DES-CBC3-SHA';
# Enable ocsp stapling (mechanism by which a site can convey certificate revocation information to visitors in a privacy-preserving, scalable manner)
resolver 8.8.8.8;
ssl_stapling on;
ssl_trusted_certificate /etc/letsencrypt/live/<cert name>/fullchain.pem; # same as your ssl_certificate path
# Config to enable HSTS(HTTP Strict Transport Security) https://developer.mozilla.org/en-US/docs/Security/HTTP_Strict_Transport_Security
add_header Strict-Transport-Security "max-age=31536000; includeSubdomains;";
```

In this block, replace `<cert name>` with the name of the certificate that was generated by certbot.  `certbot` should have told you this name when you ran the command, but if you forgot you can run:

```bash
certbot certificates
```

to get a list of certificates managed by `certbot` on this server.

> [!WARNING]
> Make sure that the paths for `ssl_certificate`, `ssl_certificate_key`, and `ssl_trusted_certificate` match the paths given by `certbot certificates`

## Redirect HTTP to HTTPS

In the same config file, find and uncomment the following `server` block:

```
## Redirect HTTP to HTTPS
## Enable this block once you've set up SSL.  This will redirect all HTTP requests to HTTPS.
server {
    listen 80;
    server_name owlfancy.com;
    return 301 https://$host$request_uri;
}
```

Any requests to `owlfancy.com` will be 301-redirected to the corresponding `https` url.

When you've finished making changes to your server configuration file, remember to run `sudo service nginx reload` so that nginx will see the new configuration.

If everything worked out correctly, you should now be able to access the HTTPS version of your site - for example, `https://owlfancy.com`.  Great!

## Set up automated renewal

Let's Encrypt certificates expire every 90 days.  This is great for security, but annoying (and potentially bad for business) if your certificate expires and your users get that horrible, scary, "Your connection is not secure" message in their browsers (why don't browsers show this message for sites that habitually serve sensitive content over http?!)

We'll take a few steps to make sure that this happens to you.  First, we'll set up a `cron` job that checks twice a day if your certificates are due for renewal, and if so, attempt the renewal process.

First, we need some extra tools.  `ts` is a tool that lets you add a timestamp to log files:

```
sudo apt install moreutils
```

Also, create a new logging subdirectory:

```
sudo mkdir /var/log/certbot
```

Now, open up your system `crontab`:

```
sudo crontab -e
```

Add the following lines at the bottom:

```
31 6,15 * * * export PATH=$PATH:/usr/sbin && /usr/bin/certbot renew --non-interactive --renew-hook "/bin/systemctl reload nginx" 2>&1 | ts "\%F \%T" >> /var/log/certbot/autorenew.log
```

- `31 6,15 * * *` tells `cron` to run this job at 6:31 and 15:31, every day;
- `export PATH=$PATH:/usr/sbin` lets this job run with root permissions;
- `--non-interactive` tells certbot not to wait for user input;
- `--renew-hook "/bin/systemctl reload nginx"` will tell certbot to automatically reload nginx when renewal succeeds;
- `2>&1 | ts "\%F \%T" >> /var/log/certbot-autorenew.log` logs each line of `certbot`'s output with a timestamp to `/var/log/certbot-autorenew.log`, to help you trace errors when the renewal fails.

> [!TIP]
> `certbot` automatically tries to upgrade itself each time it is run.  If you find that your `cron` job is failing often due to difficulties in automatically upgrading `certbot`, add `--no-self-upgrade` to the command.

## Set up a third-party service to monitor your certificates

Don't wait for a customer to call you and tell you that they "think your site was hacked" because your certificate expired and they got the "Your connection is not secure" message.  Sign up for free with a service like [Certificate Monitor](https://certificatemonitor.org) to automatically email you when your certificates are getting ready to expire.

With Let's Encrypt, 60-day warnings are normal, but 30-day or 14-day warnings likely mean that something has gone wrong with the autorenewal process.  In this case, check the `/var/log/certbot-autorenew.log` file to see what went wrong.  There is a Let's Encrypt IRC channel on Freenode (`#letsencrypt`) where you can get additional support for certificate renewal errors.

## Additional useful `certbot` commands

These aren't needed during initial setup, but may come in handy in the future:

### Show a list of all certificates installed on your server:

```bash
sudo certbot certificates
```

### Delete a certificate without revoking (let it expire naturally)

```bash
sudo certbot delete --cert-name example.com
```

> [!NOTE]
> The **certificate name** is not necessarily the same as the **domain name**.  Use the `certificates` command above to see the names of each certificate and the domains that they point to.

### Expand an existing certificate

```bash
sudo certbot certonly --cert-name example.com -d example.com,www.example.com,test.example.com
```

This will take the certificate named `example.com`, and set its domains as `example.com,www.example.com,test.example.com`.  Note that this replaces the entire certificate, so you will have to re-validate the acme challenge for _all_ listed domains.

> [!WARNING]
> Even when using `--cert-name`, you need to specify **all** the domains/subdomains that you would ultimately like to have registered on this certificate (not just the new ones).  Certbot cannot "add" additional domains/subdomains to an existing certificate - it must reissue a completely new cert.
