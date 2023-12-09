---
title: Quick Start Guide
metadata:
    description: The official documentation for UserFrosting, a PHP framework and full-featured user management application.
taxonomy:
    category: docs
---

[notice=note]This quick start guide is aimed at experienced PHP developers who already have a development environment set up. If that's not your case, head over to the [First Chapter](/background) to start your journey.[/notice]

UserFrosting is a free, open-source jumping-off point for building user-centered web applications with PHP and Javascript. It comes with a sleek, modern interface, basic user account features, and an administrative user management system - all fully functioning out of the box.

[notice]This is the documentation for **UserFrosting 5**. If you are looking for documentation for _UserFrosting 4_, please see [here](https://learn.userfrosting.com/4.6/).[/notice]

## Requirements

[notice=tip]Using Docker? [Check out the Docker Documentation](/installation/environment/docker) to install UserFrosting through it's native Docker integration.[/notice]

UserFrosting has a few system requirements. You need to make sure your local UserFrosting development environment meets the following requirements:

- PHP **8.0 or higher** (*8.2* recommended)
- [Composer 2](https://getcomposer.org/)
- [Node.js](https://nodejs.org/en/) **18.0** or higher, and [npm](https://www.npmjs.com) **9.0** or higher

## Installing UserFrosting

Use Composer to create an empty project with the latest version of UserFrosting skeleton into a new `UserFrosting` folder. This will clone the skeleton repository and run the installation process.

```bash
composer create-project userfrosting/userfrosting UserFrosting "^5.0"
```

[notice=tip]During installation, you can choose **sqlite** as database provider if you don't have a database provider available.[/notice]

If any dependencies are not met, an error will occur. Simply try again after fixing said error, or manually run `composer install` and `php bakery bake` from the install directory. For more information about the `bake` command, head to the [Bakery CLI](/cli) chapter.

At this point you can run locally using the PHP Server : 

```bash
php -S localhost:8080 -t public
```

You can now access UserFrosting at : [http://localhost:8080](http://localhost:8080)

## Visit your website

At this point, you should be able to access your application. You should see a basic page:

![Basic front page of a UserFrosting installation](/images/front-page.png)

## What's next...

For more detailed information about installing UserFrosting, or if you need help with the basic setup requirements, check out the [Installation Chapter](/installation). Otherwise, head over to the [Sprinkles Chapter](/sprinkles).
