--
title: Hosting with IIS
metadata:
    description: Additional steps needed to host UserFrosting sites on IIS.
taxonomy:
    category: docs
---

>NOTE: This recipe assumes that the reader is familiar with IIS7 or greater, and that the `web.config` file has been placed in `public`.

In order for UserFrosting to work as expected under IIS, additional action may be required.

* Ensure that the PHP handler is permitted to handle at least the PUT, POST, GET, and DELETE verbs.
* Make sure IIS has permission to read and/or modify the appropriate files. Note that being unable to read `web.config` will result in all requests failing, as security percaution built into IIS.

# Optimizations
Out of the box, IIS can be rather slow when it comes to hosting PHP sites vs. Apache and Nginx. Thankfully there are some configuration changes and addons that can potentially greatly improve performance.

Coming soon!
