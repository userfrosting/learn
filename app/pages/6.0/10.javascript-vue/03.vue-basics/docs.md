---
title: Vue Basics
description: "A crash course on the essentials of working with Vue in UserFrosting: project structure, components, reactivity, and communication patterns."
wip: true
---

Now that you understand why Vue matters and what it can do, let's get practical. This page covers the essentials of working with Vue in UserFrosting: where files go, how to create components, and the basic building blocks you'll use every day.

## Project Structure

Vue components in UserFrosting live in your sprinkle's `app/assets/` directory:

```
app/assets/
├── components/          # Your Vue components (.vue files)
│   ├── UserCard.vue
│   ├── MessageList.vue
│   └── SearchBar.vue
├── composables/         # Reusable logic functions
│   ├── useAuth.ts
│   └── useFetch.ts
├── main.ts             # Application entry point
└── theme.less          # Styles entry point
```

**Key directories**:
- **components/** - Single File Components (`.vue` files)
- **composables/** - Shared reactive logic (TypeScript functions)
- **main.ts** - Application entry point
- **theme.less** - Styles entry point (covered in the [Theming chapter](/ui-theming/customizing-themes))

## Single File Components (SFCs)

Vue uses **Single File Components** (SFCs) that bundle template, logic, and styles in one `.vue` file. Everything related to a component lives together, making it easy to understand and maintain.

A Vue component has three sections:
1. `<template>` - HTML structure and dynamic bindings
2. `<script setup>` - Component logic and reactive state
3. `<style scoped>` - Component-specific styles

### 1. Template (Required)
The `<template>` section defines your HTML structure. Use Vue's special syntax for dynamic content:

```vue
<template>
  <!-- Display data -->
  <p>{{ message }}</p>
  
  <!-- Bind attributes -->
  <img :src="imageUrl" :alt="imageAlt" />
  
  <!-- Handle events -->
  <button @click="handleClick">Click me</button>
  
  <!-- Conditional rendering -->
  <p v-if="isVisible">I'm visible!</p>
  
  <!-- Loop through data -->
  <li v-for="item in items" :key="item.id">
    {{ item.name }}
  </li>
</template>
```

### 2. Script Setup (Required)
The `<script setup>` section contains your component's logic. UserFrosting uses TypeScript (`lang="ts"`) for better development experience:

```vue
<script setup lang="ts">
import { ref, computed } from 'vue'

// Everything here is automatically available in the template
const count = ref(0)
const doubled = computed(() => count.value * 2)

function increment() {
  count.value++
}
</script>
```

> [!NOTE]
> UserFrosting uses Vue 3's `<script setup>` syntax, which is more concise than the older Options API. Everything defined in this block is automatically available in your template—no need to explicitly return or export.

### 3. Style (Optional)
The `<style scoped>` section contains CSS that only applies to this component:

```vue
<style scoped>
.my-button {
  background: blue;
  color: white;
}
/* This won't affect other components' .my-button classes */
</style>
```

The `scoped` attribute is important—it prevents your styles from accidentally affecting other components. Always use it unless you explicitly want global styles.

> [!TIP]
> UserFrosting rarely makes uses the style section, as we prefer to keep styles in separate LESS files. However, for small, component-specific styles, this is a convenient option.

## Understanding Reactivity

Vue's **reactivity system** is what makes the magic happen—when data changes, the UI updates automatically. No manual DOM manipulation required!

### ref() - Simple Values

Use `ref()` for primitives (numbers, strings, booleans):

```vue
<script setup lang="ts">
import { ref } from 'vue'

const count = ref(0)
const message = ref('Hello')
const isActive = ref(true)

// In JavaScript, access/modify with .value
function increment() {
  count.value++
}

function toggleActive() {
  isActive.value = !isActive.value
}
</script>

<template>
  <!-- In templates, Vue unwraps automatically (no .value needed) -->
  <p>Count: {{ count }}</p>
  <p>{{ message }}</p>
  <p v-if="isActive">Active!</p>
  
  <button @click="increment">Increment</button>
</template>
```

**Key points**:
- Use `.value` in JavaScript to access/modify
- In templates, Vue unwraps automatically
- Perfect for simple values that might change

### reactive() - Objects and Arrays

Use `reactive()` for objects and arrays:

```vue
<script setup lang="ts">
import { reactive } from 'vue'

