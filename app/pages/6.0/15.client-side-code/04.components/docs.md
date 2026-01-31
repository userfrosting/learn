---
title: Common UI Components
description: Reusable Vue 3 components for building interactive user interfaces in UserFrosting
wip: true
---

UserFrosting 6.0 provides patterns and examples for building common UI components with Vue 3. This section covers the most frequently used interactive elements in web applications.

## Component Categories

### Forms

Handle user input with validated, AJAX-powered forms. Learn how to:
- Build reactive forms with two-way data binding
- Implement client and server-side validation
- Submit data with proper CSRF protection
- Display loading states and error messages
- Create reusable form composables

**[Learn more about Forms →](client-side-code/components/forms)**

### Tables

Display and manipulate collections of data with sortable, filterable, paginated tables:
- Client-side sorting and filtering
- Server-side pagination with Sprunjes
- Integration with TanStack Table library
- Reusable table composables
- Loading states and empty states

**[Learn more about Tables →](client-side-code/components/tables)**

### Collections

Manage dynamic lists of items where users can add, remove, and reorder entries:
- Dynamic form fields (add/remove)
- Complex collection items with multiple fields
- Drag-and-drop reordering
- Validation for collection items
- Server integration patterns

**[Learn more about Collections →](client-side-code/components/collections)**

### Alerts and Notifications

Provide user feedback through alerts, notifications, and toast messages:
- UIkit notification system
- Alert stream integration
- Reusable alert composables
- Error handling patterns
- Confirmation dialogs

**[Learn more about Alerts →](client-side-code/components/alerts)**

## Component Patterns

### Reusability

Build components that can be reused across your application:

```vue
<!-- Reusable Button Component -->
<script setup lang="ts">
interface Props {
  variant?: 'primary' | 'default' | 'danger'
  size?: 'small' | 'default' | 'large'
  disabled?: boolean
  loading?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'default',
  size: 'default',
  disabled: false,
  loading: false
})

const emit = defineEmits<{
  click: [event: MouseEvent]
}>()
</script>

<template>
  <button
    :class="[
      'uk-button',
      `uk-button-${variant}`,
      `uk-button-${size}`
    ]"
    :disabled="disabled || loading"
    @click="emit('click', $event)"
  >
    <span v-if="loading" uk-spinner="ratio: 0.5"></span>
    <slot v-else />
  </button>
</template>
```

### Composition

Combine smaller components to build complex UIs:

```vue
<template>
  <UserForm>
    <FormField label="Username" v-model="user.username" />
    <FormField label="Email" v-model="user.email" type="email" />
    <FormActions>
      <Button variant="primary" @click="save">Save</Button>
      <Button @click="cancel">Cancel</Button>
    </FormActions>
  </UserForm>
</template>
```

### Composables

Extract and share logic across components:

```typescript
// composables/useForm.ts
export function useForm(initialData, onSubmit) {
  const form = ref({ ...initialData })
  const errors = ref({})
  const isSubmitting = ref(false)

  async function submit() {
    isSubmitting.value = true
    try {
      await onSubmit(form.value)
    } catch (e) {
      errors.value = e.response?.data?.errors || {}
    } finally {
      isSubmitting.value = false
    }
  }

  return { form, errors, isSubmitting, submit }
}
```

## UIkit Integration

UserFrosting uses [UIkit](https://getuikit.com/) for styling. Common UIkit components you'll use:

### Buttons
```html
<button class="uk-button uk-button-primary">Primary</button>
<button class="uk-button uk-button-default">Default</button>
<button class="uk-button uk-button-danger">Danger</button>
```

### Cards
```html
<div class="uk-card uk-card-default uk-card-body">
  <h3 class="uk-card-title">Title</h3>
  <p>Content</p>
</div>
```

### Modals
```html
<div id="my-modal" uk-modal>
  <div class="uk-modal-dialog uk-modal-body">
    <h2 class="uk-modal-title">Title</h2>
    <p>Modal content</p>
  </div>
</div>
```

**Open programmatically**:
```typescript
import UIkit from 'uikit'

UIkit.modal('#my-modal').show()
```

### Dropdowns
```html
<button class="uk-button uk-button-default" type="button">
  Menu
</button>
<div uk-dropdown>
  <ul class="uk-nav uk-dropdown-nav">
    <li><a href="#">Item 1</a></li>
    <li><a href="#">Item 2</a></li>
  </ul>
</div>
```

## Best Practices

### 1. Component Organization

Organize components by feature or function:

```
components/
├── common/           # Reusable across app
│   ├── Button.vue
│   ├── Input.vue
│   └── Modal.vue
├── user/            # User-related
│   ├── UserCard.vue
│   ├── UserForm.vue
│   └── UserTable.vue
└── layout/          # Layout components
    ├── Header.vue
    ├── Sidebar.vue
    └── Footer.vue
```

### 2. Props and Events

Follow Vue's data flow pattern:
- **Props down**: Parent passes data to child
- **Events up**: Child notifies parent of changes

```vue
<!-- Parent -->
<UserCard :user="user" @edit="handleEdit" @delete="handleDelete" />

<!-- Child -->
<script setup>
const props = defineProps(['user'])
const emit = defineEmits(['edit', 'delete'])
</script>
```

### 3. Type Safety

Use TypeScript for better developer experience:

```typescript
interface User {
  id: number
  username: string
  email: string
}

const props = defineProps<{
  user: User
  editable?: boolean
}>()
```

### 4. Error Handling

Always handle errors gracefully:

```typescript
try {
  await performAction()
  showSuccess('Action completed')
} catch (error) {
  showError('Action failed')
  console.error(error)
}
```

## What's Next?

Dive into specific component types:

- **[Forms](client-side-code/components/forms)** - User input and validation
- **[Tables](client-side-code/components/tables)** - Data display and manipulation
- **[Collections](client-side-code/components/collections)** - Dynamic lists
- **[Alerts](client-side-code/components/alerts)** - User feedback

Or explore foundational concepts:

- **[Vue Components](client-side-code/vue-components)** - Vue 3 basics
- **[Exporting Variables](client-side-code/exporting-variables)** - Data passing
- **[Asset Management](asset-management)** - Building and bundling

## Further Reading

- [Vue 3 Component Basics](https://vuejs.org/guide/essentials/component-basics.html)
- [UIkit Components](https://getuikit.com/docs/introduction)
- [TypeScript with Vue](https://vuejs.org/guide/typescript/overview.html)
