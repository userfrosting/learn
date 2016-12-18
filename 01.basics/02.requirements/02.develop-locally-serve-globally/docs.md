---
title: Develop Locally, Serve Globally
description: The right way to approach development.
taxonomy:
    category: docs
---

Just about every week, we see someone wander into [chat](https://chat.userfrosting.com) and ask:

> I'm having trouble getting UserFrosting to work on (insert some obscure web hosting service).  Can anyone help me?

The first thing I do is ask them - **have you been able to get it to work locally?**  More often than not, it turns out that they haven't even tried - in fact, they don't even have a local development environment set up!

If you're not sure what ["local development environment"](http://daraskolnick.com/developer-tip-tuesday-always-develop-locally/) means, then please continue reading.

#### Why you shouldn't test code on a live server 

You don't need special hardware to run a web server.  PHP, Apache, nginx, MySQL - these are all free open-source projects that you can install and run on just about any computer.  When you pay your $60/year for Jimbo's Web Hosting, Inc., you're not paying for the software.  You're paying for storage and bandwidth on a machine that is (hopefully) running in a well-maintained data center, with 24/7 uptime.  Some of your money might also go to paying for basic sysadmin tasks, like updating software, maintaining mail servers, etc.

The 24/7 uptime is critical for your live application - no doubt about that.  But for **developing** your application, you don't need 
to run and test it on a computer in a data center.  In fact, there are several reasons why this is a terrible idea:

1. Convenience.  I've seen people who write their code in a text editor or IDE, but then upload their code to their hosting service and refresh the page *at their live URL* to "test" their code.  This introduces an entire extra step into your development cycle.  And what a pain it is!  It's much easier if you can just test your code directly on your own machine.

2. Security.  There are all sorts of opportunities for PHP to inadvertently disclose sensitive data, like database passwords and API keys.  This is especially true if you are [developing with `display_errors` enabled](/background/server-side), which many PHP installations do by default!  Developing in a local environment that only you can access ensures that you'll have the opportunity to iron out these vulnerabilities before the application goes live.

Ultimately, you should not think of writing code as the only activity involved in building a website.  Making a website or web application really consists of two distinct phases: **development** and **deployment**, each of which should happen in their own separate environments and involve automated testing, managing dependencies, compiling assets, and a variety of other tasks.

In this same vein, any framework or CMS that has you do a "one-click install" is fundamentally framing the problem in the wrong way.  There is no such thing as "installing" a web application.  Rather, a web application is _developed_ and then _deployed_.  By missing this crucial distinction, you lose out on the ability to maintain your application and roll out changes in an organized, controlled, and sane way.

#### Setting up a local development environment

If you think that setting up a local environment is too much work, think again!

There are a number of "one-click" installers available, which can set up your machine with a complete web application stack in just a few minutes:

- [XAMPP](https://www.apachefriends.org/index.html) - the easiest way to get started with Apache, MariaDB, and PHP (recommended for UserFrosting)
- [MAMP/MAMP Pro](http://mamp.info)
- [WampServer](http://www.wampserver.com/en/)

#### Alternatives to installing a stack natively

If you don't have a laptop on which you can install a full solution stack natively, you might want to consider looking into [VirtualBox](https://www.virtualbox.org/manual/ch01.html).  VirtualBox allows you to run a complete virtual operating system (such as Ubuntu) from within any parent operating system.  You could even use [Portable VirtualBox](http://www.vbox.me/), a wrapper for VirtualBox, to run an entire operating system off a USB flash drive!

Once you have your VirtualBox set up, you can install a [LAMP](https://www.digitalocean.com/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu-16-04) or [LEMP](https://www.digitalocean.com/community/tutorials/how-to-install-linux-nginx-mysql-php-lemp-stack-on-ubuntu-14-04) stack to run your application.

#### Collaboration and deployment

Even after you have a local development environment set up, you'll still need a way to collaborate with the rest of your team and, when you're ready to launch, push your code to a live server.

It turns out that `git`, the popular version control software, can be used to do both!  For collaborating with your team, we suggest that you set up a free private repository on [Bitbucket](https://bitbucket.org/).  You should agree upon a proper [git flow](http://nvie.com/posts/a-successful-git-branching-model/), and each member of your team should have their own local development environment.  For more information, see the next section, ["Essential Tools for Modern PHP"](/basics/requirements/essential-tools-for-php).

When it comes time to actually deploy your application, you can [set up a special git remote on your live server](https://www.digitalocean.com/community/tutorials/how-to-set-up-automatic-deployment-with-git-with-a-vps).  Then, deploying new changes will be as simple as:

```
git push deploy master
```

For more about deployment, see [Section 13.3](/going-live/deployment).