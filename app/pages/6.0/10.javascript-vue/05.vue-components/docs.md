---
title: Building Vue 3 Components
description: Master advanced Vue 3 patterns including reactivity, template directives, component communication, lifecycle hooks, and composables.
wip: true
---

Now that you understand [how to create basic Vue components](/javascript-vue/working-with-vue), it's time to dive deeper. This page covers the advanced patterns, template syntax, and communication techniques you'll need to build complex, production-ready Vue applications in UserFrosting.

> [!TIP]
> If you're new to Vue, start with [Working with Vue](/javascript-vue/working-with-vue) first. This page assumes you're comfortable with SFC structure, basic reactivity (`ref`, `reactive`, `computed`), and component basics.

## Advanced Reactivity Patterns

While [Working with Vue](/javascript-vue/working-with-vue) covered the basics of `ref()`, `reactive()`, and `computed()`, let's explore more advanced patterns you'll encounter in real applications.

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

// Immediate execution (run on mount)
watch(searchQuery, (value) => {
  // ...
}, { immediate: true })

// Deep watching (for nested objects)
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

`watchEffect()` automatically tracks dependencies and re-runs when any of them change:

```vue
<script setup lang="ts">
import { ref, watchEffect } from 'vue'

const count = ref(0)
const doubled = ref(0)

// Automatically tracks `count` and runs when it changes
watchEffect(() => {
  doubled.value = count.value * 2
  console.log(`Count: ${count.value}, Doubled: ${doubled.value}`)
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
  <p>{{ isActive ? 'Active' : 'Inactive' }}</p>
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

## Component Communication

### Props (Parent → Child)

Pass data from parent to child components:

**Parent.vue**:
```vue
<template>
  <UserCard
    :user-id="123"
    :username="'alex'"
    :email="'alex@example.com'"
    :is-admin="true"
  />
</template>

<script setup lang="ts">
import UserCard from './UserCard.vue'
</script>
```

**UserCard.vue**:
```vue
<script setup lang="ts">
// Define props with TypeScript
const props = defineProps<{
  userId: number
  username: string
  email: string
  isAdmin?: boolean // Optional prop
}>()

// Or with defaults
const props = withDefaults(
  defineProps<{
    userId: number
    username: string
    isAdmin?: boolean
  }>(),
  {
    isAdmin: false
  }
)
</script>

<template>
  <div>
    <h3>{{ username }}</h3>
    <p>{{ email }}</p>
    <span v-if="isAdmin">👑 Admin</span>
  </div>
</template>
```

### Emits (Child → Parent)

Send events from child to parent:

**Child.vue**:
```vue
<script setup lang="ts">
// Define events with TypeScript
const emit = defineEmits<{
  userDeleted: [userId: number]
  statusChanged: [status: string, timestamp: Date]
}>()

function deleteUser(userId: number) {
  // Perform deletion...
  emit('userDeleted', userId)
}

function changeStatus(status: string) {
  emit('statusChanged', status, new Date())
}
</script>

<template>
  <button @click="deleteUser(props.userId)">Delete</button>
</template>
```

**Parent.vue**:
```vue
<template>
  <UserCard
    @user-deleted="handleUserDeleted"
    @status-changed="handleStatusChange"
  />
</template>

<script setup lang="ts">
function handleUserDeleted(userId: number) {
  console.log('User deleted:', userId)
  // Refresh list, show notification, etc.
}

function handleStatusChange(status: string, timestamp: Date) {
  console.log(`Status changed to ${status} at ${timestamp}`)
}
</script>
```

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

## Using Components in UserFrosting

### Step 1: Create the Component

**app/assets/components/UserGreeting.vue**:
```vue
<template>
  <div class="uk-card uk-card-default uk-card-body">
    <h3>Hello, {{ username }}!</h3>
    <p>You have {{ messageCount }} new messages</p>
    <button class="uk-button uk-button-primary" @click="viewMessages">
      View Messages
    </button>
  </div>
</template>

<script setup lang="ts">
const props = defineProps<{
  username: string
  messageCount: number
}>()

const emit = defineEmits<{
  viewMessages: []
}>()

function viewMessages() {
  emit('viewMessages')
}
</script>

<style scoped>
.uk-card {
  margin: 1rem 0;
}
</style>
```

### Step 2: Register in Entry Point

**app/assets/main.ts**:
```typescript
import { createApp } from 'vue'
import UserGreeting from './components/UserGreeting.vue'

const app = createApp({})

// Register component globally
app.component('UserGreeting', UserGreeting)

