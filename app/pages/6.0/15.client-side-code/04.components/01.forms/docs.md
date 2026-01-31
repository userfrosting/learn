---
title: Building Forms with Vue 3
description: Create validated, AJAX-powered forms using Vue 3 and modern form handling patterns
wip: true
---

Forms are essential for user interaction in web applications. UserFrosting 6.0 uses Vue 3 to create reactive, validated forms that submit via AJAX, providing a smooth user experience without full page reloads.

This guide shows you how to build forms with Vue 3, handle validation, submit data to your API, and display feedback to users.

## Basic Form Component

Here's a simple login form component to get started:

**LoginForm.vue**:
```vue
<template>
  <form @submit.prevent="handleSubmit" class="uk-form-stacked">
    <!-- CSRF Token (UserFrosting requirement) -->
    <input type="hidden" :name="csrfName" :value="csrfValue" />
    
    <!-- Username Field -->
    <div class="uk-margin">
      <label class="uk-form-label" for="username">Username</label>
      <div class="uk-form-controls">
        <input 
          id="username"
          v-model="form.username" 
          type="text" 
          class="uk-input"
          :class="{ 'uk-form-danger': errors.username }"
          placeholder="Enter your username"
        />
        <div v-if="errors.username" class="uk-text-danger uk-text-small">
          {{ errors.username }}
        </div>
      </div>
    </div>
    
    <!-- Password Field -->
    <div class="uk-margin">
      <label class="uk-form-label" for="password">Password</label>
      <div class="uk-form-controls">
        <input 
          id="password"
          v-model="form.password" 
          type="password" 
          class="uk-input"
          :class="{ 'uk-form-danger': errors.password }"
          placeholder="Enter your password"
        />
        <div v-if="errors.password" class="uk-text-danger uk-text-small">
          {{ errors.password }}
        </div>
      </div>
    </div>
    
    <!-- Remember Me Checkbox -->
    <div class="uk-margin">
      <label>
        <input v-model="form.rememberMe" type="checkbox" class="uk-checkbox" />
        Remember me
      </label>
    </div>
    
    <!-- Submit Button -->
    <div class="uk-margin">
      <button 
        type="submit" 
        class="uk-button uk-button-primary"
        :disabled="isSubmitting"
      >
        {{ isSubmitting ? 'Signing in...' : 'Sign in' }}
      </button>
    </div>
    
    <!-- Error/Success Messages -->
    <div v-if="successMessage" class="uk-alert-success" uk-alert>
      <p>{{ successMessage }}</p>
    </div>
  </form>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import axios from 'axios'

// Form state
const form = ref({
  username: '',
  password: '',
  rememberMe: false
})

// UI state
const isSubmitting = ref(false)
const errors = ref<Record<string, string>>({})
const successMessage = ref('')

// CSRF tokens (from global site object)
const csrfName = computed(() => site.csrf.keys.name)
const csrfValue = computed(() => site.csrf.name)

// Validation
function validateForm(): boolean {
  errors.value = {}
  
  if (!form.value.username.trim()) {
    errors.value.username = 'Username is required'
  }
  
  if (!form.value.password) {
    errors.value.password = 'Password is required'
  }
  
  return Object.keys(errors.value).length === 0
}

// Submit handler
async function handleSubmit() {
  if (!validateForm()) {
    return
  }
  
  isSubmitting.value = true
  errors.value = {}
  successMessage.value = ''
  
  try {
    const response = await axios.post(
      `${site.uri.public}/account/login`,
      {
        user_name: form.value.username,
        password: form.value.password,
        rememberme: form.value.rememberMe
      },
      {
        headers: {
          [csrfName.value]: csrfValue.value
        }
      }
    )
    
    successMessage.value = 'Login successful! Redirecting...'
    
    // Redirect after success
    setTimeout(() => {
      window.location.href = response.data.redirect || '/dashboard'
    }, 1000)
    
  } catch (error: any) {
    if (error.response?.data?.errors) {
      // Server validation errors
      errors.value = error.response.data.errors
    } else {
      errors.value.general = error.response?.data?.message || 'Login failed'
    }
  } finally {
    isSubmitting.value = false
  }
}
</script>

<style scoped>
.uk-form-danger {
  border-color: #f0506e;
}
</style>
```

