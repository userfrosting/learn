---
title: Skeleton Template
description: Map the frontend structure and learn where entry setup, layout, navigation, routes, and theme overrides live.
---

Before you start adding components or customizing styles, it helps to know exactly which files do what. This page gives you the map.

## Your UI Files Live in `app/assets/`

Your frontend source files are organized inside `app/assets/`:

```
app/assets/
├── main.ts          # Vite frontend entry point
├── App.vue          # Root Vue component (just renders <RouterView />)
├── theme.less       # Your project-level theme overrides
├── layouts/         # General page layout
├── components/      # Reusable UI components
├── public/          # Public assets like images and fonts
├── views/           # Page components
└── router/          # Vue Router configuration and route definitions
```

This is also where your Sprinkle adds its own files when extending the UI. Each folder has a clear purpose, and keeping that separation makes the codebase much easier to work with as your app grows.

## Start Here: The Entry File

Everything starts in `main.ts`. The skeleton entry file does a lot more than just load styles — it wires up the complete Vue application:

```ts
/** Create App */
import { createApp } from 'vue'
import App from './App.vue'
const app = createApp(App)

/** Setup Pinia with persisted state */
import { createPinia } from 'pinia'
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate'
const pinia = createPinia()
pinia.use(piniaPluginPersistedstate)
app.use(pinia)

/** Setup Router and import routes */
import router from './router'
app.use(router)

/** Setup Core Sprinkle */
import CoreSprinkle from '@userfrosting/sprinkle-core'
app.use(CoreSprinkle)

/** Setup Account Sprinkle */
import AccountSprinkle from '@userfrosting/sprinkle-account'
app.use(AccountSprinkle, { router })

/** Setup Admin Sprinkle */
import AdminSprinkle from '@userfrosting/sprinkle-admin'
app.use(AdminSprinkle)

/** Setup Theme */
import PinkCupcake from '@userfrosting/theme-pink-cupcake'
app.use(PinkCupcake)

// Import custom theme overrides
import './theme.less'

// Done
app.mount('#app')
```

Let's break down what each section does:

- **Create App** — Creates the root Vue application instance from `App.vue`, which contains just `<RouterView />`.
- **Pinia** — Registers the state management store, plus the persistence plugin so selected store values survive page refreshes.
- **Router** — Registers Vue Router for client-side navigation and defined routes.
- **CoreSprinkle** — Sets up the UserFrosting core: loads configuration from the API, loads translations, registers `$t` and `$tdate` as global template helpers, and configures CSRF protection.
- **AccountSprinkle** — Enables authentication-aware behavior: login/logout flows, authentication guards on routes (the router is passed in so guards can be registered).
- **AdminSprinkle** — Registers the admin panel routes and components.
- **PinkCupcake** — Installs the complete theme: globally registers all `UF*` components, sets up UIkit and its icon pack, registers FontAwesome, and sets up UIkit notifications.
- **`theme.less`** — Your theme entry file. Imports your project-level theme, including PinkCupcake's own styles and your variable overrides.
- **Mount** — Finally mounts the app to the DOM, inside the `#app` div found in the Twig template.

> [!IMPORTANT]
> Notice that `theme.less` is imported **after** the PinkCupcake plugin, not before it. The order matters: PinkCupcake registers UIkit internally, and your `theme.less` file imports Pink-Cupcake's LESS source directly, so the overrides need to follow the base import chain.

## The Theme Entry File

`app/assets/theme.less` is where you import Pink-Cupcake's LESS source and add your own visual overrides:

```less
@import '@userfrosting/theme-pink-cupcake/less/main.less';

/* Your project-level variable overrides go below the import */
@sidebar-width: 300px;
@alert-primary-title-background: #6ab0de;
```

This file imports the full Pink-Cupcake LESS source (which itself pulls in UIkit), then your overrides follow. When Vite compiles the LESS, your variable values win because they appear after the defaults. We'll come back to this in detail when we reach [Customizing Themes](/ui-theming/customizing-themes).

## Layout vs. Content vs. Navigation

Keeping these folder roles separate makes your app easier to navigate and maintain:

- **`layouts/`** — the outer shell: where the navbar, sidebar, and main content are arranged together. This is where you set up the overall page layout and decide where the content goes. The skeleton includes a `LayoutPage.vue` and `LayoutDashboard.vue` that you can use as a starting point for most pages.
- **`components/`** — reusable pieces like nav menus, dropdowns, or shared card templates.
- **`views/`** — route-level pages that fill the content area defined by the layout.
- **`router/`** — route definitions and their metadata.
- **`public/`** — public assets like images and fonts. These will be copied to the build output as-is and served directly by the web server.

## Typical Workflow for New UI

Whenever you add a new piece of UI to your app, this is the order that works well:

1. Create the new view under `views/`.
2. Register a route in `router/` that points to the new view.
3. Add navigation entries in the navbar or sidebar components in `components/`.
4. Apply UIkit classes for layout and structure.
5. Adjust theme variables in `theme.less` if the design needs it.
