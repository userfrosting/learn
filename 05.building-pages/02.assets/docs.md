---
title: Assets
metadata:
    description: Assets consist of the Javascript files, CSS files, image files, fonts, and other resources used to create the client-side experience of your web application.  UserFrosting provides a framework for loading these resources easily and efficiently.
taxonomy:
    category: docs
---

The Javascript files, CSS files, image files, fonts, and other resources used to create the client-side experience of your web application are collectively known as **assets**.  In UserFrosting, assets are kept under the `assets/` subdirectories of each Sprinkle.

When a user loads a page of your website in their browser, it includes a number of `<link ...>`, `<img ...>`, `<script ...>` and other tags that tell their browser how to fetch these additional resources from the server.

When dealing with assets on the server, our application needs to address two problems:

1. How do we **generate appropriate URLs** for these resources, and inject the appropriate reference tags into the pages that we return to the client?
2. When the client actually loads a page and **requests** these resources, how do we find them on the server and return them to the client?

## Referencing Assets

### Asset Bundles

Most CSS and Javascript resources should be integrated into your pages through **asset bundles**.  Asset bundles are groups of assets for which UserFrosting can automatically render `<link>` or `<script>` tags in your pages, using the `assets` Twig helper.  Asset bundles are defined in `build/bundle.config.json`.  UserFrosting ships with a number of predefined bundles.  If you look in your `bundle.config.json` file, you will see, for example:

```
{
  "bundle": {
    "js/main": {
        "scripts": [
            "vendor/bootstrap-3.3.6/js/bootstrap.js",
            "vendor/handlebars-1.2.0/handlebars.js",
            "vendor/jqueryValidation-1.14.0/jquery.validate.js",
            "vendor/jqueryValidation-1.14.0/additional-methods.js",
            "js/fortress-jqueryvalidation-methods.js",
            "js/uf-jqueryvalidation-config.js",
            "js/uf-alerts.js",
            "js/uf-form.js",
            "js/uf-modal.js"
        ],
        "options": {
            "result": {
                "type": {
                  "scripts": "plain"
                }
            }
        }
    },
    ...
```

Under `bundle` you will notice the name of the bundle (`js/main`), and then a list of paths to bundle assets.  When the appropriate reference tags for the assets are rendered, UserFrosting will look in the most recently loaded Sprinkle's `/assets` directory and search back through the stack until it finds a match.  Thus, you can override an asset in `core`, for example, by redefining it with the same relative path and filename in your `site` Sprinkle.

#### Javascript Bundles

By convention, Javascript bundles should be named with the `js/` prefix.  The assets for a Javascript bundle must be defined under the `scripts` key in your bundle.

#### CSS Bundles
 
By convention, CSS bundles should be named with the `css/` prefix.  The assets for a Javascript bundle must be defined under the `styles` key in your bundle.