## Form Handling Patterns

### 1. Two-Way Data Binding

Use `v-model` for automatic synchronization between form inputs and data:

```vue
<script setup lang="ts">
import { ref } from 'vue'

const form = ref({
  email: '',
  age: 0,
  newsletter: false,
  role: 'user'
})
</script>

<template>
  <!-- Text input -->
  <input v-model="form.email" type="email" />
  
  <!-- Number input -->
  <input v-model.number="form.age" type="number" />
  
  <!-- Checkbox -->
  <input v-model="form.newsletter" type="checkbox" />
  
  <!-- Select dropdown -->
  <select v-model="form.role">
    <option value="user">User</option>
    <option value="admin">Admin</option>
  </select>
</template>
```

### 2. Form Validation

#### Client-Side Validation

Validate before submitting:

```typescript
interface FormErrors {
  [key: string]: string
}

const errors = ref<FormErrors>({})

function validateEmail(email: string): boolean {
  const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return regex.test(email)
}

function validateForm(): boolean {
  errors.value = {}
  
  // Required fields
  if (!form.value.username.trim()) {
    errors.value.username = 'Username is required'
  } else if (form.value.username.length < 3) {
    errors.value.username = 'Username must be at least 3 characters'
  }
  
  // Email validation
  if (!form.value.email) {
    errors.value.email = 'Email is required'
  } else if (!validateEmail(form.value.email)) {
    errors.value.email = 'Please enter a valid email'
  }
  
  // Password strength
  if (form.value.password.length < 8) {
    errors.value.password = 'Password must be at least 8 characters'
  }
  
  return Object.keys(errors.value).length === 0
}
```

#### Server-Side Validation

Handle validation errors from the server:

```typescript
async function handleSubmit() {
  try {
    await axios.post('/api/users', form.value)
  } catch (error: any) {
    if (error.response?.status === 422) {
      // Laravel-style validation errors
      errors.value = error.response.data.errors
    } else if (error.response?.data?.errors) {
      // UserFrosting-style errors
      errors.value = error.response.data.errors
    }
  }
}
```

### 3. CSRF Protection

UserFrosting requires CSRF tokens for POST requests. Include them automatically:

```vue
<script setup lang="ts">
import { computed } from 'vue'
import axios from 'axios'

// Get CSRF from global site object
const csrfName = computed(() => site.csrf.keys.name)
const csrfValue = computed(() => site.csrf.name)

async function submitForm(data: any) {
  await axios.post('/api/endpoint', data, {
    headers: {
      [csrfName.value]: csrfValue.value
    }
  })
}
</script>

<template>
  <form @submit.prevent="handleSubmit">
    <!-- Hidden CSRF fields -->
    <input type="hidden" :name="csrfName" :value="csrfValue" />
    <!-- Rest of form... -->
  </form>
</template>
```

### 4. Loading States

Show feedback during submission:

```vue
<script setup lang="ts">
import { ref } from 'vue'

const isSubmitting = ref(false)

async function handleSubmit() {
  isSubmitting.value = true
  
  try {
    await axios.post('/api/endpoint', form.value)
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <button 
    type="submit" 
    :disabled="isSubmitting"
    class="uk-button uk-button-primary"
  >
    <span v-if="isSubmitting">
      <span uk-spinner="ratio: 0.5"></span> Submitting...
    </span>
    <span v-else>Submit</span>
  </button>
</template>
```

## Advanced Patterns

### Reusable Form Composable

Create a composable for common form logic:

