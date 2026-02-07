---
title: Advanced Usage
description: Advanced techniques for asset management including TypeScript, Vue 3, preprocessors, code splitting, and optimization.
wip: true
---

Ready to level up your asset management skills? This guide covers advanced techniques that will help you build more sophisticated applications. Don't let the word "advanced" intimidate you—we'll explain each concept clearly, and you can adopt these techniques gradually as your needs grow.

## TypeScript Support

Vite provides first-class TypeScript support with zero configuration. Simply use `.ts` files and Vite will compile them automatically.

[TypeScript](https://www.typescriptlang.org/) is a strongly typed programming language that builds on JavaScript by adding optional static type definitions. It helps catch errors during development, provides better IDE support with autocomplete and refactoring tools, and makes your code more maintainable and self-documenting.


### Type Checking

While Vite compiles TypeScript, it doesn't perform type checking during compilation for performance. Run type checking separately:

```bash
npm run typecheck
# or: vue-tsc --noEmit
```

Include this in your CI/CD pipeline to catch type errors before deployment.

### TypeScript Configuration

Configure TypeScript via `tsconfig.json`:

```json
{
  "compilerOptions": {
    "target": "ES2020",
    "useDefineForClassFields": true,
    "module": "ESNext",
    "lib": ["ES2020", "DOM", "DOM.Iterable"],
    "skipLibCheck": true,

    /* Bundler mode */
    "moduleResolution": "bundler",
    "allowImportingTsExtensions": true,
    "resolveJsonModule": true,
    "isolatedModules": true,
    "noEmit": true,
    "jsx": "preserve",

    /* Linting */
    "strict": true,
    "noUnusedLocals": true,
    "noUnusedParameters": true,
    "noFallthroughCasesInSwitch": true
  },
  "include": ["app/assets/**/*.ts", "app/assets/**/*.vue"],
  "references": [{ "path": "./tsconfig.node.json" }]
}
```

### Type Declarations

Create type declarations for global variables or modules:

**`env.d.ts`:**
```ts
/// <reference types="vite/client" />
/// <reference types="@userfrosting/sprinkle-core" />

// Declare module for .vue files
declare module '*.vue' {
    import type { DefineComponent } from 'vue'
    const component: DefineComponent<{}, {}, any>
    export default component
}

// Global properties on Vue instances
declare module 'vue' {
    interface ComponentCustomProperties {
        $t: (key: string, params?: any) => string
        $tdate: (date: Date | string, format?: string) => string
    }
}
```

## Vue 3 Integration

UserFrosting uses Vue 3 for interactive components. Vite's official Vue plugin provides optimal Single File Component (SFC) support.

### Creating Components

**`app/assets/components/UserCard.vue`:**
```vue
<script setup lang="ts">
import { ref } from 'vue'

interface Props {
    username: string
    email: string
}

const props = defineProps<Props>()
const isExpanded = ref(false)

function toggleExpanded() {
    isExpanded.value = !isExpanded.value
}
</script>

<template>
    <div class="user-card" @click="toggleExpanded">
        <h3>{{ username }}</h3>
        <p v-if="isExpanded">{{ email }}</p>
    </div>
</template>

<style scoped lang="less">
.user-card {
    padding: 1rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    cursor: pointer;

    &:hover {
        background: #f5f5f5;
    }

    h3 {
        margin: 0 0 0.5rem 0;
    }
}
</style>
```

### Component Registration

Register components globally in your main entry point:

```ts
import { createApp } from 'vue'
import App from './App.vue'
import UserCard from './components/UserCard.vue'

const app = createApp(App)

// Global registration
app.component('UserCard', UserCard)

app.mount('#app')
```

Or use local registration within components:

```vue
<script setup lang="ts">
import UserCard from './components/UserCard.vue'
</script>

<template>
    <UserCard username="admin" email="admin@example.com" />
</template>
```

### Vue Router

Set up routing for single-page applications:

```ts
import { createRouter, createWebHistory } from 'vue-router'
import type { RouteRecordRaw } from 'vue-router'

const routes: RouteRecordRaw[] = [
    {
        path: '/',
        name: 'home',
        component: () => import('./views/HomePage.vue')
    },
    {
        path: '/users',
        name: 'users',
        component: () => import('./views/UsersPage.vue')
    }
]

const router = createRouter({
    history: createWebHistory('/'),
    routes
})

export default router
```

### Pinia State Management

UserFrosting uses Pinia for state management:

```ts
import { defineStore } from 'pinia'
import axios from 'axios'

export const useUserStore = defineStore('user', {
    state: () => ({
        currentUser: null as User | null,
        isLoading: false
    }),

    getters: {
        isAuthenticated: (state) => state.currentUser !== null
    },

    actions: {
        async fetchCurrentUser() {
            this.isLoading = true
            try {
                const response = await axios.get('/api/users/current')
                this.currentUser = response.data
            } finally {
                this.isLoading = false
            }
        }
    }
})
```

## CSS Preprocessors

Vite supports LESS, Sass, and Stylus out of the box. Just import preprocessor files and install the appropriate package.

### LESS

UserFrosting uses LESS by default, particularly for UIKit theming.

```bash
npm install -D less
```

**`app/assets/theme.less`:**
```less
// Import UIKit
@import 'uikit/src/less/uikit.less';

// Override UIKit variables
@global-primary-background: #0066cc;
@global-font-family: 'Helvetica Neue', sans-serif;

// Custom styles
.custom-container {
    padding: @global-margin;

    h1 {
        color: @global-primary-background;
    }
}
```

### Sass/SCSS

If you prefer Sass:

```bash
npm install -D sass
```

**`app/assets/styles.scss`:**
```scss
$primary-color: #0066cc;

.button {
    background: $primary-color;

    &:hover {
        background: darken($primary-color, 10%);
    }
}
```

> [!WARNING]
> UserFrosting uses **Less** as its default CSS preprocessor for the default theme. If you choose to use SASS/SCSS instead, you will not be able to easily customize or extend the default theme's styles, as the theme's variables and mixins are written in Less. Consider this trade-off when choosing your preprocessor.

## Next Steps

Learn about managing assets across [multiple sprinkles](/asset-management/sprinkle-assets) or how to [migrate from Webpack](/asset-management/migration).
