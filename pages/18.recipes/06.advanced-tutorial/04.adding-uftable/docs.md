---
title: Adding ufTable
metadata:
    description: Adding ufTable to our page.
taxonomy:
    category: docs
---


### Add an asset

We will need to add an asset so that ufTable can dynamically fetch data from the database. Assets can be 'bundled' by including them in `asset-bundles.json` in the root directory of your sprinkle.

Create the directory `assets` and sub directory `js` inside `pastries`. Then create `pages` and `widgets` sub directories inside `js`:
```
pastries
├──assets
   ├── js
       ├── pages
       ├── widgets
```




### Adding an asset-bundle

`asset-bundle.json` is stored in the root directory of your sprinkle. Create that file now:

`pastries/asset-bundle.json`
```
{
  "bundle": {
    "js/pages/pastries": {
      "scripts": [
        "js/widgets/pastries.js",
        "js/pages/pastries.js"
      ],
      "options": {
        "result": {
          "type": {
            "scripts": "plain"
          }
        }
      }
    }
  }
}
```

Here are some things to take note of:

- `js/pages/pastries` (without the `.js`) is the name of our asset-bundle and is what will be referenced in our Twig template when we add the asset-bundle to the page.

- This asset-bundle includes two assets (the files we created in the previous step): `js/widgets/pastries.js` and `js/pages/pastries.js`.
