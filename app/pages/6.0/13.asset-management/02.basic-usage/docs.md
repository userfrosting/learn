---
title: Referencing static assets
metadata:
    description: Referencing statics assets in your Twig templates.
    obsolete: true
---

When a user loads a page of your website in their browser, it includes a number of `<link ...>`, `<img ...>`, `<script ...>` and other tags that tell their browser how to fetch these additional resources from the server. Since the client must issue a separate request to retrieve an asset after loading a page, we need a way to generate asset URLs in our templates. To automatically build a url for a single asset in a Twig template, you may use the `asset()` helper. This helper takes a file path to an asset, and generates an appropriate absolute url:

```
<img src="{{ asset('assets/images/barn-owl.jpg') }}">
```

You'll notice that we reference the file path with abstraction of where it is actually located within the filesystem, if it's a node dependency, or which sprinkle defines it. Instead, when we refer to an asset using the path `assets/images/barn-owl.jpg`, we give a reference relative to the `public/` directory. 

The assets building process will compile or copy assets inside the `public/assets/images` folder by default and UserFrosting will use a special manifest file generated at build time to pin-point where the requested file is located.

If you need to copy other static assets from outside of a JavaScript file that's processed by Webpack - like a template - you can use the `copyFiles()` method to copy those files into your final output directory. First enable it in `webpack.config.js`:

**webpack.config.js**
```js
Encore 
    // Copy images
    .copyFiles({ from: './app/assets/images', to: 'images/[path][name].[hash:8].[ext]' })
    // Copy other assets
    .copyFiles({ from: './app/assets/slides', to: 'slides/[path][name].[ext]' }) // <-- Add this
;
```

See [Encore documentation](https://symfony.com/doc/current/frontend/encore/copy-files.html#referencing-image-files-from-a-template) for more information.

> [!NOTE]
> The same method can be used to reference javascript and css files.
> ```
> <script src="{{ asset('assets/js/barn-owl.js') }}">
> ```
> However, for Javascript and CSS files, it's generally best to use assets bundling, which we'll see in the next page.
