---
title: Sprinkle Assets
description: Managing assets across multiple sprinkles in UserFrosting, including creating asset bundles and sharing code between sprinkles.
# Verified for UF 6.0
---

One of UserFrosting's most powerful features is its [Sprinkle system](/structure/sprinkles)—a way to organize your application into modular, reusable packages. Each sprinkle can have its own assets, and these assets can be shared across your application. This is perfect for building reusable components, creating themes, or organizing large applications into manageable pieces.

## Multi-Sprinkle Architecture

In UserFrosting, sprinkles can provide both compiled assets (for use in applications) and source assets (for development and extension).

### Asset Organization

Each sprinkle follows a standard structure:

```
my-sprinkle/
├── app/
│   └── assets/           # Source assets
│       ├── index.ts      # Main export
│       ├── components/   # Vue components
│       ├── composables/  # Vue composables
│       ├── stores/       # Pinia stores
│       └── interfaces/   # TypeScript interfaces
├── dist/                 # Built library (generated)
│   ├── index.js
│   ├── components.js
│   └── index.d.ts        # TypeScript definitions
├── vite.config.ts        # Vite configuration
└── package.json          # Package metadata
```

## Building Sprinkle Assets

Sprinkles can be built as libraries that other sprinkles or applications can consume.

### Library Mode Configuration

**`vite.config.ts` for a sprinkle:**

```ts
import { defineConfig } from 'vite'
import { resolve } from 'path'
import vue from '@vitejs/plugin-vue'
import dts from 'vite-plugin-dts'

export default defineConfig({
    plugins: [
        vue(),
        dts({
            include: ['app/assets/**/*.ts', 'app/assets/**/*.vue'],
            outDir: 'dist',
            copyDtsFiles: true
        })
    ],
    build: {
        lib: {
            entry: {
                index: resolve(__dirname, 'app/assets/index.ts'),
                components: resolve(__dirname, 'app/assets/components/index.ts'),
                composables: resolve(__dirname, 'app/assets/composables/index.ts'),
                stores: resolve(__dirname, 'app/assets/stores/index.ts')
            },
            formats: ['es']
        },
        rollupOptions: {
            // Externalize dependencies to avoid duplication
            external: [
                'vue',
                'vue-router',
                'axios',
                'pinia',
                '@userfrosting/sprinkle-core'
            ],
            output: {
                preserveModules: false,
                entryFileNames: '[name].js'
            }
        }
    }
})
```

### Key Concepts

**Entry Points:**
Multiple entry points allow consumers to import only what they need:

```ts
// Import everything
import MySprinkle from '@my/sprinkle'

// Import specific modules
import { UserCard } from '@my/sprinkle/components'
import { useUserApi } from '@my/sprinkle/composables'
```

**External Dependencies:**
Mark shared dependencies as external to avoid bundle duplication. Common externals:
- `vue`, `vue-router` - Vue ecosystem
- `axios` - HTTP client
- `pinia` - State management
- Other UserFrosting sprinkles

**TypeScript Definitions:**
The `vite-plugin-dts` generates `.d.ts` files for TypeScript support.

## Package Configuration

**`package.json` for a sprinkle:**

```json
{
  "name": "@my/sprinkle",
  "version": "1.0.0",
  "type": "module",
  "main": "./dist/index.js",
  "types": "./dist/index.d.ts",
  "exports": {
    ".": {
      "types": "./dist/index.d.ts",
      "import": "./dist/index.js"
    },
    "./components": {
      "types": "./dist/components.d.ts",
      "import": "./dist/components.js"
    },
    "./composables": {
      "types": "./dist/composables.d.ts",
      "import": "./dist/composables.js"
    },
    "./stores": {
      "types": "./dist/stores.d.ts",
      "import": "./dist/stores.js"
    }
  },
  "files": [
    "dist"
  ]
}
```

The `exports` field defines the public API and enables subpath imports.

## Using Sprinkle Assets

### Installing a Sprinkle

Add the sprinkle to your application's `package.json`:

```json
{
  "dependencies": {
    "@userfrosting/sprinkle-core": "^6.0.0",
    "@userfrosting/sprinkle-account": "^6.0.0",
    "@userfrosting/theme-pink-cupcake": "^1.0.0",
    "@my/custom-sprinkle": "^1.0.0"
  }
}
```

Then install:

```bash
npm install
```

### Importing from Sprinkles

Import and use sprinkle assets in your application:

```ts
// Main entry point
import { createApp } from 'vue'
import App from './App.vue'

const app = createApp(App)

// Install sprinkles as Vue plugins
import CoreSprinkle from '@userfrosting/sprinkle-core'
import AccountSprinkle from '@userfrosting/sprinkle-account'
import PinkCupcake from '@userfrosting/theme-pink-cupcake'

app.use(CoreSprinkle)
app.use(AccountSprinkle)
app.use(PinkCupcake)

app.mount('#app')
```

### Importing Specific Modules

