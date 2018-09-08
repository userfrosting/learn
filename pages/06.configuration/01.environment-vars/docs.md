---
title: Environment Variables
metadata:
    description: The .env file is used to define important values in development such as database credentials, which should be placed directly in environment variables during production.
taxonomy:
    category: docs
---

The basic database settings for UserFrosting can be set through environment variables. By default, UserFrosting looks for the following environment variables:

- `DB_NAME`: The name of the database you just created
- `DB_USER`: The database user account
- `DB_PASSWORD`: The database user password

If you don't want to (or can't) configure environment variables directly in your development environment, UserFrosting uses the fantastic [phpdotenv](https://github.com/vlucas/phpdotenv) library to let you set these variables in a `.env` file. When running the `bake` installer, this file will be created for you. To make any modifications, your can run the following **Bakery** command:

```bash
$ php bakery setup
```

You can also edit the `.env` file manually. Simply copy the sample file in your `app/` directory:

```bash
$ cp app/.env.example app/.env
```

Now, you can set values in the `.env` file and UserFrosting will pick them up _as if_ they were actual environment variables.

You may also want to configure your SMTP server settings as well at this point so that you can use features that require mail, such as password reset and email verification. See [Chapter 14](/mail) for more information on the mail service.
