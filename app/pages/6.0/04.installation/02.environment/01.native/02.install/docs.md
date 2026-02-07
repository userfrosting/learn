---
title: Installing UserFrosting
description: Getting UserFrosting up and running in your development environment.
---

Now that your local development environment is setup and ready to go, it's finally time to download and access your first UserFrosting application for the first time !

## Clone the UserFrosting repository

Use Composer to create an empty project with the latest version of UserFrosting skeleton into a new `UserFrosting` folder:

<!-- TODO : UPDATE FOR OFFICIAL RELEASE -->
```bash
composer create-project userfrosting/userfrosting UserFrosting "^6.0" --stability=beta
```

> [!TIP]
> Note the `UserFrosting` at the end of the command. This means `composer` will create new `UserFrosting` subdirectory inside the current location. You can change `UserFrosting` to whatever you like.

This may take some time to complete. If Composer has completed successfully, you should see that a `vendor/` directory has been created. This `vendor/` directory contains all of UserFrosting's PHP dependencies - there should be nearly 30 subdirectories in here! 

Next the **Bakery** will execute it's magic. You'll have to answer some questions, which will guide you into the configuration process. These will help you set up the **database** credentials, create the first **admin user** and install the third-party **assets**.

> [!NOTE]
> If any error is encountered at this point, in the main project directory, run `php bakery bake` to re-attempt the installation.

You will first be prompted for your database credentials. This is the information PHP needs to connect to your database. If you followed our guide to setup, you can select **SQLite** and the default path when asked. If PHP can't connect to your database using these credentials, make sure you have entered the right information and re-run the `bake` command.

Bakery will also prompt you for SMTP credentials, so that UserFrosting can send emails for activating new accounts and setting and resetting passwords. 
If you're using **Mailpit** or **Mailtrap**, you can select "SMTP" and enter the appropriate info. If you are not ready to set up email at this time, you can choose _No email support_ to skip SMTP configuration, however you'll get error when trying to register a new account. 

If the database connection is successful, the installer will then check that the basic dependencies are met. If so, the installer will run the _migrations_ to populate your database with new tables. During this process, you will be prompted for some information to set up the master account (first user). Finally, the installer will run the command to fetch javascript dependencies and build the assets.

> [!TIP]
> Composer `create-project` command is an umbrella command which run the following commands. You can still run them following manually if you want, or to debug any issue :
> 1. Run `git clone` (from the UserFrosting repo)
> 2. Run `composer install`
> 3. Run `php bakery bake`

## Serve and visit your website

UserFrosting requires two servers to run locally: the PHP backend server and the Vite development server for frontend assets.

**Terminal 1 - Start the PHP server:**

```bash
php bakery serve
```

This starts the backend on [http://localhost:8080](http://localhost:8080).

**Terminal 2 - Start the Vite dev server:**

```bash
npm run dev
```

This starts the Vite development server with Hot Module Replacement (HMR) for instant frontend updates.

> [!TIP]
> You can use the `==> Serve` VS Code task to start both servers simultaneously if you're using VS Code.

You can now access UserFrosting at [http://localhost:8080](http://localhost:8080). You should see the default UserFrosting pages and login with the newly created master account. 

![Basic front page of a UserFrosting installation](images/front-page.png)

> [!TIP]
> To stop either server, hit `ctrl+c` in its respective terminal.

## Star the project

It will help us a lot if you could star [the UserFrosting project on GitHub](https://github.com/userfrosting/UserFrosting). Just look for the button in the upper right-hand corner!

[![How to star](images/how-to-star.png)](https://github.com/userfrosting/UserFrosting)

Congratulations! Now that this is complete, you're ready to start developing your application. Learn more about [Sprinkles](sprinkles) to understand how to structure and extend your UserFrosting application.
