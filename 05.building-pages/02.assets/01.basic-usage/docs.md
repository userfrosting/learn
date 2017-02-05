---
title: Basic usage
metadata:
    description: Referencing assets in your Twig templates.
taxonomy:
    category: docs
---

## Referencing assets

Since the client must issue a separate request to retrieve an asset after loading a page, we need a way to generate asset URLs in 
our templates.  To automatically build a url for a single asset in a Twig template, you may use the `assets.url()` helper.  This helper takes a file path to an asset, and generates an appropriate absolute url:

```
<img src="{{ assets.url('assets://local/account/images/barn-owl.jpg') }}">
```

You'll notice that we reference the file path using the `assets://` **stream wrapper**.  [Stream wrappers](http://php.net/manual/en/intro.stream.php) allow UserFrosting to define a sort of virtual mini-filesystem for a particular type of resource (in this case, assets).

When we refer to an asset using the path `assets://local/account/images/barn-owl.jpg`, UserFrosting will search through each loaded Sprinkle's `assets/` directory, starting with the most recently loaded Sprinkle, looking for a relative match to `local/account/images/barn-owl.jpg`.  As with other sorts of entities, this allows us to override assets from previously loaded Sprinkles.

For example, suppose we have:

```
account
└── assets
    └── local
        └── account
            └── images
                    └── barn-owl.jpg
```

as well as:

```
site
└── assets
    └── local
        └── account
            └── images
                └── barn-owl.jpg
```

Assuming we've loaded the `account` and `site` Sprinkles (in that order), we can now use the uri `assets://local/account/images/barn-owl.jpg` in our code, and UserFrosting will correctly resolve it to `/site/assets/local/account/images/barn-owl.jpg`.

>>>>> Notice the directory pattern used to organise the assets. This pattern is used to provide more control over asset overriding, such that assets aren't accidentally overridden, where `local` refers to non-vendor assets, and the use of the Sprinkle name to specify where the assets originally came from. While following this pattern is optional, it is recommended.

### Public asset URLs

Custom stream uris like `assets://local/account/images/barn-owl.jpg` will be correctly interpreted in your server-side code, but cannot be understood by clients' browsers.  To serve an asset like this to the client, UserFrosting must generate a public http(s) URL for use in HTML (e.g. for `<img>`, `<link>`, `<script>`, and other tags).

This is handled automatically by the `assets.url()` helper in Twig.  For example, the call to `assets.url('assets://local/account/images/barn-owl.jpg')` might generate a URL `https://owlfancy.com/assets-raw/local/account/images/barn-owl.jpg`.

The question is, how does this URL get correctly resolved by the server when it is requested?  After all, there is no `assets-raw/` _directory_ in our project's public root directory.  By default, all requests made to URLs beginning with `/assets-raw/` are sent to a special route defined in the core Sprinkle.  This route then uses the [`assetLoader` service](/services/default-services#assetloader) to resolve the request to an asset in a Sprinkle.

For example, `http://localhost/myUserFrostingProject/public/assets-raw/core/assets/vendor/bootstrap-3.3.6/css/bootstrap.css` will be resolved to the `core` Sprinkle, and respond with the contents of `vendor/bootstrap-3.3.6/css/bootstrap.css` (if it exists).

>>> The `assetLoader` service will automatically try to determine the MIME type of the asset based on the file extension, and set the appropriate `Content-Type` header in the response.
