---
title: Managing Collections with Vue 3
description: Build dynamic lists and collections with Vue 3 for adding, removing, and editing items
wip: true
---

Collections allow users to manage dynamic lists of items—like adding multiple phone numbers to a contact or assigning multiple roles to a user. Vue 3's reactivity makes managing collections straightforward and intuitive.

> [!NOTE]
> [TODO: Screenshot] - Collection interface with add/remove buttons

## Basic Collection Component

Here's a simple component for managing a list of items:

```vue
<template>
  <div class="collection">
    <h3>{{ title }}</h3>
    
    <!-- List of items -->
    <div v-for="(item, index) in items" :key="item.id" class="collection-item">
      <div class="uk-grid-small" uk-grid>
        <div class="uk-width-expand">
          <input 
            v-model="item.value" 
            type="text" 
            class="uk-input"
            :placeholder="placeholder"
          />
        </div>
        <div class="uk-width-auto">
          <button 
            @click="removeItem(index)" 
            class="uk-button uk-button-danger uk-button-small"
          >
            <span uk-icon="trash"></span>
          </button>
        </div>
      </div>
    </div>
    
    <!-- Add button -->
    <button @click="addItem" class="uk-button uk-button-default uk-margin-top">
      <span uk-icon="plus"></span> Add {{ itemName }}
    </button>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

interface CollectionItem {
  id: number
  value: string
}

interface Props {
  title: string
  itemName?: string
  placeholder?: string
  initialItems?: CollectionItem[]
}

const props = withDefaults(defineProps<Props>(), {
  itemName: 'Item',
  placeholder: 'Enter value...',
  initialItems: () => []
})

const emit = defineEmits<{
  change: [items: CollectionItem[]]
}>()

const items = ref<CollectionItem[]>([...props.initialItems])
let nextId = items.value.length + 1

function addItem() {
  items.value.push({
    id: nextId++,
    value: ''
  })
  emit('change', items.value)
}

function removeItem(index: number) {
  items.value.splice(index, 1)
  emit('change', items.value)
}

// Expose items for parent component
defineExpose({
  items
})
</script>

<style scoped>
.collection-item {
  margin-bottom: 10px;
}
</style>
```

**Usage**:
```vue
<script setup lang="ts">
import { ref } from 'vue'
import Collection from './components/Collection.vue'

const phoneNumbers = ref([
  { id: 1, value: '555-1234' },
  { id: 2, value: '555-5678' }
])

function handleChange(items) {
  console.log('Items changed:', items)
}
</script>

<template>
  <Collection 
    title="Phone Numbers"
    item-name="Phone Number"
    placeholder="Enter phone number"
    :initial-items="phoneNumbers"
    @change="handleChange"
  />
</template>
```

## Complex Collection Items

For more complex items with multiple fields:

```vue
<template>
  <div class="role-collection">
    <h3>User Roles</h3>
    
    <div v-for="(role, index) in roles" :key="role.id" class="uk-card uk-card-default uk-card-body uk-margin-small">
      <div class="uk-grid-small" uk-grid>
        <!-- Role Selection -->
        <div class="uk-width-1-2">
          <label class="uk-form-label">Role</label>
          <select v-model="role.role_id" class="uk-select">
            <option v-for="r in availableRoles" :key="r.id" :value="r.id">
              {{ r.name }}
            </option>
          </select>
        </div>
        
        <!-- Start Date -->
        <div class="uk-width-1-4">
          <label class="uk-form-label">Start Date</label>
          <input 
            v-model="role.start_date" 
            type="date" 
            class="uk-input"
          />
        </div>
        
        <!-- End Date -->
        <div class="uk-width-1-4">
          <label class="uk-form-label">End Date</label>
          <input 
            v-model="role.end_date" 
            type="date" 
            class="uk-input"
          />
        </div>
        
        <!-- Remove Button -->
        <div class="uk-width-1-1">
          <button 
            @click="removeRole(index)" 
            class="uk-button uk-button-danger uk-button-small"
          >
            Remove Role
          </button>
        </div>
      </div>
    </div>
    
    <button @click="addRole" class="uk-button uk-button-primary uk-margin-top">
      Add Role
    </button>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

interface Role {
  id: number
  role_id: number
  start_date: string
  end_date: string
}

interface AvailableRole {
  id: number
  name: string
}

const roles = ref<Role[]>([])
const availableRoles = ref<AvailableRole[]>([
  { id: 1, name: 'Admin' },
  { id: 2, name: 'User' },
  { id: 3, name: 'Moderator' }
])

let nextId = 1

function addRole() {
  roles.value.push({
    id: nextId++,
    role_id: 1,
    start_date: new Date().toISOString().split('T')[0],
    end_date: ''
  })
}

function removeRole(index: number) {
  roles.value.splice(index, 1)
}
</script>
```

## Reusable Collection Composable

Create a composable for common collection logic:

