---
title: Basic CLI Usage
description: Essential Bakery commands to get started with UserFrosting development.
---

Now that you have UserFrosting installed, it's important to understand how to use **Bakery**, the command-line interface (CLI) tool. You'll rely on Bakery throughout your development workflow—from starting the development server to managing your database and clearing caches. This page covers the essential commands you'll use most frequently. For a complete reference of all available commands and how to create your own, see the [Bakery CLI](/cli/) chapter.

> [!NOTE]
> All Bakery commands are executed in your **terminal** or **command prompt**—the same one you used during installation. Make sure you're in your project's root directory before running any commands.

## Running Commands

All Bakery commands are run from your project's root directory using:

```bash
php bakery <command>
```

You can see a list of all available commands by running:

```bash
php bakery list
```

For help with a specific command:

```bash
php bakery help <command>
```

## Essential Commands

### Starting the Development Server

The `serve` command starts UserFrosting's built-in development server:

```bash
php bakery serve
```

By default, this starts the server at `http://localhost:8080`. You can specify a different host and port:

```bash
php bakery serve --host=0.0.0.0 --port=8000
```

> [!WARNING]
> The built-in server is for **development only**. Never use it in production. See the [Going Live](/going-live/) chapter for production deployment options.

### Initial Setup

When setting up a new project or after pulling updates, run the `bake` command to execute all setup tasks:

```bash
php bakery bake
```

This single command runs multiple setup operations:
- Clears the cache
- Runs database migrations
- Compiles frontend assets
- Sets up configuration files (on first run)

For a fresh installation, you can also use the interactive setup wizard:

```bash
php bakery setup
```

This guides you through:
1. Environment configuration (.env file)
2. Database connection
3. SMTP configuration (optional)
4. Running migrations
5. Creating the root user account

### Database Migrations

Run pending database migrations without the full bake process:

```bash
php bakery migrate
```

To see which migrations have been run or are pending:

```bash
php bakery migrate:status
```

### Clearing the Cache

When you make configuration changes or template updates, clear the cache:

```bash
php bakery clear-cache
```

### Debugging Information

Get helpful debugging information about your UserFrosting installation:

```bash
php bakery debug
```

This shows:
- UserFrosting version
- PHP version and configuration
- Database connection status
- Loaded sprinkles
- Environment settings

## What's Next?

These commands will get you through most day-to-day development work. As you build your application, you'll discover many more commands for:

- Creating and managing users
- Running database seeders
- Testing email configuration
- Building and watching assets
- Creating custom commands for your own tasks

For a complete reference and advanced features, see the [Bakery CLI](/cli/) chapter.
