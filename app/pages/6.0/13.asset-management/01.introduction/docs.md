---
title: Introduction
description: Overview of asset management in UserFrosting 6, including the transition from Webpack Encore to Vite.
---

UserFrosting uses a modern asset bundling system to compile and manage frontend assets like JavaScript, TypeScript, Vue components, and stylesheets. Think of it as a smart assistant that transforms your development files into optimized, production-ready resources that browsers love.

Starting with UserFrosting 6, the framework has transitioned from Webpack Encore to **Vite** as its default and recommended asset bundler. Don't worry if you're new to build tools—this chapter will guide you through everything you need to know!

## Why Vite?

UserFrosting 6 adopts [Vite](https://vitejs.dev) as its primary asset bundler. If you've worked with build tools before, you'll immediately notice the difference. If this is your first time with asset bundling, you're starting with one of the best tools available!

Here's what makes Vite special:

- **Lightning-fast dev server**: Vite uses native ES modules during development. What does this mean for you? Your development server starts instantly—no more waiting for bundling—and changes appear in your browser almost immediately thanks to Hot Module Replacement (HMR)
- **Optimized builds**: For production, Vite uses Rollup to create highly optimized bundles with automatic code splitting (breaking your code into smaller chunks) and tree-shaking (removing unused code)
- **First-class TypeScript support**: Write TypeScript without any additional configuration—Vite handles it automatically
- **Modern by default**: Built for modern browsers while still supporting older ones when needed
- **Simpler configuration**: Compared to Webpack, Vite's configuration is more intuitive and requires less boilerplate code
- **Better Vue 3 support**: Vite's official Vue plugin provides optimized handling of Single File Components (`.vue` files)

While Vite is the recommended bundler, UserFrosting 6 maintains backward compatibility with [Webpack Encore](asset-management/webpack-encore) for existing projects that require it. You're not forced to migrate immediately—you can take your time!

## Asset Workflow Overview

Let's walk through how assets flow from your development files to your user's browser. Understanding this workflow will help you troubleshoot issues and optimize your application:

1. **Source files** are created in `app/assets/`:
   - TypeScript/JavaScript files (`main.ts`, components, etc.)
   - Vue 3 Single File Components (`.vue` files)
   - Stylesheets (LESS, Sass, CSS)
   - Static assets (images, fonts, etc.)

2. **Vite processes** these files differently based on your environment:
   - **Development mode**: Serves files directly with HMR for instant updates—no waiting for builds!
   - **Production mode**: Bundles, minifies, and optimizes everything for the fastest possible load times

3. **Compiled assets** are output to `public/assets/`:
   - JavaScript bundles with automatic code splitting (your app is broken into smaller chunks that load on-demand)
   - Extracted CSS files
   - Manifest file (`.vite/manifest.json`) that maps your source files to the compiled versions
   - Copied static assets

4. **Twig templates** reference assets using helper functions:
   - `{{ vite_js('main.ts') }}` - Load JavaScript entry points
   - `{{ vite_css('main.ts') }}` - Load CSS from entry points
   - `{{ vite_preload('component.ts') }}` - Preload modules for better performance

## Development vs Production

Vite behaves differently depending on whether you're developing your application or deploying it to production. This dual approach gives you the best of both worlds: speed during development and optimization for your users.

**Development Mode** (`assets.vite.dev = true`):
- Vite development server runs on port 5173 (configurable)
- Assets are served directly from memory—no build step required, changes are instant!
- Hot Module Replacement enables updates without page refresh (your form data stays intact while code updates)
- Source maps and detailed error messages help you debug issues quickly
- Twig functions point to the Vite dev server

**Production Mode** (`assets.vite.dev = false`):
- Assets are pre-built and optimized for the fastest possible load times
- Files are served as static assets from `public/assets/`
- Minification and tree-shaking reduce file sizes significantly
- Cache-busting via hashed filenames ensures users always get the latest version
- Twig functions point to the manifest-mapped filenames

You'll spend most of your time in development mode, but understanding production mode is important for deployment and troubleshooting.

## What's Next?

This chapter will guide you through:

- **Getting Started**: Setting up Vite configuration and understanding the project structure
- **Bakery Commands**: Using CLI commands to build and serve assets
- **Using Assets**: Integrating assets into your Twig templates
- **Advanced Usage**: TypeScript, Vue 3, preprocessors, and optimization techniques
- **Sprinkle Assets**: Managing assets across multiple sprinkles
- **Migration**: Transitioning from Webpack Encore to Vite

Ready to get started? Continue to the [Getting Started](asset-management/getting-started) guide.
