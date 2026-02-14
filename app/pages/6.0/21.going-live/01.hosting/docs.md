---
title: Hosting Your Site
description: UserFrosting can easily be deployed to any server with PHP 8.1 or higher, a compatible database, and a webserver application (nginx, Apache, or IIS).
---

Nowadays, there is little reason not to use a VPS (virtual private server) for hosting your application. Prices have fallen considerably, to the point where they are as affordable as shared hosting.

It is true that a VPS requires a little more effort to set up than shared hosting. However, companies like DigitalOcean have gone to great effort to produce a [huge collection of tutorials](https://www.digitalocean.com/community/tutorials) so that even someone with zero devops experience can set up a webserver on a VPS and deploy their application.

## Why you should not use shared hosting

A few reasons that we suggest that you **_not_** use shared hosting:

- **Resource allocation.** Shared hosting is _literally_ **shared**, which means that your application will take a hit if another hosting customer's application locks up a lot of memory and/or processing power.
- **Security.** Similiarly, if another application on the same server is improperly secured, and the hosting company has not taken steps to properly limit what their customers' applications can do on the host operating system, a malicious agent can **gain unauthorized access** to the server. This means that they could [gain read-only access](http://resources.infosecinstitute.com/risks-on-a-shared-hosting-server/) to your user directory!
- **Support tooling.** Shared hosting tends to be well behind the curve in terms of the tools and strategies that modern developers use to make their workflow more efficient. Most shared hosting services **don't provide command-line access**, and even those that do won't let you install new software on the server. You're stuck with whatever they provide, and you can bet that tools like Composer and Node.js won't be on their radar.
- **Deployment options.** Our recommended strategy for deploying small- and medium-sized projects is via `git`. In contrast, shared hosting providers often only support FTP for transferring files from development to production. This means that whenever you want to update your live site, you need to either try to figure out which files have changed, or **delete your entire live codebase and reupload everything**! By deploying with git, you can use a simple `push` command to **automatically update** your live codebase.

## VPS hosting options

### DigitalOcean

DigitalOcean is a popular VPS hosting service that offers a flat monthly rate. They call their virtual machines "Droplets", which are priced based on memory, processing power, disk storage, and bandwidth. Each Droplet is essentially a dedicated IP address and computer that you have root access to, and on which you can install whatever operating system and software you like.

Their entry-level Droplets start at around $6/month (pricing as of 2026), which provides 1GB memory, 25GB storage, and 1TB bandwidth per month. This is sufficient to run a typical UserFrosting application.

> [!NOTE]
> Pricing and specifications may vary. Check [DigitalOcean's pricing page](https://www.digitalocean.com/pricing/droplets) for current offerings.

They also provide a convenient web-based control panel, which lets you perform some basic administrative tasks and monitor your Droplet's resource usage:

![Droplet control panel](/images/droplet.png)

### Promotions

DigitalOcean offers various discounts and promotions:

- **Students**: Get free credit through the [GitHub Student Developer Pack](https://education.github.com/pack) (requires `.edu` email)
- **New users**: Often receive credit via [referral links](https://m.do.co/c/833058cf3824) - this also supports UserFrosting!
