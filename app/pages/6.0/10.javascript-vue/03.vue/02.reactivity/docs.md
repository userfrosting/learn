---
title: Understanding Reactivity
description: Learn how Vue reactivity works with ref, reactive, and computed.
---

Vue's reactivity system is what makes the magic happen. When your data changes, the UI updates automatically without manual DOM manipulation.

## Core Reactivity APIs

Three core APIs power most day-to-day Vue state management:

1. `ref()` for primitive values
2. `reactive()` for objects and arrays
3. `computed()` for derived values

### Using ref()

Use [`ref()`](https://vuejs.org/api/reactivity-core.html#ref) for primitives such as numbers, strings, and booleans:

```vue
<script setup lang="ts">
import { ref } from 'vue'

const count = ref(0)
const message = ref('Hello')
const isActive = ref(true)

function increment() {
  count.value++
}

function toggleActive() {
  isActive.value = !isActive.value
}
</script>

<template>
  <p>Count: {{ count }}</p>
  <p>{{ message }}</p>
  <p v-if="isActive">Active!</p>

  <button @click="increment">Increment</button>
</template>
```

In JavaScript, access refs with `.value`. In templates, Vue unwraps refs automatically.

### Using reactive()

Use [`reactive()`](https://vuejs.org/api/reactivity-core.html#reactive) for objects and arrays:

```vue
<script setup lang="ts">
import { reactive } from 'vue'

const user = reactive({
  name: 'Alex',
  email: 'alex@example.com',
  roles: ['user', 'moderator']
})

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

> [!TIP]
> Rule of thumb: use `ref()` for primitives or when replacing a full value, and `reactive()` for objects and arrays when mutating properties.

### Using computed()

[Computed properties](https://vuejs.org/guide/essentials/computed.html) derive values from reactive state and update automatically:

```vue
<script setup lang="ts">
import { ref, computed } from 'vue'

const firstName = ref('John')
const lastName = ref('Doe')

const fullName = computed(() => `${firstName.value} ${lastName.value}`)
</script>

<template>
  <p>{{ fullName }}</p>
</template>
```

Computed values are cached and recomputed only when their dependencies change.
