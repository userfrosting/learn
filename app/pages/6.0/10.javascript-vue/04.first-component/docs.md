---
title: Your first component
description: 
wip: true
---



### Complete Component Example

Now let's see how these three sections work together in a real component. Create **app/assets/components/UserGreeting.vue**:

```vue
<template>
  <!-- 1. Template: Define the UI structure -->
  <div class="uk-card uk-card-default uk-card-body">
    <h3>Hello, {{ username }}!</h3>
    <p>You have {{ messageCount }} new messages</p>
    <button class="uk-button uk-button-primary" @click="viewMessages">
      View Messages
    </button>
  </div>
</template>

<script setup lang="ts">
// 2. Script: Define component logic and interface
import { defineProps, defineEmits } from 'vue'

// Props: Data passed from parent
const props = defineProps<{
  username: string
  messageCount: number
}>()

// Emits: Events this component can send to parent
const emit = defineEmits<{
  viewMessages: []
}>()

// Methods: Component behavior
function viewMessages() {
  emit('viewMessages')
}
</script>

<style scoped>
/* 3. Styles: Component-specific CSS */
.uk-card {
  margin: 1rem 0;
}
</style>
```

This component demonstrates how the three sections work together:
- **Template**: Uses props (`username`, `messageCount`) and event handlers (`@click`)
- **Script**: Defines typed props and emits with TypeScript
- **Style**: Adds scoped CSS (though UIkit provides most styling)

### Registering and Using Components

To use your component, register it in **app/assets/main.ts**:

```typescript
import { createApp } from 'vue'
import UserGreeting from './components/UserGreeting.vue'

const app = createApp({})

// Register globally (available in all templates)
app.component('UserGreeting', UserGreeting)

// Mount Vue to the page
app.mount('#app')
```

Then use it in your Twig templates with a Vue mount point:

```twig
<div id="app">
    <user-greeting
        username="{{ current_user.username }}"
        :message-count="{{ messageCount }}"
        @view-messages="handleViewMessages"
    />
</div>

{% block scripts_page %}
    {{ vite_js('main.ts') }}
{% endblock %}
```

> [!TIP]
> Component names use PascalCase in JavaScript (`UserGreeting`) but kebab-case in HTML/Twig (`user-greeting`). Vue automatically converts between them.

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
