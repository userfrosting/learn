---
title: Docker
metadata:
  description: Docker is a containerization platform that helps maintain consistent behavior across different development and production environments.
taxonomy:
  category: docs
---

[notice=warning]Docker support is currently at **experimental** stability. Expect quirks and documentation gaps.

To contribute to this documentation, please submit a pull request to our [learn repository](https://github.com/userfrosting/learn/tree/master/pages).[/notice]

The relevant files for installing UserFrosting with Docker are `docker-compose.yml`, and the contents of `docker/`.

First, install [Docker Compose](https://docs.docker.com/compose/install/).

Second, initialize a new UserFrosting project:

1. Clone UserFrost git repository with `git clone https://github.com/userfrosting/UserFrosting.git userfrosting`
2. Change to the new directory `cd userfrosting`
3. Copy `app/sprinkles.example.json` to `app/sprinkles.json`
4. Copy `app/.env.example` to `app/.env`
5. Run `chmod 777 app/{logs,cache,sessions}` to fix file permissions for web server. (NOTE: File
   permissions should be properly secured in a production environment!)
6. Run `docker-compose build --no-cache` to build all the docker containers.
7. Run `docker-compose up -d` to to start all the containers.
8. Run `docker-compose exec app sh -c "composer update"` to install all composer modules used in UserFrosting.
9. Run `docker-compose exec app sh -c "php bakery bake"` to install UserFrosting (database configuration and migrations, creation of admin user, ...). You'll need to provide info to create the admin user.

Now visit `http://localhost:8591/` to see your UserFrosting homepage!

**You can paste these into a bash file and execute it!**

```bash
git clone https://github.com/userfrosting/UserFrosting.git userfrosting
cd userfrosting
cp app/sprinkles.example.json app/sprinkles.json
cp app/.env.example app/.env
chmod 777 app/{logs,cache,sessions}
docker-compose build --no-cache
docker-compose up -d
docker-compose exec app sh -c "composer update"
docker-compose exec app sh -c "php bakery bake"
```

**Start / stop containers**

If you need to stop the UserFrosting docker containers, just change to your userfrosting directory and run:

`docker-compose stop`

To start containers again, change to your userfrosting directory and run:

`docker-compose up -d`

**Purge docker containers to start over**

If you need to purge your docker containers (this will not delete any source file or sprinkle, but will empty the database), run:

```bash
docker-compose down --remove-orphans
```

And then start the installation process again.

**This is not (yet) meant for production!**

You may be tempted to run with this in production but this setup has not been security-hardened. For example:

- Database is exposed on port 8593 so you can access MySQL using your favorite client at localhost:8593. However,
  the way Docker exposes this actually bypasses common firewalls like `ufw` so this should not be exposed in production.
- Database credentials are hard-coded so obviously not secure.
- File permissions may be more open than necessary.
- HTTPS not implemented fully
- It just hasn't been thoroughly tested in the capacity of being a production system.
