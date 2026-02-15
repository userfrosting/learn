---
title: Alerts and Notifications
description: Display user feedback with Vue 3 alert components and UIkit notifications
wip: true
---

Providing feedback to users is essential for a good user experience. UserFrosting 6.0 uses Vue 3 components and UIkit notifications to display alerts, messages, and toasts throughout the application.

This guide covers different types of user feedback and how to implement them effectively.

## UIkit Notifications

UIkit provides a built-in notification system for temporary messages:

### Basic Usage

```typescript
import UIkit from 'uikit'

// Simple notification
UIkit.notification('User created successfully!')

// With options
UIkit.notification('Settings saved!', {
  status: 'success',
  pos: 'top-right',
  timeout: 3000
})

// Different statuses
UIkit.notification('Warning: This action cannot be undone', 'warning')
UIkit.notification('Error: Failed to save', 'danger')
UIkit.notification('Info: New features available', 'primary')
```

### Notification Options

```typescript
UIkit.notification(message, {
  status: 'primary' | 'success' | 'warning' | 'danger',  // Style
  pos: 'top-right' | 'top-center' | 'top-left' |         // Position
       'bottom-right' | 'bottom-center' | 'bottom-left',
  timeout: 5000,        // Auto-close after ms (0 = no auto-close)
  group: null,          // Group name to stack related notifications
  closeButton: true     // Show close button
})
```

## Vue Alert Component

Create a reusable alert component for persistent messages:

**AlertBox.vue**:
```vue
<template>
  <div
    v-if="visible"
    class="uk-alert"
    :class="alertClass"
    uk-alert
  >
    <a class="uk-alert-close" uk-close @click="close"></a>
    <h3 v-if="title">{{ title }}</h3>
    <p>{{ message }}</p>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'

interface Props {
  type?: 'primary' | 'success' | 'warning' | 'danger'
  title?: string
  message: string
  closeable?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  type: 'primary',
  closeable: true
})

const emit = defineEmits<{
  close: []
}>()

const visible = ref(true)

const alertClass = computed(() => `uk-alert-${props.type}`)

function close() {
  visible.value = false
  emit('close')
}
</script>
```

**Usage**:
```vue
<script setup lang="ts">
import { ref } from 'vue'
import AlertBox from './components/AlertBox.vue'

const showAlert = ref(true)
</script>

<template>
  <AlertBox
    v-if="showAlert"
    type="success"
    title="Success!"
    message="Your profile has been updated."
    @close="showAlert = false"
  />
</template>
```

## Alert Stream Integration

UserFrosting's alert stream system sends messages from the server. Here's how to fetch and display them:

### Fetching Alerts

```typescript
import axios from 'axios'
import UIkit from 'uikit'

interface Alert {
  type: 'success' | 'danger' | 'warning' | 'info'
  message: string
}

async function fetchAndDisplayAlerts() {
  try {
    const response = await axios.get<Alert[]>(
      `${site.uri.public}/alerts`
    )

    response.data.forEach(alert => {
      UIkit.notification(alert.message, {
        status: alert.type === 'danger' ? 'danger' : alert.type,
        timeout: 5000
      })
    })
  } catch (error) {
    console.error('Failed to fetch alerts:', error)
  }
}
```

### Alert Composable

Create a composable for managing alerts:

**composables/useAlerts.ts**:
```typescript
import { ref, Ref } from 'vue'
import axios from 'axios'
import UIkit from 'uikit'

interface Alert {
  id: string
  type: 'success' | 'danger' | 'warning' | 'info'
  message: string
  title?: string
}

export function useAlerts() {
  const alerts: Ref<Alert[]> = ref([])
  const loading = ref(false)

  async function fetchAlerts() {
    loading.value = true

    try {
      const response = await axios.get(`${site.uri.public}/alerts`)
      alerts.value = response.data

      // Display as UIkit notifications
      response.data.forEach((alert: Alert) => {
        showNotification(alert.message, alert.type)
      })
    } catch (error) {
      console.error('Failed to fetch alerts:', error)
    } finally {
      loading.value = false
    }
  }

  function showNotification(
    message: string,
    type: 'success' | 'danger' | 'warning' | 'info' = 'info'
  ) {
    UIkit.notification(message, {
      status: type === 'danger' ? 'danger' : type,
      pos: 'top-right',
      timeout: 5000
    })
  }

  function showSuccess(message: string) {
    showNotification(message, 'success')
  }

  function showError(message: string) {
    showNotification(message, 'danger')
  }

  function showWarning(message: string) {
    showNotification(message, 'warning')
  }

  function showInfo(message: string) {
    showNotification(message, 'info')
  }

  function add(alert: Omit<Alert, 'id'>) {
    const newAlert: Alert = {
      ...alert,
      id: Date.now().toString() + Math.random()
    }
    alerts.value.push(newAlert)
    showNotification(alert.message, alert.type)
  }

  function remove(id: string) {
    alerts.value = alerts.value.filter(alert => alert.id !== id)
  }

  function clear() {
    alerts.value = []
  }

  return {
    alerts,
    loading,
    fetchAlerts,
    showNotification,
    showSuccess,
    showError,
    showWarning,
    showInfo,
    add,
    remove,
    clear
  }
}
```

