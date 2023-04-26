---
title: Develop Locally, Serve Globally
metadata:
    description: The right way to approach development.
taxonomy:
    category: docs
---
[plugin:content-inject](/modular/_update5.0)

Just about every week, we see someone wander into [chat](https://chat.userfrosting.com) and ask:

> I'm having trouble getting UserFrosting to work on (insert some obscure web hosting service).  Can anyone help me?

The first thing I do is ask them - **have you been able to get it to work locally?**  More often than not, it turns out that they haven't even tried - in fact, they don't even have a local development environment set up!

If you're not sure what ["local development environment"](http://daraskolnick.com/developer-tip-tuesday-always-develop-locally/) means, then please continue reading.

#### Why you shouldn't test code on a live server

You don't need special hardware to run a web server.  PHP, Apache, nginx, MySQL - these are all free open-source projects that you can install and run on just about any computer.  When you pay your $60/year for Jimbo's Web Hosting, Inc., you're not paying for the software.  You're paying for storage and bandwidth on a machine that is (hopefully) running in a well-maintained data center, with 24/7 uptime.  Some of your money might also go to paying for basic sysadmin tasks, like updating software, maintaining mail servers, etc.

The 24/7 uptime is critical for your live application - no doubt about that.  But for **developing** your application, you don't need
to run and test it on a computer in a data center.  In fact, there are several reasons why this is a terrible idea:

1. Convenience.  I've seen people who write their code in a text editor or IDE, but then upload their code to their hosting service and refresh the page *at their live URL* to "test" their code.  This introduces an entire extra step into your development cycle.  And what a pain it is!  It's much easier if you can just test your code directly on your own machine.

2. Security.  There are all sorts of opportunities for PHP to inadvertently disclose sensitive data, like database passwords and API keys.  This is especially true if you are [dumping stack traces to the response](/background/security/server-misconfiguration), which many PHP installations and frameworks do by default!  Developing in a local environment that only you can access ensures that you'll have the opportunity to iron out these vulnerabilities before the application goes live.

Ultimately, you should not think of writing code as the only activity involved in building a website.  Making a website or web application really consists of two distinct phases: **development** and **production**, each of which should happen in their own separate environments and involve automated testing, managing dependencies, compiling assets, and a variety of other tasks.

In this same vein, any framework or CMS that has you do a "one-click install" is fundamentally framing the problem in the wrong way.  There is no such thing as "installing" a web application.  Rather, a web application is _developed_ and then _deployed_.  By missing this crucial distinction, you lose out on the ability to maintain your application and roll out changes in an organized, controlled, and sane way.

#### Setting up a local development environment

If you think that setting up a local environment is too much work, think again!

There are a number of "one-click" installers available, which can set up your machine with a complete web application stack in just a few minutes:

- [XAMPP](https://www.apachefriends.org/index.html) - a bundle that includes Apache, MariaDB, and PHP
- [MAMP/MAMP Pro](http://mamp.info)
- [WampServer](http://www.wampserver.com/en/)

#### Alternatives to installing a stack natively

If you don't have a laptop on which you can install a full solution stack natively, or you want to develop in an environment that **more closely resembles your production environment,** consider using [VirtualBox](https://www.virtualbox.org/manual/ch01.html).  VirtualBox allows you to run a complete virtual operating system (such as Ubuntu) from within any parent operating system.

If you install [Vagrant](https://www.vagrantup.com/) as well, you can use pre-built and pre-configured environments like [Homestead](https://laravel.com/docs/8.x/homestead), which comes with PHP, nginx, MariaDB, Composer, and Node.js pre-installed.  What's more, Vagrant can actually link up its virtual file system to your native OS's file system.  This makes it easy to write code on your "real" machine, and then **run** it in the virtual environment instantly without having to actually **copy** it over to the virtual machine.

To get started with VirtualBox, Vagrant, and Homestead, head over to our documentation on [development environments](/installation/environment/homestead).

#### Collaboration and deployment

Even after you have a local development environment set up, you'll still need a way to collaborate with the rest of your team and, when you're ready to launch, push your code to a live server.

It turns out that `git`, the popular version control software, can be used to do both!  For collaborating with your team, we suggest that you set up a free private repository on [Bitbucket](https://bitbucket.org/).  You should agree upon a proper [git flow](http://nvie.com/posts/a-successful-git-branching-model/), and each member of your team should have their own local development environment.  For more information, see the installation guide, [Essential Tools for Modern PHP](/installation/requirements/essential-tools-for-php).

When it comes time to actually deploy your application, you can [set up a special git remote on your live server](https://www.digitalocean.com/community/tutorials/how-to-set-up-automatic-deployment-with-git-with-a-vps).  Then, deploying new changes will be as simple as:

```bash
git push deploy master
```

For more about deployment, see the chapter on [Going Live](/going-live).
