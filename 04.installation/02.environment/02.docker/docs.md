---
title: Docker
metadata:
  description: Docker is a containerization platform that helps maintain consistent behavior across different development and production environments.
taxonomy:
  category: docs
---

If you don't already have a local environment or you're familiar with **Docker**, this page will guide you in installing UserFrosting using Docker.

Docker provides a great starting point for building a UserFrosting application using PHP, NGINX ans MySQL without requiring prior Docker experience. All the necessary tools will be available through Docker. The only necessary tool required on your computer here, beside Docker, is the **command line**. 

Note if you're familiar with PHP development or already have PHP installed locally, you may instead want to consider setting up [natively](/installation/environment/native).

## Command Line Interface

[plugin:content-inject](/04.installation/_modular/cli)

## Install Docker
First, you'll need to install Docker. Just follow the installation instructions from the Docker Website: 
- [Mac](https://docs.docker.com/desktop/install/mac-install/)
- [Windows (via WSL2)](https://docs.docker.com/desktop/install/windows-install/)
- [Linux](https://docs.docker.com/desktop/install/linux-install/)

## Get UserFrosting 

For the next part, you'll need to use to command line. We'll use Composer through a Docker image to create an empty project with the latest version of UserFrosting skeleton into a new `UserFrosting` folder:

```bash
docker run --rm -it -v "$(pwd):/app" composer create-project userfrosting/userfrosting UserFrosting "^5.0@dev" --no-scripts --no-install --ignore-platform-reqs
```

[notice=tip]Note the `UserFrosting` in the command. This means Composer will create new `UserFrosting` subdirectory inside the current location. You can change `UserFrosting` to whatever you like.[/notice]

## Build Containers & Setup UserFrosting

Now it's simply a matter of navigating to the directory containing the source code you just downloaded, building the containers, starting them, and install UserFrosting. 

1. Navigate to the directory
   
   ```bash
   cd UserFrosting
   ```

   [notice=tip]If you customized the `UserFrosting` in the previous command, don't forget to change it in the command above.[/notice]

2. Build each the Docker Containers (this might take a while):
   
   ```bash
   docker-compose build
   ```

3. Start each Docker Containers:
   
   ```bash
   docker-compose up -d
   ```

4. Install PHP dependencies:
   
   ```bash
   docker-compose exec app sh -c "composer update"
   ```

5. Install UserFrosting (database configuration and migrations, creation of admin user, ...). You'll need to provide info to create the admin user:
   
   ```bash
   docker-compose exec app sh -c "php bakery bake"
   ```

Now visit [http://localhost:8080](http://localhost:8080) to see your UserFrosting homepage!

You should see the default UserFrosting pages and login with the newly created master account. 

![Basic front page of a UserFrosting installation](/images/front-page.png)

To stop the containers, run : 

```bash
docker-compose stop
```

## Mailpit

You can see the captured email at [http://localhost:8025](http://localhost:8025).

UserFrosting's default `docker-compose.yml` file contains a service entry for [Mailpit](https://github.com/axllent/mailpit). Mailpit intercepts emails sent by your application during local development and provides a convenient web interface so that you can preview your email messages in your browser. 

When UserFrosting is running, you may access the Mailpit web interface at: [http://localhost:8025](http://localhost:8025).

## Executing Commands

### Working with the Containers

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

### Working with UserFrosting

Every Bakery command need to be wrapped in docker-compose syntax, since you need to run theses commands on the containers, not your computer.

For example : 
```bash
docker-compose exec app sh -c "php bakery ..."
```

<!-- TODO : Complete this -->

## Production environnement

**This is not (yet) meant for production!**

You may be tempted to run with this in production but this setup has not been security-hardened. For example:

- Database is exposed on port 8593 so you can access MySQL using your favorite client at localhost:8593. However,
  the way Docker exposes this actually bypasses common firewalls like `ufw` so this should not be exposed in production.
- Database credentials are hard-coded so obviously not secure.
- File permissions may be more open than necessary.
- HTTPS not implemented fully
- It just hasn't been thoroughly tested in the capacity of being a production system.

## Advanced configuration

<!-- At its heart, Sail is the docker-compose.yml file and the sail script that is stored at the root of your project -->

<!-- If you're experienced with Docker and Docker compose,  -->

-- TODO --