**Usage in component**:
```vue
<script setup lang="ts">
import { onMounted } from 'vue'
import { useAlerts } from '@/composables/useAlerts'
import axios from 'axios'

const { showSuccess, showError, fetchAlerts } = useAlerts()

onMounted(() => {
  // Fetch alerts on component mount
  fetchAlerts()
})

async function saveSettings() {
  try {
    await axios.post('/api/settings', { /* data */ })
    showSuccess('Settings saved successfully!')
  } catch (error) {
    showError('Failed to save settings')
  }
}
</script>
```

## Toast Notifications Component

For more advanced toast notifications, create a dedicated component:

**ToastManager.vue**:
```vue
<template>
  <teleport to="body">
    <div class="toast-container">
      <transition-group name="toast">
        <div
          v-for="toast in toasts"
          :key="toast.id"
          class="toast uk-alert"
          :class="`uk-alert-${toast.type}`"
        >
          <a class="uk-alert-close" uk-close @click="removeToast(toast.id)"></a>
          <p>{{ toast.message }}</p>
        </div>
      </transition-group>
    </div>
  </teleport>
</template>

<script setup lang="ts">
import { ref } from 'vue'

interface Toast {
  id: string
  type: 'success' | 'danger' | 'warning' | 'info'
  message: string
  duration: number
}

const toasts = ref<Toast[]>([])

function addToast(
  message: string,
  type: Toast['type'] = 'info',
  duration = 5000
) {
  const id = Date.now().toString() + Math.random()

  toasts.value.push({ id, message, type, duration })

  if (duration > 0) {
    setTimeout(() => removeToast(id), duration)
  }

  return id
}

function removeToast(id: string) {
  toasts.value = toasts.value.filter(toast => toast.id !== id)
}

defineExpose({
  addToast,
  removeToast
})
</script>

<style scoped>
.toast-container {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 9999;
  max-width: 400px;
}

.toast {
  margin-bottom: 10px;
  box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from {
  opacity: 0;
  transform: translateX(50px);
}

.toast-leave-to {
  opacity: 0;
  transform: translateX(50px);
}
</style>
```

**App-level usage**:
```typescript
// main.ts
import { createApp } from 'vue'
import ToastManager from './components/ToastManager.vue'

const app = createApp({})

// Make toast manager available globally
const toastContainer = document.createElement('div')
document.body.appendChild(toastContainer)

const toastApp = createApp(ToastManager)
const toastInstance = toastApp.mount(toastContainer)

// Make it accessible globally
app.config.globalProperties.$toast = toastInstance

app.mount('#app')
```

## Error Handling

Display errors from AJAX requests:

```vue
<script setup lang="ts">
import { ref } from 'vue'
import axios, { AxiosError } from 'axios'
import { useAlerts } from '@/composables/useAlerts'

const { showError, showSuccess } = useAlerts()

async function performAction() {
  try {
    await axios.post('/api/action')
    showSuccess('Action completed!')

  } catch (error) {
    handleError(error as AxiosError)
  }
}

function handleError(error: AxiosError) {
  if (error.response) {
    // Server responded with error status
    const message = error.response.data?.message || 'An error occurred'
    showError(message)

    // Handle validation errors
    if (error.response.status === 422 && error.response.data?.errors) {
      Object.values(error.response.data.errors).forEach((msgs: any) => {
        if (Array.isArray(msgs)) {
          msgs.forEach(msg => showError(msg))
        } else {
          showError(msgs)
        }
      })
    }
  } else if (error.request) {
    // Request made but no response
    showError('Network error: Could not reach server')
  } else {
    // Something else went wrong
    showError('An unexpected error occurred')
  }
}
</script>
```

## Loading States with Alerts

Show loading indicators and completion messages:

