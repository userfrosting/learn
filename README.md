# UserFrosting Documentation

https://learn.userfrosting.com

This is the repository for the documentation for UserFrosting 4.  It is built with the flat-file CMS [Grav](http://getgrav.org), using their [RTFM skeleton](https://github.com/getgrav/grav-skeleton-rtfm-site#rtfm-skeleton).

## Getting started

This application uses the [Grav](https://learn.getgrav.org/) CMS.  This repository does not contain a full Grav installation - rather, it just contains the contents of Grav's `user` directory, which is where all of our custom content, themes, and assets live.  This was done as per the [recommendation on Grav's blog](https://getgrav.org/blog/developing-with-github-part-2), to make it easier to deploy changes to the live server.

In terms of actually getting it running, you can opt for a local installation, or utilise a containerised VPS solution like Docker.

## Complete Local Installation

[See Website Installation Guide](https://github.com/userfrosting/learn/blob/website/README.md#getting-started) to install a complete instance of the docs all versions available.

## Local installation

This method will only install the version contained within this branch. To install the full documentation, see the [Complete Local Installation](#complete-local-installation).

### Step 1 - Install Grav

To install this website on your computer, first [install grav core](https://getgrav.org/downloads) in a project folder called `userfrosting-learn` under your webserver's document root folder. Then, find the `user` folder inside of your project folder.  Delete the contents of the `user` folder and clone this repository directly into the user folder.

```bash
git clone https://github.com/getgrav/grav.git userfrosting-learn
cd userfrosting-learn
rm -r user
git clone https://github.com/userfrosting/learn.git user
```

When you're done it should look like this:

```
htdocs/
└── userfrosting-learn/
   ├── assets/
   ├── ...
   ├── user/
       ├── .git
       ├── accounts/
       ├── assets/
       ├── config/
       └── ...
   └── ...
```

### Step 2 - Setup permission (MacOS)

Grav needs your webserver to be able to write to certain directories.  In MacOS with XAMPP installed, this won't work by default.  To deal with this, add default webserver user `daemon` to MacOS's `staff` group (which already has the necessary permissions for writing to files/directories):

```bash
sudo dseditgroup -o edit -a daemon -t user staff
```

### Step 3 - Install Grav

To finish Grav install, just run:

```bash
bin/grav install
```

## Docker Installation

Most docker images (like the one used here) automate the installation of Grav. So for the most part, getting started with Docker is less tedious.

### Step 1

We start off by cloning this repo.

```bash
git clone https://github.com/userfrosting/learn.git userfrosting-learn
```

### Step 2

From the newly cloned folder, we can build the image and start it, with the appropriate configuration.

```bash
docker build -t learn:latest .
docker run -d --name=learn -p 8080:80 -v "$(pwd):/var/www/grav/user" learn:latest
```

It will take a couples of second for the site to be up and running while the base Grav installation is setup. Once this is done, you can access the documentation at [http://localhost:8080/](http://localhost:8080/).

To stop the image:

```bash
docker stop learn
```

To access Grav command line utility or gpm, use :

```bash
docker exec -it ufLearn bash
chmod +x bin/gpm # This is only needed if permissions are acting up
bin/grav install
```

## Credits

Favicons were generated with https://realfavicongenerator.net/