Import only what you need from sprinkles:

```ts
// Components
import { UserCard, UserList } from '@my/sprinkle/components'

// Composables
import { useUserApi, useAuth } from '@my/sprinkle/composables'

// Stores
import { useUserStore } from '@my/sprinkle/stores'

// Types
import type { User, UserRole } from '@my/sprinkle/interfaces'
```

## Core Sprinkle Example

The Core sprinkle provides essential functionality used by other sprinkles:

### Exports Structure

**`@userfrosting/sprinkle-core/index.ts`:**
```ts
import type { App } from 'vue'
import { useConfigStore, useTranslator } from './stores'
import { useAxiosInterceptor } from './composables'

export default {
    install: (app: App) => {
        // Setup axios interceptors
        useAxiosInterceptor()
        
        // Load configuration
        useConfigStore().load()
        
        // Setup translator
        const translator = useTranslator()
        translator.load()
        app.config.globalProperties.$t = translator.translate
    }
}

// Re-export for direct imports
export { useConfigStore, useTranslator } from './stores'
export { useAlerts, useCsrf } from './composables'
export type { AlertInterface, ApiResponse } from './interfaces'
```

### Available Subpaths

```ts
// Full sprinkle
import CoreSprinkle from '@userfrosting/sprinkle-core'

// Specific modules
import { useConfigStore } from '@userfrosting/sprinkle-core/stores'
import { useAlerts } from '@userfrosting/sprinkle-core/composables'
import type { AlertInterface } from '@userfrosting/sprinkle-core/interfaces'
```

## Theme Sprinkles

Theme sprinkles provide UI components and styling. The Pink Cupcake theme is a good example:

### Theme Structure

```
theme-pink-cupcake/
├── src/
│   ├── index.ts          # Main entry
│   ├── components/       # UI components
│   │   ├── index.ts
│   │   └── Pages/
│   │       ├── Account/
│   │       └── Admin/
│   ├── plugins/          # Vue plugins
│   ├── views/            # Page templates
│   └── less/             # LESS styles
└── vite.config.ts
```

### Using a Theme

```ts
import { createApp } from 'vue'
import App from './App.vue'

// Import theme
import PinkCupcake from '@userfrosting/theme-pink-cupcake'

const app = createApp(App)
app.use(PinkCupcake)

// Import theme styles
import '@userfrosting/theme-pink-cupcake/dist/less/theme.less'

app.mount('#app')
```

## Creating a Custom Sprinkle

### 1. Initialize Project Structure

```bash
mkdir my-sprinkle
cd my-sprinkle
npm init -y
```

### 2. Install Dependencies

```bash
npm install -D vite @vitejs/plugin-vue vite-plugin-dts typescript vue-tsc
npm install vue @userfrosting/sprinkle-core
```

### 3. Create Source Files

**`app/assets/index.ts`:**
```ts
import type { App } from 'vue'

export default {
    install: (app: App) => {
        // Initialize your sprinkle
        console.log('My Sprinkle loaded')
    }
}

// Export components
export { default as CustomComponent } from './components/CustomComponent.vue'
```

**`app/assets/components/CustomComponent.vue`:**
```vue
<script setup lang="ts">
const message = 'Hello from Custom Sprinkle!'
</script>

<template>
    <div class="custom-component">
        {{ message }}
    </div>
</template>
```

### 4. Configure Vite

**`vite.config.ts`:**
```ts
import { defineConfig } from 'vite'
import { resolve } from 'path'
import vue from '@vitejs/plugin-vue'
import dts from 'vite-plugin-dts'

export default defineConfig({
    plugins: [
        vue(),
        dts({
            include: ['app/assets/**/*.ts', 'app/assets/**/*.vue'],
            outDir: 'dist'
        })
    ],
    build: {
        lib: {
            entry: resolve(__dirname, 'app/assets/index.ts'),
            formats: ['es']
        },
        rollupOptions: {
            external: ['vue', '@userfrosting/sprinkle-core']
        }
    }
})
```

### 5. Build Your Sprinkle

```bash
npm run build
# or: vite build
```

### 6. Publish (Optional)

Publish to npm or use locally:

```bash
# Publish to npm
npm publish

# Or install locally in your app
cd /path/to/your-app
npm install /path/to/my-sprinkle
```

## Best Practices

1. **Minimize Dependencies**: Only include essential packages
2. **Export Selectively**: Use subpath exports for tree-shaking
3. **Type Everything**: Provide complete TypeScript definitions
4. **Version Carefully**: Use semantic versioning
5. **Document APIs**: Include JSDoc comments for exported functions
6. **Test Independently**: Unit test your sprinkle in isolation
7. **Avoid Side Effects**: Keep imports pure (no global modifications)

## Next Steps

Learn how to [migrate from Webpack Encore](/asset-management/migration) if you have an existing UserFrosting 5 project, or explore the [Sprinkle System](/structure/sprinkles) in more depth.
