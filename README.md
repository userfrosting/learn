# UserFrosting Documentation

https://learn.userfrosting.com

This is the repository for the documentation for UserFrosting 5. It is built with the flat-file CMS [Grav](http://getgrav.org), using their [RTFM skeleton](https://github.com/getgrav/grav-skeleton-rtfm-site#rtfm-skeleton).

## Getting started

This site is built using [Grav](https://learn.getgrav.org/) CMS, which like UserFrosting v4 has combined framework and project skeleton code. To permit easier content management, this repository only includes Grav's `user/` directory, which is where all of our custom content, themes, and assets live. See [Grav Development with GitHub - Part 2](https://getgrav.org/blog/developing-with-github-part-2) for more on this approach.

## Syntax

Grav uses Markdown to format text. Additionally, this site uses the [Grav Shortcode Core Plugin](https://github.com/getgrav/grav-plugin-shortcode-core) plugin to enable more non-standard Markdown code, like `[notice]`.

See [Grav Shortcode Plugin](https://github.com/getgrav/grav-plugin-shortcode-core?tab=readme-ov-file#available-shortcodes) for a list of available markdown code.

## Installation

### Complete Local Installation

**This branch contains the documentation associated with the branch of the same name from [`userfrosting/userfrosting`](https://github.com/userfrosting/UserFrosting).** [See Website Installation Guide](https://github.com/userfrosting/learn/blob/website/README.md#getting-started) to install a complete instance of the docs with all versions available. 

To install a single copy of this branch (without multiple versions available), multiple installation methods are available:

1. [Local Installation](#local-installation)
2. [Docker](#docker)

### Local Installation

This guide does not cover setting up the webserver and assumes your host is already setup.

1. Install Grav and replace the `user` directory with this repository
   ```
   git clone https://github.com/getgrav/grav.git userfrosting-learn
   cd userfrosting-learn
   rm -r user
   git clone https://github.com/userfrosting/learn.git user
   ```

2. Install Grav
   ```
   bin/grav install
   ```

When you're done, the directory structure should look like this:

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

### Docker

1. Clone repo
   ```
   git clone https://github.com/userfrosting/learn.git userfrosting-learn
   cd userfrosting-learn
   ```

2. Build Grav container
   ```
   docker build -t learn:latest .
   ```

3. Start Grav container
   ```
   docker run -d --rm --name=learn -p 8888:80 -v "$(pwd):/var/www/grav/user" learn:latest
   ```

It will take a couples of second for the site to be up and running while the base Grav installation is done. Once this is complete, you can access the documentation at [http://localhost:8888/](http://localhost:8888/).

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

## Credits

Favicons were generated with https://realfavicongenerator.net/
