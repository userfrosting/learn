---
title: Asset Management
description: Learn how to manage, compile, and optimize frontend assets in UserFrosting using Vite.
---

#### Chapter 13

# Asset Management

Frontend assets — JavaScript, TypeScript, CSS, Vue components, images, fonts, and other resources — create the client-side experience of your web application. When dealing with assets on the server, our application needs to address some problems:

1. How do we locate assets that are usually not located in the publicly served folder and **generate appropriate URLs** as proxies so they can be accessed publicly?
2. When the client actually loads a page and **requests** an asset via the URL, how do we map the URL back to a file path on the server and return it to the client?
3. How do we handle compiled assets and bundle assets together to improve efficiency?
4. How do we integrate with modern frameworks, like Vue.JS, or preprocessors like Less or Sass? 
5. How do we load external NPM dependencies?

To answer this, UserFrosting uses **Vite** as its default asset bundler to manage, compile, and optimize these resources efficiently.

Vite provides lightning-fast development with Hot Module Replacement (HMR), instant server start, and optimized production builds. It offers native support for modern web technologies including TypeScript, Vue 3, CSS preprocessors, and ESM modules.

This chapter covers everything you need to know about managing assets in UserFrosting, from basic setup to advanced optimization techniques.