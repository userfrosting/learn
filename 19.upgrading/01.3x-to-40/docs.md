---
title: 0.3.x to 4.1.x
metadata:
    description: 
taxonomy:
    category: docs
---

>>> To contribute to this documentation, please submit a pull request to our [learn repository](https://github.com/userfrosting/learn/tree/master/pages).

UserFrosting 4 represents a significant rewrite and redesign of the core codebase from previous versions.

## What's the same

UserFrosting's major third-party components have remained unchanged in UF4, and you will be able to apply most of what you learned in UF3.  For example, on the server side (PHP) we still use:

- Slim micro-framework;
- Eloquent ORM;
- Twig templating engine;
- PHPMailer

And on the client side (Javascript) we still use:

- HandlebarsJS;
- Tablesorter;
- Select2

## What's new

- The [Sprinkle](/sprinkles) system, which keeps your code completely separate from the core UF codebase;
- We're upgraded from Slim 2 to Slim 3, which is significantly different;
- Completely redesigned [database structure](/database/default-tables);
- Initialization is now achieved through [services](/services), with the Pimple dependency injection container;
- [Composer](/installation/requirements/essential-tools-for-php#composer) is now a mandatory part of the installation process;
- [Bower](/sprinkles/contents#-bower-json) is now used to install third-party client-side assets (Javascript/CSS packages);
- "Groups" and "Primary Group" have been replaced by "Roles" and "Group", respectively;
- Tables no longer need to be "registered" in any kind of initialization routine.  Simply set the table names directly in your data models;
- Twig templates have been [reorganized and redesigned](/templating-with-twig/sprinkle-templates);
- SB Admin has been removed and we now use the [AdminLTE](https://adminlte.io/) front-end theme;
- Client-side code has been heavily refactored into reusable [components](/client-side-code/components).

## Migrating

To upgrade from UserFrosting 0.3.x to UserFrosting 4, you'll need to migrate both your database and your code.

To begin, you should first [clone a fresh copy](/installation/environment/native#clone-the-userfrosting-repository) of the UserFrosting 4 codebase.  Once this is done, instead of the normal installation process (`bake`), we will install and run the [upgrade Sprinkle](https://github.com/userfrosting/upgrade), which uses a [custom Bakery command](https://github.com/userfrosting/upgrade/blob/master/src/Bakery/Upgrade.php) to help migrate data to the UF4 database structure.

Finally, you will need to gradually extract and refactor your code from your UserFrosting 0.3.x project into a UserFrosting 4 [Sprinkle](/sprinkles/first-site).

### Database

We now have an [upgrade Sprinkle](https://github.com/userfrosting/upgrade), which will help you migrate your users, groups, permissions, and event logs from a production UF3.1 database, to the UF4 database structure.  It will do this as an "in place" migration, which means that your legacy UF 0.3.x tables will be renamed, and the new UF4 tables will be created in the same database.  It will then attempt to create new records in the `users`, `roles`, `role_users`, `permissions`, `permission_roles`, and `activities` tables from the data in the legacy tables.

>>>> It is strongly recommended that you make a backup copy of your database, especially live production databases, before attempting the database upgrade.

#### Set up UserFrosting 4 and install the `upgrade` Sprinkle

Clone the UF4 repo and run `composer install`, as per the documentation.  Manually copy `app/sprinkles.example.json` to `app/sprinkles.json`.

Edit UserFrosting `app/sprinkles.json` and add the following to the `require` list : `"userfrosting/upgrade": "~4.1.0"`. Also add `upgrade` to the `base` list. For example:

```
{
    "require": {
        "userfrosting/upgrade": "~4.1.0"
    },
    "base": [
        "core",
        "account",
        "admin",
        "upgrade"
    ]
}
```

#### Update Composer

Run `composer update` from the root project directory.  This will use Composer to automatically download and install the `userfrosting/upgrade` Sprinkle in your project.

#### Test the upgrade tool on your development database

Run `php bakery upgrade` from the root project directory.  It will prompt you for the credentials for your database - use the credentials for the UF 0.3.x database you wish to upgrade.

You will then be asked a few questions about your legacy tables.  Answer these questions, and then the tool will attempt to complete the migration.

Check the migrated database, and make sure you are happy with the results.  You may need to modify the upgrade tool to make any necessary changes to your project-specific schema.  For example, if you had any custom columns in your UF 0.3.1 `user` table, you will want to modify the upgrade Sprinkle so that it copies values from these columns to a [separate auxiliary table](/recipes/extending-the-user-model) instead.

### Codebase

Migrating your code requires that you first develop a careful understand of how UF 4.1 works.  After creating a new Sprinkle, we recommend that you begin by migrating your model classes.

#### Data models

Since UserFrosting 4 still uses the Eloquent ORM, your models will not need that much modification.  Most notably, you will need to:

- Place your models in the `src/Database/Models` directory of your Sprinkle;
- Change the namespace of your models, as described [here](/recipes/advanced-tutorial/database#creating-a-data-model);
- Extend the `UserFrosting\Sprinkle\Core\Database\Models\Model` base model, instead of the old `UFModel`;
- Directly set the `$table` property in your model to the actual table names, rather than using the `$_table_id` property;
- Eliminate any references to ` Database::getSchemaTable`, and simply use the actual table name instead.

#### Routes and controllers

We recommend that you try to migrate one route/controller method at a time.

Best practices are now to completely [isolate your route definitions from their implementations](/routes-and-controllers/controller-classes).  Route definitions, which used to be defined in `public/index.php`, are now defined in your Sprinkle's `/routes` directory.  Note that the syntax for route definitions has changed in Slim 3, and that you no longer need to explicitly instantiate your controller classes.  Simply pass the fully qualified controller method in to your route definition, and Slim will automatically resolve it and run your controller code when the route is matched.

You'll also notice that in Slim 3, the methods for retrieving user input (query string (`$_GET`) parameters, request body (`$_POST`) parameters, etc) have changed considerably.  See [Client Input](/routes-and-controllers/client-input) for more information.

Other changes:

- Controller classes now belong in the `src/Controller/` directory of your Sprinkle;
- Controller namespaces must be changed to reflect the Sprinkle structure; see [this example](/routes-and-controllers/controller-classes#defining-controller-classes);
- Any code you had in the `ApiController` should now be refactored to use a [custom Sprunje](/database/data-sprunjing).  Place your api controller methods into the semantically appropriate controller classes.  For example, what used to be handled by `ApiController::listUsers` is now handled in `UserController::getList`.
- `$this->_app` has been replaced with the [dependency injection container](/services/default-services).  For example, instead of `$this->_app->config`, you would now do `$this->ci->config` in your controller methods.

#### Templates

You should migrate your templates to your Sprinkle one at a time as your migrate the routes/controllers that reference them.

Note that the UF 3.1 theming system has been removed.  Templates should simply go in the [corresponding subdirectory](/templating-with-twig/sprinkle-templates#template-organization) of your Sprinkle's `/templates` directory.  If you require user-specific themes, you can split your templates into several Sprinkles, and set the `theme` attribute of a user to the name of the Sprinkle that corresponds to their theme.

Please note that the structure of templates has also changed.  The base template for all other page templates is now `pages/abstract/base.html.twig`.  See [here](/templating-with-twig/sprinkle-templates#abstract-templates) to get an idea of which blocks to override in your page templates.

#### Client-side code

All assets (JS, CSS, images, etc) should be moved from your `public/` directory into your Sprinkle's `assets/` directory.  Create a `bower.json` and use it to manage your third-party CSS and Javascript libraries, rather than downloading them directly into your project.

You should factor out your client-side code into separate, page-specific `.js` files, rather than inlining it into your page template files.  Use the [asset manager](/asset-management/basic-usage) to reference Javascript files and bundles in your templates.

Where possible, rewrite your client-side code to take advantage of our built-in [components](/client-side-code/components), such as `ufForm`, `ufTable`, and `ufCollection`.

## Upgrading in production

Once you have finished rewriting your project for UserFrosting 4, and you have modified the `upgrade` Sprinkle as needed for any of your project-specific tables, you can deploy this to your production server as explained in the [Going Live](/going-live) chapter.  Again, instead of running `php bakery bake`, you will run `php bakery upgrade` to migrate your legacy tables to the new structure.  As we mentioned before, **BE SURE TO MAKE A BACKUP** of your production database before attempting to run the upgrade tool.