**composables/useForm.ts**:
```typescript
import { ref, Ref } from 'vue'
import axios, { AxiosError } from 'axios'

interface UseFormOptions<T> {
  initialData: T
  onSuccess?: (data: any) => void
  onError?: (error: AxiosError) => void
}

export function useForm<T extends Record<string, any>>(
  options: UseFormOptions<T>
) {
  const form = ref<T>({ ...options.initialData }) as Ref<T>
  const errors = ref<Record<string, string>>({})
  const isSubmitting = ref(false)
  const isDirty = ref(false)

  function reset() {
    form.value = { ...options.initialData }
    errors.value = {}
    isDirty.value = false
  }

  function setErrors(serverErrors: Record<string, string | string[]>) {
    errors.value = {}
    
    for (const [field, messages] of Object.entries(serverErrors)) {
      errors.value[field] = Array.isArray(messages) 
        ? messages[0] 
        : messages
    }
  }

  async function submit(url: string, method: 'post' | 'put' | 'patch' = 'post') {
    isSubmitting.value = true
    errors.value = {}
    
    try {
      const response = await axios[method](url, form.value, {
        headers: {
          [site.csrf.keys.name]: site.csrf.name,
          [site.csrf.keys.value]: site.csrf.value
        }
      })
      
      options.onSuccess?.(response.data)
      return response.data
      
    } catch (error: any) {
      if (error.response?.data?.errors) {
        setErrors(error.response.data.errors)
      }
      
      options.onError?.(error)
      throw error
      
    } finally {
      isSubmitting.value = false
    }
  }

  return {
    form,
    errors,
    isSubmitting,
    isDirty,
    reset,
    setErrors,
    submit
  }
}
```

**Usage**:
```vue
<script setup lang="ts">
import { useForm } from '@/composables/useForm'

interface UserForm {
  username: string
  email: string
  password: string
}

const { form, errors, isSubmitting, submit, reset } = useForm<UserForm>({
  initialData: {
    username: '',
    email: '',
    password: ''
  },
  onSuccess: (data) => {
    alert('User created successfully!')
    reset()
  }
})

async function handleSubmit() {
  await submit('/api/users', 'post')
}
</script>

<template>
  <form @submit.prevent="handleSubmit">
    <input v-model="form.username" />
    <span v-if="errors.username">{{ errors.username }}</span>
    
    <button type="submit" :disabled="isSubmitting">
      Submit
    </button>
  </form>
</template>
```

### File Upload

Handle file uploads with progress tracking:

```vue
<script setup lang="ts">
import { ref } from 'vue'
import axios from 'axios'

const selectedFile = ref<File | null>(null)
const uploadProgress = ref(0)
const isUploading = ref(false)

function onFileSelected(event: Event) {
  const target = event.target as HTMLInputElement
  selectedFile.value = target.files?.[0] || null
}

async function uploadFile() {
  if (!selectedFile.value) return
  
  const formData = new FormData()
  formData.append('file', selectedFile.value)
  formData.append(site.csrf.keys.name, site.csrf.name)
  formData.append(site.csrf.keys.value, site.csrf.value)
  
  isUploading.value = true
  uploadProgress.value = 0
  
  try {
    await axios.post('/api/upload', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      },
      onUploadProgress: (progressEvent) => {
        if (progressEvent.total) {
          uploadProgress.value = Math.round(
            (progressEvent.loaded * 100) / progressEvent.total
          )
        }
      }
    })
    
    alert('File uploaded successfully!')
    
  } finally {
    isUploading.value = false
  }
}
</script>

<template>
  <div>
    <input type="file" @change="onFileSelected" />
    
    <button 
      @click="uploadFile" 
      :disabled="!selectedFile || isUploading"
    >
      Upload
    </button>
    
    <div v-if="isUploading">
      <progress :value="uploadProgress" max="100"></progress>
      {{ uploadProgress }}%
    </div>
  </div>
</template>
```

