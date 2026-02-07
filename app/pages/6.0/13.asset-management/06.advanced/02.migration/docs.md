---
title: Migration from Webpack
description: Guide for migrating from Webpack Encore to Vite in UserFrosting 6, including backward compatibility options.
wip: true
---

If you're upgrading an existing UserFrosting 5 project, you might be wondering about migrating from Webpack Encore to Vite. Good news: you have options! You can migrate to Vite for improved performance, or continue using Webpack if that works better for your project. This guide will help you decide and walk you through both paths.

## Why Migrate to Vite?

Vite offers several advantages over Webpack Encore:

**Performance:**
- **Instant server start** - No bundling in development
- **Lightning-fast HMR** - Updates in milliseconds
- **Optimized builds** - Faster production builds with Rollup

**Developer Experience:**
- **Simpler configuration** - Less boilerplate
- **Native TypeScript** - No additional setup
- **Better Vue 3 support** - Official plugin with optimizations

**Modern Stack:**
- **ESM-first** - Built for modern browsers
- **Better tree-shaking** - Smaller bundle sizes
- **Future-proof** - Active development and community

<!-- TODO : Globally, this page should point on how to use Webpack with UF 6, or be moved to the upgrade guide -->
## Migration Steps

### 1. Install Vite Dependencies

```bash
npm install -D vite @vitejs/plugin-vue vite-plugin-vue-devtools
```

If using YAML imports:
```bash
npm install -D @modyfi/vite-plugin-yaml
```

### 2. Create Vite Configuration
<!-- TODO : Skeleton already ship with it. Tell to copy it from the skeleton -->
Create `vite.config.ts` in your project root:

```ts
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'
import ViteYaml from '@modyfi/vite-plugin-yaml'

const vitePort = parseInt(process.env.VITE_PORT || '5173', 10)

export default defineConfig({
    plugins: [
        vue(),
        ViteYaml(),
        vueDevTools({
            appendTo: 'app/assets/main.ts'
        })
    ],
    server: {
        host: true,
        strictPort: true,
        port: vitePort,
        origin: `http://localhost:${vitePort}`,
    },
    root: 'app/assets/',
    base: '/assets/',
    build: {
        outDir: '../../public/assets',
        assetsDir: '',
        emptyOutDir: true,
        manifest: true,
        rollupOptions: {
            input: {
                // Copy your entries from webpack.entries.js
                main: 'app/assets/main.ts'
            }
        }
    },
    css: {
        preprocessorOptions: {
            less: {
                relativeUrls: 'all'
            }
        }
    },
    optimizeDeps: {
        include: ['uikit', 'uikit/dist/js/uikit-icons']
    }
})
```

### 3. Update package.json Scripts

<!-- TODO : Skeleton already ship with both -->
Replace Webpack scripts with Vite scripts:

**Before (Webpack):**
```json
{
  "scripts": {
    "webpack:dev": "encore dev",
    "webpack:build": "encore production",
    "webpack:watch": "encore dev --watch"
  }
}
```

**After (Vite):**
```json
{
  "scripts": {
    "vite:dev": "vite",
    "vite:build": "vite build"
  }
}
```

### 4. Update Configuration

Modify `app/config/default.php`:

```php
'assets' => [
    // Change bundler from 'webpack' to 'vite'
    'bundler' => env('ASSETS_BUNDLER', 'vite'),

    // Add/update Vite configuration
    'vite' => [
        'manifest' => 'assets://.vite/manifest.json',
        'dev' => env('VITE_DEV_ENABLED', true),
        'base' => '/assets/',
        'server' => 'http://[::1]:' . env('VITE_PORT', 5173),
    ],
],
```

### 5. Update Twig Templates

Replace Webpack Encore functions with Vite functions:

**Before (Webpack):**
```twig
{{ encore_entry_script_tags('app') }}
{{ encore_entry_link_tags('app') }}
```

**After (Vite):**
```twig
{{ vite_js('main.ts') }}
{{ vite_css('main.ts') }}
```

### 6. Update Environment Variables

Add to your `.env` file:

```bash
# Vite configuration
VITE_PORT=5173
VITE_DEV_ENABLED=true
ASSETS_BUNDLER=vite
```

### 7. Build and Test

```bash
# Install dependencies
npm install

# Start development
php bakery assets:vite
php bakery serve

