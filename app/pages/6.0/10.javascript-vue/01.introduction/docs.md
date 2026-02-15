---
title: The Modern JavaScript Stack
description: Understanding the role of JavaScript in modern web applications and why UserFrosting embraces a modern JavaScript stack.
---

UserFrosting uses modern JavaScript tools to build user interfaces and interactive web applications.

- **Vue 3**: Progressive JavaScript framework for building reactive user interfaces
- **Axios**: Promise-based HTTP client for API communication
- **TypeScript**: Type-safe JavaScript that catches errors during development

## Vue 3 : Interactive User Interfaces

Gone are the days of jQuery selectors and DOM manipulation. Vue 3's reactive data binding and component-based architecture make it easier to build complex UIs while writing less code.

If you're coming from jQuery or vanilla JavaScript, Vue 3 might seem like a significant change. Here's why it's worth learning:

- **Reactive Data Binding**: Changes to your data automatically update the UI. No more manually finding elements and updating their content.
- **Component-Based**: Break your UI into reusable pieces. Each component encapsulates its HTML, CSS, and JavaScript logic.
- **TypeScript Support**: Catch bugs before they reach production with type checking and intelligent code completion.
- **Better Performance**: Virtual DOM and optimized reactivity mean faster updates and smoother interactions.
- **Modern Developer Experience**: Hot Module Replacement (HMR) shows your changes instantly without page reloads.

Here's a quick taste of how Vue makes interactivity easy. Let's build a simple counter:

**Traditional JavaScript (the old way):**
```javascript
// Find the elements
const button = document.getElementById('increment-btn');
const display = document.getElementById('counter-display');
let count = 0;

// Manually update the DOM
button.addEventListener('click', () => {
  count++;
  display.textContent = count; // Don't forget to update!
});
```

**Vue 3 (the modern way):**
```vue
<script setup lang="ts">
import { ref } from 'vue';

// Reactive data - changes automatically update the UI
const count = ref(0);
</script>

<template>
  <div>
    <p>Count: {{ count }}</p>
    <button @click="count++">Increment</button>
  </div>
</template>
```

That's it! Notice how:
- **No manual DOM manipulation** - Vue updates the UI automatically
- **Declarative** - You describe what the UI should look like, not how to update it
- **Reactive** - Change `count`, and the `<p>` tag updates instantly
- **Clean and readable** - The code is self-explanatory

This same pattern scales to complex data tables, forms, and interactive dashboards. Once you understand the basics, building sophisticated UIs becomes much easier.

> [!TIP]
> If you're new to Vue 3, don't worry! This chapter explains concepts as we go. You don't need to be a Vue expert to build great features with UserFrosting.

## Axios: Communicating with the Server

[Axios](https://axios-http.com/) is a promise-based HTTP client for making API requests. It provides a clean and consistent API for sending requests and handling responses, making it easier to communicate with your backend. In other words, **Axios is the bridge between your Vue components and the UserFrosting backend API**.**

**Key benefits of using Axios**:
- **Consistent API**: Works the same in browsers and Node.js
- **Automatic JSON parsing**: No need for `response.json()`
- **Promise-based response handling**: Easily handle responses when they arrive
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

## TypeScript: Type Safety for JavaScript

[TypeScript](https://www.typescriptlang.org/) is JavaScript with syntax for types. It helps you catch errors during development instead of in production. UserFrosting leverages TypeScript to provide a more robust development experience.

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

## How It All Works Together

Here's how these technologies combine in a typical UserFrosting page:

1. **Backend (PHP/Twig)**: Renders the initial HTML structure and injects data
2. **Vite**: Provides your JavaScript (including Vue and Axios) and CSS assets to the browser
3. **Vue 3**: Takes over specific parts of the page (or the entire page) for interactivity
4. **TypeScript**: Provides type safety for your Vue components and utilities
5. **Axios**: Communicates with the backend API for dynamic data

**Example flow**:
```txt
User loads page
    ↓
PHP handle the request and render the page
    ↓
Twig renders HTML
    ↓
Vite serves JavaScript (Vue) and CSS assets
    ↓
Vue mounts to DOM
    ↓
User clicks button
    ↓
Vue handles click event
    ↓
Axios sends API request
    ↓
PHP handle request and responds with JSON
    ↓
Vue updates UI reactively
```

## Next Steps

Ready to add interactivity to your UserFrosting application? Let's start by diving deeper into Vue 3 and why UserFrosting chose it as the foundation for interactive features.