### Dynamic Form Fields

Add/remove fields dynamically:

```vue
<script setup lang="ts">
import { ref } from 'vue'

interface EmailField {
  id: number
  value: string
}

const emails = ref<EmailField[]>([
  { id: 1, value: '' }
])

let nextId = 2

function addEmail() {
  emails.value.push({
    id: nextId++,
    value: ''
  })
}

function removeEmail(id: number) {
  emails.value = emails.value.filter(email => email.id !== id)
}
</script>

<template>
  <div v-for="email in emails" :key="email.id">
    <input v-model="email.value" type="email" />
    <button @click="removeEmail(email.id)">Remove</button>
  </div>
  
  <button @click="addEmail">Add Email</button>
</template>
```

## Form Validation Libraries

For complex validation, consider these libraries:

### VeeValidate

[VeeValidate](https://vee-validate.logaretm.com/v4/) provides comprehensive form validation:

```bash
npm install vee-validate yup
```

```vue
<script setup lang="ts">
import { useForm } from 'vee-validate'
import * as yup from 'yup'

const schema = yup.object({
  email: yup.string().required().email(),
  password: yup.string().required().min(8),
})

const { defineField, handleSubmit, errors } = useForm({
  validationSchema: schema
})

const [email] = defineField('email')
const [password] = defineField('password')

const onSubmit = handleSubmit((values) => {
  console.log('Form submitted:', values)
})
</script>

<template>
  <form @submit="onSubmit">
    <input v-model="email" type="email" />
    <span>{{ errors.email }}</span>
    
    <input v-model="password" type="password" />
    <span>{{ errors.password }}</span>
    
    <button type="submit">Submit</button>
  </form>
</template>
```

## Integration with Twig

Render your form component in a Twig template:

**templates/pages/login.html.twig**:
```twig
{% extends '@admin/pages/abstract/dashboard.html.twig' %}

{% block content %}
    <div id="login-form-app"></div>
{% endblock %}

{% block scripts_page %}
    {{ vite_js('login.ts') }}
{% endblock %}
```

**assets/login.ts**:
```typescript
import { createApp } from 'vue'
import LoginForm from './components/LoginForm.vue'

createApp(LoginForm).mount('#login-form-app')
```

## Best Practices

### 1. Validate on Both Sides

Always validate on both client and server:
- **Client**: Immediate feedback, better UX
- **Server**: Security, can't be bypassed

### 2. Clear Error Messages

Provide specific, actionable error messages:

✅ **Good**: "Email must be in format: user@example.com"  
❌ **Bad**: "Invalid input"

### 3. Disable Submit During Processing

Prevent duplicate submissions:

```vue
<button 
  type="submit" 
  :disabled="isSubmitting || !isValid"
>
  Submit
</button>
```

### 4. Show Field-Level Errors

Display errors next to the relevant field:

```vue
<div class="uk-margin">
  <input v-model="form.email" :class="{ 'uk-form-danger': errors.email }" />
  <div v-if="errors.email" class="uk-text-danger">
    {{ errors.email }}
  </div>
</div>
```

### 5. Reset Forms After Success

Clear the form after successful submission:

```typescript
async function handleSubmit() {
  await submit('/api/users')
  // Reset form
  form.value = { ...initialData }
  errors.value = {}
}
```

## What's Next?

- **[Tables](client-side-code/components/tables)**: Display data in sortable, filterable tables
- **[Collections](client-side-code/components/collections)**: Manage dynamic lists of items
- **[Alerts](client-side-code/components/alerts)**: Show notifications to users

## Further Reading

- [Vue 3 Form Handling](https://vuejs.org/guide/essentials/forms.html)
- [VeeValidate Documentation](https://vee-validate.logaretm.com/v4/)
- [Axios Documentation](https://axios-http.com/docs/intro)
- [UIkit Forms](https://getuikit.com/docs/form)
