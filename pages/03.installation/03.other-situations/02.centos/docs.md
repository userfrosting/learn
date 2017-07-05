---
title: Configuring for CentOS
metadata:
    description: Notes for configuring UserFrosting to work with CentOS and Apache.
taxonomy:
    category: docs
---

## Install prerequisites

```bash
// install apache httpd, git composer 
yum update
yum -y install httpd git composer
```

Get epel and repositories to install PHP 5.6 (do not do `yum install php` as this will get you PHP 5.4):

```bash
curl -O https://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm
curl -O https://centos7.iuscommunity.org/ius-release.rpm
rpm -Uvh ius-release*.rpm
yum install epel-release
yum -y update
```

Install PHP 5.6 and packages for managing DB connections:

```bash
yum -y install php56u php56u-opcache php56u-pdo php56u-pgsql php56u-xml php56u-mcrypt php56u-gd php56u-devel php56u-mysql php56u-intl php56u-mbstring php56u-bcmath
```

### Node.js

Getting Node.js is fun on CentOS. Do not run `yum install nodejs` as npm will not work properly and you will have to remove it. (If you've already done it use `yum remove node npm`).

```bash
curl -sL https://rpm.nodesource.com/setup_7.x | bash -
yum install -y nodejs
```
Check that the installed version of Node.js is > 4.0:

```bash
npm --v
```

## Configure Apache

```bash
// enable apache to start at boot and start service
systemctl start httpd.service
systemctl enable httpd.service
```

Make sure Apache has permission (e.g. 775 or 777) to write to the necessary directories.

```bash
chmod 775 userfrosting/app/cache userfrosting/app/logs userfrosting/app/sessions
```

Allow the Apache user to write files on the server:

```bash
chcon -t public_content_rw_t userfrosting/app/cache userfrosting/app/logs userfrosting/app/sessions
```

## Configure SELinux

Enable SELinux to allow Apache to write to disk:

```bash
setsebool allow_httpd_anon_write true
```

If your database is not running on localhost, you will need to allow SELinux to let Apache make network connections:

```bash
setsebool -P httpd_can_network_connect true
```

## Install SSL certificate (production environments)

```bash
yum install mod_ssl python-certbot-apache
certbot --apache -d userfrosting.com
```
