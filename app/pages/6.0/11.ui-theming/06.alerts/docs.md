---
title: Alerts and Notifications
description: Provide clear user feedback with Pink-Cupcake alert components and UIkit notifications.
---

Feedback turns actions into confidence. When a user clicks Save and nothing happens — no confirmation, no error, no movement — your app feels broken, even when the save actually worked. Good UI always closes the loop.

In UserFrosting apps, you have two practical ways to communicate feedback to users:

1. **Persistent in-page alerts** with `UFAlert` — ideal for validation summaries or status messages connected to the current action.
2. **Temporary toast notifications** with UIkit — ideal for quick confirmations that don't need to stay on screen.

## In-Page Alerts with `UFAlert`

`UFAlert` is a Pink-Cupcake component that renders a dismissible alert block. It takes an `AlertInterface` object via its required `:alert` prop — the same object your API composables return as `apiError`. Drop it near the top of your form or content area:

```vue
<script setup lang="ts">
import { ref } from 'vue'
import { Severity, type AlertInterface } from '@userfrosting/sprinkle-core'

const alert = ref<AlertInterface | null>(null)

function showSuccess() {
    alert.value = { title: 'Profile saved', style: Severity.Success, closeBtn: true }
}
</script>

<template>
    <UFAlert v-if="alert" :alert="alert" @close="alert = null" />
</template>
```

The `@close` event fires when the user dismisses the alert, so you can clear it from state. `UFAlert` is best for messages that are tied to a specific ongoing action on this page — a form submission result, a validation summary, a confirmation of what just happened.

## Toast Notifications with UIkit

For quick, transient feedback that doesn't need to occupy page space, UIkit's built-in notification system works well:

```ts
import UIkit from 'uikit'

UIkit.notification('Profile saved', {
    status: 'success',
    pos: 'top-right',
    timeout: 2500
})
```

Available `status` values are `primary`, `success`, `warning`, and `danger`. The notification auto-dismisses after `timeout` milliseconds without any extra code.

## When to Use Which

- **Use `UFAlert`** when the message relates to a specific action on the current page and the user should be able to read it at their own pace (a failed form submission, a warning before a destructive action).
- **Use UIkit notifications** for brief background confirmations the user should notice but doesn't need to act on (record saved, item deleted, settings updated).

Avoid triggering both for the same event — pick one channel and stay consistent throughout your app. Having an alert *and* a toast appear for the same action creates noise and confusion.
