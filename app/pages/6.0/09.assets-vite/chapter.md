---
title: Assets & Vite
description: Learn how to use Vite to compile, bundle, and optimize frontend assets in UserFrosting.
---

#### Chapter 9

# Assets & Vite

Frontend assets — JavaScript, TypeScript, CSS, images, fonts, and other resources — create the client-side experience of your web application. When dealing with assets on the server, our application needs to address some problems:

1. How do we locate assets that are usually not located in the publicly served folder and **generate appropriate URLs** as proxies so they can be accessed publicly?
2. How do we handle compiled assets and bundle assets together to improve efficiency?
3. How do we integrate with CSS preprocessors like Less or Sass?
4. How do we load external NPM dependencies?

To answer this, UserFrosting uses **Vite** as its default build tool and asset bundler to manage, compile, and optimize these resources efficiently.

Vite provides lightning-fast development with Hot Module Replacement (HMR), instant server start, and optimized production builds. It offers native support for modern web technologies including TypeScript, CSS preprocessors, and ESM modules.

This chapter covers everything you need to know about the build pipeline and asset management in UserFrosting, from basic setup to advanced optimization techniques.