const user = reactive({
  name: 'Alex',
  email: 'alex@example.com',
  roles: ['user', 'moderator']
})

// Mutate directly (no .value needed)
function updateName(newName: string) {
  user.name = newName
}

function addRole(role: string) {
  user.roles.push(role)
}
</script>

<template>
  <p>Name: {{ user.name }}</p>
  <p>Email: {{ user.email }}</p>
  <ul>
    <li v-for="role in user.roles" :key="role">{{ role }}</li>
  </ul>
</template>
```

**When to use which**:
- `ref()` for primitives, or when you might reassign the whole value
- `reactive()` for objects/arrays you'll mutate properties of

### computed() - Derived Values

Computed properties automatically update when their dependencies change:

```vue
<script setup lang="ts">
import { ref, computed } from 'vue'

const firstName = ref('John')
const lastName = ref('Doe')

// Automatically updates when firstName or lastName change
const fullName = computed(() => `${firstName.value} ${lastName.value}`)
</script>

<template>
  <p>{{ fullName }}</p>
  <!-- Changes when either firstName or lastName change -->
</template>
```

Computed properties are **cached**—they only recalculate when dependencies change. This is more efficient than calling a function every time.

## Introduction to Composables

**Composables** are reusable functions that encapsulate reactive logic. They're Vue's way of sharing stateful logic between components without using mixins or higher-order components.

Think of composables as "superpowered utility functions" that can manage their own reactive state.

### Simple Example: Mouse Position

```typescript
// composables/useMouse.ts
import { ref, onMounted, onUnmounted } from 'vue'

export function useMouse() {
  const x = ref(0)
  const y = ref(0)

  function updatePosition(event: MouseEvent) {
    x.value = event.pageX
    y.value = event.pageY
  }

  onMounted(() => {
    window.addEventListener('mousemove', updatePosition)
  })

  onUnmounted(() => {
    window.removeEventListener('mousemove', updatePosition)
  })

  // Return reactive state and methods
  return { x, y }
}
```

**Using the composable**:

```vue
<script setup lang="ts">
import { useMouse } from '@/composables/useMouse'

// Get reactive mouse position
const { x, y } = useMouse()
</script>

<template>
  <p>Mouse position: {{ x }}, {{ y }}</p>
</template>
```

> [!NOTE]
> To see this example in action, head to the [Vue Documentation](https://vuejs.org/guide/reusability/composables.html#composables).

### Why Composables?

**Without composables**, you'd copy-paste this logic:
```vue
<script setup lang="ts">
// Every component needs to duplicate this code
const x = ref(0)
const y = ref(0)

function updatePosition(event: MouseEvent) {
  x.value = event.pageX
  y.value = event.pageY
}

onMounted(() => window.addEventListener('mousemove', updatePosition))
onUnmounted(() => window.removeEventListener('mousemove', updatePosition))
</script>
```

**With composables**, you write it once and reuse it everywhere:
```vue
<script setup lang="ts">
const { x, y } = useMouse() // That's it!
</script>
```

> [!NOTE]
> Composables are covered in more detail in the [Building Vue 3 Components](/javascript-vue/vue-components) page. For now, just know they exist as a way to share reactive logic between components.

## Component Communication Basics

Components need to communicate with each other. Vue uses two main patterns:

### Props: Parent → Child

Parents pass data down to children via **props**:

```vue
<!-- Parent.vue -->
<template>
  <UserCard username="Alex" :message-count="5" />
</template>

<!-- UserCard.vue -->
<script setup lang="ts">
const props = defineProps<{
  username: string
  messageCount: number
}>()
</script>

<template>
  <p>{{ username }} has {{ messageCount }} messages</p>
</template>
```

### Emits: Child → Parent

Children notify parents of events via **emits**:

```vue
<!-- Child.vue -->
<script setup lang="ts">
const emit = defineEmits<{
  deleteClicked: [userId: number]
}>()

function handleDelete() {
  emit('deleteClicked', 123)
}
</script>

<template>
  <button @click="handleDelete">Delete</button>
</template>

<!-- Parent.vue -->
<template>
  <UserCard @delete-clicked="handleUserDeleted" />
</template>

