---
title: Using Assets in Templates
description: Learn how to load JavaScript, CSS, and other assets in your Twig templates using Vite's helper functions.
wip: true
---

Now that Vite is compiling your assets, let's learn how to actually use them in your templates! UserFrosting provides simple Twig functions that handle all the complexity behind the scenes. You'll be amazed at how easy this is.

## Twig Helper Functions

UserFrosting includes three main Twig functions for loading Vite assets:

### vite_js()

Loads a JavaScript entry point and includes the proper `<script>` tag.

```twig
{{ vite_js('main.ts') }}
```

**Output in development mode:**
```html
<script type="module" src="http://localhost:5173/@vite/client"></script>
<script type="module" src="http://localhost:5173/main.ts"></script>
```

**Output in production mode:**
```html
<script type="module" crossorigin src="/assets/main-a1b2c3d4.js"></script>
```

### vite_css()

Loads CSS associated with an entry point. In development, Vite injects CSS automatically via JavaScript, so this may not output anything. In production, it outputs a `<link>` tag.

```twig
{{ vite_css('main.ts') }}
```

**Output in production mode:**
```html
<link rel="stylesheet" href="/assets/main-a1b2c3d4.css">
```

> [!NOTE]
> In development mode with HMR, CSS is injected dynamically by Vite's JavaScript. The `vite_css()` function typically doesn't output anything in dev mode, as the styles are loaded by the Vite dev server.

### vite_preload()

Preloads a module for better performance. This is useful for code-split chunks that will be needed soon.

```twig
{{ vite_preload('components/Dashboard.ts') }}
```

**Output:**
```html
<link rel="modulepreload" href="/assets/Dashboard-x9y8z7w6.js">
```

## Typical Template Usage

Here's how assets are typically loaded in a UserFrosting template:

**`app/templates/content/scripts_site.html.twig`:**
```twig
{# Load site-wide JavaScript #}
{% block scripts_site %}
    {{ vite_js('main.ts') }}
{% endblock %}
```

**`app/templates/content/stylesheets_site.html.twig`:**
```twig
{# Load site-wide CSS #}
{% block stylesheets_site %}
    {{ vite_css('main.ts') }}
{% endblock %}
```

These blocks are then included in your base template:

**`app/templates/pages/abstract/base.html.twig`:**
```twig
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{% block page_title %}{% endblock %}</title>

    {# Include stylesheets #}
    {% include 'content/stylesheets_site.html.twig' %}
</head>
<body>
    <div id="app"></div>

    {# Include scripts at end of body #}
    {% include 'content/scripts_site.html.twig' %}
</body>
</html>
```



## Loading Multiple Entry Points

If your application has multiple entry points, you can load them individually:

```twig
{# Load admin-specific assets #}
{% if is_admin %}
    {{ vite_js('admin.ts') }}
    {{ vite_css('admin.ts') }}
{% endif %}

{# Load main application assets #}
{{ vite_js('main.ts') }}
{{ vite_css('main.ts') }}
```

Make sure all entry points are defined in your `vite.config.ts`:

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

## Static Assets

Static assets (images, fonts, etc.) should be placed in `app/assets/public/`. Vite will copy them to `public/assets/` during the build.

**Directory structure:**
```
app/assets/
└── public/
    ├── images/
    │   └── logo.png
    └── fonts/
        └── custom-font.woff2
```

**Reference in templates:**
```twig
<img src="/assets/images/logo.png" alt="Logo">
```

**Reference in CSS/LESS:**
```less
.logo {
    background-image: url('/assets/images/logo.png');
}

@font-face {
    font-family: 'CustomFont';
    src: url('/assets/fonts/custom-font.woff2');
}
```

## Troubleshooting

### Assets not loading

**Symptom:** 404 errors for asset files

**Solutions:**
1. **Development:** Ensure Vite dev server is running (`php bakery assets:vite`)
2. **Production:** Run `php bakery assets:build --production`
3. Check that `assets.vite.dev` matches your environment
4. Verify `assets.vite.server` points to the correct Vite server URL

### Wrong assets loaded

**Symptom:** Old versions of assets are loaded

**Solutions:**
1. Clear browser cache (Ctrl+Shift+R or Cmd+Shift+R)
2. Check that manifest file exists: `public/assets/.vite/manifest.json`
3. Rebuild assets: `php bakery assets:build --production`
4. In development, restart Vite server

### CORS errors in development

**Symptom:** CORS policy errors in browser console

**Solutions:**
1. Ensure `server.origin` in `vite.config.ts` matches the Vite server URL
2. Check that `assets.vite.server` in config matches the actual server
3. For Docker, ensure `server.host: true` is set in Vite config

### Module not found

**Symptom:** `Failed to resolve module specifier`

**Solutions:**
1. Ensure the file exists in `app/assets/`
2. Check the import path is relative or uses an alias
3. Restart Vite dev server
4. Clear Vite cache: `rm -rf node_modules/.vite`

## Next Steps

Learn about [Advanced Usage](/assets-vite/advanced) including TypeScript, Vue components, preprocessors, and optimization techniques.
