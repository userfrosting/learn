---
title: Adding Custom Features
description: Learn how to extend the skeleton template by creating composables and custom pages.
wip: true
---

Now that you understand the skeleton template's structure, let's put that knowledge into practice by adding your own features. In this guide, you'll create a reusable composable and add a new page to your application.

> [!NOTE]
> This chapter focuses on adding functionality. The [next chapter](/javascript-vue/ui-components) will cover UI components and styling with UIkit.

## Creating a Custom Composable

Composables are reusable functions that encapsulate reactive logic using Vue's Composition API. They're perfect for extracting common functionality that multiple components need.

Let's create a `useUserStats` composable that tracks user activity statistics—something you might want to display in various places throughout your application.

### Step 1: Create the Composable

Create a new directory `app/assets/composables/` and add `useUserStats.ts`:

```ts
import { ref, computed } from 'vue'
import { useAuthStore } from '@userfrosting/sprinkle-account/stores'

/**
 * Composable for tracking and displaying user statistics
 */
export function useUserStats() {
    const auth = useAuthStore()
    const activityCount = ref(0)
    const lastVisit = ref<Date | null>(null)

    // Computed property that combines user info with stats
    const userSummary = computed(() => {
        if (!auth.isAuthenticated || !auth.user) {
            return 'Guest user'
        }

        const name = auth.user.full_name
        const activities = activityCount.value
        
        return `${name} - ${activities} recent activities`
    })

    // Method to increment activity count
    function recordActivity() {
        activityCount.value++
        lastVisit.value = new Date()
    }

    // Return public API
    return {
        activityCount,
        lastVisit,
        userSummary,
        recordActivity
    }
}
```

**What this composable does:**
- **Accesses the auth store** to check if a user is logged in
- **Manages reactive state** (`activityCount`, `lastVisit`)
- **Provides computed values** (`userSummary`) that update automatically
- **Exposes methods** (`recordActivity`) for updating state
- **Returns a clean API** that any component can use

### Step 2: Use the Composable in HomeView

Now let's enhance the existing `HomeView.vue` to use this composable. Update `app/assets/views/HomeView.vue`:

```vue
<script setup lang="ts">
import { computed } from 'vue'
import { DateTime } from 'luxon'
import { useAuthStore } from '@userfrosting/sprinkle-account/stores'
import { useConfigStore, useTranslator } from '@userfrosting/sprinkle-core/stores'
import { useUserStats } from '../composables/useUserStats'

const auth = useAuthStore()
const config = useConfigStore()
const { translate } = useTranslator()

// Use our custom composable
const { userSummary, activityCount, recordActivity } = useUserStats()

const helloMsg = computed(() => {
    return translate('WELCOME_TO', {
        title: config.get('site.title'),
        user: auth.user?.full_name ?? 'Guest'
    })
})

// Record activity when component loads
recordActivity()
</script>

<template>
    <article class="uk-article">
        <h1 class="uk-article-title">{{ helloMsg }}</h1>
        <p class="uk-article-meta">{{ $tdate(DateTime.now().toISO()) }}</p>
        
        <!-- Display our composable data -->
        <div class="uk-alert uk-alert-primary">
            {{ userSummary }} ({{ activityCount }} page views)
        </div>
        
        <p class="uk-text-lead">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
        
        <!-- Rest of the template... -->
    </article>
</template>
```

The key changes:
1. **Import the composable** at the top
2. **Destructure its return values** to access `userSummary`, `activityCount`, and `recordActivity`
3. **Use the values in the template** just like any other reactive data
4. **Call methods** like `recordActivity()` when needed

> [!TIP]
> Composables follow the naming convention `use[Name]` (e.g., `useUserStats`, `useFetch`, `useAuth`). This makes them easily recognizable and follows Vue community standards.

## Creating a Custom Page

Let's create a new "Dashboard" page that also uses our `useUserStats` composable, demonstrating how composables enable code reuse.

### Step 1: Create the View Component

Create `app/assets/views/DashboardView.vue`:

```vue
<script setup lang="ts">
import { DateTime } from 'luxon'
import { useAuthStore } from '@userfrosting/sprinkle-account/stores'
import { useUserStats } from '../composables/useUserStats'

const auth = useAuthStore()
const { userSummary, activityCount, recordActivity } = useUserStats()

// Record this page visit
recordActivity()
</script>

<template>
    <article class="uk-article">
        <h1 class="uk-article-title">My Dashboard</h1>
        <p class="uk-article-meta">{{ $tdate(DateTime.now().toISO()) }}</p>

        <div v-if="auth.isAuthenticated">
            <div class="uk-card uk-card-default uk-card-body">
                <h3 class="uk-card-title">Activity Summary</h3>
                <p>{{ userSummary }}</p>
                <p>Total recorded activities: {{ activityCount }}</p>
            </div>

            <div class="uk-card uk-card-default uk-card-body uk-margin-top">
                <h3 class="uk-card-title">Quick Stats</h3>
                <dl class="uk-description-list">
                    <dt>Username:</dt>
                    <dd>{{ auth.user?.user_name }}</dd>
                    <dt>Email:</dt>
                    <dd>{{ auth.user?.email }}</dd>
                    <dt>Member since:</dt>
                    <dd>{{ $tdate(auth.user?.created_at) }}</dd>
                </dl>
            </div>
        </div>

        <div v-else class="uk-alert uk-alert-warning">
            Please log in to view your dashboard.
        </div>
    </article>
</template>
```

Notice how both `HomeView` and `DashboardView` can use the same `useUserStats` composable. They share the same reactive state, so incrementing `activityCount` in one place updates it everywhere.

### Step 2: Register the Route

Update `app/assets/router/index.ts` to add the new route:

```ts
import AccountRoutes from '@userfrosting/sprinkle-account/routes'
import AdminRoutes from '@userfrosting/sprinkle-admin/routes'
import ErrorRoutes from '@userfrosting/sprinkle-core/routes'
import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '',
            redirect: { name: 'home' },
            component: () => import('../layouts/LayoutPage.vue'),
            children: [
                {
                    path: '/',
                    name: 'home',
                    component: () => import('../views/HomeView.vue')
                },
                {
                    path: '/about',
                    name: 'about',
                    meta: {
                        title: 'ABOUT'
                    },
                    component: () => import('../views/AboutView.vue')
                },
                // Add your new route
                {
                    path: '/dashboard',
                    name: 'dashboard',
                    meta: {
                        title: 'My Dashboard',
                        auth: {} // Requires authentication
                    },
                    component: () => import('../views/DashboardView.vue')
                },
                ...AccountRoutes,
                ...ErrorRoutes
            ]
        },
        {
            path: '/admin',
            component: () => import('../layouts/LayoutDashboard.vue'),
            children: [...AdminRoutes],
            meta: {
                title: 'ADMIN_PANEL'
            }
        }
    ]
})

export default router
```

**Key route properties:**
- **path** - The URL path (`/dashboard`)
- **name** - A unique identifier for programmatic navigation
- **meta.title** - Page title (can be a translation key)
- **meta.auth** - Requires authentication (empty object means "must be logged in")
- **component** - The view component to render (lazy-loaded with `import()`)

### Step 3: Add Navigation Link

To make your new page accessible, add a link to `app/assets/components/NavBarContent.vue`:

```vue
<template>
    <UFNavBarItem :to="{ name: 'about' }" :label="$t('ABOUT')" />
    <UFNavBarItem :to="{ name: 'dashboard' }" label="Dashboard" />
</template>
```

Or add it to the sidebar in `app/assets/components/SideBarContent.vue`:

```vue
<template>
    <!-- ... existing items ... -->
    <UFSideBarItem :to="{ name: 'dashboard' }" label="Dashboard" />
    <!-- ... more items ... -->
</template>
```

## Testing Your Changes

Start the development servers and test your new features:

```bash
# Terminal 1: Backend
php bakery serve

# Terminal 2: Frontend
npm run vite:dev
```

Visit your application at `http://localhost:8080`:
1. Navigate to the home page - you should see the user summary alert
2. Click your "Dashboard" link - the activity count should increment
3. Navigate back to home - the count persists across pages

## Key Takeaways

**Composables enable code reuse:**
- Extract common logic into composable functions
- Share reactive state across multiple components
- Keep components focused on presentation

**Adding pages is straightforward:**
1. Create a view component in `app/assets/views/`
2. Register a route in `app/assets/router/index.ts`
3. Add navigation links in your layout components

**Structure follows patterns:**
- Composables in `app/assets/composables/`
- Views in `app/assets/views/`
- Components in `app/assets/components/`
- Routes in `app/assets/router/`

## What's Next?

You've learned how to extend the skeleton template with custom functionality. The [next chapter](/javascript-vue/ui-components) will dive into building rich user interfaces with Vue components and UIkit styling.
