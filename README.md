# learn.userfrosting.com

https://learn.userfrosting.com

This is the repository for the documentation for UserFrosting 4.  It is built with the flat-file CMS [Grav](http://getgrav.org), using their [RTFM skeleton](https://github.com/getgrav/grav-skeleton-rtfm-site#rtfm-skeleton).

## Getting started

This application uses the [Grav](https://learn.getgrav.org/) CMS.  This repository does not contain a full Grav installation - rather, it just contains the contents of Grav's `user` directory, which is where all of our custom content, themes, and assets live.  This was done as per the [recommendation on Grav's blog](https://getgrav.org/blog/developing-with-github-part-2), to make it easier to deploy changes to the live server.

In terms of actually getting it running, you can opt for a local installation, or utilise a containerised VPS solution like Docker.

### Local installation

#### Step 1 - Install Grav

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

To finish the install off, just run:

```bash
bin/grav install
```

#### Step 2

Grav needs your webserver to be able to write to certain directories.  In OSX with XAMPP installed, this won't work by default.  To deal with this:

Add default webserver user `daemon` to OSX's `staff` group (which already has the necessary permissions for writing to files/directories):

```bash
sudo dseditgroup -o edit -a daemon -t user staff
```

#### Step 3

Install plugins and base theme. The base theme is learn2. The plugins each have empty directories in the plugins directory.

```bash
bin/gpm install -y error problems breadcrumbs anchors highlight simplesearch learn2
```

### Docker

Most docker images (like the one used here) automate the installation of Grav. So for the most part, getting started with Docker is less tedious. Instead the tediousness is at the end due to a bug in Grav.

### Step 1

We start off by cloning this repo.

```bash
git clone https://github.com/userfrosting/learn.git userfrosting-learn
```

### Step 2

Then we start the image, with the appropraite configuration.

```bash
docker pull ahumaro/grav-php-nginx
docker run -d -i -p 80:80 -p 2222:22 -v "$(pwd):/usr/share/nginx/html/user/" --name ufLearn ahumaro/grav-php-nginx
```

### Step 3

Install plugins and base theme. The base theme is learn2. The plugins each have empty directories in the plugins directory.

```bash
docker exec -it ufLearn bash
chmod +x bin/gpm # This is only needed if permissions are acting up
bin/gpm install -y error problems breadcrumbs anchors highlight simplesearch learn2
```

NOTE: Grav uses `rename` when moving plugins to their final destination, which means this is where everything falls apart. The issue is that `rename` doesn't work to well when crossing a drive boundary (even for emulated drives), throwing a "Invalid cross-device link" error when attempted. Until a fix is out, you'll need to install the theme and plugins manually under docker.

## Credits

Favicons were generated with https://realfavicongenerator.net/

