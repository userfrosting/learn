---
title: Compiled Assets
metadata:
    description: Asset bundles should be compiled in production to allow for faster and more efficient transfer to the client.
taxonomy:
    category: docs
---

In a more minimalistic setup, asset retrieval is fairly straightforward.  We might just keep all of our Javascript files in a `js/` directory directly under our public document root directory.  Then the URL is simply `http://example.com/js/whatever.js`, and our webserver matches the _URL path_ `/js/whatever.js` to the _filesystem_ path `/path/to/document/root/js/whatever.js`, and places the contents of that file in the HTTP response.  In most web servers this happens so transparently, that a lot of new developers assume that they're somehow giving direct access to the server's file system.  In reality the web server is mediating the interaction, and generating an HTTP response using the _contents_ of these files.

In UserFrosting, the Sprinkle system makes this a little more complicated.  Each Sprinkle can contribute its own assets to the application (under its `assets/` subdirectory), and it should be possible for a Sprinkle to override assets in another Sprinkle that was loaded earlier in the stack.

Configuring the web server to handle all of the extra logic would be tedious, error-prone, and could easily introduce security risks.  For these reasons, UserFrosting has the ability to [serve assets through the application](/building-pages/assets/basic-usage#public-asset-urls), rather than relying on the web server to handle these requests directly.

## Compiled Assets

You may be thinking "won't raw assets add a lot of overhead and slow down my application?"  If so, you would be absolutely, 100% correct.  This is why raw assets are only meant to be used in **development**.  When you're ready to deploy your application to the live server, one of the tasks that must be done is to **compile** your assets and asset bundles.

Compiling assets basically does three main things:

1. It copies assets from individual Sprinkles to the public web directory, so that they can be served directly by the web server instead of having to go through UserFrosting;
2. It [minifies](https://en.wikipedia.org/wiki/Minification_(programming)) CSS and Javascript assets, so that they are smaller and can be loaded more quickly by the client;
3. It concatenates assets within each asset bundle into a single file, reducing the number of requests that the client needs to make.  Again, this makes the page load more quickly for the client.

To accomplish this, we will use a suite of Javascript-based tools which help automate the process.

### Node.js

All of the tools we use are based on [Node.js](https://nodejs.org/en/).  Why?  Since every web application needs to minify and process assets, it makes sense to develop the tools for these tasks in a common language.  No matter which server-side technology a developer uses, they all have to deal with Javascript sooner or later.  Thus, Javascript-based tools are the most popular and actively developed and maintained options.

Fortunately for you, you should already have Node.js installed if you completed the UserFrosting installation process successfully!

### Gulp

Gulp is a tool used to automate Javascript tasks.  The basic idea is that you pass your file(s) through a number of plugins, each of which can perform some kind of transformation on your data.  Thus, the output from one plugin becomes the input to the next.

Gulp should have been automatically installed for you during the [installation process](/basics/installation#npm-dependencies).

#### Running the Build Task

All build tasks are defined in `build/gulpfile.js`.  UserFrosting ships with four preconfigured tasks for building assets:
    
1. `uf-bundle-build`
2. `uf-bundle`
3. `uf-assets-copy`
4. `uf-bundle-clean`

The `uf-bundle-build` task combines the `bundle.config.json` files in each loaded Sprinkle (as per your `sprinkles.json` file), respecting the collision rules defined in each bundle.  This combined bundle file is written to `build/bundle.config.json`.

The `uf-bundle` task uses [`gulp-bundle-assets`](https://github.com/dowjones/gulp-bundle-assets) to minify and concatenate the assets referenced in each bundle in `build/bundle.config.json` into a single file per bundle.  These compiled bundles will be placed in the `public/assets/` directory by default.

The `uf-assets-copy` task copies fonts, images, and other files from your Sprinkles to the `public/assets/` directory, so that your web server can directly serve these files as well. 

To run these commands, simply run `npm run <command name>` in your `build/` directory.
  
#### Using Compiled Assets

Once the compiled asset files have been generated, we can easily configure the asset manager to substitute the urls for raw assets in our pages with urls for compiled assets.  Simply set the configuration value for `assets.use_raw` to `false`.

If you reload your page and view the source, you'll see that references to the compiled assets are now being used instead:

```html
<!-- Include main CSS asset bundle -->
<link rel="stylesheet" type="text/css" href="http://localhost/myUserFrostingProject/public/assets/css/main-2c1912c984.css" >

<!-- Page-group-specific CSS asset bundle -->
<link rel="stylesheet" type="text/css" href="http://localhost/myUserFrostingProject/public/assets/css/guest-5a16771b5a.css" >
```

The `AssetManager` pulls the names of these compiled assets from `build/bundle.result.json`, which was generated when we ran the `uf-bundle` task.
