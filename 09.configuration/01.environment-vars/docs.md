---
title: Environment Variables
metadata:
    description: The .env file is used to define important values in development such as database credentials, which should be placed directly in environment variables during production.
taxonomy:
    category: docs
---

The basic database settings for UserFrosting can be set through environment variables. By default, UserFrosting looks for the following environment variables:

|       Variable       | Description                                                                             |
|:--------------------:|-----------------------------------------------------------------------------------------|
|      `UF_MODE`       | The current [environment Modes](/configuration/config-files#environment-modes)          |
|    `CSRF_ENABLED`    | The global switch for the [CSRF Guard](/routes-and-controllers/client-input/csrf-guard) |
|   `DB_CONNECTION`    | The database connection to use                                                          |
| `DB_TEST_CONNECTION` | The database connection to use in the test environment                                  |
|     `DB_DRIVER`      | The database driver to use (choice of `mysql`, `pgsql`, `sqlite` or `sqlsrv`)           |
|      `DB_HOST`       | The database host (ie.: localhost)                                                      |
|      `DB_PORT`       | The database port                                                                       |
|      `DB_NAME`       | The name of the database to use for this install                                        |
|      `DB_USER`       | The database user account                                                               |
|    `DB_PASSWORD`     | The database user password                                                              |
|    `MAIL_MAILER`     | Set to one of 'smtp', 'mail', 'qmail', 'sendmail'                                       |
| `MAIL_FROM_ADDRESS`  | Outgoing emails are sent from this address                                               |
|   `MAIL_FROM_NAME`   | Outgoing emails are sent with this name                                                  |
|     `SMTP_HOST`      | SMTP server host used to send emails                                                    |
|     `SMTP_USER`      | SMTP server user used to send emails                                                    |
|   `SMTP_PASSWORD`    | SMTP server user password used to send emails                                           |
|     `SMTP_PORT`      | SMTP server port                                                                        |
|     `SMTP_AUTH`      | SMTP server authentication enabled (true or false)                                      |
|    `SMTP_SECURE`     | Enable TLS encryption. Set to `tls`, `ssl` or `false` (to disable)                     |

If you don't want to (or can't) configure environment variables directly in your development environment, UserFrosting uses the fantastic [phpdotenv](https://github.com/vlucas/phpdotenv) library to let you set these variables in a `.env` file. When running the `bake` installer, this file will be created for you. To make any modifications, your can run the following **Bakery** helper command:

```bash
$ php bakery setup
```

You can also edit the `.env` file manually. Simply copy the sample file in your `app/` directory:

```bash
$ cp app/.env.example app/.env
```

Now, you can set values in the `.env` file and UserFrosting will pick them up _as if_ they were actual environment variables.

You may also want to configure your SMTP server settings as well at this point so that you can use features that require mail, such as password reset and email verification. See [Chapter 16](/mail) for more information on the mail service.
