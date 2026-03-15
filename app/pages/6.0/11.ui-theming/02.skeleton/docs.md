---
title: Skeleton Template
description: Explore the default file structure in the skeleton template and understand how each file contributes to your UserFrosting application.
---

When you install UserFrosting, you get a complete starter template—the skeleton—with everything you need to begin building your application. This starter project includes a working Vue 3 single-page application with routing, layouts, example pages, and all the necessary configuration. Understanding these files will help you build on a solid foundation.

## Template Structure

The skeleton template's Vue application lives in `app/assets/` with the following structure:

```
app/assets/
├── App.vue                  # Root component
├── main.ts                  # Application entry point  
├── theme.less               # Custom style overrides
├── router/
│   └── index.ts             # Route definitions
├── components/
│   ├── FooterContent.vue    # Footer content
│   ├── NavBarContent.vue    # Navigation bar content
│   └── SideBarContent.vue   # Sidebar menu content
├── layouts/
│   ├── LayoutDashboard.vue  # Admin dashboard layout
│   └── LayoutPage.vue       # Standard page layout
├── views/
│   ├── AboutView.vue        # About page
│   └── HomeView.vue         # Home page
└── public/
    └── favicons/            # Favicon assets
```

Each of these files plays a specific role in your application. Let's explore them grouped by purpose.

## Application Core

### main.ts - Entry Point

This is where your application starts. It creates the Vue app instance and configures all the features you'll use throughout your application. Let's break down what each section does:

**Creating the App Instance**

```ts
import { createApp } from 'vue'
import App from './App.vue'
const app = createApp(App)
```

This creates the root Vue application instance using [`App.vue`](#appvue---root-component) as the root component.

**State Management with Pinia**

```ts
import { createPinia } from 'pinia'
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate'
const pinia = createPinia()
pinia.use(piniaPluginPersistedstate)
app.use(pinia)
```

[Pinia](https://pinia.vuejs.org/) is Vue's official state management library. The persistence plugin saves state to localStorage so it survives page refreshes—useful for keeping users logged in and remembering their preferences. We'll cover stores in more detail in the [State Management chapter](/javascript-vue/state-management).

**Routing Configuration**

```ts
import router from './router'
app.use(router)
```

Configures [Vue Router](https://router.vuejs.org/) for single-page application navigation. The router allows users to navigate between different pages without full page reloads. We'll explore how routes are defined in `router/index.ts` later in this chapter.

**Loading Sprinkles**

```ts
import CoreSprinkle from '@userfrosting/sprinkle-core'
app.use(CoreSprinkle)

import AccountSprinkle from '@userfrosting/sprinkle-account'
app.use(AccountSprinkle, { router })

import AdminSprinkle from '@userfrosting/sprinkle-admin'
app.use(AdminSprinkle)
```

[Sprinkles](/sprinkles) are UserFrosting's modular packages. Each sprinkle can register Vue components, routes, and stores:

- **Core** - Base functionality (alerts, config, translation)
- **Account** - User authentication and management
- **Admin** - Administrative interface for users, roles, and permissions

**Theme and Styling**

```ts
import PinkCupcake from '@userfrosting/theme-pink-cupcake'
app.use(PinkCupcake)

import './theme.less'
```

Applies the Pink Cupcake theme (UserFrosting's default UI based on UIkit 3) and loads your custom style overrides from [`theme.less`](#theme-less---style-customization).

**Mounting the Application**

```ts
app.mount('#app')
```

Finally, this attaches your Vue application to the `<div id="app">` element in your PHP-rendered HTML template. This is when your application becomes visible and interactive.

### App.vue - Root Component

The simplest file in your application, but also the most important. It's the root component that wraps your entire application:

```vue
<template>
    <RouterView />
</template>
```

This single `<RouterView />` component is where Vue Router displays the currently active page. As users navigate your application, different views get rendered here.

### theme.less - Style Customization

This file is your entry point for customizing the look and feel of your application:

```less
@import '@userfrosting/theme-pink-cupcake/less/main.less';

// Example: Uncomment to change navbar background color
// @navbar-background: #009556;
```

It imports the Pink Cupcake theme and provides a place to override LESS variables or add custom CSS. See the [Theming chapter](/ui-theming/customizing-themes) for more details on style customization.

## router/index.ts - Route Configuration

The `router/index.ts` file defines all the routes (URLs) in your **frontend** application and which views should display for each. The skeleton includes example routes for the home page and about page, plus it imports routes from the Account and Admin sprinkles:

```ts
import AccountRoutes from '@userfrosting/sprinkle-account/routes'
import AdminRoutes from '@userfrosting/sprinkle-admin/routes'
import ErrorRoutes from '@userfrosting/sprinkle-core/routes'
```

This modular approach allows each sprinkle to manage its own routes, making it easy to add or remove functionality without modifying your core routing configuration. This file will be where you add new routes for your custom pages as you build out your application.

> [!NOTE]
> This is different from the **backend** routing defined in your PHP controllers. The frontend routes are for navigating between pages in your Vue application, while backend routes handle API requests and server-side rendering.

## Layouts

Layouts define the overall structure of your pages (navigation, sidebar, footer). The skeleton provides two layouts:

1. `LayoutPage.vue` - Standard Layout : Used for public pages and authenticated user pages. Includes a navigation bar, collapsible sidebar (mobile only), and footer.
2. `LayoutDashboard.vue` - Admin Layout: Used for the administrative interface. Similar to the page layout but with a persistent sidebar. 
 
Both layouts use the same content components for the navbar, sidebar, and footer, allowing you to maintain a consistent look while customizing the structure as needed.

## Components

These components customize the content of the layouts:

1. **NavBarContent.vue** - Navigation bar items (links, user menu)  
2. **SideBarContent.vue** - Sidebar menu items and user card  
3. **FooterContent.vue** - Footer content (copyright notice)

These are intentionally kept separate from the layouts so you can easily customize what appears in each section without modifying the layout structure itself. They provides a default set of links and content, but you can modify or replace them as needed for your application.

## Views (Pages)

Views are the actual page content that gets displayed. The skeleton includes two example pages:

1. **HomeView.vue** - The home page with a welcome message and demo content  
2. **AboutView.vue** - A simple about page with placeholder text

Both pages demonstrate best practices: using translation keys, accessing stores for configuration and authentication state, and composing content with UIkit components.

## Static Assets

The `public/favicons/` directory contains favicon files for different devices and browsers. These are copied directly to the `public/assets/` directory during the build process and referenced in your PHP templates.

## What's Next?

Now that you understand the skeleton template's structure, you're ready to start building your own pages and components. The [next page](/javascript-vue/adding-pages) will walk you through creating new views, defining routes, and organizing your application as it grows.