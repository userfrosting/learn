---
title: Client-Side Stack Overview
description: Understanding the modern frontend tools and libraries that power UserFrosting 6.0
wip: true
---

UserFrosting 6.0 uses a modern, component-based approach to building user interfaces. This page introduces the core technologies and explains how they work together to create fast, interactive web applications.

## Core Technologies

### Vue 3 - The Progressive Framework

[Vue 3](https://vuejs.org/) is the heart of UserFrosting's client-side architecture. It's a progressive JavaScript framework that makes building interactive UIs straightforward and enjoyable.

**What makes Vue 3 special**:
- **Reactive data binding**: Your data and UI stay in sync automatically
- **Component-based**: Build reusable pieces that encapsulate structure, style, and behavior
- **Single File Components**: Keep your HTML, CSS, and JavaScript together in `.vue` files
- **Composition API**: Organize complex logic more effectively than Options API
- **Virtual DOM**: Efficient updates minimize browser repaints
- **TypeScript-first**: Excellent TypeScript integration out of the box

**Example** - A simple reactive component:
```vue
<template>
  <button @click="count++">
    Clicked {{ count }} times
  </button>
</template>

<script setup lang="ts">
import { ref } from 'vue'

const count = ref(0)
</script>
```

No DOM manipulation, no event listeners to wire up manually—Vue handles it all.

### TypeScript - Type Safety for JavaScript

[TypeScript](https://www.typescriptlang.org/) is JavaScript with syntax for types. It helps you catch errors during development instead of in production.

**Benefits**:
- **Catch bugs early**: Type checking finds errors before you run the code
- **Better IDE support**: Autocompletion, refactoring, and inline documentation
- **Self-documenting**: Types serve as living documentation
- **Safer refactoring**: Rename variables, methods, and interfaces with confidence

**Example**:
```typescript
interface User {
  id: number
  username: string
  email: string
}

function greetUser(user: User): string {
  return `Hello, ${user.username}!`
}

// TypeScript catches this error at compile time:
// greetUser({ id: 1 }) // Error: Missing required properties
```

> [!TIP]
> TypeScript is optional but highly recommended. You can start with regular JavaScript and gradually add types as you learn.

### UIkit - Modern CSS Framework

[UIkit](https://getuikit.com/) is a lightweight, modular CSS framework that provides the building blocks for UserFrosting's default AdminLTE theme.

**Key features**:
- **Lightweight**: Smaller bundle size than Bootstrap
- **Modular**: Import only the components you need
- **LESS-based**: Easy theme customization with variables
- **Responsive**: Mobile-first design out of the box
- **Rich components**: Modals, dropdowns, accordions, and more

**Example** - UIkit button:
```html
<button class="uk-button uk-button-primary">
  Primary Action
</button>
```

### Vite - Modern Build Tool

[Vite](https://vitejs.dev/) (covered in [Asset Management](asset-management)) handles building and bundling your frontend code.

**Development benefits**:
- **Instant server start**: No bundling during development
- **Hot Module Replacement**: See changes immediately without page refresh
- **Fast builds**: Significantly faster than Webpack

### Axios - HTTP Client

[Axios](https://axios-http.com/) is a promise-based HTTP client for making API requests.

**Why we use it**:
- **Consistent API**: Works the same in browsers and Node.js
- **Automatic JSON parsing**: No need for `response.json()`
- **Request/response interceptors**: Add authentication headers globally
- **Better error handling**: Structured error objects
- **Cancel requests**: Abort pending requests when needed

**Example**:
```typescript
import axios from 'axios'

// GET request
const response = await axios.get('/api/users')
const users = response.data

// POST request with data
await axios.post('/api/users', {
  username: 'alex',
  email: 'alex@example.com'
})
```

## Supporting Libraries

UserFrosting includes these additional libraries for common tasks:

### FontAwesome - Icon Library

[FontAwesome](https://fontawesome.com/) provides thousands of icons for your interface.

**Usage in templates**:
```html
<i class="fa fa-user"></i> User Profile
<i class="fa fa-cog"></i> Settings
```

### Luxon - Date & Time Handling

[Luxon](https://moment.github.io/luxon/) (modern replacement for Moment.js) handles date parsing, formatting, and manipulation.

**Example**:
```typescript
import { DateTime } from 'luxon'

const now = DateTime.now()
const formatted = now.toFormat('yyyy-MM-dd HH:mm:ss')
const relative = now.toRelative() // "2 hours ago"
```

### Highlight.js - Code Syntax Highlighting

[Highlight.js](https://highlightjs.org/) provides syntax highlighting for code blocks (used in UserFrosting's documentation).

## Legacy Libraries (Deprecated)

> [!WARNING]
> These libraries are included for backward compatibility but are **deprecated**. New code should not use them.

### jQuery (Deprecated)

**Status**: Included but deprecated. Will be removed in a future version.

**Migration**: Replace jQuery code with Vue 3 components or vanilla JavaScript.

**Why deprecated**: Modern browsers have native APIs for what jQuery provided, and Vue's reactivity makes DOM manipulation unnecessary.

### Handlebars (Deprecated)

**Status**: Deprecated in favor of Vue templates.

**Migration**: Convert Handlebars templates to Vue Single File Components.

**Why deprecated**: Vue's template syntax is more powerful and integrates better with component logic.

### Bootstrap (Removed from Core)

**Status**: Replaced by UIkit in the default AdminLTE theme.

**Migration**: Convert Bootstrap classes to UIkit equivalents.

**Why changed**: UIkit provides a modern, lightweight alternative with excellent customization through LESS.

## How It All Works Together

Here's how these technologies combine in a typical UserFrosting page:

1. **Backend (PHP/Twig)**: Renders the initial HTML structure and injects data
2. **Vue 3**: Takes over specific parts of the page (or the entire page) for interactivity
3. **TypeScript**: Provides type safety for your Vue components and utilities
4. **UIkit**: Styles components with responsive, beautiful CSS
5. **Axios**: Communicates with the backend API for dynamic data
6. **Vite**: Bundles everything together efficiently

**Example flow**:
```
User loads page
    ↓
Twig renders HTML + data
    ↓
Vue mounts to DOM
    ↓
User clicks button
    ↓
Vue handles click event
    ↓
Axios sends API request
    ↓
Server responds with JSON
    ↓
Vue updates UI reactively
```

## Development Workflow

Your typical development cycle:

1. **Start dev servers** (in two terminals):
   ```bash
   php bakery serve       # Backend (port 8080)
   npm run vite:dev       # Frontend (port 5173)
   ```

2. **Make changes** to Vue components or TypeScript files

3. **See results instantly** via Hot Module Replacement—no page refresh needed

4. **Build for production**:
   ```bash
   npm run vite:build
   ```

## File Organization

Frontend code lives in `app/assets/`:

```
app/assets/
├── main.ts              # Application entry point
├── theme.less           # Main stylesheet (UIkit customization)
├── components/          # Vue components
│   ├── MyComponent.vue
│   └── shared/
│       └── Button.vue
├── composables/         # Reusable Vue logic
│   └── useAuth.ts
├── css/                 # Additional stylesheets
├── public/              # Static assets (images, fonts)
└── types/               # TypeScript type definitions
```

## What's Next?

Now that you understand the stack, explore how to:

- **[Export Variables](client-side-code/exporting-variables)**: Pass data from PHP to JavaScript
- **[Build Vue Components](client-side-code/vue-components)**: Create reactive user interfaces
- **[Work with Forms](client-side-code/components/forms)**: Handle user input and validation
- **[Create Tables](client-side-code/components/tables)**: Display and manipulate data
- **[Show Alerts](client-side-code/components/alerts)**: Provide user feedback

> [!NOTE]
> Don't feel overwhelmed! You don't need to master everything at once. Start with the basics and gradually explore more advanced features as needed. 