<script setup lang="ts">
function handleUserDeleted(userId: number) {
  console.log('Delete user:', userId)
}
</script>
```

> [!IMPORTANT]
> Always follow **unidirectional data flow**: props down, events up. Never mutate props directly in a child component—emit an event to let the parent handle it.

## TypeScript in Vue

UserFrosting uses TypeScript for better developer experience. You get:
- **Autocomplete** in your editor
- **Type checking** to catch errors early
- **Better refactoring** tools

Basic TypeScript usage in components:

```vue
<script setup lang="ts">
import { ref, Ref } from 'vue'

// Define interfaces for complex types
interface User {
  id: number
  username: string
  email: string
}

// Type your props
const props = defineProps<{
  user: User
}>()

// Type your refs
const count: Ref<number> = ref(0)
const users: Ref<User[]> = ref([])

// Type your functions
function selectUser(user: User): void {
  console.log('Selected:', user.username)
}
</script>
```

Don't worry if TypeScript feels unfamiliar—you can start with basic types and gradually learn more as you go. The [TypeScript](/javascript-vue/typescript) page covers this in more detail.

## File Organization Tips

As your app grows, organize components by feature or domain:

```
app/assets/components/
├── users/
│   ├── UserCard.vue
│   ├── UserForm.vue
│   └── UserList.vue
├── messages/
│   ├── MessageThread.vue
│   └── MessageComposer.vue
└── common/
    ├── Button.vue
    ├── Modal.vue
    └── LoadingSpinner.vue
```

**Guidelines**:
- Group related components in folders
- Use descriptive, specific names (`UserCard` not `Card`)
- Keep a `common/` or `shared/` folder for reusable UI components
- One component per file

### The Main Entry Point: main.ts

Let's break down UserFrosting's main entry point to understand what each section does:

```ts
/** Create App */
import { createApp } from 'vue'
import App from './App.vue'
const app = createApp(App)
```

This creates the root Vue application instance. `App.vue` is the root component that wraps your entire application.

```ts
/** Setup Pinia */
import { createPinia } from 'pinia'
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate'
const pinia = createPinia()
pinia.use(piniaPluginPersistedstate)
app.use(pinia)
```

[Pinia](https://pinia.vuejs.org/) is Vue's official state management library. This code:
- Creates a Pinia instance for managing global state
- Adds the persistence plugin so state survives page refreshes (stored in localStorage)
- Registers Pinia with the Vue app

```ts
/** Setup Router */
import router from './router'
app.use(router)
```

Configures [Vue Router](https://router.vuejs.org/) for single-page application navigation. The router lets users navigate between different pages without full page reloads.

```ts
/** Setup Core Sprinkle */
import CoreSprinkle from '@userfrosting/sprinkle-core'
app.use(CoreSprinkle)

/** Setup Account Sprinkle */
import AccountSprinkle from '@userfrosting/sprinkle-account'
app.use(AccountSprinkle, { router })
```

Registers UserFrosting's [Sprinkles](/sprinkles)—modular packages that add functionality. Core provides base features, while Account handles user authentication and management. Each sprinkle can register Vue components, routes, and store modules.

```ts
/** Setup Theme */
import PinkCupcake from '@userfrosting/theme-pink-cupcake'
app.use(PinkCupcake)

// Import custom theme overrides
import './theme.less'
```

Applies the Pink Cupcake theme (UserFrosting's default UI styling) and loads your custom style overrides from `theme.less`.

```ts
// Mount the app
app.mount('#app')
```

Finally, this attaches the Vue application to the DOM element with id `app` (found in your PHP template). This is when your Vue app becomes visible and interactive.

## What's Next?

You now know how to create Vue components, where to put them, and the basic building blocks (reactive state, props, events, composables).

The [Building Vue 3 Components](/javascript-vue/vue-components) page dives deeper into:
- Template syntax (directives, conditionals, loops)
- Advanced reactivity patterns
- Lifecycle hooks
- Detailed composables patterns
- TypeScript best practices
- Debugging techniques

Or explore other topics:
- **[Exporting Variables](/javascript-vue/exporting-variables)** - Pass data from PHP to Vue
- **[TypeScript](/javascript-vue/typescript)** - Type safety in detail
- **[UI Components](/ui-theming/)** - Build forms, tables, and modals

> [!TIP]
> Start small: create a simple component, get it working, then gradually add complexity. You don't need to master everything at once!