# Test in browser
# Visit http://localhost:8080
```

## Configuration Comparison

### Entry Points

**Webpack (`webpack.entries.js`):**
```js
export default {
    app: "./app/assets/main.ts",
    admin: "./app/assets/admin.ts"
}
```

**Vite (`vite.config.ts`):**
```ts
export default defineConfig({
    build: {
        rollupOptions: {
            input: {
                main: 'app/assets/main.ts',
                admin: 'app/assets/admin.ts'
            }
        }
    }
})
```

### Loaders → Plugins

**Webpack:**
```js
Encore
    .enableVueLoader()
    .enableLessLoader()
    .enableTypeScriptLoader()
```

**Vite:**
```ts
import vue from '@vitejs/plugin-vue'

export default defineConfig({
    plugins: [vue()],
    css: {
        preprocessorOptions: {
            less: {}
        }
    }
    // TypeScript: built-in, no plugin needed
})
```

### Output Configuration

**Webpack:**
```js
Encore
    .setOutputPath('public/assets/')
    .setPublicPath('/assets/')
```

**Vite:**
```ts
export default defineConfig({
    base: '/assets/',
    build: {
        outDir: '../../public/assets'
    }
})
```

### Code Splitting

**Webpack:**
```js
Encore.splitEntryChunks()
```

**Vite:**
```ts
// Automatic, or configure manually:
export default defineConfig({
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    vendor: ['vue', 'vue-router']
                }
            }
        }
    }
})
```

## Continuing with Webpack

If you need to continue using Webpack Encore, UserFrosting 6 maintains backward compatibility.

### Keep Using Webpack

1. **Set the bundler in configuration:**

```php
// app/config/default.php
'assets' => [
    'bundler' => 'webpack',  // Use 'webpack' instead of 'vite'
],
```

Or via environment variable:

```bash
ASSETS_BUNDLER=webpack
```

2. **Use Webpack commands:**

```bash
php bakery assets:webpack
php bakery assets:build
```

3. **Keep Webpack Encore templates:**

```twig
{{ encore_entry_script_tags('app') }}
{{ encore_entry_link_tags('app') }}
```

### When to Use Webpack

Consider staying with Webpack if:

- You have complex Webpack-specific configurations
- You use Encore features not available in Vite
- Your team has extensive Webpack expertise
- Migration cost outweighs benefits for your project

### Webpack Support Timeline

Webpack Encore support is maintained for backward compatibility but:

- ⚠️ **Not recommended** for new projects
- ⚠️ **May be deprecated** in future major versions
- ⚠️ **Receives minimal updates** compared to Vite

## Common Migration Issues

### Import Path Changes

**Issue:** Module resolution differences

**Solution:** Vite uses browser-native ESM. Ensure imports use extensions:

```ts
// May work in Webpack but not Vite
import Component from './Component'

// Recommended for Vite
import Component from './Component.vue'
```

### Global Variables

**Issue:** Webpack's `DefinePlugin` equivalents

**Solution:** Use environment variables:

```ts
// Webpack
const API_URL = process.env.API_URL

// Vite
const API_URL = import.meta.env.VITE_API_URL
```

### Static Assets

**Issue:** Different asset handling

**Solution:** Place static assets in `app/assets/public/`:

```
app/assets/
└── public/
    ├── images/
    └── fonts/
```

Reference with absolute paths:
```html
<img src="/assets/images/logo.png">
```

### CSS Imports

**Issue:** Import path resolution

**Solution:** Use `@import` with proper paths:

```less
// Webpack
@import '~uikit/src/less/uikit.less';

// Vite
@import 'uikit/src/less/uikit.less';
```

## Hybrid Approach

You can run both bundlers side-by-side during migration:

1. **Keep both configurations:**
   - `webpack.config.js` for Webpack
   - `vite.config.ts` for Vite

2. **Use different entry points:**
   ```ts
   // vite.config.ts
   input: {
       'vite-main': 'app/assets/vite-main.ts'
   }
   ```

3. **Load conditionally in templates:**
   ```twig
   {% if use_vite %}
       {{ vite_js('vite-main.ts') }}
   {% else %}
       {{ encore_entry_script_tags('app') }}
   {% endif %}
   ```

4. **Gradually migrate pages** to Vite

## Next Steps

After migrating to Vite:

1. **Remove Webpack dependencies:**
   ```bash
   npm uninstall @symfony/webpack-encore webpack webpack-cli
   ```

2. **Delete Webpack configuration:**
   ```bash
   rm webpack.config.js webpack.entries.js
   ```

3. **Update documentation** for your team

4. **Optimize your Vite configuration** based on your needs

Explore the [Vite Configuration](asset-management/vite-configuration) guide to learn more about Vite configuration, or jump to [Advanced Usage](asset-management/advanced) for optimization techniques.
