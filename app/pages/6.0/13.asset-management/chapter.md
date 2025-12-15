---
title: Assets
description: Assets consist of the Javascript files, CSS files, image files, fonts, and other resources used to create the client-side experience of your web application. UserFrosting uses a proven framework for loading these resources easily and efficiently.
obsolete: true
---

#### Chapter 13

# Assets

The Javascript files, CSS files, image files, fonts, and other resources used to create the client-side experience of your web application are collectively known as **assets**. When dealing with assets on the server, our application needs to address some problems:

1. How do we locate a Sprinkle's assets, **generate an appropriate URL**, and inject the appropriate reference tags when rendering a template?
2. When the client actually loads a page and **requests** an asset via the URL, how do we map the URL back to a file path on the server and return it to the client?
3. How do we handle compiled assets, aka bundle of assets bundle together to improve efficiency?
4. How do we integrate with modern frameworks, like Vue.JS, or preprocessors like Sass? 
5. How to we load external NPM dependencies?

These questions are answered in this chapter.