// Mount to DOM
app.mount('#app')
```

### Step 3: Use in Twig Template

```twig
{# Make sure you have a mount point #}
<div id="app">
    <user-greeting
        username="{{ current_user.username }}"
        :message-count="{{ messageCount }}"
        @view-messages="handleViewMessages"
    />
</div>

{# Load your compiled assets #}
{% block scripts_page %}
    {{ vite_js('main.ts') }}
{% endblock %}
```

## Composables (Reusable Logic)

Extract and reuse logic across components with composables:

**composables/useAuth.ts**:
```typescript
import { ref, computed } from 'vue'
import axios from 'axios'

export function useAuth() {
  const user = ref(null)
  const loading = ref(false)
  const error = ref<Error | null>(null)

  const isLoggedIn = computed(() => user.value !== null)

  async function login(username: string, password: string) {
    loading.value = true
    error.value = null

    try {
      const response = await axios.post('/api/login', {
        username,
        password
      })
      user.value = response.data.user
    } catch (e) {
      error.value = e as Error
    } finally {
      loading.value = false
    }
  }

  function logout() {
    user.value = null
  }

  return {
    user,
    loading,
    error,
    isLoggedIn,
    login,
    logout
  }
}
```

**Usage in component**:
```vue
<script setup lang="ts">
import { useAuth } from '@/composables/useAuth'

const { user, isLoggedIn, login, logout } = useAuth()

async function handleLogin() {
  await login('alex', 'password123')
}
</script>

<template>
  <div v-if="isLoggedIn">
    <p>Welcome, {{ user.name }}!</p>
    <button @click="logout">Logout</button>
  </div>
  <div v-else>
    <button @click="handleLogin">Login</button>
  </div>
</template>
```

## TypeScript Best Practices

UserFrosting components use TypeScript for better developer experience:

```vue
<script setup lang="ts">
import { ref, Ref } from 'vue'

// Define interfaces for complex types
interface User {
  id: number
  username: string
  email: string
  roles: string[]
}

// Type your props
const props = defineProps<{
  users: User[]
  onSelect?: (user: User) => void
}>()

// Type your refs
const selectedUser: Ref<User | null> = ref(null)
const users: Ref<User[]> = ref([])

// Type your functions
function selectUser(user: User): void {
  selectedUser.value = user
  props.onSelect?.(user)
}

async function fetchUsers(): Promise<User[]> {
  const response = await axios.get<User[]>('/api/users')
  return response.data
}
</script>
```

## Best Practices

### 1. Keep Components Focused

Each component should have a single, clear purpose:

✅ **Good**:
- `UserCard.vue` - Display user info
- `UserForm.vue` - Edit user
- `UserList.vue` - List users

❌ **Bad**:
- `UserEverything.vue` - Does all user-related things

### 2. Use Scoped Styles

Always scope styles to avoid global conflicts:

```vue
<style scoped>
/* Only applies to this component */
.card {
  border: 1px solid #ddd;
}
</style>
```

### 3. Props Down, Events Up

Follow unidirectional data flow:
- Parent passes data to child via props
- Child notifies parent via events
- Never mutate props directly

```vue
<!-- ✅ Good: Emit event -->
<script setup>
const emit = defineEmits(['update'])
function change() {
  emit('update', newValue)
}
</script>

<!-- ❌ Bad: Mutate prop -->
<script setup>
const props = defineProps(['value'])
function change() {
  props.value = newValue // DON'T DO THIS
}
</script>
```

### 4. Extract Reusable Logic

Move shared logic to composables:

```typescript
// composables/useFetch.ts
export function useFetch<T>(url: string) {
  const data = ref<T | null>(null)
  const loading = ref(false)
  const error = ref<Error | null>(null)

  async function fetch() {
    loading.value = true
    try {
      const response = await axios.get<T>(url)
      data.value = response.data
    } catch (e) {
      error.value = e as Error
    } finally {
      loading.value = false
    }
  }

  return { data, loading, error, fetch }
}
```

## Debugging Vue Components

### Vue DevTools

Install [Vue DevTools](https://devtools.vuejs.org/) browser extension to:
- Inspect component tree
- View component state and props
- Track emitted events
- Profile component performance
- Time-travel debug (see state history)

### Console Debugging

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

## What's Next?

Now that you understand Vue 3 components, learn how to build specific UI patterns:

- **[Forms](/ui-theming/forms)**: Build validated, AJAX-powered forms
- **[Tables](/ui-theming/tables)**: Create sortable, filterable data tables
- **[Alerts](/ui-theming/alerts)**: Display notifications and messages
- **[Collections](/ui-theming/collections)**: Manage dynamic lists

## Further Learning

- **[Vue 3 Official Guide](https://vuejs.org/guide/)** - Comprehensive Vue documentation
- **[TypeScript with Vue](https://vuejs.org/guide/typescript/overview.html)** - TypeScript integration
- **[Vite Documentation](https://vitejs.dev/guide/)** - Build tool details
- **[Composition API FAQ](https://vuejs.org/guide/extras/composition-api-faq.html)** - Why Composition API?

> [!TIP]
> Don't try to learn everything at once. Start with basic components, add reactivity, then gradually explore advanced features like composables and TypeScript as you need them.
