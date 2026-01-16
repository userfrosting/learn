---
title: Vite Configuration
description: Learn how to configure Vite, understand entry points, and manage environment variables in UserFrosting.
wip: true
---

The `vite.config.ts` file in your project root configures how Vite processes your assets. This file is included in the Skeleton template and already contains everything you need, but understanding its structure helps you customize the build process for your specific needs.

## Vite Configuration File

Here's the typical UserFrosting configuration with detailed explanations:

```ts
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'
import ViteYaml from '@modyfi/vite-plugin-yaml'

// Get vite port from env, default to 5173
const vitePort = parseInt(process.env.VITE_PORT || '5173', 10)

// https://vitejs.dev/config/
export default defineConfig({
    plugins: [
        vue(),                    // Vue 3 support
        ViteYaml(),               // YAML file imports
        vueDevTools({             // Vue DevTools integration
            appendTo: 'app/assets/main.ts'
        })
    ],
    server: {
        host: true,               // Allow external access (needed for Docker)
        strictPort: true,         // Fail if port is already in use
        port: vitePort,
        origin: `http://localhost:${vitePort}`,
    },
    root: 'app/assets/',          // Source directory
    base: '/assets/',             // Public path
    build: {
        outDir: '../../public/assets',   // Output directory
        assetsDir: '',                   // No subdirectory for assets
        emptyOutDir: true,               // Clean before build
        manifest: true,                  // Generate manifest.json
        rollupOptions: {
            input: {
                main: 'app/assets/main.ts'  // Entry points
            }
        }
    },
    // Fix UIKit path issues with LESS
    css: {
        preprocessorOptions: {
            less: {
                relativeUrls: 'all'
            }
        }
    },
    // Force optimization of UIKit in dev mode
    optimizeDeps: {
        include: ['uikit', 'uikit/dist/js/uikit-icons']
    }
})
```

## Configuration Sections

### Plugins

Plugins extend Vite's functionality to support different file types and development tools:

```ts
plugins: [
    vue(),                    // Vue 3 support
    ViteYaml(),               // YAML file imports
    vueDevTools({             // Vue DevTools integration
        appendTo: 'app/assets/main.ts'
    })
]
```

- **`@vitejs/plugin-vue`** - Enables Vue 3 Single File Component (SFC) support. This allows you to write `.vue` files with `<template>`, `<script>`, and `<style>` sections.
- **`@modyfi/vite-plugin-yaml`** - Allows importing YAML files as JavaScript modules. This is required for Fortress form schemas, which are defined in YAML format.
- **`vite-plugin-vue-devtools`** - Integrates Vue DevTools for debugging. This provides browser developer tools integration for inspecting component hierarchies, state, and events.

> [!TIP]
> You can add more plugins for additional functionality. For example, `vite-plugin-pwa` for Progressive Web App support, or `vite-plugin-compression` for gzip/brotli compression.

### Server Options

Configuration for the development server:

```ts
server: {
    host: true,               // Allow external access (needed for Docker)
    strictPort: true,         // Fail if port is already in use
    port: vitePort,           // Development server port
    origin: `http://localhost:${vitePort}`,
}
```

- **`host: true`** - Allows access from external hosts. This is required for Docker containers where the app runs inside a container but needs to be accessible from your host machine's browser.
- **`strictPort: true`** - Prevents Vite from automatically trying the next available port if the specified port is in use. This ensures consistency and catches port conflicts early.
- **`port`** - The development server port (default: 5173). Can be configured via the `VITE_PORT` environment variable.
- **`origin`** - The public URL for the dev server. This is used by UserFrosting to reference assets during development.

### Build Options

Configuration for production builds:

```ts
build: {
    outDir: '../../public/assets',   // Output directory
    assetsDir: '',                   // No subdirectory for assets
    emptyOutDir: true,               // Clean before build
    manifest: true,                  // Generate manifest.json
    rollupOptions: {
        input: {
            main: 'app/assets/main.ts'  // Entry points
        }
    }
}
```

- **`root`** - Where source files are located (`app/assets/`). Vite resolves all imports relative to this directory.
- **`base`** - Public path for assets (`/assets/`). This is the URL path where assets will be served in production.
- **`outDir`** - Where compiled files are written (`public/assets/`). This is relative to the root directory.
- **`manifest: true`** - Generates a `manifest.json` file that maps source files to their hashed output filenames. UserFrosting reads this to reference the correct asset files.
- **`rollupOptions.input`** - Defines entry points for your application. Each entry creates a separate bundle.

### CSS Preprocessors

Configuration for CSS preprocessing:

```ts
css: {
    preprocessorOptions: {
        less: {
            relativeUrls: 'all'
        }
    }
}
```

- **`preprocessorOptions`** - Configure LESS, Sass, or Stylus preprocessors. UserFrosting uses LESS by default with UIKit.
- **`relativeUrls: 'all'`** - Ensures LESS resolves relative URLs correctly. UIKit requires this for proper path resolution to fonts and images.

### Optimization

Configuration for dependency optimization:

```ts
optimizeDeps: {
    include: ['uikit', 'uikit/dist/js/uikit-icons']
}
```

- **`optimizeDeps.include`** - Forces Vite to pre-bundle these dependencies during development. This improves dev server startup time and prevents issues with UIKit's CommonJS modules.

## Understanding Entry Points

Entry points are the starting files where Vite begins building your application. Think of them as the front door to your house—everything else is connected through them.

### What is an Entry Point?

An entry point is a JavaScript or TypeScript file that imports everything your application needs. When Vite builds your assets, it:

1. Starts at the entry point file
2. Follows all `import` statements to find dependencies
3. Bundles everything together into optimized output files
4. Generates a dependency graph showing how files relate

In UserFrosting, the main entry point is `app/assets/main.ts`, configured in `vite.config.ts`:

```ts
export default defineConfig({
  build: {
    rollupOptions: {
      input: {
        main: 'app/assets/main.ts'  // Entry point
      }
    }
  }
})
```

### The Main Entry Point: main.ts

Let's break down UserFrosting's main entry point to understand what each section does:

```ts
/** Create App */
import { createApp } from 'vue'
import App from './App.vue'
const app = createApp(App)
```

This creates the root Vue application instance. `App.vue` is the root component that wraps your entire application.

```ts
/** Setup Pinia */
import { createPinia } from 'pinia'
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate'
const pinia = createPinia()
pinia.use(piniaPluginPersistedstate)
app.use(pinia)
```

[Pinia](https://pinia.vuejs.org/) is Vue's official state management library. This code:
- Creates a Pinia instance for managing global state
- Adds the persistence plugin so state survives page refreshes (stored in localStorage)
- Registers Pinia with the Vue app

```ts
/** Setup Router */
import router from './router'
app.use(router)
```

Configures [Vue Router](https://router.vuejs.org/) for single-page application navigation. The router lets users navigate between different pages without full page reloads.

```ts
/** Setup Core Sprinkle */
import CoreSprinkle from '@userfrosting/sprinkle-core'
app.use(CoreSprinkle)