**composables/useCollection.ts**:
```typescript
import { ref, Ref } from 'vue'

interface CollectionItem {
  id: number
  [key: string]: any
}

export function useCollection<T extends CollectionItem>(
  initialItems: T[] = [],
  itemFactory: () => Omit<T, 'id'>
) {
  const items: Ref<T[]> = ref([...initialItems])
  let nextId = items.value.length > 0 
    ? Math.max(...items.value.map(i => i.id)) + 1 
    : 1

  function add(): T {
    const newItem = {
      id: nextId++,
      ...itemFactory()
    } as T
    
    items.value.push(newItem)
    return newItem
  }

  function remove(id: number) {
    const index = items.value.findIndex(item => item.id === id)
    if (index > -1) {
      items.value.splice(index, 1)
    }
  }

  function removeAt(index: number) {
    if (index >= 0 && index < items.value.length) {
      items.value.splice(index, 1)
    }
  }

  function update(id: number, updates: Partial<T>) {
    const item = items.value.find(i => i.id === id)
    if (item) {
      Object.assign(item, updates)
    }
  }

  function clear() {
    items.value = []
  }

  function reset(newItems: T[]) {
    items.value = [...newItems]
    nextId = items.value.length > 0 
      ? Math.max(...items.value.map(i => i.id)) + 1 
      : 1
  }

  return {
    items,
    add,
    remove,
    removeAt,
    update,
    clear,
    reset
  }
}
```

**Usage**:
```vue
<script setup lang="ts">
import { useCollection } from '@/composables/useCollection'

interface PhoneNumber {
  id: number
  label: string
  number: string
}

const { items: phones, add, remove } = useCollection<PhoneNumber>(
  [{ id: 1, label: 'Mobile', number: '555-1234' }],
  () => ({ label: '', number: '' })
)

function addPhone() {
  add()
}

function removePhone(id: number) {
  remove(id)
}
</script>

<template>
  <div>
    <div v-for="phone in phones" :key="phone.id">
      <input v-model="phone.label" placeholder="Label" />
      <input v-model="phone.number" placeholder="Number" />
      <button @click="removePhone(phone.id)">Remove</button>
    </div>
    <button @click="addPhone">Add Phone</button>
  </div>
</template>
```

## Drag and Drop Reordering

Add drag-and-drop using [VueDraggable](https://github.com/SortableJS/vue.draggable.next):

```bash
npm install vuedraggable@next
```

```vue
<script setup lang="ts">
import { ref } from 'vue'
import draggable from 'vuedraggable'

const items = ref([
  { id: 1, name: 'Item 1' },
  { id: 2, name: 'Item 2' },
  { id: 3, name: 'Item 3' }
])

function logOrder() {
  console.log('New order:', items.value)
}
</script>

<template>
  <draggable 
    v-model="items" 
    item-key="id"
    @end="logOrder"
  >
    <template #item="{ element }">
      <div class="draggable-item">
        <span uk-icon="table"></span>
        {{ element.name }}
      </div>
    </template>
  </draggable>
</template>

<style scoped>
.draggable-item {
  padding: 10px;
  margin: 5px 0;
  background: #f8f8f8;
  border: 1px solid #ddd;
  cursor: move;
}
</style>
```

## Server Integration

Submit collections with forms:

```vue
<script setup lang="ts">
import { ref } from 'vue'
import axios from 'axios'
import { useCollection } from '@/composables/useCollection'

interface Email {
  id: number
  address: string
  is_primary: boolean
}

const { items: emails, add, remove } = useCollection<Email>(
  [],
  () => ({ address: '', is_primary: false })
)

async function saveEmails() {
  try {
    await axios.post('/api/user/emails', {
      emails: emails.value.map(e => ({
        address: e.address,
        is_primary: e.is_primary
      }))
    }, {
      headers: {
        [site.csrf.keys.name]: site.csrf.name,
        [site.csrf.keys.value]: site.csrf.value
      }
    })
    
    alert('Emails saved!')
  } catch (error) {
    console.error('Failed to save:', error)
  }
}
</script>
```

## Validation

Validate collection items:

```vue
<script setup lang="ts">
import { ref, computed } from 'vue'

const items = ref([
  { id: 1, email: '' }
])

const errors = ref<Record<number, string>>({})

const isValid = computed(() => {
  errors.value = {}
  
  items.value.forEach(item => {
    if (!item.email) {
      errors.value[item.id] = 'Email is required'
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(item.email)) {
      errors.value[item.id] = 'Invalid email format'
    }
  })
  
  return Object.keys(errors.value).length === 0
})
</script>

<template>
  <div v-for="item in items" :key="item.id">
    <input v-model="item.email" />
    <span v-if="errors[item.id]" class="uk-text-danger">
      {{ errors[item.id] }}
    </span>
  </div>
</template>
```

## Best Practices

### 1. Use Unique Keys

Always provide unique `:key` for v-for:

```vue
<div v-for="item in items" :key="item.id">
  <!-- content -->
</div>
```

### 2. Validate Empty Collections

Check if collection has items:

```vue
<div v-if="items.length === 0" class="uk-alert-warning" uk-alert>
  No items added yet. Click "Add Item" to get started.
</div>
```

### 3. Confirm Before Removing

Prevent accidental deletions:

```typescript
function removeItem(id: number) {
  if (confirm('Are you sure you want to remove this item?')) {
    remove(id)
  }
}
```

### 4. Limit Collection Size

Prevent performance issues:

```typescript
const MAX_ITEMS = 10

function addItem() {
  if (items.value.length >= MAX_ITEMS) {
    alert(`Maximum ${MAX_ITEMS} items allowed`)
    return
  }
  add()
}
```

## What's Next?

- **[Forms](client-side-code/components/forms)**: Submit collections as part of forms
- **[Tables](client-side-code/components/tables)**: Display collections in tables
- **[Alerts](client-side-code/components/alerts)**: Show feedback when modifying collections

## Further Reading

- [Vue 3 List Rendering](https://vuejs.org/guide/essentials/list.html)
- [VueDraggable](https://github.com/SortableJS/vue.draggable.next)
- [Vue Transitions](https://vuejs.org/guide/built-ins/transition.html)
