---
title: Additional Recommendations
description: Additional recommended configuration steps to get the most out of your VPS production server.
wip: true
---
<!-- [plugin:content-inject](modular/_updateRequired) -->

> [!NOTE]
> This page needs updating. To contribute to this documentation, please submit a pull request to our [learn repository](https://github.com/userfrosting/learn/tree/master/pages).

## Install and configure Google's Pagespeed module

To ensure the best possible experience for your users, we highly recommend that you install Google's [Pagespeed module](https://developers.google.com/speed/pagespeed/module/) on your production server. This module will automatically optimize your web pages for speed and performance before they are served to the client.

Since we recommend that you use nginx to serve your UserFrosting application, this guide will cover how to add the Pagespeed module to your nginx installation.

### Build nginx with the Pagespeed module

The easiest way to add the Pagespeed module to nginx is to use the Pagespeed team's automated build script to recompile nginx.

Before we can do this, however, we need to check how our copy of nginx was compiled the first time around. Our goal is to rebuild nginx with all the same modules and configuration options that were used for the current build, with the addition of the Pagespeed module on top of that.

If you're using a DigitalOcean LEMP distribution, nginx was already compiled and installed on your server. So, you probably have no idea what was actually included in that build. To determine this, we'll use the command:

```bash
nginx -V
```

This should print out some basic information about the build, along with a long list of `configure arguments:`. Take a moment to copy these into a text file somewhere. For example, my build was compiled with the following arguments:

```
--with-cc-opt='-g -O2 -fPIE -fstack-protector-strong -Wformat -Werror=format-security -Wdate-time -D_FORTIFY_SOURCE=2' --with-ld-opt='-Wl,-Bsymbolic-functions -fPIE -pie -Wl,-z,relro -Wl,-z,now' --prefix=/usr/share/nginx --conf-path=/etc/nginx/nginx.conf --http-log-path=/var/log/nginx/access.log --error-log-path=/var/log/nginx/error.log --lock-path=/var/lock/nginx.lock --pid-path=/run/nginx.pid --http-client-body-temp-path=/var/lib/nginx/body --http-fastcgi-temp-path=/var/lib/nginx/fastcgi --http-proxy-temp-path=/var/lib/nginx/proxy --http-scgi-temp-path=/var/lib/nginx/scgi --http-uwsgi-temp-path=/var/lib/nginx/uwsgi --with-debug --with-pcre-jit --with-ipv6 --with-http_ssl_module --with-http_stub_status_module --with-http_realip_module --with-http_auth_request_module --with-http_addition_module --with-http_dav_module --with-http_geoip_module --with-http_gunzip_module --with-http_gzip_static_module --with-http_image_filter_module --with-http_v2_module --with-http_sub_module --with-http_xslt_module --with-stream --with-stream_ssl_module --with-mail --with-mail_ssl_module --with-threads
```

Unfortunately, this copy of nginx was _built_ in a different environment than the one it was actually _distributed_ with. So, we are missing some dependencies required to actually build nginx again with these modules. In my case, I was missing dependencies for the `http_geoip`, `http_xslt`, and `http_image_filter` modules, as well as support for PCRE and the `unzip` command. So, let's install these dependencies now:

```bash
sudo apt-get install libpcre3-dev libxslt1-dev libgd-dev libgeoip-dev unzip
```

Hopefully you're not missing anything else - if you are, the build script will let us know!

To download and run the build script, we simply need to do:

```bash
bash <(curl -f -L -sS https://ngxpagespeed.com/install) --nginx-version latest
```

The script will download nginx and the pagespeed modules' source code, and begin the build process. **Pay attention to the prompts that come up:**

```bash
About to build nginx. Do you have any additional ./configure
arguments you would like to set? For example, if you would like
to build nginx with https support give --with-http_ssl_module
If you don't have any, just press enter.
>
```

**Yes, we do** - every single option that we retrieved with `nginx -V` earlier! So go ahead and paste all of that in, and press enter. You'll get a confirmation prompt, which should confirm all of these options plus the `--add-module=/home/alexw/ngx_pagespeed-latest-stable` option.

You'll get a few more prompts, to which we can just answer "yes":

```bash
You have set --with-debug for building nginx, but precompiled Debug binaries for
PSOL, which ngx_pagespeed depends on, aren't available. If you're trying to
debug PSOL you need to build it from source. If you just want to run nginx with
debug-level logging you can use the Release binaries.

Use the available Release binaries? [Y/n]
```

And then:

```bash
Build nginx? [Y/n]
```

At this point, you have successfully built a new binary of nginx, located at `~/nginx-<version>/objs/nginx` (replace `<version>` with whatever version the Pagespeed script used. At the time of writing this article, the current version was `1.13.3`).

### Replace the current nginx binary in `/usr/sbin`

Next, we need to replace our current nginx binary with the one we just built. I recommend copying the old binary to a backup file just in case something is wrong with our new build:

```bash
sudo mv /usr/sbin/nginx /usr/sbin/nginx-old
```

Now we can copy our new build into `/usr/sbin`:

```bash
sudo cp ~/nginx-<version>/objs/nginx /usr/sbin/nginx
```

Again, replace `<version>` with the version of your new build. Restart nginx:

```bash
sudo service nginx restart
```

### Prevent Ubuntu from automatically "upgrading" your nginx build

It's likely that Ubuntu will have [automatic, unattended upgrades](https://help.ubuntu.com/community/AutomaticSecurityUpdates#Using_the_.22unattended-upgrades.22_package) enabled by default. This is fine for many packages, but can be disastrous for your custom nginx build. This feature can overwrite your build, **causing nginx to fatally terminate** with an `Unknown directive "pagespeed"` error the next time it is restarted!

To prevent this, you can blacklist nginx from Ubuntu's unattended upgrades.

In the file `/etc/apt/apt.conf.d/50unattended-upgrades`, find the block that begins with `Unattended-Upgrade::Package-Blacklist {`.

Add the following lines in this block:
```
// Do not update nginx, since we use a custom build for pagespeed
"nginx";
```

This should prevent Ubuntu from replacing your custom nginx build.

### Configure `server` blocks to use Pagespeed

Now that you have Pagespeed compiled into nginx, you can uncomment the Pagespeed configuration settings (note that we have already added these commented-out in your configuration file) in the `server` block for each of your sites' configuration files (in `/etc/nginx/sites-available`):

```
## Begin - Pagespeed
pagespeed on;
pagespeed FileCachePath /var/ngx_pagespeed_cache;
pagespeed Disallow "*.svg*";
# Add additional filters here
pagespeed EnableFilters prioritize_critical_css;

# Ensure requests for pagespeed optimized resources go to the pagespeed
# handler and no extraneous headers get set.
location ~ "\.pagespeed\.([a-z]\.)?[a-z]{2}\.[^.]{10}\.[^.]+" { add_header "" ""; }
location ~ "^/ngx_pagespeed_static/" { }
location ~ "^/ngx_pagespeed_beacon" { }
## End - Pagespeed
```

A full list of filters that can be enabled/disabled for Pagespeed can be found [here](https://modpagespeed.com/doc/config_filters#enabling).

Don't forget to reload nginx (`sudo service nginx reload`) after modifying a configuration file!

To confirm that your site is now using Pagespeed we can inspect the response headers for one of our pages. You can do this in your browser console, or even more easily, using `curl`:

```bash
curl -I https://www.userfrosting.com
```

This should return a list of response headers, one of which should be the `X-Page-Speed` header:

```
HTTP/1.1 200 OK
Server: nginx/1.13.3
Content-Type: text/html;charset=UTF-8
Connection: keep-alive
Vary: Accept-Encoding
Strict-Transport-Security: max-age=15768000
Date: Sat, 05 Aug 2017 19:45:15 GMT
X-Page-Speed: 1.12.34.2-0
Cache-Control: max-age=0, no-cache
```
