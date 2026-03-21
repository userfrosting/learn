---
title: Advanced Vue Features
description: Master advanced Vue 3 patterns including reactivity, template directives, component communication, lifecycle hooks, and composables.
---

Now that you understand [Components](/javascript-vue/vue/components), [Reactivity](/javascript-vue/vue/reactivity), and [Composables](/javascript-vue/vue/composables), it's time to dive deeper. This page covers the advanced patterns, template syntax, and communication techniques you'll need to build complex, production-ready Vue applications in UserFrosting.

## Advanced Reactivity Patterns

While [Understanding Reactivity](/javascript-vue/vue/reactivity) covered the basics of `ref()`, `reactive()`, and `computed()`, let's explore more advanced patterns you'll encounter in real applications.

### Watching Values with watch()

Sometimes you need to perform side effects when reactive data changes. That's where `watch()` comes in:

```vue
<script setup lang="ts">
import { ref, watch } from 'vue'

const searchQuery = ref('')
const searchResults = ref([])

// Watch a single value
watch(searchQuery, async (newQuery, oldQuery) => {
  console.log(`Search changed from "${oldQuery}" to "${newQuery}"`)
  searchResults.value = await fetchSearchResults(newQuery)
})

// Watch multiple values
const firstName = ref('')
const lastName = ref('')

watch([firstName, lastName], ([newFirst, newLast], [oldFirst, oldLast]) => {
  console.log(`Name changed: ${newFirst} ${newLast}`)
})

// Immediate execution (run on mount) with immediate: true
watch(searchQuery, (value) => {
  // ...
}, { immediate: true })

// Deep watching (for nested objects). Will trigger when "name" changes
const user = ref({ profile: { name: 'Alex' } })

watch(user, (newUser) => {
  console.log('User changed:', newUser)
}, { deep: true })
</script>
```

**When to use `watch()`**:
- Fetching data when a value changes
- Updating localStorage/sessionStorage
- Logging or analytics
- Complex side effects

### watchEffect() - Automatic Dependency Tracking

`watchEffect()` automatically tracks all dependencies and re-runs when **any** of them change:

```vue
<script setup lang="ts">
import { ref, watchEffect } from 'vue'

const count = ref(0)
const multiplied = ref(2)
const result = ref(0)

// Automatically tracks `count` and `multiplied` (and `result`), then runs when either changes
watchEffect(() => {
  result.value = count.value * multiplied.value
  console.log(`Count: ${count.value}, Multiplied: ${multiplied.value}, Result: ${result.value}`)
})
</script>
```

> [!TIP]
> Use `computed()` for derived values, `watch()` for side effects with specific dependencies, and `watchEffect()` for side effects with automatic dependency tracking.

## Template Syntax

Vue templates look like HTML but with special directives for dynamic behavior.

### Text Interpolation

Display reactive data with double curly braces:

```vue
<template>
  <p>Message: {{ message }}</p>
  <p>Count + 1: {{ count + 1 }}</p>
  <p>Uppercase: {{ message.toUpperCase() }}</p>
  <p>If statement: {{ isActive ? 'Active' : 'Inactive' }}</p>
</template>
```

### Attribute Binding (v-bind / :)

Bind reactive data to HTML attributes:

```vue
<template>
  <!-- Shorthand: : -->
  <img :src="imageUrl" :alt="imageAlt" />
  <button :disabled="isLoading" :class="buttonClass">
    Submit
  </button>

  <!-- Dynamic classes -->
  <div :class="{ active: isActive, disabled: isDisabled }">
    ...
  </div>

  <!-- Dynamic styles -->
  <div :style="{ color: textColor, fontSize: fontSize + 'px' }">
    ...
  </div>
</template>
```

### Event Handling (v-on / @)

Listen to DOM events:

```vue
<template>
  <!-- Shorthand: @ -->
  <button @click="handleClick">Click me</button>
  <input @input="handleInput" />
  <form @submit.prevent="onSubmit">
    ...
  </form>

  <!-- Event modifiers -->
  <button @click.stop="handleClick">Stop propagation</button>
  <button @click.once="initialize">Run once</button>

  <!-- Keyboard modifiers -->
  <input @keyup.enter="submit" @keyup.esc="cancel" />
</template>

<script setup lang="ts">
function handleClick(event: MouseEvent) {
  console.log('Clicked!', event)
}

function handleInput(event: Event) {
  const target = event.target as HTMLInputElement
  console.log('Input value:', target.value)
}

function onSubmit() {
  // Form submission logic
}
</script>
```

### Conditional Rendering

**v-if / v-else-if / v-else** - Elements added/removed from DOM:

```vue
<template>
  <div v-if="userRole === 'admin'">
    Admin Panel
  </div>
  <div v-else-if="userRole === 'moderator'">
    Moderator Tools
  </div>
  <div v-else>
    Regular User View
  </div>
</template>
```

**v-show** - Elements stay in DOM, just toggle `display`:

```vue
<template>
  <!-- Better for frequently toggled elements -->
  <div v-show="isVisible">
    This toggles display: none
  </div>
</template>
```

### List Rendering (v-for)

Loop through arrays or objects:

```vue
<template>
  <!-- Array -->
  <ul>
    <li v-for="user in users" :key="user.id">
      {{ user.name }}
    </li>
  </ul>

  <!-- With index -->
  <ul>
    <li v-for="(user, index) in users" :key="user.id">
      {{ index + 1 }}. {{ user.name }}
    </li>
  </ul>

  <!-- Object properties -->
  <ul>
    <li v-for="(value, key) in userObject" :key="key">
      {{ key }}: {{ value }}
    </li>
  </ul>
</template>
```

> [!IMPORTANT]
> Always provide a unique `:key` when using `v-for`. This helps Vue track elements efficiently.

### Two-Way Binding (v-model)

Synchronize form inputs with state:

```vue
<script setup lang="ts">
import { ref } from 'vue'

const message = ref('')
const checked = ref(false)
const selected = ref('')
const multiSelect = ref([])
</script>

<template>
  <!-- Text input -->
  <input v-model="message" />
  <p>{{ message }}</p>

  <!-- Checkbox -->
  <input type="checkbox" v-model="checked" />

  <!-- Select -->
  <select v-model="selected">
    <option value="a">Option A</option>
    <option value="b">Option B</option>
  </select>

  <!-- Multi-select -->
  <select v-model="multiSelect" multiple>
    <option value="a">A</option>
    <option value="b">B</option>
    <option value="c">C</option>
  </select>

  <!-- Modifiers -->
  <input v-model.trim="message" /> <!-- trim whitespace -->
  <input v-model.number="age" /> <!-- convert to number -->
  <input v-model.lazy="message" /> <!-- update on change, not input -->
</template>
```

> [!TIP]
> You might wonder why you need `v-model` at all — can't you just bind the value with `:value` and handle `@input` yourself? You can, but `v-model` is shorthand for exactly that pair. The real reason to use it is that without it, the binding is **one-way**: the input shows the ref's value, but typing doesn't update the ref. `v-model` wires both directions — ref to input *and* input back to ref — in one directive.

## Lifecycle Hooks

Run code at specific points in a component's life:

```vue
<script setup lang="ts">
import {
  onBeforeMount,
  onMounted,
  onBeforeUpdate,
  onUpdated,
  onBeforeUnmount,
  onUnmounted
} from 'vue'

onBeforeMount(() => {
  console.log('About to mount')
})

onMounted(() => {
  console.log('Component mounted to DOM')
  // Fetch data, set up event listeners, etc.
})

onBeforeUpdate(() => {
  console.log('About to re-render')
})

onUpdated(() => {
  console.log('Re-rendered')
})

onBeforeUnmount(() => {
  console.log('About to unmount')
})

onUnmounted(() => {
  console.log('Component removed')
  // Cleanup: remove listeners, cancel timers, etc.
})
</script>
```

**Most commonly used**:
- `onMounted` - Fetch data, initialize libraries
- `onUnmounted` - Cleanup (timers, listeners, subscriptions)

## Debugging Vue Components

### Vue DevTools

[Vue DevTools](https://devtools.vuejs.org/) is enabled by default when using the Vite dev server. You'll see it automatically in your browser's developer tools at the bottom of each page. It allows you to:
- Inspect component tree
- View component state and props
- Track emitted events
- Profile component performance
- Time-travel debug (see state history)

### Console Debugging

Like any JavaScript, you can use `console.log()`, `console.warn()`, and `console.error()` to debug your Vue components. You can log reactive state, props, computed values, and more.

```vue
<script setup lang="ts">
import { watch, watchEffect } from 'vue'

// Watch specific value
watch(user, (newUser, oldUser) => {
  console.log('User changed:', { newUser, oldUser })
})

// Watch multiple values
watch([firstName, lastName], ([newFirst, newLast]) => {
  console.log('Name changed:', newFirst, newLast)
})

// Watch immediately and on changes
watchEffect(() => {
  console.log('Current user:', user.value)
})
</script>

<template>
  <!-- Debug template values -->
  <pre>{{ JSON.stringify(user, null, 2) }}</pre>
</template>
```
