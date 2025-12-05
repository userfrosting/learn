---
title: Docker
metadata:
  description: Docker is a containerization platform that helps maintain consistent behavior across different development and production environments.
taxonomy:
  category: docs
---

If you don't already have a local environment or you're not familiar with **Docker**, this page will guide you in installing UserFrosting using Docker.

Docker provides a great starting point for building a UserFrosting application using PHP, NGINX, and MySQL without requiring prior Docker experience. All the necessary tools will be available through Docker. The only necessary tool required on your computer, besides Docker, is the **command line**.

If you're familiar with PHP development, or already have PHP installed locally, you may instead want to consider setting up [natively](/installation/environment/native).

## Command Line Interface

[plugin:content-inject](/04.installation/_modular/cli)

## Install Docker
First, you'll need to install Docker. Just follow the installation instructions from the Docker website:
- [Mac](https://docs.docker.com/desktop/install/mac-install/)
- [Windows (via WSL2)](https://docs.docker.com/desktop/install/windows-install/)
- [Linux](https://docs.docker.com/desktop/install/linux-install/)

## Get UserFrosting 

For the next part, you'll need to use the command line. We'll use Composer (through a Docker image) to create an empty project, with the latest version of the UserFrosting skeleton, into a new `UserFrosting` subdirectory:

```bash
docker run --rm -it -v "$(pwd):/app" composer create-project userfrosting/userfrosting UserFrosting "^5.1" --no-scripts --no-install --ignore-platform-reqs
```

> [!TIP]
> Note the `UserFrosting` in the command. This means Composer will create a new `UserFrosting/` subdirectory inside the current location. You may change `UserFrosting` to anything you like.

## Build Containers & Setup UserFrosting

Now it's simply a matter of navigating to the directory containing the source code you just downloaded, building the containers, starting them, then installing UserFrosting. 

1. Navigate to the directory:
   
   ```bash
   cd UserFrosting
   ```

   > [!TIP]
> If you customized `UserFrosting` in the previous command, don't forget to change it in the command above.

2. Build each of the Docker Containers (this might take a while):
   
   ```bash
   docker-compose build --no-cache
   ```

3. Start each Docker Container:
   
   ```bash
   docker-compose up -d
   ```

4. Set some directory permissions (your may have to enter your root password):
   
   ```bash
   sudo touch app/logs/userfrosting.log
   sudo chown -R $USER: .
   sudo chmod 777 app/{logs,cache,sessions}
   ```

5. Install PHP dependencies:
   
   ```bash
   docker-compose exec app composer update
   ```

6. Install UserFrosting (database configuration and migrations, creation of admin user, etc.). You'll need to provide info to create the admin user:
   
   ```bash
   docker-compose exec app php bakery bake
   ```

Now visit [http://localhost:8080](http://localhost:8080) to see your UserFrosting homepage!

You should see the default UserFrosting pages and be able to login with the newly created master account. 

![Basic front page of a UserFrosting installation](/images/front-page.png)

To stop the containers, run : 

```bash
docker-compose stop
```

## Mailpit

You can see captured email at [http://localhost:8025](http://localhost:8025).

UserFrosting's default `docker-compose.yml` file contains a service entry for [Mailpit](https://github.com/axllent/mailpit). Mailpit intercepts emails sent by your application during local development and provides a convenient web interface so that you can preview your email messages in your browser. 

While UserFrosting is running, you may access the Mailpit web interface at: [http://localhost:8025](http://localhost:8025).

## Working with UserFrosting

Every Bakery command needs to be wrapped in Docker Compose syntax, since you need to run these commands in the containers, not your computer.

For example:

```bash
docker-compose exec app php bakery ...
```

## Working with the Containers

If you need to stop the UserFrosting docker containers, just change to your userfrosting directory and run:

```bash
docker-compose stop
```

To start containers again, change to your userfrosting directory and run:

```bash
docker-compose up -d
```

If you need to purge your docker containers (this will not delete any source file or sprinkle, but will empty the database), run:

```bash
docker-compose down --remove-orphans
```

And then start the installation process again.

## Advanced configuration

At the heart of everything is the `docker-compose.yml` file. If you're experienced with Docker and Docker Compose, this is where you can customize your Docker experience. For example, you can customize the port each service runs on. And since the file is located in *your Sprinkle*, aka your app, it's possible to save this file in your repo.

The `docker-compose.yml` file also contains the MySQL database and Mail environment variables. Since these variables are defined globally inside the container, they don't need to be redefined inside the `.env` file.

> [!WARNING]
> If you have **two** instances of UserFrosting on your computer, **they will share the same config**. This means a couple of things:
> 1. You can't run both Docker instances of UserFrosting *at the same time* with the default config, as ports will overlap.
> 2. Both instances will share the same database.
> If you wish to run multiple instances of UserFrosting on the same computer with Docker, you must edit the `docker-compose.yml` of all but one instance and change the default ports and database volumes/database names.

> [!NOTE]
> An "*address already in use*" error can be thrown if a port defined in `docker-compose.yml` is already used on your system. For example, if Mailpit is installed locally and running on the default port, you'll get an "address already in use" error when running Docker. This can be solved by changing the port in `docker-compose.yml`.

## Production environment

**This is not (yet) meant for production!**

You may be tempted to run with this in production, but this setup has not been security-hardened. For example:

- The database is exposed on port 8593 so you can access MySQL using your favorite client at localhost:8593. However,
  the way Docker exposes ports bypasses common firewalls like `ufw`. This should not be exposed in production.
- Database credentials are hard-coded — obviously not secure.
- File permissions may be more open than necessary.
- HTTPS is not implemented.
- It just hasn't been thoroughly tested in the capacity of being a production system.

If you're experienced with Docker in a production environment, don't hesitate to reach out and contribute to this documentation.
