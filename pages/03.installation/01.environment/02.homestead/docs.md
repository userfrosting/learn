---
title: Homestead
metadata:
    description: 
taxonomy:
    category: docs
---

>>> If this is your first time installing UserFrosting or if you're not already familiar with setting up a local environment, this page will guide you in setting up your first local environment using Homestead. If you already have a local environment (e.g., LAMP or LEMP) and you're already familiar with **composer**, the [Native Installation](/installation/environment/native) guide is for you.

## Motivation

We get it - you just want to get UserFrosting up and running as quickly as possible.  Don't worry!  We'll get you there.

But before we do, it's very important that we clear up some common misconceptions first.

**"I need a web hosting service to run a web server"**

I think that part of the reason so many developers take the "code-upload-refresh" approach to web development, is because they don't actually know any better (apologies if you do - in that case, feel free to skip this section).

I often encounter developers who are under the mistaken impression that you need to _upload_ your code to a hosting company's server (often, via FTP) in order for it to work.  Somehow, magically, the hosting service is what makes it possible to run and test PHP code.  The concept of "web hosting" and "web server" have been conflated in their minds.

In actuality, "web server" and "web hosting" are not the same thing. A web server is nothing more than a piece of software that runs on a computer and listens for **HTTP requests**.  The most common web servers - Apache and Nginx - are completely free and open-source.  You can download and run them on your personal computer.  

When you sign up with a web hosting company, you're not paying for the web server software; you're paying to run a web server on _their computers_ instead of your own.  The reason that you need a web hosting service is **not** because they possess some magical ability to run PHP.  What you're paying for is:

- Hardware maintenance and reliable uptime
- Better network connections, superior bandwidth
- Static IP addresses
- Support services (backup, monitoring, etc)

These are important concerns - and part of the reason that most people don't run their web applications off of a home/office server - but they have nothing to do with the _development_ of your application.

**"It's easier to upload my code to the live server and test it there, instead of setting up a web server on my own computer."**

No.  In the long run, the amount of time you'll spend uploading your code after each change you make will **easily dwarf** the amount of time it takes to set up a working test server on your own computer.  If you don't like the idea of [installing a stack natively](/installation/environment/native), this guide gives you an alternative option that is easy and highly reliable.

Incidentally, when you run your code in a place that is only accessible to you, is exclusively for testing your work, and where it's ok (and expected) when something breaks, this is referred to as a **development environment.**  In contrast, when you put your code somewhere so that it is running live and interacting with real visitors to your site, this is called a **production environment.**

## Virtualbox, Vagrant and Homestead



### Setting up Homestead

Install [Vagrant](https://scotch.io/tutorials/get-vagrant-up-and-running-in-no-time) and [Homestead](https://scotch.io/tutorials/getting-started-with-laravel-homestead).

After you've set up Vagrant and Homestead, installation is the same as that for a [native environment](/installation/environment/native#GetUserFrosting).

To contribute to this documentation, please submit a pull request to our [learn repository](https://github.com/userfrosting/learn/tree/master/pages).


Make sure that the directories you map in `Homestead.yaml` exist _before_ you run `vagrant up`.  Otherwise, you will need to reload your virtual machine using `vagrant reload --provision` so that Homestead has a chance to find your directories.

Install VirtualBox 5.1.18