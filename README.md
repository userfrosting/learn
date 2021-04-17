# UserFrosting Documentation

https://learn.userfrosting.com

This is the repository for the documentation for UserFrosting 4.  It is built with the flat-file CMS [Grav](http://getgrav.org), using their [RTFM skeleton](https://github.com/getgrav/grav-skeleton-rtfm-site#rtfm-skeleton).

## Getting Started

This site is built using [Grav](https://learn.getgrav.org/) CMS, which like UserFrosting v4 has combined framework and project skeleton code. To permit easier content management, this repository only includes Grav's `user` directory, which is where all of our custom content, themes, and assets live. See [Grav Development with GitHub - Part 2](https://getgrav.org/blog/developing-with-github-part-2) for more on this approach.

This unique structure does complicate local hosting somewhat. Lando is recommended, however local installation and Docker are also documented.

### Lando

1. Clone repo
   ```
   git clone -b website https://github.com/userfrosting/learn.git userfrosting-learn
   cd userfrosting-learn
   ```

2. Prepare multisite
   ```
   git submodule update --init
   ```

3. Start Lando
   ```
   lando start
   ```

### Local Installation

This guide does not cover setting up the webserver and assumes XAMPP is being used.

1. Install Grav and replace the `user` directory with this repository
   ```
   git clone https://github.com/getgrav/grav.git userfrosting-learn
   cd userfrosting-learn
   rm -r user
   git clone -b website https://github.com/userfrosting/learn.git user
   ```

2. Correct permissions (MacOS with XAMPP)
   ```
   sudo dseditgroup -o edit -a daemon -t user staff
   ```

3. Setup Grav multisite
   ```
   cp user/setup.php setup.php
   cd user/
   git submodule update --init
   cd ../
   ```

4. Install Grav
   ```
   bin/grav install
   ```


### Docker

Most docker images (like the one used here) automate the installation of Grav. So for the most part, getting started with Docker is less tedious. Instead the tediousness is at the end due to a bug in Grav.

1. Clone repo
   ```
   git clone -b website https://github.com/userfrosting/learn.git userfrosting-learn
   cd userfrosting-learn
   ```

2. Start Grav container
   ```
   docker pull ahumaro/grav-php-nginx
   docker run -d -i -p 80:80 -p 2222:22 -v "$(pwd):/usr/share/nginx/html/user/" --name ufLearn ahumaro/grav-php-nginx
   ```

3. TODO Multisite

4. Install Grav
   ```
   docker exec -it ufLearn bash
   chmod +x bin/gpm # This is only needed if permissions are acting up
   bin/grav install
   ```

   NOTE: Grav uses `rename` when moving plugins to their final destination, which means this is where everything falls apart. The issue is that `rename` doesn't work to well when crossing a drive boundary (even for emulated drives), throwing a "Invalid cross-device link" error when attempted. Until a fix is out, you'll need to install the theme and plugins manually under docker.

## Adding a new documentation version

Move to the `user/` directory, and add the desired branch as a new git submodule. Replace `{brancheName}` with the name of the branch you want to include.

```bash
git submodule add -b {brancheName} https://github.com/userfrosting/learn.git sites/{brancheName}
```

Next edit `user/config/versions.yaml` to add your new branch/version to the dropdown list.

## Updating submodules / doc pages

To update pages from the legacy version to their latest version, run from the `user/` directory :

```bash
cd user/
git submodule update --remote --merge
```

## Credits

Favicons were generated with https://realfavicongenerator.net/