```vue
<script setup lang="ts">
import { ref } from 'vue'
import UIkit from 'uikit'

const isLoading = ref(false)

async function performLongOperation() {
  isLoading.value = true

  // Show loading notification
  const loadingNotification = UIkit.notification(
    'Processing... Please wait',
    {
      status: 'primary',
      timeout: 0  // Don't auto-close
    }
  )

  try {
    await longRunningTask()

    // Close loading, show success
    loadingNotification.close()
    UIkit.notification('Operation completed!', 'success')

  } catch (error) {
    loadingNotification.close()
    UIkit.notification('Operation failed', 'danger')

  } finally {
    isLoading.value = false
  }
}
</script>
```

## Confirmation Dialogs

Use UIkit modals for confirmations:

```vue
<script setup lang="ts">
import UIkit from 'uikit'

async function deleteUser(userId: number) {
  // UIkit doesn't have built-in confirm, use native or create modal
  const confirmed = confirm('Are you sure you want to delete this user?')

  if (confirmed) {
    try {
      await axios.delete(`/api/users/${userId}`)
      UIkit.notification('User deleted', 'success')
    } catch (error) {
      UIkit.notification('Failed to delete user', 'danger')
    }
  }
}

// Or create a custom modal component
function showConfirmModal(message: string, onConfirm: () => void) {
  // Create modal element
  const modal = document.createElement('div')
  modal.innerHTML = `
    <div id="confirm-modal" uk-modal>
      <div class="uk-modal-dialog uk-modal-body">
        <h2 class="uk-modal-title">Confirm</h2>
        <p>${message}</p>
        <p class="uk-text-right">
          <button class="uk-button uk-button-default uk-modal-close">Cancel</button>
          <button class="uk-button uk-button-primary" id="confirm-btn">Confirm</button>
        </p>
      </div>
    </div>
  `
  document.body.appendChild(modal)

  const confirmBtn = document.getElementById('confirm-btn')
  confirmBtn?.addEventListener('click', () => {
    onConfirm()
    UIkit.modal('#confirm-modal').hide()
    document.body.removeChild(modal)
  })

  UIkit.modal('#confirm-modal').show()
}
</script>
```

## Best Practices

### 1. Use Appropriate Alert Types

Match the alert type to the message:
- **Success**: Completed actions, confirmations
- **Danger/Error**: Failures, critical issues
- **Warning**: Cautions, reversible actions
- **Info**: General information, tips

### 2. Auto-Dismiss Non-Critical Alerts

Success messages should auto-dismiss:
```typescript
UIkit.notification('Saved!', {
  status: 'success',
  timeout: 3000
})
```

Errors might need manual dismissal:
```typescript
UIkit.notification('Critical error occurred', {
  status: 'danger',
  timeout: 0  // Requires manual close
})
```

### 3. Position Appropriately

Top-right is standard for notifications:
```typescript
UIkit.notification(message, { pos: 'top-right' })
```

Use top-center for important announcements.

### 4. Avoid Alert Spam

Don't show multiple alerts for the same action:

```typescript
// ❌ Bad: Shows many alerts
errors.forEach(error => showError(error))

// ✅ Good: Combine into one message
showError(`${errors.length} errors occurred`)
```

### 5. Provide Actionable Messages

Be specific about what happened and what to do:

✅ **Good**: "Failed to save. Please check your internet connection and try again."
❌ **Bad**: "Error occurred"

## Integration with Forms

Automatically show alerts after form submission:

```vue
<script setup lang="ts">
import { useAlerts } from '@/composables/useAlerts'
import { useForm } from '@/composables/useForm'

const { showSuccess, showError } = useAlerts()

const { form, submit } = useForm({
  initialData: { name: '', email: '' },
  onSuccess: () => {
    showSuccess('Form submitted successfully!')
  },
  onError: (error) => {
    showError(error.response?.data?.message || 'Submission failed')
  }
})

async function handleSubmit() {
  await submit('/api/endpoint')
}
</script>
```

## What's Next?

- **[Forms](/ui-theming/forms)**: Build validated forms that show alerts
- **[Tables](/ui-theming/tables)**: Display data with loading states
- **[Collections](/ui-theming/collections)**: Manage lists with feedback

## Further Reading

- [UIkit Notification Component](https://getuikit.com/docs/notification)
- [UIkit Alert Component](https://getuikit.com/docs/alert)
- [Vue 3 Teleport](https://vuejs.org/guide/built-ins/teleport.html)
- [Vue 3 Transitions](https://vuejs.org/guide/built-ins/transition.html)
