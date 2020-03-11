---
title: Basic usage
metadata:
    description: Referencing assets in your Twig templates.
taxonomy:
    category: docs
---

When a user loads a page of your website in their browser, it includes a number of `<link ...>`, `<img ...>`, `<script ...>` and other tags that tell their browser how to fetch these additional resources from the server.

When dealing with assets on the server, our application needs to address two problems:

1. How do we locate a Sprinkle's assets, **generate an appropriate URL**, and inject the appropriate reference tags when rendering a template?
2. When the client actually loads a page and **requests** an asset via the URL, how do we map the URL back to a file path on the server and return it to the client?

These questions are answered in this chapter.

## Referencing assets

Since the client must issue a separate request to retrieve an asset after loading a page, we need a way to generate asset URLs in our templates. To automatically build a url for a single asset in a Twig template, you may use the `assets.url()` helper. This helper takes a file path to an asset, and generates an appropriate absolute url:

```
<img src="{{ assets.url('assets://userfrosting/images/barn-owl.jpg') }}">
```

You'll notice that we reference the file path using the `assets://` **stream wrapper**. [Stream wrappers](/advanced/locator#streams-and-locations) allow UserFrosting to define a sort of virtual mini-filesystem for a particular type of resource (in this case, assets).

When we refer to an asset using the path `assets://userfrosting/images/barn-owl.jpg`, UserFrosting will use [the locator service](/advanced/locator) to search through each loaded Sprinkle's `assets/` directory, starting with the most recently loaded Sprinkle, looking for a relative match to `userfrosting/images/barn-owl.jpg`. As with other sorts of entities, this allows us to override assets from previously loaded Sprinkles.

For example, suppose we have:

```
account
└── assets
    └── userfrosting
        └── images
            └── barn-owl.jpg
```

as well as:

```
site
└── assets
    └── userfrosting
        └── images
            └── barn-owl.jpg
```

Assuming we've loaded the `account` and `site` Sprinkles (in that order), we can now use the uri `assets://userfrosting/images/barn-owl.jpg` in our code, and UserFrosting will correctly resolve it to `/site/assets/userfrosting/images/barn-owl.jpg`.

[notice=note]Notice the directory pattern used to organise the assets. Generally speaking, the basic assets that come with UserFrosting go in a `userfrosting/` subdirectory in each Sprinkle's main assets directory. You should put your own custom assets in a separate subdirectory at the same level, unless you actually need to override one of assets that ship with UserFrosting. This will avoid collision as you load more sprinkles.[/notice]

### Public asset URLs

Custom stream uris like `assets://owlfancy.com/images/barn-owl.jpg` will be correctly interpreted in your server-side code, but cannot be understood by clients' browsers. To serve an asset like this to the client, UserFrosting must generate a public http(s) URL for use in HTML (e.g. for `<img>`, `<link>`, `<script>`, and other tags).

This is handled automatically by the `assets.url()` helper in Twig. For example, the call to `assets.url('assets://userfrosting/images/barn-owl.jpg')` might generate a URL `https://owlfancy.com/assets-raw/site/assets/userfrosting/images/barn-owl.jpg`.

The question is, how does this URL get correctly resolved by the server when it is requested? After all, there is no `assets-raw/` _directory_ in our project's public root directory. By default, all requests made to URLs beginning with `/assets-raw/` are sent to a special route defined in the core Sprinkle. This route then uses the [`assetLoader` service](/services/default-services#assetloader) to resolve the request to an asset in a Sprinkle.

For example, `http://owlfancy.com/myUserFrostingProject/public/assets-raw/core/assets/vendor/bootstrap-3.3.6/css/bootstrap.css` will be resolved to the `core` Sprinkle, and respond with the contents of `vendor/bootstrap-3.3.6/css/bootstrap.css` (if it exists).

[notice=info]The `assetLoader` service will automatically try to determine the MIME type of the asset based on the file extension, and set the appropriate `Content-Type` header in the response.[/notice]
