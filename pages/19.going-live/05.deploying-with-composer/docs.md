---
title: Deploying with Composer
metadata:
    description:
taxonomy:
    category: docs
---

>NOTE: This guide assumes the reader already have a fully functionnal server with ssh access. It also assume the reader is familiar with git and Github.

This recipe will guide you to create a git repository for your own Sprinkle and how you can deploy it on your production environment using Composer. This can be viewed as an alternate method of _going live_ on a server where you have the necessary tools to install UserFrosting (git, Composer, npm, node, etc.). One advantage of this method is easy setup and _super easy updates_ !

This method doesn't require you to have a git version of the main UserFrosting repository. Instead, it uses git to clone the latest UserFrosting version and **Composer** to manage your sprinkle, who is _stored_ in an independent git repository. Your sprinkle will then behave just like any Composer dependency. The magic behind this method comes from the integration of UserFrosting sprinkles with [Composer installers](https://github.com/Composer/installers). More info on this can be found on the [Community Sprinkles](/sprinkles/community#sprinkles-and-composer) page. 

For this guide, we will be using **Github** as our git provider. We'll first create a git repository for our sprinkle, configure said sprinkle to be able to install it with Composer and finally deploy a fresh UserFrosting installation with our Sprinkle.

## Push your sprinkle to Github

First thing is to do is create a new Github repository for our Sprinkle, if you don't already have one. Note that only your sprinkle's code should be in that repository. You don't need to add the core UserFrosting code in that repository. Just your code is enough. You can look at the [Community Sprinkles](https://github.com/search?q=topic%3Auserfrosting-sprinkle&type=Repositories) on Github if you want an example of what a sprinkle repository looks like. Note that your repository can be public or private. Just remember that private repository can't be added to Packagist (more on this later).

>>>>> For this example, we'll name our repository `userfrosting/pastries`. The name you give your sprinkle here doesn't matter and will be prefixed with your username. Just make sure to change the references to that name later when we use it !

Once you have your sprinkle repository sprinkle ready and committed your code to it, you'll be ready to configure your sprinkle and start deploying !

### The `composer.json` file

As stated in the [Sprinkles Chapter](/sprinkles/first-site#composer-json), each sprinkles requires a `composer.json` file. To be able to deploy your sprinkle using Composer, we need to make sure it follows some basic principles:

- Have the correct `type`
- Have the necessary `installer-name` (optional)
- Be a valid `composer.json` file

Let's look at our `userfrosting/pastries` Composer file :
```json
{
    "name": "userfrosting/pastries",
    "type": "userfrosting-sprinkle",
    "description": "Simple Pastries List for UserFrosting",
    "keywords": ["pastries", "sprinkle", "userfrosting"],
    "homepage": "https://github.com/userfrosting/pastries",
    "license" : "MIT",
    "autoload": {
        "psr-4": {
            "UserFrosting\\Sprinkle\\Pastries\\": "src/"
        }
    },
    "extra": {
        "installer-name": "pastries"
    }
}
```

So far, it's not so much different than a normal `composer.json` file. But the first element is really important. In the `type` section, you must use the `userfrosting-sprinkle` name. This is what tells **Composer Installer** that this Composer dependency is a Sprinkle and that it needs to install it in the sprinkle directory. Otherwise, your sprinkle will be installed in the `app/vendor` directory !

The `installer-name` part is also important if you want to install your sprinkle in a different directory than the repository name. For example, if your GitHub repository is named `Pastries_Sprinkle`, without the `installer-name` instruction, Composer would install your sprinkle package in `app/sprinkles/Pastries_Sprinkle`. The installer name allows you to specify a different name for the installation directory. 

## Deploying on a production server

Now that we have our Sprinkle ready for deployment via Composer, it's time to move to our staging or production server and install a fresh copy of UserFrosting. The instructions here are similar to the [Quick Start Guide](/). If you're not familiar with those instructions, it's still time to go back to [the beginning](/installation) !

### Base UserFrosting Installation

Using ssh, `cd` where you want to install UserFrosting and use git to clone the latest version of UserFrosting into a new `userfrosting` folder:

```bash
$ git clone https://github.com/userfrosting/UserFrosting.git userfrosting
```

### Adding your Sprinkle

At this point, we'll add our custom sprinkle to the system. Let's start by creating our `sprinkles.json` file. `cd` nto the newly created `userfrosting` folder and use nano (or whatever) to create a new empty file:

```bash
$ nano app/sprinkles.json
```

That file usually contains a list of all the sprinkles we want to load on our installation. However, it's actually a disguised `composer.json` file! This means, not only can we add our sprinkle to the list, we can use this file to ask Composer to clone it for us. Lets's look at the finalized content and then dive into each section:

```
{
    "require": {
        "userfrosting/pastries": "dev-master"
    },
    "repositories": [
        {
            "type": "git",
            "url":  "git@github.com:userfrosting/pastries.git"
        }
    ],
    "base": [
        "core",
        "account",
        "admin",
        "pastries"
    ]
}
```

>>> If you want to load multiple repository using this method, simply list all of your repository in each section.

#### require

`require` tells Composer to download your package and what version to use. Change `userfrosting/pastries` to your repository full name and keep `dev-master` as the version. This way, Composer will load the code from the `master` branch of your repository.

#### repositories

`repository` tells Composer where to find your package. Each repository here is defined using a `type` and `url`. The `type` instruct Composer on how to download to the repository and the `url` is the url of your repository. See Composer Documentation for more info on this. If your repository is on Github, just change the `userfrosting/pastries` part of the url to your repository full name.

>>>>> This is not required if you add your (public) repository to [Packagist](https://packagist.org). But at this point, it's not required. If you plan on distributing your Sprinkle, it may however be a good idea to do so.

#### base

`base` is our sprinkle list. Simply add your sprinkle name to the end of the list. Here we added `pastries`, but don't forget to change the name to whatever name you gave your sprinkle. This should be the same name as the one you defined in your sprinkle `composer.json` under `installer-name` !

### Finishing setup

Now that our sprinkle is ready to go, we continue the installation process like usual. First, we need to fetch the Composer dependencies:

```bash
$ composer install
```

Now run the final setup by calling the [Bakery CLI](/cli):

```bash
$ php bakery bake
``` 

This will run the installation process:

- Setting up your database credential in `app/.env`
- Checking for missing dependencies
- Running the database migration
- Building the raw assets

That's it! Your UserFrosting installation with your sprinkle should now be done!

>>>>>> This method can also be used with existing installation. Simply edit your `sprinkles.json` file and run `composer update` and `php bakery bake` to install your sprinkle.

## Updating

### Updating UserFrosting

To update an instance of UserFrosting installed with those instructions, simply run from the top directory `git pull`. Don't worry about Git complaining about your sprinkle not being in version control. That is totally normal. Don't forget to run `composer update` and `php bakery bake` afterward too !

### Updating your Sprinkle

To update your sprinkles installed using Composer, simply commit your changes to your Github repository and run `composer update`. Don't forget to run `php bakery bake` afterward too !

>>> This recipe was spronsored by @jy and [USOR](https://usorgames.com). Get in touch with the UserFrosting team if you want to sponsor your own receipe !
