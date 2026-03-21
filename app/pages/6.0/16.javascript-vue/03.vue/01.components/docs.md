---
title: Components
description: Learn Vue Single File Components and the basics of component communication in UserFrosting.
---

Vue 3 components are the building blocks of your application's interface. This page introduces the essential concepts you need to start building with Vue: **Single File Components** (SFCs) for structuring your code and **component communication** for passing data between parent and child components.

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
> UserFrosting uses Vue 3's [`<script setup>` syntax](https://vuejs.org/api/sfc-script-setup.html), which is more concise than the older Options API. Everything defined in this block is automatically available in your template—no need to explicitly return or export.

> [!TIP]
> By default `<script setup lang="ts">` is used in UserFrosting to enabled Typescript in components for better type safety and editor support. You can use plain JavaScript by omitting `lang="ts"`, but we recommend TypeScript for a better development experience.

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

The `scoped` attribute is important—it prevents your styles from accidentally affecting other components. Always use it unless you explicitly want global styles. Learn more about [scoped CSS in Vue's documentation](https://vuejs.org/api/sfc-css-features.html#scoped-css).

> [!TIP]
> UserFrosting rarely makes uses the style section, as we prefer to keep styles in separate LESS files. However, for small, component-specific styles, this is a convenient option.

## Using Components in Other Components

One of Vue's most powerful features is **component composition**—the ability to build larger interfaces by nesting smaller, reusable components. Any component can import and use other components in its template, creating a tree-like hierarchy.

Here's a simple counter button component:

```vue
<!-- ButtonCounter.vue -->
<script setup lang="ts">
import { ref } from 'vue'

const count = ref(0)
</script>

<template>
  <button @click="count++">
    You clicked me {{ count }} times.
  </button>
</template>
```

To use this component in another component, simply import it and include it in your template:

```vue
<!-- App.vue -->
<script setup lang="ts">
import ButtonCounter from './ButtonCounter.vue'
</script>

<template>
  <h1>Here is a child component!</h1>
  <ButtonCounter />
  <ButtonCounter />
  <ButtonCounter />
</template>
```

[Test this example on Vue Playground](https://play.vuejs.org/#eNqVUcFOAjEU/JVnL2gguxo9kYUohEQ9qFEvJr2suw8o7Lab9hUxm/132y6gBCXx0KSdmTdvJq3ZTVVFK4uszxKTaVERGCRbDbkUZaU0wcgSKTlWVhJqmGpVQieK91Bv0OEyiVsHN+sehGVVpIT+Rcn8YniLGiF1p0zlJ2RzUeSQKbdEoiRzksROE7T7G+N/gUm828t67CDl3zVr0DiFZlOwLcRlpqQhl9IZwMArTs/PjhQFSN7DSrjOCpEtB5yF2W6Xs0ADvCkLgcMcSoS63rg3DZAo0UTBJW5tDgqRcYmmYhYtjJKuTO3VfklZiQL1Y0XCJeasD4HxXFoU6uM+YKQt9rZ4Nsds+Qu+MGuPcfak0aBeIWc7jlI9Q2rpycsDrt19R5Yqt4VTHyGf0ajC+oytbGRl7mL/0IW0d+FLhJy9msmaUJptKR/UK5ug58x90/hI9e+4l9FVmOOyYc0XEp0C/Q==). 

Each `<ButtonCounter />` instance maintains its own separate state. Each button will maintain its own, separate `count`—that's because a new instance of the component is created each time you use it. 

> [!NOTE]
> In SFCs, it's recommended to use `<PascalCase />` tag names for child components to differentiate from native HTML elements. PascalCase names are more consistent with JavaScript conventions.

> [!TIP]
> For more details on component composition, props, events, and advanced patterns, visit the [Vue 3 Component Basics documentation](https://vuejs.org/guide/essentials/component-basics.html).

## Component Communication Basics

Components need to communicate with each other. Vue uses two main patterns:

### Props: Parent → Child

Parents pass data down to children via [**props**](https://vuejs.org/guide/components/props.html):

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

Children notify parents of events via [**emits**](https://vuejs.org/guide/components/events.html):

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

To use TypeScript in Vue components, simply add `lang="ts"` to your `<script setup>` block. This enables type checking and better editor support for your component's logic.

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

Don't worry if TypeScript feels unfamiliar-you can start with basic types and gradually learn more as you go. For deeper language concepts, continue with the [official TypeScript docs](https://www.typescriptlang.org/docs/).
