---
title: Configuring for IIS
metadata:
    description: Additional steps needed to configure IIS to work with UserFrosting.
taxonomy:
    category: docs
---

[notice=note]This recipe assumes that the reader is familiar with IIS7 or greater, and that the `web.config` file has been placed in `public`.[/notice]

In order for UserFrosting to work as expected under IIS, additional action may be required.

* Ensure that the PHP handler is permitted to handle at least the PUT, POST, GET, and DELETE verbs.
* Make sure IIS has permission to read and/or modify the appropriate files. Note that being unable to read `web.config` will result in all requests failing, as security precaution built into IIS.
* Make sure all UserFrosting files are readable, and that `app/cache`, `app/logs`, and `app/sessions` are all writeable, by the IUSR user account.
* Install and add the [RewriteModule](https://www.iis.net/downloads/microsoft/url-rewrite).
* By default, `php.ini` won't have the necessary PDO extension enabled. Make sure the PDO extension required for your database is enabled.

## Optimizations

Out of the box, IIS can be rather slow when it comes to hosting PHP sites vs. Apache and Nginx. Thankfully there are some configuration changes and addons that can potentially greatly improve performance.

* If you've got a GUI, get PHP Manager. This tool makes manipulating the `php.ini` file significantly easier, and can detect configuration issues for which it frequently is able to provide a 1 click resolution for.
* Disable .NET CLR for the application pool. Since UserFrosting is based purely on PHP, .NET is not needed and will just add overhead.
* Removed unneeded handler mappings. The only handlers you'll need are the PHP handler (`FastCgiModule`) and the static file handler (`StaticFileModule`).
* HTTPS. Seriously. IIS will only enable use of the latest, and fastest HTTP/2 protocol if the site supports HTTPS. Gains here will be admittedly very small, but given the security benefits (and SEO boost), its definitely worth implementing. (this is new in IIS10)
* Enable cache expiration. Without an expiration date specified, browsers have to make a judgement call, or will attempt to figure out the assets last-modified date. This can lead to unusual site behaviour and increased server load. The `web.config` file already has a commented out expiration setting for 31 days.
* Remove unneeded Modules. The only Modules you'll need are:
    * `AnonymousAuthenticationModule`
    * `FastCgiModule`
    * `HttpCacheModule` (improves performance)
    * `HttpLoggingModule` (technically not needed, but useful for logging each request)
    * `RequestFilteringModule` (used to filter requests that attempt to exploit holes in security, not essential but highly recommended)
    * `StaticCompressionModule` (improves performance)
    * `StaticFileCache`
* Enable static file compression. (by default compression is triggered by how frequently a file is hit, this can be overridden)
* Install and enable [WinCache](https://www.iis.net/downloads/microsoft/wincache-extension). Even under PHP 7, which is already capable of caching the PHP byte code, WinCache helps. Just make sure you grab the right version.