>>>> Generally speaking, it is a good idea to define your Javascript and CSS resources in separate bundles.  The `options` key in both types of bundles is required, and it tells [gulp-bundle-assets](https://github.com/dowjones/gulp-bundle-assets) how to construct the `build/bundle.result.json` file for linking to [compiled assets](#compiled-assets).

#### Rendering Bundles

To render a bundle on a page, simply use the `assets.js()` and `assets.css()` Twig helpers:

```
{{ assets.js('js/main') | raw }}
```

UserFrosting will automatically generate the `<script>` tags for Javascript bundles, or `<link>` tags for CSS bundles, when it renders the template:

```
<script src="http://localhost/myUserFrostingProject/public/assets-raw/core/assets/vendor/bootstrap-3.3.6/js/bootstrap.js" ></script>
<script src="http://localhost/myUserFrostingProject/public/assets-raw/core/assets/vendor/handlebars-1.2.0/handlebars.js" ></script>
<script src="http://localhost/myUserFrostingProject/public/assets-raw/core/assets/vendor/jqueryValidation-1.14.0/jquery.validate.js" ></script>
<script src="http://localhost/myUserFrostingProject/public/assets-raw/core/assets/vendor/jqueryValidation-1.14.0/additional-methods.js" ></script>
<script src="http://localhost/myUserFrostingProject/public/assets-raw/core/assets/js/fortress-jqueryvalidation-methods.js" ></script>
<script src="http://localhost/myUserFrostingProject/public/assets-raw/core/assets/js/uf-jqueryvalidation-config.js" ></script>
<script src="http://localhost/myUserFrostingProject/public/assets-raw/core/assets/js/uf-alerts.js" ></script>
<script src="http://localhost/myUserFrostingProject/public/assets-raw/core/assets/js/uf-form.js" ></script>
<script src="http://localhost/myUserFrostingProject/public/assets-raw/core/assets/js/uf-modal.js" ></script>
```

#### Template Blocks for Placing Assets

You can use the `assets.css()` and `assets.js()` helpers anywhere in a Twig template, of course, but best practice dictates that CSS links should go in the `<head>` element of your page, and Javascript tags should go just at the end of your `<body>` element.

To facilitate placement of CSS and Javascript tags, the base layout template `layouts/basics.html.twig` defines a number of template blocks.  For CSS, these blocks are:

```
{% block stylesheets %}
    {# Override this block in a child layout template or page template to override site-level stylesheets. #}
    {% block stylesheets_site %}
        <!-- Include main CSS asset bundle -->
        {{ assets.css() | raw }}
    {% endblock %}

    {# Override this block in a child layout template or page template to specify or override stylesheets for groups of similar pages. #}
    {% block stylesheets_page_group %}
    {% endblock %}

    {# Override this block in a child layout template or page template to specify or override page-level stylesheets. #}
    {% block stylesheets_page %}
    {% endblock %}
{% endblock %}
```

Similarly, for Javascript assets, we have:

```
{% block scripts %}
    {# Override this block in a child layout template or page template to override site-level scripts. #}
    {% block scripts_site %}
        <!-- Load jQuery -->
        <script src="//code.jquery.com/jquery-2.2.4.min.js" ></script>
        <!-- Fallback if CDN is unavailable -->
        <script>window.jQuery || document.write('<script src="{{ assets.url('assets://jquery-2.2.4/jquery.min.js', true) }}"><\/script>')</script>

        {{ assets.js() | raw }}
    {% endblock %}

    {# Override this block in a child layout template or page template to specify or override scripts for groups of similar pages. #}
    {% block scripts_page_group %}
    {% endblock %}

    {# Override this block in a child layout template or page template to specify or override page-level scripts. #}
    {% block scripts_page %}
    {% endblock %}
{% endblock %}
```

The main idea is for each page to include no more than three different bundles of each type - a **sitewide bundle**, containing assets that every page on your site uses; a **page group bundle**, to share assets among sets of similar pages; and a **page-specific bundle**, for assets that are specific enough to only be needed on one page.

>>>>>> You may want to create a child layout that extends `layouts/basic.html.twig` for pages that share a common asset bundle.  In your child layout, you can inject page group asset bundles by defining the `stylesheets_page_group` and `scripts_page_group` bundles.

### Unbundled Assets

Not all assets can be bundled.  Images, for example, are often referenced using an `<img>` tag.  To automatically build a url for a single asset in a Twig template, you may use the `assets.url()` helper.  This helper takes a file path to an asset, and generates an appropriate absolute url:

```
<img src="{{ assets.url('assets://images/stuck.jpg') }}">
```

You'll notice that we reference the file path using the `assets://` **stream wrapper**.  [Stream wrappers](http://php.net/manual/en/intro.stream.php) allow UserFrosting to define a sort of virtual mini-filesystem for a particular type of resource (in this case, assets).

When we refer to an asset using the path `assets://images/stuck.jpg`, UserFrosting will search through each loaded Sprinkle's `assets/` directory, starting with the most recently loaded Sprinkle, looking for a relative match to `images/stuck.jpg`.  Again, as with other sorts of entities, this allows us to override assets from previously loaded Sprinkles.

## Asset Retrieval

Once a url for an asset has been generated, our web server will need to correctly interpret any requests made for the asset at that url, and return the contents of the file to the client.

In a more minimalistic setup, this process is fairly straightforward.  We might just keep all of our Javascript files in a `js/` directory directly under our public document root directory.  Then the URL is simply `http://example.com/js/whatever.js`, and our webserver matches the _URL path_ `/js/whatever.js` to the _filesystem_ path `/path/to/document/root/js/whatever.js`, and places the contents of that file in the HTTP response.  In most web servers this happens so transparently, that a lot of new developers assume that they're somehow giving direct access to the server's file system.  In reality the web server is mediating the interaction, and generating an HTTP response using the _contents_ of these files.

### Raw Assets

In UserFrosting, the Sprinkle system makes this a little more complicated.  Each Sprinkle can contribute its own assets to the application (under its `assets/` subdirectory), and it should be possible for a Sprinkle to override assets in another Sprinkle that was loaded earlier in the stack.

Configuring the web server to handle all of the extra logic would be tedious, error-prone, and could easily introduce security risks.  For these reasons, UserFrosting has the ability to serve assets through the application, rather than relying on the web server to handle these requests directly.  By default, all requests made to URLs beginning with `http://localhost/myUserFrostingProject/public/assets-raw/` will be sent to a special route defined in the core Sprinkle.  This route then uses the [`assetLoader` service](/services/default-services#assetloader) to resolve the request to an asset in a Sprinkle.

For example, `http://localhost/myUserFrostingProject/public/assets-raw/core/assets/vendor/bootstrap-3.3.6/css/bootstrap.css` will be resolved to the `core` Sprinkle, and respond with the contents of `vendor/bootstrap-3.3.6/css/bootstrap.css` (if it exists).

>>> The `assetLoader` service will automatically try to determine the MIME type of the asset based on the file extension, and set the appropriate `Content-Type` header in the response.

### Compiled Assets

You may be thinking "won't raw assets add a lot of overhead and slow down my application?"  If so, you would be absolutely, 100% correct.  This is why raw assets are only meant to be used in **development**.  When you're ready to deploy your application to the live server, one of the tasks that must be done is to **compile** your assets and asset bundles.

Compiling assets basically does three main things:

1. It copies assets from individual Sprinkles to the public web directory, so that they can be served directly by the web server instead of having to go through UserFrosting;
2. It [minifies](https://en.wikipedia.org/wiki/Minification_(programming)) CSS and Javascript assets, so that they are smaller and can be loaded more quickly by the client;
3. It concatenates assets within each asset bundle into a single file, reducing the number of requests that the client needs to make.  Again, this makes the page load more quickly for the client.

To accomplish this, we will use a suite of Javascript-based tools which help automate the process.

#### Node.js

All of the tools we use are based on [Node.js](https://nodejs.org/en/).  Why?  Since every web application needs to minify and process assets, it makes sense to develop the tools for these tasks in a common language.  No matter which server-side technology a developer uses, they all have to deal with Javascript sooner or later.  Thus, Javascript-based tools are the most popular and actively developed and maintained options.

The first thing you'll need to do is [get Node.js and npm (the Node package manager) installed](/basics/requirements/essential-tools-for-php#nodejs).  Even though we'll be using these tools to get our application ready for deployment, you don't actually need to install Node.js on your live server.  You can install it locally, perform your build tasks, and then push the built application to the live server afterwards.

#### Gulp

Gulp is a tool used to automate Javascript tasks.  The basic idea is that you pass your file(s) through a number of plugins, each of which can perform some kind of transformation on your data.  Thus, the output from one plugin becomes the input to the next.

To install Gulp, you can use `npm`:

```bash
npm install --global gulp-cli
```

#### Install Required Node Modules

Once Node (npm) and Gulp are installed, we can install the packages necessary for the build script which is responsible for compiling our assets.

npm is to Node what Composer is to PHP.  And, just like Composer has `composer.json`, npm has `package.json`.  You will notice a preconfigured `package.json` file in the `/build` directory.  To install the required packages for our build script, simply run `npm install` in `/build`.

You can safely exclude the `node_modules` directory from your repository, even if you plan to use git to push your project to production.  These node modules are only used for environment build tasks and are not used by the application itself.

#### Running the Build Task

All build tasks are defined in `build/gulpfile.js`.  UserFrosting ships with two preconfigured tasks, `build` and `copy`.

The `build` task uses [`gulp-bundle-assets`](https://github.com/dowjones/gulp-bundle-assets) to minify and concatenate the assets referenced in each bundle in `bundle.config.json` into a single file per bundle.  These compiled bundles will be placed in the `public/assets/` directory by default.

The `copy` task copies fonts, images, and other files from your Sprinkles to the `public/assets/` directory, so that your web server can directly serve these files as well.  At the moment, you will need to set up a separate command to copy each Sprinkle's images and fonts.  The `copy` task is configured to automatically run when `build` is run.

To run the build task, simply run `gulp build` from the command line, in the `build/` directory.
  
#### Using Compiled Assets

Once the compiled asset files have been generated, we can easily configure the asset manager to substitute the urls for raw assets in our pages with urls for compiled assets.  Simply set the configuration value for `assets.use_raw` to `false`.

If you reload your page and view the source, you'll see that references to the compiled assets are now being used instead:

```html
<!-- Include main CSS asset bundle -->
<link rel="stylesheet" type="text/css" href="http://localhost/myUserFrostingProject/public/assets/css/main-2c1912c984.css" >

<!-- Page-group-specific CSS asset bundle -->
<link rel="stylesheet" type="text/css" href="http://localhost/myUserFrostingProject/public/assets/css/guest-5a16771b5a.css" >
```

The `AssetManager` pulls the names of these compiled assets from `build/bundle.result.json`, which was generated when we ran the gulp `build` task.
