---
title: Pinia State Management
description: How to manage state in your Vue components using Pinia in UserFrosting 
---

[Pinia](https://pinia.vuejs.org/) is Vue's official state management library. It gives you a central place to store data shared across multiple components, such as the current user, app configuration, notifications, or UI preferences.

If Vue's local state (`ref`, `reactive`) is enough for a single component, keep it local. Reach for Pinia when state needs to be reused, coordinated, or persisted across different parts of your app.

## Why Use Pinia?

- **Predictable state flow** - One source of truth instead of duplicated state in many components
- **Reusable logic** - Keep business logic in stores instead of repeating it in UI components
- **Great developer experience** - TypeScript support, Vue Devtools integration, and simple API

## Pinia in UserFrosting

In a standard UserFrosting frontend app, Pinia is already installed and wired in your app bootstrap. You can start creating stores right away.

UserFrosting also includes the Pinia persistence plugin. When a store enables persistence, its state is saved in browser storage and restored after a page reload.

You can create stores in `app/assets/stores/`. For example, let's create a simple counter store:

```ts
// app/assets/stores/counter.ts
import { defineStore } from 'pinia'

export const useCounterStore = defineStore('counter', {
	state: () => ({
		count: 0,
	}),
	getters: {
		doubleCount: (state) => state.count * 2,
	},
	actions: {
		increment() {
			this.count++
		},
	},
	persist: true, // Enable persistence for this store
})
```

Use it in any component:

```vue
<script setup lang="ts">
import { useCounterStore } from '../stores/counter'

const counter = useCounterStore()
</script>

<template>
	<button @click="counter.increment">Count: {{ counter.count }}</button>
	<p>Double: {{ counter.doubleCount }}</p>
</template>
```

If you where to use the same counter in a second component, it would share the same state. Incrementing the counter in one component would update it in the other.

If `persist: true` is enabled on a Pinia store, the counter value is restored after refresh. This is different than a local component state (`ref`), where the value is recreated from scratch on each reload.
