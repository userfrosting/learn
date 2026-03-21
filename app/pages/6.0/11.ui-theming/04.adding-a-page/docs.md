---
title: Adding a Page to Your App
description: Add a new view end-to-end by wiring a route, metadata, and navbar/sidebar navigation.
---

One of the most common tasks in any UserFrosting project is adding a new page. It looks simple — create a view, add a route, done — but in practice there are some connected pieces that all need to align: the view component, the route definition, the route metadata, the navbar or sidebar entry.

Miss any one of them and you'll end up with a page that exists but isn't reachable, or is reachable but has no navigation link, or is accessible when it shouldn't be. This walkthrough covers all steps so nothing gets missed.

## Step 1: Create the View

Create your Vue view component under `app/assets/views/`. For this example, we're adding a Reports page:

```vue
<!-- app/assets/views/ReportsView.vue -->
<template>
    <div class="uk-card uk-card-default uk-card-body">
        <h2 class="uk-card-title">Reports</h2>
        <p>Welcome to the reports page.</p>
    </div>
</template>
```

## Step 2: Register the Route

Add the new route to your router configuration. Route metadata should include `title`, `description`, and optionally `auth`, `guest` and `permission` if the page should be access-restricted. The `component` should point to your new view:

```ts
{
    path: '/reports',
    name: 'reports',
    meta: {
        title: 'REPORTS',
        description: 'REPORTS_DESCRIPTION',
        auth: {}, // Optional: add this if the page requires authentication
        guest: {}, // Optional: add this if the page should only be accessible to guests
        permission: { slug: 'uri_reports' } // Optional: add this if the page requires a specific permission
    },
    component: () => import('../views/ReportsView.vue')
}
```

The `title` and `description` values are translation keys — they'll be resolved through the UserFrosting translation system before being displayed. The `permission.slug` controls who can access this page based on their assigned permissions.

Check out [Vue Router documentation](https://router.vuejs.org/) for more details on route configuration and metadata, including how to use [nested routes](https://router.vuejs.org/guide/essentials/nested-routes.html) if your page should be a child of another route.

## Step 3: Add a Navbar Entry or Sidebar Entry

`UFNavBar` accepts a `title` prop and provides a default slot for navigation items. Use `UFNavBarItem` with `to` (a route location object) and `label`:

```vue
<UFNavBar title="Control Panel">
    <UFNavBarItem :to="{ name: 'dashboard' }" label="Dashboard" />
    <UFNavBarItem :to="{ name: 'reports' }" label="Reports" />
</UFNavBar>
```

Alternatively, you can add the page to the sidebar using `UFSideBarItem`, which accepts `to`, `label`, `icon` (a UIkit icon name), and `faIcon` (a FontAwesome class):

```vue
<UFSideBar>
    <UFSideBarItem :to="{ name: 'dashboard' }" label="Dashboard" icon="home" />
    <UFSideBarItem :to="{ name: 'reports' }" label="Reports" faIcon="chart-line" />
</UFSideBar>
```

Use `icon` for UIkit icons and `faIcon` for FontAwesome icons — they're mutually exclusive on the same item.
