---
title: Webpack Encore
metadata:
    description: Asset bundles should be compiled in production to allow for faster and more efficient transfer to the client.
taxonomy:
    category: docs
---

In a minimalistic setup, asset retrieval is fairly straightforward. We might just keep all of our Javascript files in a `js/` directory directly under our public document root directory. Then the URL is simply `http://example.com/js/whatever.js`, the web server matches the _URL path_ `/js/whatever.js` to the _filesystem_ path `/path/to/document/root/public/js/whatever.js` and finally places the contents of that file in the HTTP response. In most web servers, this happens so transparently that a lot of new developers assume that they're somehow giving direct access to the server's file system. In reality the web server is mediating the interaction, and generating an HTTP response using the _contents_ of these files.

In UserFrosting, the Sprinkle system makes this a little more complicated. Each Sprinkle can contribute their own assets to the application (typically under a `app/assets/` subdirectory), and it should be possible for a Sprinkle to override assets in another Sprinkle that was loaded earlier in the stack. Plus, since the Sprinkle is not part of our code, but loaded as an **npm** dependency, the assets can be hard to locate.

Modern frameworks also complicate things. For example, [Vue.js](https://vuejs.org), [React](https://react.dev), [Sass](https://sass-lang.com) and [Less](https://lesscss.org) requires a bundling or compilation step. Finally, third party dependencies can be managed through npm, similar to how Composer manage PHP dependencies, and are not known to UserFrosting PHP backend.

For these reasons, UserFrosting uses **Symfony's [Webpack Encore](https://github.com/symfony/webpack-encore)** to handle all frontend related task. Webpack Encore is a simpler way to integrate [Webpack](https://webpack.js.org). It provides a clean & powerful API for bundling JavaScript modules, pre-processing CSS & JS and compiling and minifying assets, and provides the necessary integration in the Twig template system.

Compiling assets through Webpack Encore basically does :

1. It copies statics assets from individual Sprinkles to the public web directory, so that they can be served directly by the web server instead of having to go through UserFrosting;
2. It uses entry files to bundle together assets for a specific use into a single file, reducing the number of requests the browser needs to make. This makes the page load more quickly for the client;
3. It can process Sass or Less files into a compiled css file;
4. It can build Vue and React files;
5. It [minifies](https://en.wikipedia.org/wiki/Minification_(programming)) CSS and Javascript assets, so that they are smaller and can be loaded more quickly by the browser;
6. In production, it enables versioning of each assets file, to better manage caching by the browser;
7. And so much more.

## Npm and `packages.json`

Webpack Encore, as with most frontend dependencies, is installed by [npm](https://www.npmjs.com). Fortunately for you, you should already have Node.js and npm installed if you completed the UserFrosting installation process successfully!

> [!NOTE]
> Why do we use Node.js, anyway, instead of a PHP-based asset management tools?
> Because over time, npm imposed itself as the defacto manager for javascript based project. Since most frontend packages relies on Javascript, and with the arrival of more complex framework, it makes sense to use tools for these tasks in a common language. No matter which server-side technology a developer uses, they all have to deal with Javascript sooner or later. Thus, Javascript-based tools are the most popular and actively developed and maintained options.

Where Composer has it's `composer.json` file to defines the project dependencies, npm has the `package.json` file. The default UserFrosting `package.json` looks like this : 

```json
{
    "dependencies": {
        "@userfrosting/sprinkle-admin": "~5.1.0",
        "@userfrosting/theme-adminlte": "~5.1.0"
    },
    "devDependencies": {
        "@symfony/webpack-encore": "^4.4.0",
        "file-loader": "^6.2.0",
        "sass": "^1.51.0",
        "webpack-notifier": "^1.14.1"
    },
    "scripts": {
        "dev-server": "encore dev-server",
        "dev": "encore dev",
        "watch": "encore dev --watch",
        "build": "encore production --progress"
    }
}
```

**Dependencies** are the your app frontend dependencies, which are will eventually be passed to the browser. For example, _JQuery_ can be a dependencies, as it will be used by the browser. **devDependencies** are CLI dependencies. They are required to run the building process. Finally, **scripts** are npm command alias.

In the example above, you can see both the *Admin Sprinkle* and *AdminLTE Theme* are included as dependencies. While theses are also included by Composer, it's necessary to also include them here, because their assets will be accessed by npm and Webpack Encore command, and npm commands, written in JavaScript can't access Composer PHP dependencies. The dev dependencies includes, among others, Webpack Encore. Of course, each of these dependencies also have their own "sub dependencies", just like with Composer. All of these dependencies will be installed when running `npm install`, `php bakery assets:build` or `php bakery assets:install`. Finally, the *scripts* section expose the encore build tasks.

Dependencies installation will be automatically handled by the `php bakery bake` or `php bakery assets:build` commands. Alternatively, you can still uses the typical npm commands from the command line:

```bash
$ npm install

# or

$ npm update
```

The same goes for installing you own dependencies, which should be done using npm command directly. For example : 

```bash
npm i bootstrap --save
```

> [!NOTE]
> As with Composer, `npm update` should be run after any changes made manually to `package.json`

## Webpack Encore configuration

Webpack Encore configuration can be found in `/webpack.config.js`. The default configuration found in the _UserFrosting Skeleton Repo_ is pretty self explanatory, so be sure to check it out. For more information about Encore Configuration, check out the [Symfony Documentation](https://symfony.com/doc/current/frontend.html#webpack-encore).

The default configuration looks similar to this. features can be enabled or disabled by commenting/uncommenting lines, or adding new configuration.

```js
const Encore = require('@symfony/webpack-encore');

// List dependent sprinkles and local entries files
const sprinkles = {
    AdminLTE: require('@userfrosting/theme-adminlte/webpack.entries'),
    Admin: require('@userfrosting/sprinkle-admin/webpack.entries'),
    App: require('./webpack.entries')
}

// Merge dependent Sprinkles entries with local entries
let entries = {}
Object.values(sprinkles).forEach(sprinkle => {
    entries = Object.assign(entries, sprinkle);
});

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.UF_MODE || 'dev');
}

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/assets')
    
    // public path used by the web server to access the output path
    .setPublicPath('/assets/')
    
    // only needed for CDN's or sub-directory deploy
    //.setManifestKeyPrefix('build/')

    // Include all entries
    .addEntries(entries)

    // Copy Favicons
    .copyFiles({ from: './app/assets/favicons', to: 'favicons/[path][name].[hash:8].[ext]' })

    // Copy images
    .copyFiles({ from: './app/assets/images', to: 'images/[path][name].[hash:8].[ext]' })

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    .splitEntryChunks()

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()
    // .disableSingleRuntimeChunk()
    
    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())

    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    // enables @babel/preset-env polyfills
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })

    // enables Sass/SCSS support
    .enableSassLoader()

    // uncomment if you use TypeScript
    //.enableTypeScriptLoader()

    // uncomment to get integrity="..." attributes on your script & link tags
    // requires WebpackEncoreBundle 1.4 or higher
    //.enableIntegrityHashes(Encore.isProduction())

    // uncomment if you're having problems with a jQuery plugin
    .autoProvidejQuery()

    // uncomment if you use React
    //.enableReactPreset()
;

module.exports = Encore.getWebpackConfig();
```

We will learn in the next pages about entry files. For now, it's only important to note the following block : 

```js
const sprinkles = {
    AdminLTE: require('@userfrosting/theme-adminlte/webpack.entries'),
    Admin: require('@userfrosting/sprinkle-admin/webpack.entries'),
    App: require('./webpack.entries')
}
```

This is a list of dependent sprinkles entry files list, plus our own list. Every time you add a new sprinkle, you might need to add it's `webpack.entries` to this list. Also notice how the `.js` extension of the actual file reference is omitted, which is totally fine as it's a feature of Node.js.

## Compiling assets

To build the assets, run the following [Bakery command](/cli/commands#assets:build) : 

```bash
$ php bakery assets:build
```

The resulting compiled assets will be stored in `/public/assets/`, as defined in `webpack.config.js`.

The watch option can be used when actively working on a file. It will compile assets and automatically re-compile when a file change:

```bash
$ php bakery assets:build --watch

# or 

$ php bakery assets:webpack --watch
```

> [!WARNING]
> Whenever you make changes in your `webpack.config.js` or `webpack.entries.js` file, you must stop and restart encore when using the "watch" option.

To compile assets for a **production** environment, simply use:

```bash
$ php bakery assets:build --production

# or 

$ php bakery assets:webpack --production
```

> [!TIP]
> If you have shell access (for example, [using a VPS](/going-live/vps-production-environment)), you can build production assets directly on your host server as part of your deployment process. Otherwise, you can build them locally before transferring your application to the host server. Unlike Composer, frontend dependencies doesn't depend on any server configuration, so it is safe to build locally and upload the resulting build.

Alternatively, the underlying npm scripts can also be executed directly. However, be aware some preflight check are executed by Bakery (e.g.: make sure `webpack.config.js` exist), and won't be executed if running the scripts directly.

```bash
# Compile assets once (Same as `php bakery assets:webpack`)
npm run dev

# Compile assets and automatically re-compile when files change
# (Same as php bakery assets:webpack --watch)
npm run watch

# Compile for production
# (Same as php bakery assets:webpack --production)
npm run build
```

In the next pages, we'll see how you can use the compiled assets inside your templates and go in details through the entry system.
