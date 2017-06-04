---
title: Assets
metadata:
    description: Assets consist of the Javascript files, CSS files, image files, fonts, and other resources used to create the client-side experience of your web application.  UserFrosting provides a framework for loading these resources easily and efficiently.
taxonomy:
    category: docs
---

### Chapter 11

# Assets

The Javascript files, CSS files, image files, fonts, and other resources used to create the client-side experience of your web application are collectively known as **assets**.  In UserFrosting, assets are kept under the `assets/` subdirectories of each Sprinkle.

When a user loads a page of your website in their browser, it includes a number of `<link ...>`, `<img ...>`, `<script ...>` and other tags that tell their browser how to fetch these additional resources from the server.

When dealing with assets on the server, our application needs to address two problems:

1. How do we locate a Sprinkle's assets, **generate an appropriate URL**, and inject the appropriate reference tags when rendering a template?
2. When the client actually loads a page and **requests** an asset via the URL, how do we map the URL back to a file path on the server and return it to the client?

These questions are answered in this chapter.