/** Setup Account Sprinkle */
import AccountSprinkle from '@userfrosting/sprinkle-account'
app.use(AccountSprinkle, { router })
```

Registers UserFrosting's [Sprinkles](/sprinkles)—modular packages that add functionality. Core provides base features, while Account handles user authentication and management. Each sprinkle can register Vue components, routes, and store modules.

```ts
/** Setup Theme */
import PinkCupcake from '@userfrosting/theme-pink-cupcake'
app.use(PinkCupcake)

// Import custom theme overrides
import './theme.less'
```

Applies the Pink Cupcake theme (UserFrosting's default UI styling) and loads your custom style overrides from `theme.less`.

```ts
// Mount the app
app.mount('#app')
```

Finally, this attaches the Vue application to the DOM element with id `app` (found in your PHP template). This is when your Vue app becomes visible and interactive.

### Why Entry Points Matter

Understanding entry points helps you:

1. **Add new libraries** - Know where to import and register new dependencies
2. **Customize initialization** - Control the order plugins and sprinkles load
3. **Debug loading issues** - Trace where components and features are registered
4. **Optimize bundles** - Understand what's included in your main bundle
5. **Create multiple bundles** - Split code for different parts of your application

### Multiple Entry Points

For larger applications, you might define multiple entry points for different sections:

```ts
export default defineConfig({
  build: {
    rollupOptions: {
      input: {
        main: 'app/assets/main.ts',        // Public pages
        admin: 'app/assets/admin.ts',      // Admin dashboard
        login: 'app/assets/login.ts'       // Login page
      }
    }
  }
})
```

This creates separate bundles (`main.js`, `admin.js`, `login.js`), allowing:
- **Faster page loads** - Users only download code for the current section
- **Better caching** - Changes to admin don't invalidate the public bundle
- **Code isolation** - Admin features aren't exposed to public users
- **Lazy loading** - Load bundles on-demand when users navigate

Each entry point can import different components, libraries, and styles, giving you fine-grained control over what users download.

> [!TIP]
> Start with a single entry point and only create multiple entries if you notice performance issues or have distinct application sections with minimal code sharing.

## Configuration

The Vite integration can be configured in your UserFrosting config file (`app/config/default.php`). Below is the default configuration provided by the Core Sprinkle:

```php
'assets' => [
    'bundler' => env('ASSETS_BUNDLER', 'vite'), // Either 'vite' or 'webpack'
    
    'vite' => [
        // Path to Vite's manifest.json file
        'manifest' => 'assets://.vite/manifest.json',
        
        // Enable development mode (use Vite dev server)
        'dev' => env('VITE_DEV_ENABLED', true),
        
        // Public base path for assets
        'base' => '/assets/',
        
        // Vite development server URL
        'server' => 'http://[::1]:' . env('VITE_PORT', 5173),
    ],
],
```

**`manifest`**
- Path to Vite's build manifest file
- Uses the [Uniform Resource Locator](/advanced/locator) scheme
- Default: `assets://.vite/manifest.json` resolves to `public/assets/.vite/manifest.json`

