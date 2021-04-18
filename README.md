# UserFrosting Documentation

https://learn.userfrosting.com

This is the repository for the documentation for UserFrosting 4. It is built with the flat-file CMS [Grav](http://getgrav.org), using their [RTFM skeleton](https://github.com/getgrav/grav-skeleton-rtfm-site#rtfm-skeleton). 

## Getting Started

This site is built using [Grav](https://learn.getgrav.org/) CMS, which like UserFrosting v4 has combined framework and project skeleton code. To permit easier content management, this repository only includes Grav's `user/` directory, which is where all of our custom content, themes, and assets live. See [Grav Development with GitHub - Part 2](https://getgrav.org/blog/developing-with-github-part-2) for more on this approach.

This branch contains the site with documentation for every versions of UserFrosting, as available at [https://learn.userfrosting.com](https://learn.userfrosting.com). This particular setup is made possible using [Grav Multisite Setup feature](https://learn.getgrav.org/17/advanced/multisite-setup) and each branch of this repo setup as git submodules in the `sites/` directory. 

Submodules in this branch are kept automatically to the latest version using [GitHub Actions Workflow](https://github.com/userfrosting/learn/actions/workflows/website.yml).

## Installation

This unique structure does complicate local hosting somewhat complicated, but other installation methods are also available:

1. [Local Installation](#local-installation)
2. [Lando](#lando)
3. [Docker](#docker)

### Local Installation

This guide does not cover setting up the webserver and assumes your host is already setup.

1. Install Grav and replace the `user` directory with this repository
   ```
   git clone https://github.com/getgrav/grav.git userfrosting-learn
   cd userfrosting-learn
   rm -r user
   git clone -b website https://github.com/userfrosting/learn.git user
   ```

2. Setup Grav multisite
   ```
   cp user/setup.php setup.php
   cd user/
   git submodule update --init
   cd ../
   ```

3. Install Grav
   ```
   bin/grav install
   ```

### Lando

1. Clone repo
   ```
   git clone -b website https://github.com/userfrosting/learn.git userfrosting-learn
   cd userfrosting-learn
   ```

2. Prepare multisite / submodules
   ```
   git submodule update --init
   ```

3. Start Lando
   ```
   lando start
   ```

To stop the container:

```bash
lando stop
```

### Docker

1. Clone repo
   ```
   git clone -b website https://github.com/userfrosting/learn.git userfrosting-learn
   cd userfrosting-learn
   ```

2. Build Grav container
   ```
   docker build -t learn:latest .
   ```

3. Start Grav container
   ```
   docker run -d --rm --name=learn -p 8080:80 -v "$(pwd):/var/www/grav/user" learn:latest
   ```

It will take a couples of second for the site to be up and running while the base Grav installation is done. Once this is complete, you can access the documentation at [http://localhost:8080/](http://localhost:8080/).

To stop the container:

```bash
docker stop learn
```

To access Grav command line utility or gpm, you can use :

```bash
docker exec -it learn bash
chmod +x bin/gpm # This is only needed if permissions are acting up
bin/grav install
```

## Adding a new documentation version

Move to Grav's `user/` directory, and add the desired branch as a new git submodule. Replace `{brancheName}` with the name of the branch you want to include.

```bash
git submodule add -b {brancheName} https://github.com/userfrosting/learn.git sites/{brancheName}
```

Next edit `config/versions.yaml` from this branch to add your new branch/version to the dropdown list.

## Updating submodules / doc pages

To update pages from the legacy version to their latest version, run from Grav's `user/` directory :

```bash
git submodule update --remote --merge
```

## Credits

Favicons were generated with https://realfavicongenerator.net/
