---
title: SSL/HTTPS
metadata:
    description: It is extremely important to use an SSL certificate when you go live.  Using SSL will prevent malicious agents on unsecured networks from intercepting your users' passwords when they log in to your application, as well as other sensitive information.
taxonomy:
    category: docs
---

>>> To contribute to this documentation, please submit a pull request to our [learn repository](https://github.com/userfrosting/learn/tree/master/pages).

Let's Encrypt has put an enormous amount of effort into making `certbot` very user-friendly.  Most problems that come up when installing an SSL certificate with `certbot` can be traced back to file permissions issues.  Make sure that you understand how Linux file permissions work before attempting this task.