**`dev`**
- Controls whether to use the Vite dev server or built assets
- Set to `true` in development, `false` in production
- Tied to `VITE_DEV_ENABLED` environment variable

**`base`**
- Public path where assets are served inside the `public/` directory
- Should match the `base` setting in `vite.config.ts`
- Default: `/assets/`

**`server`**
- URL of the Vite development server
- Must match the host and port Vite is running on
- Default: `http://[::1]:5173` (IPv6 localhost)
- Controlled by `VITE_PORT` environment variable

### Environment Variables

Vite supports environment variables for configuration. These variables let you customize behavior without modifying code, making it easy to have different settings for development, staging, and production environments. Use these variables in your `.env` file.

**`VITE_PORT`** - Development server port (default: `5173`)
```bash
VITE_PORT=5174
```

Changes the port where the Vite dev server runs. Useful if port 5173 is already in use by another application.

**`VITE_DEV_ENABLED`** - Enable/disable dev mode in UserFrosting config (default: `true`)
```bash
VITE_DEV_ENABLED=true
```

Controls whether UserFrosting loads assets from the Vite dev server or from pre-built files. Set to `false` in production to use optimized, built assets.

**`ASSETS_BUNDLER`** - Choose between `vite` or `webpack` (default: `vite`)
```bash
ASSETS_BUNDLER=vite
```

Selects which bundler to use. While Vite is the default and recommended option, you can switch to Webpack Encore if needed for backward compatibility.

> [!NOTE]
> Variables prefixed with `VITE_` are exposed to your client-side code and can be accessed using `import.meta.env.VITE_*`. Other variables are only available during the build process and to the PHP backend.

## Development vs Production

The behavior of asset functions changes based on the `assets.vite.dev` setting:

### Development Mode (`dev = true`)

When the Vite dev server is running:

1. **JavaScript files** are loaded directly from the Vite server
2. **CSS** is injected by Vite's HMR system
3. **Hot Module Replacement** updates modules without page refresh
4. **Source maps** are available for debugging
5. No build step required - changes are instant

**Example output:**
```html
<script type="module" src="http://localhost:5173/@vite/client"></script>
<script type="module" src="http://localhost:5173/main.ts"></script>
```

### Production Mode (`dev = false`)

When using pre-built assets:

1. **Manifest file** maps entry points to hashed filenames
2. **JavaScript** uses hash-based filenames for cache busting
3. **CSS** is extracted into separate files
4. **Minification** reduces file sizes
5. **Code splitting** optimizes load times

**Example output:**
```html
<script type="module" crossorigin src="/assets/main-a1b2c3d4.js"></script>
<link rel="stylesheet" href="/assets/main-a1b2c3d4.css">
```

## Next Steps

Now that you understand Vite configuration and entry points, learn about the [Vue Framework](/asset-management/vue-framework) that powers UserFrosting's modern interactive interfaces.
